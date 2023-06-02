<?php

namespace App\Controller;

use App\Entity\UserPersonalInfo;
use App\Form\SettingsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function settings(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
        }

        $userPersonalInfo = $user->getUserPersonalInfo();
        $userReadingInterest = $user->getUserReadingInterest();

        $settingsForm = $this->createForm(SettingsFormType::class, [$userPersonalInfo, $userReadingInterest]);
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            if (!empty($settingsForm->get('name')->getData())) {
                $userPersonalInfo->setName($settingsForm->get('name')->getData());
            }
            if (!empty($settingsForm->get('surname')->getData())) {
                $userPersonalInfo->setSurname($settingsForm->get('surname')->getData());
            }
            if (!empty($settingsForm->get('nickname')->getData())) {
                $nickname = $settingsForm->get('nickname')->getData();
                $nickname = $entityManager->getRepository(UserPersonalInfo::class)->findOneBy(['nickname' => $nickname]);
                if ($nickname) {
                    $this->addFlash('error', 'Nickname already in use');
                    return $this->redirectToRoute('settings');
                } else {
                    $userPersonalInfo->setNickname($settingsForm->get('nickname')->getData());
                }
            }
            if (!empty($settingsForm->get('languages')->getData())) {
                $userReadingInterest->setLanguages($settingsForm->get('languages')->getData());
            }
            if (!empty($settingsForm->get('genres')->getData())) {
                $userReadingInterest->setGenres($settingsForm->get('genres')->getData());
            }

            // Persist the user reading interest to the database
            $entityManager->persist($userPersonalInfo);
            $entityManager->persist($userReadingInterest);
            $entityManager->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/settings.html.twig', [
            'controller_name' => 'SettingsController',
            'settingsForm' => $settingsForm->createView()
        ]);
    }
}