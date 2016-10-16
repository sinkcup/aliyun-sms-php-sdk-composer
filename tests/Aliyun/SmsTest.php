<?php
require_once __DIR__ . '/../autoload.php';
class SmsTest extends PHPUnit_Framework_TestCase
{
    private $conf = array(
        'accessKeyId' => 'change me',
        'accessKeySecret' => 'change me',
        'signName' => 'change me',
        'templateCode' => 'change me',
    );

    public function testSingleSendSms()
    {
        $c = new \Aliyun\Sms($this->conf);
        try{
            $params = [
                'code' => strval(rand(100000, 999999)),
            ];
            $r = $c->singleSendSms('18515291984', $params);
            $this->assertEquals(true, $r);
        } catch (\Exception $e) {
            echo $e->getCode();
            echo $e->getMessage();
        }
    }
}
