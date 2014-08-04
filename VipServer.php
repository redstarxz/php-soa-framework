<?php
use Evenement\EventEmitter;

class VipServer
{
    private $io;

    public function __construct($socketIo)
    {
        $this->io = $socketIo;
        // redefine on
        $this->io->on('connection', function($conn) {
                $parser = new VipProtoParser();
                $parser->on('frame', function ($frame) use ($conn) {
                    $conn->write($frame['payload']);
                });
                $conn->on('data', array($parser, 'feed'));
            
            });
    }

    public function handleMessage()
    {

    }

}

class VipProtoParser extends EventEmitter
{
    private $buffer = '';

    public function feed($data)
    {
        $data = $this->buffer.$data;
        $this->bytesRecvd = strlen($data);
        $frames = [];
        while ($this->hasFullFrame($data)) {
            list($frameData, $data) = $this->extractFrameData($data);
            $frame = $this->parseFrameData($frameData);
            $frames[] = $frame;
        }
        $this->buffer = $data;
        foreach ($frames as $frame) {
            $this->emit('frame', array($frame));
        }
    }

    private function hasFullFrame($data)
    {
        if (!$data) {
            return false;
        }
        try {
            $payload_length = $this->getPayloadLength($data);
            $payload_start  = $this->getPayloadStartingByte();
        } catch (Exception $e) {
            return false;
        }
        return $this->bytesRecvd >= $payload_length + $payload_start;
    }

    private function getPayloadLength($data)
    {
        // eg: int four bytes
        if (strlen($data) > 4) {
            // length of data bytes
            $this->defLen = current(unpack('V', substr($data, 0, 4)));
            var_dump($this->defLen);
            return $this->defLen;
        }
        throw Exception('not enough data for header len');
    }

    private function getPayloadStartingByte()
    {
        $this->startByte = 4;
        return $this->startByte;
    }

    private function extractFrameData($data)
    {
        $len = $this->defLen + $this->startByte;
        $frameData = substr($data, 0, $len);
        $data = substr($data, $len);
        return array($frameData, $data);
    }

    private function parseFrameData($frameData)
    {
        $payload = substr($frameData, 4);
        return array('size' =>$this->defLen, 'payload' => $payload);
    }

}
