<?php

/**
 * @OA\Info(
 *     title="Library Management System API",
 *     description="REST API for managing library operations including books, authors, genres, users, and borrowing records",
 *     version="1.0",
 *     @OA\Contact(
 *         email="web2001programming@gmail.com",
 *         name="Web Programming"
 *     )
 * )
 */

/**
 * @OA\Server(
 *     url= "http://localhost:8000",
 *     description="API server"
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="ApiKey",
 *     type="apiKey",
 *     in="header",
 *     name="Authentication"
 * )
 */

