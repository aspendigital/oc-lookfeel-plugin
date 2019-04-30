<?php

namespace AspenDigital\LookFeel\FormWidgets;

use Backend\Classes\FormWidgetBase;

class MultiConditional extends FormWidgetBase
{
    protected $defaultAlias = 'multiconditional';

    public function init()
    {
        $this->formField->cssClass .= ' hide';
    }

    public function render()
    {
        if ($this->controller->methodExists('formGetWidget')) {
            $form = $this->controller->formGetWidget();
        }
        else { // Settings form controller does not use FormController behavior
            $form = $this->controller->widget->form;
        }
        
        $config = $this->formField->getConfig('multiConditional');
        
        if (empty($config['sources'])) {
            $config['sources'] = [];
        }
        
        array_walk($config['sources'], function(&$source) use($form) {
            $source['field'] = $form->getField($source['field'])->getName();
        });
        
        return $this->makePartial('multiconditional', ['field'=>$this->formField, 'config' => $config]);
    }
    
    public function loadAssets()
    {
        $this->addJs('js/multiconditional.js', '1'); // Manual cache busting
    }
}