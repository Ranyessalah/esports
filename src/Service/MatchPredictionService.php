<?php

namespace App\Service;

class MatchPredictionService
{
    public function predict(int $team1, int $team2): ?string
    {
        $python = "C:/Program Files/Python312/python.exe";
        $script = "C:/Users/Administrator/Desktop/esports_nermine/predict.py";
    
        $command = escapeshellcmd("\"$python\" \"$script\" $team1 $team2");
    
        $output = shell_exec($command);
    
        return $output ?: "Prediction unavailable";
    }
}
