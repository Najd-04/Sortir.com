<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findSortieAvecParametre(
        ?string      $site,
        ?string      $nom,
        ?string      $dateDebut,
        ?string      $dateFin,
        ?Etat        $etatPasse,
        ?Participant $organisateur,
        ?Participant $isInscrit, // Participant inscrit
        ?Participant $isNotInscrit, // Participant non inscrit
        Participant  $connectedUser, // L'utilisateur connecté
        Etat $etatEnCreation // L'état "En création"
    ): array
    {
        $query = $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'ASC');

        // Filtrage par site
        if (!empty($site)) {
            $query->andWhere('s.site = :site')
                ->setParameter('site', $site);
        }

        // Filtrage par nom de sortie
        if (!empty($nom)) {
            $query->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $nom . '%');
        }

        // Filtrage par dates
        if (!empty($dateDebut) && !empty($dateFin)) {
            $query->andWhere('s.dateHeureDebut BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        } elseif (!empty($dateDebut)) {
            $query->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        } elseif (!empty($dateFin)) {
            $query->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }

        // Filtrage par état
        if (!empty($etatPasse)) {
            $query->andWhere('s.etat = :etatPasse')
                ->setParameter('etatPasse', $etatPasse);
        }

        // Filtrage par organisateur
        if (!empty($organisateur)) {
            $query->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $organisateur);
        }

        // Filtrage des sorties où le participant est inscrit
        if (!empty($isInscrit)) {
            $query->join('s.inscriptions', 'i')
                ->andWhere('i.participant = :isInscrit')
                ->setParameter('isInscrit', $isInscrit);
        }

        if (!empty($isNotInscrit)) {
            $query->andWhere('s.id NOT IN (
            SELECT sortie.id 
            FROM App\Entity\Inscription i_sub
            JOIN i_sub.sortie sortie
            WHERE i_sub.participant = :isNotInscrit
        )')
                ->setParameter('isNotInscrit', $isNotInscrit);
        }

        // Condition pour afficher les sorties "En création" uniquement si l'utilisateur est l'organisateur
        $query->andWhere('s.etat != :etatEnCreation OR s.organisateur = :connectedUser')
            ->setParameter('etatEnCreation', $etatEnCreation)
            ->setParameter('connectedUser', $connectedUser);

        return $query->getQuery()->getResult();
    }


    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
