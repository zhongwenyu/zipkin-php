<?php
/**
 * Created by 钟文宇
 * Time: 2019/3/22 16:21
 */
namespace ybrenLib\zipkinphp\trace;

class Trace{

    protected $_id;
    protected $_traceId;
    protected $traces = [];
    protected $baseSpan = null;

    public function __construct($name , $traceId = '' , $parentId = '' , $serviceName = '' , $ipv4 = ''){
        if(empty($traceId)){
            $this->setTraceId();
        }else{
            $this->_traceId = $traceId;
        }
        empty($serviceName) && $serviceName = $_SERVER['HTTP_HOST'];

        // 创建基础span
        $this->baseSpan = new Span('server' , $name , $this->_traceId , $parentId);
        $this->baseSpan->setLocalEndPoint($serviceName , $ipv4);
        $this->_id = $this->baseSpan->getId();
    }

    /**
     * @title 结束trace
     * @return array
     */
    public function endTrace(){
        $this->addSpan($this->baseSpan);
        $traces = $this->traces;
        $this->traces = [];
        return $traces;
    }

    public function addTrace($traces = []){
        if(!empty($traces)){
            $this->traces = array_merge($this->traces , $traces);
        }
    }

    /**
     * getBaseSpan
     * @return Span
     */
    public function getBaseSpan(){
        return $this->baseSpan;
    }

    /**
     * @title 添加span到trace
     * @param Span $span
     */
    public function addSpan(Span $span){
        $this->traces[] = $span->endSpan();
    }

    public function newSpan($kind , $name){
        return new Span($kind , $name , $this->_traceId , $this->_id);
    }

    public function setTraceId(){
        if(empty($this->_traceId)){
            $this->_traceId = md5('trace' . uniqid() . rand(0,9999));
        }
    }

    public function getTraceId(){
        return $this->_traceId;
    }

    public function getId(){
        return $this->_id;
    }

    public function getTraces(){
        return $this->traces;
    }
}