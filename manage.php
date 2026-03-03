<?php
/*
 * MANAGE.PHP - CRUD Management Page
 * 
 * This page demonstrates the full CRUD cycle:
 * C - Create (Add new entries)
 * R - Read (Display all entries)
 * U - Update (Edit existing entries)
 * D - Delete (Remove entries)
 * 
 * Key PHP Concepts Demonstrated:
 * - Form handling with $_POST and $_GET superglobals
 * - Switch statements for multiple conditions
 * - String functions (trim)
 * - Validation and error handling
 * - Dynamic form behavior (add vs edit mode)
 */

require_once 'config.php';

// =============================================================================
// INITIALIZE VARIABLES
// =============================================================================
$message = '';      // String to hold success/error messages
$editItem = null;   // Variable to hold item being edited (null = not editing)

// =============================================================================
// FORM HANDLING - PROCESS POST REQUESTS
// =============================================================================
/*
 * $_POST is a SUPERGLOBAL array that contains form data sent via POST method
 * $_SERVER is another SUPERGLOBAL with information about the request
 * 
 * SUPERGLOBALS are built-in PHP arrays available everywhere:
 * - $_POST: Form data sent via POST
 * - $_GET: Data from URL parameters
 * - $_SERVER: Server and environment information
 * - $_SESSION: Session data
 * - $_COOKIE: Cookie data
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if 'action' field exists in the form data
    if (isset($_POST['action'])) {
        /*
         * SWITCH STATEMENT - Alternative to multiple if/else
         * More efficient when checking one variable against multiple values
         */
        switch ($_POST['action']) {
            
            // =============================================================================
            // CREATE OPERATION
            // =============================================================================
             case 'create':
                
            // Get form data and clean it
            // trim() FUNCTION removes whitespace from beginning and end
            $phrase = trim($_POST['phrase']);
            $joke = trim($_POST['jokes']);
            $image_filename = null; // Default: no image

            // Handle image upload
            if (isset($_FILES['entry_image']) && $_FILES['entry_image']['size'] > 0) {
                
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['entry_image']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    
                    // Validate file size (2MB max)
                    if ($_FILES['entry_image']['size'] <= 2 * 1024 * 1024) {
                        
                        // Create unique filename
                        $extension = pathinfo($_FILES['entry_image']['name'], PATHINFO_EXTENSION);
                        $image_filename = 'quote_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                        
                        // Create uploads directory if it doesn't exist
                        if (!file_exists('uploads')) {
                            mkdir('uploads', 0777, true);
                        }
                        
                        // Move uploaded file
                        $upload_path = 'uploads/' . $image_filename;
                        if (!move_uploaded_file($_FILES['entry_image']['tmp_name'], $upload_path)) {
                            $message = "Failed to upload image!";
                            $messageType = "error";
                            $image_filename = null;
                        }
                    } else {
                        $message = "Image size must be less than 2MB!";
                        $messageType = "error";
                    }
                } else {
                    $message = "Only JPG, PNG, and GIF images are allowed!";
                    $messageType = "error";
                }
            }
            
            // Validation: Make sure at least one field has content
            if (!empty($phrase) || !empty($joke)) {
                // Prepare INSERT statement
                // NOW() is a MySQL function that gets current timestamp
                $stmt = $pdo->prepare("INSERT INTO quotes (phrase, jokes, image_filename, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$phrase, $joke, $image_filename]);
                $message = "Entry added successfully!";
            } else {
                $message = "Please enter either a phrase or a joke.";
            }
            break;  // Exit the switch statement

            // =============================================================================
            // UPDATE OPERATION
            // =============================================================================
            case 'update':
                $id = $_POST['id'];           // Get the ID of item to update
                $phrase = trim($_POST['phrase']);
                $joke = trim($_POST['jokes']);

                // Get current entry to check existing image
                $stmt = $pdo->prepare("SELECT image_filename FROM quotes WHERE id = ?");
                $stmt->execute([$id]);
                $currentEntry = $stmt->fetch();
                $image_filename = $currentEntry['image_filename']; // Keep existing image by default
            
                 // Handle new image upload
                if (isset($_FILES['entry_image']) && $_FILES['entry_image']['error'] === UPLOAD_ERR_OK) {
                    // Validate file type
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $file_type = $_FILES['entry_image']['type'];
                    
                    if (in_array($file_type, $allowed_types)) {
                        // Validate file size (2MB max)
                        if ($_FILES['entry_image']['size'] <= 2 * 1024 * 1024) {
                            // Create unique filename
                            $extension = pathinfo($_FILES['entry_image']['name'], PATHINFO_EXTENSION);
                            $new_image_filename = 'quote_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                            
                            // Create uploads directory if it doesn't exist
                            if (!file_exists('uploads')) {
                                mkdir('uploads', 0777, true);
                            }
                            
                            // Move uploaded file
                            $upload_path = 'uploads/' . $new_image_filename;
                            if (move_uploaded_file($_FILES['entry_image']['tmp_name'], $upload_path)) {
                                // Delete old image if exists
                                if ($image_filename && file_exists('uploads/' . $image_filename)) {
                                    unlink('uploads/' . $image_filename);
                                }
                                // Update to new image filename
                                $image_filename = $new_image_filename;
                            } else {
                                $message = "Failed to upload new image!";
                                $messageType = "error";
                            }
                        } else {
                            $message = "Image size must be less than 2MB!";
                            $messageType = "error";
                        }
                    } else {
                        $message = "Only JPG, PNG, and GIF images are allowed!";
                        $messageType = "error";
                    }
                }
                
                // Check if user wants to remove the image
                if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
                    // Delete old image file if exists
                    if ($image_filename && file_exists('uploads/' . $image_filename)) {
                        unlink('uploads/' . $image_filename);
                    }
                    $image_filename = null;
                }

                            
                 // Prepare UPDATE statement with image
                $stmt = $pdo->prepare("UPDATE quotes SET phrase = ?, jokes = ?, image_filename = ? WHERE id = ?");
                $stmt->execute([$phrase, $joke, $image_filename, $id]);
                
                if (!isset($message) || empty($message)) {
                    $message = "Entry updated successfully!";
                }
                break;
                
            // =============================================================================
            // DELETE OPERATION
            // =============================================================================
            case 'delete':
                $id = $_POST['id'];

                // Get image filename before deleting
                $stmt = $pdo->prepare("SELECT image_filename FROM quotes WHERE id = ?");
                $stmt->execute([$id]);
                $entry = $stmt->fetch();
                
                // Prepare DELETE statement
                $stmt = $pdo->prepare("DELETE FROM quotes WHERE id = ?");
                $stmt->execute([$id]);
                // Delete image file if exists
                if ($entry && $entry['image_filename'] && file_exists('uploads/' . $entry['image_filename'])) {
                    unlink('uploads/' . $entry['image_filename']);
                }
                $message = "Entry deleted successfully!";
                break;
        }
    }
}

