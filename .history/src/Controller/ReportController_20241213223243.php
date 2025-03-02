// src/Controller/ReportController.php

namespace App\Controller;

use App\Service\RapportService; // Corrigez ici pour RapportService
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends AbstractController
{
    private $reportService;

    public function __construct(RapportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * @Route("/report/weekly", name="report_weekly")
     */
    public function weeklyReport(): Response
    {
        $reportData = $this->reportService->generateWeeklyReport();

        return $this->render('admin/reports/weekly.html.twig', [
            'reportData' => $reportData,
        ]);
    }

    /**
     * @Route("/report/monthly", name="report_monthly")
     */
    public function monthlyReport(): Response
    {
        $reportData = $this->reportService->generateMonthlyReport();

        return $this->render('admin/reports/monthly.html.twig', [
            'reportData' => $reportData,
        ]);
    }
}
