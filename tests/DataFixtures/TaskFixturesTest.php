<?php

/**
 * TaskFixturesTest.
 *
 * Unit test for the TaskFixtures class.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\TaskFixtures;
use App\Entity\Task;
use App\Entity\Category;
use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\User;
use App\Entity\Tag;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use PHPUnit\Framework\TestCase;

/**
 * Test klasy TaskFixtures.
 */
class TaskFixturesTest extends TestCase
{
    /**
     * Testuje metodę load, która powinna wywołać loadData
     * i utworzyć 100 obiektów Task z poprawnie ustawionym tytułem.
     */
    public function testLoadCallsLoadData(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);

        $persisted = [];
        $managerMock->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            if ($entity instanceof Task) {
                $persisted[] = $entity;
            }
        });

        $managerMock->expects($this->atLeastOnce())->method('flush');

        $referenceRepoMock = $this->createMock(ReferenceRepository::class);

        $category = $this->createMock(Category::class);
        $artist = $this->createMock(Artist::class);
        $genre = $this->createMock(Genre::class);
        $user = $this->createMock(User::class);
        $tag = $this->createMock(Tag::class);

        $fixture = $this->getMockBuilder(TaskFixtures::class)
            ->onlyMethods(['getRandomReference', 'getRandomReferenceList'])
            ->getMock();

        $fixture->setReferenceRepository($referenceRepoMock);

        $fixture->method('getRandomReference')->willReturnMap([
            ['categories', Category::class, $category],
            ['artists', Artist::class, $artist],
            ['genres', Genre::class, $genre],
            ['users', User::class, $user],
        ]);

        $fixture->method('getRandomReferenceList')->willReturn(array_fill(0, 3, $tag));

        $fixture->load($managerMock);

        $this->assertCount(100, $persisted);
        foreach ($persisted as $task) {
            $this->assertInstanceOf(Task::class, $task);
            $this->assertNotEmpty($task->getTitle());
        }
    }

    /**
     * Testuje, czy metoda getDependencies zwraca poprawną listę zależności.
     */
    public function testGetDependenciesReturnsCorrectClasses(): void
    {
        $fixture = new TaskFixtures();

        $expectedDependencies = [
            \App\DataFixtures\CategoryFixtures::class,
            \App\DataFixtures\TagFixtures::class,
            \App\DataFixtures\ArtistFixtures::class,
            \App\DataFixtures\GenreFixtures::class,
            \App\DataFixtures\UserFixtures::class,
        ];

        $this->assertSame($expectedDependencies, $fixture->getDependencies());
    }
}
