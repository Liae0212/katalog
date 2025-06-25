<?php

/**
 * CategoryTest.
 *
 * Unit test for the Category class.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe klasy Category.
 */
class CategoryTest extends TestCase
{
    /**
     * Testuje gettery i settery dla pól:
     * createdAt, updatedAt, title, slug.
     */
    public function testGettersAndSetters(): void
    {
        $category = new Category();

        $createdAt = new \DateTimeImmutable('2024-01-01 12:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-02 12:00:00');
        $title = 'My Category';
        $slug = 'my-category';

        $category->setCreatedAt($createdAt);
        $category->setUpdatedAt($updatedAt);
        $category->setTitle($title);
        $category->setSlug($slug);

        $this->assertSame($createdAt, $category->getCreatedAt());
        $this->assertSame($updatedAt, $category->getUpdatedAt());
        $this->assertSame($title, $category->getTitle());
        $this->assertSame($slug, $category->getSlug());
    }

    /**
     * Testuje, że kolekcja zadań (tasks) jest pusta na początku.
     */
    public function testTasksCollectionInitiallyEmpty(): void
    {
        $category = new Category();
        $tasks = $category->getTasks();

        $this->assertCount(0, $tasks);
    }

    /**
     * Testuje dodanie i usunięcie zadania (Task)
     * oraz poprawność relacji dwukierunkowej.
     */
    public function testAddAndRemoveTask(): void
    {
        $category = new Category();
        $task = new Task();

        $category->getTasks()->add($task);
        $task->setCategory($category);

        $this->assertTrue($category->getTasks()->contains($task));
        $this->assertSame($category, $task->getCategory());

        $category->getTasks()->removeElement($task);
        $task->setCategory(null);

        $this->assertFalse($category->getTasks()->contains($task));
        $this->assertNull($task->getCategory());
    }
}
