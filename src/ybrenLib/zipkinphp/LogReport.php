<?php
namespace ybrenLib\zipkinphp;

use ybrenLib\logger\Logger;
use Zipkin\Recording\Span;
use Zipkin\Reporter;

final class LogReport implements Reporter
{
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Span[] $spans
     */
    public function report(array $spans)
    {
        if(empty($spans)){
            return;
        }
        $spansArr = [];
        foreach ($spans as $span) {
            $spansArr[] = $span->toArray();
        }
        $this->logger->info(json_encode($spansArr));
    }
}