<?php

namespace App\Command;

use App\Entity\Store;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'belimo:create-user',
    description: 'Add user',
)]
class BelimoCreateUserCommand extends Command
{

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = new User();
        $user->setEmail($io->ask('Email ?'));

        $userRepository = $this->entityManager->getRepository(User::class);
        if ($userRepository->count(['email' => $user->getEmail()]) >= 1) {
            $io->error('An account already exists with this email address');
            return Command::FAILURE;
        }

        $password = $io->askHidden('Password ?');
        $repeatPassword = $io->askHidden('Repeat password');

        if ($password !== $repeatPassword) {
            $io->error('Passwords don\'t match.');
            return Command::FAILURE;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $admin = $io->ask('User admin ? (yes/no) [no]', 'no');
        if ($admin !== "no") {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User successfully created');

        return Command::SUCCESS;
    }
}
