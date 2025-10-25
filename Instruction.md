# Daily Inspiration - Setup Guide 🌟

A beautiful, modern web application that displays daily jokes and phrases to brighten your users' day!

## 📁 Files Included

1. **index.php** - Home page with daily joke and phrase display
3. **style.css** - styles for the home page
4. **manage-style.css** - Professional admin interface styling
5. **manage.php** - Your existing management page (already uploaded)

## 🎨 Features

### Home Page (index.php)
- **Daily Random Content**: Shows a random joke and phrase from your database on each visit
- **Beautiful Animations**: Floating background shapes and smooth transitions
- **Responsive Design**: Looks great on all devices (desktop, tablet, mobile)
- **Interactive Elements**: 
  - Refresh button to get new content
  - Link to management page
  - Fun fact section
  - Current date and time display
- **Stats Badge**: Shows total number of entries in your database
- **Modern UI**: Gradient backgrounds, cards with hover effects, and emoji icons

### Management Page (manage.php)
- Already styled with manage-style.css
- Professional admin interface
- Responsive table design
- Easy-to-use forms for adding/editing content

## 🚀 Installation Steps

### 1. Upload Files
Upload all the files to your web server in the same directory as your existing `config.php` file:

```
your-project-folder/
├── config.php (already exists)
├── index.php (NEW)
├── manage.php (already exists)
├── style.css (NEW)
└── manage-style.css (NEW)
```

### 2. Database Setup
Make sure you have a database table called `quotes` with the following structure:

```sql
CREATE TABLE quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phrase TEXT,
    jokes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Add Some Content
Visit `manage.php` to add jokes and phrases to your database, or insert some test data:

```sql
INSERT INTO quotes (phrase, jokes) VALUES 
('Believe you can and you\'re halfway there.', 'Why don\'t scientists trust atoms? Because they make up everything!'),
('The only way to do great work is to love what you do.', 'Why did the scarecrow win an award? Because he was outstanding in his field!'),
('Innovation distinguishes between a leader and a follower.', 'What do you call a fake noodle? An impasta!');
```

### 4. Access Your Site
- **Home Page**: Visit `http://yoursite.com/index.php`
- **Management**: Visit `http://yoursite.com/manage.php`

## 🎯 How It Works

### Daily Content Display
The home page uses SQL's `ORDER BY RAND()` to select random jokes and phrases:

```php
// Get a random joke
$jokeStmt = $pdo->prepare("SELECT jokes FROM quotes WHERE jokes IS NOT NULL AND jokes != '' ORDER BY RAND() LIMIT 1");

// Get a random phrase
$phraseStmt = $pdo->prepare("SELECT phrase FROM quotes WHERE phrase IS NOT NULL AND phrase != '' ORDER BY RAND() LIMIT 1");
```

Each time a user visits or refreshes the page, they see different content!

## 🎨 Customization

### Colors
The default color scheme uses purple gradients. To change colors, edit these CSS variables in `style.css`:

**Main Gradient** (line 15):
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

**Card Accents**:
- Phrase card (line 163): `#f093fb` to `#f5576c`
- Joke card (line 173): `#4facfe` to `#00f2fe`

### Layout
- Card sizes: Edit `.content-grid` (line 137)
- Spacing: Adjust padding/margin values
- Fonts: Change `font-family` in body styles

### Animations
Disable animations for accessibility by adding this to your CSS:
```css
@media (prefers-reduced-motion: reduce) {
    * {
        animation: none !important;
    }
}
```

## 📱 Mobile Responsive

All pages are fully responsive with breakpoints at:
- **768px** - Tablet view
- **480px** - Mobile view

The management page table converts to a card-based layout on mobile for better usability.

## ✨ Advanced Features

### Refresh Content
Users can click the "Get New Content" button to reload the page and see new jokes/phrases without navigating away.

### Accessibility
- Semantic HTML structure
- Proper heading hierarchy
- Focus states for keyboard navigation
- Print-friendly styles
- Reduced motion support

### Performance
- Minimal database queries
- CSS animations using GPU acceleration
- Optimized for fast loading

## 🐛 Troubleshooting

### "No entries" message appears
- Add content via manage.php
- Check database connection in config.php
- Verify table name is `quotes`

### Styles not loading
- Ensure all CSS files are in the same directory as index.php
- Check file permissions (should be readable)
- Clear browser cache

### Database errors
- Verify PDO connection in config.php
- Check database credentials
- Ensure quotes table exists

## 🎉 Tips for Best Results

1. **Add Variety**: Include 10+ jokes and phrases for better variety
2. **Update Regularly**: Add new content weekly to keep it fresh
3. **Quality Content**: Choose inspiring phrases and clean humor
4. **Test Mobile**: Always check how it looks on phones
5. **Share It**: Make it your browser homepage for daily inspiration!

## 📄 Browser Support

Works on all modern browsers:
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers

## 🔒 Security

The code includes security best practices:
- Prepared statements for SQL queries
- `htmlspecialchars()` for XSS prevention
- Input validation
- Secure form handling

- Username: wisdom
- password: Admin@2025

## 📧 Need Help?

If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Verify PHP error logs
3. Ensure file permissions are correct
4. Test database connection separately

---

Enjoy your daily inspiration app! 🌟😄💭