<?php

namespace App\Controller;


use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    #[Route('/villes.Sql', name: '_ville')]


    public function afficherVilles( VilleRepository $repository): Response
    {

        /*
    *  temps 1 : afficher villes.Sql
       temps 2: ajouter villes.Sql
       temps 3: rechercher villes.Sql
    */


        $villes = $repository->findAll();


        //todo: MAJ template quand prêt
        return $this->render('gestion_admin/ville.html.twig', [
            'villes.Sql' => $villes,

        ]);
    }





}

