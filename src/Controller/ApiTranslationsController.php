<?php

namespace App\Controller;

use App\Service\DeepLService;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/translations', name: 'api_translations_')]
class ApiTranslationsController extends AbstractController
{

    #[Route(path: '/deepl', name: 'deepl', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Tag(name: 'Translations')]
    public function deepl(Request $request, DeepLService $deepLService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(
            !isset($payload['sourceLanguage']) ||
            !isset($payload['targetLanguage']) ||
            !isset($payload['fields']) ||
            !is_array($payload['fields'])
        ) {
            return $this->json([
                'error' => 'Invalid translation payload.',
            ], 400);
        }

        if(
            !in_array($payload['sourceLanguage'], ['de', 'fr', 'it']) ||
            !in_array($payload['targetLanguage'], ['de', 'fr', 'it'])
        ) {
            return $this->json([
                'error' => 'Invalid language.',
            ], 400);
        }

        try {

            $translations = $deepLService->translate(
                $payload['fields'],
                $payload['sourceLanguage'],
                $payload['targetLanguage']
            );

            return $this->json([
                'translations' => $translations,
            ]);

        } catch(\Exception $exception) {

            return $this->json([
                'error' => $exception->getMessage(),
            ], 400);

        }
    }

}