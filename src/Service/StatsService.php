<?php
namespace App\Service;

use App\Entity\Equipe;
use App\Repository\MatchsRepository;
use App\Repository\EquipeRepository;

class StatsService
{
    private $matchsRepository;
    private $equipeRepository;

    public function __construct(MatchsRepository $matchsRepository, EquipeRepository $equipeRepository)
    {
        $this->matchsRepository = $matchsRepository;
        $this->equipeRepository = $equipeRepository;
    }

    /**
     * Calcule les statistiques pour toutes les équipes
     * Retourne un tableau classé par nombre de victoires
     */
    public function getClassementEquipes(): array
    {
        $equipes = $this->equipeRepository->findAll();
        $matchsTermines = $this->matchsRepository->getMatchsWithScores();
        
        $stats = [];
        
        foreach ($equipes as $equipe) {
            $stats[$equipe->getId()] = [
                'equipe' => $equipe,
                'victoires' => 0,
                'defaites' => 0,
                'nuls' => 0,
                'matchs_joues' => 0,
                'points' => 0,
                'buts_pour' => 0,
                'buts_contre' => 0,
                'difference' => 0
            ];
        }
        
        foreach ($matchsTermines as $match) {
            $equipe1 = $match->getEquipe1();
            $equipe2 = $match->getEquipe2();
            $score1 = $match->getScoreEquipe1();
            $score2 = $match->getScoreEquipe2();
            
            if (!$equipe1 || !$equipe2) continue;
            
            $id1 = $equipe1->getId();
            $id2 = $equipe2->getId();
            
            // Mise à jour des buts
            if (isset($stats[$id1])) {
                $stats[$id1]['buts_pour'] += $score1;
                $stats[$id1]['buts_contre'] += $score2;
                $stats[$id1]['difference'] = $stats[$id1]['buts_pour'] - $stats[$id1]['buts_contre'];
            }
            
            if (isset($stats[$id2])) {
                $stats[$id2]['buts_pour'] += $score2;
                $stats[$id2]['buts_contre'] += $score1;
                $stats[$id2]['difference'] = $stats[$id2]['buts_pour'] - $stats[$id2]['buts_contre'];
            }
            
            // Détermination du résultat
            if ($score1 > $score2) {
                // Victoire équipe 1
                if (isset($stats[$id1])) {
                    $stats[$id1]['victoires']++;
                    $stats[$id1]['matchs_joues']++;
                    $stats[$id1]['points'] += 3;
                }
                if (isset($stats[$id2])) {
                    $stats[$id2]['defaites']++;
                    $stats[$id2]['matchs_joues']++;
                }
            } elseif ($score1 < $score2) {
                // Victoire équipe 2
                if (isset($stats[$id1])) {
                    $stats[$id1]['defaites']++;
                    $stats[$id1]['matchs_joues']++;
                }
                if (isset($stats[$id2])) {
                    $stats[$id2]['victoires']++;
                    $stats[$id2]['matchs_joues']++;
                    $stats[$id2]['points'] += 3;
                }
            } else {
                // Match nul
                if (isset($stats[$id1])) {
                    $stats[$id1]['nuls']++;
                    $stats[$id1]['matchs_joues']++;
                    $stats[$id1]['points'] += 1;
                }
                if (isset($stats[$id2])) {
                    $stats[$id2]['nuls']++;
                    $stats[$id2]['matchs_joues']++;
                    $stats[$id2]['points'] += 1;
                }
            }
        }
        
        // Tri par nombre de victoires (décroissant)
        usort($stats, function($a, $b) {
            if ($a['victoires'] != $b['victoires']) {
                return $b['victoires'] - $a['victoires'];
            }
            // Si égalité de victoires, on trie par différence de buts
            return $b['difference'] - $a['difference'];
        });
        
        return $stats;
    }
    
    /**
     * Calcule les statistiques pour une équipe spécifique
     */
    public function getStatsEquipe(Equipe $equipe): array
    {
        $stats = $this->getClassementEquipes();
        
        foreach ($stats as $stat) {
            if ($stat['equipe']->getId() === $equipe->getId()) {
                return $stat;
            }
        }
        
        return [
            'equipe' => $equipe,
            'victoires' => 0,
            'defaites' => 0,
            'nuls' => 0,
            'matchs_joues' => 0,
            'points' => 0,
            'buts_pour' => 0,
            'buts_contre' => 0,
            'difference' => 0
        ];
    }
}