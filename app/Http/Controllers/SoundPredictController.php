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

        $image_64 = $request->sound; //your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
      
      // find substring fro replace here eg: data:image/png;base64,
       $image = str_replace($replace, '', $image_64); 
       $image = str_replace(' ', '+', $image); 
      
       


       do {
        $imageName = uniqid().'.'.$extension;
       } while (Storage::exists("sound" . $imageName));

      $isSave = Storage::put("sound/$imageName", base64_decode($image));

       if($isSave){
$path = "sound/$imageName";
       }else{
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

//     public function addToHisotry(AddToHistorySoundPredictRequest $request)
//     {
// //        $user = Auth::user();
// //        $file = $request->sound;
// //        do {
// //            $name = uniqid() . "." . $file->getClientOriginalExtension();
// //        } while (Storage::exists("sound/" . $name));
// //
// //        $path = Storage::putFileAs("sound/", $file, $name);
// //
// //
// //        //moment piotrkowy
// //
// //        $result = json_encode([
// //            "pop" => 99.9149,
// //            "hiphop" => 0.0248,
// //            "jazz" => 0.0176,
// //            "disco" => 0.0079,
// //            "rock" => 0.0050,
// //        ]);
// //
// //
// //        if (is_null($user)) {
// //            $soundPredict = SoundPredict::create([
// //                'result' => $result,
// //                'path' => $path,
// //            ]);
// //        } else {
// //            $soundPredict = $user->soundPredicts()->create([
// //                'result' => $result,
// //                'path' => $path,
// //            ]);
// //        }

//         return response()->json([
//             'predict' => $soundPredict,
//         ], 200);

//     }

}