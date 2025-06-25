<?php

/**
 * GenreServiceTest.
 *
 * Unit tests for the GenreService class.
 */

namespace App\Tests\Service;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use App\Repository\TaskRepository;
use App\Service\GenreService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GenreServiceTest.
 */
class GenreServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Genre service.
     */
    private ?GenreService $genreService;

    /**
     * Setup before each test.
     */
    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->genreService = $container->get(GenreService::class);
    }

    /**
     * Test saving a Genre entity.
     */
    public function testSave(): void
    {
        $genre = new Genre();
        $genre->setGenre('Test Genre Save');

        $this->genreService->save($genre);

        $found = $this->entityManager->getRepository(Genre::class)->find($genre->getId());

        $this->assertNotNull($found);
        $this->assertEquals($genre->getGenre(), $found->getGenre());
    }

    /**
     * Test deleting a Genre entity.
     */
    public function testDelete(): void
    {
        $genre = new Genre();
        $genre->setGenre('Test Genre Delete');
        $this->entityManager->persist($genre);
        $this->entityManager->flush();
        $id = $genre->getId();

        $this->genreService->delete($genre);

        $deleted = $this->entityManager->getRepository(Genre::class)->find($id);

        $this->assertNull($deleted);
    }

    /**
     * Test getting paginated list.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $genre = new Genre();
            $genre->setGenre('Genre #'.$i);
            $this->genreService->save($genre);
        }

        $pagination = $this->genreService->getPaginatedList($page);

        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertGreaterThanOrEqual($count, $pagination->getTotalItemCount());
    }

    /**
     * Test canBeDeleted returns true when genre has no tasks.
     */
    public function testCanBeDeletedTrue(): void
    {
        /** @var TaskRepository&MockObject $taskRepoMock */
        $taskRepoMock = $this->createMock(TaskRepository::class);
        $taskRepoMock->method('countByGenre')->willReturn(0);

        $genre = new Genre();
        $genre->setGenre('Deletable Genre');

        $service = new GenreService(
            static::getContainer()->get(GenreRepository::class),
            $taskRepoMock,
            static::getContainer()->get(PaginatorInterface::class),
            $this->entityManager
        );

        $this->assertTrue($service->canBeDeleted($genre));
    }

    /**
     * Test canBeDeleted returns false when genre has tasks.
     */
    public function testCanBeDeletedFalse(): void
    {
        /** @var TaskRepository&MockObject $taskRepoMock */
        $taskRepoMock = $this->createMock(TaskRepository::class);
        $taskRepoMock->method('countByGenre')->willReturn(3);

        $genre = new Genre();
        $genre->setGenre('Non-Deletable Genre');

        $service = new GenreService(
            static::getContainer()->get(GenreRepository::class),
            $taskRepoMock,
            static::getContainer()->get(PaginatorInterface::class),
            $this->entityManager
        );

        $this->assertFalse($service->canBeDeleted($genre));
    }
}
