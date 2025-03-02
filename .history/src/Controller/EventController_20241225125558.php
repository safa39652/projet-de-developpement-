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
        // Récupération des données de la requête
        $title = $request->request->get('title');
        $startDateStr = $request->request->get('start');
  

        // Validation des champs obligatoires
        if (empty($title) || empty($startDateStr) || empty($endDateStr)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
        }

        // Conversion des dates
            $startDate = new \DateTime($startDateStr);
           

            

        // Création de l'événement
        $event = new Event();
        $event->setTitle($title);
        $event->setStartDate($startDate);
     

        // Enregistrement dans la base de données
        $em->persist($event);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }

    // Route pour récupérer tous les événements
    #[Route('/event/list', name: 'event_list', methods: ['GET'])]
    public function eventList(EntityManagerInterface $em): JsonResponse
    {
        // Récupération de tous les événements
        $events = $em->getRepository(Event::class)->findAll();
        $eventData = [];

        // Formatage des données pour la réponse JSON
        foreach ($events as $event) {
            $eventData[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
               
            ];
        }

        return new JsonResponse($eventData);
    }
}
