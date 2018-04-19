<?php

namespace App\Controls\Sidebar;

use App\Manager\UserManager;
use App\Model\RoomManager;
use Dibi\Connection;
use Nette\Application\UI\Control;

Class Sidebar extends Control
{
    /**
     * @var Connection
     */
    private $roomManager;

    private $userManager;

    private $sidebarParams;

    public function __construct($sidebarParams, RoomManager $roomManager, UserManager $userManager)
    {
        parent::__construct();

        $this->sidebarParams = $sidebarParams;
        $this->roomManager = $roomManager;
        $this->userManager = $userManager;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . "/default.latte");

        if (isset($this->sidebarParams['roomId']))
        {
            $roomId = $this->sidebarParams['roomId'];
        } else {
            $roomId = null;
        }
        $template->members = $this->userManager->getUsersOnline($roomId);

        $template->render();
    }

    public function handleRefresh($roomId)
    {
        $this->sidebarParams['roomId'] = $roomId;

        $this->redrawControl("sidebar-snippet");
    }
}
