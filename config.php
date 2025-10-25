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
// IMPORTANT: iPage uses a remote MySQL server, not 'localhost'
$host = 'inventionuniversecom.ipagemysql.com';  // ✅ Your actual iPage MySQL server, for dev use localhost
$dbname = 'wisdom_quotes_database';              // Your database name
$username = 'wisdom_admin';                            // Your database username for dev use root
$password = 'Admin@2025';                        // Your database password for dev use root


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
    
    // Set the character set to UTF-8 for proper encoding
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
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
 * 
 * 6. iPAGE SPECIFIC:
 *    - iPage uses remote MySQL servers, not 'localhost'
 *    - The hostname format is: yourdomain.ipagemysql.com
 *    - This is normal for shared hosting environments
 */
?>