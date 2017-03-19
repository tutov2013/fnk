<?php
global $obFnk;

class Fnk
{
    public function __construct()
    {
        $sDirClasses = __DIR__ . '/class/';
        $arClasses = array('Helper', 'View');
        foreach ($arClasses as $sClass) {
            require $sDirClasses . $sClass . '.php';
            $this->{$sClass} = new $sClass();
        }
    }
}

$obFnk = new Fnk();
$sPluginDir = plugin_dir_url(__FILE__);
wp_register_style('fnk.css', $sPluginDir . 'css/fnk.css', array(), '1.0');
wp_enqueue_style('fnk.css');