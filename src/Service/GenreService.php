<?php

/**
 * Genre service.
 */

namespace App\Service;

use App\Repository\GenreRepository;
use App\Repository\TaskRepository;
use App\Entity\Genre;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class GenreService.
 */
class GenreService implements GenreServiceInterface
{
    /**
     * Constructor.
     *
     * @param GenreRepository        $genreRepository Genre repository
     * @param TaskRepository         $taskRepository  Task repository
     * @param PaginatorInterface     $paginator       Paginator
     * @param EntityManagerInterface $entityManager   Entity Manager
     */
    public function __construct(private readonly GenreRepository $genreRepository, private readonly TaskRepository $taskRepository, private readonly PaginatorInterface $paginator, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->genreRepository->queryAll(),
            $page,
            GenreRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Genre $genre Genre entity
     */
    public function save(Genre $genre): void
    {
        $this->genreRepository->save($genre);
    }

    /**
     * Delete entity.
     *
     * @param Genre $genre Genre entity
     */
    public function delete(Genre $genre): void
    {
        $this->entityManager->remove($genre);
        $this->entityManager->flush();
    }

    /**
     * Can Genre be deleted?
     *
     * @param Genre $genre Genre entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Genre $genre): bool
    {
        try {
            $result = $this->taskRepository->countByGenre($genre);

            return $result <= 0;
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }
}
