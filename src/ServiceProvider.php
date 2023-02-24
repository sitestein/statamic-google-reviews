<?php

namespace Sitestein\GoogleReviews;

use Sitestein\GoogleReviews\Tags\GoogleReviews;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        GoogleReviews::class,
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/statamic-google-reviews.php', 'statamic-google-reviews'
       );
    }
}
