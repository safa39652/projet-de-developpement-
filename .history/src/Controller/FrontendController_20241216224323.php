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
    // Récupérer tous les employés
    $employes = $employeRepository->findAll();

    // Récupérer les pointages de la base de données
    $pointages = $pointageRepository->findAll();

    // Informations supplémentaires pour le tableau de bord
    $connectedUsers = 45;
    $pendingAlerts = 3;
    $weeklyReports = 12;

    // Passer la variable 'employes' à la vue
    return $this->render('front/dashboard.html.twig', [
        'employes' => $employes, // Passez les employés à la vue
        'pointages' => $pointages, // Passez les pointages à la vue
        'connectedUsers' => $connectedUsers,
        'pendingAlerts' => $pendingAlerts,
        'weeklyReports' => $weeklyReports
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
    
    #[Route('/admin/rapport/export', name: 'admin_rapport_export')]
public function exportExcel(EmployeRepository $employeRepo, PointageRepository $pointageRepo)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'Employé');
    $sheet->setCellValue('B1', 'Heure d\'Entrée');
    $sheet->setCellValue('C1', 'Heure de Sortie');
    $sheet->setCellValue('D1', 'Statut');

    // Fetch data
    $pointages = $pointageRepo->findAll();
    $row = 2;

    foreach ($pointages as $pointage) {
        // Check if the employee exists and has a valid name
        $employe = $pointage->getEmploye();
        
        if ($employe && method_exists($employe, 'getNom')) {
            $sheet->setCellValue('A' . $row, $employe->getNom());
        } else {
            $sheet->setCellValue('A' . $row, 'Employé non trouvé');
        }

        // Check for entry and exit times
        $sheet->setCellValue('B' . $row, $pointage->getHeureEntree() ? $pointage->getHeureEntree()->format('H:i') : 'Non définie');
        $sheet->setCellValue('C' . $row, $pointage->getHeureSortie() ? $pointage->getHeureSortie()->format('H:i') : 'Non définie');
        
        // Define status based on presence
        $sheet->setCellValue('D' . $row, $pointage->getStatut() ?? 'Non défini');
        
        $row++;
    }

    // Export the file
    $writer = new Xlsx($spreadsheet);
    $fileName = 'rapport_presence.xlsx';
    $tempFile = tempnam(sys_get_temp_dir(), $fileName);
    $writer->save($tempFile);

    return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
}

 
    
}
