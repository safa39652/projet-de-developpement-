<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;  // Importation de la nouvelle syntaxe

class EventController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route("/", name: "events_get", methods: ["GET"])] // Utilisation de l'attribut Route
    public function getEvents(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();

        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('Y-m-d H:i:s'),
                'end' => $event->getEndDate() ? $event->getEndDate()->format('Y-m-d H:i:s') : null,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route("/add", name: "events_add", methods: ["POST"])] // Utilisation de l'attribut Route
    public function addEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setTitle($data['title']);
        $event->setStartDate(new \DateTime($data['start']));
        if (isset($data['end'])) {
            $event->setEndDate(new \DateTime($data['end']));
        }

        $this->em->persist($event);
        $this->em->flush();

        return new JsonResponse(['status' => 'Event added!'], 201);
    }

    #[Route("/update/{id}", name: "events_update", methods: ["PUT"])] // Utilisation de l'attribut Route
    public function updateEvent($id, Request $request, EventRepository $eventRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $event = $eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['status' => 'Event not found'], 404);
        }

        $event->setTitle($data['title']);
        $event->setStartDate(new \DateTime($data['start']));
        if (isset($data['end'])) {
            $event->setEndDate(new \DateTime($data['end']));
        }

        $this->em->flush();

        return new JsonResponse(['status' => 'Event updated!']);
    }
}
