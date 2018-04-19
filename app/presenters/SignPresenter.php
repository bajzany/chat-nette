<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 7:50
 */

namespace App\Presenters;

use App\Manager\UserManager;
use App\Model\Authenticator;
use App\Model\RoomManager;
use Nette;
use Nette\Application\UI\Form;

class SignPresenter extends SecurityPresenter
{

    /** @var UserManager @inject */
    public $userManager;

    /** @var RoomManager @inject */
    public $roomManager;

    /** @var Authenticator @inject */
    public $authenticator;

    public function renderIn()
    {
    }

    public function actionOut()
    {
        $this->logout();
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;

        $form->addText('email', 'E-mail: *', 35)
            ->addRule(Form::FILLED, 'Vyplňte Váš email')
            ->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, 'Neplatná emailová adresa');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, Nette\Utils\ArrayHash $values)
    {
        $this->getUser()->setAuthenticator($this->authenticator);

        try {
            $this->getUser()->login($values->email, $values->password);
            $this->flashMessage('Přihlášení bylo úspěšné.', 'alert-success');
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }
}