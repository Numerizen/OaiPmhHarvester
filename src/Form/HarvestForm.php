<?php
namespace OaiPmhHarvester\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class HarvestForm extends Form
{
    public function init()
    {
        $this->setAttribute('action', 'oaipmhharvester/sets');

        $this
            ->add([
                'name' => 'endpoint',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'OAI-PMH endpoint', // @translate
                    'info' => 'The base URL of the OAI-PMH data provider.', // @translate
                ],
                'attributes' => [
                    'id' => 'endpoint',
                    'required' => true,
                    // The protocol requires http, but most of repositories
                    // support it.
                    'placeholder' => 'https://example.org/oai-pmh-repository/request',
                ],
            ])
            ->add([
                'name' => 'harvest_all_records',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Skip listing of sets and harvest all records', // @translate
                ],
                'attributes' => [
                    'id' => 'harvest_all_records',
                ],
            ])
        ;

        $inputFilter = $this->getInputFilter();
        $inputFilter
            ->add([
                'name' => 'endpoint',
                'required' => true,
            ]);
    }
}
