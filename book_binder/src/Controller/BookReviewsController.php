<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BookReviewFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\BookReviews;
use Symfony\Component\HttpFoundation\Request;

class BookReviewsController extends AbstractController
{
    #[Route('/book/review/{userId}/{bookId}', name: 'app_book_review')]
    public function addBookReview(Request $request, int $userId, int $bookId, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        $bookReview = new BookReviews();
        $form = $this->createForm(BookReviewFormType::class, $bookReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookReview->setUserID($user);
            $bookReview->setBookID($bookId);
            $bookReview->setCreatedAt(new \DateTime());

            $entityManager->persist($bookReview);
            $entityManager->flush();

            return $this->redirectToRoute('book_reviews_list');
        }

        return $this->render('book_reviews/book_review.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/reviews/list', name: 'book_reviews_list')]
    public function showLatestReviews(EntityManagerInterface $entityManager): Response
    {
        $bookReviews = $entityManager->getRepository(BookReviews::class)->findBy([], ['created_at' => 'DESC'], 10);

        return $this->render('book_reviews/book_review_list.html.twig', [
            'bookReviews' => $bookReviews,
        ]);
    }

    #[Route('/book/reviews/search', name: 'book_reviews_search')]
    public function searchReviews(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = $request->query->get('q');

        // Search for reviews that contain the query string in the review text or tags
        $bookReviews = $entityManager->getRepository(BookReviews::class)->createQueryBuilder('r')
            ->where('r.review LIKE :query OR r.tags LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        // Transform the reviews into an array of data that can be easily encoded as JSON
        $reviewsData = [];
        foreach ($bookReviews as $review) {
            $reviewsData[] = [
                'review_ID' => $review->getReviewID(),
                'rating' => $review->getRating(),
                'review' => $review->getReview(),
                'tags' => $review->getTags(),
            ];
        }

        return $this->render('book_reviews/book_review_search.html.twig', [
            'reviewsData' => $reviewsData,
            'query' => $query
        ]);
    }
}
