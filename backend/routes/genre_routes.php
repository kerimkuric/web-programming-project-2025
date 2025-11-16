<?php

/**
 * @OA\Get(
 *     path="/api/genres",
 *     tags={"genres"},
 *     summary="Get all genres",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all genres in the database"
 *     )
 * )
 */
Flight::route('GET /api/genres', function()  {
    try {
        $genres = Flight::genreService()->getAll();
        Flight::json([
            'success' => true,
            'data' => $genres,
            'count' => count($genres)
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
 *     path="/api/genres/{id}",
 *     tags={"genres"},
 *     summary="Get genre by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the genre",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the genre with the given ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Genre not found"
 *     )
 * )
 */
Flight::route('GET /api/genres/@id', function($id)  {
    try {
        $genre = Flight::genreService()->getById($id);
        if ($genre) {
            Flight::json([
                'success' => true,
                'data' => $genre
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'Genre not found'
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
 *     path="/api/genres",
 *     tags={"genres"},
 *     summary="Add a new genre",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Fiction")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Genre created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or duplicate genre"
 *     )
 * )
 */
Flight::route('POST /api/genres', function()  {
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

        $genreId = Flight::genreService()->createGenre($data);
        $genre = Flight::genreService()->getById($genreId);
        
        Flight::json([
            'success' => true,
            'message' => 'Genre created successfully',
            'data' => $genre
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
 *     path="/api/genres/{id}",
 *     tags={"genres"},
 *     summary="Update an existing genre by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Genre ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Genre Name")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Genre updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Genre not found"
 *     )
 * )
 */
Flight::route('PUT /api/genres/@id', function($id)  {
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

        $genre = Flight::genreService()->getById($id);
        if (!$genre) {
            Flight::json([
                'success' => false,
                'message' => 'Genre not found'
            ], 404);
            return;
        }

        Flight::genreService()->updateGenre($id, $data);
        $updatedGenre = Flight::genreService()->getById($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Genre updated successfully',
            'data' => $updatedGenre
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
 *     path="/api/genres/{id}",
 *     tags={"genres"},
 *     summary="Delete a genre by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Genre ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Genre deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Genre not found"
 *     )
 * )
 */
Flight::route('DELETE /api/genres/@id', function($id)  {
    try {
        $genre = Flight::genreService()->getById($id);
        if (!$genre) {
            Flight::json([
                'success' => false,
                'message' => 'Genre not found'
            ], 404);
            return;
        }

        Flight::genreService()->delete($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Genre deleted successfully'
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

?>

