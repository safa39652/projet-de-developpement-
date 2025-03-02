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
    #[Route('/pointer',name:'pointage_list')]
    public function index2():Response
    {
        return $this->render('pointage/liste.html.twig');
    }
   

    // Route pour afficher le profil utilisateur
  #[Route('/front/profile', name: 'app_front_profile')]
public function profile(): Response
{
    // Exemple de données utilisateur fictives
    $admin = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'role' => 'Admin',
        'joined' => '2024-01-15',
        'gender' => 'Male', // Ajouté
        'firstname' => 'John', // Ajouté
        'lastname' => 'Doe', // Ajouté
        'birth_month' => 1, // Ajouté
        'birth_day' => 15, // Ajouté
        'birth_year' => 1985, // Ajouté
        'country' => 'USA', // Ajouté
        'zipcode' => '12345', // Ajouté
        'language' => 'en', // Ajouté
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