// =============================================================================
// HANDLE EDIT REQUEST FROM URL
// =============================================================================
/*
 * $_GET contains data from URL parameters
 * Example: manage.php?edit=5 would have $_GET['edit'] = 5
 * isset() FUNCTION checks if a variable exists and is not null
 */
if (isset($_GET['edit'])) {
    // Get the specific item to edit
    $stmt = $pdo->prepare("SELECT * FROM quotes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();  // fetch() gets just one row, fetchAll() gets all rows
}

// =============================================================================
// GET ALL QUOTES FOR DISPLAY
// =============================================================================
$stmt = $pdo->prepare("SELECT * FROM quotes ORDER BY created_at DESC");
$stmt->execute();
$allQuotes = $stmt->fetchAll();

/*
 * LEARNING NOTES - UNDERSTANDING ARRAYS AND DATABASE RESULTS:
 * 
 * When we do $stmt->fetchAll(), we get a multidimensional array like this:
 * 
 * $allQuotes = [
 *     0 => ['id' => 1, 'phrase' => 'Hello', 'jokes' => 'Why did...', 'created_at' => '2023...'],
 *     1 => ['id' => 2, 'phrase' => 'World', 'jokes' => 'What do...', 'created_at' => '2023...']
 * ]
 * 
 * Each row is an associative array where keys are column names.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jokes & Phrases ⚙️</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="manage-style.css">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <h1>Manage Content ⚙️</h1>
            <p class="subtitle">Add, edit, and organize your jokes and phrases</p>
        </header>
    
    <?php 
    /*
     * DISPLAY MESSAGES
     * Only show message if it exists (not empty)
     */
    if ($message): ?>
        <div class="message success">
            <span>✅</span>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    <?php endif; ?>
    
    <!-- 
    =============================================================================
    DYNAMIC FORM - CHANGES BASED ON WHETHER WE'RE ADDING OR EDITING
    =============================================================================
    -->
    <section class="form-section">
        <h2>
            <?php echo $editItem ? '✏️ Edit Entry' : '➕ Add New Entry'; ?>
        </h2>
        
        <!-- 
            FIX APPLIED: enctype="multipart/form-data" is REQUIRED for file uploads.
            Without this attribute, PHP's $_FILES array will always be empty,
            meaning no image will ever reach the server no matter what you do.
            This is the most common reason image uploads silently fail.
        -->
        <form method="POST" enctype="multipart/form-data">
            <!--
            HIDDEN FIELDS - Not visible to user but sent with form
            These help us know what action to perform
            -->
            <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
            
            <?php if ($editItem): ?>
                <!-- If editing, we need to send the ID -->
                <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="phrase">💭 Phrase of Wisdom:</label>
                <textarea 
                    id="phrase" 
                    name="phrase" 
                    rows="3" 
                    placeholder="Enter an inspiring phrase or quote..."><?php 
                    /*
                     * PRE-FILL FORM FIELDS WHEN EDITING
                     * If $editItem exists, show its data, otherwise show empty
                     */
                    echo $editItem ? htmlspecialchars($editItem['phrase']) : ''; 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="jokes">😄 Daily Joke:</label>
                <textarea 
                    id="jokes" 
                    name="jokes" 
                    rows="3" 
                    placeholder="Enter a funny joke to brighten someone's day..."><?php 
                    echo $editItem ? htmlspecialchars($editItem['jokes']) : ''; 
                ?></textarea>
            </div>

            <!-- Image upload field -->
            <div class="form-group">
               <label>
                    Add Image (Optional):
                    <input type="file" name="entry_image" accept="image/jpeg,image/png,image/gif">
                    <small>Max size: 2MB. Formats: JPG, PNG, GIF</small>
               </label>
                
                <?php if ($editItem && $editItem['image_filename']): ?>
                    <div style="margin: 10px 0;">
                        <strong>Current Image:</strong><br>
                        <img src="uploads/<?php echo htmlspecialchars($editItem['image_filename']); ?>" 
                            alt="Current" style="max-width: 200px; margin: 5px 0;">
                        <label style="display: block;">
                            <input type="checkbox" name="remove_image" value="1"> Remove current image
                        </label>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $editItem ? '💾 Update Entry' : '➕ Add Entry'; ?>
                </button>
                <?php if ($editItem): ?>
                    <!-- Show cancel link only when editing -->
                    <a href="manage.php" class="btn-secondary">❌ Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </section>
    
    <!-- 
    =============================================================================
    DISPLAY ALL ENTRIES IN A TABLE
    =============================================================================
    -->
    <section class="table-section">
    <a href="index.php" class="back-link">← Back to Daily View</a>

        <h2>📋 All Entries</h2>
        <?php if (empty($allQuotes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No Entries Yet</h3>
                <p>Start by adding your first joke or phrase above!</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phrase</th>
                        <th>Joke</th>
                        <th>Image</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allQuotes as $quote): ?>
                        <tr>
                            <td data-label="ID" class="id-cell">
                                #<?php echo htmlspecialchars($quote['id']); ?>
                            </td>
                            <td data-label="Phrase" class="preview-cell"><?php 
                                /*
                                 * TRUNCATE LONG TEXT FOR TABLE DISPLAY
                                 * substr() FUNCTION gets part of a string
                                 * Syntax: substr(string, start, length)
                                 * strlen() FUNCTION gets string length
                                 */
                                $phrasePreview = htmlspecialchars(substr($quote['phrase'], 0, 50));
                                if (strlen($quote['phrase']) > 50) {
                                    $phrasePreview .= '...';  // .= means "add to the end of"
                                }
                                echo $phrasePreview ?: '<em style="color: #cbd5e0;">No phrase</em>';
                            ?></td>
                            <td data-label="Joke" class="preview-cell"><?php 
                                // Same truncation for jokes
                                $jokePreview = htmlspecialchars(substr($quote['jokes'], 0, 50));
                                if (strlen($quote['jokes']) > 50) {
                                    $jokePreview .= '...';
                                }
                                echo $jokePreview ?: '<em style="color: #cbd5e0;">No joke</em>';
                            ?></td>
                            <!-- Display image thumbnail -->
                            <!-- 
                                FIX APPLIED: Changed $entry['image_filename'] to $quote['image_filename']
                                
                                WHY THIS WAS BROKEN:
                                $entry was only defined inside the 'delete' case above (lines ~194-203).
                                Inside this foreach loop, the current row is stored in $quote (set by the
                                foreach statement: "foreach ($allQuotes as $quote)").
                                Using $entry here would either be undefined (PHP notice) or — if a delete
                                had just run — would accidentally reference the last deleted row's data.
                            -->
                            <td data-label="Image">
                                <?php if ($quote['image_filename']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($quote['image_filename']); ?>" 
                                        alt="Entry image" 
                                        style="max-width: 80px; max-height: 60px; border-radius: 6px;">
                                <?php else: ?>
                                    <span style="color: #999;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Created" class="date-cell">
                                <?php echo date('M j, Y', strtotime($quote['created_at'])); ?>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <!-- 
                                EDIT LINK - Uses GET parameter to identify which item to edit
                                URL will be: manage.php?edit=5 (where 5 is the ID)
                                -->
                                <a href="manage.php?edit=<?php echo $quote['id']; ?>" class="btn-edit">
                                    ✏️ Edit
                                </a>
                                
                                <!-- 
                                DELETE FORM - Inline form with JavaScript confirmation
                                onsubmit runs JavaScript before form submits
                                confirm() shows a yes/no dialog to user
                                -->
                                <form method="POST" class="delete-form" 
                                      onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
                                    <button type="submit" class="btn-delete">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
        
    </div> <!-- Close container -->

<!--
=============================================================================
COMPREHENSIVE LEARNING SUMMARY
=============================================================================

1. SUPERGLOBALS (Built-in PHP arrays available everywhere):
   $_POST    - Form data sent via POST method
   $_GET     - Data from URL parameters (?param=value)
   $_SERVER  - Server information (like request method)
   $_SESSION - Session data (for login systems)
   $_COOKIE  - Cookie data

2. IMPORTANT FUNCTIONS USED:
   trim()        - Remove whitespace from start/end of string
   empty()       - Check if variable is empty
   isset()       - Check if variable exists
   substr()      - Get part of a string
   strlen()      - Get length of string
   htmlspecialchars() - Convert special chars to HTML entities (security)

3. IMPORTANT METHODS USED:
   $pdo->prepare()  - Create prepared statement
   $stmt->execute() - Run the prepared statement
   $stmt->fetch()   - Get one row from results
   $stmt->fetchAll() - Get all rows from results

4. CONTROL STRUCTURES:
   if/else       - Basic conditionals
   switch/case   - Multiple condition checking
   foreach       - Loop through arrays
   Ternary (?:)  - Shorthand if/else

5. ARRAY TYPES:
   Indexed:      $arr = [1, 2, 3]
   Associative:  $arr = ['name' => 'John', 'age' => 25]
   Multi-dimensional: Array of arrays (like database results)

6. SECURITY PRACTICES:
   - Always use prepared statements for database queries
   - Always use htmlspecialchars() for output
   - Validate user input
   - Use confirmation for destructive actions (delete)

7. FORM HANDLING PATTERN:
   1. Check if form was submitted ($_SERVER['REQUEST_METHOD'] == 'POST')
   2. Validate the input data
   3. Process the data (database operations)
   4. Show success/error message
   5. Display the form (possibly pre-filled for editing)
-->

</body>
</html>