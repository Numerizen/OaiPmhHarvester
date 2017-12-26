<?php
namespace OaiPmhHarvester\Form;

use Zend\Form\Form;
use Zend\Debug\Debug;

class SetsForm extends Form
{

    public function init()
    {
        $this->setAttribute('action', 'harvest');

        $base_url = $this->getOption('base_url');
        $sets = $this->getOption('sets');
        $formats = $this->getOption('formats');
        
//        Debug::dump($sets);
        $this->add([
            'type' => 'hidden',
            'name' => 'base_url',
            'attributes' => [
                'id' => 'base_url',
                'value' => $base_url,
            ],
        ]);
          
        foreach ($sets as $id => $set) {
          $this->add([
              'type' => 'select',
              'name' => 'namespace['  . $id . "]",
              'options' => [
                  'label' => strip_tags($set) . "($id)",
                  'value_options' => $formats,
              ],
          ]);          
          $this->add([
              'type' => 'hidden',
              'name' => 'setSpec['  . $id . "]",
              'attributes' => [
                  'id' => 'setSpec'  .  $id,
                  'value' => strip_tags($set),
              ], 
              'options' => [
                  'label' => strip_tags($set),
                  'value_options' => $formats,
              ],
          ]);    
          $this->add([
              'type' => 'checkbox',
              'name' => 'harvest['  . $id . "]",
              'options' => [
                   'label' => 'Harvest this set ?',
                   'use_hidden_element' => true,
                   'checked_value' => 'yes',
                   'unchecked_value' => 'no',
               ],
               'attributes' => [
                    'value' => 'no',
               ],              
          ]);           
        }
        
/*
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
*/
        

//        $inputFilter = $this->getInputFilter();
//        $inputFilter->add([
//            'name' => 'base_url',
//            'required' => true,
//        ]);
    }

}
