<?php

namespace App\Enums;

enum DomainCommerceMode: string
{
    case Provider = 'provider';
    case Manual = 'manual';

    public function isManual(): bool
    {
        return $this === self::Manual;
    }

    public function isProvider(): bool
    {
        return $this === self::Provider;
    }
}
