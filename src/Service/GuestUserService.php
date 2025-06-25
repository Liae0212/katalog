<?php

/**
 * Guest User service.
 */

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;

/**
 * Class GuestUserService.
 */
class GuestUserService implements GuestUserServiceInterface
{
    /**
     * GuestUserService constructor.
     *
     * @param GuestUserRepository $guestUserRepository GuestUser repository
     */
    public function __construct(private readonly GuestUserRepository $guestUserRepository)
    {
    }

    /**
     * Save guest user.
     *
     * @param GuestUser $guestUser GuestUser entity
     */
    public function save(GuestUser $guestUser): void
    {
        if ($this->guestUserRepository->findOneByEmail($guestUser->getEmail())) {
            return;
        }

        $this->guestUserRepository->save($guestUser);
    }
}
