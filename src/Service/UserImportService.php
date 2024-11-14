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

    // Lire chaque ligne du fichier CSV
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
      // Vérification de la longueur pour s'assurer d'avoir 7 éléments
      if (count($data) !== 7) {
        error_log("Ligne incomplète ou mal formatée dans le fichier CSV: " . implode(", ", $data));
        continue; // Ignorer cette ligne
      }

      // Recherche du site par nom
      $site = $this->entityManager->getRepository(Site::class)->findOneBy(['nom' => $data[5]]);
      if (!$site) {
        error_log("Site non trouvé pour le nom: " . $data[5]);
        continue; // Ignorer cette ligne si le site n'est pas trouvé
      }

      // Vérifie si un participant avec cet email existe déjà
      $existingParticipant = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $data[3]]);
      if ($existingParticipant) {
        error_log("Participant avec email déjà existant: " . $data[3]);
        continue; // Ignorer cette ligne si l'email est déjà utilisé
      }

      // Crée un nouveau participant
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
