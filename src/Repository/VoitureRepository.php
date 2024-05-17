<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Voiture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Voiture>
 *
 * @method Voiture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Voiture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Voiture[]    findAll()
 * @method Voiture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoitureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voiture::class);
    }

    
    public function findByMarque($marque)
{
    return $this->createQueryBuilder('v')
        ->andWhere('v.marque LIKE :marque')
        ->setParameter('marque', '%'.$marque.'%')
        ->getQuery(); // Retourne l'instance de requête Doctrine
}
public function findVoituresByUserId(int $userId)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.id = :user_id') // Assuming 'id' is the foreign key property in Voiture entity
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();
    }

// Ajoutez la méthode countVoituresByParking ici
public function countVoituresByParking($idParking)
{
    return $this->count(['idparking' => $idParking]);
}


//    /**
//     * @return Voiture[] Returns an array of Voiture objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Voiture
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
