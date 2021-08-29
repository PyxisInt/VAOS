<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    public $table = 'schedules';

    public function airline()
    {
        return $this->belongsTo('App\Models\AviationGroup');
    }

    public function depapt()
    {
        return $this->belongsTo('App\Models\Location');
    }

    public function arrapt()
    {
        return $this->belongsTo('App\Models\Location');
    }

    public function aircraft_group()
    {
        return $this->belongsToMany('App\Models\AircraftGroup')->withPivot('primary');
    }

    public function aircraft()
    {
        return $this->belongsToMany('App\Models\Aircraft');
    }

    // Eloquent Eger Loading Helper
    public static function allFK()
    {
        return with('depicao')->with('arricao')->with('airline')->with('aircraft_group')->get();
    }
    public function getCallsign()
    {
        if (is_null($this->airline_id) && is_null($this->callsign)) {
            return $this->flightnum;
        }
        if (is_null($this->callsign)) {
            return $this->airline->icao.$this->flightnum;
        }
        if (is_null($this->callsign) && is_null($this->flightnum)) {
            return 'N/A';
        } else {
            return $this->callsign;
        }
    }
}
