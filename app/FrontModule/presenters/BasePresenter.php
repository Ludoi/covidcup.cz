<?php
declare(strict_types=1);

namespace FrontModule;

use App\Cups;
use App\Messages;
use Nette\Application\UI\Presenter;

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

    public function injectCups(Cups $cups): void
    {
        $this->baseCups = $cups;
    }

    public function injectMessages(Messages $messages): void
    {
        $this->baseMessages = $messages;
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        $this->setLayout('layout');
        if ($this->user->isLoggedIn()) {
            $racerid = $this->baseCups->getRacerid($this->baseCups->getActive(), $this->user->getId());
            if (!is_null($racerid)) {
                $listOfMessages = $this->baseMessages->getUnreadMessages($racerid);
                foreach ($listOfMessages as $message) {
                    $this->flashMessage((string)$message->message, (string)$message->type);
                    $message->update(['displayed' => true]);
                }
            }
        }
    }
}
