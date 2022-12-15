<?php

abstract class Provider {
    function redirectSuccess()
    {
        ["code" => $code, "state" => $state] = $_GET;
        if ($state !== $_SESSION['state']) {
            return http_response_code(400);
        }

        // Get Method
        getTokenAndUser(
            [
                'grant_type' => "authorization_code",
                "code" => $code,
                "redirect_uri" => "http://localhost:8081/" . static::$redirectUri
            ],
            [
                "client_id" => static::$clientId,
                "client_secret" => static::$clientSecret,
                "token_url" => static::$token,
                "user_url" => static::$userUrl
            ]
        );

        // Post Method
        postTokenAndUser([
            'grant_type' => "authorization_code",
            "code" => $code,
            "redirect_uri" => "http://localhost:8081/" . static::$redirectUriMethodPost
        ],[
            "client_id" => static::$clientIdMethodPost,
            "client_secret" => static::$clientSecretMethodPost,
            "token_url" => static::$tokenMethodPost,
            "user_url" => static::$userUrlMethodPost
            ]
        );

        // Function post token and user for google
        function postTokenAndUser($params, $settings)
        {
            $queryParams = http_build_query(array_merge([
                'client_id' => $settings['client_id'],
                'client_secret' => $settings['client_secret'],
            ], $params));

            $url = $settings['token_url'];


            // url
            $context = stream_context_create([
                "http" => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($queryParams) . "\r\n",
                    'content' => $queryParams
                ]
            ]);

            $response = file_get_contents($url, false, $context);

            $response = json_decode($response, true);

            $token = $response['access_token'];

            // token
            $context = stream_context_create([
                "http" => [
                    "header" => [
                        "Authorization: Bearer " . $token,
                        'Client-Id: '. static::$clientIdMethodPost,

                    ]
                ]
            ]);
            $url = $settings['user_url'];
            $response = file_get_contents($url, false, $context);
            var_dump($response);
        }

        // Function get token and user
        function getTokenAndUser($params, $settings)
        {
            $queryParams = http_build_query(array_merge([
                'client_id' => $settings['client_id'],
                'client_secret' => $settings['client_secret'],
            ], $params));
            $url = $settings['token_url'] . '?' . $queryParams;
            $response = file_get_contents($url);
            $response = json_decode($response, true);
            $token = $response['access_token'];

            $context = stream_context_create([
                "http" => [
                    "header" => [
                        "Authorization: Bearer " . $token,

                    ]
                ]
            ]);
            $url = $settings['user_url'];
            $response = file_get_contents($url, false, $context);
            var_dump(json_decode($response, true));
        }

        $url = strtok($_SERVER['REQUEST_URI'], '?');
        switch ($url) {
            case '/login':
                login();
                break;
            case '/success':
                redirectSuccess();
                break;
            case '/do_login':
                doLogin();
                break;
            case '/fb_success':
                redirectFbSuccess();
                break;
            case static::$redirectUriMethodPost:
                redirectGgSuccess();
                break;
            case static::$redirectUri:
                redirectTwSuccess();
                break;
            default:
                http_response_code(404);
                break;
        }
    }
}
?>
