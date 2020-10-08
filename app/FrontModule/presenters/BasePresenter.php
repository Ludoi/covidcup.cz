<?php

namespace FrontModule;

use Nette\Application\UI\Presenter;

/**
 * Base class for all application presenters.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
abstract class BasePresenter extends Presenter {
    protected function beforeRender()
    {
        parent::beforeRender();
        $this->setLayout('layout');
    }
}
