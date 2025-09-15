<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SendConfirmationEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler()]
class SendConfirmationEmailMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {}

    public function __invoke(SendConfirmationEmailMessage $message): void
    {
        $user = $this->em->getRepository(User::class)->find($message->getUserId());

        if(!$user || !$user->getConfirmationToken())
        {
            return;
        }

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