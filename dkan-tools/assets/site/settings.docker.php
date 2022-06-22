<?php

if (getenv("DKTL_DOCKER") == "1") {
    $databases = [
        'default' => [
            'default' => [
                'database' => 'drupal',
                'username' => 'drupal',
                'password' => '123',
                'host' => 'db',
                'port' => '',
                'driver' => 'mysql',
                'prefix' => '',
            ],
        ],
    ];

    $proxy_domain = getenv('DKTL_PROXY_DOMAIN');
    $settings['file_public_base_url'] = "http://$proxy_domain/sites/default/files";

    /**
     * Enable local development services.
     */
    $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/development.services.yml';
}
