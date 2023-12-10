<?php

namespace App\Http\Controllers;

use App\Http\Middleware\OptionalAuthSanctum;
use App\Http\Requests\AddToHistorySoundPredictRequest;
use App\Http\Requests\StoreHistorySoundPredictRequest;
use App\Models\SoundPredict;
use App\Http\Requests\StoreSoundPredictRequest;
use App\Http\Requests\UpdateSoundPredictRequest;
use Illuminate\Http\Request;
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

        $image_64 = $request->sound; //your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

        // find substring fro replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);


        do {
            $imageName = uniqid() . '.' . $extension;
        } while (Storage::exists("sound" . $imageName));

        $isSave = Storage::put("sound/$imageName", base64_decode($image));

        if ($isSave) {
            $path = "sound/$imageName";
        } else {
            return response()->json([
                'message' => 'Invalid file',
            ], 500);
        }

        //moment piotrkowy
        $service = new LaravelPython();
        $params = [
            Storage::path($path)
        ];
        $result = $service->run(Storage::path("python/predictions.py"), $params);
        $result = json_decode($result);


        if (is_null($user)) {
            $soundPredict = SoundPredict::create([
                'result' => json_encode($result),
                'path' => $path,
            ]);
        } else {
            $soundPredict = $user->soundPredicts()->create([
                'result' => json_encode($result),
                'path' => $path,
            ]);
        }

        return response()->json([
            'predict' => $soundPredict,
        ], 200);

    }

    public function addToHistory(StoreHistorySoundPredictRequest $request)
    {
        $user = Auth::user();
        $history = json_decode($request->history);
        $totalQuantity = count($history);
        $successQuantity = 0;


        foreach ($history as $item) {
            $soundPredict = SoundPredict::query()->where("user_id", null)->where("id", $item->id)->first();
            if (is_null($soundPredict)) {
                continue;
            }

            if (json_decode($soundPredict->result) != json_decode($item->result) || strtotime($soundPredict->created_at) != strtotime($item->created_at)) {
                continue;
            }
            $user->soundPredicts()->save($soundPredict);
            $successQuantity++;
        }

        return response()->json([
            'message' => 'History added',
            'total' => $totalQuantity,
            'success' => $successQuantity,
            'history' => $user->soundPredicts,
        ], 200);
    }

    public function deleteHistory(Request $request)
    {
        $user = Auth::user();
        $user->soundPredicts()->update(["user_id" => null]);
        return response()->json([
            'message' => 'History deleted',
        ], 200);
    }


}
