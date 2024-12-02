<?
namespace App\Controller;

use App\Entity\Employe;
use App\Form\EmployeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/employe/list', name: 'employe_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $employes = $em->getRepository(Employe::class)->findAll();
        return $this->render('employe/index.html.twig', ['employes' => $employes]);
    }

    #[Route('/employe/new', name: 'new_employe', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $employe = new Employe();
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $employe->setPhoto($newFilename);
                } catch (FileException $e) {
                    throw new \Exception('Erreur lors de l\'upload de la photo.');
                }
            }

            $em->persist($employe);
            $em->flush();

            return $this->redirectToRoute('employe_list');
        }

        return $this->render('employe/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/employe/{id}', name: 'employe_show')]
    public function show($id, EntityManagerInterface $em): Response
    {
        $employe = $em->getRepository(Employe::class)->find($id);

        if (!$employe) {
            throw $this->createNotFoundException('Employé non trouvé');
        }

        return $this->render('employe/show.html.twig', ['employe' => $employe]);
    }
}
