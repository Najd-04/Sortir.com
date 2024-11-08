<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
class LieuController extends AbstractController
{
    #[Route('/get-lieux', name: 'get_lieux')]
    public function getLieux(Request $request, LieuRepository $lieuRepository): JsonResponse
    {
        $villeId = $request->query->get('ville_id');
        $lieux = [];

        if ($villeId) {
            $lieux = $lieuRepository->findBy(['ville' => $villeId]);
        }

        $lieuxData = [];
        foreach ($lieux as $lieu) {
            $lieuxData[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
            ];
        }
        return new JsonResponse($lieuxData);
    }
}
