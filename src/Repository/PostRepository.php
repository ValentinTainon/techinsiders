<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\Category;
use App\Enum\PostStatus;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getNumReadyForReview(): int
    {
        return $this->count(['status' => PostStatus::READY_FOR_REVIEW]);
    }

    public function findAllPublished(?Category $category = null, ?Tag $tag = null): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->orderBy('p.createdAt', 'DESC');

        if ($category) {
            $query->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }

        if ($tag) {
            $query->join('p.tags', 't')
                ->andWhere('t = :tag')
                ->setParameter('tag', $tag);
        }

        return $query->getQuery()->getResult();
    }

    public function findLatestPublished(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
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

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
