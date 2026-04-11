# Symfony Djot Bundle Demo

A demo application showcasing all features of the [php-collective/symfony-djot](https://github.com/php-collective/symfony-djot) bundle.

## Requirements

- PHP 8.2+
- Composer

## Installation

```bash
# Clone the repository
git clone https://github.com/php-collective/symfony-djot-demo.git
cd symfony-djot-demo

# Install dependencies
composer install
```

## Running the Demo

Start the Symfony development server:

```bash
php -S localhost:8000 -t public
```

Then open http://localhost:8000 in your browser.

## Demo Pages

| Route | Description |
|-------|-------------|
| `/` | Home - Overview of all features |
| `/twig-filter` | Using the `\|djot` Twig filter |
| `/twig-function` | Using the `djot()` Twig function |
| `/service` | Injecting `DjotConverterInterface` in services |
| `/form` | Form integration with `DjotType` |
| `/safe-mode` | XSS protection for untrusted content |
| `/plain-text` | Extracting plain text with `\|djot_text` |
| `/extensions` | All available Djot extensions |

## Features Demonstrated

### Twig Integration

```twig
{# Filter for variables #}
{{ article.body|djot }}

{# Function for inline strings #}
{{ djot('*bold* and _italic_') }}

{# Plain text extraction #}
{{ content|djot_text }}

{# Using named converters #}
{{ content|djot('user_content') }}
```

### Service Injection

```php
use PhpCollective\SymfonyDjot\Service\DjotConverterInterface;

class MyController
{
    public function index(DjotConverterInterface $djot): Response
    {
        $html = $djot->toHtml('# Hello *world*!');
        $text = $djot->toText('# Hello *world*!');
    }
}
```

### Safe Mode

```yaml
# config/packages/symfony_djot.yaml
symfony_djot:
    converters:
        user_content:
            safe_mode: true
```

### Extensions

The demo showcases all 11 available extensions:

- **Autolink** - Converts bare URLs to links
- **Code Group** - Transforms code-group divs into tabbed interfaces
- **Default Attributes** - Adds default attributes to elements
- **External Links** - Adds `target="_blank"` to external links
- **Frontmatter** - Parses YAML/TOML/JSON frontmatter
- **Heading Permalinks** - Adds anchor links to headings
- **Mentions** - Converts @username to profile links
- **Semantic Spans** - Converts spans to `<kbd>`, `<dfn>`, `<abbr>`
- **Smart Quotes** - Converts straight quotes to curly quotes
- **Table of Contents** - Generates TOC from headings
- **Wikilinks** - Supports `[[Page Name]]` wiki-style links

## Configuration

See `config/packages/symfony_djot.yaml` for example configurations of all features.
