<?php
namespace OaiPmhHarvester\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class HarvestForm extends Form
{
    public function init()
    {
        $this->setAttribute('action', 'oaipmhharvester/sets');

        $this->add([
            'name' => 'base_url',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Base URL', // @translate
                'info' => 'The base URL of the OAI-PMH data provider.', // @translate
            ],
            'attributes' => [
                'id' => 'base_url',
                'required' => 'true',
                'value' => 'http://localhost/bacasable/oai-pmh-repository/request',
                'size' => 60,
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'base_url',
            'required' => true,
        ]);
    }
}
