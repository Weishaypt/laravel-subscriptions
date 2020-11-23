<?php

declare(strict_types=1);

namespace Weishaypt\Subscriptions\Providers;

use Weishaypt\Subscriptions\Models\Plan;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Weishaypt\Subscriptions\Models\PlanFeature;
use Weishaypt\Subscriptions\Models\PlanSubscription;
use Weishaypt\Subscriptions\Models\PlanSubscriptionUsage;
use Weishaypt\Subscriptions\Console\Commands\MigrateCommand;
use Weishaypt\Subscriptions\Console\Commands\PublishCommand;
use Weishaypt\Subscriptions\Console\Commands\RollbackCommand;

class SubscriptionsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.weishaypt.subscriptions.migrate',
        PublishCommand::class => 'command.weishaypt.subscriptions.publish',
        RollbackCommand::class => 'command.weishaypt.subscriptions.rollback',
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'weishaypt.subscriptions');

        // Bind eloquent models to IoC container
        $this->app->singleton('weishaypt.subscriptions.plan', $planModel = $this->app['config']['weishaypt.subscriptions.models.plan']);
        $planModel === Plan::class || $this->app->alias('weishaypt.subscriptions.plan', Plan::class);

        $this->app->singleton('weishaypt.subscriptions.plan_feature', $planFeatureModel = $this->app['config']['weishaypt.subscriptions.models.plan_feature']);
        $planFeatureModel === PlanFeature::class || $this->app->alias('weishaypt.subscriptions.plan_feature', PlanFeature::class);

        $this->app->singleton('weishaypt.subscriptions.plan_subscription', $planSubscriptionModel = $this->app['config']['weishaypt.subscriptions.models.plan_subscription']);
        $planSubscriptionModel === PlanSubscription::class || $this->app->alias('weishaypt.subscriptions.plan_subscription', PlanSubscription::class);

        $this->app->singleton('weishaypt.subscriptions.plan_subscription_usage', $planSubscriptionUsageModel = $this->app['config']['weishaypt.subscriptions.models.plan_subscription_usage']);
        $planSubscriptionUsageModel === PlanSubscriptionUsage::class || $this->app->alias('weishaypt.subscriptions.plan_subscription_usage', PlanSubscriptionUsage::class);

        // Register console commands
        $this->registerCommands($this->commands);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish Resources
        $this->publishesConfig('weishaypt/laravel-subscriptions');
        $this->publishesMigrations('weishaypt/laravel-subscriptions');
        ! $this->autoloadMigrations('weishaypt/laravel-subscriptions') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
