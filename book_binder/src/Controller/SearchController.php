<?php

namespace App\Controller;

use App\Entity\MeetupRequests;
use App\Entity\Book;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Api\GoogleBooksApiClient;
use App\Entity\MeetupRequestList;
use App\Form\MeetupRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;


/*
 * This controller meant for the development of the
 * GoogleBooksApiClient, it shows examples on how to
 * use the class.
 */
class SearchController extends AbstractController
{
    #[Route('/bookSearch/{query}', name: 'bookSearch')]
    public function index($query): Response
    {
        $ApiClient = new GoogleBooksApiClient();

        $results = $ApiClient->searchBooksByTitle($query, 40);

        // Pass the results array to the Twig template.
        return $this->render('book_binder/book_search.html.twig', [
            'controller_name' => 'BookBinderController',
            'results' => $results,
            'query' => $query
        ]);
    }

    /**
     * @throws \Google_Exception
     */
    #[Route('/bookPage/{id}', name: 'bookPage')]
    public function clickBook($id, Security $security, EntityManagerInterface $entityManager): Response
    {
        $ApiClient = new GoogleBooksApiClient();
        $book = $ApiClient->getBookById($id);

        $thumbnailUrl = $book->getVolumeInfo()->getImageLinks()->getThumbnail();

        $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy(['book_ID' => $id], ['datetime' => 'DESC'], 10);
        // Fetch the books based on book IDs in meetupRequests

        return $this->render('book_binder/book_page.html.twig', [
            'book' => $book,
            'thumbnailUrl' => $thumbnailUrl,
            'meetupRequests' => $meetupRequests
        ]);
    }

    /**
     * @Route("/bookSuggestion/{input}", name="book_suggestion", requirements={"input"=".*"})
     */
    #[Route("/bookSuggestion/{input}", name: 'book_suggestion')]
    public function bookSuggestion($input, EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager->getRepository(Book::class)->createQueryBuilder('b')
            ->where('b.title LIKE :title')
            ->setParameter('title', '%' . $input . '%')
            ->getQuery()
            ->getResult();

        $suggestions = [];
        foreach ($books as $book) {
            $suggestions[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                // add any other book properties you need
            ];
        }

        return new JsonResponse($suggestions);
    }
}
