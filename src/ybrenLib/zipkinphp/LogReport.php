<?php
namespace ybrenLib\zipkinphp;

use ybrenLib\logger\Logger;
use ybrenLib\logger\utils\StringUtil;
use Zipkin\Recording\Span;
use Zipkin\Reporter;

final class LogReport implements Reporter
{
    private $logger;

    private $logFormat = "%s{<n>}%s{<n>}";
    private $bulkIndexName = "index";
    private $indexTypeName = "span";

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
     //   $spansArr = [];
        $str = "";
        $len = count($spans);
        $cursor = 1;
        foreach ($spans as $span) {
            $spanArray = $span->toArray();
            $spanContent = json_encode($spanArray);
            $indexContent = json_encode([
                $this->bulkIndexName => [
                    "_index" => "zipkin:span-".date("Y-m-d"),
                    "_type" => $this->indexTypeName,
                    "_id" => $spanArray['id'] . StringUtil::getRandomStr("zipkin"),
                ],
            ]);
            $str .= sprintf($this->logFormat , $indexContent , $spanContent) . ($cursor == $len ? "" : "\n");
            $cursor++;
        }
        $this->logger->info($str);
    }
}