<?php
declare(strict_types=1);

namespace FrontModule;

use App\Articles;
use App\ChatControl;
use App\ChatControlFactory;
use App\Cups;
use App\Messages;
use App\PlanControl;
use App\PlanControlFactory;
use App\ResultEnterControl;
use App\ResultEnterControlFactory;
use App\Routes;

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
    private Articles $articles;
    private int $cupid;
    private Messages $messages;

    public function __construct(Routes $routes, ChatControlFactory $chatControlFactory,
                                PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory,
                                Cups $cups, Articles $articles, Messages $messages)
    {
        $this->routes = $routes;
        $this->chatControlFactory = $chatControlFactory;
        $this->planControlFactory = $planControlFactory;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->cups = $cups;
        $this->articles = $articles;

        $this->cupid = $cups->getActive();
        $this->messages = $messages;
    }

    protected function createComponentChatControl(): ChatControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->chatControlFactory->create($this->cupid, $onInsert);
    }

    protected function createComponentPlanControl(): PlanControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->planControlFactory->create($this->cupid, null, false, $onInsert);
    }

    protected function createComponentResultEnterControl(): ResultEnterControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->resultEnterControlFactory->create($this->cupid, null, $onInsert);
    }

    public function actionDefault()
    {
        $this->template->article = $this->articles->findAll()->order('created DESC')->limit(1)->fetch();
    }

    public function actionRules() {
        $this->template->routes = $this->cups->find($this->cupid)->related('cups_routes');
    }
}
