<?php

namespace App\Http\Controllers\Api;

use App\Services\Job\TwitterService;

class CommandController
{
    /**
     * push test
     */
    public function test()
    {
        // echo "test!";
        $twitterService = new TwitterService();
        $cKey = env('TWITTER_CONSUMER_KEY', 'error');
        $cSecret = env('TWITTER_CONSUMER_SECRET', 'error');
        $aToken = env('TWITTER_ACCESS_TOKEN', 'error');
        $aTokenSecret = env('TWITTER_ACCESS_TOKEN_SECRET', 'error');
        $twitterService->tweet("test", $cKey, $cSecret, $aToken, $aTokenSecret);
    }
}