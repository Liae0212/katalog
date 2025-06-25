<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe klasy Comment.
 */
class CommentTest extends TestCase
{
    /**
     * Testuje gettery i settery dla pól:
     * content oraz nick.
     *
     * @return void
     */
    public function testGettersAndSetters(): void
    {
        $comment = new Comment();

        $content = 'This is a comment content.';
        $nick = 'JohnDoe';

        $comment->setContent($content);
        $comment->setNick($nick);

        $this->assertSame($content, $comment->getContent());
        $this->assertSame($nick, $comment->getNick());
    }

    /**
     * Testuje setter i getter dla pola author (User|null).
     *
     * @return void
     */
    public function testAuthorSetterAndGetter(): void
    {
        $comment = new Comment();
        $user = $this->createMock(User::class);

        $comment->setAuthor($user);
        $this->assertSame($user, $comment->getAuthor());

        $comment->setAuthor(null);
        $this->assertNull($comment->getAuthor());
    }

    /**
     * Testuje setter i getter dla pola task (Task|null).
     *
     * @return void
     */
    public function testTaskSetterAndGetter(): void
    {
        $comment = new Comment();
        $task = $this->createMock(Task::class);

        $comment->setTask($task);
        $this->assertSame($task, $comment->getTask());

        $comment->setTask(null);
        $this->assertNull($comment->getTask());
    }

    /**
     * Testuje, że początkowo id jest null.
     *
     * @return void
     */
    public function testIdInitiallyNull(): void
    {
        $comment = new Comment();
        $this->assertNull($comment->getId());
    }
}
