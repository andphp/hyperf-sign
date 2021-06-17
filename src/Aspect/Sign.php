<?php


namespace AndPHP\HyperfSign\Aspect;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
/**
 * @Aspect
 */
class Sign extends AbstractAspect
{
    /**
     * @Inject()
     * @var ConfigInterface
     */
    private $config;

    /**
     * @Inject()
     * @var RequestInterface
     */
    private $request;

    /**
     * @Inject()
     * @var ResponseInterface
     */
    private $response;

    public $annotations = [
        \AndPHP\HyperfSign\Annotation\Sign::class,
    ];
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if(!empty($proceedingJoinPoint->getAnnotationMetadata()->method)){
            /**
             * @var \AndPHP\HyperfSign\Annotation\Sign $metaClass
             */
            $metaClass = $proceedingJoinPoint->getAnnotationMetadata()->method[\AndPHP\HyperfSign\Annotation\Sign::class];
            $sign = [
                "key"        => "c4ca4238a0b923820dcc509a6f75849b",
                "secret"     => "28c8edde3d61a0411511d3b1866f0636",
                "expires_in" => 600,
            ];
            $data = $metaClass->verifySign($this->request->all(),$this->config->get('sign',$sign));
            if($data['code'] !== 0){
                return $this->response->json($data);
            }

        }
        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $result = $proceedingJoinPoint->process();

        if(!empty($proceedingJoinPoint->getAnnotationMetadata()->class)){
            /**
             * @var \AndPHP\HyperfSign\Annotation\Sign $metaClass
             */
            $metaClass = $proceedingJoinPoint->getAnnotationMetadata()->class[\AndPHP\HyperfSign\Annotation\Sign::class];

            if(is_array($result) || is_object($result)){
                $result = json_decode(json_encode($result),true);
            }else{
                return $result;
            }
            $sign = [
                "key"        => "c4ca4238a0b923820dcc509a6f75849b",
                "secret"     => "28c8edde3d61a0411511d3b1866f0636",
                "expires_in" => 600,
            ];
            return $metaClass->getSign($result,$this->config->get('sign',$sign));
        }

        return $result;
    }
}