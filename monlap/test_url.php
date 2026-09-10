<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$provider = Laravel\Socialite\Facades\Socialite::driver('sso');
$reflection = new ReflectionClass($provider);
$method = $reflection->getMethod('getAuthUrl');
$method->setAccessible(true);
echo $method->invoke($provider, 'dummy_state');
