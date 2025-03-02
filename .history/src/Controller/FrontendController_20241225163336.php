<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; 
use App\Repository\EmployeRepository;
use App\Repository\PointageRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class FrontendController extends AbstractController
{
    private $httpClient;
    private $employeRepository;

    public function __construct(HttpClientInterface $httpClient, EmployeRepository $employeRepository)
    {
        $this->httpClient = $httpClient;
        $this->employeRepository = $employeRepository;
    }

    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }

    #[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
    public function checkEmployee(Request $request): JsonResponse
    {
        // Vérifier si un fichier a été téléchargé
        $file = $request->files->get('image');
        if (!$file) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }

        // Définir le chemin où l'image sera stockée
        $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension(); // Générer un nom unique pour l'image
        $file->move($uploadsDirectory, $fileName); // Déplacer le fichier téléchargé dans le dossier uploads

        // Construire l'URL complète de l'image
        $imageUrl = $request->getSchemeAndHttpHost() . '/uploads/' . $fileName;

        // Appeler l'API Python avec l'image enregistrée
        $response = $this->httpClient->request('POST', 'http://localhost:5000/recognize', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'image' => fopen($uploadsDirectory . '/' . $fileName, 'r'),
            ]
        ]);

        $statusCode = $response->getStatusCode();

        // Si l'API Flask répond avec succès
        if ($statusCode === 200) {
            $apiResponse = $response->toArray();

            // Si un employé est reconnu, récupérer ses données dans la base
            if (isset($apiResponse['employee'])) {
                $employeeName = $apiResponse['employee']['name'];

                // Chercher l'employé par le nom détecté
                $employe = $this->employeRepository->findOneBy(['nom' => $employeeName]);

                if ($employe) {
                    // Ajouter les informations supplémentaires de l'employé à la réponse
                    $apiResponse['employee']['poste'] = $employe->getPoste();  // Récupérer le poste de l'employé
                    $apiResponse['employee']['id'] = $employe->getId();  // Récupérer l'id de l'employé
                } else {
                    $apiResponse['employee']['poste'] = 'Inconnu';
                    $apiResponse['employee']['id'] = 'Inconnu';
                }
            }

            // Ajouter l'URL de l'image capturée au retour JSON
            $apiResponse['capturedImageUrl'] = $imageUrl;

            return new JsonResponse($apiResponse);
        } else {
            // Si une erreur se produit dans l'API Flask
            return new JsonResponse(['message' => 'Erreur lors de la reconnaissance'], $statusCode);
        }
    }
    

    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(EmployeRepository $employeRepository, PointageRepository $pointageRepository): Response
{
    // Récupérer le nombre d'employés
    $employeCount = $employeRepository->count([]);

    // Récupérer le nombre de pointages
    $pointageCount = $pointageRepository->count([]);

    // Informations supplémentaires pour le tableau de bord
    $connectedUsers = 45; // Exemple statique, vous pouvez le dynamiser si nécessaire
    $pendingAlerts = 3; // Exemple statique, vous pouvez le dynamiser si nécessaire
    $weeklyReports = 12; // Exemple statique, vous pouvez le dynamiser si nécessaire

    // Passer toutes les données à la vue
    return $this->render('front/dashboard.html.twig', [
        'employeCount' => $employeCount, // Nombre d'employés
        'pointageCount' => $pointageCount, // Nombre de pointages
        'connectedUsers' => $connectedUsers, // Utilisateurs connectés
        'pendingAlerts' => $pendingAlerts, // Alertes en attente
        'weeklyReports' => $weeklyReports, // Rapports hebdomadaires
    ]);
}




    #[Route("/admin/login", name: "admin_login", methods: ["GET", "POST"])]
    public function adminLogin(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
    
        if ($username === 'admin' && $password === 'admin') {
            return $this->redirectToRoute('app_front_dashboard');
        }
    
        return $this->render('admin/login.html.twig');
    }

    #[Route('/rapport-presence', name: 'rapport_presence', methods: ['GET'])]
public function generatePresenceReport(PointageRepository $pointageRepository): Response
{
    // Récupérer les données de pointage depuis la base de données
    $pointages = $pointageRepository->findAll();

    // Créer une feuille de calcul
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter les en-têtes
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Employé');
    $sheet->setCellValue('C1', 'Heure Entrée');
    $sheet->setCellValue('D1', 'Heure Sortie');
    $sheet->setCellValue('E1', 'Statut');
    $sheet->setCellValue('F1', 'Mois');
    $sheet->setCellValue('G1', 'Année');

    // Remplir les données
    $row = 2;
    foreach ($pointages as $pointage) {
        $sheet->setCellValue('A' . $row, $pointage->getId());
        $sheet->setCellValue('B' . $row, $pointage->getEmploye());
        $sheet->setCellValue('C' . $row, $pointage->getHeureEntree()?->format('Y-m-d H:i:s'));
        $sheet->setCellValue('D' . $row, $pointage->getHeureSortie()?->format('Y-m-d H:i:s') ?? 'Non disponible');
        $sheet->setCellValue('E' . $row, $pointage->getStatut());
        $sheet->setCellValue('F' . $row, $pointage->getMois());
        $sheet->setCellValue('G' . $row, $pointage->getAnnee());
        $row++;
    }

    // Configurer l'écriture du fichier Excel
    $writer = new Xlsx($spreadsheet);
    $fileName = 'rapport_presence.xlsx';
    $tempFile = tempnam(sys_get_temp_dir(), $fileName);
    $writer->save($tempFile);

    // Télécharger le fichier
    return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
}

    


 
    
}
