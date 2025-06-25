<?php

/**
 * GuestUserServiceTest.
 *
 * Unit tests for the GuestuserService class.
 */

namespace App\Tests\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use App\Service\GuestUserService;
use App\Service\GuestUserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GuestUserServiceTest.
 */
class GuestUserServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Guest user service.
     */
    private ?GuestUserServiceInterface $guestUserService;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $repository = $container->get(GuestUserRepository::class);

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->guestUserService = new GuestUserService($repository);
    }

    /**
     * Test saving a new guest user.
     */
    public function testSaveNewGuestUser(): void
    {
        $email = 'test_guest@example.com';
        $guestUser = new GuestUser();
        $guestUser->setEmail($email);

        $this->guestUserService->save($guestUser);

        $savedUser = $this->entityManager
            ->getRepository(GuestUser::class)
            ->findOneBy(['email' => $email]);

        $this->assertNotNull($savedUser);
        $this->assertEquals($email, $savedUser->getEmail());
    }

    /**
     * Test save prevents duplicates.
     */
    public function testSaveDuplicateGuestUserIsNotSavedTwice(): void
    {
        $email = 'duplicate_guest@example.com';
        $guestUser1 = new GuestUser();
        $guestUser1->setEmail($email);

        $this->entityManager->persist($guestUser1);
        $this->entityManager->flush();

        $guestUser2 = new GuestUser();
        $guestUser2->setEmail($email);

        $this->guestUserService->save($guestUser2);

        $results = $this->entityManager
            ->getRepository(GuestUser::class)
            ->findBy(['email' => $email]);

        $this->assertCount(1, $results);
        $this->assertEquals($email, $results[0]->getEmail());
    }
}
