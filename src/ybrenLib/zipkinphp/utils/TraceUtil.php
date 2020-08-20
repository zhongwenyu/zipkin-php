<?php
namespace ybrenLib\zipkinphp\utils;

use ybrenLib\logger\driver\flume\FlumeLogConstants;
use ybrenLib\logger\utils\MDC;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\SamplingFlags;
use Zipkin\Propagation\Id;
use Zipkin\Propagation\TraceContext;
use Zipkin\Tracer;

class TraceUtil{

    /**
     * @param Tracer $tracer
     * @param SamplingFlags|null $samplingFlags
     * @return \Zipkin\Span
     */
    public static function newTrace(Tracer $tracer , SamplingFlags $samplingFlags = null){
        if ($samplingFlags === null) {
            $samplingFlags = DefaultSamplingFlags::createAsEmpty();
        }
        return $tracer->joinSpan(self::createAsRoot($samplingFlags));
    }

    private static function createAsRoot(SamplingFlags $samplingFlags = null, $usesTraceId128bits = false){
        if ($samplingFlags === null) {
            $samplingFlags = DefaultSamplingFlags::createAsEmpty();
        }

        $traceId = MDC::get(FlumeLogConstants::$TraceId);
        $nextId = Id\generateNextId();

        return TraceContext::create(
            $traceId,
            $nextId,
            null,
            $samplingFlags->isSampled(),
            $samplingFlags->isDebug(),
            $usesTraceId128bits
        );
    }
}