<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Config;
use Log;
use Carbon\Carbon;
use App\Services\EmbedService;

class EmbedController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Embed Controller
    |--------------------------------------------------------------------------
    |
    | Retrieves the urls for images and thumbnails for displaying on various pages
    |
    */

    /**
     * Return the oEmbed response - full image link and thumbnail link
     *
     * @param  App\Services\EmbedService  $service
     * @param  string  $url
     * @return array
     */
    public function getEmbed(Request $request, EmbedService $service)
    {
        // get url from request
        $url = $request->input('url');
        // Remove any queries
        $url = preg_split('/[?#]/', $url)[0];
        // Check if its a URL at all
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'error' => "Not an URL"
            ];
        }

        // Check if its from an accepted domain
        foreach(Config::get('lorekeeper.embed.urls') as $pattern) {
            if(preg_match($pattern, $url)) {
                $response = $service->getEmbed($url);
                if (isset($response['url'])) {
                    // download the image
                    $ch = curl_init($response['url']);
                    // make directory if it doesn't exist
                    if (!file_exists(public_path('images/embeds'))) {
                        mkdir(public_path('images/embeds'), 0777, true);
                    }
                    $filename = Carbon::now()->timestamp . '.png';
                    $fp = fopen(public_path('images/embeds/'.$filename), 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);

                    return [
                        'success' => 'true',
                        'url' => url('images/embeds/'.$filename),
                        'name' => $filename,
                    ];
                }
                else {
                    return [
                        'error' => "No image found",
                        'data' => json_encode($response),
                    ];
                }
            }
        }
        return [
            'error' => "Not an accepted URL"
        ];        
    }
}
