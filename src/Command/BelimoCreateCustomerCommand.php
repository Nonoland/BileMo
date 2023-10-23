<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Store;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'belimo:create-customer',
    description: 'Add customer',
)]
class BelimoCreateCustomerCommand extends Command
{

    private EntityManagerInterface $entityManager;
    private StoreRepository $storeRepository;

    public function __construct(EntityManagerInterface $entityManager, StoreRepository $storeRepository)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->storeRepository = $storeRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $customer = new Customer();

        $customer->setLastname($io->ask('Lastname ?'));
        $customer->setFirstname($io->ask('Firstname ?'));
        $customer->setEmail($io->ask('Email ?'));

        $stores = $this->storeRepository->findAll();
        $storesByName = [];
        foreach ($stores as $store) {
            $storesByName[$store->getName()] = $store;
        }
        $questionStore = new Question('Which store ?');
        $questionStore->setAutocompleterCallback(function (string $input) use ($storesByName): array {
            $input = strtolower($input);
            $matchedNames = [];
            foreach ($storesByName as $name => $store) {
                if (str_starts_with(strtolower($name), $input)) {
                    $matchedNames[] = $name;
                }
            }
            return $matchedNames;
        });
        $customer->setStore($storesByName[$io->askQuestion($questionStore)]);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
