<?php

namespace App\Http\Controllers;


/**
 * @OA\Info(
 *    title="Hitch Mail",
 *    version="3.0.0",
 *   description="Auther: Vikas Chauhan",
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
