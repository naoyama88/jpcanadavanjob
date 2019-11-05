<?php

namespace App\Services\Job;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService
{
    public function makeTweet(string $job): string
    {
        $newLine = PHP_EOL;
        $contentText = '';

        $tempIndex = 1;

        // title
        $contentText .= $tempIndex . ') ' . $job->title;
        $contentText .= $newLine;

        // job category
        $contentText .= 'カテゴリ： ';
        $contentText .= JobCategory::CATEGORIES[$job->category];
        $contentText .= $newLine;

        // hyper link
        $contentText .= $job->href;
        $contentText .= $newLine;

        // post datetime
        $contentText .= ' 入稿時間 ' . $job->post_datetime;
        $contentText .= $newLine;
        $contentText .= $newLine;

        $tempIndex++;

        $contentText = rtrim($contentText, PHP_EOL);

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
            // ツイート成功
            print "tweeted\n";
        } else {
            // ツイート失敗
            print "tweet failed\n";
        }
    }
}
