<?php

namespace App\Services\House;

use App\Libs\Constant\Category;
use App\Libs\Constant\MailType;
use App\Libs\Constant\Messages;
use App\Models\House;
use phpQuery;
use Illuminate\Support\Facades\Log;

class HouseService
{
    /**
     * @param array $houseRecords
     * @param $latestId
     * @return array
     */
    public function extractNewHouses(array $houseRecords, $latestId): array
    {
        $newHouseRecords = [];
        if (empty($latestId)) {
            $newHouseRecords = $houseRecords;
        } else {
            // sort by id
            array_multisort(array_column($houseRecords, 'id'), SORT_DESC, $houseRecords);
            foreach ($houseRecords as $houseRecord) {
                if ($houseRecord['id'] > $latestId) {
                    $newHouseRecords[] = $houseRecord;
                } else {
                    // houseRecords have already been sorted. no need to continue loop
                    break;
                }
            }
        }

        return $newHouseRecords;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function scrapeHouses(): array
    {
        $html = file_get_contents("http://bbs.jpcanada.com/listing.php?bbs=3&order=2");
        $doc = phpQuery::newDocument($html);
        $records = [];
        foreach ($doc["#bbs-table"]->find("div.divTableRow") as $tableRow) {
            $record = [];

            // gif image meant house category (example. /icon/bbs208.png)
            $category = pq($tableRow)->find('img')->attr('src');
            if (!in_array($category, Category::HOUSE_CATEGORIES)) {
                // exclude 広告 or お知らせ
                continue;
            }
            $record['category'] = $category;

            // house ID (ex. No.129298)
            $record['id'] = trim(pq($tableRow)->find('nobr')->text(), "No.");

            // item title (ex. クリーン 1部屋☆ダウンタウンまで約30分)
            $record['title'] = pq($tableRow)->find('div.col4>a')->text();

            // hyper link to item page (ex. topics.php?bbs=3&msgid=129297&order=2&cat=&&dummy=0)
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
    public function getTodayHouses(string $sentType)
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
        $todayHouses = House::whereBetween('post_datetime', [$from, $to])
            ->where($sentTypeColumn, '0')
            ->orderByDesc('id')
            ->get();

        return $todayHouses;
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
        House::whereIn('id', $ids)
            ->update([$sentTypeColumn => 1]);
    }

    /**
     * get latest if from houses table
     *
     * @return mixed|null
     */
    public function getLatestId()
    {
        $house = House::orderBy('id', 'desc')
            ->first();
        if (empty($house)) {
            return null;
        }

        return $house->id;
    }

    /**
     * insert new houses to houses table
     *
     * @param array $newHouseRecords
     * @return bool
     */
    public function insertNewHouses(array $newHouseRecords) : bool
    {
        // dev: should by multiple insert?
        foreach ($newHouseRecords as $insertHouseRecord) {
            $house = new House();
            $house->id = $insertHouseRecord['id'];
            $house->category = $insertHouseRecord['category'];
            $house->title = $insertHouseRecord['title'];
            $house->href = $insertHouseRecord['href'];
            $house->post_datetime = $insertHouseRecord['post_datetime'];
            $house->sent_01 = '0';
            $house->sent_02 = '0';
            $house->sent_03 = '0';

            $house->save();
        }

        return true;
    }

    /**
     * @param string $userText
     * @return \Illuminate\Support\Collection
     */
    public function getHousesByText(string $userText)
    {
        $from = date('Y-m-d 00:00:00', strtotime("-1 month"));
        $to = date('Y-m-d H:i:s');
        $todayHouses = House::whereBetween('post_datetime', [$from, $to])
            ->where('title', 'like', '%' . $userText . '%')
            ->orderByDesc('id')
            ->get();

        return $todayHouses;
    }
}
