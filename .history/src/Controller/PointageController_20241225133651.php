<?php namespace App\Controller;

use Psr\Log\LoggerInterface;  // Assurez-vous d'importer le LoggerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pointage;
use App\Entity\Employe;

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
        // Décoder les données reçues
        $data = json_decode($request->getContent(), true);
    
        // Log pour déboguer les données
        $this->logger->info('Données reçues : ' . json_encode($data));
    
        // Vérification des données nécessaires
        if (!isset($data['employe']) || !isset($data['statut'])) {
            return new JsonResponse(['message' => 'Données invalides'], 400);
        }
    
        // Vérification si l'employé existe dans la base de données
        $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $data['employe']]);
    
        if (!$employe) {
            // Si l'employé n'existe pas, retourner une erreur
            return new JsonResponse(['message' => 'Employé inconnu'], 404);
        }
    
        // Recherche du pointage existant pour cet employé
        $pointage = $entityManager->getRepository(Pointage::class)
                                  ->findOneBy(['employe' => $data['employe'], 'heureSortie' => null]);
    
        if ($pointage) {
            // Si un pointage existe sans heure de sortie, enregistrer l'heure de sortie
            $pointage->setHeureSortie(new \DateTime()); // L'heure actuelle
            $entityManager->flush(); // Sauvegarder dans la base
            $this->logger->info('Heure de sortie enregistrée pour : ' . $data['employe']);
            
            return new JsonResponse(['message' => 'Heure de sortie enregistrée avec succès']);
        } else {
            // Sinon, il s'agit d'un pointage d'entrée (création d'un nouveau pointage)
            $pointage = new Pointage();
            $pointage->setEmploye($data['employe']);
            $pointage->setHeureEntree(new \DateTime()); // L'heure actuelle
            $pointage->setStatut($data['statut']);
            $entityManager->persist($pointage);
            $entityManager->flush(); // Sauvegarde dans la base
    
            // Vérifier si le pointage a bien été enregistré
            if ($pointage->getId() === null) {
                return new JsonResponse(['message' => 'Erreur lors de l\'enregistrement du pointage'], 500);
            }
    
            $this->logger->info('Pointage enregistré avec succès pour : ' . $data['employe']);
            
            return new JsonResponse(['message' => 'Pointage enregistré avec succès !']);
        }
    }
    
      
}
