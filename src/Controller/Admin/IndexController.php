<?php declare(strict_types=1);

namespace OaiPmhHarvester\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use OaiPmhHarvester\Form\HarvestForm;
use OaiPmhHarvester\Form\SetsForm;
use Omeka\Entity\Job;
use Omeka\Stdlib\Message;

class IndexController extends AbstractActionController
{
    /**
     * A standard php server allows only 1000 fields, and there are three fields
     * by set (prefix, hidden, harvest).
     *
     * TODO Add a js to return a json and avoid limit of 250 repository sets to harvest.
     *
     * Warning: the repository Calames used for tests is wrong: setSpec are not unique.
     * @link http://www.calames.abes.fr/oai/oai2.aspx?verb=ListSets
     *
     * @var int
     */
    protected $maxListSets = 250;

    /**
     * Main form to set the url.
     */
    public function indexAction()
    {
        /** @var \OaiPmhHarvester\Form\HarvestForm $form */
        $form = $this->getForm(HarvestForm::class);

        if ($this->getRequest()->isPost()) {
            $params = $this->params()->fromRoute();
            $hasError = !empty($params['has_error']);
            $post = $this->params()->fromPost();
            $step = $post['step'] ?? null;
            if (!$hasError && $step === 'harvest-repository') {
                $form->setData($post);
                if ($form->isValid()) {
                    $params = $this->params()->fromRoute();
                    $params['action'] = 'sets';
                    $params['prev_action'] = 'index';
                    return $this->forward()->dispatch(__CLASS__, $params);
                } else {
                    $this->messenger()->addFormErrors($form);
                }
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    /**
     * Prepares the sets view.
     */
    public function setsAction()
    {
        // Avoid direct access to the page.
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/default', ['controller' => 'oai-pmh-harvester', 'action' => 'index']);
        }

        // Check if the post come from index or sets.
        $params = $this->params()->fromRoute();
        $post = $this->params()->fromPost();

        $step = $post['step'] ?? 'harvest-repository';
        $prevAction = $params['prev_action'] ?? null;
        $hasError = !empty($params['has_error']);
        unset($post['step'], $params['prev_action'], $params['has_error']);

        if ($step === 'harvest-repository' || $prevAction === 'index') {
            /** @var \OaiPmhHarvester\Form\HarvestForm $form */
            $form = $this->getForm(HarvestForm::class);
            $form->setData($post);
            if (!$form->isValid()) {
                $params['action'] = 'index';
                $params['has_error'] = true;
                return $this->forward()->dispatch(__CLASS__, $params);
            }
            $data = $form->getData();
        } elseif ($step === 'harvest-list-sets') {
            if ($hasError) {
                return $this->redirect()->toRoute('admin/default', ['controller' => 'oai-pmh-harvester', 'action' => 'index']);
            }
            // The first time, the check is already done.
            // The full check on the full form is done below.
            $data = $post;
        } else {
            return $this->redirect()->toRoute('admin/default', ['controller' => 'oai-pmh-harvester', 'action' => 'index']);
        }

        // Process Harvest form.
        // Most of checks are done via the form in the first step.

        $endpoint = $data['endpoint'];
        $harvestAllRecords = !empty($data['harvest_all_records']);
        $predefinedSets = $data['predefined_sets'] ?? [];
        // In the second form, predefined sets are hdden.
        if (!is_array($predefinedSets)) {
            $predefinedSets = @json_decode($predefinedSets, true) ?: [];
        }
        $data['predefined_sets'] = $predefinedSets;

        // TODO Move last checks to form.
        $optionsData = $this->dataFromEndpoint($endpoint, $harvestAllRecords, $predefinedSets);
        if (!empty($optionsData['message'])) {
            $this->messenger()->addError($optionsData['message']);
            $params['action'] = $optionsData['redirect'] ?? 'sets';
            $params['has_error'] = true;
            return $this->forward()->dispatch(__CLASS__, $params);
        }

        // TODO Add list of existing item sets, taking care of the metadata prefix. Or set it inside the select.

        $optionsData = [
            'step' => 'harvest-list-sets',
        ] + $data + $optionsData;

        // The form for sets is dynamic.
        $form = $this->getForm(SetsForm::class, $optionsData)
            ->setAttribute('action', $this->url()->fromRoute('admin/default', ['controller' => 'oai-pmh-harvester', 'action' => 'sets']));
        $optionsData['predefined_sets'] = json_encode($predefinedSets, 320);
        $form
            ->setData($optionsData);

        if ((!$predefinedSets && $optionsData['total'] <= $this->maxListSets && !empty($optionsData['sets']) && count($optionsData['sets']) !== $optionsData['total'])
            || (!$predefinedSets && $optionsData['total'] > $this->maxListSets && !empty($optionsData['sets']) && count($optionsData['sets']) !== $this->maxListSets)
        ) {
            $this->messenger()->addWarning('This repository has duplicate identifiers for sets, so they are not all displayed. You may warn the admin of the repository.'); // @translate
        }

        // Don't check validity if the previous form was the repository one.
        if ($prevAction === 'index') {
            return new ViewModel([
                'form' => $form,
                'endpoint' => $endpoint,
                'repositoryName' => $optionsData['repository_name'],
                'total' => $optionsData['total'],
                'harvestAllRecords' => $harvestAllRecords,
            ]);
        }

        if (!$harvestAllRecords && !$predefinedSets && empty($data['harvest'])) {
            $this->messenger()->addError('At least one repository should be selected.'); // @translate
            return new ViewModel([
                'form' => $form,
                'endpoint' => $endpoint,
                'repositoryName' => $optionsData['repository_name'],
                'total' => $optionsData['total'],
                'harvestAllRecords' => $harvestAllRecords,
            ]);
        }

        if ($form->isValid()) {
            $params['action'] = 'harvest';
            return $this->forward()->dispatch(__CLASS__, $params);
        }

        $this->messenger()->addFormErrors($form);
        $params['has_error'] = true;
        return new ViewModel([
            'form' => $form,
            'endpoint' => $endpoint,
            'repositoryName' => $optionsData['repository_name'],
            'total' => $optionsData['total'],
            'harvestAllRecords' => $harvestAllRecords,
        ]);
    }

    /**
     * Launch the harvest process.
     */
    public function harvestAction()
    {
        // Avoid direct access to the page.
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/default', ['controller' => 'oai-pmh-harvester', 'action' => 'index']);
        }

        // Check if the post come from index or sets.
        $params = $this->params()->fromRoute();
        $post = $this->params()->fromPost();
        $step = $post['step'] ?? 'harvest-repository';

        if ($step !== 'harvest-list-sets') {
            $params['action'] = 'index';
            $params['has_error'] = true;
            return $this->forward()->dispatch(__CLASS__, $params);
        }

        // Pass a filtered post as params.
        $endpoint = $post['endpoint'];
        $harvestAllRecords = !empty($post['harvest_all_records']);
        $predefinedSets  = $post['predefined_sets'] ?? [];
        // In the second form, predefined sets are hdden.
        if (!is_array($predefinedSets)) {
            $predefinedSets = @json_decode($predefinedSets, true) ?: [];
        }
        $optionsData = $this->dataFromEndpoint($endpoint, $harvestAllRecords, $predefinedSets);
        $form = $this->getForm(SetsForm::class, $optionsData);
        $form->setData($post);
        if (!$form->isValid()) {
            $params['action'] = 'sets';
            return $this->forward()->dispatch(__CLASS__, $params);
        }

        // Process List Sets form.
        $data = $form->getData();

        // TODO Fix get data for namespace. Use fieldset/collection.
        $data['namespace'] = $post['namespace'];
        $data['setSpec'] = $post['setSpec'];
        $data['harvest'] = $post['harvest'];
        foreach (array_keys($data) as $k) {
            if (strpos($k, 'namespace[') === 0
                || strpos($k, 'setSpec[') === 0
                || strpos($k, 'harvest[') === 0
            ) {
                unset($data[$k]);
            }
        }

        $filters = [
            'whitelist' => $data['filters_whitelist'] ?? [],
            'blacklist' => $data['filters_blacklist'] ?? [],
        ];

        $message = new Message(
            $this->translate('Harvesting from "%s" sets'), // @translate
            $data['endpoint']
        );
        $message .= ': ';

        $repositoryName = $data['repository_name'];
        $harvestAllRecords = !empty($data['harvest_all_records']);

        // List item sets and create oai-pmh harvesting sets if needed.
        $api = $this->api();

        // TODO Append description of sets, if any.
        $sets = [];
        if ($harvestAllRecords) {
            $prefix = $data['namespace'][0];
            $message .= $repositoryName;
            $uniqueUri = $data['endpoint'] . "?verb=ListRecords&metadataPrefix=$prefix";
            $itemSet = $api->searchOne('item_sets', ['property' => [['property_id' => 37, 'type' => 'eq', 'text' => $uniqueUri]]])->getContent();
            if (!$itemSet) {
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
                        '@id' => $uniqueUri,
                        'o:label' => 'OAI-PMH repository',
                    ]],
                ];
                $itemSet = $api->create('item_sets', $toCreate)->getContent();
            }
            $sets[''] = [
                'set_name' => $repositoryName,
                'metadata_prefix' => $prefix,
                'item_set_id' => $itemSet->id(),
            ];
        } else {
            foreach (array_keys($data['harvest'] ?? []) as $setSpec) {
                $prefix = $data['namespace'][$setSpec];
                $label = $data['setSpec'][$setSpec];
                $message .= sprintf(
                    $this->translate('%s as %s'), // @translate
                    $label,
                    $prefix
                ) . ' | ';
                $uniqueUri = $data['endpoint'] . "?verb=ListRecords&set=$setSpec&metadataPrefix=$prefix";
                $itemSet = $api->searchOne('item_sets', ['property' => [['property_id' => 37, 'type' => 'eq', 'text' => $uniqueUri]]])->getContent();
                if (!$itemSet) {
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
                            '@id' => $uniqueUri,
                            'o:label' => 'OAI-PMH repository',
                        ]],
                    ];
                    $itemSet = $api->create('item_sets', $toCreate)->getContent();
                }
                $sets[$setSpec] = [
                    'set_name' => $label,
                    'metadata_prefix' => $prefix,
                    'item_set_id' => $itemSet->id(),
                ];
            }
        }

        $message = trim($message, ':| ') . '.';
        $this->messenger()->addSuccess($message);

        if ($filters['whitelist']) {
            $message = new Message(
                $this->translate('These whitelist filters are used: "%s".'), // @translate
                implode('", "', $filters['whitelist']
            ));
            $this->messenger()->addSuccess($message);
        }

        if ($filters['blacklist']) {
            $message = new Message(
                $this->translate('These blacklist filters are used: "%s".'), // @translate
                implode('", "', $filters['blacklist']
            ));
            $this->messenger()->addSuccess($message);
        }

        $urlPlugin = $this->url();
        foreach ($sets as $setSpec => $set) {
            $endpoint = $data['endpoint'];
            // TODO : job harvest / job item creation ?
            // TODO : toutes les propriétés (prefix, resumption, etc.)
            $args = [
                'repository_name' => $repositoryName,
                'endpoint' => $endpoint,
                'set_spec' => $setSpec,
                'item_set_id' => $set['item_set_id'],
                'has_err' => false,
                'metadata_prefix' => $set['metadata_prefix'],
                'entity_name' => 'items',
                'filters' => $filters,
            ] + $set;
            $job = $this->jobDispatcher()->dispatch(\OaiPmhHarvester\Job\Harvest::class, $args);

            $urlPlugin = $this->url();
            // TODO Don't use PsrMessage for now to fix issues with Doctrine and inexisting file to remove.
            $message = new Message(
                'Harvesting %1$s started in background (job %2$s#%3$d%4$s, %5$slogs%4$s). This may take a while.', // @translate
                $set['set_name'],
                sprintf(
                    '<a href="%s">',
                    htmlspecialchars($urlPlugin->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]))
                ),
                $job->getId(),
                '</a>',
                sprintf(
                    '<a href="%s">',
                    // Check if module Log is enabled (avoid issue when disabled).
                    htmlspecialchars(class_exists(\Log\Stdlib\PsrMessage::class)
                        ? $urlPlugin->fromRoute('admin/log/default', [], ['query' => ['job_id' => $job->getId()]])
                        : $urlPlugin->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId(), 'action' => 'log'])
                ))
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
        }

        return $this->redirect()->toRoute('admin/default', ['controller' => 'oai-pmh-harvester', 'action' => 'past-harvests']);
    }

    public function pastHarvestsAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $undoJobIds = [];
            foreach ($data['jobs'] ?? [] as $jobId) {
                $undoJob = $this->undoJob($jobId);
                $undoJobIds[] = $undoJob->getId();
            }
            $this->messenger()->addSuccess(new Message(
                'Undo in progress in the following jobs: %s', // @translate
                implode(', ', $undoJobIds)
            ));
        }

        $page = $this->params()->fromQuery('page', 1);
        $query = $this->params()->fromQuery() + [
            'page' => $page,
            'sort_by' => $this->params()->fromQuery('sort_by', 'id'),
            'sort_order' => $this->params()->fromQuery('sort_order', 'desc'),
        ];
        $response = $this->api()->search('oaipmhharvester_harvests', $query);

        $this->paginator($response->getTotalResults(), $page);

        return new ViewModel([
            'harvests' => $response->getContent(),
        ]);
    }

    protected function undoJob($jobId): Job
    {
        $harvest = $this->api()->read('oaipmhharvester_harvests', ['job' => $jobId])->getContent();

        $args = ['jobId' => $jobId];
        $job = $this->jobDispatcher()->dispatch(\OaiPmhHarvester\Job\Undo::class, $args);

        $this->api()->update(
            'oaipmhharvester_harvests',
            $harvest->id(),
            [
                'o:undo_job' => ['o:id' => $job->getId() ],
            ]
        );

        return $job;
    }

    /**
     * Get data for the setsForm.
     *
     * The endpoint should be checked.
     */
    protected function dataFromEndpoint($endpoint, $harvestAllRecords, $predefinedSets): array
    {
        $harvestAllRecords = (bool) $harvestAllRecords;
        $hasPredefinedSets = !empty($predefinedSets);
        $result = [
            'repository_name' => '',
            'endpoint' => '',
            'harvest_all_records' => false,
            'predefined_sets' => $predefinedSets,
            'formats' => [],
            'favorite_format' => '',
            'sets' => [],
            'has_predefined_sets' => $hasPredefinedSets,
            'message' => null,
        ];

        if (!$endpoint) {
            $result['message'] = $this->translate('Missing endpoint.'); // @translate
            return $result;
        }

        $message = null;

        $oaiPmhRepository = $this->oaiPmhRepository($endpoint);
        $repositoryName = $oaiPmhRepository->getRepositoryName()
            ?: $this->translate('[Untitled repository]'); // @translate

        $formats = $oaiPmhRepository->listOaiPmhFormats();

        $favoriteFormat = array_intersect($oaiPmhRepository->listManagedPrefixes(), $formats);
        $favoriteFormat = reset($favoriteFormat) ?: 'oai_dc';

        // TODO Move the next checks of oai-pmh sets to the helper.

        if ($hasPredefinedSets) {
            $originalPredefinedSets = $predefinedSets;
            foreach ($predefinedSets as $setSpec => $format) {
                if (!$setSpec) {
                    unset($predefinedSets[$setSpec]);
                } elseif (!$format) {
                    $predefinedSets[$setSpec] = $favoriteFormat;
                }
            }

            if ($hasPredefinedSets && count($originalPredefinedSets) !== count($predefinedSets)) {
                $result['message'] = $this->translate('The sets you specified are not correctly formatted.'); // @translate
                $result['redirect'] = 'index';
                return $result;
            }

            // Check if all sets have a managed format.
            $checks = array_filter($formats, function ($v, $k) {
                return $v === $k;
            }, ARRAY_FILTER_USE_BOTH);
            $unmanaged = array_filter($predefinedSets, function ($v) use ($checks) {
                return !in_array($v, $checks);
            });
            if ($unmanaged) {
                $result['message'] = new Message(
                    $this->translate('The following formats are not managed: "%s".'), // @translate
                    implode('", "', $unmanaged)
                );
                return $result;
            }
        }

        if ($harvestAllRecords) {
            $total = null;
            $sets = [];
        } else {
            $setsTotals = $oaiPmhRepository->listOaiPmhSets();
            $total = $setsTotals['total'];
            $sets = $predefinedSets ?: array_slice($setsTotals['sets'], 0, $this->maxListSets, true);
        }

        // TODO Normalize sets form with fieldsets and better names.
        return [
            'repository_name' => $repositoryName,
            'endpoint' => $endpoint,
            'harvest_all_records' => $harvestAllRecords,
            'predefined_sets' => $predefinedSets,
            'formats' => $formats,
            'favorite_format' => $favoriteFormat,
            'sets' => $sets,
            'has_predefined_sets' => $hasPredefinedSets,
            'total' => $total,
            'message' => $message,
        ];
    }
}
