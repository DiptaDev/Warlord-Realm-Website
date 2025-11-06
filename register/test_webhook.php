<?php
include 'config.php';

$testData = [
    'username' => 'TestUser',
    'email' => 'test@example.com',
    'minecraftType' => 'original',
    'discord' => 'TestUser#1234',
    'skills' => 'Building dan PvP',
    'experience' => 'Pernah main di server lain',
    'socialMedia' => '@testuser'
];

if (sendDiscordNotification($testData)) {
    echo "Webhook test berhasil!";
} else {
    echo "Webhook test gagal. Cek error log.";
}
?>