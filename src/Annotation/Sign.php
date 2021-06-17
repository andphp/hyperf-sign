<?php


namespace AndPHP\HyperfSign\Annotation;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 * Class Sign
 * @package AndPHP\HyperfSign\Annotation
 */
class Sign extends AbstractAnnotation
{
    /**
     * @Inject()
     * @var ConfigInterface
     */
    private $config;


    // 获取sign
    public function getSign(array $data) {
        $secret = $this->config->get('sign.secret','secret');
        $key = $this->config->get('sign.key','key');
        var_dump($this->config);
        array_push($data,["key"=>$key,'timestamp'=>time()]);
        // 对数组的值按key排序
        ksort($data);
        // 生成url的形式
        $params = http_build_query($data);
        // 生成sign
        $data['sign'] = md5($params . $secret);
        return $data;
    }

    /**
     * 后台验证sign是否合法
     * @param  [type] $secret [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function verifySign(array $data) {
        $secret = $this->config->get('sign.secret');
        $expires_in = $this->config->get('sign.expires_in');
        // 验证参数中是否有签名
        if (!isset($data['sign']) || !$data['sign']) {
            echo '发送的数据签名不存在';
            die();
        }
        if (!isset($data['timestamp']) || !$data['timestamp']) {
            echo '发送的数据参数不合法';
            die();
        }
        // 验证请求， 10分钟失效
        if (time() - $data['timestamp'] > $expires_in) {
            echo '验证失效， 请重新发送请求';
            die();
        }
        $sign = $data['sign'];
        unset($data['sign']);
        ksort($data);
        $params = http_build_query($data);
        // $secret是通过key在api的数据库中查询得到
        $sign2 = md5($params . $secret);
        if ($sign == $sign2) {
            //die('验证通过');
            return true;
        } else {
            //die('请求不合法');
            return false;
        }
    }
}