<?php

namespace Grinderspro\Simpla\Pluginsystem;

/**
 * Class Plugins
 *
 * Своя система плагинов, основанная на трейтах (traits PHP 5.4) для расширения функционала SimplaCMS
 * с минимальным вмешательством в ядро
 *
 * @author grinderspro <grinderspro@gmail.com>
 */

require_once('api/Simpla.php');


class Plugins extends Simpla
{

    use mobileMenu, cssBuild;

    const PLUGIN_DIR = 'plugins';

    function __construct()
    {

        parent::__construct();

        $this->design->smarty->registerPlugin("function", "getMobileMenu", array(
            $this,
            'getMobileMenu'
        ));

        $this->design->smarty->registerPlugin("function", "getCssBuild", array(
            $this,
            'getCssBuild'
        ));

        //...

    }
}
