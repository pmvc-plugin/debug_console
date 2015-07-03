<?php
namespace PMVC\PlugIn\debug_console;
use PMVC as p;
${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_console';

class debug_console extends p\PlugIn
{

    private $bInitJs=false;

    private function getStatic(){
        if($this->bInitJs){
            return p\plug('asset');
        }
        $this->bInitJs=true;
        $static=p\plug('asset');
        $static->importJs('http://i.intw.tw/lib/dlog/dlog.min.js');
        $static->js("var log = new dlog({ level: 'trace'});");
        return $static;
    }

    public function dump($p,$type='info'){
        $json_str = json_encode($p);
        if(!p\exists('asset','plugIn')){
            return;
        }
        $static = $this->getStatic();
        $static->js("log.".$type."(".$json_str.")"); 
        $static->echoJs();
    }
}

