<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class EntityListener
{

    public function __construct(
        private TagAwareCacheInterface $cache
    ) {

    }

    #[PostPersist]
    public function postPersist(LifecycleEventArgs $args): void
    {
        if ($args->getObject() instanceof Customer) {
            $this->cache->invalidateTags(["customersLists", "customersDetails"]);
        }

        if ($args->getObject() instanceof Product) {
            $this->cache->invalidateTags(["productsLists", "productsDetails"]);
        }
    }
}