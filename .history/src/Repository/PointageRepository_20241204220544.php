<?
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class PointageRepository extends EntityRepository
{
    public function getPointagesByDay($day)
    {
        $qb = $this->createQueryBuilder('p');
        
        // Nous appelons getDayOfWeek() pour obtenir le numéro du jour
        $qb->where('DAYOFWEEK(p.heureEntree) = :day')
           ->setParameter('day', $this->getDayOfWeek($day));  // Appel de la méthode getDayOfWeek()

        return $qb->getQuery()->getResult();  // Récupération des résultats
    }

    // Méthode pour obtenir le jour de la semaine sous forme numérique
    public function getDayOfWeek($day)
    {
        $daysOfWeek = [
            'Sunday' => 1,
            'Monday' => 2,
            'Tuesday' => 3,
            'Wednesday' => 4,
            'Thursday' => 5,
            'Friday' => 6,
            'Saturday' => 7,
        ];

        return isset($daysOfWeek[$day]) ? $daysOfWeek[$day] : null;  // Retourne le numéro du jour
    }
}
