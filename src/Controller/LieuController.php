<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu/ajouter', name: 'ajouter_lieu', methods: ['POST'])]
    public function ajouterLieu(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $villeId = $request->request->get('ville_id');
        $lieuName = $request->request->get('lieu_name');

        $ville = $em->getRepository(Ville::class)->find($villeId);

        if (!$ville || !$lieuName) {
            return new JsonResponse(['error' => 'Ville ou nom du lieu manquant'], 400);
        }

        $lieu = new Lieu();
        $lieu->setNom($lieuName);
        $lieu->setVille($ville);

        $em->persist($lieu);
        $em->flush();

        return new JsonResponse([
            'id' => $lieu->getId(),
            'name' => $lieu->getNom()
        ]);
    }
}
