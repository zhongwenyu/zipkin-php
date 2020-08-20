<?php
namespace ybrenLib\zipkinphp\handler;

use ybrenLib\zipkinphp\bean\DbZipkinBean;
use ybrenLib\zipkinphp\bean\ServiceZipkinBean;
use ybrenLib\zipkinphp\bean\ZipkinConstants;
use ybrenLib\zipkinphp\SpanFactory;
use ybrenLib\zipkinphp\utils\ContextUtil;
use ybrenLib\zipkinphp\ZipkinClient;
use Zipkin\Propagation\Map;
use Zipkin\Timestamp;

class ZipkinHandler{

    public static function dbStart(DbZipkinBean $dbZipkinBean){
        if(!ZipkinClient::getInitStatus()){
            return;
        }

        $childSpan = SpanFactory::createDbSpan($dbZipkinBean);
        if(!is_null($childSpan)){
            self::addChildSpan(md5($dbZipkinBean->getSql()) , $childSpan);
        }
    }

    public static function dbEnd(DbZipkinBean $dbZipkinBean){
        if(!ZipkinClient::getInitStatus()){
            return;
        }

        $childSpanArr = self::getChildSpan($dbZipkinBean->getSql());
        if(!is_null($childSpanArr)){
            self::delChildSpan($dbZipkinBean->getSql());
            $childSpan = $childSpanArr['childSpan'];
            $nowTimestamp = Timestamp\now();

            // 采样
            !is_null($dbZipkinBean->getException()) && $childSpan->tag("error" , $dbZipkinBean->getException()->getMessage());
            $childSpan->annotate("request_finish" , $nowTimestamp);
            $childSpan->finish();
        }
    }

    /**
     * @param ServiceZipkinBean $serviceZipkinBean
     * @param $headers
     * @return \Zipkin\Span|null
     */
    public static function serviceStart(ServiceZipkinBean $serviceZipkinBean , &$headers){
        if(!ZipkinClient::getInitStatus()){
            return null;
        }

        $childSpan = SpanFactory::createServiceSpan($serviceZipkinBean);
        if(!is_null($childSpan)){
            $tracing = ContextUtil::get(ZipkinConstants::$Tracing_name);
            $injector = $tracing->getPropagation()->getInjector(new Map());
            $injector($childSpan->getContext(), $headers);
        }
        return $childSpan;
    }

    public static function serviceEnd($childSpan , ServiceZipkinBean $serviceZipkinBean){
        if(!ZipkinClient::getInitStatus()){
            return;
        }

        if(is_null($childSpan)){
            return;
        }
        $serviceZipkinBean->setFinishTimestamp(Timestamp\now());

        // 记录响应
        !is_null($serviceZipkinBean->getResponse()) && $childSpan->tag("response" , $serviceZipkinBean->getResponse());

        // 采样
        !is_null($serviceZipkinBean->getException()) && $childSpan->tag("error" , $serviceZipkinBean->getException()->getMessage());
        $childSpan->annotate("request_finish" , Timestamp\now());
        $childSpan->finish();
    }

    /**
     * @var array
     */
    private static $childSpans = [];

    private static function getChildSpan($sql){
        $key = md5($sql);
        $childSpans = ContextUtil::get(ZipkinConstants::$Child_span_name , []);
        return $childSpans[$key] ?? null;
    }

    private static function delChildSpan($sql){
        $key = md5($sql);
        $childSpans = ContextUtil::get(ZipkinConstants::$Child_span_name , []);
        if(isset($childSpans[$key])){
            unset($childSpans[$key]);
        }
    }

    private static function addChildSpan($key , \Zipkin\Span $childSpan){
        $childSpans = ContextUtil::get(ZipkinConstants::$Child_span_name , []);
        $childSpans[$key] = [
            "timestamp" => Timestamp\now(),
            "childSpan" => $childSpan
        ];
        ContextUtil::put(ZipkinConstants::$Child_span_name , $childSpans);
    }
}