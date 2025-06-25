<?php

/**
 * CategoryRepositoryTest.
 *
 * Unit tests for the CategoryRepository form.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Testy dla klasy CategoryRepository.
 */
class CategoryRepositoryTest extends TestCase
{
    /**
     * @var ManagerRegistry mock ManagerRegistry
     */
    private ManagerRegistry $registryMock;

    /**
     * @var EntityManagerInterface mock EntityManager
     */
    private EntityManagerInterface $entityManagerMock;

    /**
     * @var QueryBuilder mock QueryBuilder
     */
    private QueryBuilder $queryBuilderMock;

    /**
     * @var CategoryRepository testowany repository
     */
    private CategoryRepository $repository;

    /**
     * Przygotowuje środowisko testowe, tworzy mocki i instancję repository.
     */
    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(ManagerRegistry::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);

        $this->entityManagerMock->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->registryMock->method('getManagerForClass')
            ->with(Category::class)
            ->willReturn($this->entityManagerMock);

        $this->repository = new CategoryRepository($this->registryMock);

        $refObject = new \ReflectionObject($this->repository);
        $emProperty = $refObject->getParentClass()->getProperty('_em');
        $emProperty->setAccessible(true);
        $emProperty->setValue($this->repository, $this->entityManagerMock);
    }

    /**
     * Testuje metodę save - czy wywołuje persist i flush na EntityManager.
     */
    public function testSave(): void
    {
        $category = new Category();

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($category);

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->repository->save($category);
    }

    /**
     * Testuje metodę delete - czy wywołuje remove i flush na EntityManager.
     */
    public function testDelete(): void
    {
        $category = new Category();

        $this->entityManagerMock->expects($this->once())
            ->method('remove')
            ->with($category);

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->repository->delete($category);
    }
}
