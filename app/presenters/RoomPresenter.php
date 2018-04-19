<?php

namespace App\Presenters;

use App\Controls\Sidebar\MessageWindowComponent;
use App\Controls\Sidebar\SidebarComponent;
use App\Manager\MessageManager;
use App\Model\RoomManager;
use Nette\Application\UI\Form;

class RoomPresenter extends SecurityPresenter
{
    use SidebarComponent;

    /** @var MessageManager $messageManager @inject */
    public $messageManager;

    /** @var RoomManager $roomManager  @inject */
    public $roomManager;

    public $roomId;

//    protected function startup()
//    {
//        parent::startup();
//    }

    public function renderDetail($id)
    {
        $this->roomId = $id;

        $this->setParams(['roomId' => $id]);
        $this->template->room = $this->roomManager->getRoom($id);
        $messages = $this->roomManager->getMessagesAmount($id, 100);
        $messages = array_reverse($messages);
        $this->template->messages = $messages;

        if (!$this->isAjax()) {
            if ($this->getUser()->isLoggedIn()) {
                $data = [ 'room_id' => $this->roomId, 'user_id' => $this->getUser()->getId() ];
                $this->roomManager->insertUserRoom($data);
            }
        }

        $this->redrawControl('chat-window-snippet');
        $this->handleRefreshSidebar($this->roomId);
    }

    protected function createComponentSendMessageForm()
    {
        $form = new Form;

        $form->addText('text', 'Zpráva:', 255)
            ->setRequired()
            ->setAttribute("id", "message");
        $form->addHidden("roomId", $this->roomId);
        $form->addSubmit('send', 'Odeslat')
            ->setAttribute('class', 'ajax');
        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style

        $form->onSuccess[] = [$this, 'signSendMessageFormSucceeded'];
        return $form;
    }

    public function signSendMessageFormSucceeded(Form $form, $values)
    {
        $data['text'] = $values->text;
        $data['date'] = new \DateTime();
        $data['user_id'] = $this->getUser()->getIdentity()->getId();
        $data['room_id'] = $values->roomId;

        try{
            $this->messageManager->createMessage($data);
        }   catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }

        $this->handleRefreshChat();
    }

    public function handleRefreshChat()
    {
        $this->redrawControl('chat-window-snippet');
    }
}