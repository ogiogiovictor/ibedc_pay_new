<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MoveContentProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:move-content-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data from a specific table to CSV and truncate the table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableName = 'users';  // Update with the actual table name
        $filePath = storage_path('app/public/exported2_data.csv');

        $this->info("Exporting data from $tableName to CSV...");
        
        // Step 1: Connect to the specified database and retrieve data
        $data = DB::connection("middleware")->table($tableName)->get();
        //$data = DB::table($tableName)->get();

        if ($data->isEmpty()) {
            $this->info("No data found in $tableName to export.");
        } else {
            $csvContent = $this->convertToCSV($data);

            // Save the CSV content to the file
            Storage::disk('public')->put('exported_data.csv', $csvContent);
            $this->info("Data exported successfully to $filePath");
        }

        // Step 2: Truncate the table
       // DB::table($tableName)->truncate();
         // Step 2: Truncate the table on the specified connection
         //DB::connection("middleware")->table($tableName)->truncate();
        $this->info("$tableName truncated successfully.");
    }


     /**
     * Convert data to CSV format.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @return string
     */
    private function convertToCSV($data)
    {
        $csvContent = '';

        // Get headers from the first row's keys
        $headers = array_keys((array)$data->first());
        $csvContent .= implode(',', $headers) . "\n";

        // Loop through each row and convert it to CSV format
        foreach ($data as $row) {
            $csvContent .= implode(',', array_map('strval', (array)$row)) . "\n";
        }

        return $csvContent;
    }


}
