<?php

/**
 * TagRepositoryTest.
 *
 * Unit tests for the TagRepository form.
 */

namespace App\Tests\Repository;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagRepositoryTest.
 *
 * Testy integracyjne repozytorium TagRepository.
 */
class TagRepositoryTest extends KernelTestCase
{
    private ?TagRepository $repository;

    /**
     * Inicjalizacja testu, boot kernel, pobranie repozytorium i wyczyszczenie tabeli.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->repository = $container->get(TagRepository::class);

        $em = $container->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\Tag t')->execute();
    }

    /**
     * Test zapisu i wyszukiwania encji Tag.
     */
    public function testSaveAndFind(): void
    {
        $tag = new Tag();
        $tag->setTitle('Symfony');

        $this->repository->save($tag);

        $this->assertNotNull($tag->getId());

        $found = $this->repository->find($tag->getId());
        $this->assertInstanceOf(Tag::class, $found);
        $this->assertSame('Symfony', $found->getTitle());
    }

    /**
     * Test usuwania encji Tag.
     */
    public function testDelete(): void
    {
        $tag = new Tag();
        $tag->setTitle('DeleteMe');
        $this->repository->save($tag);

        $id = $tag->getId();
        $this->assertNotNull($id);

        $this->repository->delete($tag);

        $deleted = $this->repository->find($id);
        $this->assertNull($deleted);
    }

    /**
     * Test metody queryAll - zwraca QueryBuilder.
     */
    public function testQueryAllReturnsQueryBuilder(): void
    {
        $qb = $this->repository->queryAll();

        $this->assertInstanceOf(\Doctrine\ORM\QueryBuilder::class, $qb);

        $dql = $qb->getDQL();
        $this->assertStringContainsString('tag', $dql);
    }
}
