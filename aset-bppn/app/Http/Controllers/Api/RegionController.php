<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;

class RegionController extends Controller
{
    public function provinces()
    {
        return response()->json(Province::all());
    }

    public function regencies($province_id)
    {
        return response()->json(Regency::where('province_id', $province_id)->get());
    }

    public function districts($regency_id)
    {
        return response()->json(District::where('regency_id', $regency_id)->get());
    }

    public function villages($district_id)
    {
        return response()->json(Village::where('district_id', $district_id)->get());
    }
}
