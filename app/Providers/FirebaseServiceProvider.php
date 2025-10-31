<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase.factory', function () {
            $factory = new Factory();

            // Configure credentials
            if (config('firebase.credentials.path') && file_exists(config('firebase.credentials.path'))) {
                $factory = $factory->withServiceAccount(config('firebase.credentials.path'));
            } elseif (config('firebase.credentials.array.project_id')) {
                $factory = $factory->withServiceAccount(config('firebase.credentials.array'));
            }

            // Configure database URL if provided
            if (config('firebase.database.url')) {
                $factory = $factory->withDatabaseUri(config('firebase.database.url'));
            }

            // Configure storage bucket if provided
            if (config('firebase.storage.bucket')) {
                $factory = $factory->withDefaultStorageBucket(config('firebase.storage.bucket'));
            }

            return $factory;
        });

        // Register Firebase Cloud Messaging
        $this->app->singleton('firebase.messaging', function () {
            try {
                return $this->app->make('firebase.factory')->createMessaging();
            } catch (\Exception $e) {
                \Log::warning('Firebase Messaging not configured: ' . $e->getMessage());
                return null;
            }
        });

        // Register Firebase Realtime Database
        $this->app->singleton('firebase.database', function () {
            try {
                return $this->app->make('firebase.factory')->createDatabase();
            } catch (\Exception $e) {
                \Log::warning('Firebase Database not configured: ' . $e->getMessage());
                return null;
            }
        });

        // Register Firebase Storage
        $this->app->singleton('firebase.storage', function () {
            try {
                return $this->app->make('firebase.factory')->createStorage();
            } catch (\Exception $e) {
                \Log::warning('Firebase Storage not configured: ' . $e->getMessage());
                return null;
            }
        });

        // Register aliases for easier access
        $this->app->alias('firebase.messaging', Messaging::class);
        $this->app->alias('firebase.database', Database::class);
        $this->app->alias('firebase.storage', Storage::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/firebase.php' => config_path('firebase.php'),
            ], 'firebase-config');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'firebase.factory',
            'firebase.messaging',
            'firebase.database',
            'firebase.storage',
            Messaging::class,
            Database::class,
            Storage::class,
        ];
    }
}
