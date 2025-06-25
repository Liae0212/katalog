<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use App\Service\CommentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * Comment service.
     *
     * @var CommentService
     */
    private CommentService $commentService;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->commentService = static::getContainer()->get(CommentService::class);
    }

    /**
     * Create a user entity.
     *
     * @return User
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Create a category entity.
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
     * Create a task entity linked to a category.
     *
     * @param Category $category
     * @return Task
     */
    private function createTask(Category $category): Task
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setCategory($category);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    /**
     * Test saving a comment.
     *
     * @return void
     */
    public function testSave(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();
        $task = $this->createTask($category);

        $comment = new Comment();
        $comment->setContent('Test comment content');
        $comment->setNick('Tester');
        $comment->setAuthor($user);
        $comment->setTask($task);

        $this->commentService->save($comment);

        $this->assertNotNull($comment->getId());
        $this->assertEquals('Test comment content', $comment->getContent());
        $this->assertEquals($user->getId(), $comment->getAuthor()->getId());
    }

    /**
     * Test deleting a comment.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();
        $task = $this->createTask($category);

        $comment = new Comment();
        $comment->setContent('Comment to delete');
        $comment->setNick('Tester');
        $comment->setAuthor($user);
        $comment->setTask($task);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $id = $comment->getId();

        $this->commentService->delete($comment);

        $deletedComment = $this->commentService->findOneById($id);
        $this->assertNull($deletedComment);
    }

    /**
     * Test finding a comment by ID.
     *
     * @return void
     */
    public function testFindOneById(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();
        $task = $this->createTask($category);

        $comment = new Comment();
        $comment->setContent('Find me');
        $comment->setNick('Tester');
        $comment->setAuthor($user);
        $comment->setTask($task);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $found = $this->commentService->findOneById($comment->getId());

        $this->assertInstanceOf(Comment::class, $found);
        $this->assertEquals('Find me', $found->getContent());
    }

    /**
     * Test getting a paginated list of comments.
     *
     * @return void
     */
    public function testGetPaginatedList(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();
        $task = $this->createTask($category);

        for ($i = 0; $i < 5; $i++) {
            $comment = new Comment();
            $comment->setContent('Comment ' . $i);
            $comment->setNick('Tester');
            $comment->setAuthor($user);
            $comment->setTask($task);
            $this->entityManager->persist($comment);
        }
        $this->entityManager->flush();

        $page = 1;
        $limit = 5;

        $paginatedComments = $this->commentService->getPaginatedList($page, $limit);

        $this->assertCount($limit, $paginatedComments->getItems());
        $this->assertEquals(5, $paginatedComments->getTotalItemCount());
    }
}
