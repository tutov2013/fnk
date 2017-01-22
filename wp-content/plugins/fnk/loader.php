<?php
global $obFnk;

class Fnk
{
    public function __construct()
    {
        $sDirClasses = __DIR__ . '/class/';
        require $sDirClasses.'Helper.php';
        require $sDirClasses.'View.php';
        $this->Helper = new Helper();
        $this->View = new View();
    }


}

$obFnk = new Fnk();