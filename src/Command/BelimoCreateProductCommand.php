<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'belimo:create-product',
    description: 'Add product',
)]
class BelimoCreateProductCommand extends Command
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $product = new Product();

        $product->setName($io->ask('Name ?'));
        $product->setBrand($io->ask('Brand ?'));
        $product->setGtin($io->ask('Gtin ?'));
        $product->setImage($io->ask('Url image ?'));
        $product->setSupplierPrice($io->ask('Supplier price ?'));
        $product->setSuggestedPrice($io->ask('Suggested price ?'));
        $product->setShortDescription($io->ask('Short description ?'));
        $product->setDescription($io->ask('Description ?'));

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
