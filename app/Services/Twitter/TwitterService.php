<?php

namespace App\Services\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
use App\Libs\Constant\Category;

class TwitterService
{
    private $twitter;

    public function __construct($cKey, $cSecret, $aToken, $aTokenSecret) {
        $this->twitter = new TwitterOAuth($cKey, $cSecret, $aToken, $aTokenSecret);
    }

    public function makeTweet($record): string
    {
        $newLine = PHP_EOL;
        $contentText = '';

        // title
        $contentText .= 'ﾀｲﾄﾙ: ' . $record->title . $newLine;

        // category
        $contentText .= 'ｶﾃｺﾞﾘ: ' . Category::CATEGORIES[$record->category] . $newLine;

        // hyper link
        $contentText .= $record->href;
        $contentText .= $newLine;

        // post datetime
        $time = strtotime($record->post_datetime);
        $timeFormat = date('Y年m月d日 H時i分',$time);
        $contentText .= $timeFormat . ' 投稿' . $newLine;

        return $contentText;
    }

    public function tweet(string $tweetText)
    {
        $this->twitter->post(
            "statuses/update",
            ["status" => $tweetText]
        );

        if ($this->twitter->getLastHttpCode() == 200) {
            // success
            Log::info('tweeted');
            Log::info($tweetText);
        } else {
            // failed
            Log::info('tweet failed');
            Log::info($tweetText);
        }
    }
}
