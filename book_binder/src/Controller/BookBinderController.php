<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookBinderController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }

    #[Route("/home", name: "users")]
    public function home(Request $request): Response
    {
        $credential = $request->request->get('credential');

        // Split the credential into three parts: header, payload, and signature
        $parts = explode('.', $credential);

        // Decode the payload using Base64url decoding
        $payload = json_decode(base64_decode($parts[1]), true);

        // Get the email from the payload
        $email = $payload['email'];
        $given_name = $payload['given_name'];
        $family_name = $payload['family_name'];

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'credential' => $credential,
            'email' => $email,
            'given_name' => $given_name,
            'family_name' => $family_name
        ]);
    }
}
