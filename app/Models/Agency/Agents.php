<?php

namespace App\Models\Agency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Agents extends Model
{
    use HasFactory;

    protected $table = "agency";

    protected $fillable = [
        'agent_code', 'agent_name', 'agent_email', 'agent_official_phone', 'no_of_agents', 'status'
    ];

    public function hasAgents() : hasMany {
        
    }

    /**
     * Get the users for the agency.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'agency');
    }
}
