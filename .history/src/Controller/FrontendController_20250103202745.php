<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; 
use App\Repository\EmployeRepository;
use App\Repository\NotificationRepository;
use App\Repository\PointageRepository;
use App\Repository\RapportRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\EventRepository;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Entity\Pointage;
use App\Entity\Notification;




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

    #[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]#[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
    public function checkEmployee(Request $request): JsonResponse
    {
        $file = $request->files->get('image');
        if (!$file) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }
    
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $fileName = uniqid() . '.' . $file->guessExtension();
        $file->move($uploadsDir, $fileName);
    
        $imageUrl = $request->getSchemeAndHttpHost() . '/uploads/' . $fileName;
    
        // Appel à l'API Flask pour la reconnaissance faciale
        $recognizeResponse = $this->httpClient->request('POST', 'http://localhost:5000/recognize', [
            'body' => ['image' => fopen("$uploadsDir/$fileName", 'r')],
        ]);
    
        if ($recognizeResponse->getStatusCode() !== 200) {
            return new JsonResponse(['message' => 'Erreur API de reconnaissance'], $recognizeResponse->getStatusCode());
        }
    
        $recognizeData = $recognizeResponse->toArray();
    
        // Appel à l'API Flask pour la détection d'émotion
        $emotionResponse = $this->httpClient->request('POST', 'http://localhost:5000/emotion', [
            'body' => ['image' => fopen("$uploadsDir/$fileName", 'r')],
        ]);
    
        if ($emotionResponse->getStatusCode() !== 200) {
            return new JsonResponse(['message' => 'Erreur API de détection d\'émotion'], $emotionResponse->getStatusCode());
        }
    
        $emotionData = $emotionResponse->toArray();
    
        // Combiner les résultats des deux appels
        $result = [
            'recognition' => $recognizeData,
            'emotion' => $emotionData,
            'capturedImageUrl' => $imageUrl,
        ];
    
        return new JsonResponse($result);
    }
    

    

    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(EmployeRepository $employeRepository, PointageRepository $pointageRepository, RapportRepository $rapportRepository,NotificationRepository $notificationRepository): Response
{
    // Récupérer tous les employés
    $employes = $employeRepository->findAll();
    // Récupérer les pointages de la base de données
    $pointages = $pointageRepository->findAll();

    // Récupérer le nombre d'employés
    $employeCount = $employeRepository->count([]);

    // Récupérer le nombre de pointages
    $pointageCount = $pointageRepository->count([]);

    // Récupérer le nombre de rapports hebdomadaires
    $rapportCount = $rapportRepository->count([]);

    // Récupérer le nombre de notifications (alertes)
    $notificationCount = $notificationRepository->count([]);

    // Informations supplémentaires pour le tableau de bord
    $connectedUsers = 45; // Exemple statique, vous pouvez le dynamiser si nécessaire
    $pendingAlerts = $notificationCount; // Utilisation du nombre de notifications comme alertes en attente

    $presenceByDay = [];

    foreach ($pointages as $pointage) {
        $jour = $pointage->getJour(); // Jour de la semaine (e.g., "Monday", "Tuesday")
        
        if (!isset($presenceByDay[$jour])) {
            $presenceByDay[$jour] = 0;
        }
        $presenceByDay[$jour]++;
    }

    // Passer toutes les données à la vue
    return $this->render('front/dashboard.html.twig', [
        'presenceByDay' => $presenceByDay, 
        'pointages' => $pointages, // Passez les pointages à la vue
        'employes' => $employes, // Passez les employés à la vue
        'employeCount' => $employeCount, // Nombre d'employés
        'pointageCount' => $pointageCount, // Nombre de pointages
        'rapportCount' => $rapportCount, // Nombre de rapports hebdomadaires
        'notificationCount' => $notificationCount, // Nombre d'alertes (notifications)
        'connectedUsers' => $connectedUsers, // Utilisateurs connectés
        'pendingAlerts' => $pendingAlerts, // Alertes en attente
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

    #[Route('/rapports', name: 'rapport_page')]
public function rapportPage(): Response
{
    return $this->render('admin/rapport.html.twig',[
        // Vous pouvez ajouter des données ici si nécessaire
    ]);
}
#[Route('/pointage', name: 'pointage_page')]
public function pointagePage(): Response
{
  

        // Passer les données à la vue
        return $this->render('pointage/liste.html.twig', [
          
        ]);
}


#[Route('/rapport-presence', name: 'rapport_presence', methods: ['GET', 'POST'])]
public function generatePresenceReport(Request $request, PointageRepository $pointageRepository): Response
{
    // Récupérer les paramètres de date depuis le formulaire (semaine, mois, année)
    $dateFilter = $request->request->get('date_filter');
    $startDate = $request->request->get('start_date');
    $endDate = $request->request->get('end_date');
    
    // Filtrer les données de pointage en fonction des dates
    if ($dateFilter === 'week') {
        $pointages = $pointageRepository->findByWeek($startDate, $endDate);
    } elseif ($dateFilter === 'month') {
        $pointages = $pointageRepository->findByMonth($startDate, $endDate);
    } elseif ($dateFilter === 'year') {
        $pointages = $pointageRepository->findByYear($startDate, $endDate);
    } else {
        $pointages = $pointageRepository->findAll();
    }

    // Créer une feuille de calcul
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter les en-têtes
    $headers = ['ID', 'Employé', 'Heure Entrée', 'Heure Sortie', 'Statut', 'Mois', 'Année', 'Jour'];
    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '1', $header);
        $colIndex++;
    }

    // Appliquer un style aux en-têtes
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

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
        $sheet->setCellValue('H' . $row, $pointage->getHeureEntree()?->format('l')); // Jour en anglais (par ex. Monday)
        $row++;
    }

    // Appliquer des bordures à tout le tableau
    $tableStyle = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
    $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray($tableStyle);

    // Ajuster la largeur des colonnes
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Configurer l'écriture du fichier Excel
    $writer = new Xlsx($spreadsheet);
    $fileName = 'rapport_presence.xlsx';
    $tempFile = tempnam(sys_get_temp_dir(), $fileName);
    $writer->save($tempFile);

    // Télécharger le fichier
    return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
}


