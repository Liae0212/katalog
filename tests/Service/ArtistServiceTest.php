<?php

/**
 * Artist service tests.
 */

namespace App\Tests\Service;

use App\Entity\Artist;
use App\Service\ArtistService;
use App\Service\ArtistServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ArtistServiceTest.
 *
 * Testy integracyjne serwisu ArtistService.
 */
class ArtistServiceTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    private ?ArtistServiceInterface $artistService;

    /**
     * Inicjalizacja kontenera i serwisów.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->artistService = $container->get(ArtistService::class);
    }

    /**
     * Test metody save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        $artist = new Artist();
        $artist->setName('Test Artist');

        $this->artistService->save($artist);

        $artistId = $artist->getId();
        $resultArtist = $this->entityManager->createQueryBuilder()
            ->select('artist')
            ->from(Artist::class, 'artist')
            ->where('artist.id = :id')
            ->setParameter(':id', $artistId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($artist, $resultArtist);
    }

    /**
     * Test metody delete.
     *
     * @throws OptimisticLockException|ORMException
     */
    public function testDelete(): void
    {
        $artist = new Artist();
        $artist->setName('Test Artist');
        $this->entityManager->persist($artist);
        $this->entityManager->flush();
        $artistId = $artist->getId();

        $this->artistService->delete($artist);

        $resultArtist = $this->entityManager->createQueryBuilder()
            ->select('artist')
            ->from(Artist::class, 'artist')
            ->where('artist.id = :id')
            ->setParameter(':id', $artistId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultArtist);
    }

    /**
     * Test metody getPaginatedList.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $countToCreate = 5;

        for ($i = 0; $i < $countToCreate; ++$i) {
            $artist = new Artist();
            $artist->setName('Artist #'.$i);
            $this->artistService->save($artist);
        }

        $result = $this->artistService->getPaginatedList($page);

        $this->assertEquals($countToCreate, $result->count());
    }

    /**
     * Test metody canBeDeleted.
     */
    public function testCanBeDeleted(): void
    {
        $artist = new Artist();
        $artist->setName('Deletable Artist');
        $this->entityManager->persist($artist);
        $this->entityManager->flush();

        $this->assertTrue($this->artistService->canBeDeleted($artist));
    }
}
