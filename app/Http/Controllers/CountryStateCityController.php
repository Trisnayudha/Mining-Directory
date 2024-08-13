<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CountryStateCityController extends BaseController
{
    protected $data;

    public function __construct()
    {
        $jsonPath = public_path('countries+states+cities.json');

        if (!File::exists($jsonPath)) {
            throw new \Exception('JSON file not found at: ' . $jsonPath);
        }

        $json = File::get($jsonPath);
        $this->data = json_decode($json, true);

        if (!is_array($this->data)) {
            throw new \Exception('Invalid JSON structure');
        }
    }

    public function getCountries(Request $request)
    {
        $countryName = $request->input('country_name');

        $countries = array_filter($this->data, function ($country) use ($countryName) {
            return !$countryName || stripos($country['name'], $countryName) !== false;
        });

        $countries = array_map(function ($country) {
            return [
                'id' => $country['id'],
                'name' => $country['name'],
                'iso2' => $country['iso2'],
            ];
        }, $countries);

        return $this->sendResponse('Successfully show data', array_values($countries), 200);
    }


    public function getStates(Request $request, $countryId)
    {
        $stateName = $request->input('state_name');

        $states = array_filter($this->data, function ($item) use ($countryId) {
            return $item['id'] == $countryId;
        });

        if (!empty($states)) {
            $states = reset($states)['states'];
            $states = array_filter($states, function ($state) use ($stateName) {
                return !$stateName || stripos($state['name'], $stateName) !== false;
            });
        } else {
            $states = [];
        }

        return $this->sendResponse('Successfully show data', array_values($states), 200);
    }


    public function getCities(Request $request, $countryId, $stateId)
    {
        $cityName = $request->input('city_name');

        $country = array_filter($this->data, function ($item) use ($countryId) {
            return $item['id'] == $countryId;
        });

        if (!empty($country)) {
            $country = reset($country);
            $state = array_filter($country['states'], function ($state) use ($stateId) {
                return $state['id'] == $stateId;
            });

            if (!empty($state)) {
                $state = reset($state);
                $cities = array_filter($state['cities'], function ($city) use ($cityName) {
                    return !$cityName || stripos($city['name'], $cityName) !== false;
                });
            } else {
                $cities = [];
            }
        } else {
            $cities = [];
        }

        return $this->sendResponse('Successfully show data', array_values($cities), 200);
    }



    protected function sendResponse($message, $data, $status)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => $status
        ], $status);
    }
}
