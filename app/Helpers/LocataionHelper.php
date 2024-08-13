<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class LocationHelper
{
    public static function mapLocationIdsToNames($address)
    {
        // Load JSON data
        $jsonPath = public_path('countries+states+cities.json');
        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        // Map IDs to names
        return $address->map(function ($address) use ($data) {
            // Find country name
            $country = collect($data)->firstWhere('id', $address->country);
            $address->country = $country['name'] ?? $address->country;

            // Find state name
            $state = collect($country['states'] ?? [])->firstWhere('id', $address->province);
            $address->province = $state['name'] ?? $address->province;

            // Find city name
            $city = collect($state['cities'] ?? [])->firstWhere('id', $address->city);
            $address->city = $city['name'] ?? $address->city;

            return $address;
        });
    }
}
