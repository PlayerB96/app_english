<?php

namespace App\Providers;

use App\Repositories\AuthRepository;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(RepositoryInterface::class, function ($app) {
            $repository = $app->make(UserRepositoryInterface::class);

            if (! $repository instanceof RepositoryInterface) {
                throw new RuntimeException('Invalid repository binding for RepositoryInterface.');
            }

            return $repository;
        });
    }
}
