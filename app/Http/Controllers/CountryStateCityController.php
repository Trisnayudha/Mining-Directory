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
        $searchName = $request->input('name');

        $countries = array_filter($this->data, function ($country) use ($searchName) {
            return !$searchName || stripos($country['name'], $searchName) !== false;
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


    public function getStates(Request $request, $countryIdOrName)
    {
        $searchName = $request->input('name');

        $country = array_filter($this->data, function ($item) use ($countryIdOrName) {
            return $item['id'] == $countryIdOrName || stripos($item['name'], $countryIdOrName) !== false;
        });

        if (!empty($country)) {
            $country = reset($country);
            $states = array_filter($country['states'], function ($state) use ($searchName) {
                return !$searchName || stripos($state['name'], $searchName) !== false;
            });
        } else {
            $states = [];
        }

        return $this->sendResponse('Successfully show data', array_values($states), 200);
    }


    public function getCities(Request $request, $countryIdOrName, $stateIdOrName)
    {
        $searchName = $request->input('name');

        $country = array_filter($this->data, function ($item) use ($countryIdOrName) {
            return $item['id'] == $countryIdOrName || stripos($item['name'], $countryIdOrName) !== false;
        });

        if (!empty($country)) {
            $country = reset($country);
            $state = array_filter($country['states'], function ($state) use ($stateIdOrName) {
                return $state['id'] == $stateIdOrName || stripos($state['name'], $stateIdOrName) !== false;
            });

            if (!empty($state)) {
                $state = reset($state);
                $cities = array_filter($state['cities'], function ($city) use ($searchName) {
                    return !$searchName || stripos($city['name'], $searchName) !== false;
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
