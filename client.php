<?php
require_once __DIR__.'/vendor/autoload.php';
$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$connector = new React\SocketClient\Connector($loop, $dns);

$connector->create('127.0.0.1', 9991)->then(function (React\Stream\Stream $stream) {
      $msg = 'sssss1122sssse我时中国人eeeeeexxxxxxxxxx';
      $len = strlen($msg);
      var_dump(bin2hex(pack("N", $len)));
      $payload = pack("V", $len).$msg;
      $stream->write($payload.$payload);
      $stream->on('data', function($data) use ($stream) {
          echo $data;
                    // handle data from server
            });
}, function (\Exception $e) {var_dump($e->getMessage());});

$loop->run();
