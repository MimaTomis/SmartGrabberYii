<?php
$loader = include __DIR__ . '/../vendor/autoload.php';

if (!$loader)
	die('Load composer and install dependencies before test running');

$loader->add('SmartGrabber\Tests', __DIR__);