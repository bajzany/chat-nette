<?php
/**
 * Created by PhpStorm.
 * User: langr
 * Date: 7.4.2018
 * Time: 13:43
 */

namespace App\Controls\Sidebar;


trait SidebarComponent
{

    private $sidebarFactory;

    private $paramsa;
//    private $params;

    public function injectSidebarFactory(SidebarFactory $sidebarFactory)
    {
        $this->sidebarFactory = $sidebarFactory;
    }

    protected function createComponentSidebar()
    {
        return $this->sidebarFactory->create( $this->paramsa);
    }

    public function setParams($params) {
        $this->paramsa = $params;
    }
}