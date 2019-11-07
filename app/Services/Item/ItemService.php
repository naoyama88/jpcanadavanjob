<?php

namespace App\Services\Item;

use App\Libs\Constant\Category;
use App\Libs\Constant\MailType;
use App\Libs\Constant\Messages;
use App\Models\Item;
use phpQuery;
use Illuminate\Support\Facades\Log;

class ItemService
{
    /**
     * @param array $itemRecords
     * @param $latestId
     * @return array
     */
    public function extractNewItems(array $itemRecords, $latestId): array
    {
        $newItemRecords = [];
        if (empty($latestId)) {
            $newItemRecords = $itemRecords;
        } else {
            // sort by id
            array_multisort(array_column($itemRecords, 'id'), SORT_DESC, $itemRecords);
            foreach ($itemRecords as $itemRecord) {
                if ($itemRecord['id'] > $latestId) {
                    $newItemRecords[] = $itemRecord;
                } else {
                    // itemRecords have already been sorted. no need to continue loop
                    break;
                }
            }
        }

        return $newItemRecords;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function scrapeItems(): array
    {
        $html = file_get_contents("http://bbs.jpcanada.com/listing.php?bbs=1&order=2");
        $doc = phpQuery::newDocument($html);
        $records = [];
        foreach ($doc["#bbs-table"]->find("div.divTableRow") as $tableRow) {
            $record = [];

            // gif image meant item category (example. /icon/bbs238.png or http://bbs.jpcanada.com/icon/bbs997.gif)
            $category = pq($tableRow)->find('img')->attr('src');
            if (!in_array($category, Category::ITEM_CATEGORIES)) {
                // exclude 広告 or お知らせ
                continue;
            }
            $record['category'] = $category;

            // item ID (ex. No.89827)
            $record['id'] = trim(pq($tableRow)->find('nobr')->text(), "No.");

            // item title (ex. アイロンを売ります！)
            $record['title'] = pq($tableRow)->find('div.col4>a')->text();

            // hyper link to item page (ex. topics.php?bbs=1&msgid=163691&order=2&cat=&&dummy=0)
            $href = pq($tableRow)->find('div.col4>a')->attr('href');
            $record['href'] = 'http://bbs.jpcanada.com/' . $href;

            // post time (ex. from 2019-02-27 14:32:09 Charisma cafe and dessert house/バンクーバー)
            // to 2019-02-27 14:32:09
            $record['post_datetime'] = substr(trim(pq($tableRow)->find('div.col4>span.post-detail')->text()), 0, 19);

            $records[] = $record;
        }

        return $records;
    }

    /**
     * @param string $sentType
     * @return array|\Illuminate\Support\Collection
     */
    public function getTodayItems(string $sentType)
    {
        $from = date('Y-m-d 23:00:00', strtotime("-1 day"));
        $to = date('Y-m-d 23:00:00');
        switch ($sentType) {
            case MailType::TYPE_01:
                $sentTypeColumn = MailType::TYPE_01;
                break;
            case MailType::TYPE_02:
                $sentTypeColumn = MailType::TYPE_02;
                break;
            case MailType::TYPE_03:
                $sentTypeColumn = MailType::TYPE_03;
                break;
            default:
                Log::info(Messages::WRONG_SEND_TYPE);
                return [];
        }
        $todayItems = Item::whereBetween('post_datetime', [$from, $to])
            ->where($sentTypeColumn, '0')
            ->orderByDesc('id')
            ->get();

        return $todayItems;
    }

    /**
     * @param $ids
     * @param $sentType
     * @return array
     */
    public function updateAfterSentMail($ids, $sentType)
    {
        switch ($sentType) {
            case MailType::TYPE_01:
                $sentTypeColumn = MailType::TYPE_01;
                break;
            case MailType::TYPE_02:
                $sentTypeColumn = MailType::TYPE_02;
                break;
            case MailType::TYPE_03:
                $sentTypeColumn = MailType::TYPE_03;
                break;
            default:
                Log::info(Messages::WRONG_SEND_TYPE);
                return [];
        }
        Item::whereIn('id', $ids)
            ->update([$sentTypeColumn => 1]);
    }

    /**
     * get latest if from items table
     *
     * @return mixed|null
     */
    public function getLatestId()
    {
        $item = Item::orderBy('id', 'desc')
            ->first();
        if (empty($item)) {
            return null;
        }

        return $item->id;
    }

    /**
     * insert new items to items table
     *
     * @param array $newItemRecords
     * @return bool
     */
    public function insertNewItems(array $newItemRecords) : bool
    {
        // dev: should by multiple insert?
        foreach ($newItemRecords as $insertItemRecord) {
            $item = new Item();
            $item->id = $insertItemRecord['id'];
            $item->category = $insertItemRecord['category'];
            $item->title = $insertItemRecord['title'];
            $item->href = $insertItemRecord['href'];
            $item->post_datetime = $insertItemRecord['post_datetime'];
            $item->sent_01 = '0';
            $item->sent_02 = '0';
            $item->sent_03 = '0';

            $item->save();
        }

        return true;
    }

    /**
     * @param string $userText
     * @return \Illuminate\Support\Collection
     */
    public function getJobsByText(string $userText)
    {
        $from = date('Y-m-d 00:00:00', strtotime("-1 month"));
        $to = date('Y-m-d H:i:s');
        $todayJobs = Job::whereBetween('post_datetime', [$from, $to])
            ->where('title', 'like', '%' . $userText . '%')
            ->orderByDesc('id')
            ->get();

        return $todayJobs;
    }
}
