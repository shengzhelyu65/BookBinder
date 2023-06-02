<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingInterest;
use App\Entity\UserReadingList;
use App\Form\RegistrationFormType;
use App\Form\ReadingInterestFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $userPersonalInfo = new UserPersonalInfo();
        $userReadingList = new UserReadingList();

        // pass the UserPersonalInfo and user objects to the form
        $form = $this->createForm(RegistrationFormType::class, [$user, $userPersonalInfo]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // check if email is already registered
            $email = $form->get('email')->getData();
            $email = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($email) {
                $this->addFlash('error', 'Email already registered');
                return $this->redirectToRoute('app_register');
            } else {
                $user->setEmail($form->get('email')->getData());
            }

            $entityManager->persist($user);

            //handle user personal info if any
            $userPersonalInfo->setUser($user);
            // try to add name, if not set, set it to null
            if (!empty($form->get('name')->getData())) {
                $userPersonalInfo->setName($form->get('name')->getData());
            }
            // try to add surname, if not set, set it to null
            if (!empty($form->get('surname')->getData())) {
                $userPersonalInfo->setSurname($form->get('surname')->getData());
            }
            // check if nickname is already used and add nickname
            $nickname = $form->get('nickname')->getData();
            $nickname = $entityManager->getRepository(UserPersonalInfo::class)->findOneBy(['nickname' => $nickname]);
            if ($nickname) {
                $this->addFlash('error', 'Nickname already in use');
                return $this->redirectToRoute('app_register');
            } else {
                $userPersonalInfo->setNickname($form->get('nickname')->getData());
            }

            $entityManager->persist($userPersonalInfo);

            //handle user reading list
            $userReadingList->setUser($user);

            $userReadingList->setCurrentlyReading([]);
            $userReadingList->setWantToRead([]);
            $userReadingList->setHaveRead([]);

            $entityManager->persist($userReadingList);

            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('arthurtquintao@gmail.com', 'BookBinder Bot'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            // authenticate the user
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            // store the token in the token storage
            $tokenStorage->setToken($token);

            // Redirect to the reading interest form
            return $this->redirectToRoute('reading_interest');
        }

        return $this->render('registration/register.html.twig', [
            'controller_name' => 'RegistrationController',
            'registrationForm' => $form->createView(),
            'includeReadingInterestForm' => false,
        ]);
    }

    #[Route('/reading-interest', name: 'reading_interest')]
    public function collectReadingInterestForm(Request $request, EntityManagerInterface $entityManager,): Response
    {
        // check if the user has already filled the reading interest form
        $user = $this->getUser();
        $userReadingInterest = $entityManager->getRepository(UserReadingInterest::class)->findOneBy(['user' => $user]);
        if ($userReadingInterest) {
            return $this->redirectToRoute('app_home');
        }

        $userReadingInterest = new UserReadingInterest();

        $readingInterestForm = $this->createForm(ReadingInterestFormType::class, [$userReadingInterest]);
        $readingInterestForm->handleRequest($request);

        if ($readingInterestForm->isSubmitted() && $readingInterestForm->isValid()) {
            // Retrieve the selected languages and genres from the form
            $selectedLanguages = $readingInterestForm->get('languages')->getData();
            $selectedGenres = $readingInterestForm->get('genres')->getData();

            // Put the selected languages and genres in the user reading interest object
            $userReadingInterest->setLanguages($selectedLanguages);
            $userReadingInterest->setGenres($selectedGenres);

            // Get the user from the token storage
            $user = $this->getUser();
            // Set the user reading interest object to the user
            $userReadingInterest->setUser($user);

            // Persist the user reading interest to the database
            $entityManager->persist($userReadingInterest);
            $entityManager->flush();

            // Redirect to the next step or perform any other desired action
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'controller_name' => 'RegistrationController',
            'includeReadingInterestForm' => true,
            'readingInterestForm' => $readingInterestForm->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (is_null($user)) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
