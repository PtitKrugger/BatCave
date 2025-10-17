<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findMoreCommentsByPost(int $postId, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.post', 'p')  
            ->addSelect('p')  
            ->where('p.id = :postId')
            ->setParameter('postId', $postId)
            ->setFirstResult($offset)  
            ->setMaxResults($limit) 
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCommentsCountByPost(int $postId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->leftJoin('c.post', 'p')
            ->where('p.id = :postId')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
