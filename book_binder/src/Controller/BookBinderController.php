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

        if (!isset($credential)) {
            $credential = '';
        }

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'credential' => $credential,
        ]);
    }
}
