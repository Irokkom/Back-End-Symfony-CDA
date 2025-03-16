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
    private const CATEGORY_CONDITION = 'categories = :category';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Crée un QueryBuilder de base avec les jointures communes et le tri par date
     * @param bool $withJoins Si true, ajoute les jointures pour l'auteur et les catégories
     */
    private function createBaseQueryBuilder(bool $withJoins = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.deletedAt IS NULL')
            ->orderBy('a.createdAt', 'DESC');
        
        if ($withJoins) {
            $qb->leftJoin('a.author', 'author')
               ->leftJoin('a.categories', 'categories')
               ->addSelect('author', 'categories');
        }
        
        return $qb;
    }

    /**
     * @return Article[] Returns an array of Article objects with their authors and categories
     */
    public function findAllWithAuthorsAndCategories(): array
    {
        return $this->createBaseQueryBuilder(true)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of active (non-deleted) articles
     */
    public function findAllActive(): array
    {
        return $this->createBaseQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of latest articles
     */
    public function findLatest(int $limit = 4): array
    {
        return $this->createBaseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @return Article[] Returns an array of latest articles excluding the specified IDs
     */
    public function findLatestExcept(int $limit = 6, array $excludeIds = []): array
    {
        $qb = $this->createBaseQueryBuilder();
        
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
            $searchTerm = '%' . $filters['query'] . '%';
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(a.title)', 'LOWER(:query)'),
                $qb->expr()->like('LOWER(a.content)', 'LOWER(:query)'),
                $qb->expr()->like('LOWER(author.username)', 'LOWER(:query)'),
                'EXISTS (
                    SELECT c FROM App\Entity\Category c 
                    WHERE c MEMBER OF a.categories 
                    AND LOWER(c.name) LIKE LOWER(:query)
                )'
            ))
            ->setParameter('query', $searchTerm);
            
            // Pour debugging: affichage des paramètres SQL
            // dump($qb->getQuery()->getSQL());
            // dump($qb->getQuery()->getParameters());
        }
        
        // Filtre par catégorie
        if (!empty($filters['category'])) {
            $qb->andWhere(self::CATEGORY_CONDITION)
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

    /**
     * Recherche des articles en fonction d'une requête de recherche
     * @param string|null $query Terme de recherche
     * @param array $criteria Critères additionnels
     * @return Article[] Résultats de la recherche
     */
    public function findBySearchQuery(?string $query, array $criteria = []): array
    {
        $qb = $this->createBaseQueryBuilder(true);
        
        if ($query) {
            $qb->andWhere('a.title LIKE :query OR a.content LIKE :query OR author.username LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Ajouter des critères supplémentaires si fournis
        foreach ($criteria as $field => $value) {
            if ($field === 'category') {
                $qb->andWhere(self::CATEGORY_CONDITION)
                   ->setParameter('category', $value);
            }
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Recherche avancée avec filtres de date
     * @param string|null $query Terme de recherche
     * @param mixed|null $category Catégorie
     * @param mixed|null $author Auteur
     * @param \DateTime|null $dateFrom Date de début
     * @param \DateTime|null $dateTo Date de fin
     * @return Article[] Résultats de la recherche avancée
     */
    public function findByAdvancedSearch(?string $query, $category = null, $author = null, ?\DateTime $dateFrom = null, ?\DateTime $dateTo = null): array
    {
        $qb = $this->createBaseQueryBuilder(true);
        
        if ($query) {
            $qb->andWhere('a.title LIKE :query OR a.content LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        if ($category) {
            $qb->andWhere(self::CATEGORY_CONDITION)
               ->setParameter('category', $category);
        }
        
        if ($author) {
            $qb->andWhere('a.author = :author')
               ->setParameter('author', $author);
        }
        
        if ($dateFrom) {
            $qb->andWhere('a.createdAt >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }
        
        if ($dateTo) {
            $qb->andWhere('a.createdAt <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }
        
        return $qb->getQuery()->getResult();
    }
}
