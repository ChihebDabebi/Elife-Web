<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function findDistinctCategories(): array
    {
        $categories = $this->createQueryBuilder('r')
            ->select('DISTINCT r.categorierec')
            ->getQuery()
            ->getResult();

        return array_map('current', $categories);
    }

    public function getReclamationsByCategory(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.categorierec, COUNT(r.idrec) as reclamationsCount')
            ->groupBy('r.categorierec')
            ->getQuery()
            ->getResult();
    }

    public function getReclamationsByDaterec(): array
    {
        $reclamations = $this->createQueryBuilder('r')
            ->select('r.daterec as date, COUNT(r.idrec) as reclamationsCount')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        // Format the date in PHP
        foreach ($reclamations as &$reclamation) {
            $reclamation['date'] = $reclamation['date']->format('Y-m-d');
        }

        return $reclamations;
    }
    public function findOneByDescrirec(string $query): ?Reclamation
    {
        return $this->createQueryBuilder('r')
        ->where('r.descrirec = :descrirec')
        ->setParameter('descrirec', $descrirec)
        ->getQuery()
        ->getOneOrNullResult()
    ;
    }
}