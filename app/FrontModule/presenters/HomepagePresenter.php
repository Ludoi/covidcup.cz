<?php
declare(strict_types=1);

namespace FrontModule;

use App\ChatControl;
use App\ChatControlFactory;
use App\Cups;
use App\PlanControl;
use App\PlanControlFactory;
use App\ResultEnterControl;
use App\ResultEnterControlFactory;
use App\Routes;
use Tracy\Dumper;

/**
 * Homepage presenter.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
class HomepagePresenter extends BasePresenter {
    private Routes $routes;
    private ChatControlFactory $chatControlFactory;
    private PlanControlFactory $planControlFactory;
    private ResultEnterControlFactory $resultEnterControlFactory;
    private Cups $cups;
    private int $cupid;

    public function __construct(Routes $routes, ChatControlFactory $chatControlFactory,
                                PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory,
                                Cups $cups)
    {
        $this->routes = $routes;
        $this->chatControlFactory = $chatControlFactory;
        $this->planControlFactory = $planControlFactory;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->cups = $cups;
        $this->cupid = $cups->getActive();
    }

    protected function createComponentChatControl(): ChatControl
    {
        return $this->chatControlFactory->create($this->cupid);
    }

    protected function createComponentPlanControl(): PlanControl
    {
        return $this->planControlFactory->create($this->cupid, null, false);
    }

    protected function createComponentResultEnterControl(): ResultEnterControl
    {
        return $this->resultEnterControlFactory->create($this->cupid, null);
    }

    public function actionDefault()
    {
    }

    public function actionRules() {
        $this->template->routes = $this->routes->findAll();
    }
}
