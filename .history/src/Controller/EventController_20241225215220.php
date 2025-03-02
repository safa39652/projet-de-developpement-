<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    // Route pour ajouter un événement
    #[Route('/event/add', name: 'event_add', methods: ['POST'])]
public function createEvent(Request $request, EntityManagerInterface $em): JsonResponse
{
    // Récupérer les données de la requête
    $title = $request->request->get('title');
    $startDateStr = $request->request->get('start');
    $endDateStr = $request->request->get('end');

    // Validation des champs
    if (empty($title) || empty($startDateStr) || empty($endDateStr)) {
        return new JsonResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
    }

    try {
        // Conversion des dates
        $startDate = new \DateTime($startDateStr);
        $endDate = new \DateTime($endDateStr);

        // Vérification que la date de fin est postérieure à la date de début
        if ($startDate > $endDate) {
            return new JsonResponse(['status' => 'error', 'message' => 'End date must be later than start date'], 400);
        }

        // Création de l'événement
        $event = new Event();
        $event->setTitle($title);
        $event->setStartDate($startDate);
        $event->setEndDate($endDate);

        // Enregistrement dans la base de données
        $em->persist($event);
        $em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Event added successfully']);
    } catch (\Exception $e) {
        return new JsonResponse(['status' => 'error', 'message' => 'Invalid date format'], 400);
    }
}


#[Route('/event/list', name: 'event_list', methods: ['GET'])]
public function eventList(EntityManagerInterface $em): JsonResponse
{
    $events = $em->getRepository(Event::class)->findAll();
    $eventData = [];

    foreach ($events as $event) {
        $eventData[] = [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $event->getStartDate()?->format('Y-m-d\TH:i:s'),
            'end' => $event->getEndDate()?->format('Y-m-d\TH:i:s'),
        ];
    }

    return new JsonResponse($eventData);
}

}
