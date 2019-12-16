<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2017/6/16
 * Time: 下午6:15.
 */

class TempTarsTest
{
    public $iVersion = 3;
    public $iRequestId = 1;
    public $servantName = 'test.test.test';
    public $funcName = 'example';
    public $cPacketType = 0;
    public $iMessageType = 0;
    public $iTimeout = 2;
    public $contexts = array('test1' => 'testYong', 'test2' => '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
    public $statuses = array('test' => 'testStatus', 'test2' => '22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222');

    public function testChar()
    {
        $char = 1;

        $buf = \TUPAPI::putChar('char', $char);

        $encodeBufs["char"] = $buf;

        $requestBuf = \TUPAPI::encode($this->iVersion, $this->iRequestId, $this->servantName,
            $this->funcName, $this->cPacketType, $this->iMessageType, $this->iTimeout,
            $this->contexts, $this->statuses, $encodeBufs);


        $decodeRet = \TUPAPI::decodeReqPacket($requestBuf);

        $context = $decodeRet["context"];
        foreach ($context as $key => $value) {
            var_dump('var_dump($key)');
            var_dump($key);
            var_dump('var_dump($value)');
            var_dump($value);
        }

        $status = $decodeRet["status"];
        foreach ($status as $key => $value) {
            var_dump('var_dump($key)');
            var_dump($key);
            var_dump('var_dump($value)');
            var_dump($value);
        }
    }
}

$tempTarsTest = new TempTarsTest();
$tempTarsTest->testChar();
