<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $short_description = null;

    #[ORM\Column]
    private ?float $supplier_price = null;

    #[ORM\Column(nullable: true)]
    private ?float $suggested_price = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $features = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(length: 14)]
    private ?string $gtin = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(?string $short_description): static
    {
        $this->short_description = $short_description;

        return $this;
    }

    public function getSupplierPrice(): ?float
    {
        return $this->supplier_price;
    }

    public function setSupplierPrice(float $supplier_price): static
    {
        $this->supplier_price = $supplier_price;

        return $this;
    }

    public function getSuggestedPrice(): ?float
    {
        return $this->suggested_price;
    }

    public function setSuggestedPrice(?float $suggested_price): static
    {
        $this->suggested_price = $suggested_price;

        return $this;
    }

    public function getFeatures(): ?array
    {
        return $this->features;
    }

    public function setFeatures(?array $features): static
    {
        $this->features = $features;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(string $gtin): static
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function getData(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'gtin' => $this->getGtin(),
            'brand' => $this->getBrand(),
            'image' => $this->getImage(),
            'supplier_price' => $this->getSupplierPrice(),
            'suggested_price' => $this->getSuggestedPrice(),
            'short_description' => $this->getShortDescription(),
            'description' => $this->getDescription(),
            'features' => $this->getFeatures()
        ];
    }
}
