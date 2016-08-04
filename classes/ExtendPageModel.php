<?php

namespace AspenDigital\LookFeel\Classes;

use RainLab\Pages\Classes\Page;

class ExtendPageModel
{
    public function boot()
    {
        Page::saved(array($this, 'checkSubPageURLs'));
    }
    
    /**
     * If a page's URL changes, update any sub-pages that use that URL as a prefix
     * @param Page $page
     */
    public function checkSubPageURLs($page)
    {
        $originalUrl = $page->getOriginal('viewBag.url');
        $url = $page->getViewBag()->property('url');

        if ($originalUrl === $url) {
            return;
        }

        $regex = '#^'.preg_quote(rtrim($originalUrl, '/').'/', '#').'#';
        $replaceUrl = rtrim($url, '/') . '/';
        foreach ($page->getChildren() as $subPage) {
            $properties = $subPage->getViewBag()->getProperties();
            $properties['url'] = preg_replace($regex, $replaceUrl, $properties['url']);

            $subPage->fill([
                'settings'=>[
                    'viewBag'=>$properties
                ]
            ]);

            $subPage->save();
        }
    }
}