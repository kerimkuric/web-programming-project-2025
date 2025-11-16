<?php

/**
 * @OA\Get(
 *     path="/api/users",
 *     tags={"users"},
 *     summary="Get all users",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all users in the database",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="count", type="integer", example=10)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 */
Flight::route('GET /api/users', function() {
    try {
        $users = Flight::userService()->getAll();
        Flight::json([
            'success' => true,
            'data' => $users,
            'count' => count($users)
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

/**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the user with the given ID",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('GET /api/users/@id', function($id) {
    try {
        $user = Flight::userService()->getById($id);
        if ($user) {
            Flight::json([
                'success' => true,
                'data' => $user
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

/**
 * @OA\Post(
 *     path="/api/users",
 *     tags={"users"},
 *     summary="Create a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "phone", "password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="phone", type="string", example="1234567890"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="is_admin", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User created successfully"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or validation error"
 *     )
 * )
 */
Flight::route('POST /api/users', function() {
    try {
        // Get request body
        $rawBody = Flight::request()->getBody();
        $data = json_decode($rawBody, true);
        
        // If JSON decode failed, try Flight's getData method
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $data = Flight::request()->data->getData();
        }
        
        if ($data === null || (is_array($data) && empty($data))) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid JSON data or empty request body'
            ], 400);
            return;
        }

        $userId = Flight::userService()->createUser($data);
        $user = Flight::userService()->getById($userId);
        
        Flight::json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

/**
 * @OA\Put(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Update an existing user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="John Updated"),
 *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
 *             @OA\Property(property="phone", type="string", example="1234567890"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="is_admin", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     )
 * )
 */
Flight::route('PUT /api/users/@id', function($id) {
    try {
        // Get request body
        $rawBody = Flight::request()->getBody();
        $data = json_decode($rawBody, true);
        
        // If JSON decode failed, try Flight's getData method
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $data = Flight::request()->data->getData();
        }
        
        if ($data === null || (is_array($data) && empty($data))) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid JSON data or empty request body'
            ], 400);
            return;
        }

        $user = Flight::userService()->getById($id);
        if (!$user) {
            Flight::json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
            return;
        }

        Flight::userService()->updateUser($id, $data);
        $updatedUser = Flight::userService()->getById($id);
        
        Flight::json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $updatedUser
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Delete a user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to delete user"
 *     )
 * )
 */
Flight::route('DELETE /api/users/@id', function($id) {
    try {
        $id = (int)$id;
        $user = Flight::userService()->getById($id);
        if (!$user) {
            Flight::json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
            return;
        }

        $result = Flight::userService()->delete($id);
        
        if ($result) {
            Flight::json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

/**
 * @OA\Get(
 *     path="/api/users/email/{email}",
 *     tags={"users"},
 *     summary="Get user by email",
 *     @OA\Parameter(
 *         name="email",
 *         in="path",
 *         required=true,
 *         description="Email of the user",
 *         @OA\Schema(type="string", format="email", example="john@example.com")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the user with the given email",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('GET /api/users/email/@email', function($email) {
    try {
        $user = Flight::userService()->getByEmail($email);
        if ($user) {
            Flight::json([
                'success' => true,
                'data' => $user
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

?>

