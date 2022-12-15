<?php
class GoogleProvider extends Provider{
    protected static $clientIdMethodPost = "286347076324-pneu7snes85vtcjq30h0sgvtppe38rcp.apps.googleusercontent.com";
    protected static $clientSecretMethodPost = "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";
    protected static $redirectUriMethodPost = "google_success";
    protected static $tokenMethodPost = "https://oauth2.googleapis.com/token";
    protected static $userUrlMethodPost = "https://openidconnect.googleapis.com/v1/userinfo";
}