<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 11:20
 */

namespace App\Presenters;

use App\Manager\MessageManager;
use App\Manager\TokenManager;
use App\Manager\UserManager;
use App\Model\RoomManager;
use Dibi\Exception;
use Nette\Application\UI\Form;

class AdminPresenter extends SecurityPresenter
{
    /** @var UserManager $userManager @inject */
    public $userManager;

    /** @var MessageManager $messageManager @inject */
    public $messageManager;

    /** @var TokenManager $tokenManager @inject */
    public $tokenManager;

    /** @var RoomManager $roomManager @inject */
    public $roomManager;


    public function __construct()
    {
        parent::__construct();
    }

    protected function startup()
    {
        parent::startup();

        $this->roomManager->deleteUserRooms( $this->getUser()->getId() );
    }

    public function renderUsers()
    {
       $users = $this->userManager->getAllUsers();

       $this->template->allUsers = $users;
    }

    public function renderMessage()
    {
        $messages = $this->messageManager->getAllMessages();
        $this->template->messages = $messages;
    }

    public function renderRooms()
    {
        $rooms = $this->roomManager->getRooms();

        $this->template->rooms = $rooms;
    }

    public function renderApi()
    {

    }

    protected function createComponentCreateApiTokenForm()
    {
        $form = new Form;
        $form->addText('api_key', 'Api klíč:')
            ->setRequired();

        $form->addSubmit('send', 'Vytvořit');
        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style
        $form->onSuccess[] = [$this, 'createApiTokenFormSucceeded'];

        return $form;

    }

    public function createApiTokenFormSucceeded($form, $values)
    {
        try{
            $this->tokenManager->createApiKey($values);
        }catch (Exception $e)
        {
            $this->flashMessage($e->getMessage(),'alert-danger');
            $this->redirect('Admin:users');
        }

        $this->flashMessage('Úspěšně vložen nový klíč', 'alert-success');
        $this->redirect('Admin:users');
    }

    public function actionEditUser($id)
    {
        $user = $this->userManager->getUserById($id);

        if (!$user)
        {
            $this->flashMessage('Uživatel nenalezen!', 'alert-danger');
            $this->redirect('Admin:users');
        }


        $this['editUserForm']->setDefaults($user->toArray());
    }

