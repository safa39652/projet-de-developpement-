<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; 

class FrontendController extends AbstractController
{
    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }
    
    // src/Controller/ApiController.php
    use Symfony\Component\HttpFoundation\File\File;

#[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
public function checkEmployee(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    
    if (!isset($data['image'])) {
        return new JsonResponse(['message' => 'Aucune image reçue'], 400);
    }

    $base64Image = $data['image'];
    // Extraire le contenu de l'image à partir du base64
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
    
    // Vous pouvez ensuite créer un fichier à partir de cette donnée
    $imagePath = '/tmp/uploaded_image.jpg';
    file_put_contents($imagePath, $imageData);
    
    // Logique de reconnaissance de l'employé...
    $employee = [
        'id' => 1,
        'firstName' => 'John',
        'lastName' => 'Doe',
        'photoPath' => '/images/employees/john_doe.jpg',
    ];

    return new JsonResponse([
        'message' => 'Employé reconnu',
        'employee' => $employee
    ]);
}



    

    

    // Page du dashboard (optionnel)
    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('front/dashboard.html.twig');
    }
}
