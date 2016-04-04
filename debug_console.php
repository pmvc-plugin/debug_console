<?php
namespace PMVC\PlugIn\debug;
use PMVC as p;
${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_console';
p\initPlugin(['debug'=>null]);

class debug_console
    extends p\PlugIn
    implements DebugDumpInterface
{

    private $bInitJs=false;

    private function getStatic(){
        if($this->bInitJs){
            return p\plug('asset');
        }
        $this->bInitJs=true;
        $static=p\plug('asset');
        $static->importJs('http://i.intw.tw/lib/dlog/dlog.min.1.js');
        $static->js("var log = new dlog({ level: 'trace'});");
        return $static;
    }

    public function escape($string)
    {
        if (!empty($string) && is_string($string)) {
            return strtr($string, array('\\'=>'\\\\',"'"=>"\'",'"'=>'\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
        }
    }

    public function dump($p,$type='info'){
        $json_str = json_encode($p);
        $static = $this->getStatic();
        $static->js("log.".$type."(".$json_str.")"); 
        $static->echoJs();
    }
}

