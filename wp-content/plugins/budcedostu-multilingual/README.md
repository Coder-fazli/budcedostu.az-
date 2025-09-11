# Budcedostu Multilingual System

A custom WordPress plugin for managing multilingual content on Budcedostu.az.

## Features

- **Three Languages**: Azerbaijani (default), Russian (/ru/), English (/en/)
- **Clean URLs**: Proper language prefixing without conflicts
- **Admin Interface**: Easy language management in post/page editor
- **Language Switcher**: Template functions for frontend language switching
- **SEO Friendly**: Proper hreflang tags and language detection

## Languages Supported

- **Azerbaijani (AZ)**: Default language, no URL prefix
- **Russian (RU)**: URLs prefixed with `/ru/`
- **English (EN)**: URLs prefixed with `/en/`

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Database tables will be created automatically
4. Use template functions in your theme

## Template Functions

```php
// Display language switcher
budcedostu_display_language_switcher();

// Get current language
$current_lang = budcedostu_get_current_language();

// Get post language
$post_lang = budcedostu_get_post_language($post_id);

// Get language switcher HTML
$switcher_html = budcedostu_get_language_switcher($post_id);
```

## Usage

1. **Setting Post Language**: Edit any post/page and use the "Language & Translations" metabox
2. **URL Structure**: 
   - Azerbaijani: `https://budcedostu.az/post-title/`
   - Russian: `https://budcedostu.az/ru/post-title/`  
   - English: `https://budcedostu.az/en/post-title/`
3. **Language Switcher**: Add to your theme templates using the provided functions

## Technical Details

- **Database**: Creates `wp_budcedostu_translations` table for language relationships
- **URL Rewriting**: Uses WordPress rewrite rules for clean URLs
- **Safe Implementation**: Avoids conflicts with WordPress core permalink system
- **Admin Integration**: Adds language columns to post/page lists

## Version History

- **1.0.0**: Initial plugin release, converted from theme integration

## Requirements

- WordPress 5.0+
- PHP 7.4+

## Support

This plugin is specifically designed for Budcedostu.az and may require customization for other sites.