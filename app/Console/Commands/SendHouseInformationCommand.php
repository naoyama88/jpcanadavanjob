<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libs\Util;
use App\Services\House\HouseService;
use App\Services\Twitter\TwitterService;

class SendHouseInformationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendhouseinformation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send house information to users';

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
        $houseService = new HouseService();
        $todayHouses = $houseService->getTodayHouses('sent_01');
        if (empty($todayHouses) || count($todayHouses) === 0) {
            return true;
        }

        $cKey = env('HS_TWITTER_CONSUMER_KEY', 'error');
        $cSecret = env('HS_TWITTER_CONSUMER_SECRET', 'error');
        $aToken = env('HS_TWITTER_ACCESS_TOKEN', 'error');
        $aTokenSecret = env('HS_TWITTER_ACCESS_TOKEN_SECRET', 'error');
        $twitterService = new TwitterService($cKey, $cSecret, $aToken, $aTokenSecret);
        foreach ($todayHouses as $house) {
            $tweetText = $twitterService->makeTweet($house);
            $twitterService->tweet($tweetText);
        }
        $result = $houseService->updateAfterSentMail($todayHouses->pluck('id'), 'sent_01');

        if ($result === false) {
            return false;
        }
        return true;
    }
}
