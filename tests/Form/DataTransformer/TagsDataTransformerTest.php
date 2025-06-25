<?php

/**
 * TagsDataTransformerTest.
 *
 * Unit test for the TagsDataTransformer class.
 */

namespace App\Tests\Form\DataTransformer;

use App\Entity\Tag;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Service\TagServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe klasy TagsDataTransformer.
 */
class TagsDataTransformerTest extends TestCase
{
    private TagsDataTransformer $transformer;

    /**
     * @var MockObject&TagServiceInterface
     */
    private MockObject $tagService;

    /**
     * Inicjalizacja testu.
     */
    protected function setUp(): void
    {
        $this->tagService = $this->createMock(TagServiceInterface::class);
        $this->transformer = new TagsDataTransformer($this->tagService);
    }

    /**
     * Testuje, że transformacja pustej kolekcji zwraca pusty string.
     */
    public function testTransformReturnsEmptyStringForEmptyCollection(): void
    {
        $collection = new ArrayCollection();

        $result = $this->transformer->transform($collection);

        $this->assertSame('', $result);
    }

    /**
     * Testuje, że transformacja kolekcji tagów zwraca ciąg tytułów rozdzielonych przecinkiem.
     */
    public function testTransformReturnsCommaSeparatedTagTitles(): void
    {
        $tag1 = new Tag();
        $tag1->setTitle('php');
        $tag2 = new Tag();
        $tag2->setTitle('symfony');

        $collection = new ArrayCollection([$tag1, $tag2]);

        $result = $this->transformer->transform($collection);

        $this->assertSame('php, symfony', $result);
    }

    /**
     * Testuje, że reverseTransform odnajduje istniejące tagi i tworzy nowe.
     */
    public function testReverseTransformFindsExistingTags(): void
    {
        $tag1 = new Tag();
        $tag1->setTitle('php');

        $this->tagService->expects($this->exactly(2))
            ->method('findOneByTitle')
            ->withConsecutive(['php'], [' symfony'])
            ->willReturnOnConsecutiveCalls($tag1, null);

        $this->tagService->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Tag $tag) {
                return ' symfony' === $tag->getTitle();
            }));

        $result = $this->transformer->reverseTransform('php, symfony');

        $this->assertCount(2, $result);
        $this->assertSame('php', $result[0]->getTitle());
        $this->assertSame(' symfony', $result[1]->getTitle());
    }

    /**
     * Testuje, że reverseTransform ignoruje puste tagi i nie wywołuje serwisu.
     */
    public function testReverseTransformIgnoresEmptyTags(): void
    {
        $this->tagService->expects($this->never())->method('save');
        $this->tagService->expects($this->never())->method('findOneByTitle');

        $result = $this->transformer->reverseTransform(' , ,   ');

        $this->assertSame([], $result);
    }
}
