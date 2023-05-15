<?php

namespace App\Services;

use Embed\Embed;
use Illuminate\Support\Facades\Cache;

class EmbedService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Embed Service
    |--------------------------------------------------------------------------
    |
    | Handles retrieval of Embed data for thumbnails and full images.
    |
    */

    private $embed = null;

    /**
     * Setting up for using the Embed package.
     */
    public function beforeConstruct() {
        $this->embed = new Embed();
        $this->embed->setSettings([
            'instagram:token' => env('INSTAGRAM_TOKEN', null),
        ]);
    }

    /**
     * Get the oEmbed response using the given url.
     *
     * @param string $url
     */
    public function getEmbed($url) {
        $response = Cache::remember($url, 60 * 60 * 24 * 7, function () use ($url) {
            return $this->embed->get($url)->getOEmbed()->all();
        });

        return $response;
    }
}
