<?php

session_start();

define("OAUTH_CLIENTID", "id_635913414ff1f4.96204950");
define("OAUTH_CLIENTSECRET", "82eb52781cc74ce5564927d4e7223e07ab28f489");
//facebook identifications
define("FB_CLIENTID", "1525318654596913");
define("FB_CLIENTSECRET", "8083f67febd6c5c49e581ee34071522c");
//google identifications
define("GG_CLIENTID", "286347076324-pneu7snes85vtcjq30h0sgvtppe38rcp.apps.googleusercontent.com");
define("GG_CLIENTSECRET", "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");
//Twitch identifications
define("TW_CLIENTID", "uydgwxv0r507u0ufc7ws99ymqq4kvi");
define("TW_CLIENTSECRET", "95cgyy92muwm3iy8v1ohe7g1k6cu82");


function login()
{
    $_SESSION['state'] = uniqid();

    $queryParams = http_build_query([
        'response_type' => "code",
        'state' => $_SESSION['state'],
        'scope' => 'basic',
        'client_id' => OAUTH_CLIENTID,
        "redirect_uri" => "http://localhost:8081/success"
    ]);
    $url = "http://localhost:8080/auth?" . $queryParams;
    echo "Se connecter via OAuthServer (form)";
    echo '<form method="POST" action="do_login">
        <input type="text" name="username"><input type="text" name="password">
        <input type="submit" value="login">
        </form>';
    echo "<a href='$url'>Se connecter via OAuthServer</a>";
    echo "<br><br>";

    // facebook sdk
    $queryParams = http_build_query([
        'response_type' => "code",
        'state' => $_SESSION['state'],
        'scope' => '',
        'client_id' => FB_CLIENTID,
        "redirect_uri" => "http://localhost:8081/fb_success"
    ]);
    $url = "https://www.facebook.com/v15.0/dialog/oauth?" . $queryParams;
    echo "<a href='$url'>Se connecter via Facebook</a>";
    echo "<br><br>";

    // google sdk
    $scopes = [
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/plus.login'
    ];

    $queryParams = http_build_query([
        'response_type' => "code",
        'state' => $_SESSION['state'],
        'scope' => implode(' ', $scopes),
        'client_id' => GG_CLIENTID,
        "redirect_uri" => "http://localhost:8081/google_success"
    ]);
    $url = "https://accounts.google.com/o/oauth2/v2/auth?" . $queryParams;
    echo "<a href='$url'>Se connecter via Google</a>";
    echo "<br><br>";

    // twitch sdk
    $scopes = [
        'analytics:read:extensions',
        'analytics:read:games',
        'user:read:email'
    ];

    $queryParams = http_build_query([
        'response_type' => "code",
        'state' => $_SESSION['state'],
        'scope' => implode(' ', $scopes),
        'client_id' => TW_CLIENTID,
        "redirect_uri" => "http://localhost:8081/twitch_success"
    ]);
    $url = "https://id.twitch.tv/oauth2/authorize?" . $queryParams;
    echo "<a href='$url'>Se connecter via Twitch</a>";
    echo "<br><br>";
}

function redirectSuccess()
{
    ["code" => $code, "state" => $state] = $_GET;
    if ($state !== $_SESSION['state']) {
        return http_response_code(400);
    }

    getTokenAndUser(
        [
            'grant_type' => "authorization_code",
            "code" => $code,
            "redirect_uri" => "http://localhost:8081/success"
        ],
        [
            "client_id" => OAUTH_CLIENTID,
            "client_secret" => OAUTH_CLIENTSECRET,
            "token_url" => "http://server:8080/token",
            "user_url" => "http://server:8080/me"
        ]
    );
}

// Redirect successfully for facebook
function redirectFbSuccess()
{
    ["code" => $code, "state" => $state] = $_GET;
    if ($state !== $_SESSION['state']) {
        return http_response_code(400);
    }

    getTokenAndUser([
        'grant_type' => "authorization_code",
        "code" => $code,
        "redirect_uri" => "http://localhost:8081/fb_success"
    ], [
        "client_id" => FB_CLIENTID,
        "client_secret" => FB_CLIENTSECRET,
        "token_url" => "https://graph.facebook.com/oauth/access_token",
        "user_url" => "https://graph.facebook.com/me"
    ]);
}

// Redirect successfully for google
function redirectGgSuccess()
{
    ["code" => $code, "state" => $state] = $_GET;
    if ($state !== $_SESSION['state']) {
        return http_response_code(400);
    }


    postTokenAndUser([
        'grant_type' => "authorization_code",
        "code" => $code,
        "redirect_uri" => "http://localhost:8081/google_success"
    ], [
        "client_id" => GG_CLIENTID,
        "client_secret" => GG_CLIENTSECRET,
        "token_url" => "https://oauth2.googleapis.com/token",
        "user_url" => "https://openidconnect.googleapis.com/v1/userinfo"
    ]);
}

// Redirect successfully for twitch
function redirectTwSuccess()
{
    ["code" => $code, "state" => $state] = $_GET;
    if ($state !== $_SESSION['state']) {
        return http_response_code(400);
    }


    postTokenAndUser([
        'grant_type' => "authorization_code",
        "code" => $code,
        "redirect_uri" => "http://localhost:8081/twitch_success"
    ], [
        "client_id" => TW_CLIENTID,
        "client_secret" => TW_CLIENTSECRET,
        "token_url" => "https://id.twitch.tv/oauth2/token",
        "user_url" => "https://api.twitch.tv/helix/users"
    ]);
}

// Login func
function doLogin()
{
    getTokenAndUser(
        [
            'grant_type' => "password",
            "username" => $_POST['username'],
            "password" => $_POST['password']
        ],
        [
            "client_id" => OAUTH_CLIENTID,
            "client_secret" => OAUTH_CLIENTSECRET,
            "token_url" => "http://server:8080/token",
            "user_url" => "http://server:8080/me"
        ]
    );
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
                "Authorization: Bearer " . $token
            ]
        ]
    ]);
    $url = $settings['user_url'];
    $response = file_get_contents($url, false, $context);
    var_dump(json_decode($response, true));
}

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
    var_dump($response);
    $response = json_decode($response, true);


    $token = $response['access_token'];


    // token
    $context = stream_context_create([
        "http" => [
            "header" => [
                'Authorization: Bearer ' . $token, 
                'Client-Id: '. TW_CLIENTID
            ]
        ]
    ]);
    $url = $settings['user_url'];
    $response = file_get_contents($url, false, $context);
    var_dump($response);
}

function secondPostTokenAndUser($params, $settings)
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
    var_dump($response);
    $response = json_decode($response, true);


    $token = $response['access_token'];


    // token
    $context = stream_context_create([
        "http" => [
            "header" => [
                "Authorization: Bearer " . $token
            ]
        ]
    ]);
    $url = $settings['user_url'];
    $response = file_get_contents($url, false, $context);
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
    case '/google_success':
        redirectGgSuccess();
        break;
    case '/twitch_success':
        redirectTwSuccess();
        break;
    default:
        http_response_code(404);
        break;
}
