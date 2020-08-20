<?php
namespace ybrenLib\zipkinphp\utils;

class ContextUtil
{
    protected static $pool = [];

    static function getCoroutineUid(){
        if(PHP_SAPI == 'cli' && defined("ENV_RUN") && ENV_RUN == "swoole"){
            return \Swoole\Coroutine::getuid();
        }else{
            return 1;
        }
    }

    static function get($key , $default = null)
    {
        $cid = self::getCoroutineUid();
        if(isset(self::$pool[$cid][$key])){
            return self::$pool[$cid][$key];
        }
        return $default;
    }

    static function put($key, $item)
    {
        $cid = self::getCoroutineUid();
        self::$pool[$cid][$key] = $item;
    }

    static function delete($key = null)
    {
        $cid = self::getCoroutineUid();
        if($key){
            unset(self::$pool[$cid][$key]);
        }else{
            unset(self::$pool[$cid]);
        }
    }
}