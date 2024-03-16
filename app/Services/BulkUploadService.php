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


    public static function chunkFile(string $path, callable $generator, int $chunkSize) {

        $file = fopen($path, 'r');
        $data = [];

        for($ii = 1; ($row = fgetcsv($file, null, ',')) !== false; $ii++ ){
            $data[] = $generator($row);

            if($ii % $chunkSize == 0){
                yield $data;
                $data = [];
            }
        }

        if(!empty($data)){
            yield $data;
        }

        fclose($file);

    }


    //Use case in a controller. Lets assume this part is a controller
      $filePath = storage_path('app\small)set.csv');
        $generator = function($row){
            'log_date' => date('Y-m-d', strtotime($row[0])),
            'log_time' => $row[1],
            'log_date' => $row[2],
            'log_pressue' => $row[3],
            'log_temp' => $row[4],
            'log_temp_pressure' => $row[5],
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE gauge_readings DISABLE KEYS');

        foreach(BulkUploadService::chunkFile( $filePath, $generator, 10000) as $chunk){
            Model::insert($chunk);
        }

        
        DB::statement('ALTER TABLE gauge_readings ENABLE KEYS');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

    public function mysqlWay() {

        $escapePath = DB::getPdo()->quote($filePath);

        DB::statement("
            LOAD DATA LOCAL INFILE($escapePath)
            INTO TABLE guage_readings
            FIELDS TERMINATED BY ','
            LINES TERMINATED BY '\\n'
            ($date_var, log_time, guage_pressure, guage_pressure, guage_temp_filed)
            SET log_date = STR_TO_DATE(@date_var, '%m/%d/%y)
        ");


    }

}
