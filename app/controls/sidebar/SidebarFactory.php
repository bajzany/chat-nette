<?php

namespace App\Controls\Sidebar;

interface SidebarFactory
{
    /**
     * @param $sidebarParams
     * @return Sidebar
     */
    function create($sidebarParams);
}