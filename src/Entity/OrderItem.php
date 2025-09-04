<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\OrderitemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderitemRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class OrderItem
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    #[Assert\NotNull]
    #[Assert\Positive(message : 'Le prix du produit doit être positif')]
    #[Assert\Type(type: 'numeric')]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'orderitems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderId = null;

    #[ORM\ManyToOne(inversedBy: 'orderitems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getOrderId(): ?Order
    {
        return $this->orderId;
    }

    public function setOrderId(?Order $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
