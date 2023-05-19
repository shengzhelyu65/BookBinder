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
    #[Route('/', name: 'index')]
    public function index(Security $security, EntityManagerInterface $entityManager): Response
    {
        $includeProfileForm = false; // Set this to true or false depending on your condition

        $this->security = $security;
        $user = $this->security->getUser();

        // print_r($user);
        // Get current user entity object from the database using repository method by email
        $email = $user->getEmail();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        // print_r($user->getUserIdentifier());

        // Check if the userPersonalInfo entity object is null
        if ($user->getUserPersonalInfo() == null) {
            // If it is null, set the includeProfileForm to false
            $includeProfileForm = true;
        }

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'includeProfileForm' => $includeProfileForm,
            'userEmail' => $email,
        ]);
    }

    #[Route("/home", name: 'app_home')]
    public function home(): Response
    {
        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }
}
