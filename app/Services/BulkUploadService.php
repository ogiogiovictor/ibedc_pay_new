<?php

namespace App\Services;

class BulkUploadService
{

    //You will need to change based on your use case
    public function BadWay()
    {
        //ini_set('max_execution_time', 600);
        //Get the CSV File
        $filePath = storage_path('app\small)set.csv');

        $file = fopen($filePath, 'r');
        while(($row = fgetcsv($file, null, ',')) !== false) {
            Model::Insert([
                'log_date' => date('Y-m-d', strtotime($row[0])),
                'log_time' => $row[1],
                'log_date' => $row[2],
                'log_pressue' => $row[3],
                'log_temp' => $row[4],
                'log_temp_pressure' => $row[5],
            ]);
        }
        fclose($filePath);
    }

    public function SemiGoodWay() {

        //ini_set('memory_limit', '1024M');

        $filePath = storage_path('app\small)set.csv');

        $file = fopen($filePath, 'r');
        $data = [];

        while(($row = fgetcsv($file, null, ',')) !== false) {
            $data[] = [
                'log_date' => date('Y-m-d', strtotime($row[0])),
                'log_time' => $row[1],
                'log_date' => $row[2],
                'log_pressue' => $row[3],
                'log_temp' => $row[4],
                'log_temp_pressure' => $row[5],
            ];
        }

        foreach(array_chunk($data, 1000) as $chunk){
            Model::insert($data);
        };
        
        fclose($filePath);

    }


    public function GoodWay() {
        
    }
}
