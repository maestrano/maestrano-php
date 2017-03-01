<?php

class MaestranoTestHelper
{
    public static function getConfig()
    {
        $config = array (
            'marketplaces' =>
                array (
                    0 =>
                        array (
                            'nid' => 'demo-app-php-local',
                            'marketplace' => 'some-marketplace',
                            'environment' => 'demo-app-php',
                            'app' =>
                                array (
                                    'host' => 'http://php-demoapp.maestrano.dev',
                                    'synchronization_start_path' => '/maestrano/synchronizations',
                                    'synchronization_toggle_path' => '/maestrano/synchronizations/toggle_sync',
                                    'synchronization_status_path' => '/maestrano/synchronizations/:cld-uid',
                                ),
                            'api' =>
                                array (
                                    'id' => 'app-15pm',
                                    'key' => 'e03671e37802581b6404f6db79b81d90a32187076d9b01a51b6a4a51268e8b1e',
                                    'host' => 'https://api-hub.maestrano.com',
                                    'base' => '/api/v1/',
                                ),
                            'sso' =>
                                array (
                                    'idm' => 'http://php-demoapp.maestrano.dev',
                                    'init_path' => '/maestrano/auth/saml/init.php?marketplace=some-marketplace',
                                    'consume_path' => '/maestrano/auth/saml/consume.php?marketplace=some-marketplace',
                                    'idp' => 'https://api-hub.maestrano.com',
                                    'x509_fingerprint' => '2f:57:71:e4:40:45:57:37:a6:84:f0:c5:82:52:2f:2e:41:b7:9d:7e',
                                    'x509_certificate' => '-----BEGIN CERTIFICATE-----MIIDezCCQ....Q32v8p5lA==-----END CERTIFICATE-----',
                                ),
                            'connec' =>
                                array (
                                    'host' => 'https://api-connec.maestrano.com',
                                    'base_path' => '/api/v2',
                                    'timeout' => 300,
                                ),
                            'webhooks' =>
                                array (
                                    'account' =>
                                        array (
                                            'group_path' => 'test',
                                            'group_user_path' => '',
                                        ),
                                    'connec' =>
                                        array (
                                            'external_ids' => true,
                                            'initialization_path' => NULL,
                                            'notification_path' => '',
                                        )
                                )
                        )
                )
        );

        return $config;
    }
}
