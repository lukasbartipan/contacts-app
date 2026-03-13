<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Finished = 'finished';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Čeká ve frontě',
            self::Processing => 'Zpracovává se',
            self::Finished => 'Dokončeno',
            self::Failed => 'Chyba',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Finished => 'success',
            self::Failed => 'danger',
        };
    }
}
