<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = new Finder()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/core/src',
        __DIR__ . '/tests',
        __DIR__ . '/views',
    ])
    ->append([
        __DIR__ . '/public/index.php',
        __FILE__,
    ]);

return new Config()
    ->setRiskyAllowed(false)
    ->setRules([
        '@PER-CS' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => null,
            'import_functions' => null,
        ],
        'new_expression_parentheses' => ['use_parentheses' => false],
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'single_line_empty_body' => true,
    ])
    ->setCacheFile(__DIR__ . '/runtime/cache/php-cs-fixer.cache')
    ->setFinder($finder);
