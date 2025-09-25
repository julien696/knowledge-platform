<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\Entity\EnrollmentCursus;
use App\Entity\EnrollmentLesson;
use App\Entity\Order;
use App\State\MeProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
            name: 'me_profil',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['me:read']]
            )
        ],
        provider: MeProvider::class
        )]
class Me
{
    private ?User $user = null;

    /** @var EnrollmentCursus[] */
    private iterable $enrollmentCursuses = [];

    /** @var EnrollmentLesson[] */
    private iterable $enrollmentLessons = [];

    /** @var Order[] */
    private iterable $orders = [];

    /** @var Theme[] */
    private iterable $themes = [];

    /** @var Certification[] */
    private iterable $certifications = [];
    
    public function __construct(
    ?User $user = null,
    iterable $enrollmentCursuses = [],
    iterable $enrollmentLessons = [],
    iterable $orders = [],
    iterable $themes = [],
    iterable $certifications = []
    ) {
        $this->user = $user;
        $this->enrollmentCursuses = $enrollmentCursuses;
        $this->enrollmentLessons = $enrollmentLessons;
        $this->orders = $orders;
        $this->themes = $themes;
        $this->certifications = $certifications;
    }
    
    #[Groups(['me:read'])]
    public function getId(): ?int
    {
        return $this->user?->getId();
    }

    #[Groups(['me:read'])]
    public function getName(): ?string
    {
        return $this->user?->getName();
    }
    
    #[Groups(['me:read'])]
    public function getEmail(): ?string
    {
        return $this->user?->getEmail();
    }

    #[Groups(['me:read'])]
    public function getRole(): ?string
    {
        return $this->user?->getRole()?->value;
    }

    #[Groups(['me:read'])]
    public function getVerified(): bool
    {
        return $this->user?->isVerified() ?? false;
    }

    #[Groups(['me:read'])]
    public function getEnrollmentCursuses(): iterable
    {
        return $this->enrollmentCursuses;
    }

    #[Groups(['me:read'])]
    public function getEnrollmentLessons(): iterable
    {
        return $this->enrollmentLessons;
    }

    #[Groups(['me:read'])]
    public function getOrders(): iterable
    {
        return $this->orders;
    }

    #[Groups(['me:read'])]
    public function getThemes(): iterable
    {
        return $this->themes;
    }

    #[Groups(['me:read'])]
    public function getCertifications(): iterable
    {
        return $this->certifications;
    }
}