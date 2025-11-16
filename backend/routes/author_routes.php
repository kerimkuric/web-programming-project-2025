<?php

/**
 * @OA\Get(
 *     path="/api/authors",
 *     tags={"authors"},
 *     summary="Get all authors",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all authors in the database"
 *     )
 * )
 */
Flight::route('GET /api/authors', function() {
    try {
        $authors = Flight::authorService()->getAll();
        Flight::json([
            'success' => true,
            'data' => $authors,
            'count' => count($authors)
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
 *     path="/api/authors/{id}",
 *     tags={"authors"},
 *     summary="Get author by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the author",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the author with the given ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Author not found"
 *     )
 * )
 */
Flight::route('GET /api/authors/@id', function($id) {
    try {
        $author = Flight::authorService()->getById($id);
        if ($author) {
            Flight::json([
                'success' => true,
                'data' => $author
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'Author not found'
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
 *     path="/api/authors",
 *     tags={"authors"},
 *     summary="Add a new author",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "country"},
 *             @OA\Property(property="name", type="string", example="J.K. Rowling"),
 *             @OA\Property(property="country", type="string", example="United Kingdom")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Author created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     )
 * )
 */
Flight::route('POST /api/authors', function() {
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

        $authorId = Flight::authorService()->createAuthor($data);
        $author = Flight::authorService()->getById($authorId);
        
        Flight::json([
            'success' => true,
            'message' => 'Author created successfully',
            'data' => $author
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
 *     path="/api/authors/{id}",
 *     tags={"authors"},
 *     summary="Update an existing author by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Author ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Author Name"),
 *             @OA\Property(property="country", type="string", example="New Country")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Author updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Author not found"
 *     )
 * )
 */
Flight::route('PUT /api/authors/@id', function($id) {
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

        $author = Flight::authorService()->getById($id);
        if (!$author) {
            Flight::json([
                'success' => false,
                'message' => 'Author not found'
            ], 404);
            return;
        }

        Flight::authorService()->updateAuthor($id, $data);
        $updatedAuthor = Flight::authorService()->getById($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Author updated successfully',
            'data' => $updatedAuthor
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
 *     path="/api/authors/{id}",
 *     tags={"authors"},
 *     summary="Delete an author by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Author ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Author deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Author not found"
 *     )
 * )
 */
Flight::route('DELETE /api/authors/@id', function($id) {
    try {
        $author = Flight::authorService()->getById($id);
        if (!$author) {
            Flight::json([
                'success' => false,
                'message' => 'Author not found'
            ], 404);
            return;
        }

        Flight::authorService()->delete($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Author deleted successfully'
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
 *     path="/api/authors/country/{country}",
 *     tags={"authors"},
 *     summary="Get authors by country",
 *     @OA\Parameter(
 *         name="country",
 *         in="path",
 *         required=true,
 *         description="Country name",
 *         @OA\Schema(type="string", example="United Kingdom")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of authors from the specified country"
 *     )
 * )
 */
Flight::route('GET /api/authors/country/@country', function($country) {
    try {
        $authors = Flight::authorService()->getByCountry($country);
        Flight::json([
            'success' => true,
            'data' => array_values($authors),
            'count' => count($authors)
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

?>

