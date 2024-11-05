<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function index(SortieRepository $repository): Response
    {
        $sorties = $repository->findAll();
        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
            'sorties' => $sorties,
        ]);
    }

    #[Route('/create', name: '_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etat = $entityManager->getRepository(Etat::class)->find(1);
        $site = $entityManager->getRepository(Site::class)->find(1);
        $organisateur = $entityManager->getRepository(Participant::class)->find(1);
        $sortie = new Sortie();
        $sortie->setLieu(new Lieu());
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('saveLieu')->isClicked()) {
                // Action pour enregistrer uniquement le lieu
                $lieu = $sortie->getLieu();
                $entityManager->persist($lieu);
                $entityManager->flush();

                $this->addFlash('success', 'Le lieu a été enregistré avec succès.');

                return $this->redirectToRoute('sortie_create'); // ou redirigez vers une autre page si nécessaire

            } elseif ($form->get('saveSortie')->isClicked() && $form->isValid()) {
                // Action pour enregistrer la sortie complète (avec le lieu associé)
                $sortie->setEtat($etat);
                $sortie->setSite($site);
                $sortie->setOrganisateur($organisateur);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'Une nouvelle serie a été créée avec succès !');
                return $this->redirectToRoute('sortie_list');
            } else {
                return $this->redirectToRoute('sortie_list');
            }
        }
        return $this->render('sortie/edit.html.twig', [
            'form' => $form,
        ]);
    }

#[
Route('/update/{id}', name: '_update')]
    public function update(Request $request, EntityManagerInterface $entityManager, Sortie $sortie): Response
{
    $form = $this->createForm(SortieType::class, $sortie);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'La sortie a été modifiée avec succès !');
        return $this->redirectToRoute('sortie_list');
    }
    return $this->render('sortie/edit.html.twig', [
        'form' => $form,
    ]);
}
}

