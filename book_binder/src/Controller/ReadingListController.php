<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class ReadingListController extends AbstractController
{
    #[Route('/reading-list', name: 'reading_list')]
    public function showReadingList(EntityManagerInterface $entityManager): Response
    {
        // creeate empty JSON arrays to hold the results
        $currently_reading = [];
        $want_to_read = [];
        $have_read = [];

        // render the reading list page
        return $this->render('reading_list/reading_list.html.twig', [
            'currently_reading' => $currently_reading,
            'want_to_read' => $want_to_read,
            'have_read' => $have_read
        ]);
    }
}
