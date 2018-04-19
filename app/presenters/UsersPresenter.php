<?php

namespace App\Presenters;

use App\Manager\UserManager;
use Nette\Application\UI\Presenter;

class UsersPresenter extends Presenter
{
    /** @var UserManager @inject */
    public $userManager;

    public function renderDefault()
    {
        $this->template->publicUsers = $this->userManager->getPublicUsers();
    }

    public function handlePageRefresh()
    {
        $this->template->publicUsers = $this->userManager->getPublicUsers();

        $this->redrawControl('users-snippet');
    }
}
