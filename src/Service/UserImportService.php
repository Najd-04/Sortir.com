<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserImportService
{
  private EntityManagerInterface $entityManager;
  private UserPasswordHasherInterface $passwordHasher;

  public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
  {
    $this->entityManager = $entityManager;
    $this->passwordHasher = $passwordHasher;
  }

  public function importUsersFromFile($file): array
  {
    $handle = fopen($file->getPathname(), 'r');
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
      $site = $this->entityManager->getRepository(Site::class)->findOneBy(['nom' => $data[5]]);

      if (!$site) {
        error_log("Site non trouvé pour le nom: " . $data[5]);
        continue;
      }

      $existingParticipant = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $data[3]]);

      if ($existingParticipant) {
        error_log("Participant avec email déjà existant: " . $data[3]);
        continue;
      }

      $participant = new Participant();
      $participant->setPseudo($data[0]);
      $participant->setPrenom($data[1]);
      $participant->setNom($data[2]);
      $participant->setEmail($data[3]);
      $participant->setTelephone($data[4]);
      $participant->setSite($site);
      $participant->setPassword(
        $this->passwordHasher->hashPassword($participant, $data[6])
      );
      $participant->setActif(true);
      $participant->setRoles(['ROLE_USER']);
      $this->entityManager->persist($participant);
    }
    fclose($handle);

    $this->entityManager->flush();
    return ['status' => 'success', 'message' => 'Tous les utilisateurs ont été inscrits.'];
  }

}
