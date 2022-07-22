<?php
namespace PMVC\PlugIn\debug;
use PMVC as p;
use PMVC\Event;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__ . '\debug_console';

p\initPlugin(['debug' => null]);

class debug_console extends p\PlugIn implements DebugDumpInterface
{
    private $_isJsLoaded = false;
    private $_tmp = [];
    private $_flag;

    public function init()
    {
        if (!isset($this['js'])) {
            $this['js'] =
                'https://cdn.jsdelivr.net/npm/organism-react-ajax@0.17.6/build/dlog.min.js';
        }
        if (\PMVC\exists('dispatcher', 'plug')) {
            $dispatcher = \PMVC\plug('dispatcher');
            $dispatcher->attach($this, Event\WILL_PROCESS_VIEW);
            $dispatcher->attach($this, Event\FINISH);
        } else {
            if (!isset($this['isReady'])) {
                $this['isReady'] = true;
            }
        }
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $this->_flag =
                JSON_HEX_APOS |
                JSON_UNESCAPED_UNICODE |
                JSON_INVALID_UTF8_SUBSTITUTE;
        } else {
            $this->_flag = JSON_HEX_APOS | JSON_UNESCAPED_UNICODE;
        }
    }

    private function _getStatic()
    {
        $static = p\plug('asset');
        if ($this->_isJsLoaded) {
            return $static;
        }
        $this->_isJsLoaded = true;
        $static->importJs($this['js']);
        $static->js("var log = new dlog({ level: 'trace', name: 'PMVC'});");
        return $static;
    }

    private function _flush($console = null)
    {
        $this['isReady'] = true;
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

    public function onWillProcessView()
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

    public function escape($string, $type = null)
    {
        if (!empty($string) && is_string($string)) {
            $pUtf8 = \PMVC\plug('utf8');
            if (!$pUtf8->detectEncoding($string, 'utf-8', true)) {
                $string = $pUtf8->convertEncoding($string, 'utf-8');
            }
            // return strtr($string, array('\\'=>'\\\\',"'"=>"\'",'"'=>'\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
        }
        return $string;
    }

    protected function __dump($s)
    {
        echo '<!-- "> -->'; //hack for fixed unclose tag
        $static = $this->_getStatic();
        $static->js($s);
        $static->echoJs();
        return;
    }

    public function dump($p, $type = null)
    {
        $debug = \PMVC\plug('debug');
        if (!$debug->isShow($type, $this['level'])) {
            return;
        }
        if (!$this['isReady']) {
            $this->_tmp[] = [$type, $p];
            return;
        }

        $strJson =
            '\'' .
            str_replace(
                '\\',
                '\u005C',
                \PMVC\utf8JsonEncode($p, $this->_flag)
            ) .
            '\'';
        if (!$debug->levelToInt($type, null)) {
            $this->__dump('log.show(\'' . $type . '\',[' . $strJson . '])');
        } else {
            $this->__dump('log.' . $type . '(' . $strJson . ')');
        }
    }
}
