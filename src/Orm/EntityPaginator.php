<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\EntityPaginatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\PaginatorDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class EntityPaginator implements EntityPaginatorInterface
{
    private $crudUrlGenerator;
    private $entityFactory;
    private $currentPage;
    private $pageSize;
    private $results;
    private $numResults;

    public function __construct(CrudUrlGenerator $crudUrlGenerator, EntityFactory $entityFactory)
    {
        $this->crudUrlGenerator = $crudUrlGenerator;
        $this->entityFactory = $entityFactory;
    }

    public function paginate(PaginatorDto $paginatorDto, QueryBuilder $queryBuilder): EntityPaginatorInterface
    {
        $this->pageSize = $paginatorDto->getPageSize();
        $this->currentPage = max(1, $paginatorDto->getPageNumber());
        $firstResult = ($this->currentPage - 1) * $this->pageSize;

        /** @var Query $query */
        $query = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        if (0 === \count($queryBuilder->getDQLPart('join'))) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $paginator = new Paginator($query, $paginatorDto->fetchJoinCollection());

        if (null === $useOutputWalkers = $paginatorDto->useOutputWalkers()) {
            $useOutputWalkers = \count($queryBuilder->getDQLPart('having') ?: []) > 0;
        }
        $paginator->setUseOutputWalkers($useOutputWalkers);

        try {
            $this->results = $paginator->getIterator();
            $this->numResults = $paginator->count();
        } catch (\Exception $e) {
            $this->results = [];
            $this->numResults = 0;
        }

        return $this;
    }

    public function generateUrlForPage(int $page): string
    {
        return $this->crudUrlGenerator->build()->setQueryParam('page', $page)->includeReferrer()->generateUrl();
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return (int) ceil($this->numResults / $this->pageSize);
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    public function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage + 1);
    }

    public function hasToPaginate(): bool
    {
        return $this->numResults > $this->pageSize;
    }

    public function getNumResults(): int
    {
        return $this->numResults;
    }

    public function getResults(): ?iterable
    {
        return $this->results;
    }

    public function getResultsAsJson(): string
    {
        $results = null !== $this->getResults() ? iterator_to_array($this->getResults()) : [];
        foreach ($results as $entityInstance) {
            $entityDto = $this->entityFactory->createForEntityInstance($entityInstance);

            $jsonResult['results'][] = [
                'entityId' => $entityDto->getPrimaryKeyValueAsString(),
                'entityAsString' => $entityDto->toString(),
            ];
        }

        $jsonResult['has_next_page'] = $this->hasNextPage();

        return json_encode($jsonResult);
    }
}
