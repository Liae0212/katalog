<?php

namespace App\Tests\Repository;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Testy dla klasy GenreRepository.
 */
class GenreRepositoryTest extends TestCase
{
    /**
     * @var ManagerRegistry Mock ManagerRegistry.
     */
    private ManagerRegistry $registryMock;

    /**
     * @var EntityManagerInterface Mock EntityManager.
     */
    private EntityManagerInterface $entityManagerMock;

    /**
     * @var QueryBuilder Mock QueryBuilder.
     */
    private QueryBuilder $queryBuilderMock;

    /**
     * @var GenreRepository Testowany repository.
     */
    private GenreRepository $repository;

    /**
     * Przygotowuje środowisko testowe, tworzy mocki i instancję repository.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(ManagerRegistry::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);

        $this->queryBuilderMock->method('select')->willReturnSelf();
        $this->queryBuilderMock->method('orderBy')->willReturnSelf();

        $this->entityManagerMock->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->registryMock->method('getManagerForClass')
            ->with(Genre::class)
            ->willReturn($this->entityManagerMock);

        $this->repository = new GenreRepository($this->registryMock);

        $refObject = new \ReflectionObject($this->repository);
        $emProperty = $refObject->getParentClass()->getProperty('_em');
        $emProperty->setAccessible(true);
        $emProperty->setValue($this->repository, $this->entityManagerMock);
    }

    /**
     * Testuje metodę queryAll, czy tworzy prawidłowe zapytanie z select i orderBy.
     *
     * @return void
     */
    public function testQueryAll(): void
    {
        $this->repository = $this->getMockBuilder(GenreRepository::class)
            ->setConstructorArgs([$this->registryMock])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->repository->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock->expects($this->once())
            ->method('select')
            ->with('partial genre.{id, createdAt, updatedAt, genre}')
            ->willReturnSelf();

        $this->queryBuilderMock->expects($this->once())
            ->method('orderBy')
            ->with('genre.updatedAt', 'DESC')
            ->willReturnSelf();

        $result = $this->repository->queryAll();

        $this->assertSame($this->queryBuilderMock, $result);
    }

    /**
     * Testuje metodę save, czy wywołuje persist i flush na EntityManager.
     *
     * @return void
     */
    public function testSave(): void
    {
        $genre = new Genre();

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($genre);

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->repository->save($genre);
    }

    /**
     * Testuje metodę delete, czy wywołuje remove i flush na EntityManager.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $genre = new Genre();

        $this->entityManagerMock->expects($this->once())
            ->method('remove')
            ->with($genre);

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->repository->delete($genre);
    }
}
