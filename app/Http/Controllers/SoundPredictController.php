<?php

namespace App\Http\Controllers;

use App\Http\Middleware\OptionalAuthSanctum;
use App\Http\Requests\AddToHistorySoundPredictRequest;
use App\Models\SoundPredict;
use App\Http\Requests\StoreSoundPredictRequest;
use App\Http\Requests\UpdateSoundPredictRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use robertogallea\LaravelPython\Services\LaravelPython;

class SoundPredictController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['store']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $soundPredicts = $user->soundPredicts()->orderByDesc('created_at')->get(); //->limit(10)

        return response()->json([
            'predicts' => $soundPredicts->toArray(),
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSoundPredictRequest $request)
    {
        if (request()->bearerToken() && $user = Auth::guard('sanctum')->user()) {
            Auth::setUser($user);
        }

        $user = Auth::user();
        $file = $request->sound;
        do {
            $name = uniqid() . "." . $file->getClientOriginalExtension();
        } while (Storage::exists("sound/" . $name));

        $path = Storage::putFileAs("sound/", $file, $name);

        //moment piotrkowy
        $service = new LaravelPython();
        $params = [
            $path
        ];
        $result = $service->run(Storage::path("python/predictions.py"), $params);
        dd($result);
        $result = json_encode([
            "pop" => 99.9149,
            "hiphop" => 0.0248,
            "jazz" => 0.0176,
            "disco" => 0.0079,
            "rock" => 0.0050,
        ]);


        if (is_null($user)) {
            $soundPredict = SoundPredict::create([
                'result' => $result,
                'path' => $path,
            ]);
        } else {
            $soundPredict = $user->soundPredicts()->create([
                'result' => $result,
                'path' => $path,
            ]);
        }

        return response()->json([
            'predict' => $soundPredict,
        ], 200);

    }

    public function addToHisotry(AddToHistorySoundPredictRequest $request)
    {
//        $user = Auth::user();
//        $file = $request->sound;
//        do {
//            $name = uniqid() . "." . $file->getClientOriginalExtension();
//        } while (Storage::exists("sound/" . $name));
//
//        $path = Storage::putFileAs("sound/", $file, $name);
//
//
//        //moment piotrkowy
//
//        $result = json_encode([
//            "pop" => 99.9149,
//            "hiphop" => 0.0248,
//            "jazz" => 0.0176,
//            "disco" => 0.0079,
//            "rock" => 0.0050,
//        ]);
//
//
//        if (is_null($user)) {
//            $soundPredict = SoundPredict::create([
//                'result' => $result,
//                'path' => $path,
//            ]);
//        } else {
//            $soundPredict = $user->soundPredicts()->create([
//                'result' => $result,
//                'path' => $path,
//            ]);
//        }

        return response()->json([
            'predict' => $soundPredict,
        ], 200);

    }

}
