<?php

namespace App\Http\Controllers;

use App\Models\SoundPredict;
use App\Http\Requests\StoreSoundPredictRequest;
use App\Http\Requests\UpdateSoundPredictRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SoundPredictController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => []]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $soundPredicts = $user->soundPredicts()->orderByDesc('created_at')->limit(10)->get();

        return response()->json([
            'predicts' => $soundPredicts->toArray(),
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSoundPredictRequest $request)
    {
        $user = Auth::user();
        $file = $request->sound;
        do {
            $name = uniqid() . "." . $file->getClientOriginalExtension();
        } while (Storage::exists("sound/" . $name));

        $path = Storage::putFileAs("sound/", $file, $name);

        //moment piotrkowy


        $soundPredict = $user->soundPredicts()->create([
            'result' => json_encode([
                "pop" => 99.9149,
                "hiphop" => 0.0248,
                "jazz" => 0.0176,
                "disco" => 0.0079,
                "rock" => 0.0050,
            ]),
            'path' => $path,
        ]);

        return response()->json([
            'predict' => $soundPredict,
        ], 200);

    }

}
