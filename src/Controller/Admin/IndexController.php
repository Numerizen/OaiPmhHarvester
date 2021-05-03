<?php declare(strict_types=1);
namespace OaiPmhHarvester\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use OaiPmhHarvester\Form\HarvestForm;
use OaiPmhHarvester\Form\SetsForm;
use Omeka\Stdlib\Message;

class IndexController extends AbstractActionController
{
    /**
     * A standard php server allows only 1000 fields, and there are three fields
     * by set (prefix, hidden, harvest).
     *
     * @var int
     */
    protected $maxListSets = 200;

    /**
     * List of managed metadata prefixes.
     *
     * The order is used to set the default format in the second form.
     * Full Dublin Core is preferred.
     *
     * @var array
     */
    protected $metadataPrefixes = [
        'oai_dcterms',
        'oai_dcq',
        'oai_qdc',
        'dcterms',
        'qdc',
        'dcq',
        'oai_dc',
        'dc',
        'mets',
    ];

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
        // FIXME Validate post.
        $post = $this->params()->fromPost();
        $endpoint = @$post['endpoint'];

        // Avoid direct acces to the page.
        if (empty($endpoint)) {
            return $this->redirect()->toRoute('admin/oaipmhharvester');
        }

        $harvestAllRecords = !empty($post['harvest_all_records']);

        $url = $endpoint . '?verb=Identify';
        $response = @\simplexml_load_file($url);
        if (!$response) {
            $message = sprintf($this->translate('The endpoint "%s" does not return xml.'), $endpoint); // @translate
            $this->messenger()->addError($message);
            return $this->redirect()->toRoute('admin/oaipmhharvester');
        }

        $repositoryName = (string) $response->Identify->repositoryName ?: $this->translate('[Untitled repository]'); // @translate

        $formats = $this->listOaiPmhFormats($endpoint);
        if (empty($formats)) {
            $message = sprintf($this->translate('The endpoint "%s" does not manage any format.'), $endpoint); // @translate
            $this->messenger()->addError($message);
            return $this->redirect()->toRoute('admin/oaipmhharvester');
        }

        $favoriteFormat = array_intersect($this->metadataPrefixes, $formats);
        $favoriteFormat = reset($favoriteFormat) ?: 'oai_dc';

        // Fixes Windows and Apple copy/paste from a textarea input, then explode it.
        $sets = [];
        $predefinedSets = array_filter(array_map('trim', explode("\n", str_replace(["\r\n", "\n\r", "\r"], ["\n", "\n", "\n"], $post['sets']))), 'strlen');
        foreach ($predefinedSets as $set) {
            $id = trim((string) strtok($set, '='));
            if (strlen($id)) {
                $sets[$id] = trim((string) strtok('=')) ?: $favoriteFormat;
            }
        }

        $predefinedSets = (bool) $predefinedSets;
        if ($predefinedSets && !$sets) {
            $message = $this->translate('The sets you specified are not correctly formatted.'); // @translate
            $this->messenger()->addError($message);
            return $this->redirect()->toRoute('admin/oaipmhharvester');
        }

        // Check if all sets have a managed format.
        if ($sets) {
            $checks = array_filter($formats, function ($v, $k) {
                return $v === $k;
            }, ARRAY_FILTER_USE_BOTH);
            $unmanaged = array_filter($sets, function ($v) use ($checks) {
                return !in_array($v, $checks);
            });
            if ($unmanaged) {
                $message = sprintf(
                    $this->translate('The following formats are not managed: "%s".'), // @translate
                    implode('", "', $unmanaged)
                );
                $this->messenger()->addError($message);
                return $this->redirect()->toRoute('admin/oaipmhharvester');
            }
        }

        if ($sets) {
            $total = null;
        } else {
            $sets = $harvestAllRecords ? ['total' => null, 'sets' => []] : $this->listOaiPmhSets($endpoint);
            $total = $sets['total'];
            $sets = $sets['sets'];
        }

        $options = [
            'repository_name' => $repositoryName,
            'endpoint' => $endpoint,
            'formats' => $formats,
            'sets' => $sets,
            'harvest_all_records' => $harvestAllRecords,
            'predefined_sets' => $predefinedSets,
            'favorite_format' => $favoriteFormat,
        ];
        $form = $this->getForm(SetsForm::class, $options);

        $view = new ViewModel;
        return $view
            ->setVariable('form', $form)
            ->setVariable('repositoryName', $repositoryName)
            ->setVariable('total', $total)
            ->setVariable('harvestAllRecords', $harvestAllRecords)
        ;
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

        $message = sprintf($this->translate('Harvesting from "%s" sets:'), $post['endpoint']) // @translate
            . ' ';

        $repositoryName = $post['repository_name'];
        $harvestAllRecords = !empty($post['harvest_all_records']);

