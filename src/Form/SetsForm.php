<?php declare(strict_types=1);
namespace OaiPmhHarvester\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;

class SetsForm extends Form
{
    public function init(): void
    {
        $this->setAttribute('action', 'harvest');

        $repositoryName = $this->getOption('repository_name');
        $endpoint = $this->getOption('endpoint');
        $formats = $this->getOption('formats');
        $sets = $this->getOption('sets') ?: [];
        $harvestAllRecords = $this->getOption('harvest_all_records');
        $predefinedSets = $this->getOption('predefined_sets');
        $favoriteFormat = $this->getOption('favorite_format');

        $this
            ->add([
                'type' => Element\Hidden::class,
                'name' => 'repository_name',
                'attributes' => [
                    'id' => 'repository_name',
                    'value' => $repositoryName,
                ],
            ])
            ->add([
                'type' => Element\Hidden::class,
                'name' => 'endpoint',
                'attributes' => [
                    'id' => 'endpoint',
                    'value' => $endpoint,
                ],
            ])
            ->add([
                'type' => Element\Hidden::class,
                'name' => 'harvest_all_records',
                'attributes' => [
                    'id' => 'harvest_all_records',
                    'value' => $harvestAllRecords,
                ],
            ])
            ->add([
                'name' => 'filters_whitelist',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Filters (whitelist)', // @translate
                    'infos' => 'Add strings to filter the input, for example to import only some articles of a journal.', // @translate
                ],
                'attributes' => [
                    'id' => 'filters_whitelist',
                ],
            ])
            ->add([
                'name' => 'filters_blacklist',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Filters (blacklist)', // @translate
                    'infos' => 'Add strings to filter the input, for example to import only some articles of a journal.', // @translate
                ],
                'attributes' => [
                    'id' => 'filters_blacklist',
                ],
            ]);

        // The predefined sets are already formatted, but have no label.
        if ($predefinedSets) {
            foreach ($sets as $id => $prefix) {
                $this
                    ->add([
                        'type' => Element\Select::class,
                        'name' => 'namespace[' . $id . ']',
                        'options' => [
                            'label' => $id,
                            'value_options' => $formats,
                        ],
                        'attributes' => [
                            'id' => 'namespace[' . $id . ']',
                            'value' => $prefix,
                        ],
                    ])
                    ->add([
                        'type' => Element\Hidden::class,
                        'name' => 'setSpec[' . $id . ']',
                        'attributes' => [
                            'id' => 'setSpec' . $id,
                            'value' => $id,
                        ],
                    ])
                    ->add([
                        'type' => Element\Checkbox::class,
                        'name' => 'harvest[' . $id . ']',
                        'options' => [
                            'label' => 'Harvest this set?', // @translate
                            'use_hidden_element' => false,
                        ],
                        'attributes' => [
                            'id' => 'harvest[' . $id . ']',
                            'value' => true,
                            'checked' => 'checked',
                        ],
                    ]);
            }
        } elseif ($sets && !$harvestAllRecords) {
            foreach ($sets as $id => $set) {
                $this
                    ->add([
                        'type' => Element\Select::class,
                        'name' => 'namespace[' . $id . ']',
                        'options' => [
                            'label' => strip_tags($set) . " ($id)",
                            'value_options' => $formats,
                        ],
                        'attributes' => [
                            'id' => 'namespace[' . $id . ']',
                            'value' => $favoriteFormat,
                        ],
                    ])
                    ->add([
                        'type' => Element\Hidden::class,
                        'name' => 'setSpec[' . $id . ']',
                        'attributes' => [
                            'id' => 'setSpec' . $id,
                            'value' => strip_tags($set),
                        ],
                    ])
                    ->add([
                        'type' => Element\Checkbox::class,
                        'name' => 'harvest[' . $id . ']',
                        'options' => [
                            'label' => 'Harvest this set?', // @translate
                            'use_hidden_element' => false,
                        ],
                        'attributes' => [
                            'id' => 'harvest[' . $id . ']',
                        ],
                    ]);
            }
        } else {
            $this
                ->add([
                    'type' => Element\Select::class,
                    'name' => 'namespace[0]',
                    'options' => [
                        'label' => 'Whole repository', // @translate
                        'value_options' => $formats,
                    ],
                    'attributes' => [
                        'id' => 'namespace[0]',
                        'value' => $favoriteFormat,
                    ],
                ])
            ;
        }
    }
}
