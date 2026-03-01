<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacePlusPlusService
{
    private string $apiKey;
    private string $apiSecret;
    private HttpClientInterface $httpClient;

    private const COMPARE_URL = 'https://api-us.faceplusplus.com/facepp/v3/compare';
    private const CONFIDENCE_THRESHOLD = 80.0;

    public function __construct(
        string $faceppApiKey,
        string $faceppApiSecret,
        HttpClientInterface $httpClient
    ) {
        $this->apiKey = $faceppApiKey;
        $this->apiSecret = $faceppApiSecret;
        $this->httpClient = $httpClient;
    }

    /**
     * Compare two face images using Face++ API.
     *
     * @param string $imagePath1 Absolute path to the stored profile image
     * @param string $imagePath2 Absolute path to the login image (uploaded/captured)
     * @return array{match: bool, confidence: float, threshold: float, error: string|null}
     */
    public function compareFaces(string $imagePath1, string $imagePath2): array
    {
        try {
            $response = $this->httpClient->request('POST', self::COMPARE_URL, [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'image_file1' => fopen($imagePath1, 'r'),
                    'image_file2' => fopen($imagePath2, 'r'),
                ],
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
            ]);

            $data = $response->toArray(false);

            if (isset($data['error_message'])) {
                return [
                    'match' => false,
                    'confidence' => 0.0,
                    'threshold' => self::CONFIDENCE_THRESHOLD,
                    'error' => $data['error_message'],
                ];
            }

            $confidence = $data['confidence'] ?? 0.0;

            // Use Face++ thresholds if available, otherwise use our default
            $threshold = $data['thresholds']['1e-5'] ?? self::CONFIDENCE_THRESHOLD;

            return [
                'match' => $confidence >= $threshold,
                'confidence' => $confidence,
                'threshold' => $threshold,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'match' => false,
                'confidence' => 0.0,
                'threshold' => self::CONFIDENCE_THRESHOLD,
                'error' => 'Erreur de connexion à Face++ : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Search all users with a profile image and compare against the provided face.
     *
     * @param string $loginImagePath Absolute path to the captured/uploaded face image
     * @param UserRepository $userRepository
     * @param string $projectDir The kernel.project_dir path
     * @return array{user: User|null, confidence: float, error: string|null}
     */
    public function findMatchingUser(string $loginImagePath, UserRepository $userRepository, string $projectDir): array
    {
        // Get all users that have a profile image
        $users = $userRepository->createQueryBuilder('u')
            ->where('u.profileImage IS NOT NULL')
            ->andWhere('u.isBlocked = false')
            ->getQuery()
            ->getResult();

        if (empty($users)) {
            return [
                'user' => null,
                'confidence' => 0.0,
                'error' => 'Aucun utilisateur avec une photo de profil enregistrée.',
            ];
        }

        $bestMatch = null;
        $bestConfidence = 0.0;

        foreach ($users as $user) {
            $storedImagePath = $projectDir . '/public/' . $user->getProfileImage();

            if (!file_exists($storedImagePath)) {
                continue;
            }

            $result = $this->compareFaces($storedImagePath, $loginImagePath);

            // Skip if Face++ returned an error for this comparison
            if ($result['error']) {
                continue;
            }

            // If it's a match and confidence is higher than previous best
            if ($result['match'] && $result['confidence'] > $bestConfidence) {
                $bestMatch = $user;
                $bestConfidence = $result['confidence'];
            }
        }

        if ($bestMatch) {
            return [
                'user' => $bestMatch,
                'confidence' => $bestConfidence,
                'error' => null,
            ];
        }

        return [
            'user' => null,
            'confidence' => 0.0,
            'error' => null,
        ];
    }
}
