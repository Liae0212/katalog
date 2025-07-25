<?php

/**
 * Task voter.
 */

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TaskVoter.
 */
class TaskVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'DELETE';

    /**
     * Block permission.
     *
     * @const string
     */
    public const BLOCK = 'BLOCK';

    /**
     * TaskVoter constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::BLOCK])
            && $subject instanceof Task;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::VIEW => $this->canView($subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::DELETE => $this->canDelete($subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::BLOCK => $this->security->isGranted('ROLE_ADMIN'),
            default => false,
        };
    }

    /**
     * Checks if user can edit task.
     *
     * @param Task $task Task entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canEdit(Task $task, User $user): bool
    {
        return $task->getUsers() === $user;
    }

    /**
     * Checks if user can view task.
     *
     * @param Task $task Task entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canView(Task $task, User $user): bool
    {
        return $task->getUsers() === $user;
    }

    /**
     * Checks if user can delete task.
     *
     * @param Task $task Task entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canDelete(Task $task, User $user): bool
    {
        return $task->getUsers() === $user;
    }
}
