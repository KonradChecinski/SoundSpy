<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class OtherController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"User"},
     *      path="/user",
     *      summary="Get user info",
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
     *          @OA\Response(
     *            response="402",
     *            description="Unauthorized",
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      default="Unauthorized"
     *                ),
     *            ),
     *        ),
     * )
     */

    public function getUser(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user,
            'authorization' => [
                'token' => $request->bearerToken(),
                'type' => 'bearer',
            ]
        ]);
    }

}
