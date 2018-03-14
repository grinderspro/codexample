<?php

/**
 * Simpla CMS Plugins
 *
 * @copyright 17.05.2016 Grigiry Miroshnichenko (Grinderspro)
 * @link http://grinderspro.ru
 * @author Grigiry Miroshnichenko
 *
 */

trait cssBuild {

    function getCssBuild($params = array(), &$smarty)  {

        // settings

        $pSettings = json_decode(file_get_contents(__DIR__.'/'.str_replace('.php', '' ,basename(__FILE__)).'.json'))->settings;
        $pSettings->hosts->realHost = $_SERVER['SERVER_NAME'];

        // begin

        if($pSettings->hosts->hostReal !== $pSettings->hosts->hostTarget) {

            $pSettings->css->outputCssFile = $pSettings->css->cssFileBuild;

        } else {

            $pSettings->css->outputCssFile = $pSettings->css->cssFileBuildMin;

        }

        $this->design->assign('pluginSettings', $pSettings);

        $output = $this->design->fetch(__DIR__.'/'.str_replace('.php', '' ,basename(__FILE__)).'.tpl');

        //return

        echo $output;

    }
}
