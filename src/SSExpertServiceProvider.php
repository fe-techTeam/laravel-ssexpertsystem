<?php

namespace FeTech\SSExpert;

use Illuminate\Support\ServiceProvider;
use FeTech\SSExpert\Commands\CheckBalanceCommand;
use FeTech\SSExpert\Contracts\BalanceServiceInterface;
use FeTech\SSExpert\Contracts\GroupServiceInterface;
use FeTech\SSExpert\Contracts\SenderIdServiceInterface;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\Contracts\TemplateServiceInterface;
use FeTech\SSExpert\Services\SSExpertBalanceService;
use FeTech\SSExpert\Services\SSExpertGroupService;
use FeTech\SSExpert\Services\SSExpertSenderIdService;
use FeTech\SSExpert\Services\SSExpertSmsService;
use FeTech\SSExpert\Services\SSExpertTemplateService;

class SSExpertServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ssexpert.php', 'ssexpert');

        // Template Service
        $this->app->singleton(TemplateServiceInterface::class, function ($app) {
            return new SSExpertTemplateService($app['config']['ssexpert'] ?? []);
        });
        $this->app->alias(TemplateServiceInterface::class, 'ssexpert.template');
        $this->app->alias(TemplateServiceInterface::class, SSExpertTemplateService::class);

        // SMS Service
        $this->app->singleton(SmsServiceInterface::class, function ($app) {
            return new SSExpertSmsService($app['config']['ssexpert'] ?? []);
        });
        $this->app->alias(SmsServiceInterface::class, 'ssexpert.sms');
        $this->app->alias(SmsServiceInterface::class, SSExpertSmsService::class);

        // Balance Service
        $this->app->singleton(BalanceServiceInterface::class, function ($app) {
            return new SSExpertBalanceService($app['config']['ssexpert'] ?? []);
        });
        $this->app->alias(BalanceServiceInterface::class, 'ssexpert.balance');
        $this->app->alias(BalanceServiceInterface::class, SSExpertBalanceService::class);

        // Sender ID Service
        $this->app->singleton(SenderIdServiceInterface::class, function ($app) {
            return new SSExpertSenderIdService($app['config']['ssexpert'] ?? []);
        });
        $this->app->alias(SenderIdServiceInterface::class, 'ssexpert.sender_id');
        $this->app->alias(SenderIdServiceInterface::class, SSExpertSenderIdService::class);

        // Group Service
        $this->app->singleton(GroupServiceInterface::class, function ($app) {
            return new SSExpertGroupService($app['config']['ssexpert'] ?? []);
        });
        $this->app->alias(GroupServiceInterface::class, 'ssexpert.group');
        $this->app->alias(GroupServiceInterface::class, SSExpertGroupService::class);

        // Unified Manager
        $this->app->singleton('ssexpert', function ($app) {
            return new SSExpertManager(
                smsService: $app->make(SmsServiceInterface::class),
                templateService: $app->make(TemplateServiceInterface::class),
                balanceService: $app->make(BalanceServiceInterface::class),
                senderIdService: $app->make(SenderIdServiceInterface::class),
                groupService: $app->make(GroupServiceInterface::class),
            );
        });
        $this->app->alias('ssexpert', SSExpertManager::class);
        $this->app->alias('ssexpert', 'ssexpert.manager');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ssexpert.php' => config_path('ssexpert.php'),
            ], 'ssexpert-config');

            $this->commands([
                CheckBalanceCommand::class,
            ]);
        }
    }
}
