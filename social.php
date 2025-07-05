<?php
header('Content-Type: application/json');

require 'vendor/autoload.php';
$dotenv=Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


echo json_encode([
    'facebook' => $_ENV['FACEBOOK_URL'],
    'whatsapp' => $_ENV['WHATSAPP_URL'],
    'linkedin' => $_ENV['LINKEDIN_URL']
]);


