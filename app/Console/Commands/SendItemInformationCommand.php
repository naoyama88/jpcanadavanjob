<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libs\Util;
use App\Services\Item\ItemService;
use App\Services\Twitter\TwitterService;

class SendItemInformationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:senditeminformation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send item information to users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Util::isMidnight(date('H:i:s'))) {
            Log::info('Now it\'s midnight.');
            return true;
        }
        $itemService = new ItemService();
        $todayItems = $itemService->getTodayItems('sent_01');
        if (empty($todayItems) || count($todayItems) === 0) {
            return true;
        }

        $cKey = env('TWITTER_CONSUMER_KEY_ITEM', 'error');
        $cSecret = env('TWITTER_CONSUMER_SECRET_ITEM', 'error');
        $aToken = env('TWITTER_ACCESS_TOKEN_ITEM', 'error');
        $aTokenSecret = env('TWITTER_ACCESS_TOKEN_SECRET_ITEM', 'error');
        $twitterService = new TwitterService($cKey, $cSecret, $aToken, $aTokenSecret);
        foreach ($todayItems as $item) {
            $tweetText = $twitterService->makeTweet($item);
            $twitterService->tweet($tweetText);
        }
        $result = $itemService->updateAfterSentMail($todayItems->pluck('id'), 'sent_01');

        if ($result === false) {
            return false;
        }
        return true;
    }
}
