<?php

namespace Classiebit\Eventmie\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Country extends Model
{
    protected $guarded = [];
    
    public function get_countries()
    {   
        $result = Country::all();
        return to_array($result);
    }
    
    public function get_countries_having_events()
    {   
        $result = Country::leftJoin("events", "events.country_id", '=', "countries.id")
                ->select(["countries.*"])
                ->where("events.country_id", '!=', null)
                ->groupBy('id')
                ->get();

        return to_array($result);
    }

    // get event country
    public function get_event_country($id = null)
    {   
       $result = Country::where(['id' => $id])->first();

        return collect($result);
    }
}
