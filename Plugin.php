<?php namespace AspenDigital\LookFeel;

use App;
use Event;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /** @var \Backend\Classes\Controller */
    protected $controller;

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'          => 'Aspen Digital Look & Feel',
            'description'   => 'Customizations to October CMS behavior',
            'author'        => 'Aspen Digital',
            'homepage'      => 'http://www.aspendigital.com'
        ];
    }

    public function boot()
    {
        if (!App::runningInBackend()) {
            return;
        }

        // Overrides specifically for Pages plugin
        if (class_exists('RainLab\Pages\Classes\Page')) {
            Event::listen('backend.form.extendFieldsBefore', function($widget) {
                $event = new Classes\ExtendPageForm();
                $event->handle($widget);
            });

            $handler = new Classes\ExtendPageModel();
            $handler->boot();
        }
    }

    /**
     * Returns any back-end Form Widgets implemented in this plugin
     * @return array
     */
    public function registerFormWidgets()
    {
        return [
            'AspenDigital\LookFeel\FormWidgets\MultiConditional' => 'multiconditional'
        ];
    }
    
    public function registerPermissions()
    {
        return [
            'aspendigital.lookfeel.see_hidden_elements' => [
                'label' => 'See hidden elements in lists',
                'tab' => 'Look & Feel'
            ]
        ];
    }
}
