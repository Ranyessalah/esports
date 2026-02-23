<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ai')]
#[IsGranted('ROLE_COACH')]
class TrainingAiController extends AbstractController
{
    #[Route('/generate-course', name: 'app_ai_generate_course', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $prompt = $data['prompt'] ?? '';

        if (empty($prompt)) {
            return new JsonResponse(['error' => 'Please provide a goal for the training.'], 400);
        }

        // Professional Simulated AI Engine for Football Training
        // In a production environment, this would call OpenAI or a similar LLM API
        
        $course = $this->simulateAiLogic($prompt);

        return new JsonResponse($course);
    }

    private function simulateAiLogic(string $prompt): array
    {
        $prompt = strtolower($prompt);
        
        // Base structure
        $title = "Advanced Tactical Session";
        $theme = "Tactical Development";
        
        // Keyword detection for better simulation
        if (str_contains($prompt, 'counter') || str_contains($prompt, 'attack')) {
            $title = "Explosive Counter-Attack Mastery";
            $theme = "Attacking Transitions";
            $description = "Focus on rapid vertical progression and clinical finishing after regaining possession.\n\n" .
                "⚽ PHASE 1: DYNAMIC WARMUP (15m)\n" .
                "- Integrated passing patterns with 3rd man runs.\n" .
                "- Dynamic stretching focusing on explosive movements.\n\n" .
                "⚽ PHASE 2: TECHNICAL DRILL (25m)\n" .
                "- 3 vs 2 transition drills starting from the center circle.\n" .
                "- Focus on 'The First Pass' speed and wide overlaps.\n\n" .
                "⚽ PHASE 3: GAME SITUATION (30m)\n" .
                "- 6 vs 6 + 2 Jokers on a 60-yard pitch.\n" .
                "- Goals scored within 10 seconds of transition count triple.\n\n" .
                "💡 COACH TIPS:\n" .
                "- Demand maximum speed in the first 3 seconds after winning the ball.\n" .
                "- Look for vertical gaps before horizontal options.";
        } elseif (str_contains($prompt, 'defense') || str_contains($prompt, 'defending')) {
            $title = "Compact Defensive Block & Pressure";
            $theme = "Defensive Organization";
            $description = "Organizing the back line to minimize space between lines and master the offside trap.\n\n" .
                "⚽ PHASE 1: COHESION WARMUP (15m)\n" .
                "- Shadow play defending in units of 4.\n\n" .
                "⚽ PHASE 2: THE BLOCK (25m)\n" .
                "- 4 vs 5 'Overload Defense' in the final third.\n" .
                "- Mastery of sliding and screening passes.\n\n" .
                "⚽ PHASE 3: PRESSURE GAME (30m)\n" .
                "- Possession vs Pressing square.\n\n" .
                "💡 COACH TIPS:\n" .
                "- Focus on body orientation and communication between center-backs.";
        } else {
            $title = "Custom Skill Development: " . ucfirst($prompt);
            $theme = "Individual & Team Growth";
            $description = "A specialized session tailored to: " . $prompt . "\n\n" .
                "⚽ PHASE 1: WARMUP\n- Standard technical coordination circuit.\n\n" .
                "⚽ PHASE 2: MAIN WORK\n- High-repetition drills focusing on the requested goal.\n\n" .
                "⚽ PHASE 3: APPLICATION\n- Small-sided game (4v4) with constraints related to " . $prompt . ".\n\n" .
                "💡 COACH TIPS:\n- Encourage creativity and repeated execution of the core skill.";
        }

        return [
            'title' => $title,
            'theme' => $theme,
            'description' => $description
        ];
    }
}
