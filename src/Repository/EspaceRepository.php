<?php

namespace App\Repository;

use App\Entity\Espace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Espace|null find($id, $lockMode = null, $lockVersion = null)
 * @method Espace|null findOneBy(array $criteria, array $orderBy = null)
 * @method Espace[]    findAll()
 * @method Espace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Espace::class);
    }

    public function searchAndSort($searchTerm, $orderBy, $order)
    {
        $qb = $this->createQueryBuilder('e');
        
        // Filtre par terme de recherche
        if ($searchTerm) {
            $qb->andWhere('e.name LIKE :searchTerm')
               ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }
        
        // Tri
        $qb->orderBy('e.'.$orderBy, $order);
        
        return $qb->getQuery()->getResult();
    }
    
    

}
