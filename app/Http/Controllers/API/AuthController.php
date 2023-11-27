<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExistRequest;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\LoginDataRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\NameRequest;
use App\Http\Requests\PictureRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register', 'exist', 'getLoginData']]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/exist",
     *     summary="Check if user with email exist",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", exist=true),
     *     @OA\Response(response="200", exist=false),
     * )
     */

    public function exist(ExistRequest $request): JsonResponse
    {

        $exist = User::query()->where("email", $request->email)->count();

        return response()->json([
            'exist' => (bool)$exist,
        ], 200);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user->tokens()->delete();
            return response()->json([
                'user' => $user,
                'authorization' => [
                    'token' => $user->createToken('ApiToken')->plainTextToken,
                    'type' => 'bearer',
                ]
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function register(RegisterRequest $request): JsonResponse
    {

        $name = strstr($request->email, '@', true);

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'authorization' => [
                'token' => $user->createToken('ApiToken')->plainTextToken,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh(): JsonResponse
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json([
            'user' => $user,
            'authorisation' => [
                'token' => $user->createToken('ApiToken')->plainTextToken,
                'type' => 'bearer',
            ]
        ]);
    }

    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (is_null($user)) {
            $user = User::create([
                'google_id' => $request->id,
                'name' => $request->name,
                'email' => $request->email,
                'picture' => $request->picture,
                'password' => Hash::make("gD).%7m4=?s[j`(GPufH^V"),
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'authorization' => [
                    'token' => $user->createToken('ApiToken')->plainTextToken,
                    'type' => 'bearer',
                ]
            ]);

        } else {

            if ($request->id === $user->google_id) {
                Auth::login($user);
                $user->tokens()->delete();
                return response()->json([
                    'user' => $user,
                    'authorization' => [
                        'token' => $user->createToken('ApiToken')->plainTextToken,
                        'type' => 'bearer',
                    ]
                ]);
            }

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);

        }
    }

    public function changeName(NameRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Change successfully',
        ], 200);
    }

    public function changePicture(PictureRequest $request): JsonResponse
    {
        $user = Auth::user();
        $file = $request->picture;
        do {
            $name = uniqid() . "." . $file->getClientOriginalExtension();
        } while (Storage::exists("favicon/" . $name));

        Storage::putFileAs("favicon/", $file, $name);
        $url = route("favicon", ["favicon" => $name]);

        $user->update(['picture' => $url]);

        return response()->json([
            'message' => 'Picture upload successfully',
        ], 200);
    }

    public function getLoginData(LoginDataRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (is_null($user)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        } else {
            return response()->json([
                'name' => $user->name,
                'picture' => $user->picture,
            ], 200);
        }

    }
}
