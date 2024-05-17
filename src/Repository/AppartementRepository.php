<?php

namespace App\Repository;

use App\Entity\Appartement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Appartement>
 *
 * @method Appartement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appartement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appartement[]    findAll()
 * @method Appartement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppartementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appartement::class);
    }
    public function findByKeyword($keyword)
    {
    return $this->createQueryBuilder('a')
        ->andWhere('a.someField LIKE :keyword')
        ->setParameter('keyword', '%'.$keyword.'%')
        ->getQuery()
        ->getResult();
    }
    public function countAll(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.idappartement)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getNumberOfInvoicesPerApartment(): array
    {
        return $this->createQueryBuilder('a')
        ->select(' COUNT(f.idfacture) AS numInvoices')
        ->leftJoin('a.factures', 'f')
        ->getQuery()
        ->getResult();
    }
    public function findPaginated($page = 1, $limit = 10)
    {
        $query = $this->createQueryBuilder('p')
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, true);
    }
     // Méthode pour récupérer les appartements d'un utilisateur spécifique
     public function findAppartementsByUser($userId)
          {
              return $this->createQueryBuilder('a')
                  ->where('a.id = :userId')
                  ->setParameter('userId', $userId)
                  ->getQuery()
                  ->getResult();
          }
public function searchAndSort($searchTerm, $orderBy, $order)
{
    $qb = $this->createQueryBuilder('e');

    // Filtre par terme de recherche
    if ($searchTerm) {
        $qb->andWhere('e.numappartement LIKE :searchTerm')
           ->setParameter('searchTerm', '%'.$searchTerm.'%');
    }

    // Tri
    $qb->orderBy('e.'.$orderBy, $order);

    return $qb->getQuery()->getResult();
}
public function getStatistiquesForEtage($nbrEtages)
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.factures', 'f')
        ->where('a.nbrEtage = :nbrEtage')
        ->setParameter('etage', $nbrEtages)
        ->select('SUM(f.consommation) as consommationTotale, SUM(f.montant) as montantTotal, a.nbrEtage')
        ->groupBy('a.nbrEtage')
        ->getQuery()
        ->getResult();
}

public function getStatistiquesForAll()
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.factures', 'f')
        ->select('SUM(f.consommation) as consommationTotale, SUM(f.montant) as montantTotal, a.nbrEtage')
        ->groupBy('a.nbrEtage')
        ->getQuery()
        ->getResult();
}
public function findByTypeAndDateRange($type, $startDate, $endDate, $nbrEtage = null)
{
    $queryBuilder = $this->createQueryBuilder('f')
        ->andWhere('f.type = :type')
        ->andWhere('f.date >= :start_date')
        ->andWhere('f.date <= :end_date')
        ->setParameter('type', $type)
        ->setParameter('start_date', $startDate)
        ->setParameter('end_date', $endDate);

    if ($nbrEtage !== null) {
        $queryBuilder
            ->leftJoin('f.appartement', 'a')
            ->andWhere('a.nbrEtage = :nbrEtage')
            ->setParameter('nbrEtage', $nbrEtage);
    }

    return $queryBuilder->getQuery()->getResult();
}

//    /**
//     * @return Appartement[] Returns an array of Appartement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Appartement
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
