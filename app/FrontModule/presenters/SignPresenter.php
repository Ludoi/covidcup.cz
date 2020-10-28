<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\EmailQueue;
use App\Users;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Latte\Engine;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\Strings;

class SignPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';

    private Users $users;
    private EmailQueue $emailQueue;
    private string $token;

    public function __construct(Users $users, EmailQueue $emailQueue)
    {
        $this->users = $users;
        $this->emailQueue = $emailQueue;
    }

    public function actionDefault()
    {
        if ($this->user->isLoggedIn()) {
            $this->flashMessage('Už jsi přihlášený(á) :-)');
            $this->redirect('Homepage:');
        }
    }
    protected function createComponentSign()
    {
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::VERTICAL_MODE;
        $form->addText('email', 'Email:', 50, 250)->addRule(Form::EMAIL)->setRequired();
        $form->addPassword('password', 'Heslo:', 50, 50)->setRequired();
        $form->addCheckbox('remember', 'Zapamatuj si mě na tomto počítači');
        $form->addProtection();
        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'processSignIn'];
        return $form;
    }

    public function processSignIn(Form $form)
    {
        try {
            $values = $form->getValues();
            if ($values->remember) {
                $this->presenter->getUser()->setExpiration('+ 14 days');
            } else {
                $this->presenter->getUser()->setExpiration('+ 120 minutes');
            }
            $this->presenter->getUser()->login($values->email, $values->password);

            $this->presenter->restoreRequest($this->backlink);
            $this->presenter->redirect('Homepage:');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function renderForgottenPassword()
    {

    }

    protected function createComponentForgottenForm()
    {
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::VERTICAL_MODE;
        $form->addText('email', 'Email:')
            ->setRequired('Zadej email.');
        $form->addProtection();

        $form->addSubmit('send', 'Odeslat');

        $form->onSuccess[] = [$this, 'forgottenFormSubmitted'];

        return $form;
    }

    public function forgottenFormSubmitted(Form $form)
    {
        try {
            $values = $form->getValues();
            if (is_null($user = $this->users->getUser($values->email))) {
                $this->flashMessage('Uživatel nenalezen', 'danger');
            } else if (!$user->active) {
                $this->flashMessage('Uživatel nenalezen', 'danger');
            } else {
                $initial = $this->users->forgottenPassword($values->email);

                $emailTemplate = new Engine();
                $params = ['link' => $this->presenter->link('//Sign:recover', $initial)];

                $email = array(
                    'subject' => null,
                    'content' => $emailTemplate->renderToString(APP_DIR . '/emails/forgotten-password.latte', $params),
                    'to' => Strings::lower((string)$values->email),
                    'cc' => '',
                    'bcc' => ''
                );
                $this->emailQueue->publish($email);
                $this->flashMessage('Email odeslán', 'success');
            }

            $this->redirect('Sign:');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionRecover(string $initial)
    {
        $user = $this->users->findOneBy(['initial' => $initial]);
        if (!is_null($user) && $user->active && $user->forgotten) {
            $this->token = (string)$user->token;
        } else {
            $this->flashMessage('Akci nelze provést', 'danger');
            $this->redirect('Sign:');
        }
    }

    protected function createComponentRecoverForm()
    {
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::VERTICAL_MODE;
        $form->addHidden('token', $this->token);
        $form->addPassword('newPassword1', 'Nové heslo:')->setRequired();
        $form->addPassword('newPassword2', 'Zopakovat heslo:')->setRequired();
        $form->addProtection();

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'recoverFormSubmitted'];
        return $form;
    }

    public function recoverFormSubmitted(Form $form)
    {
        try {
            $values = $form->getValues();

            $user = $this->users->findOneBy(['token' => $values->token]);
            if (is_null($user)) {
                $this->flashMessage('Uživatel nenalezen.', 'danger');
            } else if (!$user->active) {
                $this->flashMessage('Uživatel nenalezen.', 'danger');
            } else if ($values->newPassword1 <> $values->newPassword2) {
                $this->flashMessage('Hesla nejsou stejná.', 'danger');
            } else {
                $this->users->setPassword((int)$user->id, $values->newPassword1);
                $this->flashMessage('Heslo změněno.', 'success');
            }
            $this->redirect('Homepage:');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Byl jsi odhlášen.', 'success');
        $this->redirect('Homepage:');
    }

    protected function createComponentChangePasswordForm()
    {
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::VERTICAL_MODE;
        $form->addPassword('currentPassword', 'Původní heslo:')->setRequired();
        $form->addPassword('newPassword1', 'Nové heslo:')->setRequired();
        $form->addPassword('newPassword2', 'Zopakovat nové heslo:')->setRequired();
        $form->addProtection();

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'changeFormSubmitted'];
        return $form;
    }

    public function actionChangePassword()
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:');
        }
    }

    public function changeFormSubmitted(Form $form)
    {
        try {
            $values = $form->getValues();

            $user = $this->users->find((int)$this->user->identity->getId());
            if (is_null($user)) {
                $this->flashMessage('Uživatel nenalezen.', 'danger');
            } else if (!$user->active) {
                $this->flashMessage('Uživatel nenalezen.', 'danger');
            } else if ($values->newPassword1 <> $values->newPassword2) {
                $form->addError('Nová hesla nesouhlasí.');
                return;
            } else if (!$this->users->checkPassword((int)$this->user->identity->getId(), $values->currentPassword)) {
                $form->addError('Původní heslo není správně.');
                return;
            } else {
                $this->users->setPassword((int)$this->user->identity->getId(), $values->newPassword1);
                $this->flashMessage('Heslo změněno.', 'success');
            }
            $this->redirect('Homepage:');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

}