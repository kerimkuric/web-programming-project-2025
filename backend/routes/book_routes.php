<?php

/**
 * @OA\Get(
 *     path="/api/books",
 *     tags={"books"},
 *     summary="Get all books",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all books in the database"
 *     )
 * )
 */
Flight::route('GET /api/books', function()  {
    try {
        $books = Flight::bookService()->getAll();
        Flight::json([
            'success' => true,
            'data' => $books,
            'count' => count($books)
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
 *     path="/api/books/{id}",
 *     tags={"books"},
 *     summary="Get book by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the book",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the book with the given ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Book not found"
 *     )
 * )
 */
Flight::route('GET /api/books/@id', function($id)  {
    try {
        $book = Flight::bookService()->getById($id);
        if ($book) {
            Flight::json([
                'success' => true,
                'data' => $book
            ], 200);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'Book not found'
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
 *     path="/api/books",
 *     tags={"books"},
 *     summary="Add a new book",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "author_id", "genre_id", "year", "isbn"},
 *             @OA\Property(property="title", type="string", example="The Great Gatsby"),
 *             @OA\Property(property="author_id", type="integer", example=1),
 *             @OA\Property(property="genre_id", type="integer", example=1),
 *             @OA\Property(property="year", type="integer", example=1925),
 *             @OA\Property(property="isbn", type="string", example="9780743273565")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Book created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or validation error"
 *     )
 * )
 */
Flight::route('POST /api/books', function()  {
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

        $bookId = Flight::bookService()->createBook($data);
        $book = Flight::bookService()->getById($bookId);
        
        Flight::json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book
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
 *     path="/api/books/{id}",
 *     tags={"books"},
 *     summary="Update an existing book by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Book ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string", example="Updated Book Title"),
 *             @OA\Property(property="author_id", type="integer", example=1),
 *             @OA\Property(property="genre_id", type="integer", example=1),
 *             @OA\Property(property="year", type="integer", example=2023),
 *             @OA\Property(property="isbn", type="string", example="9780743273565")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Book updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Book not found"
 *     )
 * )
 */
Flight::route('PUT /api/books/@id', function($id)  {
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

        $book = Flight::bookService()->getById($id);
        if (!$book) {
            Flight::json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
            return;
        }

        Flight::bookService()->updateBook($id, $data);
        $updatedBook = Flight::bookService()->getById($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $updatedBook
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
 *     path="/api/books/{id}",
 *     tags={"books"},
 *     summary="Delete a book by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Book ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Book deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Book not found"
 *     )
 * )
 */
Flight::route('DELETE /api/books/@id', function($id)  {
    try {
        $book = Flight::bookService()->getById($id);
        if (!$book) {
            Flight::json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
            return;
        }

        Flight::bookService()->delete($id);
        
        Flight::json([
            'success' => true,
            'message' => 'Book deleted successfully'
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
 *     path="/api/books/author/{authorId}",
 *     tags={"books"},
 *     summary="Get books by author",
 *     @OA\Parameter(
 *         name="authorId",
 *         in="path",
 *         required=true,
 *         description="Author ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of books by the specified author"
 *     )
 * )
 */
Flight::route('GET /api/books/author/@authorId', function($authorId)  {
    try {
        $books = Flight::bookService()->getByAuthor($authorId);
        Flight::json([
            'success' => true,
            'data' => array_values($books),
            'count' => count($books)
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
 *     path="/api/books/genre/{genreId}",
 *     tags={"books"},
 *     summary="Get books by genre",
 *     @OA\Parameter(
 *         name="genreId",
 *         in="path",
 *         required=true,
 *         description="Genre ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Array of books in the specified genre"
 *     )
 * )
 */
Flight::route('GET /api/books/genre/@genreId', function($genreId)  {
    try {
        $books = Flight::bookService()->getByGenre($genreId);
        Flight::json([
            'success' => true,
            'data' => array_values($books),
            'count' => count($books)
        ], 200);
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

?>

