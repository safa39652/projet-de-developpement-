<?php namespace App\Controller;

use Psr\Log\LoggerInterface;  // Assurez-vous d'importer le LoggerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pointage;
use App\Entity\Employe;
use Symfony\Component\HttpFoundation\Response; 

class PointageController extends AbstractController
{
    private LoggerInterface $logger;  // Déclarer le logger

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;  // L'injection du service LoggerInterface
    }
    #[Route('/enregistrer-pointage', name: 'enregistrer_pointage', methods: ['POST'])]
public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $this->logger->info('Données reçues : ' . json_encode($data));

    if (!isset($data['employe']) || !isset($data['statut']) || !isset($data['photo'])) {
        return new JsonResponse(['message' => 'Données invalides'], 400);
    }

    $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $data['employe']]);
    if (!$employe) {
        return new JsonResponse(['message' => 'Employé inconnu'], 404);
    }

    $pointage = $entityManager->getRepository(Pointage::class)
        ->findOneBy(['employe' => $data['employe'], 'heureSortie' => null]);

    if ($pointage) {
        $pointage->setHeureSortie(new \DateTime());
        $entityManager->flush();
        $this->logger->info('Heure de sortie enregistrée pour : ' . $data['employe']);
        return new JsonResponse(['message' => 'Heure de sortie enregistrée avec succès']);
    } else {
        $pointage = new Pointage();
        $pointage->setEmploye($data['employe']);
        $pointage->setHeureEntree(new \DateTime());
        $pointage->setStatut($data['statut']);
        $pointage->setJour((new \DateTime())->format('l'));

        // Ajouter le chemin de la photo (chemin fourni depuis les données reçues)
        $photoPath = 'uploads/' . $data['photo']; // Assurez-vous que `photo` contient le nom du fichier
        $pointage->setPhoto($photoPath);

        $heureEntreeLimite = new \DateTime('08:00');
        $heureSortieLimite = new \DateTime('17:00');
        $heureEntree = $pointage->getHeureEntree();

        if ($heureEntree > $heureEntreeLimite) {
            $pointage->setStatut('Retard');
        } elseif ($pointage->getHeureSortie() && $pointage->getHeureSortie() < $heureSortieLimite) {
            $pointage->setStatut('Sortie avant l\'heure');
        } elseif ($heureEntree <= $heureEntreeLimite && (!$pointage->getHeureSortie() || $pointage->getHeureSortie() >= $heureSortieLimite)) {
            $pointage->setStatut('Présence');
        } else {
            $pointage->setStatut('Absent');
        }

        $entityManager->persist($pointage);
        $entityManager->flush();
        $this->logger->info('Pointage enregistré avec succès pour : ' . $data['employe']);

        return new JsonResponse(['message' => 'Pointage enregistré avec succès !']);
    }
}

    #[Route('/pointage/liste', name: 'pointage_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        $pointages = $entityManager->getRepository(Pointage::class)->findAll();
        return $this->render('pointage/liste.html.twig', [
            'pointages' => $pointages,
        ]);
    }
     
    
      
}
