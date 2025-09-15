<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        // Gestion de la suppression
        if ($operation instanceof DeleteOperationInterface) {
            $this->em->remove($data);
            $this->em->flush();
            return null;
        }

        if (!$data instanceof User) {
            return $data;
        }

        // Pour une nouvelle inscription (POST /register)
        if ($operation->getName() === 'user_register') {
            return $this->handleRegistration($data);
        }

        // Pour la création d'utilisateur par un admin (POST /admin/users)
        if ($operation->getName() === 'post' && str_contains($context['uri'] ?? '', '/admin/users')) {
            return $this->handleAdminCreateUser($data);
        }

        // Pour la mise à jour d'utilisateur (PUT/PATCH)
        if (in_array($operation->getName(), ['put', 'patch'], true)) {
            return $this->handleUpdateUser($data, $context);
        }

        return $data;
    }

    private function handleRegistration(User $user): User
    {
        $user->setRole(UserRole::USER);
        $user->setIsVerified(false);
        
        $this->processPassword($user);
        $this->setConfirmationToken($user);
        
        $this->em->persist($user);
        $this->em->flush();

        $this->sendConfirmationEmail($user);

        return $user;
    }

    private function handleAdminCreateUser(User $user): User
    {
        if (!$user->getRole()) {
            $user->setRole(UserRole::USER);
        }
        
        // On exige que l'admin fournisse un mot de passe
        if (empty($user->getPlainPassword())) {
            throw new \InvalidArgumentException('Un mot de passe est requis pour créer un utilisateur');
        }
        
        $this->processPassword($user);
        
        // Si l'admin ne définit pas l'état de vérification, on le vérifie par défaut
        if ($user->isVerified() === null) {
            $user->setIsVerified(true);
        }
        
        $this->em->persist($user);
        $this->em->flush();

        // Envoyer un email avec les identifiants si nécessaire
        // $this->sendWelcomeEmail($user);
        
        return $user;
    }

    private function handleUpdateUser(User $user, array $context): User
    {
        $originalData = $context['previous_data'] ?? null;
        
        // Si le mot de passe est modifié
        if ($user->getPlainPassword()) {
            $this->processPassword($user);
        } elseif ($originalData && !empty($originalData->getPassword())) {
            // Garder l'ancien mot de passe si non modifié
            $user->setPassword($originalData->getPassword());
        }
        
        // Un utilisateur non-admin ne peut pas modifier son rôle
        if (!$this->security->isGranted('ROLE_ADMIN') && $originalData) {
            $user->setRole($originalData->getRole());
        }
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }

    private function processPassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $hashed = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashed);
            $user->eraseCredentials();
        }
    }

    private function setConfirmationToken(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $user->setConfirmationToken($token);
    }

    private function sendConfirmationEmail(User $user): void
    {
        $confirmationUrl = sprintf(
            '%s/confirm-account?token=%s',
            $_ENV['FRONTEND_URL'] ?? 'https://ton-domaine.com',
            $user->getConfirmationToken()
        );

        $email = (new Email())
            ->from($_ENV['MAILER_FROM'] ?? 'no-reply@tonsite.com')
            ->to($user->getEmail())
            ->subject('Confirmez votre compte')
            ->html(sprintf(
                '<p>Bonjour %s,</p><p>Cliquez sur ce lien pour confirmer votre compte : <a href="%s">%s</a></p>',
                $user->getName(),
                $confirmationUrl,
                $confirmationUrl
            ));

        $this->mailer->send($email);
    }
}
