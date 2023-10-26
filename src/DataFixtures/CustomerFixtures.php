<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Store;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CustomerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $storeRepository = $manager->getRepository(Store::class);
        $stores = $storeRepository->findAll();
        $storesCount = count($stores);

        //Generate customer
        for ($i = 1; $i < 5000; $i++) {
            $customer = new Customer();
            $customer->setEmail($faker->freeEmail());
            $customer->setLastname($faker->lastName());
            $customer->setFirstname($faker->firstName());
            $customer->setStore($stores[$faker->numberBetween(0, $storesCount-1)]);
            $manager->persist($customer);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            StoreFixtures::class
        ];
    }
}
