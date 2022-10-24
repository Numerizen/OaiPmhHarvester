<?php declare(strict_types=1);

namespace OaiPmhHarvester\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Omeka\Form\Element as OmekaElement;

class SetsForm extends Form
{
    public function __construct($name = null, $options = [])
    {
        is_array($name)
            ? parent::__construct($name['name'] ?? null, $name)
            : parent::__construct($name, $options);
    }

    public function init(): void
    {
        $this
            ->setAttribute('id', 'harvest-list-sets')

            ->add([
                'type' => Element\Hidden::class,
                'name' => 'repository_name',
                'attributes' => [
                    'id' => 'repository_name',
                ],
            ])
            ->add([
                'type' => Element\Hidden::class,
                'name' => 'endpoint',
                'attributes' => [
                    'id' => 'endpoint',
                ],
            ])
            ->add([
                'type' => Element\Hidden::class,
                'name' => 'harvest_all_records',
                'attributes' => [
                    'id' => 'harvest_all_records',
                ],
            ])
            ->add([
                'type' => Element\Hidden::class,
                'name' => 'predefined_sets',
                'attributes' => [
                    'id' => 'predefined_sets',
                ],
            ])

            ->add([
                'name' => 'filters_whitelist',
                'type' => OmekaElement\ArrayTextarea::class,
                'options' => [
                    'label' => 'Filters (whitelist)', // @translate
                    'info' => 'Add strings to filter the input, for example to import only some articles of a journal.', // @translate
                ],
                'attributes' => [
                    'id' => 'filters_whitelist',
                ],
            ])
            ->add([
                'name' => 'filters_blacklist',
                'type' => OmekaElement\ArrayTextarea::class,
                'options' => [
                    'label' => 'Filters (blacklist)', // @translate
                    'info' => 'Add strings to filter the input, for example to import only some articles of a journal.', // @translate
                ],
                'attributes' => [
                    'id' => 'filters_blacklist',
                ],
            ])

            ->add([
                'type' => Element\Hidden::class,
                'name' => 'step',
                'attributes' => [
                    'id' => 'step',
                    'value' => 'harvest-list-sets',
                ],
            ])

            ->appendSets()

            ->appendSetsInputFilter();
    }

    /**
     * This form is dynamic, so allows to append elements.
     */
    public function appendSets(
        ?bool $harvestAllRecords = null,
        ?array $formats = null,
        ?string $favoriteFormat = null,
        ?array $sets = null,
        ?bool $hasPredefinedSets = null
    ): self {
        $harvestAllRecords = $harvestAllRecords ?? $this->getOption('harvest_all_records', false);
        $formats = $formats ?? $this->getOption('formats', ['oai_dc']);
        $favoriteFormat = $favoriteFormat ?? $this->getOption('favorite_format', 'oai_dc');
        $sets = $sets ?? $this->getOption('sets', []);
        $hasPredefinedSets = $hasPredefinedSets ?? $this->getOption('has_predefined_sets', []);

        // TODO Normalize sets form with collection, fieldsets and better names.

        // The predefined sets are already formatted, but have no label.
        if ($hasPredefinedSets) {
            foreach ($sets as $setSpec => $prefix) {
                $this
                    ->add([
                        'type' => Element\Select::class,
                        'name' => 'namespace[' . $setSpec . ']',
                        'options' => [
                            'label' => $setSpec,
                            'value_options' => $formats,
                        ],
                        'attributes' => [
                            'id' => 'namespace[' . $setSpec . ']',
                            'value' => $prefix,
                        ],
                    ])
                    ->add([
                        'type' => Element\Hidden::class,
                        'name' => 'setSpec[' . $setSpec . ']',
                        'attributes' => [
                            'id' => 'setSpec' . $setSpec,
                            'value' => $setSpec,
                        ],
                    ])
                    ->add([
                        'type' => Element\Checkbox::class,
                        'name' => 'harvest[' . $setSpec . ']',
                        'options' => [
                            'label' => 'Harvest this set?', // @translate
                            'use_hidden_element' => false,
                        ],
                        'attributes' => [
                            'id' => 'harvest[' . $setSpec . ']',
                            'value' => true,
                            'checked' => 'checked',
                        ],
                    ]);
            }
        } elseif ($sets && !$harvestAllRecords) {
            foreach ($sets as $setSpec => $set) {
                $this
                    ->add([
                        'type' => Element\Select::class,
                        'name' => 'namespace[' . $setSpec . ']',
                        'options' => [
                            'label' => strip_tags($set) . " ($setSpec)",
                            'value_options' => $formats,
                        ],
                        'attributes' => [
                            'id' => 'namespace-' . $setSpec,
                            'value' => $favoriteFormat,
                        ],
                    ])
                    ->add([
                        'type' => Element\Hidden::class,
                        'name' => 'setSpec[' . $setSpec . ']',
                        'attributes' => [
                            'id' => 'setSpec-' . $setSpec,
                            'value' => strip_tags($set),
                        ],
                    ])
                    ->add([
                        'type' => Element\Checkbox::class,
                        'name' => 'harvest[' . $setSpec . ']',
                        'options' => [
                            'label' => 'Harvest this set', // @translate
                            'use_hidden_element' => false,
                        ],
                        'attributes' => [
                            'id' => 'harvest-' . $setSpec,
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
                        'id' => 'namespace-0',
                        'value' => $favoriteFormat,
                    ],
                ])
            ;
        }

        return $this;
    }

    public function appendSetsInputFilter(): self
    {
        $inputFilters = $this->getInputFilter();

        foreach ($this->getElements() as $element) {
            $elementName = $element->getName();
            if (strpos($elementName, 'namespace[') === 0
                || strpos($elementName, 'setSpec[') === 0
                || strpos($elementName, 'harvest[') === 0
            ) {
                $inputFilters
                    ->add([
                        'name' => $elementName,
                        'required' => false,
                    ]);
            }
        }

        return $this;
    }
}
