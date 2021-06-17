<?php


namespace AndPHP\HyperfSign\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 * Class Sign
 * @package AndPHP\HyperfSign\Annotation
 */
class Sign extends AbstractAnnotation
{

    // 获取sign
    public function getSign(array $data, array $signConfig)
    {

        $data += ["key"       => $signConfig['key'],
                  'timestamp' => time()
        ];
        // 对数组的值按key排序
        ksort($data);
        // 生成url的形式
        $params = http_build_query($data);
        // 生成sign
        $data['sign'] = md5($params . $signConfig['secret']);
        return $data;
    }

    /**
     * 后台验证sign是否合法
     * @param  [type] $secret [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function verifySign(array $data, array $signConfig)
    {
        $data = [
            'code' => 401,
            'msg'  => 'Signature verification failed'
        ];
        // 验证参数中是否有签名
        if (!isset($data['sign']) || !$data['sign']) {
            $data['msg'] = '发送的数据签名不存在';
            return $data;
        }
        if (!isset($data['timestamp']) || !$data['timestamp']) {
            $data['msg'] = '发送的数据参数不合法';
            return $data;
        }
        // 验证请求， 10分钟失效
        if (time() - $data['timestamp'] > $signConfig['expires_in']) {
            $data['msg'] = '验证失效， 请重新发送请求';
            return $data;
        }
        $sign = $data['sign'];
        unset($data['sign']);
        ksort($data);
        $params = http_build_query($data);
        // $secret是通过key在api的数据库中查询得到
        $sign2 = md5($params . $signConfig['secret']);
        if ($sign == $sign2) {
            //die('验证通过');
            $data['code'] = 0;
        }
        return $data;
    }
}