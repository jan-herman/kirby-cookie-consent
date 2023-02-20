<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('jan-herman/cookie-consent', [
    'snippets' => [
        'cookie-consent/css' => __DIR__ . '/snippets/css.php',
        'cookie-consent/js' => __DIR__ . '/snippets/js.php',
    ],
    'routes' => [
        [
            'method' => 'POST',
            'pattern' => 'ajax/log-cookie-consent',
            'action' => function () {
                $kirby = kirby();

                $request_data = $kirby->request()->data();
                $cookie = $request_data['cookie'];

                $ip_address = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                $anonymized_ip_address = preg_replace(['/\.\d*$/', '/[\da-f]*:[\da-f]*$/'], ['.XXX', 'XXXX:XXXX'], $ip_address);

                // create log data
                $log_data[] = '[' . date('Y-m-d H-i-s') . ']';
                $log_data[] = 'ID: ' . $cookie['consent_uuid'];
                $log_data[] = 'Level: [' . implode(', ', $cookie['level']) . ']';
                $log_data[] = 'Revision: ' . $cookie['revision'];
                $log_data[] = 'IP: ' . $anonymized_ip_address;
                $log_data[] = 'URL: ' . $request_data['url'];
                $log_data[] = 'User Agent: ' . $request_data['user_agent'];

                // save it to log file
                $log_path = $kirby->root('logs') . '/cookie-consent';
                $log_filename = date('Y_m') . '.log';

                if (!file_exists($log_path)) {
                    mkdir($log_path, 0777, true);
                }

                file_put_contents($log_path . DIRECTORY_SEPARATOR . $log_filename, implode('  ', $log_data) . PHP_EOL, FILE_APPEND);

                // create response
                $response['type'] = 'success';
                echo json_encode($response);

                die();
            }
        ]
    ]
]);
