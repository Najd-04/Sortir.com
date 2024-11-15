<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Exception as CsvException;
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

  public function importUsersFromFile(string $file): array
  {
    try {
      // Lecture du fichier CSV
      $csv = Reader::createFromPath($file, 'r');
      $csv->setHeaderOffset(0); // La première ligne contient les en-têtes

      $records = $csv->getRecords(); // Obtenir les enregistrements
      $counter = 0; // Nombre d'utilisateurs ajoutés

      foreach ($records as $record) {
        // Vérification de la présence du site
        $site = $this->entityManager->getRepository(Site::class)->findOneBy(['nom' => $record['site']]);
        if (!$site) {
          // Si le site n'est pas trouvé, on passe au suivant
          continue;
        }

        // Vérifie si un participant existe déjà avec l'email
        $existingParticipant = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $record['email']]);
        if ($existingParticipant) {
          continue; // Ignore ce participant s'il existe déjà
        }

        // Création du participant
        $participant = new Participant();
        $participant->setPseudo($record['pseudo']);
        $participant->setPrenom($record['prenom']);
        $participant->setNom($record['nom']);
        $participant->setEmail($record['email']);
        $participant->setTelephone($record['telephone'] ?? null);
        $participant->setSite($site);
        $participant->setPassword(
          $this->passwordHasher->hashPassword($participant, $record['password'])
        );
        $participant->setActif(true);
        $participant->setRoles(['ROLE_USER']);

        // Persister l'entité
        $this->entityManager->persist($participant);
        $counter++;
      }

      // Effectue un seul flush pour toutes les entités
      $this->entityManager->flush();

      // Retourne un message de succès
      return [
        'status' => 'success',
        'message' => "$counter utilisateurs ont été inscrits avec succès."
      ];

    } catch (CsvException $e) {
      // Gestion des erreurs de lecture du fichier CSV
      return [
        'status' => 'error',
        'message' => 'Erreur lors de la lecture du fichier CSV : ' . $e->getMessage()
      ];
    } catch (\Exception $e) {
      // Gestion des autres erreurs
      return [
        'status' => 'error',
        'message' => 'Erreur inattendue : ' . $e->getMessage()
      ];
    }
  }
}
