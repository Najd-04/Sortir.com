<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
  #[Route(path: '/login', name: 'app_login')]
  public function login(AuthenticationUtils $authenticationUtils): Response
  {
    // Récupérer l'erreur de connexion s'il y en a une
    $error = $authenticationUtils->getLastAuthenticationError();

    // Dernier nom d'utilisateur saisi par l'utilisateur
    $lastUsername = $authenticationUtils->getLastUsername();
    if ($this->getUser()) {
      return $this->redirectToRoute('app_home'); // Redirige vers la page d'accueil
    }

    return $this->render('login/login.html.twig', [
      'last_username' => $lastUsername,
      'error' => $error,
    ]);
  }

  #[Route(path: '/logout', name: 'app_logout')]
  public function logout(): void
  {

    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
  }

  #[Route('/', name: 'app_home')]
  public function home(): Response
  {

    return $this->render('home/home.html.twig');
  }
  #[Route(path: '/login_success', name: 'app_login_success')]
  public function loginSuccess(): Response
  {
    $this->addFlash('success', 'Connexion réussie !');
    return $this->redirectToRoute('app_home');
  }
  #[Route('/redirect_after_logout', name: 'app_redirect_after_logout')]
  public function redirectAfterLogout(): Response
  {
    // Ajouter le message de déconnexion réussie
    $this->addFlash('success', 'Déconnexion réussie !');
    return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
  }
}

