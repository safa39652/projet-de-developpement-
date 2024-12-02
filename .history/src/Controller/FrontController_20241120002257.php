<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    // Route principale pour la page d'accueil du front-office
    #[Route('/front', name: 'app_front')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    // Route pour le tableau de bord
   

    // Route pour afficher le profil utilisateur
    #[Route('/front/profile', name: 'app_front_profile')]

    public function profile(): Response
    {
        // Exemple de données utilisateur fictives
        $admin = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'gender' => 'Homme',
            'birthdate' => '1988-05-15',
            'country' => 'États-Unis',
            'postal_code' => '12345',
            'language' => 'Français',
        ];
    
        return $this->render('admin/profile.html.twig', [
            'admin' => $admin,
        ]);
    }
    
    
    // Route pour afficher les notifications
    #[Route('/front/notifications', name: 'app_front_notifications')]
    public function notifications(): Response
    {
        // Exemple de notifications dynamiques
        $notifications = [
            ['type' => 'info', 'message' => 'System update completed successfully.'],
            ['type' => 'warning', 'message' => 'Your password will expire in 5 days.'],
            ['type' => 'error', 'message' => 'Failed to connect to the database.'],
        ];

        return $this->render('front/notifications.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}
