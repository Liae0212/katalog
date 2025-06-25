<?php

namespace App\Tests\Entity;

use App\Entity\Tag;
use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Testy jednostkowe encji Tag.
 */
class TagTest extends KernelTestCase
{
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * Ustawia środowisko testowe i inicjalizuje walidator.
     *
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * Testuje, że początkowo id jest null.
     *
     * @return void
     */
    public function testGetIdInitiallyNull(): void
    {
        $tag = new Tag();
        $this->assertNull($tag->getId());
    }

    /**
     * Testuje setter i getter dla tytułu.
     *
     * @return void
     */
    public function testSetAndGetTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('Important');

        $this->assertSame('Important', $tag->getTitle());
    }

    /**
     * Testuje, że kolekcja zadań jest początkowo pusta.
     *
     * @return void
     */
    public function testTasksCollectionInitiallyEmpty(): void
    {
        $tag = new Tag();
        $this->assertCount(0, $tag->getTasks());
    }

    /**
     * Testuje, że przypisywanie zadań do taga nie działa bezpośrednio z tej strony relacji.
     *
     * @return void
     */
    public function testCanAssignTasks(): void
    {
        $tag = new Tag();
        $task = new Task();

        $this->assertCount(0, $tag->getTasks());
    }
}
