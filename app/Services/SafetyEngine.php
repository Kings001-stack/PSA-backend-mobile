<?php

namespace App\Services;

use App\EscalationReason;

class SafetyEngine
{
    /**
     * Safety patterns for detecting harmful queries.
     */
    protected array $patterns = [
        'dosage_prescription' => [
            '/how\s+(much|many).*(should|can|do).+take/i',
            '/what.*(dosage|dose)/i',
            '/(dosage|dose).*(for|of|on)/i',
            '/prescribe|prescription/i',
            '/increase.*dose/i',
            '/double.*dose/i',
            '/how.*take.*(tablet|pill|capsule|ml|mg)/i',
        ],
        'child_safety' => [
            '/for.*(child|baby|kid|toddler|infant)/i',
            '/child(ren)?.*(dosage|dose|medication)/i',
            '/pediatric/i',
            '/(baby|infant).*medicine/i',
        ],
        'pregnancy_safety' => [
            '/pregnant|pregnancy/i',
            '/breastfeeding|nursing/i',
            '/expecting\s+(a\s+)?baby/i',
            '/while\s+pregnant/i',
        ],
        'controlled_substance' => [
            '/opioid|morphine|fentanyl|oxycodone|hydrocodone/i',
            '/xanax|valium|ativan|klonopin/i',
            '/adderall|ritalin|vyvanse/i',
            '/controlled\s+substance/i',
        ],
        'diagnosis_attempt' => [
            '/what\s+(is\s+)?wrong\s+with\s+me/i',
            '/do\s+i\s+have/i',
            '/diagnose|diagnosis/i',
            '/what\s+(disease|condition|illness)/i',
            '/am\s+i\s+(sick|ill)/i',
        ],
        'drug_interaction' => [
            '/can\s+i\s+take\s+.+\s+with\s+.+/i',
            '/drug\s+interaction/i',
            '/mix(ing)?\s+(with|and)/i',
            '/combine\s+with/i',
        ],
        'emergency' => [
            '/emergency|urgent/i',
            '/chest\s+pain/i',
            '/heart\s+attack|stroke/i',
            '/can\'?t\s+breathe/i',
            '/severe\s+(pain|bleeding)/i',
            '/overdose/i',
            '/allergic\s+reaction/i',
        ],
        'pharmacist_request' => [
            '/speak\s+to\s+(a\s+)?pharmacist/i',
            '/talk\s+to\s+(a\s+)?human/i',
            '/call\s+me/i',
            '/contact\s+pharmacist/i',
        ],
    ];

    /**
     * Check if a message requires escalation.
     */
    public function check(string $message): ?array
    {
        $message = strtolower(trim($message));

        foreach ($this->patterns as $reasonValue => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    $reason = EscalationReason::from($reasonValue);

                    return [
                        'requires_escalation' => true,
                        'reason' => $reason,
                        'message' => $reason->message(),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Format escalation response for the chatbot.
     */
    public function formatEscalationResponse(EscalationReason $reason): array
    {
        return [
            'type' => 'escalation',
            'reason' => $reason->value,
            'message' => $reason->message(),
            'action' => 'contact_pharmacist',
            'display_message' => "I've flagged your question for our pharmacist. {$reason->message()} A pharmacist will respond to you shortly.",
        ];
    }

    /**
     * Add custom patterns for a specific tenant.
     */
    public function addCustomPatterns(EscalationReason $reason, array $patterns): void
    {
        $reasonValue = $reason->value;

        if (! isset($this->patterns[$reasonValue])) {
            $this->patterns[$reasonValue] = [];
        }

        $this->patterns[$reasonValue] = array_merge($this->patterns[$reasonValue], $patterns);
    }

    /**
     * Get all safety patterns.
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }
}
