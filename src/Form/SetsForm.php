<?php
namespace OaiPmhHarvester\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class SetsForm extends Form
{
    public function init()
    {
        $this->setAttribute('action', 'harvest');

        $base_url = $this->getOption('base_url');
        $sets = $this->getOption('sets') ?: [];
        $formats = $this->getOption('formats');

        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'base_url',
            'attributes' => [
                'id' => 'base_url',
                'value' => $base_url,
            ],
        ]);

        $this->add([
            'name' => 'filters_whitelist',
            'type' => Element\Textarea::class,
            'options' => [
                'label' => 'Filters (whitelist)', // @translate
                'infos' => 'Add strings to filter the input, for example to import only some articles of a journal.', // @translate
            ],
            'attributes' => [
                'id' => 'filters_whitelist',
            ],
        ]);

        $this->add([
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

        foreach ($sets as $id => $set) {
            $this->add([
                'type' => Element\Select::class,
                'name' => 'namespace['  . $id . "]",
                'options' => [
                    'label' => strip_tags($set) . "($id)",
                    'value_options' => $formats,
                ],
            ]);
            $this->add([
                'type' => Element\Hidden::class,
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
                'type' => Element\Checkbox::class,
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
    }
}
