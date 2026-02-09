<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppraisalStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted'; // Often implies 'Evaluation Pending'
    case REGIONAL_HEAD_REVIEW_PENDING = 'regional_head_review_pending';
    case FINAL_ASSESSMENT_PENDING = 'final_assessment_pending';
    case CLOSED = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted (Evaluation Pending)',
            self::REGIONAL_HEAD_REVIEW_PENDING => 'Regional Head Review Pending',
            self::FINAL_ASSESSMENT_PENDING => 'Final Assessment Pending',
            self::CLOSED => 'Closed',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SUBMITTED => 'warning',
            self::REGIONAL_HEAD_REVIEW_PENDING => 'orange',
            self::FINAL_ASSESSMENT_PENDING => 'primary',
            self::CLOSED => 'success',
        };
    }
}