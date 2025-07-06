<?php

namespace App\Enums;

enum ApplicationStatusEnum: string
{
    case PENDING = 'pending';
    case APPLIED = 'applied';
    case REJECTED = 'rejected';
    case HIRED = 'hired';

    public function label(): string
    {
        return match ($this) {
            self::APPLIED => 'Aplicado',
            self::REJECTED => 'Rejeitado',
            self::PENDING => 'Pendente',
            self::HIRED => 'Contratado',
        };
    }
}
