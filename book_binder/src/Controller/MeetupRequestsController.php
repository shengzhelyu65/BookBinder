<?php

namespace App\Controller;

use App\Entity\MeetupRequests;
use App\Entity\User;
use App\Form\MeetupRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MeetupRequestsController extends AbstractController
{
    #[Route('/meetup/request/{userId}/{bookId}', name: 'app_meetup_requests')]
    public function createMeetupRequest(Request $request, int $userId, int $bookId, EntityManagerInterface $entityManager): Response
    {
        $meetupRequest = new MeetupRequests();

        // Get the currently logged-in user
        // $user = $this->getUser();

        // Get the currently logged-in user from the database
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        // Create the form
        $form = $this->createForm(MeetupRequestFormType::class, $meetupRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meetupRequest->setHostUser($user);
            $meetupRequest->setBookID($bookId);

            $entityManager->persist($meetupRequest);
            $entityManager->flush();

            // Redirect to a success page or do other actions
            return $this->redirectToRoute('meetup_requests_list');
        }

        return $this->render('meetup_request/meetup_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/meetup/requests/list', name: 'meetup_requests_list')]
    public function showLatestRequests(EntityManagerInterface $entityManager): Response
    {
        $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy([], ['datetime' => 'DESC'], 10);

        return $this->render('meetup_request/meetup_request_list.html.twig', [
            'meetupRequests' => $meetupRequests
        ]);
    }
}