        // List item sets and create oai-pmh harvesting sets.
        // FIXME Check if the item sets exist.
        // TODO Append description of sets, if any.
        $sets = [];
        if ($harvestAllRecords) {
            $prefix = $post['namespace'][0];
            $message .= $repositoryName;
            $toCreate = [
                // dctype:Collection.
                'o:resource_class' => ['o:id' => 23],
                'dcterms:title' => [[
                    'type' => 'literal',
                    'property_id' => 1,
                    '@value' => $repositoryName,
                ]],
                'dcterms:isFormatOf' => [[
                    'type' => 'uri',
                    'property_id' => 37,
                    '@id' => $post['endpoint'],
                    'o:label' => 'OAI-PMH repository',
                ]],
            ];
            $itemSet = $this->api()->create('item_sets', $toCreate)->getContent();
            $sets[''] = [
                'set_name' => $repositoryName,
                'metadata_prefix' => $prefix,
                'item_set_id' => $itemSet->id(),
            ];
        } else {
            foreach (array_keys($post['harvest']) as $id) {
                $prefix = $post['namespace'][$id];
                $label = $post['setSpec'][$id];
                $message .= sprintf(
                    $this->translate('%s as %s'), // @translate
                    $label,
                    $prefix
                ) . ' | ';
                $toCreate = [
                    // dctype:Collection.
                    'o:resource_class' => ['o:id' => 23],
                    'dcterms:title' => [[
                        '@value' => $label,
                        'type' => 'literal',
                        'property_id' => 1,
                    ]],
                    'dcterms:isFormatOf' => [[
                        'type' => 'uri',
                        'property_id' => 37,
                        '@id' => $post['endpoint'],
                        'o:label' => 'OAI-PMH repository',
                    ]],
                ];
                $itemSet = $this->api()->create('item_sets', $toCreate)->getContent();
                $sets[$id] = [
                    'set_name' => $label,
                    'metadata_prefix' => $prefix,
                    'item_set_id' => $itemSet->id(),
                ];
            }
        }

        $message = rtrim($message, '| ') . '.';
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

        $urlHelper = $this->url();
        foreach ($sets as $setSpec => $set) {
            //  . "?metadataPrefix=" . $set[1] . "&verb=ListRecords&set=" . $setSpec
            $endpoint = $post['endpoint'];
            // TODO : job harvest / job item creation ?
            // TODO : toutes les propriétés (prefix, resumption, etc.)
            $args = [
                'repository_name' => $repositoryName,
                'endpoint' => $endpoint,
                'set_spec' => $setSpec,
                'item_set_id' => $set['item_set_id'],
                'has_err' => 0,
                'metadata_prefix' => $set['metadata_prefix'],
                'resource_type' => 'items',
                'filters' => $filters,
            ] + $set;
            $job = $dispatcher->dispatch(\OaiPmhHarvester\Job\Harvest::class, $args);

            $message = new Message(
                vsprintf($this->translate('Harvesting %1$s started in background (job %2$s#%3$d%4$s, %5$slogs%4$s). This may take a while.'), // @translate
                [
                    $set['set_name'],
                    sprintf(
                        '<a href="%s">',
                        htmlspecialchars($urlHelper->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]))
                    ),
                    $job->getId(),
                    '</a>',
                    sprintf(
                        '<a href="%s">',
                        htmlspecialchars($urlHelper->fromRoute('admin/id', ['controller' => 'job', 'action' => 'log', 'id' => $job->getId()]))
                    ),
                ]
            ));
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
        }

        return $this->redirect()->toRoute('admin/oaipmhharvester/past-harvests');
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
        $response = $this->api()->search('oaipmhharvester_harvests', $query);

        $this->paginator($response->getTotalResults(), $page);
        $view->setVariable('harvests', $response->getContent());
        return $view;
    }

    protected function undoJob($jobId)
    {
        $response = $this->api()->search('oaipmhharvester_harvests', ['job_id' => $jobId]);
        $harvest = $response->getContent()[0];
        $dispatcher = $this->jobDispatcher();
        $job = $dispatcher->dispatch(\OaiPmhHarvester\Job\Undo::class, ['jobId' => $jobId]);
        $response = $this->api()->update(
            'oaipmhharvester_harvests',
            $harvest->id(),
            [
                'o:undo_job' => ['o:id' => $job->getId() ],
            ]
        );
        return $job;
    }

    /**
     * Prepare the list of formats of an OAI-PMH repository.
     *
     * @param string $endpoint
     * @return string[] Associative array of format prefix and name.
     */
    protected function listOaiPmhFormats($endpoint)
    {
        $formats = [];

        $url = $endpoint . '?verb=ListMetadataFormats';
        $response = @\simplexml_load_file($url);
        if ($response) {
            foreach ($response->ListMetadataFormats->metadataFormat as $format) {
                $prefix = (string) $format->metadataPrefix;
                if (in_array($prefix, $this->metadataPrefixes)) {
                    $formats[$prefix] = $prefix;
                } else {
                    $formats[$prefix] = sprintf($this->translate('%s [unmanaged]'), $prefix); // @translate
                }
            }
        }

        return $formats;
    }

    /**
     * Prepare the list of sets of an OAI-PMH repository.
     *
     * @param string $endpoint
     * @return array
     */
    protected function listOaiPmhSets($endpoint)
    {
        $sets = [];

        $baseListSetUrl = $endpoint . '?verb=ListSets';
        $resumptionToken = false;
        $totalSets = null;
        do {
            $url = $baseListSetUrl;
            if ($resumptionToken) {
                $url = $baseListSetUrl . '&resumptionToken=' . $resumptionToken;
            }

            /** @var \SimpleXMLElement $response */
            $response = \simplexml_load_file($url);
            if (!$response || !isset($response->ListSets)) {
                break;
            }

            if (is_null($totalSets)) {
                $totalSets = isset($response->ListRecords->resumptionToken)
                    ? (int) $response->ListSets->resumptionToken['completeListSize']
                    : count($response->ListSets->set);
            }

            foreach ($response->ListSets->set as $set) {
                $sets[(string) $set->setSpec] = (string) $set->setName;
                if (count($sets) >= $this->maxListSets) {
                    break 2;
                }
            }

            $resumptionToken = isset($response->ListSets->resumptionToken) && $response->ListSets->resumptionToken !== ''
                ? (string) $response->ListSets->resumptionToken
                : false;
        } while ($resumptionToken && count($sets) <= $this->maxListSets);

        return [
            'total' => $totalSets,
            'sets' => array_slice($sets, 0, $this->maxListSets),
        ];
    }
}
