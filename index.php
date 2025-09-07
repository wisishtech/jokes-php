<?php
/*
 * INDEX.PHP - Display Page for Daily Jokes and Phrases
 * 
 * This page shows jokes and phrases for today, or recent ones if none exist for today.
 * 
 * Key PHP Concepts Demonstrated:
 * - Including external files
 * - Working with dates
 * - Database queries with prepared statements
 * - Arrays and loops
 * - Conditional statements (if/else)
 * - Mixing PHP with HTML
 */

// =============================================================================
// INCLUDE EXTERNAL FILES
// =============================================================================
// require_once() is a FUNCTION that includes another PHP file
// 'once' means it will only include it one time (prevents duplicate includes)
require_once 'config.php';  // This gives us access to the $pdo variable

// =============================================================================
// GETTING TODAY'S DATE
// =============================================================================
// date() is a built-in PHP FUNCTION that formats the current date/time
// 'Y-m-d' means: Year-month-day (2023-12-25)
$today = date('Y-m-d');

// =============================================================================
// DATABASE QUERY - GET TODAY'S QUOTES
// =============================================================================
// prepare() is a METHOD that creates a prepared statement (secure way to query)
// The ? is a placeholder that we'll fill in later (prevents SQL injection)
$stmt = $pdo->prepare("SELECT * FROM quotes WHERE DATE(created_at) = ? ORDER BY created_at DESC");

// execute() is a METHOD that runs the query
// We pass an ARRAY with values to replace the ? placeholders
$stmt->execute([$today]);  // This replaces ? with $today

// fetchAll() is a METHOD that gets all results as an array
$todayQuotes = $stmt->fetchAll();

// =============================================================================
// CONDITIONAL LOGIC - CHECK IF WE HAVE RESULTS
// =============================================================================
// empty() is a FUNCTION that checks if a variable is empty
if (empty($todayQuotes)) {
    // No quotes for today, let's get recent ones instead
    // This is a new query - we can reuse the $stmt variable
    $stmt = $pdo->prepare("SELECT * FROM quotes ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();  // No placeholders this time, so empty array
    $todayQuotes = $stmt->fetchAll();
    $showingRecent = true;  // Boolean variable to track what we're showing
} else {
    // We have quotes for today
    $showingRecent = false;
}

/*
 * LEARNING NOTES ABOUT PREPARED STATEMENTS:
 * 
 * 1. WHY USE PREPARED STATEMENTS?
 *    - Security: Prevents SQL injection attacks
 *    - Performance: Can be reused efficiently
 * 
 * 2. THE PROCESS:
 *    - prepare(): Create the statement with placeholders (?)
 *    - execute(): Run it with actual values
 *    - fetch methods: Get the results
 * 
 * 3. FETCH METHODS:
 *    - fetchAll(): Gets all results as an array
 *    - fetch(): Gets one row at a time
 *    - fetchColumn(): Gets just one column value
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daily Jokes and Phrases</title>
</head>
<body>
    <h1>Daily Jokes and Phrases</h1>
    
    <?php 
    /*
     * CONDITIONAL OUTPUT IN HTML
     * We can use PHP inside HTML to make decisions about what to display
     * The colon syntax (if: else: endif;) is alternative to braces
     */
    ?>
    <?php if ($showingRecent): ?>
        <p><em>No entries for today. Showing recent entries:</em></p>
    <?php else: ?>
        <p><em>Today's entries:</em></p>
    <?php endif; ?>
    
    <?php 
    /*
     * ANOTHER CONDITIONAL - CHECK IF WE HAVE ANY QUOTES AT ALL
     */
    if (empty($todayQuotes)): ?>
        <p>No jokes or phrases available.</p>
    <?php else: ?>
        <?php 
        /*
         * FOREACH LOOP - ITERATE THROUGH ARRAY
         * foreach() goes through each item in an array
         * $todayQuotes is an array of database rows
         * Each $quote is an associative array with column names as keys
         */
        foreach ($todayQuotes as $quote): ?>
            <div>
                <!-- 
                ACCESS ARRAY VALUES:
                $quote is an associative array: $quote['column_name'] 
                htmlspecialchars() is a SECURITY FUNCTION that prevents XSS attacks
                It converts special characters to safe HTML entities
                -->
                <h3>Entry #<?php echo htmlspecialchars($quote['id']); ?></h3>
                
                <?php 
                /*
                 * CHECK IF FIELDS HAVE CONTENT
                 * !empty() means "if not empty"
                 * We only show sections that have content
                 */
                if (!empty($quote['phrase'])): ?>
                    <h4>Phrase:</h4>
                    <p><?php echo htmlspecialchars($quote['phrase']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($quote['jokes'])): ?>
                    <h4>Joke:</h4>
                    <p><?php echo htmlspecialchars($quote['jokes']); ?></p>
                <?php endif; ?>
                
                <small>Added: <?php echo htmlspecialchars($quote['created_at']); ?></small>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <p><a href="manage.php">Manage Jokes and Phrases</a></p>

<!--
SUMMARY OF KEY CONCEPTS USED:

1. FUNCTIONS vs METHODS:
   - Functions: date(), empty(), htmlspecialchars()
   - Methods: $pdo->prepare(), $stmt->execute(), $stmt->fetchAll()

2. ARRAYS:
   - Indexed arrays: [1, 2, 3]
   - Associative arrays: ['name' => 'John', 'age' => 25]
   - Database results are associative arrays

3. LOOPS:
   - foreach: Best for arrays
   - for: Best when you know how many times to loop
   - while: Best when you don't know how many times

4. CONDITIONALS:
   - if/else: Basic decision making
   - Alternative syntax: if(): endif; (good for mixing with HTML)

5. SECURITY:
   - Prepared statements: Prevent SQL injection
   - htmlspecialchars(): Prevent XSS attacks
-->

</body>
</html>