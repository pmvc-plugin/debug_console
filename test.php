<?php
PMVC\Load::plug();
PMVC\addPlugInFolders(['../']);
class DebugConsoleTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_console';

    public function setup()
    {
      \PMVC\unplug($this->_plug);
      \PMVC\unplug('debug');
    }

    function testDebugConsole()
    {
        ob_start();
        $plug = PMVC\plug($this->_plug);
        print_r($plug);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains($this->_plug,$output);
    }

    function testDump()
    {
        $plug = PMVC\plug($this->_plug);
        \PMVC\plug('debug', ['output'=>$plug]);
        \PMVC\d('aaa');
        \PMVC\plug('asset', ['flush'=>false]);
        ob_start();
        $plug->onB4ProcessView();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('aaa',$output);
    }
}
