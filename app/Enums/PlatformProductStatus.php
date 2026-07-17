<?php

namespace App\Enums;

enum PlatformProductStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
