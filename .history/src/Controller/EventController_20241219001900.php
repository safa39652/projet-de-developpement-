<?
namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event/add', name: 'event_add', methods: ['POST'])]
    public function addEvent(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $title = $request->request->get('title');
        $startDate = new \DateTime($request->request->get('start'));
        $endDate = new \DateTime($request->request->get('end'));

        $event = new Event();
        $event->setTitle($title);
        $event->setStartDate($startDate);
        $event->setEndDate($endDate);

        $em->persist($event);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }

    // Route pour récupérer tous les événements
    #[Route('/event/list', name: 'event_list', methods: ['GET'])]
    public function getEvents(EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(Event::class)->findAll();
        $eventData = [];

        foreach ($events as $event) {
            $eventData[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
                'end' => $event->getEndDate()->format('Y-m-d\TH:i:s'),
            ];
        }

        return new JsonResponse($eventData);
    }
}
