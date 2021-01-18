<?php
declare(strict_types=1);

namespace FrontModule;

use App\Articles;
use App\ChatControl;
use App\ChatControlFactory;
use App\Cups;
use App\CupSelectionControl;
use App\CupSelectionControlFactory;
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
    private Messages $messages;
    private CupSelectionControlFactory $cupSelectionControlFactory;

    public function __construct(Routes $routes, ChatControlFactory $chatControlFactory,
                                PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory,
                                CupSelectionControlFactory $cupSelectionControlFactory,
                                Cups $cups, Articles $articles, Messages $messages)
    {
        $this->routes = $routes;
        $this->chatControlFactory = $chatControlFactory;
        $this->planControlFactory = $planControlFactory;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->cups = $cups;
        $this->articles = $articles;
        $this->messages = $messages;
        $this->cupSelectionControlFactory = $cupSelectionControlFactory;
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

    protected function createComponentCupSelectionControl(): CupSelectionControl
    {
        $onSelect = function ($cupid) {
            $url = $this->link('Homepage:setCupid', $cupid);
            $this->redirectUrl($url);
        };
        return $this->cupSelectionControlFactory->create($this->cupid, $onSelect);
    }

    public function actionDefault()
    {
        $this->template->cupActive = $this->cupActive;
        $this->template->article = $this->articles->findAll()->order('created DESC')->limit(1)->fetch();
    }

    public function actionSetCupid(?int $cupid)
    {
        if (!is_null($cupid)) {
            $this->cupid = $cupid;
        }
        $this->redirect('Homepage:default');
    }

    public function actionRules()
    {
        $this->template->routes = $this->cups->find($this->cupid)->related('cups_routes');
    }
}
