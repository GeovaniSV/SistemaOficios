<?php

namespace App\Providers;

use App\Models\User;
use App\Services\OficioAuthorizedSignerService;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Dev users bypass all permission and policy checks
        Gate::before(function (User $user, string $ability) {
            if ($user->is_dev) {
                return true;
            }
        });

        Gate::define('sign-oficios', function (User $user) {
            return app(OficioAuthorizedSignerService::class)->isAuthorized($user)
                ? Response::allow()
                : Response::deny('Você não está autorizado a assinar ofícios.');
        });
    }
}
