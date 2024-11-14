<?php

namespace App\EntityListener;


use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;

class SortieEtatListener
{

    public function __construct(
        private readonly EtatRepository $etatRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}


    //
    public function postLoad(Sortie $sortie): void{
        $cloturee = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $enCours = $this->etatRepository->findOneBy(['libelle' => 'En cours']);
        $terminee = $this->etatRepository->findOneBy(['libelle' => 'Terminée']);

        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        $dateDebutSortie = $sortie->getDateHeureDebut();
        $dureeSortie = $sortie->getDuree();

        $tempDateDebutSortie = clone $dateDebutSortie;



        $dateFinSortie =  $tempDateDebutSortie->modify('+' . $dureeSortie . ' minutes');

        $now = new \DateTime();

        if ($now > $dateDebutSortie && $now < $dateFinSortie) {
            $sortie->setEtat($enCours);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        }

        if ($dateLimiteInscription <= $now && $dateLimiteInscription < $dateDebutSortie){
            $sortie->setEtat($cloturee);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        }


        if ($now > $dateFinSortie) {
            $sortie->setEtat($terminee);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        }


    }


}