<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/ts/app.ts',
        'entrypoint' => true,
    ],
    'group-edit' => [
        'path' => './assets/ts/group-edit.ts',
        'entrypoint' => true,
    ],
    'group-list' => [
        'path' => './assets/ts/group-list.ts',
        'entrypoint' => true,
    ],
    'user-list' => [
        'path' => './assets/ts/user-list.ts',
        'entrypoint' => true,
    ],
    'user-edit' => [
        'path' => './assets/ts/user-edit.ts',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'fos-router' => [
        'version' => '2.4.6',
    ],
    'jquery' => [
        'version' => '4.0.0',
    ],
    'bazinga-translator' => [
        'version' => '8.0.0',
    ],
    'intl-messageformat' => [
        'version' => '11.2.11',
    ],
    'tslib' => [
        'version' => '2.8.1',
    ],
    '@formatjs/fast-memoize' => [
        'version' => '3.1.7',
    ],
    '@formatjs/icu-messageformat-parser' => [
        'version' => '3.5.14',
    ],
    '@formatjs/icu-skeleton-parser' => [
        'version' => '2.1.11',
    ],
    'typescript' => [
        'version' => '7.0.2',
    ],
    'fontawesome' => [
        'version' => '5.6.3',
    ],
    '@fortawesome/fontawesome-free/css/all.css' => [
        'version' => '7.3.0',
        'type' => 'css',
    ],
    'tom-select' => [
        'version' => '2.6.2',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.6.2',
        'type' => 'css',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'jszip' => [
        'version' => '3.10.1',
    ],
    'pdfmake' => [
        'version' => '0.3.11',
    ],
    'pdfmake/build/vfs_fonts' => [
        'version' => '0.3.11',
    ],
];
