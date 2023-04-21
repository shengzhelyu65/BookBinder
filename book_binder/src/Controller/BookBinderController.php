<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\UserCredentialsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function home(): Response
    {
        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('book_binder/login_test.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }
}