    protected function createComponentEditUserForm()
    {
        $form = new Form;
        $form->addText('name', 'Uživatelské jméno:')
            ->setRequired();
        $form->addText('email', 'Email:')
            ->setRequired();
        $form->addSelect('role', 'Role:', $this->getGroups())
            ->setRequired()
            ->setAttribute("id", "select2");

        $form->addSubmit('send', 'Upravit');
        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormSucceeded($form, $values)
    {
        $userId = $this->getParameter('id');

        try{
            $this->userManager->updateUser($userId, $values);
        }catch (Exception $e)
        {
            $this->flashMessage($e->getMessage(),'alert-danger');
            $this->redirect('Admin:users');
        }

        $this->flashMessage('Uživatel upraven', 'alert-success');
        $this->redirect('Admin:users');
    }





    /*bajza*/
        public function handlePromote($id)
    {
        $user = $this->userManager->getUserById($id);

        if (!$user)
        {
            $this->flashMessage('Uživatel nenalezen!', 'alert-danger');
            $this->redirect('Admin:users');
        }

        if ($user->role == 'admin')
        {
            $this->flashMessage('Uživatel už je admin!', 'alert-danger');
            $this->redirect('Admin:users');
        }

        try{
            $this->userManager->promoteUser($id);
        }catch (Exception $e)
        {
            $this->flashMessage($e->getMessage(), 'alert-danger');
            $this->redirect('Admin:users');
        }


        $this->flashMessage('Uživatel povýšen na admina', 'alert-success');
        $this->redirect('Admin:users');
    }

    public function handleDemote($id)
    {
        $user = $this->userManager->getUserById($id);

        if ($user->role == 'guest')
        {
            $this->flashMessage('Uživatel už je guest!', 'alert-danger');
            $this->redirect('Admin:users');
        }

        if ($this->getUser()->getIdentity()->getId() == $user->id)
        {
            $this->flashMessage('Nemůžeš sesadit sám sebe!', 'alert-danger');
            $this->redirect('Admin:users');
        }

        try{
            $this->userManager->demoteUser($id);
        }catch (Exception $e)
        {
            $this->flashMessage($e->getMessage(), 'alert-danger');
            $this->redirect('Admin:users');
        }

        $this->flashMessage('Uživatel sesazen z admina na guesta', 'alert-success');
        $this->redirect('Admin:users');
    }
    /*bajta*/





    public function handleDelete($id)
    {
        $user = $this->userManager->getUserById($id);


        if ($this->getUser()->getIdentity()->getId() == $user->id)
        {
            $this->flashMessage('Nemůžeš smazat sám sebe!', 'alert-danger');
            $this->redirect('Admin:users');
        }

        try{
            $this->userManager->deleteUser($id);
        }catch (Exception $e)
        {
            $this->flashMessage($e->getMessage(), 'alert-danger');
            $this->redirect('Admin:users');
        }

        $this->flashMessage('Uživatel byl smazán', 'alert-success');
        $this->redirect('Admin:users');
    }


    /* rooms */
    public function actionEditRoom($id)
    {
        $room = $this->roomManager->getRooms($id);

        $aRoomModerators = explode(",", $room->moderators);
        $room->moderators = $this->roomManager->getModeratorsIdByNames($aRoomModerators);

        if (!$room)
        {
            $this->error('Příspěvek nebyl nalezen');
        }

        $this['roomForm']->setDefaults($room);
    }

    public function actionAddRoom()
    {
    }

    public function handleDeleteRoom($id)
    {
        try {
            $isDeleted = $this->roomManager->deleteRoom($id);

            if ($isDeleted) {
                $this->flashMessage('Skupina byla úspěšně odstraněna.', 'alert-success');
            } else {
                $this->flashMessage('Skupinu se nepodařilo odstranit, již byla odstraněna.', 'alert-danger');
            }
        } catch (Exception $e) {
            $this->flashMessage("Skupinu se nepodařilo odstranit. Zkuste to prosím později.",'alert-danger');
        }

        $this->redirect('this');
    }

    private function getModerators() {
        $aModerators = [];

        $moderators = $this->roomManager->getModerators();
        foreach ($moderators as $moderator) {
            $aModerators[ $moderator->id ] = $moderator->name;
        }

        return $aModerators;
    }

    private function getGroups() {
        $aGroups = [];

        $groups = $this->userManager->getGroups();
        foreach ($groups as $group) {
            $aGroups[ $group->id ] = $group->name;
        }

        return $aGroups;
    }

    protected function createComponentRoomForm()
    {
        $aModerators = $this->getModerators();

        $form = new Form;
        $form->addText('name', 'Název:')
            ->setRequired()
            ->addRule(Form::FILLED, 'Vyplňte prosím název skupiny')
            ->addRule(FORM::MAX_LENGTH, 'Název je příliš dlouhý', 20);

        $form->addText('description', 'Popis:')
            ->setRequired(false)
            ->addRule(FORM::MAX_LENGTH, 'Popis je příliš dlouhý', 100);

        $form->addMultiSelect('moderators', 'Moderátor:', $aModerators)
            ->setRequired()
            ->addRule(Form::FILLED, 'Zvolte prosím min. 1 moderátora')
            ->setAttribute("id", "select2")
            ->setAttribute("multiple", "multiple");

        $form->addSubmit('send', 'Uložit');

        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style
        $form->onSuccess[] = [$this, 'roomFormSucceeded'];

        return $form;

    }

    public function roomFormSucceeded(Form $form, $values)
    {
        try {
            $roomId = $this->getParameter('id');
            if (is_null($roomId)) {
                $isSaved = $this->roomManager->insertRoom($values);
            } else {
                $isSaved = $this->roomManager->updateRoom($roomId, $values);
            }

            if ($isSaved) {
                $message = is_null($roomId) ? "Skupina byla úspěšně přidána." : "Skupina byla úspěšně upravena.";
                $this->flashMessage($message, 'alert-success');
            } else {
                $this->flashMessage('Skupinu se nepodařilo uložit.', 'alert-danger');
            }
        } catch (Exception $e) {
            $this->flashMessage("Skupinu se nepodařilo uložit. Zkuste to prosím později.",'alert-danger');
        }

        $this->redirect('rooms');
    }
}