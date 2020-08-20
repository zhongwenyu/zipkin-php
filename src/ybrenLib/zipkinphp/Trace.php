<?php
namespace ybrenLib\zipkinphp;

use ybrenLib\logger\appender\RollFileAppender;
use ybrenLib\logger\driver\flume\JsonConverter;
use ybrenLib\logger\LoggerFactory;
use Zipkin\Endpoint;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

class Trace{

    /**
     * @var YbrPercentageSampler
     */
    private static $sampler;

    public static function createTrace($localServiceName, $localServiceIPv4, $localServicePort = null){
        /* Do not copy this logger into production.
         * Read https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels
         */
        $endpoint = Endpoint::create($localServiceName, $localServiceIPv4, null, $localServicePort);

        $reporter = new LogReport(
            LoggerFactory::getLoggerByConfig(Trace::class , [
                "appender" => RollFileAppender::class,
                "classicConverter" => JsonConverter::class,
                "fileNamePattern" => "zipkinlog-%s-%d{Y-m-d-H}.%i.log",
                "fileSize" => 500,
                "yaconfKey" => "database.zipkin.path",
            ])
        );
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
        return $tracing;
    }

    public static function getSampler(){
        return self::$sampler;
    }
}