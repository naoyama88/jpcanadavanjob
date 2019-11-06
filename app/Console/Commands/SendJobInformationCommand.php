<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libs\Util;
use App\Services\Job\JobService;
use App\Services\Job\SendMailService;
use App\Services\Line\LineSendMessageService;
use App\Services\Job\TwitterService;

class SendJobInformationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendjobinformation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send job information to users';

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
     * TODO consider how to handle sent_type
     *
     * @return mixed
     */
    public function handle()
    {
        if (Util::isMidnight(date('H:i:s'))) {
            Log::info('Now it\'s midnight.');
            return true;
        }
        // １、スクレイピングで取得された仕事情報を確認
        // ２、仕事情報が存在する場合、１件ずつツイート内容を作成してツイート
        $jobService = new JobService();
        $todayJobs = $jobService->getTodayJob('sent_01');
        if (empty($todayJobs) || count($todayJobs) === 0) {
            return true;
        }
        $sendMailService = new SendMailService();
        $lineService = new LineSendMessageService();
        $lineText = $sendMailService->makeLineContentText($todayJobs);
        $lineService->sendLineMessage($lineText);
        $result = $jobService->updateAfterSentMail($todayJobs->pluck('id'), 'sent_01');

        $twitterService = new TwitterService();
        foreach ($todayJobs as $job) {
            $tweetText = $twitterService->makeTweet($job);
            $twitterService->tweet($tweetText);

        }

        if ($result === false) {
            return false;
        }
        return true;
    }
}
