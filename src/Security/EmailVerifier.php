<?php

namespace App\Security;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private $verifyEmailHelper;
    private $mailer;
    private $entityManager;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, string $passwordEnClair, TemplatedEmail $email): void
    {
//        $signatureComponents = $this->verifyEmailHelper->generateSignature(
//            $verifyEmailRouteName,
//            $user->getId(),
//            $user->getEmail()
//        );

        $context = $email->getContext();
//        $context['signedUrl'] = $signatureComponents->getSignedUrl();
//        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
//        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();
        $context['user'] = $user;
        $context['password'] = $passwordEnClair;

        $email->context($context);

        $this->mailer->send($email);
    }

    public function sendEmailAnnulationSortie(User $user, User $userAOrigineDeAnnulation, Sortie $sortie, string $raisonAnnulation): void
    {

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@sortir.com', 'Sortir.com'))
            ->to($user->getEmail())
            ->subject('Annulation de la sortie ' . $sortie->getNom())
            ->htmlTemplate('email/annulationSortie_email.html.twig');

        $context = $email->getContext();
        $context['sortie'] = $sortie;
        $context['userAOrigineDeAnnulation'] = $userAOrigineDeAnnulation;
        $context['user'] = $user;
        $context['raisonAnnulation'] = $raisonAnnulation;

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
