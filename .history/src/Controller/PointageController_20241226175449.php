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
    
            // Calculer le jour de la semaine
            $jour = (new \DateTime())->format('l'); // Récupère le jour en anglais (ex: "Monday")
            $pointage->setJour($jour); // Enregistrer le jour
    
            // Calculer le statut (retard, sortie avant l'heure, présence, absent)
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
            $entityManager->flush(); // Sauvegarder dans la base
    
            $this->logger->info('Pointage enregistré avec succès pour : ' . $data['employe']);
            
            return new JsonResponse(['message' => 'Pointage enregistré avec succès !']);
        }
    }
    #[Route('/pointage/liste', name: 'pointage_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        // Fetch all pointages
        $pointages = $entityManager->getRepository(Pointage::class)->findAll();

        // Render the template and pass the data
        return $this->render('pointage/liste.html.twig', [
            'pointages' => $pointages,
        ]);
    }
    
      
}
