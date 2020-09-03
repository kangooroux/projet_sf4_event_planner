<?php


namespace App\Service;


use App\Entity\Event;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailSender
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Créer un email préconfiguré
     * @param string $subject Le sujet du mail
     * @return TemplatedEmail
     */

    private function createTemplatedEmail(string $subject) :TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(new Address('bousquetalexandrepro@gmail.com', 'Alexandre'))       # éxpéditeur
            ->subject("\u{1F5D3} EventPlanner | $subject")                                        # Objet de l'Email
            ;
    }

    /**
     * Enovoyer un Email d'invitation
     * @param $form
     */
    public function sendEventInvitationEmail($address, Event $event): void
    {
        $email = $this->createTemplatedEmail('Invitation à un événement')
            ->to(new Address($address))            # Destination
            ->htmlTemplate('email/event_invitation.html.twig')      # Template twig du message
            ->context([                                                         # Variable du template
                'event' => $event,
            ])
        ;

        $this->mailer->send($email);
    }

}