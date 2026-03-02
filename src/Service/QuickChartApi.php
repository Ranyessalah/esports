<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class QuickChartApi
{
    private HttpClientInterface $client;
    private FilesystemAdapter $cache;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->cache = new FilesystemAdapter('charts', 3600);
    }

    /**
     * Appel API externe QuickChart
     */
    public function generatePerformanceChart(array $stats, string $teamName): string
    {
        // clé cache (évite spam API)
        $cacheKey = md5($teamName . json_encode($stats));

        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $chartConfig = [
            "type" => "doughnut",
            "data" => [
                "labels" => ["Victoires", "Nuls", "Défaites"],
                "datasets" => [[
                    "data" => [
                        $stats['victoires'],
                        $stats['nuls'],
                        $stats['defaites']
                    ],
                    "backgroundColor" => [
                        "#22c55e",
                        "#facc15",
                        "#ef4444"
                    ]
                ]]
            ],
            "options" => [
                "plugins" => [
                    "legend" => [
                        "position" => "bottom"
                    ],
                    "title" => [
                        "display" => true,
                        "text" => "Performance - " . $teamName
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->request(
                'POST',
                'https://quickchart.io/chart',
                [
                    'json' => [
                        'width' => 650,
                        'height' => 400,
                        'chart' => $chartConfig
                    ]
                ]
            );

            $imageContent = $response->getContent();

        } catch (\Exception $e) {
            // fallback si API down
            return '/images/chart_error.png';
        }

        // Sauvegarde locale
        $fileName = 'chart_' . uniqid() . '.png';
        $path = __DIR__ . '/../../public/charts/' . $fileName;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $imageContent);

        $url = '/charts/' . $fileName;

        // mise en cache
        $item->set($url);
        $this->cache->save($item);

        return $url;
    }
}
