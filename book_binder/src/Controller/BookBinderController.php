<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class BookBinderController extends AbstractController
{
    #[Route("/", name: 'app_home')]
    public function home(): Response
    {
        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }
}
