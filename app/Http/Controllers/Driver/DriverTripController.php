<?php

namespace App\Http\Controllers\Driver;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Trips;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DriverTripController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/driver/get-posted-trips",
     *     summary="Get posted trips with pagination",
     *     description="Retrieve a list of trips with pagination, including transformed trip details such as pickup and dropoff windows.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Drivers Trips"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trips List Got Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Trips List Got Successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="trips",
     *                     type="object",
     *                     @OA\Property(
     *                         property="current_page",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="id",
     *                                 type="integer",
     *                                 example=1
     *                             ),
     *                             @OA\Property(
     *                                 property="from_city",
     *                                 type="string",
     *                                 example="New York"
     *                             ),
     *                             @OA\Property(
     *                                 property="to_city",
     *                                 type="string",
     *                                 example="Los Angeles"
     *                             ),
     *                             @OA\Property(
     *                                 property="delivery_price",
     *                                 type="number",
     *                                 format="float",
     *                                 example=100.50
     *                             ),
     *                             @OA\Property(
     *                                 property="pickup_date",
     *                                 type="string",
     *                                 format="date",
     *                                 example="2024-12-15"
     *                             ),
     *                             @OA\Property(
     *                                 property="pickup_window",
     *                                 type="string",
     *                                 example="Morning"
     *                             ),
     *                             @OA\Property(
     *                                 property="dropoff_window",
     *                                 type="string",
     *                                 example="Afternoon"
     *                             ),
     *                             @OA\Property(
     *                                 property="status",
     *                                 type="string",
     *                                 example="pending"
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="first_page_url",
     *                         type="string",
     *                         example="http://example.com/api/trips?page=1"
     *                     ),
     *                     @OA\Property(
     *                         property="last_page",
     *                         type="integer",
     *                         example=5
     *                     ),
     *                     @OA\Property(
     *                         property="last_page_url",
     *                         type="string",
     *                         example="http://example.com/api/trips?page=5"
     *                     ),
     *                     @OA\Property(
     *                         property="next_page_url",
     *                         type="string",
     *                         example="http://example.com/api/trips?page=2"
     *                     ),
     *                     @OA\Property(
     *                         property="prev_page_url",
     *                         type="string",
     *                         nullable=true,
     *                         example=null
     *                     ),
     *                     @OA\Property(
     *                         property="path",
     *                         type="string",
     *                         example="http://example.com/api/trips"
     *                     ),
     *                     @OA\Property(
     *                         property="per_page",
     *                         type="integer",
     *                         example=10
     *                     ),
     *                     @OA\Property(
     *                         property="to",
     *                         type="integer",
     *                         example=10
     *                     ),
     *                     @OA\Property(
     *                         property="total",
     *                         type="integer",
     *                         example=50
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */
    public function getPostedTrips()
    {
        $trips = Trips::paginate(10);
        $trips->getCollection()->transform(function ($trip) {
            return [
                "id" => $trip->id,
                "from_city" => $trip->from_city,
                "to_city" => $trip->to_city,
                "delivery_price" => $trip->delivery_price,
                "pickup_date" => $trip->pickup_date,
                "pickup_window" => ProjectConstants::TIME_SLOTS[$trip->pickup_window],
                "dropoff_window" => ProjectConstants::TIME_SLOTS[$trip->dropoff_window],
                "status" => $trip->status,
            ];
        });
        $response = [
            "trips" => $trips
        ];
        return ApiResponse::successResponse($response, "Trips List Got Successfully.", ProjectConstants::SUCCESS);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/driver/get-trip",
     *     summary="Get trip details by ID",
     *     description="Retrieve detailed information about a specific trip using its ID.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Drivers Trips"},
     *     @OA\Parameter(
     *         name="trip_id",
     *         in="query",
     *         description="The ID of the trip to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trip details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Trips List Got Successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="from_city", type="string", example="New York"),
     *                 @OA\Property(property="to_city", type="string", example="Los Angeles"),
     *                 @OA\Property(property="delivery_price", type="number", format="float", example=100.50),
     *                 @OA\Property(property="pickup_date", type="string", format="date", example="2024-12-15"),
     *                 @OA\Property(property="pickup_window", type="string", example="Morning"),
     *                 @OA\Property(property="dropoff_window", type="string", example="Afternoon"),
     *                 @OA\Property(property="distance", type="number", format="float", example=450.78),
     *                 @OA\Property(property="trailer_number", type="string", example="TR123456"),
     *                 @OA\Property(property="trailer_length", type="number", format="float", example=20.5),
     *                 @OA\Property(property="trailer_breadth", type="number", format="float", example=8.0),
     *                 @OA\Property(property="trailer_height", type="number", format="float", example=6.5),
     *                 @OA\Property(property="trailer_weight", type="number", format="float", example=2500.75),
     *                 @OA\Property(property="pickup_location", type="string", example="Warehouse A"),
     *                 @OA\Property(property="dropoff_location", type="string", example="Warehouse B"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Trip not found."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Server Error."
     *             )
     *         )
     *     )
     * )
     */
    public function getTripDetails(Request $request)
    {
        try {
            $trip = Trips::findOrFail($request->trip_id);
            $response = [
                "id" => $trip->id,
                "from_city" => $trip->from_city,
                "to_city" => $trip->to_city,
                "delivery_price" => $trip->delivery_price,
                "pickup_date" => $trip->pickup_date,
                "pickup_window" => ProjectConstants::TIME_SLOTS[$trip->pickup_window],
                "dropoff_window" => ProjectConstants::TIME_SLOTS[$trip->dropoff_window],
                "distance" => $trip->distance,
                "trailer_number" => $trip->trailer_number,
                "trailer_length" => $trip->trailer_length,
                "trailer_breadth" => $trip->trailer_breadth,
                "trailer_height" => $trip->trailer_height,
                "trailer_weight" => $trip->trailer_weight,
                "delivery_price" => $trip->delivery_price,
                "pickup_location" => $trip->delivery_price,
                "dropoff_location" => $trip->delivery_price,
                "status" => $trip->status,
            ];
            return ApiResponse::successResponse($response, "Trips List Got Successfully.", ProjectConstants::SUCCESS);
        } catch (ModelNotFoundException $ex) {
            return ApiResponse::errorResponse(null, "Trip not found.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    public function bookTrip(Request $request){
        
    }
}
