<?php
declare(strict_types=1);

namespace FrontModule;

/**
 * Base sign class for all application presenters.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
abstract class BaseSignPresenter extends BasePresenter {
    public function __construct() {
        parent::__construct();
    }

    public function startup() {
        parent::startup();
        //check permission
        if (!$this->user->isLoggedIn()) {
            $backlink = $this->storeRequest();
            $this->redirect(':Front:Sign:', array('backlink' => $backlink));
        }
    }

    protected function checkPermissions(string $where, string $what) {
        if (!$this->user->isAllowed($where, $what)) {
            $this->flashMessage('Uživatel nemá k akci oprávnění.', 'danger');
            $this->redirect('Homepage:');
        }
    }

}