<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été bloqué par un administrateur. Vous ne pouvez plus vous connecter.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Nothing to check post-auth
    }
}
