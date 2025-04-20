<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Post; // Example model
use App\Policies\PostPolicy; // Example policy

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model to policy mappings
     */
    protected $policies = [
        Post::class => PostPolicy::class,
        // Add more model/policy mappings as needed:
        // User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication/authorization services
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates (if needed)
        Gate::define('edit-settings', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('update-post', function ($user, $post) {
            return $user->id === $post->user_id;
        });
    }
}