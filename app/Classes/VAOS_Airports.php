<?php
/**
 * Created by PhpStorm.
 * User: taylorbroad
 * Date: 10/25/16
 * Time: 1:07 AM.
 */

namespace App\Classes;

use GuzzleHttp\Client;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class VAOS_Airports
{
    public static function checkOrAdd($input)
    {
        Log::debug('Calling checkOrAdd');
        if (is_string($input)) {
            try {
                $apt = Location::where('icao', $input)->firstOrFail();
            }
            catch (\Exception $e)
            {
                $id = self::AddAirport($input);
                return $id;
            }
            return $apt;
        }
        else if(is_int($input)) {
            try {
                $apt = Location::where('id', $input)->firstOrFail();
            }
            catch (\Exception $e)
            {
                $id = self::AddAirport($input, true);
                return $id;
            }
            return $apt;
        }
        else {
            $apt = Location::where('id', $input->id)->first();
            if ($apt === null) {
                $id = self::addWithObject($input);
                return $id;
            }
            return $apt;
        }
    }
    public static function addWithObject($apt)
    {
        //dd($apt);
        $airport = new Location();
        $airport->id         = $apt->id;
        $airport->name       = $apt->name;
        $airport->icao       = $apt->gps_code;
        $airport->iata       = $apt->iata_code;
        $airport->lat        = $apt->latitude_deg;
        $airport->lon        = $apt->longitude_deg;
        $airport->city       = $apt->municipality;
        $airport->country    = $apt->iso_country;

        $airport->save();
        Log::debug('Created Location: '.$airport->icao);
        return $airport;
    }
    public static function AddAirport($icao, $id = false)
    {
        // lets request the airport identifier from the central database
        try {
            $client = new Client();
            if(!$id) {
                $res    = $client->request('GET', 'http://fsvaos.net/api/data/airports', [
                    'query' => [
                        'icao' => $icao,
                    ],
                ])->getBody();
            } else {
                $res    = $client->request('GET', 'http://fsvaos.net/api/data/airports', [
                    'query' => [
                        'id' => $icao,
                    ],
                ])->getBody();
            }

            // Add the airport to the database
            $data    = json_decode($res, true);
            //dd($icao);
            $airport = Location::firstOrNew(['id' => $data['id']]);
            // return dd($icao);

            $airport->id         = $data['id'];
            $airport->name       = $data['name'];
            $airport->icao       = $data['gps_code'];
            $airport->iata       = $data['iata_code'];
            $airport->lat        = $data['latitude_deg'];
            $airport->lon        = $data['longitude_deg'];
            $airport->city       = $data['municipality'];
            $airport->country    = $data['iso_country'];

            // Add the Banner URL if we got it
            if (isset($data['banner_url'])) {
                $airport->banner_url = $data['banner_url'];
            }
            $airport->save();
        } catch (\Exception $e) {
            dd($data);
        }


        return $airport;
    }
}
