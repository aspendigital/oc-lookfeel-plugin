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

        Event::listen('backend.page.beforeDisplay', function($eventController) {
            $this->controller = $eventController;
        });

        // There seems to be no really good way to manipulate assets after the backend.page.beforeDisplay
        //   event, at which point the controller action has not yet added any assets. This is a bit of a
        //   hack to ensure this plugin's assets are parsed last to simplify overriding core functionality
        Event::listen('router.after', function($request, $response) {
          if ($this->controller)
          {
            $this->controller->flushAssets();
            $this->controller->addCss('/plugins/aspendigital/lookfeel/assets/css/custom.css');
            
            $response->setContent(str_replace('</head>', $this->controller->makeAssets().'</head>', $response->getContent()));
          }
        });
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
