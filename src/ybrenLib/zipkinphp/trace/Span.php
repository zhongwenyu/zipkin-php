<?php
/**
 * Created by 钟文宇
 * Time: 2019/3/22 16:24
 */
namespace ybrenLib\zipkinphp\trace;

class Span{

    protected $_id;
    protected $_parentId;
    protected $_begin;
    protected $span = [];
    protected $kind;  // server 服务端，client 客户端，query 数据库

    public function __construct($kind , $name , $traceId , $parendId = ''){
        $this->setParentId($parendId);
        $this->kind = $kind;
        $this->_begin = $this->getTimestamp();
        $this->span = [
            'id' => $this->getId(),
            'name' => $name,
            'timestamp' => $this->_begin,
            'traceId' => $traceId,
        ];
        !empty($this->_parentId) && $this->span['parentId'] = $parendId;
        $this->span['annotations'][] = [
            'timestamp' => $this->_begin,
            'value' => $this->kind == 'server' ? 'ss' : 'cs',
        ];
    }

    public function endSpan(){
        $_end = $this->getTimestamp();
        $this->span['annotations'][] = [
            'timestamp' => $_end,
            'value' => $this->kind == 'server' ? 'sr' : 'cr',
        ];
        $this->span['duration'] = $_end - $this->_begin;
        return $this->span;
    }

    /**
     * setLocalEndPoint
     * @param $endPointName
     * @param $ipv4
     */
    public function setLocalEndPoint($serviceName , $ipv4){
        $this->span['localEndpoint'] = [
            'serviceName' => $serviceName,
            'ipv4' => $ipv4
        ];
    }

    /**
     * setRemoteEndpoint
     * @param $serviceName
     * @param $ipv4
     */
    public function setRemoteEndpoint($serviceName , $ipv4 , $port){
        $this->span['remoteEndpoint'] = [
            'serviceName' => $serviceName,
            'ipv4' => $ipv4,
            'port' => $port
        ];
    }

    /**
     * addTags
     * @param $tag
     */
    public function addTags($key , $val){
        $this->span['tags'][$key] = $val;
    }

    public function getId(){
        if(empty($this->_id)){
            $this->_id = str_pad(dechex(mt_rand()), 16, '0', STR_PAD_LEFT);
        }
        return $this->_id;
    }

    public function getSpan(){
        return $this->span;
    }

    public function setParentId($parendId){
        !empty($parendId) && $this->_parentId = $parendId;
    }

    protected function getTimestamp(){
        return intval(microtime(true)*1000000);
    }
}