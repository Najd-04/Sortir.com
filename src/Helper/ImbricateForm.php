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

    public function __construct(private EntityManagerInterface $entityManager, private FormFactoryInterface $formFactory)
    {
    }

    public function addLieuToSortieForm(FormInterface $form, Sortie $sortie, Request $request): void
    {
            $nouveauLieu = $form->get('lieu')->getData();
            $lieuExistant = $form->get('lieux')->getData();
            if ($nouveauLieu && $nouveauLieu->getNom() && $nouveauLieu->getRue() && $nouveauLieu->getLatitude() && $nouveauLieu->getLongitude() && $nouveauLieu->getVille()) {
                $formLieu = $this->formFactory->create(LieuType::class, $nouveauLieu);
                $formLieu->handleRequest($request);
                $this->entityManager->persist($nouveauLieu);
                $this->entityManager->flush();
                $sortie->setLieu($nouveauLieu);
            } else {
                $sortie->setLieu($lieuExistant);
            }
        }
}
