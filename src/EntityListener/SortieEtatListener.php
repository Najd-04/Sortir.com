<?php

namespace App\EntityListener;


use App\Entity\Sortie;
use App\Repository\EtatRepository;

class SortieEtatListener
{

    public function __construct(
        private readonly EtatRepository $etatRepository
    ) {}


    //
    public function postLoad(Sortie $sortie): void{
        $cloturee = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $enCours = $this->etatRepository->findOneBy(['libelle' => 'En cours']);
        $terminee = $this->etatRepository->findOneBy(['libelle' => 'Terminée']);

        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        $dateDebutSortie = $sortie->getDateHeureDebut();
        $dureeSortie = $sortie->getDuree();

        $dateFinSortie = $dateDebutSortie->modify('+' . $dureeSortie . ' minutes');

        $now = new \DateTime();

        if ($dateLimiteInscription >= $now && $dateLimiteInscription < $dateDebutSortie){
            $sortie->setEtat($cloturee);
        }

        if ($now > $dateDebutSortie && $now < $dateFinSortie) {
            $sortie->setEtat($enCours);
        }

        if ($now > $dateFinSortie) {
            $sortie->setEtat($terminee);
        }


    }


}