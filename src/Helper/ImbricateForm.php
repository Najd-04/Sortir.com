<?php

namespace App\Helper;

use App\Controller\SortieController;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ImbricateForm
{

    public function __construct(private EntityManagerInterface $entityManager, private FormFactoryInterface $formFactory, private LieuRepository $lieuRepository, private SortieController $sortieController)
    {
    }

    public function addLieuToSortieForm(FormInterface $form, Sortie $sortie, Request $request,)
    {
        $nouveauLieu = $form->get('lieu')->getData();
//        dd($nouveauLieu);
        $lieuExistant = $form->get('lieux')->getData();
        // Vérifie si un nouveau lieu a été ajouté
        if ($nouveauLieu && $nouveauLieu->getNom()) {
            // Enregistrer le nouveau lieu dans la base de données
            $formLieu = $this->formFactory->create(LieuType::class, $nouveauLieu);
//            createForm(LieuType::class, $nouveauLieu);
            $formLieu->handleRequest($request);
            $this->entityManager->persist($nouveauLieu);
            $this->entityManager->flush();
            // Assigner ce lieu à la sortie
            $sortie->setLieu($nouveauLieu);
        } else {
            // Si aucun nouveau lieu n'est ajouté, utiliser le lieu existant sélectionné
            $sortie->setLieu($lieuExistant);
        }

    }
}
