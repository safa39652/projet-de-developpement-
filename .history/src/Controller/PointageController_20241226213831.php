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

    public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    // Décoder les données reçues
    $data = json_decode($request->getContent(), true);

    // Log pour déboguer les données
    $this->logger->info('Données reçues : ' . json_encode($data));

    // Vérification des données nécessaires
    if (!isset($data['employe']) || !isset($data['statut']) || !isset($data['photo'])) {
        return new JsonResponse(['message' => 'Données invalides'], 400);
    }

    // Vérification si l'employé existe
    $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $data['employe']]);
    if (!$employe) {
        return new JsonResponse(['message' => 'Employé inconnu'], 404);
    }

    // Gestion de la photo
    $photoBase64 = $data['photo']; // La photo est supposée être envoyée en base64
    $photoPath = $this->savePhoto($photoBase64);

    if (!$photoPath) {
        return new JsonResponse(['message' => 'Erreur lors de l\'enregistrement de la photo'], 500);
    }

    // Création d'un nouvel enregistrement de pointage
    $pointage = new Pointage();
    $pointage->setEmploye($data['employe']);
    $pointage->setHeureEntree(new \DateTime());
    $pointage->setStatut($data['statut']);
    $pointage->setJour((new \DateTime())->format('l'));
    $pointage->setPhoto($photoPath);

    // Calcul du statut
    $heureEntreeLimite = new \DateTime('08:00');
    if ($pointage->getHeureEntree() > $heureEntreeLimite) {
        $pointage->setStatut('Retard');
    } else {
        $pointage->setStatut('Présence');
    }

    // Persist et flush
    $entityManager->persist($pointage);
    $entityManager->flush();

    $this->logger->info('Pointage enregistré : ' . json_encode($pointage));

    return new JsonResponse(['message' => 'Pointage enregistré avec succès !']);
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
