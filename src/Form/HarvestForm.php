<?php
namespace OaiPmhHarvester\Form;

use Zend\Form\Form;

class HarvestForm extends Form
{

    public function init()
    {
        $this->setAttribute('action', 'oaipmhharvester/sets');
/*
        $this->add([
                'name' => 'csv',
                'type' => 'file',
                'options' => [
                    'label' => 'CSV file', // @translate
                    'info' => 'The CSV file to upload', //@translate
                ],
                'attributes' => [
                    'id' => 'csv',
                    'required' => 'true',
                ],
        ]);
*/

        $this->add([
            'name' => 'base_url',
            'type' => 'text',
            'options' => [
                'label' => 'Base URL', // @translate
                'info' => 'The base URL of the OAI-PMH data provider.', //@translate
            ],
            'attributes' => [
                'id' => 'base_url',
                'required' => 'true',
                'value' => 'http://localhost/bacasable/oai-pmh-repository/request',
                'size' => 60,
            ],                      
        ]);
        
/*
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
*/
        

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'base_url',
            'required' => true,
        ]);
    }

}
