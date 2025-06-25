<?php

/**
 * CommentFixturesTest.
 *
 * Unit test for the CommentFixtures class.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\CommentFixtures;
use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Test klasy CommentFixtures.
 */
class CommentFixturesTest extends TestCase
{
    /**
     * Testuje, czy metoda loadData tworzy dokładnie 20 obiektów Comment,
     * które są poprawnie zapisywane i mają wymagane właściwości.
     */
    public function testLoadDataCreates20Comments(): void
    {
        $managerMock = $this->createMock(ObjectManager::class);
        $referenceRepoMock = $this->createMock(ReferenceRepository::class);

        $persisted = [];
        $managerMock->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            if ($entity instanceof Comment) {
                $persisted[] = $entity;
            }
        });

        $managerMock->expects($this->atLeastOnce())->method('flush');

        $task = $this->createMock(Task::class);
        $user = $this->createMock(User::class);

        $referenceRepoMock->method('getIdentitiesByClass')->willReturn([
            Task::class => [
                'tasks_0' => ['id' => 1],
                'tasks_1' => ['id' => 2],
            ],
            User::class => [
                'users_0' => ['id' => 1],
                'users_1' => ['id' => 2],
            ],
        ]);

        $fixture = new CommentFixtures();
        $fixture->setReferenceRepository($referenceRepoMock);

        $reflection = new \ReflectionClass($fixture);

        $managerProp = $reflection->getProperty('manager');
        $managerProp->setAccessible(true);
        $managerProp->setValue($fixture, $managerMock);

        $fakerProp = $reflection->getProperty('faker');
        $fakerProp->setAccessible(true);
        $fakerProp->setValue($fixture, Factory::create());

        $fixture = $this->getMockBuilder(CommentFixtures::class)
            ->onlyMethods(['getReference'])
            ->getMock();

        $fixture->setReferenceRepository($referenceRepoMock);

        $fixture->method('getReference')->willReturnCallback(function (string $refName) use ($task, $user) {
            return str_starts_with($refName, 'tasks_') ? $task : $user;
        });

        $managerProp->setValue($fixture, $managerMock);
        $fakerProp->setValue($fixture, Factory::create());

        $fixture->loadData();

        $this->assertCount(20, $persisted);
        $this->assertContainsOnlyInstancesOf(Comment::class, $persisted);
        foreach ($persisted as $comment) {
            $this->assertNotEmpty($comment->getNick());
            $this->assertNotEmpty($comment->getContent());
            $this->assertInstanceOf(Task::class, $comment->getTask());
            $this->assertInstanceOf(User::class, $comment->getAuthor());
        }
    }

    /**
     * Testuje metodę getDependencies, czy zwraca poprawne zależności.
     */
    public function testGetDependencies(): void
    {
        $fixture = new CommentFixtures();
        $this->assertSame(
            [\App\DataFixtures\TaskFixtures::class, \App\DataFixtures\UserFixtures::class],
            $fixture->getDependencies()
        );
    }
}
