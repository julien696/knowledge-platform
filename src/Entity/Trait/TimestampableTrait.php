<?php

namespace App\Entity\Trait;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
trait TimestampableTrait
{
    #[ORM\Column]
    private ?\DateTimeImmutable $created_at=null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at=null;

    #[ORM\PrePersist]
    public function setCreatedValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedValue(): void
    {
        $this->upadated_at = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpadatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }
}