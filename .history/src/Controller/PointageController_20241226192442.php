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
