<?php

/**
 * TaskTest.
 *
 * Unit test for the Task class.
 */

namespace App\Tests\Entity;

use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\Genre;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe encji Task.
 */
class TaskTest extends TestCase
{
    /**
     * Testuje, że początkowo id jest null.
     */
    public function testGetIdInitiallyNull(): void
    {
        $task = new Task();
        $this->assertNull($task->getId());
    }

    /**
     * Testuje setter i getter dla daty utworzenia.
     */
    public function testSetAndGetCreatedAt(): void
    {
        $task = new Task();
        $date = new \DateTimeImmutable();
        $task->setCreatedAt($date);
        $this->assertSame($date, $task->getCreatedAt());
    }

    /**
     * Testuje setter i getter dla daty aktualizacji.
     */
    public function testSetAndGetUpdatedAt(): void
    {
        $task = new Task();
        $date = new \DateTimeImmutable();
        $task->setUpdatedAt($date);
        $this->assertSame($date, $task->getUpdatedAt());
    }

    /**
     * Testuje setter i getter dla tytułu zadania.
     */
    public function testSetAndGetTitle(): void
    {
        $task = new Task();
        $title = 'Test Task';
        $task->setTitle($title);
        $this->assertSame($title, $task->getTitle());
    }

    /**
     * Testuje setter i getter dla kategorii.
     */
    public function testSetAndGetCategory(): void
    {
        $task = new Task();
        $category = new Category();
        $task->setCategory($category);
        $this->assertSame($category, $task->getCategory());
    }

    /**
     * Testuje dodawanie i usuwanie tagów.
     */
    public function testAddAndRemoveTags(): void
    {
        $task = new Task();
        $tag1 = new Tag();
        $tag2 = new Tag();

        $this->assertCount(0, $task->getTags());

        $task->addTag($tag1);
        $this->assertCount(1, $task->getTags());
        $this->assertTrue($task->getTags()->contains($tag1));

        $task->addTag($tag2);
        $this->assertCount(2, $task->getTags());

        $task->addTag($tag1);
        $this->assertCount(2, $task->getTags());

        $task->removeTag($tag1);
        $this->assertCount(1, $task->getTags());
        $this->assertFalse($task->getTags()->contains($tag1));
    }

    /**
     * Testuje setter i getter dla artysty.
     */
    public function testSetAndGetArtist(): void
    {
        $task = new Task();
        $artist = new Artist();
        $task->setArtist($artist);
        $this->assertSame($artist, $task->getArtist());
    }

    /**
     * Testuje setter i getter dla gatunku.
     */
    public function testSetAndGetGenre(): void
    {
        $task = new Task();
        $genre = new Genre();
        $task->setGenre($genre);
        $this->assertSame($genre, $task->getGenre());
    }

    /**
     * Testuje setter i getter dla użytkownika.
     */
    public function testSetAndGetUsers(): void
    {
        $task = new Task();
        $user = new User();
        $task->setUsers($user);
        $this->assertSame($user, $task->getUsers());
    }

    /**
     * Testuje, że kolekcja komentarzy jest początkowo pusta.
     */
    public function testCommentsCollectionIsInitiallyEmpty(): void
    {
        $task = new Task();
        $this->assertCount(0, $task->getComments());
    }
}
