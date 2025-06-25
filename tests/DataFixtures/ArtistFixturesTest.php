<?php

/**
 * DataFixturesTest.
 *
 * Unit test for the DataFixtures class.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\ArtistFixtures;
use App\Entity\Artist;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Faker\Factory;

/**
 * Test klasy ArtistFixtures.
 */
class ArtistFixturesTest extends TestCase
{
    /**
     * Testuje, czy metoda loadData tworzy dokładnie 30 obiektów Artist
     * i czy są one poprawnie zapisywane przez ObjectManager.
     */
    public function testLoadDataCreates30Artists(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);

        $persistedEntities = [];
        $managerMock->method('persist')->willReturnCallback(function ($entity) use (&$persistedEntities) {
            if ($entity instanceof Artist) {
                $persistedEntities[] = $entity;
            }
        });

        $managerMock->expects($this->atLeastOnce())->method('flush');

        $referenceRepo = $this->createMock(ReferenceRepository::class);

        $fixture = new ArtistFixtures();

        $fixture->setReferenceRepository($referenceRepo);

        $reflection = new \ReflectionClass($fixture);

        $managerProp = $reflection->getProperty('manager');
        $managerProp->setAccessible(true);
        $managerProp->setValue($fixture, $managerMock);

        $fakerProp = $reflection->getProperty('faker');
        $fakerProp->setAccessible(true);
        $fakerProp->setValue($fixture, Factory::create());

        $fixture->loadData();

        $this->assertCount(30, $persistedEntities);
        $this->assertContainsOnlyInstancesOf(Artist::class, $persistedEntities);
    }
}
