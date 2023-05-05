<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

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
    public function googleCheckAction(Request $request, EntityManagerInterface $entityManager): RedirectResponse
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

        // redirect the user to a new page
        return new RedirectResponse($this->generateUrl('app_login'));
    }
}
