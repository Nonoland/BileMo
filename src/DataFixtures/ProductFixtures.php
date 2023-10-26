<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        //Generate products
        for ($i = 1; $i <= 10000; $i++) {
            $product = new Product();
            $product->setName("Product $i");
            $product->setDescription($faker->realText(500));
            $product->setShortDescription($faker->realText(150));
            $product->setBrand($faker->domainWord());
            $product->setImage($faker->url());
            $product->setGtin($faker->ean13());
            $product->setSupplierPrice($faker->numberBetween(50, 9999));
            $product->setSuggestedPrice($product->getSupplierPrice() + 50);
            $product->setFeatures($faker->words($faker->numberBetween(2, 6)));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
