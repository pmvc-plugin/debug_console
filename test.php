<?php
PMVC\Load::plug();
class DebugConsoleTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_console';

    public function setup()
    {
      PMVC\addPlugInFolders(['../']);
    }

    public function teardown()
    {
      \PMVC\unplug($this->_plug);
      \PMVC\unplug('debug');
      \PMVC\unplug('dispatcher');
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

    public function testDefaultLevel()
    {
        $p = PMVC\plug($this->_plug);
        $this->assertEquals($p['level'], null);
    }

    public function testAutoIsReady()
    {
        \PMVC\folders(_PLUGIN, [], [], true);
        $p = PMVC\plug($this->_plug, [_PLUGIN_FILE=>'./debug_console.php']);
        $this->assertEquals($p['isReady'], true);
    }

    public function testAutoIsNotReady()
    {
        $p = PMVC\plug($this->_plug, ['isReady' => false]);
        $this->assertEquals($p['isReady'], false);
    }

    public function testDefaultIsReady()
    {
        $p = PMVC\plug($this->_plug);
        $this->assertEquals($p['isReady'], null);
    }
}
