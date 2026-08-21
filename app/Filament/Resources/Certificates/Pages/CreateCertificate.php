<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateCertificate extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = CertificateResource::class;
}
