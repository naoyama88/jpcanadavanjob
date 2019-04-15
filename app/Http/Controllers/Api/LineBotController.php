<?php

namespace App\Http\Controllers\Api;

use App\Services\Line\Event\RecieveLocationService;
use App\Services\Line\Event\ReceiveTextService;
use App\Services\Line\Event\FollowService;
use Illuminate\Http\Request;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\UnfollowEvent;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;

class LineBotController
{
    /**
     * callback from LINE Message API(webhook)
     * @param Request $request
     * @throws LINEBot\Exception\InvalidSignatureException
     * @throws \Exception
     */
    public function callback(Request $request)
    {
        /** @var LINEBot $bot */
        $bot = app('line-bot');

        $signature = $_SERVER['HTTP_'.LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
        if (!LINEBot\SignatureValidator::validateSignature($request->getContent(), env('LINE_CHANNEL_SECRET'), $signature)) {
            Log::info('received from difference line-server');
            abort(400);
        }

        $events = $bot->parseEventRequest($request->getContent(), $signature);

        /** @var LINEBot\Event\BaseEvent $event */
        foreach ($events as $event) {
            $replyToken = $event->getReplyToken();
            $replyMessage = 'その操作はサポートしてません。.[' . get_class($event) . '][' . $event->getType() . ']';

            switch (true){
                //友達登録＆ブロック解除
                case $event instanceof FollowEvent:
                    $service = new FollowService($bot);
                    $replyMessage = $service->execute($event)
                        ? '友達登録ありがとうございます！JPCANADA(Vancouver)のお仕事情報を流すアカウントです！'
                        : '友達登録ありがとうございます！';

                    break;
                //メッセージの受信
                case $event instanceof TextMessage:
                    $service = new ReceiveTextService($bot);
                    $replyMessage = $service->execute($event);
                    break;

                //位置情報の受信
                case $event instanceof LocationMessage:
                    $service = new RecieveLocationService($bot);
                    $replyMessage = $service->execute($event);
                    break;

                //選択肢とか選んだ時に受信するイベント
                case $event instanceof PostbackEvent:
                    break;
                //ブロック
                case $event instanceof UnfollowEvent:
                    break;
                default:
                    // 例:
                    $body = $event->getEventSourceId();
                    Log::warning('Unknown event. ['. get_class($event) . ']', compact('body'));
            }

            $bot->replyText($replyToken, $replyMessage);
        }
    }
}