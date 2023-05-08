<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BookReviewFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\BookReviews;
use Symfony\Component\HttpFoundation\Request;

class BookReviewsController extends AbstractController
{
    #[Route('/book/review/{userId}', name: 'app_book_review')]
    public function addBookReview(Request $request, int $userId, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        $bookReview = new BookReviews();
        $form = $this->createForm(BookReviewFormType::class, $bookReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bookReview);
            $entityManager->flush();

            return $this->redirectToRoute('book_reviews_list');
        }

        return $this->render('book_reviews/book_review.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/reviews/list', name: 'book_reviews_list')]
    public function showAllReviews(EntityManagerInterface $entityManager): Response
    {
        $bookReviews = $entityManager->getRepository(BookReviews::class)->findAll();

        return $this->render('book_reviews/book_review_list.html.twig', [
            'bookReviews' => $bookReviews,
        ]);
    }
}
