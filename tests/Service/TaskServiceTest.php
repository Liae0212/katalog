<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Tag;
use App\Repository\TaskRepository;
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
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * Task service.
     *
     * @var TaskService
     */
    private TaskService $taskService;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->taskService = $container->get(TaskService::class);
    }

    /**
     * Create a User entity.
     *
     * @return User
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
     *
     * @return Category
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
     *
     * @param User     $user
     * @param Category $category
     *
     * @return Task
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
     *
     * @param string $title
     *
     * @return Tag
     */
    private function createTag(string $title = 'Tag 1'): Tag
    {
        $tag = new Tag();
        $tag->setTitle($title);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }

    /**
     * Test saving a Task.
     *
     * @return void
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
     *
     * @return void
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
     * Tear down the test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->clear();
    }
}
