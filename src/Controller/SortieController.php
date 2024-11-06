<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
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

    #[Route('/new', name: '_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si un nouveau lieu a été ajouté
            $nouveauLieu = $form->get('lieu')->getData();
            if (!($nouveauLieu->getNom() == null)) {
                // Enregistrer le nouveau lieu dans la base de données
                $formLieu = $this->createForm(LieuType::class, $nouveauLieu);
                $formLieu->handleRequest($request);
                $entityManager->persist($nouveauLieu);
                $entityManager->flush();
                // Assigner ce lieu à la sortie
                $sortie->setLieu($nouveauLieu);
            } else {
                // Si aucun nouveau lieu n'est ajouté, utiliser le lieu existant sélectionné
                $sortie->setLieu($form->get('lieux')->getData());
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
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
    return $this->render('sortie/new.html.twig', [
        'form' => $form,
    ]);
}
}

