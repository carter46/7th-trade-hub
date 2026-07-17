<?php

namespace App\Enums;

enum PlatformProductType: string
{
    case WebsiteTemplate = 'website_template';
    case WebsitePackage = 'website_package';
    case DocumentTemplate = 'document_template';
    case VirtualPhone = 'virtual_phone';
    case Vpn = 'vpn';
    case Vps = 'vps';
    case Proxy = 'proxy';
    case Smtp = 'smtp';
    case Domain = 'domain';
    case Email = 'email';
    case SocialService = 'social_service';
    case EscrowService = 'escrow_service';

    public function label(): string
    {
        return config('catalog.types.'.$this->value.'.label', str_replace('_', ' ', ucfirst($this->value)));
    }

    public function icon(): string
    {
        return config('catalog.types.'.$this->value.'.icon', 'grid');
    }

    public function defaultRoute(): string
    {
        return config('catalog.types.'.$this->value.'.default_route', 'services');
    }
}
