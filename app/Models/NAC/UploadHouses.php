<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadHouses extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "upload_houses";

    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(AccoutCreaction::class, 'tracking_id', 'tracking_id');
    }

    public function customer()
    {
        return $this->hasOne(AccoutCreaction::class, 'tracking_id', 'tracking_id');
    }

     public function landlordinfo()
    {
        return $this->hasOne(ContinueAccountCreation::class, 'tracking_id', 'tracking_id');
    }

    public static function countByRegion($region = null)
    {
        $query = self::selectRaw('region, COUNT(*) as total')
            ->where('status', 1);

        if ($region && $region !== 'HQ') {
            // Regular regional user – only show their region
            $query->where('region', $region);
        }

        // If region is HQ or null, show all regions grouped
        return $query->groupBy('region')
            ->orderBy('region')
            ->get();
    }


    public static function hubsByRegion($region)
    {
        return self::selectRaw('business_hub, COUNT(*) as total')
            ->where('region', $region)
            ->where('status', 1)
            ->groupBy('business_hub')
            ->orderBy('business_hub')
            ->get();
    }


  
    public static function getBusinessHubSummaryByRegion($region = null)
{
    $query = self::query()
        ->selectRaw('
            upload_houses.service_center,
            upload_houses.business_hub,
            users.name AS user_name,
            users.email AS user_email,
            COUNT(CASE WHEN upload_houses.status = 1 THEN 1 END) AS pending,
            COUNT(CASE WHEN upload_houses.status = 5 THEN 1 END) AS rejected,
            COUNT(*) AS total_count
        ')
        ->leftJoin('users', function ($join) {
            $join->on('upload_houses.business_hub', '=', 'users.business_hub')
                ->on('upload_houses.service_center', '=', 'users.sc');
        })
        ->whereIn('upload_houses.status', [1, 5]);

    // Only apply region filter if region is not null and not HQ
    if (!empty($region) && strtoupper($region) !== 'HQ') {
        $query->where('upload_houses.region', $region);
    }

    return $query
        ->groupBy(
            'upload_houses.service_center',
            'upload_houses.business_hub',
            'users.name',
            'users.email'
        )
        ->orderBy('upload_houses.business_hub')
        ->orderBy('upload_houses.service_center')
        ->get();
}



}
