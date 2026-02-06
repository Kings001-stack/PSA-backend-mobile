<?php

namespace App;

enum EscalationReason: string
{
    case DOSAGE_PRESCRIPTION = 'dosage_prescription';
    case CHILD_SAFETY = 'child_safety';
    case PREGNANCY_SAFETY = 'pregnancy_safety';
    case CONTROLLED_SUBSTANCE = 'controlled_substance';
    case DIAGNOSIS_ATTEMPT = 'diagnosis_attempt';
    case DRUG_INTERACTION = 'drug_interaction';
    case EMERGENCY = 'emergency';
    case PHARMACIST_REQUEST = 'pharmacist_request';

    public function message(): string
    {
        return match ($this) {
            self::DOSAGE_PRESCRIPTION => 'This query requires professional medical advice regarding dosage or prescription.',
            self::CHILD_SAFETY => 'This query involves medication for children and requires pharmacist consultation.',
            self::PREGNANCY_SAFETY => 'This query involves pregnancy-related medication and requires professional guidance.',
            self::CONTROLLED_SUBSTANCE => 'This query involves controlled substances and requires pharmacist assistance.',
            self::DIAGNOSIS_ATTEMPT => 'This appears to be a medical diagnosis request. Please consult a healthcare professional.',
            self::DRUG_INTERACTION => 'This query involves potential drug interactions and requires pharmacist review.',
            self::EMERGENCY => 'This appears to be a medical emergency. Please seek immediate medical attention.',
            self::PHARMACIST_REQUEST => 'You requested to speak with a human pharmacist. AI assistance has been paused.',
        };
    }
}