#[Route('/rapport-evenements', name: 'rapport_evenements', methods: ['GET', 'POST'])]
    public function generateEventReport(Request $request, EventRepository $eventRepository): Response
    {
        // Récupérer les paramètres de date depuis le formulaire (semaine, mois, année)
        $dateFilter = $request->request->get('date_filter');
        $startDate = $request->request->get('start_date');
        $endDate = $request->request->get('end_date');
        
        // Filtrer les données des événements en fonction des dates
        if ($dateFilter === 'week') {
            $events = $eventRepository->findByWeek($startDate, $endDate);
        } elseif ($dateFilter === 'month') {
            $events = $eventRepository->findByMonth($startDate, $endDate);
        } elseif ($dateFilter === 'year') {
            $events = $eventRepository->findByYear($startDate, $endDate);
        } else {
            $events = $eventRepository->findAll();
        }

        // Créer une feuille de calcul
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter les en-têtes
        $headers = ['ID', 'Titre', 'Date Début', 'Date Fin', 'Durée (en heures)'];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '1', $header);
            $colIndex++;
        }

        // Appliquer un style aux en-têtes
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Remplir les données
        $row = 2;
        foreach ($events as $event) {
            $sheet->setCellValue('A' . $row, $event->getId());
            $sheet->setCellValue('B' . $row, $event->getTitle());
            $sheet->setCellValue('C' . $row, $event->getStartDate()?->format('Y-m-d H:i:s'));
            $sheet->setCellValue('D' . $row, $event->getEndDate()?->format('Y-m-d H:i:s'));

            // Calculer la durée en heures
            if ($event->getStartDate() && $event->getEndDate()) {
                $duration = $event->getEndDate()->diff($event->getStartDate());
                $hours = $duration->days * 24 + $duration->h + $duration->i / 60;
                $sheet->setCellValue('E' . $row, round($hours, 2));
            } else {
                $sheet->setCellValue('E' . $row, 'Non disponible');
            }
            $row++;
        }

        // Appliquer des bordures à tout le tableau
        $tableStyle = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $sheet->getStyle('A1:E' . ($row - 1))->applyFromArray($tableStyle);

        // Ajuster la largeur des colonnes
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Configurer l'écriture du fichier Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = 'rapport_evenements.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        // Télécharger le fichier
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/rapport-choisir', name: 'rapport_choisir', methods: ['POST'])]
public function generateReport(Request $request, PointageRepository $pointageRepository, EventRepository $eventRepository): Response
{
    // Récupérer les paramètres depuis le formulaire
    $reportType = $request->request->get('report_type');
    $startDate = $request->request->get('start_date');
    $endDate = $request->request->get('end_date');

    // Convertir les dates en objets DateTime
    $startDate = new \DateTime($startDate);
    $endDate = new \DateTime($endDate);

    // Vérifier le type de rapport et générer le rapport correspondant
    if ($reportType === 'presence') {
        // Filtrer les pointages par date
        $pointages = $pointageRepository->findByDateRange($startDate, $endDate);
        $spreadsheet = $this->generatePresenceSpreadsheet($pointages);
        $fileName = 'rapport_presence.xlsx';
    } elseif ($reportType === 'event') {
        // Filtrer les événements par date
        $events = $eventRepository->findByDateRange($startDate, $endDate);
        $spreadsheet = $this->generateEventSpreadsheet($events);
        $fileName = 'rapport_evenements.xlsx';
    } else {
        return new Response('Type de rapport invalide', 400);
    }

    // Sauvegarder le fichier Excel dans un fichier temporaire
    $tempFile = tempnam(sys_get_temp_dir(), $fileName);
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFile);

    // Télécharger le fichier
    return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
}

    private function generatePresenceSpreadsheet(array $pointages)
    {
        // Générer le rapport de présence (Excel)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['ID', 'Employé', 'Heure Entrée', 'Heure Sortie', 'Statut', 'Mois', 'Année', 'Jour'];
        $this->generateSpreadsheetData($sheet, $headers, $pointages);
        return $spreadsheet;
    }
    private function generateSpreadsheetData($sheet, $headers, $pointages)
{
    // Appliquer un style aux en-têtes
    $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFF00'], // Couleur jaune
        ],
    ];

    // Ajouter les en-têtes avec le style
    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '1', $header);
        $sheet->getStyle($colIndex . '1')->applyFromArray($headerStyle);
        $colIndex++;
    }

    // Remplir les données
    $row = 2;
    foreach ($pointages as $pointage) {
        $sheet->setCellValue('A' . $row, $pointage->getId());
        $sheet->setCellValue('B' . $row, $pointage->getEmploye());
        $sheet->setCellValue('C' . $row, $pointage->getHeureEntree()?->format('Y-m-d H:i:s') ?? 'Non disponible');
        $sheet->setCellValue('D' . $row, $pointage->getHeureSortie()?->format('Y-m-d H:i:s') ?? 'Non disponible');
        $sheet->setCellValue('E' . $row, $pointage->getStatut() ?? 'Non disponible');
        $sheet->setCellValue('F' . $row, $pointage->getMois() ?? 'Non disponible');
        $sheet->setCellValue('G' . $row, $pointage->getAnnee() ?? 'Non disponible');
        $sheet->setCellValue('H' . $row, $pointage->getJour() ?? 'Non disponible');

        // Aligner les colonnes
        $sheet->getStyle("A$row:H$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    // Ajuster automatiquement la largeur des colonnes
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

public function generateEventSpreadsheet($events): Spreadsheet
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter les en-têtes avec style
    $headers = ['ID', 'Titre', 'Date Début', 'Date Fin', 'Durée (en heures)'];
    $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'ADD8E6'], // Couleur bleu clair
        ],
    ];

    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '1', $header);
        $sheet->getStyle($colIndex . '1')->applyFromArray($headerStyle);
        $colIndex++;
    }

    // Remplir les données
    $row = 2;
    foreach ($events as $event) {
        $sheet->setCellValue('A' . $row, $event->getId());
        $sheet->setCellValue('B' . $row, $event->getTitle());
        $sheet->setCellValue('C' . $row, $event->getStartDate() ? $event->getStartDate()->format('Y-m-d H:i:s') : 'Non disponible');
        $sheet->setCellValue('D' . $row, $event->getEndDate() ? $event->getEndDate()->format('Y-m-d H:i:s') : 'Non disponible');
        $sheet->setCellValue('E' . $row, $event->getStartDate() && $event->getEndDate()
            ? $event->getStartDate()->diff($event->getEndDate())->h
            : 'Non disponible');

        // Aligner les colonnes
        $sheet->getStyle("A$row:E$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    // Ajuster automatiquement la largeur des colonnes
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    return $spreadsheet;
}



    
}
    
    



    

    


 
    
