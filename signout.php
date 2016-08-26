<?php
session_start();
require_once __DIR__ . "/vendor/autoload.php";
use app\Server\Server as Server;
use app\Config\Constant as Constant;
$server=new Server();
$server->logout();
header('Location: '.Constant::BASEURL);