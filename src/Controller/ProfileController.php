<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
  #[Route('/profile', name: 'app_profile')]
  #[IsGranted('IS_AUTHENTICATED')]
  public function profile(): Response
  {
    $user = $this->getUser();

    if (!$user) {
      return $this->redirectToRoute('app_login');
    }

    return $this->render('profile/profile.html.twig', [
      'user' => $user,
    ]);
  }

  #[Route('/profile/edit', name: 'app_profile_edit')]
  #[IsGranted('IS_AUTHENTICATED')]
  public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
  {
    $user = $this->getUser();

    // Utilise le formulaire d'inscription pour l'édition de profil
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // Si un mot de passe est fourni, on le met à jour
      $plainPassword = $form->get('plainPassword')->getData();
      if ($plainPassword) {
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
      }

      $entityManager->persist($user);
      $entityManager->flush();

      $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

      return $this->redirectToRoute('app_profile');
    }

    return $this->render('registration/register.html.twig', [
      'registrationForm' => $form->createView(),
    ]);
  }

  #[Route('/profile/delete/{id}', name: 'app_profile_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
  #[IsGranted('IS_AUTHENTICATED')]
  public function deleteProfile(Request $request, EntityManagerInterface $entityManager, Participant $user, SessionInterface $session, TokenStorageInterface $tokenStorage, Security $security): Response
  {
    // Vérifier le token CSRF
    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {

      // Supprimer l'utilisateur
      $entityManager->remove($user);
      $entityManager->flush();


      // Invalider la session pour supprimer toutes les informations de l'utilisateur
      $session->invalidate();

      // Supprimer l'utilisateur du contexte de sécurité (token)
      $tokenStorage->setToken(null);  // Effacer le token d'authentification
      // Ajouter un message de succès
      $this->addFlash('success', 'Votre profil a été supprimé avec succès.');

      // Rediriger l'utilisateur vers la page de connexion
      return $this->redirectToRoute('app_login');
    } else {
      // Si le token CSRF est invalide
      $this->addFlash('error', 'Token CSRF invalide, la suppression a échoué');
    }

    // Rediriger vers la page de profil en cas d'erreur
    return $this->redirectToRoute('app_profile');
  }
}