<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\GenreFixtures;
use App\Entity\Genre;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Test klasy GenreFixtures.
 */
class GenreFixturesTest extends TestCase
{
    /**
     * Testuje, czy metoda loadData tworzy 30 obiektów Genre,
     * które są poprawnie zapisywane z wymaganymi właściwościami.
     *
     * @return void
     */
    public function testLoadDataCreates30Genres(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);

        $persisted = [];
        $managerMock->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            if ($entity instanceof Genre) {
                $persisted[] = $entity;
            }
        });

        $managerMock->method('flush');

        $referenceRepoMock = $this->createMock(ReferenceRepository::class);

        $fixture = new GenreFixtures();
        $fixture->setReferenceRepository($referenceRepoMock);

        $reflection = new \ReflectionClass($fixture);

        $managerProp = $reflection->getProperty('manager');
        $managerProp->setAccessible(true);
        $managerProp->setValue($fixture, $managerMock);

        $fakerProp = $reflection->getProperty('faker');
        $fakerProp->setAccessible(true);
        $fakerProp->setValue($fixture, Factory::create());

        $fixture = $this->getMockBuilder(GenreFixtures::class)
            ->onlyMethods(['getReference'])
            ->getMock();

        $fixture->setReferenceRepository($referenceRepoMock);

        $fixture->method('getReference')->willReturnCallback(function (string $refName) {
            return new \stdClass();
        });

        $managerProp->setValue($fixture, $managerMock);
        $fakerProp->setValue($fixture, Factory::create());

        $fixture->loadData();

        $this->assertCount(30, $persisted);
        $this->assertContainsOnlyInstancesOf(Genre::class, $persisted);

        foreach ($persisted as $genre) {
            $this->assertNotEmpty($genre->getGenre());
            $this->assertInstanceOf(\DateTimeImmutable::class, $genre->getCreatedAt());
            $this->assertInstanceOf(\DateTimeImmutable::class, $genre->getUpdatedAt());
        }
    }
}
