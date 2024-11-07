<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class InscriptionController extends AbstractController
{
  #[Route('/sortie/{id}/inscrire', name: 'app_sortie_inscrire')]
  #[IsGranted('IS_AUTHENTICATED_FULLY')]
  public function inscrire(Sortie $sortie, EntityManagerInterface $entityManager): RedirectResponse
  {
    $participant = $this->getUser();

    // Vérifier si l'utilisateur est déjà inscrit
    foreach ($sortie->getInscriptions() as $inscription) {
      if ($inscription->getParticipant() === $participant) {
        $this->addFlash('error', 'Vous êtes déjà inscrit.');
        return $this->redirectToRoute('sortie_list', ['id' => $sortie->getId()]);
      }
    }

    $inscription = new Inscription();
    $inscription->setParticipant($participant);
    $inscription->setSortie($sortie);

    $entityManager->persist($inscription);
    $entityManager->flush();

    $this->addFlash('success', 'Inscription réussie !');
    return $this->redirectToRoute('sortie_list', ['id' => $sortie->getId()]);
  }

  #[Route('/sortie/{id}/desister', name: 'app_sortie_desister')]
  #[IsGranted('IS_AUTHENTICATED_FULLY')]
  public function desister(Sortie $sortie, EntityManagerInterface $entityManager): RedirectResponse
  {
    $participant = $this->getUser();

    $inscription = $entityManager->getRepository(Inscription::class)->findOneBy([
      'sortie' => $sortie,
      'participant' => $participant,
    ]);

    if (!$inscription) {
      $this->addFlash('error', 'Vous n\'êtes pas inscrit.');
      return $this->redirectToRoute('sortie_list', ['id' => $sortie->getId()]);
    }

    $entityManager->remove($inscription);
    $entityManager->flush();

    $this->addFlash('success', 'Vous avez annulé votre inscription.');
    return $this->redirectToRoute('sortie_list', ['id' => $sortie->getId()]);
  }
}
