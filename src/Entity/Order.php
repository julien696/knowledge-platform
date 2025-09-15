<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
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
    // Opérations d'administration
    operations: [
        new \ApiPlatform\Metadata\GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new \ApiPlatform\Metadata\Get(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin:read']]
        ),
        // Les utilisateurs peuvent voir leurs propres commandes
        new \ApiPlatform\Metadata\GetCollection(
            uriTemplate: '/my/orders',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['user:read']],
            provider: [Order::class, 'getUserOrders']
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
    #[Groups(['admin:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\DateTime]
    #[Groups(['admin:read', 'user:read'])]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    #[Assert\NotNull]
    #[Assert\Positive(message : 'Le prix du produit doit être positif')]
    #[Assert\Type(type: 'numeric')]
    #[Groups(['admin:read', 'user:read'])]
    private ?string $total = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['admin:read'])]
    private ?User $user = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderId', orphanRemoval: true)]
    #[Groups(['admin:read', 'user:read'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }
    
    /**
     * Récupère les commandes de l'utilisateur connecté
     */
    public static function getUserOrders($userId, OrderRepository $orderRepository): array
    {
        return $orderRepository->findBy(['user' => $userId], ['date' => 'DESC']);
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

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

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
