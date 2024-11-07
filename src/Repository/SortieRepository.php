<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
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

    public function findSortieAvecParametre(?string $nom, ?string $dateDebut, ?string $dateFin, ?Etat $etatPasse, ?Participant $organisateur): array
    {
        $query = $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'ASC');
        if (!empty($nom)) {
            $query->andWhere('s.nom like :nom')
                ->setParameter('nom', '%' . $nom . '%');
        }
        if (!empty($dateDebut) && !empty($dateFin)) {
            $query->andWhere('s.dateHeureDebut BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        } else if (!empty($dateDebut)) {
            $query->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        } else if (!empty($dateFin)) {
            $query->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }
        if (!empty($etatPasse)) {
            $query->andWhere('s.etat = :etatPasse')
                ->setParameter('etatPasse', $etatPasse);
        }
        if (!empty($organisateur)) {
            $query->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $organisateur);
        }
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
