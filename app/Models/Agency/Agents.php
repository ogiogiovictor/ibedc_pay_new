<?php

namespace App\Models\Agency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agents extends Model
{
    use HasFactory;

    protected $table = "agency";

    protected $fillable = [
        'agent_code', 'agent_name', 'agent_email', 'agent_official_phone', 'no_of_agents', 'status'
    ];

    public function hasAgents() : hasMany {
        
    }
}
