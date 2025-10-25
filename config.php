<?php
/*
 * CONFIG.PHP - Database Connection File
 * 
 * This file handles the connection to our MySQL database.
 * We use PDO (PHP Data Objects) which is a secure way to connect to databases.
 * 
 * Key PHP Concepts Demonstrated:
 * - Variables (start with $)
 * - Try-catch blocks for error handling
 * - Object-oriented programming with classes and methods
 * - Constants and method chaining
 */

// =============================================================================
// DATABASE CONFIGURATION VARIABLES
// =============================================================================
// In PHP, variables start with $ and don't need to be declared with a type
$host = 'localhost';           // String: Where our database server is located
$dbname = 'wisdom_quotes_database';   // String: Name of our database
$username = 'wisdom';            // String: Default MAMP username
$password = 'Admin@2025';            // String: Default MAMP password 
// $port = '8889';               // String: Default database server port connnection


// =============================================================================
// DATABASE CONNECTION USING TRY-CATCH
// =============================================================================
/* 
 * TRY-CATCH blocks help us handle errors gracefully
 * If something goes wrong in the 'try' block, the 'catch' block runs
 */
try {
    // Create a new PDO object (this is a CLASS from PHP)
    // PDO constructor takes: "database_type:host=hostname;dbname=database_name", username, password
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set error mode to exception - this means if something goes wrong, throw an error
    // setAttribute() is a METHOD that belongs to the PDO CLASS
    // We're calling: $object->method()
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // If we get here, connection was successful!
    
} catch(PDOException $e) {
    // If connection fails, this block runs
    // $e is the exception object that contains error information
    // getMessage() is a METHOD that gets the error message
    // die() is a FUNCTION that stops the script and displays a message
    die("Connection failed: " . $e->getMessage());
}

/*
 * LEARNING NOTES:
 * 
 * 1. VARIABLES: In PHP, all variables start with $
 *    Example: $name = "John";
 * 
 * 2. OBJECTS AND CLASSES:
 *    - A CLASS is like a blueprint (PDO is a class)
 *    - An OBJECT is an instance of a class ($pdo is an object)
 *    - METHODS are functions that belong to a class
 * 
 * 3. CALLING METHODS:
 *    - Use -> to call methods: $object->method()
 *    - Example: $pdo->setAttribute()
 * 
 * 4. STRING CONCATENATION:
 *    - Use . (dot) to join strings: "Hello " . "World"
 * 
 * 5. ERROR HANDLING:
 *    - try { } catch { } helps prevent crashes
 *    - Always handle database connection errors!
 */
?>