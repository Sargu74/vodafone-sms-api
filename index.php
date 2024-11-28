<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
  require __DIR__ . "/src/$class.php";
});

$message = new VodafoneAdapter([
  'accountId' => '914830057',
  'password' => 'Vod@Fone2021',
  'secretKey' => '',
  'senderName' => 'EGAP',
]);

$data = $message->send([
  'to' => '965079108',
  'text' => 'Enviado da EGAP,
    A sua password é:
    Olha, olha...
    Funciona! E enviada com a Vodafone através de PHP...
    Fonix que o gajo já sabe umas coisinhas... :P'
]);

echo json_encode($data);
