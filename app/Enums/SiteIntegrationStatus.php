<?php

namespace App\Enums;

enum SiteIntegrationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Disabled = 'disabled';
}
