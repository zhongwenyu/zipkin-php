<?php
namespace ybrenLib\zipkinphp;

use ybrenLib\logger\driver\flume\FlumeLogConstants;
use ybrenLib\logger\LoggerConfig;
use ybrenLib\logger\LoggerFactory;
use ybrenLib\logger\utils\ConfigUtil;
use ybrenLib\logger\utils\MDC;
use ybrenLib\zipkinphp\bean\ZipkinConstants;
use ybrenLib\zipkinphp\utils\ContextUtil;
use ybrenLib\zipkinphp\utils\TraceUtil;
use Zipkin\Propagation\B3;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\Map;
use Zipkin\Span;
use Zipkin\Timestamp;

class ZipkinClient{

    /**
     * @param array $headers
     */
    public static function start($headers = []){
        // 清空原有数据
        ContextUtil::delete();

        try{
            $ip = MDC::get(FlumeLogConstants::$ClientIp);

            $startTimestamp = Timestamp\now();

            // 初始化zipkin
            $appName = ConfigUtil::getAppName(LoggerConfig::getConfig());
            $tracing = Trace::createTrace($appName, $ip);
            if(empty($headers)){
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $headers = array_map(function ($header) {
                    return $header[0];
                }, $request->headers->all());
            }

            if(isset($headers[strtolower(B3::SPAN_ID_NAME)]) && isset($headers[strtolower(B3::TRACE_ID_NAME)])){
                $extractor = $tracing->getPropagation()->getExtractor(new Map());
                $extractedContext = $extractor($headers);
                $trace = $tracing->getTracer();
                $span = $trace->nextSpan($extractedContext);
            }else{
                ContextUtil::put(ZipkinConstants::$New_trace , true);

                $trace = $tracing->getTracer();
                /* Always sample traces */
                $defaultSamplingFlags = DefaultSamplingFlags::createAsSampled();
            //    $span = $trace->newTrace($defaultSamplingFlags);
                $span = TraceUtil::newTrace($trace , $defaultSamplingFlags);
            }

            /* Creates the main span */

            $span->start($startTimestamp);
            $span->setName(MDC::get(FlumeLogConstants::$RequestURI));
            $span->setKind(\Zipkin\Kind\SERVER);
            $span->annotate('request_started', $startTimestamp);
            $span->tag("requestId" , MDC::get(FlumeLogConstants::$RequestId));

            ContextUtil::put(ZipkinConstants::$Trace_name , $trace);
            ContextUtil::put(ZipkinConstants::$Span_name , $span);
            ContextUtil::put(ZipkinConstants::$Tracing_name , $tracing);
            ContextUtil::put(ZipkinConstants::$Init , true);
            ContextUtil::put(ZipkinConstants::$Request_start , $startTimestamp);
        }catch (\Exception $e){
            LoggerFactory::getLogger(ZipkinClient::class)->errorWithException($e->getMessage() , $e);
        }
    }

    /**
     * @return bool
     */
    public static function getInitStatus(){
        $init = ContextUtil::get(ZipkinConstants::$Init);
        return empty($init) ? false : $init;
    }

    /**
     * @return Span
     */
    public static function getSpan(){
        return ContextUtil::get("zipkinSpan");
    }

    public static function flush(\Throwable $e = null){
        $endTime = Timestamp\now();
        if(self::shouldSaveTraces($endTime)){
            $trace = ContextUtil::get(ZipkinConstants::$Trace_name);
            $span = ContextUtil::get(ZipkinConstants::$Span_name);
            if(!is_null($span) && !is_null($trace)){
                $span->annotate('request_finish', $endTime);
                $span->finish();
                $trace->flush();
            }
        }
        ContextUtil::delete();
    }

    /**
     * 判断是否应保存trace信息
     * @return bool
     */
    private static function shouldSaveTraces($endTime){
        $isNewTrace = ContextUtil::get(ZipkinConstants::$New_trace);
        $hasChildspan = ContextUtil::get(ZipkinConstants::$Has_childspan);
        $startTime = ContextUtil::get(ZipkinConstants::$Request_start);

        if($isNewTrace === true && (is_null($hasChildspan) || (!empty($startTime) && ($endTime - $startTime) < 500*100 && !self::getSimpled()))){
            // 判断条件，链路起始，没有子span或者耗时小于500ms并且不满足采样率
            return false;
        }
        return true;
    }

    /**
     * 获取采样率 100采1
     */
    private static function getSimpled(){
        return rand(0 , 100) == 1;
    }
}