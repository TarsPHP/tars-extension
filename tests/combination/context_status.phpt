--TEST--
context: status

--SKIPIF--
<?php require __DIR__ . "/../include/skipif.inc"; ?>
--INI--
zend.assertions=-1
assert.active=1
assert.warning=1
assert.bail=0
assert.quiet_eval=0

--FILE--
<?php
require_once __DIR__ . "/../include/config.inc";

$iVersion = 3;
$iRequestId = 1;
$servantName = 'test.test.test';
$funcName = 'example';
$cPacketType = 0;
$iMessageType = 0;
$iTimeout = 2;
$contexts = array('test1' => 'testYong', 'test2' => '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
$statuses = array('test1' => 'testStatus', 'test2' => '22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222');

$char = 1;
$buf = \TUPAPI::putChar('char', $char);

$encodeBufs["char"] = $buf;

$requestBuf = \TUPAPI::encode($iVersion, $iRequestId, $servantName,
    $funcName, $cPacketType, $iMessageType, $iTimeout,
    $contexts, $statuses, $encodeBufs);


$decodeRet = \TUPAPI::decodeReqPacket($requestBuf);

$contexts_decode = $decodeRet["context"];

if($contexts_decode === $contexts){
    echo "success";
}

$statuses_decode = $decodeRet["status"];
if($statuses_decode === $statuses){
    echo "success";
}

?>
--EXPECT--
successsuccess