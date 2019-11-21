<?php

namespace App\Http\Controllers\Api;

use App\Services\Twitter\TwitterService;

class CommandController
{
    /**
     * push test
     */
    public function test()
    {
        echo "test!";
        $cKey = env('HS_TWITTER_CONSUMER_KEY', 'error');
        $cSecret = env('HS_TWITTER_CONSUMER_SECRET', 'error');
        $aToken = env('HS_TWITTER_ACCESS_TOKEN', 'error');
        $aTokenSecret = env('HS_TWITTER_ACCESS_TOKEN_SECRET', 'error');
        $twitterService = new TwitterService($cKey, $cSecret, $aToken, $aTokenSecret);
        $twitterService->tweet("test");
    }
}