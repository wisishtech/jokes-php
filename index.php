<?php
/*
 * INDEX.PHP - Daily Joke & Phrase Display
 * 
 * This page shows a random daily joke and phrase to brighten users' day.
 * Each visit displays fresh content from the database.
 * 
 * Key Concepts:
 * - Random data selection with SQL ORDER BY RAND()
 * - Fallback content when database is empty
 * - Modern, animated UI design
 */

require_once 'config.php';

// =============================================================================
// GET A SINGLE RANDOM ENTRY WITH BOTH JOKE AND PHRASE (AND OPTIONAL IMAGE)
// =============================================================================
/*
 * FIX APPLIED: Previously the page ran TWO separate queries — one for a random
 * joke and one for a random phrase. This meant the joke and phrase shown came
 * from completely different database rows and their images couldn't be matched.
 *
 * Now we fetch ONE random row that has BOTH a phrase and a joke.
 * This ensures the image belongs to the same entry as the content being shown.
 *
 * We use a fallback query to handle rows that only have one field filled in.
 */

// First try: get a row that has both a phrase AND a joke
$stmt = $pdo->prepare("
    SELECT id, phrase, jokes, image_filename 
    FROM quotes 
    WHERE phrase IS NOT NULL AND phrase != '' 
      AND jokes IS NOT NULL AND jokes != '' 
    ORDER BY RAND() 
    LIMIT 1
");
$stmt->execute();
$dailyEntry = $stmt->fetch();

// Second try: if no row has both, just grab any row
if (!$dailyEntry) {
    $stmt = $pdo->prepare("SELECT id, phrase, jokes, image_filename FROM quotes ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $dailyEntry = $stmt->fetch();
}

// Fallback content if the database is completely empty
$dailyJoke   = ($dailyEntry && $dailyEntry['jokes'])  ? $dailyEntry['jokes']  : "Why don't scientists trust atoms? Because they make up everything! 🔬";
$dailyPhrase = ($dailyEntry && $dailyEntry['phrase']) ? $dailyEntry['phrase'] : "The journey of a thousand miles begins with a single step. 🌟";
$dailyImage  = ($dailyEntry && $dailyEntry['image_filename']) ? $dailyEntry['image_filename'] : null;
$dailyId     = ($dailyEntry) ? $dailyEntry['id'] : null;

// Get total count for stats
$countStmt = $pdo->query("SELECT COUNT(*) as total FROM quotes");
$totalEntries = $countStmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Inspiration ✨</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Animated Background Elements -->
    <div class="background-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <div class="main-container">
        <!-- Header Section -->
        <header class="hero-header">
            <div class="logo-container">
                <div class="logo-icon">✨</div>
                <h1 class="main-title">Daily Inspiration</h1>
            </div>
            <p class="tagline">Your daily dose of wisdom and laughter</p>
            <div class="stats-badge">
                <span class="stats-icon">📊</span>
                <span class="stats-text"><?php echo $totalEntries; ?> inspiring entries</span>
            </div>

            <?php 
            /*
             * DISPLAY THE IMAGE FROM THE SAME ROW AS THE SHOWN PHRASE & JOKE
             * 
             * FIX APPLIED: Previously there was a second separate query here
             * (inside the header) that overwrote $phraseData and fetched a
             * different random row. This caused mismatches — the image could
             * belong to a completely different entry than the text on screen.
             *
             * Now we simply use $dailyImage which was set from the same
             * $dailyEntry row we fetched at the top of the file.
             *
             * htmlspecialchars() protects against XSS — always escape output!
             */
            if ($dailyImage): ?>
                <img src="uploads/<?php echo htmlspecialchars($dailyImage); ?>" 
                     alt="Illustration for entry #<?php echo htmlspecialchars($dailyId); ?>" 
                     style="max-width: 100%; max-height: 200px; border-radius: 12px; margin: 15px 0 20px;">
            <?php endif; ?>

            <?php if ($dailyId): ?>
                <!-- Show which database row is being displayed, helpful for debugging -->
                <div style="font-size: 0.8rem; opacity: 0.6; margin-bottom: 5px;">Entry #<?php echo htmlspecialchars($dailyId); ?></div>
            <?php endif; ?>
        </header>

        <!-- Main Content Grid -->
        <main class="content-grid">
            
            <!-- Phrase Card -->
            <article class="card phrase-card">
                <div class="card-header">
                    <div class="card-icon">💭</div>
                    <h2 class="card-title">Phrase of the Day</h2>
                </div>
                <div class="card-body">
                    <blockquote class="phrase-content">
                        <span class="quote-mark quote-mark-start">"</span>
                        <p><?php echo nl2br(htmlspecialchars($dailyPhrase)); ?></p>
                        <span class="quote-mark quote-mark-end">"</span>
                    </blockquote>
                </div>
                <div class="card-footer">
                    <div class="card-decoration"></div>
                </div>
            </article>

            <!-- Joke Card -->
            <article class="card joke-card">
                <div class="card-header">
                    <div class="card-icon">😄</div>
                    <h2 class="card-title">Joke of the Day</h2>
                </div>
                <div class="card-body">
                    <div class="joke-content">
                        <p><?php echo nl2br(htmlspecialchars($dailyJoke)); ?></p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-decoration"></div>
                </div>
            </article>

        </main>

        <!-- Action Buttons -->
        <section class="action-section">
            <button class="btn-refresh" onclick="location.reload()">
                <span class="btn-icon">🔄</span>
                <span>Get New Content</span>
            </button>
            <a href="manage.php" class="btn-manage">
                <span class="btn-icon">⚙️</span>
                <span>Manage Content</span>
            </a>
        </section>

        <!-- Fun Facts Section -->
        <aside class="fun-fact-section">
            <div class="fun-fact-card">
                <span class="fun-fact-icon">💡</span>
                <p class="fun-fact-text">
                    Did you know? Laughter can boost your immune system and reduce stress hormones!
                </p>
            </div>
        </aside>

        <!-- Footer -->
        <footer class="page-footer">
            <p>Made with ❤️ to brighten your day</p>
            <p class="footer-time">
                <?php echo date('l, F j, Y'); ?> • <?php echo date('g:i A'); ?>
            </p>
        </footer>
    </div>

    <!-- JavaScript for Enhanced Interactions -->
    <script>
        // Add fade-in animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.6s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 150);
            });

            // Add hover sound effect (optional - just visual feedback)
            const buttons = document.querySelectorAll('button, .btn-manage');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });

        // Refresh button animation
        const refreshBtn = document.querySelector('.btn-refresh');
        refreshBtn.addEventListener('click', function() {
            const icon = this.querySelector('.btn-icon');
            icon.style.animation = 'spin 0.5s ease-in-out';
            setTimeout(() => {
                icon.style.animation = '';
            }, 500);
        });
    </script>
</body>
</html>