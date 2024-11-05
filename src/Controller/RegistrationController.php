<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
  #[Route('/register', name: 'app_register')]
  public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
  {
    $user = new Participant();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      /** @var string $plainPassword */
      $plainPassword = $form->get('plainPassword')->getData();

      // encode the plain password
      $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
      $user->setActif(true);
      $entityManager->persist($user);
      $entityManager->flush();
      $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');

      return $this->redirectToRoute('app_login');
    }

    return $this->render('registration/register.html.twig', [
      'registrationForm' => $form,
    ]);
  }
}
