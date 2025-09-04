<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Order
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\DateTime]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    #[Assert\NotNull]
    #[Assert\Positive(message : 'Le prix du produit doit être positif')]
    #[Assert\Type(type: 'numeric')]
    private ?string $total = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Orderitem>
     */
    #[ORM\OneToMany(targetEntity: Orderitem::class, mappedBy: 'orderId', orphanRemoval: true)]
    private Collection $orderitems;

    public function __construct()
    {
        $this->orderitems = new ArrayCollection();
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
     * @return Collection<int, Orderitem>
     */
    public function getOrderitems(): Collection
    {
        return $this->orderitems;
    }

    public function addOrderitem(Orderitem $orderitem): static
    {
        if (!$this->orderitems->contains($orderitem)) {
            $this->orderitems->add($orderitem);
            $orderitem->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderitem(Orderitem $orderitem): static
    {
        if ($this->orderitems->removeElement($orderitem)) {
            // set the owning side to null (unless already changed)
            if ($orderitem->getOrderId() === $this) {
                $orderitem->setOrderId(null);
            }
        }

        return $this;
    }
}
