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
        'version' => '10.7.18',
    ],
    'tslib' => [
        'version' => '2.8.1',
    ],
    '@formatjs/fast-memoize' => [
        'version' => '2.2.7',
    ],
    '@formatjs/icu-messageformat-parser' => [
        'version' => '2.11.4',
    ],
    '@formatjs/icu-skeleton-parser' => [
        'version' => '1.8.16',
    ],
    'typescript' => [
        'version' => '6.0.2',
    ],
    'fontawesome' => [
        'version' => '5.6.3',
    ],
    '@fortawesome/fontawesome-free/css/all.css' => [
        'version' => '7.3.0',
        'type' => 'css',
    ],
    'datatables.net-dt/css/dataTables.dataTables.min.css' => [
        'version' => '2.3.8',
        'type' => 'css',
    ],
    'datatables.net-dt' => [
        'version' => '2.3.8',
    ],
    'datatables.net' => [
        'version' => '2.3.8',
    ],
    'tom-select' => [
        'version' => '2.6.1',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.6.1',
        'type' => 'css',
    ],
];
