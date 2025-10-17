<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findLastTenPosts()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC') 
            ->setMaxResults(10)              
            ->getQuery()
            ->getResult();
    }

    public function findPosts(int $offset, int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFirstTenPostsByUser(User $user): array {
        return $this->findBy(['author' => $user], ['createdAt' => 'DESC'], 10, 0);
    }

    public function findPostsByUser(User $user, int $offset, int $limit): array
    {
        return $this->findBy(['author' => $user], ['createdAt' => 'DESC'], $limit, $offset);
    }

    public function findPostWithLimitedComments(int $id, int $limit): Post
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.comments', 'c')
            ->addSelect('c')
            ->where('p.id = :postId')
            ->setParameter('postId', $id)
            ->setMaxResults($limit)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
