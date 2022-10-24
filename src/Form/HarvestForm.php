<?php declare(strict_types=1);

namespace OaiPmhHarvester\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Validator\Callback;
use OaiPmhHarvester\Mvc\Controller\Plugin\OaiPmhRepository;
use Omeka\Form\Element as OmekaElement;

class HarvestForm extends Form
{
    /**
     * @var \OaiPmhHarvester\Mvc\Controller\Plugin\OaiPmhRepository
     */
    protected $oaiPmhRepository;

    public function init(): void
    {
        $translator = $this->oaiPmhRepository->getTranslator();

        $this
            ->setAttribute('id', 'harvest-repository')

            ->add([
                'name' => 'endpoint',
                'type' => Element\Url::class,
                'options' => [
                    'label' => 'OAI-PMH endpoint', // @translate
                    'info' => 'The base URL of the OAI-PMH data provider.', // @translate
                ],
                'attributes' => [
                    'id' => 'endpoint',
                    'required' => true,
                    // The protocol requires http, but most of repositories
                    // support https, except Gallica and some other big
                    // institutions.
                    'placeholder' => 'https://example.org/oai-pmh-repository',
                ],
                // TODO Add a filter to remove query and fragment.
            ])
            ->add([
                'name' => 'harvest_all_records',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Skip listing of sets and harvest all records', // @translate
                ],
                'attributes' => [
                    'id' => 'harvest_all_records',
                    'required' => false,
                ],
            ])
            ->add([
                'name' => 'predefined_sets',
                'type' => OmekaElement\ArrayTextarea::class,
                'options' => [
                    'label' => 'Skip listing of sets and harvest only these sets', // @translate
                    'info' => 'Set one set identifier and a metadata prefix by line. Separate the set and the prefix by "=". If no prefix is set, "dcterms" or "oai_dc" will be used.', // @translate
                    'as_key_value' => true,
                ],
                'attributes' => [
                    'id' => 'predefined_sets',
                    'row' => 10,
                    'placeholder' => 'digital:serie-alpha = mets
humanities:serie-beta',
                ],
            ])

            ->add([
                'name' => 'filters_whitelist',
                'type' => OmekaElement\ArrayTextarea::class,
                'options' => [
                    'label' => 'Filters on each record (whitelist)', // @translate
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
                    'label' => 'Filters on each record (blacklist)', // @translate
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
                    'value' => 'harvest-repository',
                ],
            ])
        ;

        $inputFilter = $this->getInputFilter();
        $inputFilter
            ->add([
                'name' => 'endpoint',
                'required' => true,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => [$this->oaiPmhRepository, 'hasNoQueryAndNoFragment'],
                            'messages' => [
                                'callbackValue' => $translator->translate('The endpoint "%value%" should not have a query.'), // @translate
                            ],
                        ],
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => [$this->oaiPmhRepository, 'isXmlEndpoint'],
                            'messages' => [
                                'callbackValue' => $translator->translate('The endpoint "%value%" does not return xml.'), // @translate
                            ],
                        ],
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => [$this->oaiPmhRepository, 'hasOaiPmhManagedFormats'],
                            'messages' => [
                                'callbackValue' => $translator->translate('The endpoint "%value%" does not manage any format.'), // @translate
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function setOaiPmhRepository(OaiPmhRepository $oaiPmhRepository): self
    {
        $this->oaiPmhRepository = $oaiPmhRepository;
        return $this;
    }
}
