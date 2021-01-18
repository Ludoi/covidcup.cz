<?php
declare(strict_types=1);

namespace FrontModule;

use App\Cups;
use App\Messages;
use App\Users;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;

/**
 * Base class for all application presenters.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
abstract class BasePresenter extends Presenter
{
    private Cups $baseCups;
    private Messages $baseMessages;
    private Users $baseUsers;
    protected ?ActiveRow $userProfile;
    protected ?int $cupid;
    protected ?int $racerid;
    protected ?ActiveRow $selectedCup;
    protected ?bool $cupActive;

    public function injectUsers(Users $users): void
    {
        $this->baseUsers = $users;
    }

    public function injectCups(Cups $cups): void
    {
        $this->baseCups = $cups;
    }

    public function injectMessages(Messages $messages): void
    {
        $this->baseMessages = $messages;
    }

    private function setUserProfile()
    {
        if ($this->user->isLoggedIn()) {
            $this->userProfile = $this->baseUsers->find((int)$this->user->getId());
        } else {
            $this->userProfile = null;
        }
    }

    private function setCupid()
    {
        $this->cupid = $this->baseCups->getActive();
        if (!is_null($this->userProfile)) {
            $this->cupid = (int)$this->userProfile->cupid;
            $request = $this->getHttpRequest();
            $storedCupid = (int)$request->getCookie('cupid');
            if ($this->cupid !== $storedCupid) {
                if (!is_null($this->baseCups->find($storedCupid))) {
                    $this->cupid = $storedCupid;
                }
            }
        }
        $this->selectedCup = $this->baseCups->find($this->cupid);
        $now = new \DateTime();
        $this->cupActive = $this->baseCups->isDateValid($this->cupid, $now, false);
    }

    private function setRacerid()
    {
        if (!is_null($this->userProfile)) {
            $this->racerid = $this->baseCups->getRacerid($this->cupid, (int)$this->userProfile->id);
        } else {
            $this->racerid = null;
        }
    }

    protected function startup()
    {
        parent::startup();
        $this->setUserProfile();
        $this->setCupid();
        $this->setRacerid();
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        $this->setLayout('layout');
        if ($this->user->isLoggedIn()) {
            $this->template->userProfile = $this->userProfile;
            if (!is_null($this->racerid)) {
                $listOfMessages = $this->baseMessages->getUnreadMessages($this->racerid);
                foreach ($listOfMessages as $message) {
                    $this->flashMessage((string)$message->message, (string)$message->type);
                    $message->update(['displayed' => true]);
                }
            }
        }
    }
}
