<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();
    return $config->setRules([
        '@Symfony' => true,
        'full_opening_tag' => false,
        'phpdoc_separation' => false,
        'global_namespace_import' => ['import_classes' => true]
    ])
    ->setFinder($finder)
;