<?php


namespace App\Service;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserRoleService
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function sessionUserCanChangeUserRole(?User $editedUser = null): bool
    {
        if (is_null($editedUser)) {
            return false;
        }

        /** @var User|null $sessionUser */
        $sessionUser = $this->tokenStorage->getToken()->getUser() ?? null;
        if (is_null($sessionUser)) {
            return false;
        }

        if ($sessionUser->isSuperAdmin()) {
            return true;
        }

        return !$editedUser->isTeam();
    }

    public function getAllowedNewRoleChoices() {
        $choices = User::ROLE_MAP;

        $superAdminKey = array_flip($choices)['ROLE_SUPER_ADMIN'];
        $adminKey = array_flip($choices)['ROLE_ADMIN'];

        /** @var User|null $user */
        $user = $this->tokenStorage->getToken()->getUser() ?? null;

        if (isset($user) && $user instanceof User) {
            if (!$user->isSuperAdmin()) {
                unset($choices[$superAdminKey]);
                unset($choices[$adminKey]);
            }
        }

        return $choices;
    }
}