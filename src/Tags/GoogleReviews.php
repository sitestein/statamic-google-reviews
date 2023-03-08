<?php

namespace Sitestein\GoogleReviews\Tags;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Assets\Asset;
use Statamic\Tags\Tags;

class GoogleReviews extends Tags
{
    public function index()
    {
        if (in_array(null, $this->config())) {
            return;
        }

        $ttl = 60 * 60 * 24; // day

        $data = Cache::remember('google-reviews', $ttl, function (): Collection {
            try {
                return $this->fetch();
            } catch (ConnectionException $e) {
                return collect([]);
            }
        })->when($this->threshold(), function ($collect) {
            return $collect->where('rating', '>=', $this->threshold());
        });

        return $data->when($data->count() >= $this->limit(), function ($collect) {
            return $collect->random($this->limit());
        });
    }

    protected function fetch()
    {
        $params = http_build_query([
            'key' => $this->apiToken(),
            'place_id' => $this->placeId(),
            'language' => $this->language(),
        ]);

        $http = Http::timeout(2)->get("{$this->baseUrl()}?{$params}");

        return collect($http->json('result.reviews'));
    }

    protected function config()
    {
        return [
            $this->baseUrl(),
            $this->apiToken(),
            $this->placeId(),
            $this->language(),
        ];
    }

    protected function baseUrl()
    {
        return 'https://maps.googleapis.com/maps/api/place/details/json';
    }

    protected function apiToken()
    {
        return config('statamic-google-reviews.api_token');
    }

    protected function placeId()
    {
        return $this->params->get('place_id')
            ?: config('statamic-google-reviews.place_id');
    }

    protected function language()
    {
        $config = config('statamic-google-reviews.language') ?: config('app.locale');

        return $this->params->get('language') ?: $config;
    }

    protected function limit()
    {
        return $this->params->get('limit') ?: 3;
    }

    protected function threshold()
    {
        return $this->params->get('threshold') ?: 4;
    }
}
