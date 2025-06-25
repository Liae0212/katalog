<?php

/**
 * GuestUserRepositoryTest.
 *
 * Unit tests for the GuestUserRepository form.
 */

namespace App\Tests\Repository;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GuestUserRepositoryTest.
 */
class GuestUserRepositoryTest extends KernelTestCase
{
    private ?GuestUserRepository $repository;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->repository = $container->get(GuestUserRepository::class);

        $em = $container->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\GuestUser g')->execute();
    }

    /**
     * Test saving a guest user.
     */
    public function testSaveGuestUser(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('test@example.com');

        $this->repository->save($guestUser);

        $this->assertNotNull($guestUser->getId());

        $found = $this->repository->find($guestUser->getId());
        $this->assertInstanceOf(GuestUser::class, $found);
        $this->assertSame('test@example.com', $found->getEmail());
    }

    /**
     * Test finding a guest user by email.
     */
    public function testFindByEmail(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('user@example.com');
        $this->repository->save($guestUser);

        $found = $this->repository->findOneBy(['email' => 'user@example.com']);
        $this->assertNotNull($found);
        $this->assertSame('user@example.com', $found->getEmail());
    }
}
