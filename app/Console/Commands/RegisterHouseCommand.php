<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libs\Util;
use App\Services\House\HouseService;
use App\Libs\Constant\Category;

class RegisterHouseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:registerhouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register house';

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
     * Get house information from the website
     * trice in an hour
     * sometimes the number of getting house count will change (depending on heroku scheduler)
     */
    public function handle()
    {
        if (Util::isMidnight(date('H:i:s'))) {
            Log::info('Now it\'s midnight.');
            return true;
        }

        // the scheduler is expected to be set every 10 minutes
        // don't get house info if the tens place of the minutes is even number (not to access many times)
        $minutes = date('i');
        if (Util::isEvenNumber($minutes)) {
            Log::info('Now it\'s not time to scrape because the minutes is' . $minutes . '.');
            return true;
        }

        $houseService = new HouseService();
        $listedHouses = $houseService->scrapeHouses();
        Log::info($listedHouses);
        if (empty($listedHouses)) {
            Log::info('no house or could not get house');
            return false;
        }

        // just in case if something in the site I scrape has been changed
        $unknownCategory = array_diff(array_column($listedHouses, 'category'), array_keys(Category::CATEGORIES));
        if (!empty($unknownCategory)) {
            Log::info("Unknown category has been found. Any other categories might have been added possibly.");
            return false;
        }

        $latestId = $houseService->getLatestId();
        $newHouseRecords = $houseService->extractNewHouses($listedHouses, $latestId);

        if (empty($newHouseRecords)) {
            // no new items
            Log::info('no new houses');
            return true;
        }

        $result = $houseService->insertNewHouses($newHouseRecords);

        return $result;

    }
}
