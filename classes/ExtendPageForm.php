<?php

namespace AspenDigital\LookFeel\Classes;

use BackendAuth;
use Backend\Widgets\Form;
use Cms\Classes\Layout;
use RainLab\Pages\Classes\Page;
use Request;

class ExtendPageForm
{
    /** @var string */
    protected $defaultLayout;
    
    /** @var array */
    protected $layouts = [];
    
    /** @var Form */
    protected $form;
    
    /** @var Page */
    protected $page;
    
    /**
     * Called on extendFieldsBefore event
     * @param Form $form
     */
    public function handle($form)
    {
        // Only process page form and only if the form is set to render all page fields
        //   (AJAX and save with e.g. repeater fields call these events but don't set up tabs)
        $controller = $form->getController();
        if (! $controller instanceof \RainLab\Pages\Controllers\Index || empty($form->tabs['fields']) || ! $form->model instanceof Page) {
            return;
        }
        
        $this->form = $form;
        $this->page = $form->model;
        
        $this->loadLayouts();
        
        // If no layout is set for the page, it needs to be set now before the local event fires and 
        //  form fields are added based on the page layout
        $this->checkDefault();

        $form->bindEvent('form.extendFields', [$this, 'handleLocalAfterEvent']);
        $this->moveVariablesToSecondary();
    }
    
    /**
     * Move variable fields to secondary tabs
     */
    public function moveVariablesToSecondary()
    {
        foreach ($this->form->tabs['fields'] as $key=>$field) {
            if (array_key_exists('X_OCTOBER_IS_VARIABLE', $field) || (array_key_exists('type', $field) && $field['type'] === 'richeditor')) {
                unset($this->form->tabs['fields'][$key]);
                $field['cssClass'] = 'moved-to-secondary' . (empty($field['cssClass']) ? '' : ' ' . $field['cssClass']);
                $this->form->secondaryTabs['fields'][$key] = $field;
            }
        }
    }
    
    /**
     * Field objects exist on the form at this point
     */
    public function handleLocalAfterEvent()
    {
        // Hide markup field if necessary.  If field is removed rather than hidden, issues occur
        //  if a layout with no markup is on the initial form load but the layout is then
        //  switched to one with markup
        $layoutName = $this->page->getViewBag()->property('layout');
        if ($layoutName && $this->layouts[$layoutName]->hideContentField) {
            $tab = $this->form->getTab('secondary');
            $field = $this->form->getField('markup');
            $field->hidden = true;
            
            // For styling purposes, remove and re-add the markup's tab pane so that it's at the end of the list.
            //  It's a bit of a hack to deal with the front-end counting on the first pane not being hidden.
            $temp = $tab->fields[$field->tab];
            unset($tab->fields[$field->tab]);
            $tab->fields[$field->tab] = $temp;
        }
        
        $this->removeHiddenLayouts();
    }
    
    /**
     * Load layouts and determine default
     */
    protected function loadLayouts()
    {
        foreach (Layout::listInTheme($this->page->theme) as $layout) {
            if ($layout->default) {
                $this->defaultLayout = $layout->getBaseFileName();
            }
            $this->layouts[ $layout->getBaseFileName() ] = $layout;
        }
    }
    
    /**
     * If no layout has been selected (page creation), inherit from a parent page if it exists,
     *   falling back to a global default if not
     */
    protected function checkDefault()
    {
        if (!$this->page->getViewBag()->property('layout')) {
            // TODO: Accessing the request is a hack, but we have no other access to the parent page
            //   selected for a new sub-page, and the layout has to be set now so that fields for
            //   any layout variables can be added.
            $parent = Request::input('parent') ? Page::load($this->page->theme, Request::input('parent')) : null;

            if ($parent) {
                $layoutName = $parent->getViewBag()->property('layout');
                
                // Check if the parent page defines a layout for its sub-pages
                if (!empty($this->layouts[$layoutName]->childLayout)) {
                    $layoutName = $this->layouts[$layoutName]->childLayout;
                }
            }
            else {
                $layoutName = $this->defaultLayout;
            }

            // Use fill() as there are actually two copies of the viewBag the Page maintains
            $this->page->fill([
                'settings'=>[
                    'viewBag'=>[
                        'layout' => $layoutName
                    ]
                ]
            ]);
        }
    }
    
    /**
     * Remove layouts from the options list if necessary
     */
    protected function removeHiddenLayouts()
    {
        if (BackendAuth::getUser()->hasAccess('aspendigital.lookfeel.see_hidden_elements')) {
            return;
        }
        
        $hiddenKeys = array();
        foreach ($this->layouts as $name=>$layout) {
            if ($layout->hidden) {
                $hiddenKeys[$name] = 1;
            }
        }
        
        // If a page is already set to a hidden layout, it should not be removed from the list
        $layoutName = $this->page->getViewBag()->property('layout');
        if ($layoutName) {
            unset($hiddenKeys[$layoutName]);
        }
        
        $field = $this->form->getField('viewBag[layout]');
        $field->options( array_diff_key($field->options(), $hiddenKeys) );
    }
}
