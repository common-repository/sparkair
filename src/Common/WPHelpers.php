<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common;

class WPHelpers
{
    public static function hasMainMenuPage($pageName)
    {
        global $menu;
        $found = \false;
        foreach ($menu as $item) {
            if ($item[2] == $pageName) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    public static function hasSubMenuPage($mainPageName, $subPageName)
    {
        global $submenu;
        if (!isset($submenu[$mainPageName])) {
            return \false;
        }
        $found = \false;
        foreach ($submenu[$mainPageName] as $item) {
            if ($item[2] == $subPageName) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
