<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
        
    }

    public function findBySearch($value): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.title LIKE :val')
            ->orWhere( 'a.content LIKE :val')
            ->orWhere('a.summary LIKE :val')
            ->setParameter('val', '%' . $value .'%')
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTopArticle(): ?Article
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.likes', 'l') // on lie la table likes
            ->leftJoin('a.comments', 'c') // on lie la table comments
            ->addSelect('COUNT(DISTINCT l) AS HIDDEN likesCount') // on compte le nombre de likes différents
            ->addSelect('COUNT(DISTINCT c) AS HIDDEN commentsCount') // on compte le nombre de commentaires different
            ->where('a.publication = 1') // uniquement les articles publiés
            ->groupBy('a.id') // on les groupes par leur id
            ->orderBy('likesCount', 'DESC') // on les classe par nombre de likes décroissant
            ->addOrderBy('commentsCount', 'DESC') // on les classe par nombre de commentaires décroissant
            ->setMaxResults(1) // on ne garde q'un seul résultat
            ->getQuery() // on génére la requêt finale
            ->getOneOrNullResult() // on exécute la requête, renvoi null si par de résultat
        ;
    }

//    /**
//     * @return Article[] Returns an array of Article objects
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

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
