<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    operations: [
        new Post(
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['order:write']],
            normalizationContext: ['groups' => ['order:read']]
        ),
        new \ApiPlatform\Metadata\GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new \ApiPlatform\Metadata\Get(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new \ApiPlatform\Metadata\GetCollection(
            uriTemplate: '/my/orders',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['user:read']],
            provider: \App\State\UserOrdersProvider::class
        ),
        new \ApiPlatform\Metadata\Get(
            uriTemplate: '/my/orders/{id}',
            security: "is_granted('ROLE_USER') and object.getUser() == user",
            normalizationContext: ['groups' => ['user:read']]
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Order
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin:read', 'user:read', 'order:read', 'me:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\DateTime]
    #[Groups(['admin:read', 'user:read', 'order:read', 'me:read'])]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 50)]
    #[Groups(['admin:read', 'user:read', 'me:read'])]
    private string $status = 'pending';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['admin:read', 'user:read'])]
    private ?string $stripeOrderId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Type(type: 'numeric')]
    #[Groups(['admin:read', 'user:read', 'order:read', 'me:read'])]
    private ?float $amount = 0.0;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['admin:read', 'order:read'])]
    private ?User $user = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderId', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['admin:read', 'user:read', 'order:write', 'me:read'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }
    
    public function calculateAmount(): void
    {
        $total = 0.0;
        foreach ($this->orderItems as $item) {
            $total += (float) $item->getPrice();
        }
        $this->amount = $total;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->date === null) {
            $this->date = new \DateTime();
        }
        // Ne recalculer l'amount que s'il y a des orderItems
        if (!$this->orderItems->isEmpty()) {
            $this->calculateAmount();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStripeOrderId(): ?string
    {
        return $this->stripeOrderId;
    }

    public function setStripeOrderId(?string $stripeOrderId): self
    {
        $this->stripeOrderId = $stripeOrderId;
        return $this;
    }


    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderId($this);
            $this->calculateAmount();
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderId() === $this) {
                $orderItem->setOrderId(null);
            }
        }

        return $this;
    }
}
