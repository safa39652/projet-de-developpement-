<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FrontendController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }

    #[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
    public function checkEmployee(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['image'])) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }

        try {
            $response = $this->httpClient->request('POST', 'http://localhost:5000/recognize', [
                'json' => [
                    'image_base64' => $data['image']
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $apiResponse = $response->toArray();
                return new JsonResponse([
                    'employee' => $apiResponse['employee'] ?? null,
                    'message' => $apiResponse['message'] ?? 'Reconnu avec succès'
                ]);
            }

            return new JsonResponse(['message' => 'Pas de correspondance'], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }
}
