<?php

namespace App\Tests\Entity;

use App\Entity\Genre;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe klasy Genre.
 */
class GenreTest extends TestCase
{
    /**
     * Testuje gettery i settery dla pól:
     * createdAt, updatedAt oraz genre.
     *
     * @return void
     */
    public function testGettersAndSetters(): void
    {
        $genre = new Genre();

        $createdAt = new \DateTimeImmutable('2023-01-01 12:00:00');
        $updatedAt = new \DateTimeImmutable('2023-01-02 13:00:00');
        $name = 'Rock';

        $genre->setCreatedAt($createdAt);
        $genre->setUpdatedAt($updatedAt);
        $genre->setGenre($name);

        $this->assertSame($createdAt, $genre->getCreatedAt());
        $this->assertSame($updatedAt, $genre->getUpdatedAt());
        $this->assertSame($name, $genre->getGenre());
    }

    /**
     * Testuje, że początkowo id jest null.
     *
     * @return void
     */
    public function testIdInitiallyNull(): void
    {
        $genre = new Genre();
        $this->assertNull($genre->getId());
    }

    /**
     * Testuje, że kolekcja zadań jest początkowo pusta.
     *
     * @return void
     */
    public function testTasksCollectionInitiallyEmpty(): void
    {
        $genre = new Genre();
        $this->assertCount(0, $genre->getTasks());
    }

    /**
     * Testuje dodanie zadania do kolekcji i
     * wywołanie metody setGenre na obiekcie Task.
     *
     * @return void
     */
    public function testAddTask(): void
    {
        $genre = new Genre();
        $task = $this->createMock(Task::class);

        $task->expects($this->once())
            ->method('setGenre')
            ->with($genre);

        $genre->addTask($task);

        $this->assertCount(1, $genre->getTasks());
        $this->assertTrue($genre->getTasks()->contains($task));
    }

    /**
     * Testuje, że dodanie tego samego zadania
     * nie powoduje duplikatu i nie wywołuje setGenre ponownie.
     *
     * @return void
     */
    public function testAddTaskDoesNotDuplicate(): void
    {
        $genre = new Genre();
        $task = $this->createMock(Task::class);

        $task->expects($this->once())
            ->method('setGenre')
            ->with($genre);

        $genre->addTask($task);
        $genre->addTask($task);

        $this->assertCount(1, $genre->getTasks());
    }
}
