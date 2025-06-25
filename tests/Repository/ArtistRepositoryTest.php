<?php

namespace App\Tests\Repository;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testy dla klasy ArtistRepository.
 */
class ArtistRepositoryTest extends TestCase
{
    /**
     * @var ManagerRegistry&MockObject Mock ManagerRegistry.
     */
    private ManagerRegistry&MockObject $registryMock;

    /**
     * @var EntityManagerInterface&MockObject Mock EntityManager.
     */
    private EntityManagerInterface&MockObject $entityManagerMock;

    /**
     * @var ArtistRepository Testowany repository.
     */
    private ArtistRepository $repository;

    /**
     * @var QueryBuilder&MockObject Mock QueryBuilder.
     */
    private QueryBuilder&MockObject $queryBuilderMock;

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

        $this->registryMock->method('getManagerForClass')
            ->with(Artist::class)
            ->willReturn($this->entityManagerMock);

        $classMetadata = new ClassMetadata(Artist::class);

        $this->repository = $this->getMockBuilder(ArtistRepository::class)
            ->setConstructorArgs([$this->registryMock])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->repository->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $refObject = new \ReflectionObject($this->repository);
        $emProperty = $refObject->getParentClass()->getProperty('_em');
        $emProperty->setAccessible(true);
        $emProperty->setValue($this->repository, $this->entityManagerMock);

        $classMetadataProperty = $refObject->getParentClass()->getProperty('_class');
        $classMetadataProperty->setAccessible(true);
        $classMetadataProperty->setValue($this->repository, $classMetadata);
    }

    /**
     * Testuje metodę queryAll - czy buduje zapytanie z odpowiednim select i orderBy.
     *
     * @return void
     */
    public function testQueryAll(): void
    {
        $this->queryBuilderMock->expects($this->once())
            ->method('select')
            ->with('partial artist.{id, createdAt, updatedAt, name}')
            ->willReturnSelf();

        $this->queryBuilderMock->expects($this->once())
            ->method('orderBy')
            ->with('artist.updatedAt', 'DESC')
            ->willReturnSelf();

        $result = $this->repository->queryAll();

        $this->assertSame($this->queryBuilderMock, $result);
    }

    /**
     * Testuje metodę save - czy wywołuje persist i flush na EntityManager.
     *
     * @return void
     */
    public function testSave(): void
    {
        $artist = new Artist();

        $this->entityManagerMock->expects($this->once())->method('persist')->with($artist);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->repository->save($artist);
    }

    /**
     * Testuje metodę delete - czy wywołuje remove i flush na EntityManager.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $artist = new Artist();

        $this->entityManagerMock->expects($this->once())->method('remove')->with($artist);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->repository->delete($artist);
    }
}
