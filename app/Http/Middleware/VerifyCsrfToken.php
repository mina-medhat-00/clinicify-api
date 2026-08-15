<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        "login",
        "adduser",
        "chkuname/*",
        "submit/*",
        "book/*",
        "cancel/*",
        "schedule/*",
        "edit/*",
        "update/*",
        "get/*",
        "create/*",
        "change/*",
        "join/meeting",
        "join/meeting/*",
    ];
}
