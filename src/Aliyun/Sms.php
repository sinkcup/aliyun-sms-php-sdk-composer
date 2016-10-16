<?php
/**
 * 阿里云短信服务 aliyun sms PHP SDK（composer版）
 *
 * 书写规范：PSR2
 *
 * phpcs --standard=PSR2 Sms.php
 *
 * @author   sink <sinkcup@live.it>
 * @link     https://github.com/sinkcup/aliyun-sms-php-sdk-composer
 * @link     https://www.aliyun.com/product/sms
 */

namespace Aliyun;

class Sms
{
    private $bucket = null;

    private $conf = array(
        'host' => 'sms.aliyuncs.com',
        'format' => 'json',
        'version' => '2016-09-27',
        'signatureVersion' => '1.0',
        'signatureMethod' => 'HMAC-SHA1',
        'accessKeyId' => null,
        'accessKeySecret' => null,
        'signName' => null,
        'templateCode' => null,
    );

    public function __construct($conf)
    {
        $this->setConf($conf);
    }

    public function setConf($conf)
    {
        $this->conf = array_merge($this->conf, $conf);
        return true;
    }

    /**
     * 单一发短信接口
     *
     * @example shell curl -d 'Action=SingleSendSms
     &SignName=阿里云短信服务
     &TemplateCode=SMS_1595010
     &RecNum=13011112222
     &ParamString={"no":"123456"}
     &<公共请求参数>' 'https://sms.aliyuncs.com/'
     * @return boolean
     */
    public function singleSendSms($mobile, $params, $template = null, $sign = null)
    {
        $data = [
            'Action' => 'SingleSendSms',
            'SignName' => empty($sign) ? $this->conf['signName'] : $sign,
            'TemplateCode' => empty($template) ? $this->conf['templateCode'] : $template,
            'RecNum' => is_array($mobile) ? implode(',', $mobile) : $mobile,
            'ParamString' => json_encode($params),
        ];
        if (empty($data['SignName']) || empty($data['TemplateCode'])) {
            return false;
        }
        $tmp = $this->mergePublicParams($data);
        $tmp['Signature'] = $this->sign('POST', $tmp);

        $ch = curl_init();
        $options = array(
            CURLOPT_URL => 'https://' . $this->conf['host'] . '/',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($tmp),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return true;
        }
        throw new Exception($r, $code);
    }
    
    /**
     * 合并“公共请求参数”
     *
     * @link https://help.aliyun.com/document_detail/44362.html
     */
    private function mergePublicParams($data)
    {
        $default = [
            'Format' => $this->conf['format'],
            'Version' => $this->conf['version'],
            'SignatureVersion' => $this->conf['signatureVersion'],
            'SignatureMethod' => $this->conf['signatureMethod'],
            'AccessKeyId' => $this->conf['accessKeyId'],
            'Timestamp' => gmDate("Y-m-d\TH:i:s\Z"),
            'SignatureNonce' => uniqid('', true),
        ];
        return array_merge($default, $data);
    }

    /**
     * 签名
     *
     * @link https://help.aliyun.com/document_detail/44363.html
     */
    private function sign($httpMethod, $data)
    {
        ksort($data);
        $q = http_build_query($data);
        return base64_encode(hash_hmac('sha1', $httpMethod . '&%2F&' . rawurlencode($q), $this->conf['accessKeySecret'] . '&', true));
    }
}
