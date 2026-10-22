<?php

namespace App\Command;

use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:promote-admin',
    description: 'Attribue le rôle administrateur à un utilisateur existant.'
)]
final class PromoteUserToAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail du compte à promouvoir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = trim((string) $input->getArgument('email'));
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            $io->error(sprintf('Aucun utilisateur trouvé pour l’adresse « %s ».', $email));

            return Command::FAILURE;
        }

        $adminRole = $this->roleRepository->findOneBy(['code' => 'ROLE_ADMIN']);
        if ($adminRole === null) {
            $io->error('Le rôle ROLE_ADMIN est absent de la base. Exécutez les migrations avant de réessayer.');

            return Command::FAILURE;
        }

        if ($user->getRole()?->getCode() === 'ROLE_ADMIN') {
            $io->success(sprintf('Le compte « %s » est déjà administrateur.', $email));

            return Command::SUCCESS;
        }

        $user->setRole($adminRole);
        $user->setRoles(['ROLE_ADMIN']);
        $this->entityManager->flush();

        $io->success(sprintf('Le compte « %s » est maintenant administrateur.', $email));

        return Command::SUCCESS;
    }
}
