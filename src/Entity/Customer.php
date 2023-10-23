<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: UserCustomer::class)]
    private Collection $userCustomer;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function __construct()
    {
        $this->userCustomer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, UserCustomer>
     */
    public function getUserCustomer(): Collection
    {
        return $this->userCustomer;
    }

    public function addUserCustomer(UserCustomer $userCustomer): static
    {
        if (!$this->userCustomer->contains($userCustomer)) {
            $this->userCustomer->add($userCustomer);
            $userCustomer->setCustomer($this);
        }

        return $this;
    }

    public function removeUserCustomer(UserCustomer $userCustomer): static
    {
        if ($this->userCustomer->removeElement($userCustomer)) {
            // set the owning side to null (unless already changed)
            if ($userCustomer->getCustomer() === $this) {
                $userCustomer->setCustomer(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
