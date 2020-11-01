<?php
namespace PMVC\PlugIn\debug;

use PHPUnit_Framework_TestCase;

\PMVC\Load::plug();
\PMVC\addPlugInFolders([__DIR__.'/../']);


class DebugConsoleTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_console';

    public function setup()
    {
        $debug = \PMVC\plug('debug', ['output'=>$this->_plug]);
        \PMVC\plug('asset', ['flush'=>false]);
    }

    public function teardown()
    {
      \PMVC\unplug($this->_plug);
      \PMVC\unplug('dispatcher');
      \PMVC\unplug('debug');
    }

    function testDebugConsole()
    {
        ob_start();
        $plug = \PMVC\plug($this->_plug);
        print_r($plug);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains($this->_plug,$output);
    }

    function testDump()
    {
        $plug = \PMVC\plug($this->_plug, ['isReady' => false]);
        \PMVC\d('aaa');
        ob_start();
        $plug->onB4ProcessView();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('aaa',$output);
    }

    public function testDefaultLevel()
    {
        $p = \PMVC\plug($this->_plug);
        $this->assertEquals($p['level'], null);
    }

    public function testAutoIsReady()
    {
        $old = \PMVC\value(\PMVC\folders(_PLUGIN), ['folders']);
        $folders = \PMVC\folders(_PLUGIN, [], [], true);
        $p = \PMVC\plug($this->_plug, [_PLUGIN_FILE=>'./debug_console.php']);
        $this->assertEquals(true, $p['isReady']);
        \PMVC\addPlugInFolders(array_reverse($old));
    }

    public function testAutoIsNotReady()
    {
        $p = \PMVC\plug($this->_plug, ['isReady' => false]);
        $this->assertEquals(false, $p['isReady']);
    }

    public function testDefaultIsReady()
    {
        $old = \PMVC\value(\PMVC\folders(_PLUGIN), ['folders']);
        $folders = \PMVC\folders(_PLUGIN, [], [], true);
        $p = \PMVC\plug($this->_plug, [_PLUGIN_FILE=>'./debug_console.php']);
        $this->assertEquals(true, $p['isReady']);
        \PMVC\addPlugInFolders(array_reverse($old));
    }
}
