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
    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        // Exemple de données dynamiques
        $connectedUsers = 45;
        $pendingAlerts = 3;
        $weeklyReports = 12;

        return $this->render('front/dashboard.html.twig', [
            'connectedUsers' => $connectedUsers,
            'pendingAlerts' => $pendingAlerts,
            'weeklyReports' => $weeklyReports,
        ]);
    }

    // Route pour afficher le profil utilisateur
    #[Route('/front/profile', name: 'app_front_profile')]
    public function profile(): Response
    {
        // Exemple de données utilisateur fictives
        $user = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'role' => 'Admin',
            'joined' => '2024-01-15',
        ];

        return $this->render('front/profile.html.twig', [
            'user' => $user,
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
