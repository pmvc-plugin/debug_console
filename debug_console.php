<?php
namespace PMVC\PlugIn\debug;
use PMVC as p;
use PMVC\Event;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_console';

p\initPlugin(['debug'=>null]);

class debug_console
    extends p\PlugIn
    implements DebugDumpInterface
{

    private $_isJsLoaded=false;
    private $_tmp = [];

    public function init()
    {
        if (!isset($this['js'])) {
            $this['js'] =
                'https://cdn.jsdelivr.net/npm/organism-react-ajax@0.6.13/build/dlog.min.js';
        }
        if (\PMVC\exists('dispatcher', 'plug')) {
            $dispatcher = \PMVC\plug('dispatcher');
            $dispatcher->attach($this, Event\B4_PROCESS_VIEW);
            $dispatcher->attach($this, Event\FINISH);
        } else {
            if (!isset($this['isReady'])) {
                $this['isReady']=true;
            }
        }
    }

    private function _getStatic()
    {
        $static=p\plug('asset');
        if($this->_isJsLoaded) {
            return $static;
        }
        $this->_isJsLoaded=true;
        $static->importJs($this['js']);
        $static->js("var log = new dlog({ level: 'trace', name: 'PMVC'});");
        return $static;
    }


    private function _flush($console = null)
    {
        $this['isReady']=true;
        if (count($this->_tmp)) {
            if (!$console) {
                $console = \PMVC\plug('debug')->getOutput();
            }
            foreach ($this->_tmp as $a) {
                $console->dump($a[1], $a[0]); 
            }
            $this->_tmp = [];
        }
    }

    public function onB4ProcessView()
    {
        $this->_flush();
    }

    public function onFinish()
    {
        $this->_flush();
    }

    public function __destruct()
    {
        $this->_flush($this);
    }

    public function escape($string)
    {
        if (!empty($string) && is_string($string)) {
            if (!mb_detect_encoding($string, 'utf-8', true)) {
                $string = utf8_encode($string);
            }
            return strtr($string, array('\\'=>'\\\\',"'"=>"\'",'"'=>'\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
        }
    }

    protected function __dump($s)
    {
        $static = $this->_getStatic();
        $static->js($s);
        $static->echoJs();
        return;
    }

    public function dump($p,$type=null)
    {
        $debug = \PMVC\plug('debug');
        if (!$debug->isShow($type, $this['level'])) {
            return;
        }
        if (!$this['isReady']) {
            $this->_tmp[] = [$type, $p];
            return;
        }
        $strJson = json_encode($p);
        if (!$debug->levelToInt($type, null)) {
            $this->__dump("log.show('".$type."',[".$strJson."])"); 
        } else {
            $this->__dump("log.".$type."(".$strJson.")"); 
        }
    }
}

