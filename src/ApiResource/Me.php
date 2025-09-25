<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\Entity\EnrollmentCursus;
use App\Entity\EnrollmentLesson;
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
    #[Groups(['me:read'])]
    public ?User $user = null;

    /** @var EnrollmentCursus[] */
    #[Groups(['me:read'])]
    public iterable $enrollmentCursuses = [];

    /** @var EnrollmentLesson[] */
    #[Groups(['me:read'])]
    public iterable $enrollmentLessons = [];

    #[Groups(['me:read'])]
    /** @var Theme[] */
    public iterable $themes = [];

    #[Groups(['me:read'])]
    /** @var Certification[] */
    public iterable $certifications = [];
    
    public function __construct(
    ?User $user = null,
    iterable $enrollmentCursuses = [],
    iterable $enrollmentLessons = [],
    iterable $themes = [],
    iterable $certifications = []
    ) {
        $this->user = $user;
        $this->enrollmentCursuses = $enrollmentCursuses;
        $this->enrollmentLessons = $enrollmentLessons;
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
    public function isVerified(): bool
    {
        return $this->user?->isVerified() ?? false;
    }
}