<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 14:49
 */

namespace App\Presenters;

use App\Manager\MessageManager;
use Nette\Application\UI\Form;

class MessagePresenter extends SecurityPresenter
{

    /** @var MessageManager @inject */
    public $messageManager;

    protected function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {
        $messages = $this->messageManager->getMessagesAmount(10);
        $messages = array_reverse($messages);
        $this->template->messages = $messages;

        $this->redrawControl('chat-window-snippet');
    }

    protected function createComponentSendMessageForm()
    {
        $form = new Form;

        $form->addText('text', 'Zpráva: *', 255)
            ->setRequired()
            ->setAttribute("id", "message");
        $form->addSubmit('send', 'Odeslat')
            ->setAttribute('class', 'ajax');
        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style

        $form->onSuccess[] = [$this, 'signSendMessageFormSucceeded'];
        return $form;
    }

    public function handleAjax()
    {
        $this->redrawControl('chat-window-snippet');
    }

    public function signSendMessageFormSucceeded(Form $form, $values)
    {
        $data['text'] = $values->text;
        $data['date'] = new \DateTime();
        $data['user_id'] = $this->getUser()->getIdentity()->getId();

        try{
            $this->messageManager->createMessage($data);
        }catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }
}