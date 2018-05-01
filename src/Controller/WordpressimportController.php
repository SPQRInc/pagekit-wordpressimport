<?php

namespace Spqr\Wordpressimport\Controller;

use Pagekit\Application as App;
use Spqr\Wordpressimport\Import\ImportManager;

/**
 * @Access(admin=true)
 * @return string
 */
class WordpressimportController
{
    /**
     * @Access("wordpressimport: manage settings")
     */
    public function settingsAction()
    {
        $module = App::module('spqr/wordpressimport');
        $config = $module->config;
        
        return [
            '$view' => [
                'title' => __('Import Settings'),
                'name'  => 'spqr/wordpressimport:views/admin/settings.php',
            ],
            '$data' => [
                'config' => App::module('spqr/wordpressimport')->config(),
            ],
        ];
    }
    
    /**
     * @Request({"config": "array"}, csrf=true)
     * @param array $config
     *
     * @return array
     */
    public function saveAction($config = [])
    {
        App::config()->set('spqr/wordpressimport', $config);
        
        return ['message' => 'success'];
    }
    
    /**
     * @Request(csrf=true)
     */
    public function uploadAction()
    {
        $upload = App::request()->files->get('file');
        $hash   = substr(sha1(App::module('system')->config('secret').rand(0,
                9999).date_format(new \DateTime(), 'd/m/Y H:i:s')), 0, 20);
        
        $filename = 'import-'.$hash.'.xml';
        $path     = App::get('path.temp').DIRECTORY_SEPARATOR.'wordpressimport';
        
        if ($upload === null || !$upload->isValid()) {
            App::abort(400, __('No file uploaded.'));
        }
        
        if (!empty($upload->guessExtension())
            && $upload->guessExtension() != 'xml'
        ) {
            App::abort(400,
                __('Please upload a valid xml file.'));
        }
        
        $upload->move($path, $filename);
        
        $file = ['path' => $path, 'filename' => $filename];
        
        return compact('file');
    }
    
    /**
     * @Request({"file": "array"}, csrf=true)
     */
    public function importAction($file)
    {
        return App::response()->stream(function () use ($file) {
            try {
                $importmanager = new ImportManager;
                $importmanager->import($file);
                
                echo "\nstatus=success";
            } catch (\Exception $e) {
                printf("%s\nstatus=error", $e->getMessage());
            }
        });
    }
    
}