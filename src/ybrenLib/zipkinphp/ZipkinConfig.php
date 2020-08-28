<?php
namespace ybrenLib\zipkinphp;

class ZipkinConfig{

    /**
     * sampleRate 采样率(>= 1 , 10表示10采1,100表示100采1)
     * newTraceDuration 新链路请求持续时间 毫秒
     * childTraceDuration 子链路请求持续时间 毫秒
     * @var null
     */
    private static $config = null;

    public static function getConfig(){
        if(is_null(self::$config)){
            self::initConfig();
        }
        return self::$config;
    }

    private static function initConfig(){
        if(defined("ROOT_PATH")){
            $configFileName = ROOT_PATH . "zipkin.json";
            if(file_exists($configFileName)){
                self::$config = json_decode(file_get_contents($configFileName) , true);
                return;
            }
        }
        self::$config = [];
    }
}