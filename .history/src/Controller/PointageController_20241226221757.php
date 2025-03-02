<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
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
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/enregistrer-pointage', name: 'enregistrer_pointage', methods: ['POST'])]
    #[Route('/enregistrer-pointage', name: 'enregistrer_pointage', methods: ['POST'])]
    public function enregistrerPointage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Décoder les données JSON
        $data = json_decode($request->getContent(), true);
    
        // Récupérer le fichier photo si envoyé
        $photoFile = $request->files->get('photo');
        $photoPath = null;
    
        if ($photoFile) {
            // Définir un répertoire d'upload
            $uploadDir = $this->getParameter('upload_directory');
    
            // Générer un nom de fichier unique
            $fileName = uniqid() . '.' . $photoFile->guessExtension();
    
            // Déplacer le fichier dans le répertoire d'upload
            $photoFile->move($uploadDir, $fileName);
    
            // Conserver le chemin du fichier
            $photoPath = $uploadDir . '/' . $fileName;
        }
    
        // Vérifier que les autres champs sont valides
        if (!isset($data['employe'], $data['statut'])) {
            return new JsonResponse(['message' => 'Données invalides'], 400);
        }
    
        // Récupérer l'employé dans la base de données
        $employe = $entityManager->getRepository(Employe::class)->findOneBy(['nom' => $data['employe']]);
        if (!$employe) {
            return new JsonResponse(['message' => 'Employé inconnu'], 404);
        }
    
        // Enregistrer le pointage
        $pointage = new Pointage();
        $pointage->setEmploye($data['employe']);
        $pointage->setHeureEntree(new \DateTime());
        $pointage->setPhoto($photoPath); // Associer le chemin de la photo
    
        $entityManager->persist($pointage);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Pointage enregistré avec succès !']);
    }
    

    #[Route('/pointage/liste', name: 'pointage_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les pointages
        $pointages = $entityManager->getRepository(Pointage::class)->findAll();

        // Rendre le template avec les données
        return $this->render('pointage/liste.html.twig', [
            'pointages' => $pointages,
        ]);
    }
}
