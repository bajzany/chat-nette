<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 14.03.2018
 * Time: 22:24
 */

namespace App\Presenters;


use Nette\Application\UI\Presenter;

use Nette\Application\UI\Form;
use App\Manager\UserManager;

class RegisterPresenter extends Presenter
{

    /** @var UserManager @inject */
    public $userManager;


    protected function startup()
    {
        parent::startup();
    }

    public function renderRegister()
    {
    }


    /**
     * @return Form
     */
    protected function createComponentRegisterForm()
    {
        $form = new Form;
        $form->addText('name', 'Uživatelské jméno: *')
            ->addRule(Form::FILLED, 'Vyplňte uživatelské jméno')
            ->addCondition(Form::FILLED);
        $form->addText('email', 'E-mail: *', 35)
            ->addRule(Form::FILLED, 'Vyplňte Váš email')
            ->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, 'Neplatná emailová adresa');
        $form->addPassword('password', 'Heslo: *', 20)
            ->setOption('description', 'Alespoň 6 znaků')
            ->addRule(Form::FILLED, 'Vyplňte Vaše heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 6);
        $form->addPassword('password2', 'Heslo znovu: *', 20)
            ->addConditionOn($form['password'], Form::VALID)
            ->addRule(Form::FILLED, 'Heslo znovu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password']);
        $form->addSubmit('send', 'Registrovat');

        $form->setRenderer(new \Instante\Bootstrap3Renderer\BootstrapRenderer); //<< BootstrapForm Style

        $form->onSuccess[] = [$this, 'registerFormSubmitted'];

        return $form;
    }

    public function registerFormSubmitted(Form $form, $values)
    {
        try{
            $isSaved = $this->userManager->register($values);

            if ($isSaved) {
                $this->flashMessage("Registrace uživatele proběhla v pořádku.", 'alert-success');
            } else {
                $this->flashMessage('Uživatele se nepodařilo zaregistrovat.', 'alert-danger');
            }

        }catch (\Dibi\Exception $exception)
        {
            $form->addError("Při registraci uživatele se vyskytla chyba, proveďte registraci prosím znovu.");
            return;
        }

        if($isSaved){
            $this->redirect('Sign:in');
        }
    }
}