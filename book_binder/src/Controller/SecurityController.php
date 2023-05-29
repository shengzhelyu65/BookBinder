<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/login/google', name: 'app_login_google')]
    public function googleCheckAction(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): RedirectResponse
    {
        // Get the credential from the request
        $credential = $request->request->get('credential');

        // Split the credential into three parts: header, payload, and signature
        $parts = explode('.', $credential);

        // Decode the payload using Base64url decoding
        $payload = json_decode(base64_decode($parts[1]), true);

        // Get the email from the payload
        $email = $payload['email'];

        // check if the user already exists in the database
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            // if the user doesn't exist, create a new user object and set its properties
            $user = new User();
            $user->setEmail($email);
            $user->setPassword('');

            // persist the user object to the database
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // authenticate the user
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            // create a new user
            $user = new User();
            $user->setEmail($email);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // set the nickname
        $nickname = explode('@', $email)[0];
        if (!$user->getUserPersonalInfo()) {
            $userPersonalInfo = new UserPersonalInfo();
            $userPersonalInfo->setUser($user);
            $userPersonalInfo->setNickname($nickname);
            $entityManager->persist($userPersonalInfo);
            $entityManager->flush();
        }

        // authenticate the user
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        // store the token in the token storage
        $tokenStorage->setToken($token);

        if (!$user->getUserReadingList()) {
            $userReadingList = new UserReadingList();
            $userReadingList->setUser($user);
            $userReadingList->setCurrentlyReading([]);
            $userReadingList->setWantToRead([]);
            $userReadingList->setHaveRead([]);
            $entityManager->persist($userReadingList);
            $entityManager->flush();
        }

        // redirect the user to the home page or the reading interest page
        if (!$user->getUserReadingInterest()) {
            return $this->redirectToRoute('reading_interest');
        }

        return $this->redirectToRoute('app_home');
    }
}
