<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Crée un QueryBuilder de base avec les jointures communes et le tri par date
     * @param bool $activeOnly Si true, ajoute une condition pour n'inclure que les articles actifs (non supprimés)
     */
    private function createBaseQueryBuilder(bool $activeOnly = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('author', 'categories')
            ->leftJoin('a.author', 'author')
            ->leftJoin('a.categories', 'categories')
            ->orderBy('a.createdAt', Criteria::DESC);
            
        if ($activeOnly) {
            $qb->andWhere('a.deletedAt IS NULL');
        }
        
        return $qb;
    }

    /**
     * @return Article[] Returns an array of Article objects with their authors and categories
     */
    public function findAllWithAuthorsAndCategories(): array
    {
        return $this->createBaseQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of active (non-deleted) articles
     */
    public function findAllActive(): array
    {
        return $this->createBaseQueryBuilder(true)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of latest articles
     */
    public function findLatest(int $limit = 4): array
    {
        return $this->createBaseQueryBuilder(true)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @return Article[] Returns an array of latest articles excluding the specified IDs
     */
    public function findLatestExcept(int $limit = 6, array $excludeIds = []): array
    {
        $qb = $this->createBaseQueryBuilder(true);
        
        if (!empty($excludeIds)) {
            $qb->andWhere('a.id NOT IN (:excludeIds)')
               ->setParameter('excludeIds', $excludeIds);
        }
        
        return $qb->setMaxResults($limit)
                 ->getQuery()
                 ->getResult();
    }

    /**
     * @return Article[] Returns an array of articles by category name
     */
    public function findByCategory(string $categoryName): array
    {
        return $this->createBaseQueryBuilder(true)
            ->andWhere('categories.name = :categoryName')
            ->setParameter('categoryName', $categoryName)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of articles matching the search query
     */
    public function searchByQuery(string $query): array
    {
        return $this->createBaseQueryBuilder(true)
            ->where('LOWER(a.title) LIKE LOWER(:query)')
            ->orWhere('LOWER(a.content) LIKE LOWER(:query)')
            ->orWhere('EXISTS (
                SELECT c FROM App\Entity\Category c 
                WHERE c MEMBER OF a.categories 
                AND LOWER(c.name) LIKE LOWER(:query)
            )')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée d'articles avec filtres
     * 
     * @param array $filters Un tableau de filtres pouvant contenir:
     *                      - query: Terme de recherche
     *                      - category: Entité Category
     *                      - dateFrom: Date de début
     *                      - dateTo: Date de fin
     * @return Article[] Un tableau d'articles correspondant aux filtres
     */
    public function findByAdvancedFilters(array $filters): array
    {
        $qb = $this->createBaseQueryBuilder(true);
        
        // Recherche textuelle
        if (!empty($filters['query'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(a.title)', 'LOWER(:query)'),
                $qb->expr()->like('LOWER(a.content)', 'LOWER(:query)'),
                'EXISTS (
                    SELECT c FROM App\Entity\Category c 
                    WHERE c MEMBER OF a.categories 
                    AND LOWER(c.name) LIKE LOWER(:query)
                )'
            ))
            ->setParameter('query', '%' . $filters['query'] . '%');
        }
        
        // Filtre par catégorie
        if (!empty($filters['category'])) {
            $qb->andWhere('categories = :category')
               ->setParameter('category', $filters['category']);
        }
        
        // Filtre par date de début
        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('a.createdAt >= :dateFrom')
               ->setParameter('dateFrom', $filters['dateFrom']);
        }
        
        // Filtre par date de fin
        if (!empty($filters['dateTo'])) {
            // Ajouter 1 jour à la date de fin pour inclure les articles créés ce jour-là
            $dateTo = clone $filters['dateTo'];
            $dateTo->modify('+1 day');
            
            $qb->andWhere('a.createdAt < :dateTo')
               ->setParameter('dateTo', $dateTo);
        }
        
        return $qb->getQuery()->getResult();
    }
}
