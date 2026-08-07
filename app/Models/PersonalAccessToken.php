<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\App;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

final class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Sanctum tokens live on the landlord connection, not the tenant DB.
     */
    public function getConnectionName(): ?string
    {
        return App::environment('testing') ? null : 'mysql';
    }
}
