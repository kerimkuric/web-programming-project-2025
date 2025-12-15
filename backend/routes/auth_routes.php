<?php

/**
 * @OA\Post(
 *     path="/api/auth/register",
 *     summary="Register new user.",
 *     description="Add a new user to the database.",
 *     tags={"auth"},
 *     @OA\RequestBody(
 *         description="Add new user",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"password", "email", "name", "phone"},
 *                 @OA\Property(
 *                     property="password",
 *                     type="string",
 *                     example="some_password",
 *                     description="User password"
 *                 ),
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     example="demo@gmail.com",
 *                     description="User email"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     example="John Doe",
 *                     description="User name"
 *                 ),
 *                 @OA\Property(
 *                     property="phone",
 *                     type="string",
 *                     example="1234567890",
 *                     description="User phone"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User has been added."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error."
 *     )
 * )
 */
Flight::route("POST /api/auth/register", function () {
    $data = Flight::request()->data->getData();
    $response = Flight::auth_service()->register($data);

    if ($response['success']) {
        Flight::json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $response['data']
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => $response['error'] ?? 'Registration failed',
            'error' => $response['error'] ?? 'Registration failed'
        ], 500);
    }
});

/**
 * @OA\Post(
 *      path="/api/auth/login",
 *      tags={"auth"},
 *      summary="Login to system using email and password",
 *      @OA\Response(
 *           response=200,
 *           description="User data and JWT"
 *      ),
 *      @OA\RequestBody(
 *          description="Credentials",
 *          @OA\JsonContent(
 *              required={"email","password"},
 *              @OA\Property(property="email", type="string", example="demo@gmail.com", description="User email address"),
 *              @OA\Property(property="password", type="string", example="some_password", description="User password")
 *          )
 *      )
 * )
 */
Flight::route('POST /api/auth/login', function() {
    $data = Flight::request()->data->getData();
    $response = Flight::auth_service()->login($data);

    if ($response['success']) {
        Flight::json([
            'success' => true,
            'message' => 'User logged in successfully',
            'data' => $response['data']
        ]);
    } else {
        Flight::json([
            'success' => false,
            'message' => $response['error'] ?? 'Login failed',
            'error' => $response['error'] ?? 'Login failed'
        ], 500);
    }
});

?>
