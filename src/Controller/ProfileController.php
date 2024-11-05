<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(): Response
    {
      $user = $this->getUser(); // Récupère l'utilisateur connecté

      // Redirection si l'utilisateur n'est pas authentifié
      if (!$user) {
        return $this->redirectToRoute('app_login');
      }

      return $this->render('profile/profile.html.twig', [
        'user' => $user,
      ]);
    }
}
