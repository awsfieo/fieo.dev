<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmployeeAppraisalStatus: string implements HasLabel, HasColor
{
    case PENDING = 'Pending';
    case PROCESSED = 'Processed';
    case RELEASED = 'Released';
    case HOLD = 'Hold';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => 'danger',
            self::PROCESSED => 'warning',
            self::RELEASED => 'success',
            self::HOLD => 'gray',
        };
    }
}