<?php

/**
 * @OA\Get(
 *     path="/api/borrowings",
 *     tags={"borrowings"},
 *     summary="Get all borrowings",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all borrowings in the database"
 *     )
 * )
 */
Flight::route('GET /api/borrowings', function()  {
    try {
        $borrowings = Flight::borrowingService()->getAll();
        Flight::json([
            'success' => true,
            'data' => $borrowings,
            'count' => count($borrowings)
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
 *     path="/api/borrowings/{id}",
 *     tags={"borrowings"},
 *     summary="Get borrowing by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the borrowing",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the borrowing with the given ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Borrowing not found"
 *     )
 * )
 */
Flight::route('GET /api/borrowings/@id', function($id)  {
    try {
        $borrowing = Flight::borrowingService()->getById($id);
        if ($borrowing) {
            Flight::json([
                'success' => true,
                'data' => $borrowing
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'Borrowing not found'
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
 *     path="/api/borrowings",
 *     tags={"borrowings"},
 *     summary="Create a new borrowing",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "book_id", "borrow_date"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="book_id", type="integer", example=1),
 *             @OA\Property(property="borrow_date", type="string", format="date", example="2025-01-15")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Borrowing created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or book already borrowed"
 *     )
 * )
 */
Flight::route('POST /api/borrowings', function()  {
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

        $borrowingId = Flight::borrowingService()->createBorrowing($data);
        $borrowing = Flight::borrowingService()->getById($borrowingId);
        
        Flight::json([
            'success' => true,
            'message' => 'Borrowing created successfully',
            'data' => $borrowing
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
 *     path="/api/borrowings/{id}",
 *     tags={"borrowings"},
 *     summary="Update an existing borrowing by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Borrowing ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="book_id", type="integer", example=1),
 *             @OA\Property(property="borrow_date", type="string", format="date", example="2025-01-15"),
 *             @OA\Property(property="return_date", type="string", format="date", example="2025-01-20")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Borrowing updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Borrowing not found"
 *     )
 * )
 */
Flight::route('PUT /api/borrowings/@id', function($id)  {
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

        $borrowing = Flight::borrowingService()->getById($id);
        if (!$borrowing) {
            Flight::json([
                'success' => false,
                'message' => 'Borrowing not found'
            ], 404);
            return;
        }

        Flight::borrowingService()->updateBorrowing($id, $data);
        $updatedBorrowing = Flight::borrowingService()->getById($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Borrowing updated successfully',
            'data' => $updatedBorrowing
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
 *     path="/api/borrowings/{id}",
 *     tags={"borrowings"},
 *     summary="Delete a borrowing by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Borrowing ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Borrowing deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Borrowing not found"
 *     )
 * )
 */
Flight::route('DELETE /api/borrowings/@id', function($id)  {
    try {
        $borrowing = Flight::borrowingService()->getById($id);
        if (!$borrowing) {
            Flight::json([
                'success' => false,
                'message' => 'Borrowing not found'
            ], 404);
            return;
        }

        Flight::borrowingService()->delete($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Borrowing deleted successfully'
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

/**
 * @OA\Post(
 *     path="/api/borrowings/{id}/return",
 *     tags={"borrowings"},
 *     summary="Return a borrowed book",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Borrowing ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Book returned successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Book already returned or borrowing not found"
 *     )
 * )
 */
Flight::route('POST /api/borrowings/@id/return', function($id)  {
    try {
        Flight::borrowingService()->returnBook($id);
        $borrowing = Flight::borrowingService()->getById($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Book returned successfully',
            'data' => $borrowing
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

/**
 * @OA\Get(
 *     path="/api/borrowings/user/{userId}",
 *     tags={"borrowings"},
 *     summary="Get borrowings by user",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of borrowings for the specified user"
 *     )
 * )
 */
Flight::route('GET /api/borrowings/user/@userId', function($userId)  {
    try {
        $borrowings = Flight::borrowingService()->getByUserId($userId);
        Flight::json([
            'success' => true,
            'data' => array_values($borrowings),
            'count' => count($borrowings)
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
 *     path="/api/borrowings/book/{bookId}",
 *     tags={"borrowings"},
 *     summary="Get borrowings by book",
 *     @OA\Parameter(
 *         name="bookId",
 *         in="path",
 *         required=true,
 *         description="Book ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of borrowings for the specified book"
 *     )
 * )
 */
Flight::route('GET /api/borrowings/book/@bookId', function($bookId)  {
    try {
        $borrowings = Flight::borrowingService()->getByBookId($bookId);
        Flight::json([
            'success' => true,
            'data' => array_values($borrowings),
            'count' => count($borrowings)
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
 *     path="/api/borrowings/active",
 *     tags={"borrowings"},
 *     summary="Get active borrowings (not returned)",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all active borrowings (books not yet returned)"
 *     )
 * )
 */
Flight::route('GET /api/borrowings/active', function()  {
    try {
        $borrowings = Flight::borrowingService()->getActiveBorrowings();
        Flight::json([
            'success' => true,
            'data' => array_values($borrowings),
            'count' => count($borrowings)
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

?>

