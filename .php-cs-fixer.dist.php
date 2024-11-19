<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(dirs: [
        'assets',
        'bin',
        'docs',
        'node_modules',
        'templates',
        'var',
        'vendor',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules(rules: [
        '@Symfony' => true,
        '@PSR12' => true,
        'phpdoc_align' => [
            'tags' => ['param', 'property', 'property-read', 'property-write', 'return', 'throws', 'type', 'var', 'method'],
            'align' => 'left',
        ],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
            'after_heredoc' => true,
        ],
    ])
    ->setFinder(finder: $finder)
    ;
