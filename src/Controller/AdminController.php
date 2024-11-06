<?php

namespace App\Controller;


use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use App\Service\SentenceCaseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    #[Route('/villes', name: '_ville')]
    public function afficherVilles(EntityManagerInterface $em,  Request $request, VilleRepository $repository, SentenceCaseService $sentenceCaseService ): Response
    {


        /*
    *  temps 1 : afficher villes.Sql
       temps 2: ajouter villes.Sql
       temps 3: rechercher villes.Sql
    */


        //pour afficher liste villes
        $villes = $repository->findAll();


        //pour ajouter nouvelle ville
        $nouvelleVille = new Ville($sentenceCaseService);

        $inputForm = $this->createForm(VilleType::class, $nouvelleVille);
        $inputForm->handleRequest($request);

        // dump($inputForm->getErrors(true));

        if ($inputForm->isSubmitted() && $inputForm->isValid()) {


            $em->persist($nouvelleVille);
            $em->flush();

            $this->addFlash('success', 'Une nouvelle ville a été ajoutée');
            return $this->redirectToRoute('admin_ville');
        }


        return $this->render('gestion_admin/ville.html.twig', [
            'villes' => $villes,
            'inputForm' => $inputForm,
        ]);
    }


    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function delete(Ville $ville, EntityManagerInterface $em, Request $request, SentenceCaseService $sentenceCaseService): Response {

       $em->remove($ville);
       $em->flush();
       $this->addFlash('success','La ville a été supprimée');


        return $this->redirectToRoute('admin_ville');
    }



    private function createfForm(string $class, Ville $nouvelleVille)
    {
    }



}
