<?php

/**
 * TaskServiceTest.
 *
 * Unit tests for the TaskService class.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Tag;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TaskServiceTest.
 */
class TaskServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Task service.
     */
    private TaskService $taskService;

    /**
     * Test saving a Task.
     */
    public function testSave(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();

        $task = new Task();
        $task->setTitle('Save Test');
        $task->setCategory($category);
        $task->setUsers($user);

        $this->taskService->save($task);

        $this->assertNotNull($task->getId());
    }

    /**
     * Test deleting a Task.
     */
    public function testDelete(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();
        $task = $this->createTask($user, $category);
        $taskId = $task->getId();

        $this->taskService->delete($task);
        $deletedTask = $this->entityManager->getRepository(Task::class)->find($taskId);

        $this->assertNull($deletedTask);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->taskService = $container->get(TaskService::class);
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->clear();
    }

    /**
     * Create a User entity.
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test_user@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Create a Category entity.
     */
    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Test Category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    /**
     * Create a Task entity linked to a User and a Category.
     */
    private function createTask(User $user, Category $category): Task
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setCategory($category);
        $task->setUsers($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    /**
     * Create a Tag entity.
     */
    private function createTag(string $title = 'Tag 1'): Tag
    {
        $tag = new Tag();
        $tag->setTitle($title);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }
}
