<?

namespace App\Service;

use App\Repository\EmployeRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;

class RapportService
{
    private $em;
    private $employeRepository;
    private $pointageRepository;

    public function __construct(EntityManagerInterface $em, EmployeRepository $employeRepository, PointageRepository $pointageRepository)
    {
        $this->em = $em;
        $this->employeRepository = $employeRepository;
        $this->pointageRepository = $pointageRepository;
    }

    public function generateWeeklyReport()
    {
        // Exemple de récupération des données pour un rapport hebdomadaire
        $employes = $this->employeRepository->findAll();
        $pointages = $this->pointageRepository->findThisWeek(); // Méthode à définir dans le repository

        // Vous pouvez ici générer un tableau ou un fichier CSV/PDF avec ces données
        $reportData = [
            'employes' => $employes,
            'pointages' => $pointages,
        ];

        return $reportData;
    }

    public function generateMonthlyReport()
    {
        // Idem pour un rapport mensuel
        $employes = $this->employeRepository->findAll();
        $pointages = $this->pointageRepository->findThisMonth(); // Méthode à définir dans le repository

        $reportData = [
            'employes' => $employes,
            'pointages' => $pointages,
        ];

        return $reportData;
    }
}
