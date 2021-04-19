<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FileCsvUploadFormType;
use App\Form\RegistrationFormType;
use App\Repository\CampusRepository;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use App\Services\FileCsvUpload;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator, CampusRepository $campusRepository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the password
            $passwordEnClair = $form->get('plainPassword')->getData();
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_USER']);
            $user->setAdministrateur(false);
            $user->setActif(true);
            $user->setIsVerified(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $passwordEnClair,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@sortir.com', 'Sortir.com'))
                    ->to($user->getEmail())
                    ->subject('Salut le nouveau, viens vite nous rejoindre')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Permet la connexion automatique après l'inscription
//            return $guardHandler->authenticateUserAndHandleSuccess(
//                $user,
//                $request,
//                $authenticator,
//                'main' // firewall name in security.yaml
//            );
            return $this->redirectToRoute('app_register');
        }

        $fileCsvUpload = new FileCsvUpload();
        $fileCsvUploadForm = $this->createForm(FileCsvUploadFormType::class, $fileCsvUpload);
        $fileCsvUploadForm->handleRequest($request);

        if ($fileCsvUploadForm->isSubmitted() && $fileCsvUploadForm->isValid()) {

            $file = $fileCsvUploadForm->get('file')->getData();
            $urlFile = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('fichierCSV_directory'), $urlFile);
            $fileCsvUpload->setFile($urlFile);

            $entityManager = $this->getDoctrine()->getManager();

            $handle = fopen($this->getParameter('fichierCSV_directory') . '/' . $urlFile, 'r');
            if ($handle) {
                while (!feof($handle)) {
                    $buffer = explode(';', fgets($handle));
                    $user = new User();
                    $user->setPseudo($buffer[0]);
                    $user->setEmail($buffer[1]);
                    $user->setNom($buffer[2]);
                    $user->setPrenom($buffer[3]);
                    $campus = $campusRepository->find($buffer[4]);
                    $user->setCampus($campus);
                    $user->setRoles(['ROLE_USER']);
                    $user->setAdministrateur(false);
                    $user->setActif(true);
                    $user->setIsVerified(true);
                    $passwordEnClair = $buffer[2] . '2021';
                    $user->setPassword($passwordEncoder->encodePassword($user,$buffer[2] . '2021'));

                    $entityManager->persist($user);

//                    $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $passwordEnClair,
//                        (new TemplatedEmail())
//                            ->from(new Address('no-reply@sortir.com', 'Sortir.com'))
//                            ->to($user->getEmail())
//                            ->subject('Salut le nouveau, viens vite nous rejoindre')
//                            ->htmlTemplate('registration/confirmation_email.html.twig')
//                    );
                }
                fclose($handle);
            }
            $entityManager->flush();
            unlink($this->getParameter('fichierCSV_directory') . '/' . $urlFile);
            $this->addFlash('success', 'Les utilisateurs ont bien été créée à partir du fichier');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'fileCsvUploadForm' => $fileCsvUploadForm->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
