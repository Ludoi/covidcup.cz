<?php
declare(strict_types=1);

namespace FrontModule;

use App\Cups;
use App\CupsRacers;
use App\EmailQueue;
use App\Users;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Latte\Engine;
use Latte\Runtime\Template;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tracy\Dumper;

/**
 * Registration presenter.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
class RegistrationPresenter extends BasePresenter
{
    private Users $users;
    private EmailQueue $emailQueue;
    private Cups $cups;
    private CupsRacers $cupsRacers;

    public function __construct(Users $users, EmailQueue $emailQueue, Cups $cups, CupsRacers $cupsRacers)
    {
        $this->users = $users;
        $this->cups = $cups;
        $this->emailQueue = $emailQueue;
        $this->cupsRacers = $cupsRacers;
    }

    public function renderDefault()
    {
    }

    public function actionConfirmation(string $initial)
    {
        $user = $this->users->findOneBy(['initial' => $initial]);
        $this->template->initial = null;
        if (!is_null($user)) {
            if (!$user->active) {
                $this->template->initial = $initial;
                $this->template->userProfile = $user;
            } else {
                $this->flashMessage('Účastník už je potvrzený.');
                $this->redirect('Homepage:default');
            }
        } else {
            $this->flashMessage('Špatný kód.', 'error');
            $this->redirect('Homepage:default');
        }
    }

    public function renderConfirmation(string $initial)
    {

    }

    public function actionConfirm(string $initial)
    {
        $user = $this->users->findOneBy(['initial' => $initial]);
        if (!is_null($user)) {
            $this->users->activate((int)$user->id, true);
            $this->cupsRacers->insert([ 'cupid' => $this->cups->getActive(), 'userid' => (int)$user->id ]);
            $this->flashMessage('Přihlášení dokončeno.', 'success');
        }
        $this->redirect('Homepage:default');
    }

    public function actionCancel(string $initial)
    {
        $user = $this->users->findOneBy(['initial' => $initial]);
        if (!is_null($user)) {
            $this->users->remove((int)$user->id);
            $this->flashMessage('Přihlášení smazáno.', 'success');
        }
        $this->redirect('Homepage:default');
    }

    protected function createComponentRegistration() {
        $now = new DateTime;
        $thisYear = (int)$now->format('Y');
        $lowestYear = $thisYear - 100;
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::VERTICAL_MODE;
        $form->addText('firstname', 'Jméno:', 30, 50)->setRequired('Vyplň jméno.')
            ->addRule(Form::FILLED, 'Jméno je povinné.');
        $form->addText('lastname', 'Příjmení:', 50, 50)->setRequired('Vyplň příjmení.')
            ->addRule(Form::FILLED, 'Příjmení je povinné.');
        $form->addText('nickname', 'Přezdívka:', 50, 50)
            ->addRule(Form::FILLED, 'Jméno do výsledků je povinné.');
        $form->addInteger('year', 'Rok narození:')->setRequired('Vyplň rok narození.')
            ->addRule(Form::RANGE, "Rok narození musí být od {$lowestYear} do $thisYear.", array($lowestYear, $thisYear));
        $form->addSelect('gender', 'Pohlaví', array('m' => 'muž', 'f' => 'žena'))
            ->setPrompt('Vyber pohlaví')->setRequired('Vyplň pohlaví.');
        $form->addText('email', 'Email:', 50, 250)->addRule(Form::EMAIL)->setRequired();

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'saveRegistration'];
        return $form;
    }

    public function saveRegistration(Form $form) {
        $entry = $form->getValues();

        $user = $this->users->findOneBy([ 'email' => $entry['email']]);
        if (!is_null($user)) {
            $form->addError('Email už někdo použil.');
            return;
        }
        $user = $this->users->findOneBy([ 'nickname' => $entry['nickname']]);
        if (!is_null($user)) {
            $form->addError('Jméno do výsledků už někdo použil.');
            return;
        }
        list($password, $initial) = $this->users->newUser((string)$entry['email'], (string)$entry['nickname'], (string)$entry['firstname'],
            (string)$entry['lastname'], (int)$entry['year'], (string)$entry['gender'], 'racer', false);
        $emailTemplate = new Engine();
        $params = [ 'password' => $password, 'activationLink' => $this->presenter->link('//Registration:confirmation', $initial) ];

        $email = array(
            'subject' => null,
            'content' => $emailTemplate->renderToString(APP_DIR . '/emails/registration-activation.latte', $params),
            'to' => Strings::lower((string)$entry['email']),
            'cc' => '',
            'bcc' => ''
        );
        $this->emailQueue->publish($email);

        $this->flashMessage('Registrace byla uložena a byl odeslán aktivační email.', 'success');
        $this->redirect('Homepage:default');
    }
}
