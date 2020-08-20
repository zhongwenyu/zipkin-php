<?php
namespace ybrenLib\zipkinphp;

use ybrenLib\zipkinphp\bean\DbZipkinBean;
use ybrenLib\zipkinphp\bean\ServiceZipkinBean;
use ybrenLib\zipkinphp\bean\ZipkinConstants;
use ybrenLib\zipkinphp\utils\ContextUtil;
use Zipkin\Span;

class SpanFactory{

    /**
     * @param $sql
     * @param $database
     * @param $type
     * @param $username
     * @return Span|null
     */
    public static function createDbSpan(DbZipkinBean $dbZipkinBean){
        $sql = $dbZipkinBean->getSql();
        $database = $dbZipkinBean->getDatabase();
        $username = $dbZipkinBean->getUsername();
        $type = $dbZipkinBean->getType();
        if(empty($sql)){
            return null;
        }

        $trace = ContextUtil::get(ZipkinConstants::$Trace_name);
        if(is_null($trace)){
            return null;
        }
        $span = ZipkinClient::getSpan();
        $childSpan = $trace->newChild($span->getContext());
        $childSpan->tag("db.statement",$sql);
        $childSpan->tag("db.instance",$database);
        $childSpan->tag("db.type",$type);
        $childSpan->tag("db.user",$username);
        $childSpan->setKind(\Zipkin\Kind\CLIENT);
        $childSpan->setName($database);
        $childSpan->annotate('request_started', \Zipkin\Timestamp\now());
        $childSpan->start();

        return $childSpan;
    }

    /**
     * @param $url
     * @param $method
     * @param $request
     * @return Span|null
     */
    public static function createServiceSpan(ServiceZipkinBean $serviceZipkinBean){
        $url = $serviceZipkinBean->getUrl();
        $method = $serviceZipkinBean->getMethod();
        $request = $serviceZipkinBean->getRequest();

        $trace = ContextUtil::get(ZipkinConstants::$Trace_name);
        if(is_null($trace)){
            return null;
        }
        $span = ZipkinClient::getSpan();
        $childSpan = $trace->newChild($span->getContext());
        $childSpan->setName($url);
        $childSpan->setKind(\Zipkin\Kind\CLIENT);
        $childSpan->tag("http.url" , $url);
        $childSpan->tag("http.method" , $method);
        $childSpan->tag("http.request" , $request);
        $childSpan->annotate('request_started', \Zipkin\Timestamp\now());
        $childSpan->start();

        return $childSpan;
    }
}