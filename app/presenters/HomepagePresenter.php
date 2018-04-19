<?php

namespace App\Presenters;

use App\Model\RoomManager;

class HomepagePresenter extends SecurityPresenter
{
    /** @var RoomManager @inject */
    public $roomManager;

    public function renderDefault()
    {
        $this->template->rooms = $this->roomManager->getRoomsHomepage();
    }

    public function handlePageRefresh()
    {
        $this->template->rooms = $this->roomManager->getRoomsHomepage();

        $this->redrawControl('rooms-snippet');

        $this->handleRefreshSidebar();
    }
}
