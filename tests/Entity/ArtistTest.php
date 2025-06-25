<?php

namespace App\Tests\Entity;

use App\Entity\Artist;
use App\Entity\Task;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe klasy Artist.
 */
class ArtistTest extends TestCase
{
    /**
     * Testuje gettery i settery dla pól:
     * createdAt, updatedAt, name.
     *
     * @return void
     */
    public function testGettersAndSetters(): void
    {
        $artist = new Artist();

        $dateCreated = new DateTimeImmutable('2023-01-01 10:00:00');
        $dateUpdated = new DateTimeImmutable('2023-01-02 15:00:00');
        $name = 'John Doe';

        $artist->setCreatedAt($dateCreated);
        $artist->setUpdatedAt($dateUpdated);
        $artist->setName($name);

        $this->assertSame($dateCreated, $artist->getCreatedAt());
        $this->assertSame($dateUpdated, $artist->getUpdatedAt());
        $this->assertSame($name, $artist->getName());
    }

    /**
     * Testuje dodanie zadania (Task) do artysty (Artist)
     * oraz poprawne przypisanie relacji dwukierunkowej.
     *
     * @return void
     */
    public function testAddTask(): void
    {
        $artist = new Artist();
        $task = new Task();

        $artist->addTask($task);

        $this->assertTrue($artist->getTasks()->contains($task));
        $this->assertSame($artist, $task->getArtist());
    }

    /**
     * Testuje, że dodanie tego samego zadania
     * nie powoduje duplikatów w kolekcji.
     *
     * @return void
     */
    public function testAddTaskDoesNotAddDuplicate(): void
    {
        $artist = new Artist();
        $task = new Task();

        $artist->addTask($task);
        $artist->addTask($task);

        $tasks = $artist->getTasks();
        $this->assertCount(1, $tasks);
    }

    /**
     * Testuje usunięcie zadania (Task) z artysty (Artist)
     * oraz poprawne zerowanie relacji dwukierunkowej.
     *
     * @return void
     */
    public function testRemoveTask(): void
    {
        $artist = new Artist();
        $task = new Task();

        $artist->addTask($task);
        $artist->removeTask($task);

        $this->assertFalse($artist->getTasks()->contains($task));
        $this->assertNull($task->getArtist());
    }

    /**
     * Testuje, że usunięcie zadania,
     * które nie zostało dodane, nie powoduje błędów
     * i nie zmienia stanu obiektu.
     *
     * @return void
     */
    public function testRemoveTaskDoesNothingIfNotPresent(): void
    {
        $artist = new Artist();
        $task = new Task();

        $artist->removeTask($task);

        $this->assertFalse($artist->getTasks()->contains($task));
        $this->assertNull($task->getArtist());
    }
}
