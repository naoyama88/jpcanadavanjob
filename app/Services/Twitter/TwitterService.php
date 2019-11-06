<?php

namespace App\Services\Job;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
use App\Libs\Constant\JobCategory;

class TwitterService
{
    public function makeJobTweet($job): string
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

    public function tweet($tweetText, $cKey, $cSecret, $aToken, $aTokenSecret)
    {
        $twitter = new TwitterOAuth($cKey, $cSecret, $aToken, $aTokenSecret);

        $twitter->post(
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
