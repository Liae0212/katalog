<?php

/**
 * TagServiceTest.
 *
 * Unit tests for the TagService class.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Service\TagService;
use App\Service\TagServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Tag service.
     */
    private ?TagServiceInterface $tagService;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->tagService = $container->get(TagService::class);
    }

    /**
     * Test save.
     */
    public function testSave(): void
    {
        $tag = new Tag();
        $tag->setTitle('Test Tag');

        $this->tagService->save($tag);

        $savedTag = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Tag::class, 't')
            ->where('t.id = :id')
            ->setParameter(':id', $tag->getId(), Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($tag, $savedTag);
    }

    /**
     * Test delete.
     */
    public function testDelete(): void
    {
        $tag = new Tag();
        $tag->setTitle('To Be Deleted');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        $id = $tag->getId();

        $this->tagService->delete($tag);

        $deletedTag = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Tag::class, 't')
            ->where('t.id = :id')
            ->setParameter(':id', $id, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($deletedTag);
    }

    /**
     * Test findOneById.
     *
     * @throws NonUniqueResultException
     */
    public function testFindOneById(): void
    {
        $tag = new Tag();
        $tag->setTitle('Tag By ID');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $found = $this->tagService->findOneById($tag->getId());

        $this->assertEquals($tag, $found);
    }

    /**
     * Test findOneByTitle.
     */
    public function testFindOneByTitle(): void
    {
        $title = 'Unique Title';
        $tag = new Tag();
        $tag->setTitle($title);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $found = $this->tagService->findOneByTitle($title);

        $this->assertEquals($tag, $found);
    }

    /**
     * Test getPaginatedList.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $count = 3;
        for ($i = 0; $i < $count; ++$i) {
            $tag = new Tag();
            $tag->setTitle('Tag #'.$i);
            $this->tagService->save($tag);
        }

        $result = $this->tagService->getPaginatedList($page);

        $this->assertGreaterThanOrEqual($count, $result->count());
    }
}
