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
    $file = $request->files->get('photo'); // Vérifier si une image a été envoyée

    // Log pour vérifier les données reçues
    $this->logger->info('Données reçues : ' . json_encode($data));

    if (!$file || !isset($data['employe']) || !isset($data['statut'])) {
        return new JsonResponse(['message' => 'Données ou photo manquantes'], 400);
    }

    // Chemin d'upload
    $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
    $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
    $file->move($uploadsDirectory, $fileName);

    $photoPath = '/uploads/' . $fileName;

    // Vérification de l'existence de l'employé
    $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $data['employe']]);
    if (!$employe) {
        return new JsonResponse(['message' => 'Employé inconnu'], 404);
    }

    // Création ou mise à jour du pointage
    $pointage = $entityManager->getRepository(Pointage::class)
                              ->findOneBy(['employe' => $data['employe'], 'heureSortie' => null]);

    if ($pointage) {
        $pointage->setHeureSortie(new \DateTime());
        $pointage->setPhoto($photoPath); // Ajouter la photo
    } else {
        $pointage = new Pointage();
        $pointage->setEmploye($data['employe']);
        $pointage->setHeureEntree(new \DateTime());
        $pointage->setPhoto($photoPath); // Ajouter la photo
        $pointage->setStatut($data['statut']);
    }

    $entityManager->persist($pointage);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Pointage enregistré avec succès', 'photo' => $photoPath]);
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
