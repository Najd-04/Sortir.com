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
    // Lire le fichier CSV et inscrire les utilisateurs
    $handle = fopen($file->getPathname(), 'r');
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
      // Vérifier si le site existe
      $site = $this->entityManager->getRepository(Site::class)->findOneBy(['nom' => $data[5]]);

      if (!$site) {
        // Si le site n'existe pas, logguez un message et ignorez ce participant
        error_log("Site non trouvé pour le nom: " . $data[5]);
        continue;  // Ignorer l'utilisateur si le site est introuvable
      }

      // Vérifier si l'email existe déjà dans la base de données
      $existingParticipant = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $data[3]]);

      if ($existingParticipant) {
        // Si l'email existe déjà, logguez un message et ignorez cet utilisateur
        error_log("Participant avec email déjà existant: " . $data[3]);
        continue;
      }

      // Créer un participant
      $participant = new Participant();
      $participant->setPseudo($data[0]);
      $participant->setPrenom($data[1]);
      $participant->setNom($data[2]);
      $participant->setEmail($data[3]);
      $participant->setTelephone($data[4]);
      $participant->setSite($site);  // Assurez-vous que le site est bien une entité
      $participant->setPassword(
        $this->passwordHasher->hashPassword($participant, $data[6])
      );
      $participant->setActif(true);  // Assurez-vous que 'actif' est bien défini
      $participant->setRoles(['ROLE_USER']);
      $this->entityManager->persist($participant);
    }
    fclose($handle);

    $this->entityManager->flush();  // Enregistrez tous les participants en une seule fois
    return ['status' => 'success', 'message' => 'Tous les utilisateurs ont été inscrits.'];
  }

}
