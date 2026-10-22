<?php

namespace App\Listener;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: LoginSuccessEvent::class)]
final class Login_sucessFull
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->logger->info('Connexion réussie.', [
            'user_id' => $user->getId(),
            'firewall' => $event->getFirewallName(),
        ]);
    }
}