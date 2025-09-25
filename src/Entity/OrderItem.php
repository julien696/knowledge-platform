<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
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
    #[Groups(['admin:read', 'user:read'])]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderId = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[Groups(['admin:read', 'user:read', 'order:write', 'user:read'])]
    private ?Lesson $lesson = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[Groups(['admin:read', 'user:read', 'order:write', 'user:read'])]
    private ?Cursus $cursus = null;

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

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    public function setCursus(?Cursus $cursus): static
    {
        $this->cursus = $cursus;
        return $this;
    }

    #[Groups(['admin:read', 'user:read', 'order:read', 'me:read'])]
    public function getProductName(): ?string
    {
        if ($this->cursus) {
            return $this->cursus->getName();
        }
        if ($this->lesson) {
            return $this->lesson->getName();
        }
        return null;
    }

    #[Groups(['admin:read', 'user:read', 'order:read', 'me:read'])]
    public function getProductType(): ?string
    {
        if ($this->cursus) {
            return 'cursus';
        }
        if ($this->lesson) {
            return 'lesson';
        }
        return null;
    }
}
