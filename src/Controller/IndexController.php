<?php
namespace OaiPmhHarvester\Controller;

use OaiPmhHarvester\Form\HarvestForm;
use OaiPmhHarvester\Form\SetsForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function indexAction()
    {
        $view = new ViewModel;
        $form = $this->getForm(HarvestForm::class);
        $view->form = $form;
        return $view;
    }

    /**
     * Prepares the sets view.
     */
    public function setsAction()
    {
        $post = $this->params()->fromPost();
        $base_url = $post['base_url'];

        $url = $base_url . "?verb=ListSets";

        $response = \simplexml_load_file($url);
        if (isset($response->ListSets)) {
            $list = "<ul>";
            $sets = [];

            foreach ($response->ListSets->set as $n => $set) {
                $sets[(string) $set->setSpec] = (string) $set->setName;
            }
            $list .= "</ul>";
        }
        $url = $base_url . "?verb=ListMetadataFormats";
        $response = \simplexml_load_file($url);
        $formats = [];
        foreach ($response->ListMetadataFormats->metadataFormat as $idFormat => $format) {
            $prefix = (string) $format->metadataPrefix;
            // TODO : autres formats ?
            if (in_array($prefix, ['oai_dc', 'oai_dcterms', 'dc', 'dcterms', 'mets'])) {
                $formats[$prefix] = $prefix;
            }
        }
        $view = new ViewModel;
        $view->content .= $this->translate('Please choose a set to import.'); // @translate
        $form = $this->getForm(SetsForm::class, ['sets' => $sets, 'formats' => $formats, 'base_url' => $base_url]);
        $view->form = $form;
        return $view;
    }

    /**
     * Launch the harvest process.
     */
    public function harvestAction()
    {
        $post = $this->params()->fromPost();

        $message = 'Harvesting from ' . $post['base_url'] . ' sets :  ';

        // List collections and create sets
        $sets = [];
        foreach ($post['namespace'] as $id => $prefix) {
            if ($post['harvest'][$id] == 'yes') {
                $message .= $post['setSpec'][$id] . ' as ' . $prefix . '|';
                $tocreate = [
                    ["dcterms:title" =>
                        ['@value' => $post['setSpec'][$id],
                            'type' => 'literal',
                            "property_id" => 1,
                        ],
                    ],
                ];
                $setId = $this->api()->create('item_sets', $tocreate, [], ['responseContent' => 'resource'])->getContent();
                $sets[$id] = [$post['setSpec'][$id], $prefix, $setId->getId()];
            }
        }

        $this->messenger()->addSuccess($message);

        $dispatcher = $this->jobDispatcher();

        foreach ($sets as $setSpec => $set) {
            //  . "?metadataPrefix=" . $set[1] . "&verb=ListRecords&set=" . $setSpec
            $url = $post['base_url'];
            // TODO : job harvest / job item creation ?
            // TODO : toutes les propriétés (prefix, resumption, etc.)
            $harvestJson = [
                'comment' => 'Harvest ' . $set[0] . ' from ' . $url,
                'base_url' => $url,
                'set_name' => $set[0],
                'set_spec' => $setSpec,
                'collection_id' => $set[2],
                'has_err' => 0,
                'metadata_prefix' => $set[1],
                'resource_type' => 'items',
            ];
            $job = $dispatcher->dispatch('OaiPmhHarvester\Job\HarvestJob', $harvestJson);
            $this->messenger()->addSuccess('Harvesting ' . $set[0] . ' in Job ID ' . $job->getId());
        }

        return $this->redirect()->toRoute('admin/oaipmhharvester/past-harvests', ['action' => 'pastHarvests'], true);

        $view = new ViewModel;
        $view->content = $this->translate('Processing job Harvest'); // @translate
        return $view;
    }

    public function pastHarvestsAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $undoJobIds = [];
            foreach ($data['jobs'] as $jobId) {
                $undoJob = $this->undoJob($jobId);
                $undoJobIds[] = $undoJob->getId();
            }
            $this->messenger()->addSuccess(sprintf(
                'Undo in progress in the following jobs: %s', // @translate
                implode(', ', $undoJobIds)
            ));
        }

        $view = new ViewModel;
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->params()->fromQuery() + [
            'page' => $page,
            'sort_by' => $this->params()->fromQuery('sort_by', 'id'),
            'sort_order' => $this->params()->fromQuery('sort_order', 'desc'),
        ];
        $response = $this->api()->search('oaipmhharvester_harvestjob', $query);

        $this->paginator($response->getTotalResults(), $page);
        $view->setVariable('imports', $response->getContent());
        return $view;
    }

    protected function undoJob($jobId)
    {
        $response = $this->api()->search('oaipmhharvester_harvestjob', ['job_id' => $jobId]);
        $harvest = $response->getContent()[0];
        $dispatcher = $this->jobDispatcher();
        $job = $dispatcher->dispatch('OaiPmhHarvester\Job\Undo', ['jobId' => $jobId]);
        $response = $this->api()->update(
            'oaipmhharvester_harvestjob',
            $harvest->id(),
            [
                'o:undo_job' => ['o:id' => $job->getId() ],
            ]
        );
        return $job;
    }
}
