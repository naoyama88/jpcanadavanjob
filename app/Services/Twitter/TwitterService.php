<?php

namespace App\Services\Job;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
use App\Libs\Constant\JobCategory;

class TwitterService
{
    public function makeTweet($job): string
    {
        $newLine = PHP_EOL;
        $contentText = '';

        // title
        $contentText .= 'ﾀｲﾄﾙ: ' . $job->title . $newLine;

        // job category
        $contentText .= 'ｶﾃｺﾞﾘ: ' . JobCategory::CATEGORIES[$job->category] . $newLine;

        // hyper link
        $contentText .= $job->href;
        $contentText .= $newLine;

        // post datetime
        $time = strtotime($job->post_datetime);
        $timeFormat = date('Y年m月d日 H時i分',$time);
        $contentText .= $timeFormat . ' 投稿' . $newLine;

        return $contentText;
    }

    public function tweet($tweetText)
    {
        $consumerKey = env('TWITTER_CONSUMER_KEY', 'error');
        $consumerSecret = env('TWITTER_CONSUMER_SECRET', 'error');
        $accessToken = env('TWITTER_ACCESS_TOKEN', 'error');
        $accessTokenSecret = env('TWITTER_ACCESS_TOKEN_SECRET', 'error');

        $twitter = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

        $result = $twitter->post(
            "statuses/update",
            ["status" => $tweetText]
        );

        if ($twitter->getLastHttpCode() == 200) {
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
