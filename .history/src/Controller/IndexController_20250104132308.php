<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EmployeRepository;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Employe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EmployeType;  // Importation de la classe de formulaire EmployeType
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
class IndexController extends AbstractController
{


    
    #[Route('/employe/list', name: "employe_list")]
    public function home(EntityManagerInterface $em): Response
    {
        // Récupération de tous les employés
        $employes = $em->getRepository(Employe::class)->findAll();
        return $this->render('employe/index.html.twig', ['employes' => $employes]);
    }

    #[Route('/employe/new', name: 'new_employe', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Créer un nouvel employé
        $employe = new Employe();
        $form = $this->createForm(EmployeType::class, $employe);

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer l'employé dans la base de données
            $em->persist($employe);
            $em->flush();

            // Rediriger vers la liste des employés après enregistrement
            return $this->redirectToRoute('employe_list');
        }

        return $this->render('employe/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/api/employe/{id}', name: 'api_employe_show', methods: ['GET'])]
    public function show($id, EntityManagerInterface $em): JsonResponse
    {
        // Rechercher l'employé par son identifiant
        $employe = $em->getRepository(Employe::class)->find($id);
    
        // Si l'employé n'existe pas, retourner une erreur 404 avec un message
        if (!$employe) {
            return new JsonResponse(['message' => 'Employé non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        // Préparer les données de l'employé en réponse JSON
        $data = [
            'id' => $employe->getId(),
            'name' =>$employe->getNom(),
            'poste' =>$employe->getPoste(),
            
            // Ajoutez d'autres champs si nécessaire
        ];
    
        // Retourner les données au format JSON avec un code HTTP 200
        return $this->render('employe/show.html.twig');
    }
    

    #[Route('/employe/edit/{id}', name: 'edit_employe', methods: ['GET', 'POST'])]
public function edit(Request $request, $id, EntityManagerInterface $em): Response
{
    // Récupérer l'employé à modifier
    $employe = $em->getRepository(Employe::class)->find($id);

    // Vérifier si l'employé existe
    if (!$employe) {
        throw $this->createNotFoundException('Employé non trouvé');
    }

    // Créer le formulaire pour modifier l'employé
    $form = $this->createForm(EmployeType::class, $employe);

    // Traitement du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder les modifications dans la base de données
        $em->flush();

        // Rediriger vers la liste des employés après modification
        return $this->redirectToRoute('employe_list');
    }

    // Passer l'employe à la vue en plus du formulaire
    return $this->render('employe/edit.html.twig', [
        'form' => $form->createView(),
        'employee' => $employe,  // Passer l'employe à Twig
    ]);
}


    #[Route('/employe/delete/{id}', name: 'delete_employe', methods: ['GET'])]
    public function delete($id, EntityManagerInterface $em): Response
    {
        // Récupérer l'employé à supprimer
        $employe = $em->getRepository(Employe::class)->find($id);

        // Vérifier si l'employé existe
        if (!$employe) {
            throw $this->createNotFoundException('Employé non trouvé');
        }

        // Supprimer l'employé
        $em->remove($employe);
        $em->flush();

        // Rediriger vers la liste des employés après suppression
        return $this->redirectToRoute('employe_list');
    }







    #[Route('/api/employe/add', name: 'api_employe_add', methods: ['POST'])]
public function apiAdd(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $employe = new Employe();
    $employe->setNom($data['nom']);
    $employe->setPoste($data['poste']);

    $em->persist($employe);
    $em->flush();

    // Retourner les données de l'employé ajouté sous forme JSON
    return new JsonResponse([
        'id' => $employe->getId(),
        'nom' => $employe->getNom(),
        'poste' => $employe->getPoste(),
    ]);
}


}
