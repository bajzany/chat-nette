<?php

namespace App\Presenters;


use App\Controls\Sidebar\SidebarFactory;
use App\Manager\UserManager;
use App\Model\RoomManager;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;

/**
 */
class SecurityPresenter extends Presenter
{
    CONST INTERVAL_OFFLINE = 5; // in minutes

    private $sidebarFactory;

    /* @var UserManager $userManager */
    private $userManager;

    /* @var RoomManager $roomManager */
    private $roomManager;

    protected $params;

    /**
     * @param SidebarFactory $sidebarFactory
     * @param UserManager $userManager
     * @param RoomManager $roomManager
     */
    public function injectDependecies(SidebarFactory $sidebarFactory, UserManager $userManager, RoomManager $roomManager)
    {
        $this->sidebarFactory = $sidebarFactory;
        $this->userManager = $userManager;
        $this->roomManager = $roomManager;
    }

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn())
        {
            $message = Html::el();
            $link = Html::el('a')->href($this->link('Sign:in'))->setText('Přihlásit');
            $message->addHtml("Pro využívání služeb chatu je potřeba být přihlášen! ");
            $message->addHtml($link);
            $this->flashMessage($message, 'alert-danger');
        } else {
            if ($this->getUser() ) {
                if ($this->getUser()->isLoggedIn())
                {
                    if (!$this->isAjax())
                    {
                        $this->roomManager->deleteUserRooms( $this->getUser()->getId() );

                        $this->userManager->updateUserActivity($this->getUser()->getId(), ['last_activity%dt' => date("Y-m-d H:i:s")]);
                        $this->userManager->updateUserStatus($this->getUser()->getId(), ['status' => 1]);
                        $this->userManager->checkStatusAllUsers(self::INTERVAL_OFFLINE);
                    }
                }
            }
        }
    }

    public function homepageRefresh($refreshSidebar = true)
    {
        $this->template->rooms = $this->roomManager->getRoomsHomepage();

        $this->redrawControl('rooms-snippet');
        if ($refreshSidebar) {
            $this->handleRefreshSidebar();
        }
    }
    public function logout()
    {
        $this->userManager->updateUserActivity( $this->getUser()->getId(), ['last_activity%dt' => date("Y-m-d H:i:s")]);
        $this->userManager->updateUserStatus( $this->getUser()->getId(), ['status' => 0]);
        $this->roomManager->deleteUserRooms( $this->getUser()->getId() );

        $this->flashMessage('Odhlášení bylo úspěšné.', 'alert-success');
        $this->getUser()->logout();
        $this->redirect('Homepage:');
    }

    public function isAdmin()
    {
        if (!$this->getUser()->isInRole('admin'))
        {
            $this->flashMessage('Nejsi Admin!', 'alert-danger');
            $this->redirect('Homepage:');
        }
    }

    protected function createComponentSidebar()
    {
        return $this->sidebarFactory->create($this->params);
    }

    public function handleRefreshSidebar($roomId = null)
    {
        $this['sidebar']->handleRefresh($roomId);
    }

}