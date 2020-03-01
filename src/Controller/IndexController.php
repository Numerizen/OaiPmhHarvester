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

    /**
     * Main form to set the url.
     */
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
        $baseUrl = $post['base_url'];

        $url = $baseUrl . '?verb=Identify';
        $response = @\simplexml_load_file($url);
        if (!$response) {
            $message = sprintf($this->translate('The url "%s" does not return xml.'), $baseUrl); // @translate
            $this->messenger()->addError($message);
            return $this->redirect()->toRoute('admin/oaipmhharvester');
        }

        $repositoryName = (string) $response->Identify->repositoryName ?: $this->translate('[Untitled repository]'); // @translate

        $formats = [];
        $url = $baseUrl . '?verb=ListMetadataFormats';
        $response = @\simplexml_load_file($url);
        if ($response) {
            foreach ($response->ListMetadataFormats->metadataFormat as $format) {
                $prefix = (string) $format->metadataPrefix;
                if (in_array($prefix, ['oai_dc', 'oai_dcterms', 'dc', 'dcterms', 'mets'])) {
                    $formats[$prefix] = $prefix;
                }
            }
        }

        $sets = [];
        $url = $baseUrl . '?verb=ListSets';
        if (isset($response->ListSets)) {
            $resumptionToken = false;
            $baseListSetUrl = $url;
            do {
                if ($resumptionToken) {
                    $url = $baseListSetUrl . '&resumptionToken=' . $resumptionToken;
                }
                /** @var \SimpleXMLElement $response */
                $response = \simplexml_load_file($url);
                if (!isset($response->ListSets)) {
                    break;
                }

                foreach ($response->ListSets->set as $set) {
                    $sets[(string) $set->setSpec] = (string) $set->setName;
                }

                $resumptionToken = isset($response->ListSets->resumptionToken) && $response->ListSets->resumptionToken !== ''
                    ? (string) $response->ListSets->resumptionToken
                    : false;
            } while ($resumptionToken);
        }

        $form = $this->getForm(SetsForm::class, [
            'base_url' => $baseUrl,
            'formats' => $formats,
            'sets' => $sets,
        ]);

        $view = new ViewModel;
        $view->repositoryName = $repositoryName;
        $view->form = $form;
        return $view;
    }

    /**
     * Launch the harvest process.
     */
    public function harvestAction()
    {
        $post = $this->params()->fromPost();

        $filters = [];
        $filters['whitelist'] = $post['filters_whitelist'];
        $filters['blacklist'] = $post['filters_blacklist'];
        // This method fixes Windows and Apple copy/paste from a textarea input,
        // then explode it by line.
        foreach ($filters as &$filter) {
            $filter = array_filter(array_map('trim', explode("\n", str_replace(["\r\n", "\n\r", "\r"], ["\n", "\n", "\n"], $filter))), 'strlen');
        }

        $message = sprintf($this->translate('Harvesting from "%s" sets:'), $post['base_url']) // @translate
            . ' ';

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

        $message = rtrim($message, '|');
        $this->messenger()->addSuccess($message);

        if ($filters['whitelist']) {
            $message = sprintf($this->translate('These whitelist filters are used: "%s".'), implode('", "', $filters['whitelist']));
            $this->messenger()->addSuccess($message);
        }

        if ($filters['blacklist']) {
            $message = sprintf($this->translate('These blacklist filters are used: "%s".'), implode('", "', $filters['blacklist']));
            $this->messenger()->addSuccess($message);
        }

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
                'filters' => $filters,
            ];
            $job = $dispatcher->dispatch(\OaiPmhHarvester\Job\HarvestJob::class, $harvestJson);
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
        $job = $dispatcher->dispatch(\OaiPmhHarvester\Job\Undo::class, ['jobId' => $jobId]);
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
