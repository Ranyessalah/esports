<?php

namespace App\Service;

class MatchPredictionService
{
    public function predict(int $team1, int $team2): ?string
    {
       $python = "C:\\Users\\User\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
$script = "C:\\Users\\User\\OneDrive\\Bureau\\esports_nermine\\esports_nermine\\predict.py";

        $command = "\"$python\" \"$script\" $team1 $team2";

        $output = shell_exec($command . " 2>&1");

        if (!$output) {
            return null;
        }

        return trim($output);
    }
}
