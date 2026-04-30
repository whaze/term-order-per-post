# Whaze Term Order for Posts

A WordPress plugin that lets developers enable per-post custom ordering of taxonomy terms, directly from the Gutenberg editor sidebar.

## Requirements

- PHP 8.0+
- WordPress 6.0+

## Installation

```bash
composer install
npm install
npm run build
```

## Usage

Register a post type / taxonomy combination (on `init` or later):

```php
add_action( 'init', function () {
    whaze_term_order_for_posts_register( 'post', 'category' );
    whaze_term_order_for_posts_register( 'movie', 'genre' );
} );
```

Retrieve ordered terms:

```php
$terms = whaze_term_order_for_posts_get_terms( get_the_ID(), 'category' );
```

## REST API

The `term_order` field is added to the REST response for all registered post types:

```json
{
  "term_order": {
    "category": [3, 7, 12]
  }
}
```

It is readable and writable.

## Development

```bash
# PHP linting
composer run phpcs

# PHP auto-fix
composer run phpcbf

# JS build (production)
npm run build

# JS build (watch)
npm run start

# JS lint
npm run lint:js

# Generate .pot file (requires WP-CLI)
npm run make-pot
```

## Licence

GPL-2.0-or-later
