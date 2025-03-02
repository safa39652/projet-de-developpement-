<?
namespace App\Service;

use App\Repository\PointageRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

class RapportService
{
    private PointageRepository $pointageRepository;

    public function __construct(PointageRepository $pointageRepository)
    {
        $this->pointageRepository = $pointageRepository;
    }

    public function genererRapport(string $employe, \DateTime $startDate, \DateTime $endDate): string
    {
        // Récupérer les données
        $pointages = $this->pointageRepository->findByEmployeAndDateRange($employe, $startDate, $endDate);

        // Générer le contenu HTML du rapport
        $html = '<h1>Rapport de présence pour ' . $employe . '</h1>';
        $html .= '<table border="1" cellspacing="0" cellpadding="5">';
        $html .= '<tr><th>Date</th><th>Heure d\'entrée</th><th>Heure de sortie</th><th>Statut</th></tr>';

        foreach ($pointages as $pointage) {
            $html .= sprintf(
                '<tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                </tr>',
                $pointage->getHeureEntree()->format('Y-m-d'),
                $pointage->getHeureEntree()->format('H:i:s'),
                $pointage->getHeureSortie() ? $pointage->getHeureSortie()->format('H:i:s') : 'Non défini',
                $pointage->getStatut()
            );
        }

        $html .= '</table>';

        // Convertir en PDF avec Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Sauvegarder le PDF dans un fichier
        $output = $dompdf->output();
        $filePath = 'rapport_presence_' . $employe . '_' . $startDate->format('Y-m-d') . '_au_' . $endDate->format('Y-m-d') . '.pdf';
        file_put_contents($filePath, $output);

        return $filePath;
    }
}
