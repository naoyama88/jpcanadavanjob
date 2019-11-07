<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libs\Util;
use App\Services\Item\ItemService;
use App\Libs\Constant\Category;

class RegisterItemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:registeritem';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register item';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Get item information from the website
     * trice in an hour
     * sometimes the number of getting item count will change (depending on heroku scheduler)
     */
    public function handle()
    {
        if (Util::isMidnight(date('H:i:s'))) {
            Log::info('Now it\'s midnight.');
            return true;
        }

        // the scheduler is expected to be set every 10 minutes
        // don't get item info if the tens place of the minutes is even number (not to access many times)
        $minutes = date('i');
        if (Util::isEvenNumber($minutes)) {
            Log::info('Now it\'s not time to scrape because the minutes is' . $minutes . '.');
            return true;
        }

        $itemService = new ItemService();
        $listedItems = $itemService->scrapeItems();
        if (empty($listedItems)) {
            Log::info('no item or could not get item');
            return false;
        }

        // just in case if something in the site I scrape has been changed
        $unknownCategory = array_diff(array_column($listedItems, 'category'), array_keys(Category::CATEGORIES));
        if (!empty($unknownCategory)) {
            Log::info("Unknown category has been found. Any other categories might have been added possibly.");
            return false;
        }

        $latestId = $itemService->getLatestId();
        $newItemRecords = $itemService->extractNewItems($listedItems, $latestId);

        if (empty($newItemRecords)) {
            // no new items
            Log::info('no new items');
            return true;
        }

        $result = $itemService->insertNewItems($newItemRecords);

        return $result;

    }
}
