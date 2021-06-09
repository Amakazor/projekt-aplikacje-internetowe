<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserCreationMailer
{
    /**
     * @var VerifyEmailHelperInterface
     */
    private $helper;
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * UserCreationMailer constructor.
     * @param VerifyEmailHelperInterface $helper
     * @param MailerInterface $mailer
     * @param TranslatorInterface $translator
     */
    public function __construct (VerifyEmailHelperInterface $helper, MailerInterface $mailer, TranslatorInterface $translator)
    {

        $this->helper = $helper;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @param User $user
     * @throws TransportExceptionInterface
     */
    public function sendVerificationEmail(User $user): void
    {
        $signatureComponents = $this->helper->generateSignature('user_mail_confirmation', $user->getId(), $user->getEmail(), ['id' => $user->getId()]);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject($this->translator->trans('email.verifyEmail'))
            ->htmlTemplate('email/confirmationEmail.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                'plainPassword' => $user->getPlainPassword(),
                'login' => $user->getUsername()
            ]);

        $this->mailer->send($email);
    }
}