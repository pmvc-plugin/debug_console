<?php
namespace PMVC\PlugIn\debug;
use PMVC as p;
${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_console';
p\initPlugin(['debug'=>null]);

class debug_console
    extends p\PlugIn
    implements DebugDumpInterface
{

    private $_isJsLoaded=false;

    private function _getStatic(){
        $static=p\plug('asset');
        if($this->_isJsLoaded){
            return $static;
        }
        $this->_isJsLoaded=true;
        $static->importJs($this['js']);
        $static->js("var log = new dlog({ level: 'trace', name: 'PMVC'});");
        return $static;
    }

    public function init()
    {
        if (!isset($this['js'])) {
            $this['js'] =
                '//cdn.jsdelivr.net/npm/organism-react-ajax@latest/build/src/lib/dlog.min.js';
        }
    }

    public function escape($string)
    {
        if (!empty($string) && is_string($string)) {
            if (!mb_detect_encoding($string,'utf-8',true)) {
                $string = utf8_encode($string);
            }
            return strtr($string, array('\\'=>'\\\\',"'"=>"\'",'"'=>'\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
        }
    }

    public function dump($p,$type=null){
        $debug = \PMVC\plug('debug');
        if (!$debug->isShow($type, $this['level'])) {
            return;
        }
        $strJson = json_encode($p);
        $static = $this->_getStatic();
        if (!$debug->levelToInt($type, null)) {
            $static->js("log.show('".$type."',[".$strJson."])"); 
        } else {
            $static->js("log.".$type."(".$strJson.")"); 
        }
        unset($strJson, $p, $type);
        $static->echoJs();
    }
}

