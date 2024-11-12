<?php

namespace App\EntityListener;


use App\Entity\Sortie;
use App\Repository\EtatRepository;

class EtatListener
{

    public function __construct(
        private readonly EtatRepository $etatRepository
    ) {}

    public function postLoad(Sortie $sortie): void{
        $etat = $this->etatRepository->findOneBy(['libelle' => 'Cloturée']);

        if ($sortie->getDateLimiteInscription() >= new \DateTime()){
            $sortie->setEtat($etat);
        }
    }


}