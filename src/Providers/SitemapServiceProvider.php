<?php

namespace Grilar\Sitemap\Providers;

use Grilar\Base\Events\CreatedContentEvent;
use Grilar\Base\Events\DeletedContentEvent;
use Grilar\Base\Events\UpdatedContentEvent;
use Grilar\Base\Supports\ServiceProvider;
use Grilar\Base\Traits\LoadAndPublishDataTrait;
use Grilar\Sitemap\Sitemap;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;

class SitemapServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected bool $defer = true;

    public function boot(): void
    {
        $this->setNamespace('packages/sitemap')
            ->loadAndPublishConfigurations(['config'])
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app['events']->listen([
            CreatedContentEvent::class,
            UpdatedContentEvent::class,
            DeletedContentEvent::class,
        ], function () {
            cache()->forget('cache_site_map_key');
        });
    }

    public function register(): void
    {
        $this->app->bind('sitemap', function ($app) {
            $config = config('packages.sitemap.config');

            return new Sitemap(
                $config,
                $app[Repository::class],
                $app['config'],
                $app['files'],
                $app[ResponseFactory::class],
                $app['view']
            );
        });

        $this->app->alias('sitemap', Sitemap::class);
    }

    public function provides(): array
    {
        return ['sitemap', Sitemap::class];
    }
}
