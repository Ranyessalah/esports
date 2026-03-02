<?php

namespace App\Service;

use App\Entity\Matchs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

 
    public function sendMatchNotification(string $coachEmail, Matchs $match): void    {
        $html = $this->twig->render('emails/match_notification.html.twig', [
            'match' => $match,
        ]);

        $email = (new Email())
            ->from('ClutchX Tournament <no-reply@longevityplus.store>')
            ->to($coachEmail)
            ->subject('🎮 New Match Scheduled - '.$match->getNomMatch())
            ->html($html);

        $this->mailer->send($email);
    }
}
