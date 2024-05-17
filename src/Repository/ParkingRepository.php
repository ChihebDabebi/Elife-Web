<?php

namespace App\Repository;

use App\Entity\Parking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parking>
 *
 * @method Parking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parking[]    findAll()
 * @method Parking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParkingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parking::class);
    }

    /*
    public function findBySearchTerm($searchTerm, $sortBy, $sortOrder)
{
    $queryBuilder = $this->createQueryBuilder('p');

    if ($searchTerm) {
        $queryBuilder
            ->andWhere('p.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%');
            // Ajoutez d'autres conditions de recherche si nécessaire
    }

    if ($sortBy && property_exists(Parking::class, $sortBy)) {
        $queryBuilder->orderBy('p.'.$sortBy, $sortOrder);
    }

    return $queryBuilder->getQuery()->getResult();
}
*/
public function findBySearchTerm($searchTerm, $sortBy = null, $sortOrder = 'ASC')
{
    $queryBuilder = $this->createQueryBuilder('p');

    if ($searchTerm) {
        $queryBuilder
            ->andWhere('p.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%');
            // Ajoutez d'autres conditions de recherche si nécessaire
    }

    if ($sortBy && property_exists(Parking::class, $sortBy)) {
        $queryBuilder->orderBy('p.'.$sortBy, $sortOrder);
    }

    return $queryBuilder->getQuery()->getResult();
}


public function countAvailablePlaces(): array
{
    return $this->createQueryBuilder('p')
        ->select('p.idparking', 'p.nom', 'p.capacite - COUNT(v.idvoiture) as place_disponible')
        ->leftJoin('p.voitures', 'v')
        ->groupBy('p.idparking')
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Parking[] Returns an array of Parking objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Parking
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}