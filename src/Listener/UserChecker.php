<?php

namespace App\Listener;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isValid() !== true) {
            throw new CustomUserMessageAuthenticationException('Ce compte a été désactivé par un administrateur.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
