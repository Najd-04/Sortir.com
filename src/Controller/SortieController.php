<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Helper\ImbricateForm;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{

    #[Route('/list', name: '_list')]
    public function index(SortieRepository $repository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $sites = $entityManager->getRepository(Site::class)->findAll();
        $site = $request->query->get('site', '');
        $nom = $request->query->get('nom', '');
        $dateDebut = $request->query->get('date_debut', '');
        $dateFin = $request->query->get('date_fin', '');
        $sortiePassee = $request->query->get('sortie_passee', false);
        $organisateurChecked = $request->query->get('organisateur_checked', false);
        $isInscritChecked = $request->query->get('is_inscrit_checked', false); // Checkbox "Inscrit"
        $isNotInscritChecked = $request->query->get('is_not_inscrit_checked', false);
        $etat = null;
        if ($sortiePassee) {
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'passée']);
        }

        $organisateur = null;
        if ($organisateurChecked) {
            $organisateur = $user;
        }

        $isInscrit = null;
        if ($isInscritChecked) {
            $isInscrit = $this->getUser();
        }

        $isNotInscrit = null;
        if ($isNotInscritChecked) {
            $isNotInscrit = $this->getUser();
        }

        // Appliquer les filtres en fonction des checkboxes
        $sorties = $repository->findSortieAvecParametre(
            $site,
            $nom,
            $dateDebut,
            $dateFin,
            $etat,
            $organisateur,
            $isInscrit,
            $isNotInscrit
        );

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
            'selected_site' => $site,
            'nom' => $nom,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'sortie_passee' => $sortiePassee,
            'organisateur_checked' => $organisateurChecked,
            'is_inscrit_checked' => $isInscritChecked, // Passer la valeur "Inscrit"
            'is_not_inscrit_checked' => $isNotInscritChecked, // Passer la valeur "Non inscrit"
        ]);
    }


    #[Route('/detail/{id}', name: '_detail')]
    public function detail(Sortie $sortie): Response {
      $googleMapsApiKey = $_ENV['GOOGLE_MAPS_API_KEY'];

      return $this->render('sortie/detail.html.twig', [
        'sortie' => $sortie,
        'google_maps_api_key' => $googleMapsApiKey,
      ]);
    }

    #[Route('/new', name: '_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, ImbricateForm $imbricateForm): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imbricateForm->addLieuToSortieForm($form, $sortie, $request);
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('sortie_list');
        }
        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: '_update', requirements: ['id' => '\d+']), ]
    public function update(Request $request, EntityManagerInterface $entityManager, Sortie $sortie, ImbricateForm $imbricateForm): Response
    {
        $connectedUser = $this->getUser()->getUserIdentifier();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($connectedUser === $sortie->getOrganisateur()) {
                $imbricateForm->addLieuToSortieForm($form, $sortie, $request);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('sortie_list');
        }
        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}

