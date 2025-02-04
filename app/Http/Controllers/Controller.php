<?php

namespace App\Http\Controllers;


/**
 * @OA\Info(
 *    title="MartEase APP",
 *    version="3.0.0",
 *   description="Author: Vikas Chauhan",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
