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
     * @OA\Get(
     *     tags={"Auth"},
     *     path="/auth/exist",
     *     summary="Cos2",
     *     summary="Check if user with email exist",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Exist",
     *         content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="exist",
     *                          type="bool",
     *                          description="Is exists"
     *                      ),
     *                      example={
     *                          "exist": true,
     *                      }
     *                  )
     *              )
     *          }
     *     ),
     * )
     */

    public function exist(ExistRequest $request): JsonResponse
    {

        $exist = User::query()->where("email", $request->email)->count();

        return response()->json([
            'exist' => (bool)$exist,
        ], 200);
    }

    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/login",
     *      summary="Login user",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="User's email",
     *          required=true,
     *          @OA\Schema(type="email")
     *      ),
     *      @OA\Parameter(
     *           name="password",
     *           in="query",
     *           description="User's password",
     *           required=true,
     *           @OA\Schema(type="password")
     *       ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                   property="user",
     *                   type="object",
     *                   @OA\Property(
     *                      property="id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="google_id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="email",
     *                       type="string",
     *                 ),
     *                 @OA\Property(
     *                      property="email_verified_at",
     *                      type="date",
     *                 ),
     *                 @OA\Property(
     *                      property="picture",
     *                      type="string",
     *                 ),
     *              ),
     *              @OA\Property(
     *                 property="authorization",
     *                 type="object",
     *                 @OA\Property(
     *                      property="token",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="type",
     *                       type="string",
     *                  ),
     *          ),
     *
     *          ),
     *      ),
     *     @OA\Response(
     *           response="401",
     *           description="Failed",
     *           @OA\JsonContent(
     *               type="object",
     *               @OA\Property(
     *                    property="message",
     *                    type="string",
     *                    default="Invalid credentials"
     *              ),
     *          )
     *      ),
     * )
     */

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

    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/register",
     *      summary="Register user",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="User's email",
     *          required=true,
     *          @OA\Schema(type="email")
     *      ),
     *      @OA\Parameter(
     *           name="password",
     *           in="query",
     *           description="User's password",
     *           required=true,
     *           @OA\Schema(type="password")
     *       ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                    property="message",
     *                    type="string",
     *                    default="User created successfully"
     *              ),
     *              @OA\Property(
     *                   property="user",
     *                   type="object",
     *                   @OA\Property(
     *                      property="id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="google_id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="email",
     *                       type="string",
     *                 ),
     *                 @OA\Property(
     *                      property="email_verified_at",
     *                      type="date",
     *                 ),
     *                 @OA\Property(
     *                      property="picture",
     *                      type="string",
     *                 ),
     *              ),
     *              @OA\Property(
     *                 property="authorization",
     *                 type="object",
     *                 @OA\Property(
     *                      property="token",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="type",
     *                       type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     * )
     */

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

    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/logout",
     *      summary="Logout user",
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                    property="message",
     *                    type="string",
     *                    default="Successfully logged out"
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *           response="401",
     *           description="Unauthorized",
     *           @OA\JsonContent(
     *               type="object",
     *               @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     default="Unauthorized"
     *               ),
     *           ),
     *       ),
     * )
     */

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/refresh",
     *      summary="Refresh user token",
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                   property="user",
     *                   type="object",
     *                   @OA\Property(
     *                      property="id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="google_id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="email",
     *                       type="string",
     *                 ),
     *                 @OA\Property(
     *                      property="email_verified_at",
     *                      type="date",
     *                 ),
     *                 @OA\Property(
     *                      property="picture",
     *                      type="string",
     *                 ),
     *              ),
     *              @OA\Property(
     *                 property="authorization",
     *                 type="object",
     *                 @OA\Property(
     *                      property="token",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="type",
     *                       type="string",
     *                  ),
     *          ),
     *
     *          ),
     *      ),
     *     @OA\Response(
     *           response="401",
     *           description="Unauthorized",
     *           @OA\JsonContent(
     *               type="object",
     *               @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     default="Unauthorized"
     *               ),
     *           ),
     *       ),
     * )
     */

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

    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/glogin",
     *      summary="Login google user",
     *     @OA\Parameter(
     *           name="google_id",
     *           in="query",
     *           description="User's google id",
     *           required=true,
     *           @OA\Schema(type="int")
     *       ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="User's google email",
     *          required=true,
     *          @OA\Schema(type="email")
     *      ),
     *     @OA\Parameter(
     *           name="name",
     *           in="query",
     *           description="User's google name",
     *           required=true,
     *           @OA\Schema(type="string")
     *       ),
     *     @OA\Parameter(
     *            name="picture",
     *            in="query",
     *            description="User's favicon url",
     *            required=true,
     *            @OA\Schema(type="string")
     *        ),
     *      @OA\Parameter(
     *           name="verified_email",
     *           in="query",
     *           description="If User verified his email",
     *           required=true,
     *           @OA\Schema(type="boolean")
     *       ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                   property="user",
     *                   type="object",
     *                   @OA\Property(
     *                      property="id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="google_id",
     *                      type="int",
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="email",
     *                       type="string",
     *                 ),
     *                 @OA\Property(
     *                      property="email_verified_at",
     *                      type="date",
     *                 ),
     *                 @OA\Property(
     *                      property="picture",
     *                      type="string",
     *                 ),
     *              ),
     *              @OA\Property(
     *                 property="authorization",
     *                 type="object",
     *                 @OA\Property(
     *                      property="token",
     *                      type="string",
     *                 ),
     *                 @OA\Property(
     *                       property="type",
     *                       type="string",
     *                  ),
     *          ),
     *
     *          ),
     *      ),
     *     @OA\Response(
     *           response="401",
     *           description="Failed",
     *           @OA\JsonContent(
     *               type="object",
     *               @OA\Property(
     *                    property="message",
     *                    type="string",
     *                    default="Invalid credentials"
     *              ),
     *          )
     *      ),
     * )
     */

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

    /**
     * @OA\Patch(
     *      tags={"Auth"},
     *      path="/auth/name",
     *      summary="Change user's name",
     *     @OA\Parameter(
     *           name="name",
     *           in="query",
     *           description="User's name",
     *           required=true,
     *           @OA\Schema(type="string")
     *       ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                    property="message",
     *                    type="string",
     *                    default="Change successfully"
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *           response="401",
     *           description="Unauthorized",
     *           @OA\JsonContent(
     *               type="object",
     *               @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     default="Unauthorized"
     *               ),
     *           ),
     *       ),
     * )
     */

    public function changeName(NameRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Change successfully',
        ], 200);
    }

    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/picture",
     *      summary="Change user's picture",
     *     @OA\Parameter(
     *           name="picture",
     *           in="query",
     *           description="picture",
     *           required=true,
     *           @OA\Schema(type="base64")
     *       ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                    property="message",
     *                    type="string",
     *                    default="Picture upload successfully"
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *           response="401",
     *           description="Unauthorized",
     *           @OA\JsonContent(
     *               type="object",
     *               @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     default="Unauthorized"
     *               ),
     *           ),
     *       ),
     * )
     */

    public function changePicture(PictureRequest $request): JsonResponse
    {
        $user = Auth::user();

        $image_64 = $request->picture; //your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

        // find substring fro replace here eg: data:image/png;base64,

        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);


        do {
            $imageName = uniqid() . '.' . $extension;
        } while (Storage::exists("favicon" . $imageName));

        $isSave = Storage::put("favicon/$imageName", base64_decode($image));

        if (!$isSave) {
            return response()->json([
                'message' => 'Invalid file',
            ], 500);
        }

        $url = route("favicon", ["favicon" => $imageName]);

        $user->update(['picture' => $url]);

        return response()->json([
            'message' => 'Picture upload successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *      tags={"Auth"},
     *      path="/auth/logindata",
     *      summary="Get user's name and picture",
     *     @OA\Parameter(
     *           name="email",
     *           in="query",
     *           description="email",
     *           required=true,
     *           @OA\Schema(type="string")
     *       ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                    property="name",
     *                    type="string",
     *              ),
     *              @OA\Property(
     *                     property="picture",
     *                     type="string",
     *               ),
     *          ),
     *      ),
     *     @OA\Response(
     *            response="401",
     *            description="Failed",
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     default="Invalid credentials"
     *               ),
     *           )
     *       ),
     * )
     */

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
