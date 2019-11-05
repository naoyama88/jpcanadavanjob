<?php

namespace App\Http\Controllers\Api;

use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
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
        $twitterService->tweet("test");
    }
}