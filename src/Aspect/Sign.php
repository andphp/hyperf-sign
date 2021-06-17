<?php


namespace AndPHP\HyperfSign\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class Sign extends AbstractAspect
{
    public $annotations = [
        \AndPHP\HyperfSign\Annotation\Sign::class,
    ];
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $result = $proceedingJoinPoint->process();

        /**
         * @var \AndPHP\HyperfSign\Annotation\Sign $metaClass
         */
        $metaClass = $proceedingJoinPoint->getAnnotationMetadata()->class[\AndPHP\HyperfSign\Annotation\Sign::class];

        if(is_array($result) || is_object($result)){
            $result = json_decode(json_encode($result),true);
        }else{
            return $result;
        }
        return $metaClass->getSign($result);
    }
}