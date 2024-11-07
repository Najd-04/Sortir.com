<?php

namespace App\Controller;


use App\Entity\Ville;
use App\Form\VilleType;
use App\Helper\SentenceCaseService;
use App\Repository\VilleRepository;
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


    #[Route('villes/delete/{id}', name: '_ville-delete', requirements: ['id' => '\d+'])]
    public function delete(Ville $ville, EntityManagerInterface $em, Request $request, SentenceCaseService $sentenceCaseService): Response {

       $em->remove($ville);
       $em->flush();
       $this->addFlash('success','La ville a été supprimée');


        return $this->redirectToRoute('admin_ville');
    }

    #[Route('villes/update/{id}', name: '_ville-update', requirements: ['id' => '\d+'])]
    public function update(Ville $ville, EntityManagerInterface $em, Request $request, SentenceCaseService $sentenceCaseService): Response {

        $updateForm = $this->createForm(VilleType::class, $ville);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {

            $em->flush();

            $this->addFlash('success', 'La ville a été modifiée avec succès.');
            return $this->redirectToRoute('admin_ville');
        }

        //template à changer ici
        return $this->render('serie/edit.html.twig', [
            'updateForm' => $updateForm,
        ]);

    }


    private function createfForm(string $class, Ville $nouvelleVille)
    {
    }



}

