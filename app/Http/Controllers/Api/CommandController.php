<?php

namespace App\Http\Controllers\Api;

use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class CommandController
{
    /**
     * push test
     */
    public function test()
    {
        echo "test!";
    }
}