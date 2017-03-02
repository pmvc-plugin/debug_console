<?php
PMVC\Load::plug();
PMVC\addPlugInFolders(['../']);
class DebugConsoleTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_console';

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
        ob_start();
        \PMVC\d('aaa');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('aaa',$output);
    }
}
