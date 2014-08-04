<?php
require_once __DIR__.'/vendor/autoload.php';
require_once 'VipServer.php';
$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$conns = new \SplObjectStorage();

$vip = new VipServer($socket);

$socket->listen(9991);
$loop->run();
