<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Espace;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function searchAndSort($searchTerm, $orderBy, $order)
    {
        $qb = $this->createQueryBuilder('e');

        // Filtre par terme de recherche
        if ($searchTerm) {
            $qb->andWhere('e.title LIKE :searchTerm')
               ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }

        // Tri
        $qb->orderBy('e.'.$orderBy, $order);

        return $qb->getQuery()->getResult();
    }
    public function findEventByUserId(int $userId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :user_id') // Assuming 'id' is the foreign key property in Voiture entity
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();
    }

public function isSpaceOccupiedAtDate(Espace $espace, \DateTimeInterface $date): bool
{
    return $this->createQueryBuilder('e')
        ->andWhere('e.idEspace = :espace')
        ->andWhere('e.date = :date')
        ->setParameter('espace', $espace)
        ->setParameter('date', $date)
        ->getQuery()
        ->getOneOrNullResult() !== null;
}



}
