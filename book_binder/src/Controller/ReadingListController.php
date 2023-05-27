<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingInterest;
use App\Entity\UserReadingList;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class ReadingListController extends AbstractController
{
    #[Route('/reading-list', name: 'reading_list')]
    public function showReadingList(EntityManagerInterface $entityManager): Response
    {

        // fetch the user's reading list
        $user = $this->getUser();
        $user_reading_list = $user->getUserReadingList();

        // if the user has a reading list, fetch the books
        if ($user_reading_list) {
            $currently_reading = $user_reading_list->getCurrentlyReading();
            $want_to_read = $user_reading_list->getWantToRead();
            $have_read = $user_reading_list->getHaveRead();
        }

        // query the Books table based on the ids in currently_reading

        // query the Books table based on the ids in want_to_read

        // query the Books table based on the ids in have_read


        // render the reading list page
        return $this->render('reading_list/reading_list.html.twig', [
            'currently_reading' => $currently_reading,
            'want_to_read' => $want_to_read,
            'have_read' => $have_read
        ]);
    }
}
