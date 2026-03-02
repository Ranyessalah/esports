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
 * @param array<int,array{wins:int,losses:int,draws:int}> $stats
 */

public function generatePerformanceChart(array $stats, string $teamName): string
{
    if (empty($stats)) {
        return '/images/chart_error.png';
    }

    // 🔥 NEW STRUCTURE
    $wins   = $stats[0]['wins'];
    $draws  = $stats[0]['draws'];
    $losses = $stats[0]['losses'];

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
                    $wins,
                    $draws,
                    $losses
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
        return '/images/chart_error.png';
    }

    $fileName = 'chart_' . uniqid() . '.png';
    $path = __DIR__ . '/../../public/charts/' . $fileName;

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    file_put_contents($path, $imageContent);

    $url = '/charts/' . $fileName;

    $item->set($url);
    $this->cache->save($item);

    return $url;
}
}
