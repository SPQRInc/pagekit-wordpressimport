<?php

namespace Spqr\Wordpressimport\Import;

use Pagekit\Application as App;
use Spqr\Wordpressimport\Attachment\WordpressAttachment;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class AttachmentManager
 *
 * @package Spqr\Wordpressimport\Import
 */
class AttachmentManager
{
    /**
     * @var array
     */
    protected $attachments = [];
    
    /**
     * @param int $id
     *
     * @return bool|mixed
     */
    public function get(int $id)
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->id === $id) {
                return $attachment;
            }
        }
        
        return false;
    }
    
    /**
     * @param $xml
     *
     * @return array
     */
    public function getFromXML($xml)
    {
        foreach ($xml->channel->item as $item) {
            $attachment = $this->normalize($item);
            if ($attachment) {
                $this->add($attachment);
            }
        }
        
        return $this->getAll();
    }
    
    /**
     * @param $item
     *
     * @return $this|bool
     */
    private function normalize($item)
    {
        $content
                   = $item->children('http://purl.org/rss/1.0/modules/content/');
        $excerpt
                   = $item->children('http://wordpress.org/export/1.2/excerpt/');
        $blog_post = $item->children('http://wordpress.org/export/1.2/');
        
        if ($blog_post->post_type == 'attachment') {
            
            $file
                = (string)$blog_post->xpath("//wp:postmeta[wp:meta_key[text() = '_wp_attached_file']]/wp:meta_value")[0];
            
            if ($file) {
                $path = $this->download($blog_post->attachment_url, $file);
            }
            
            $wp         = new WordpressAttachment;
            $attachment = $wp->create([
                'id'             => (int)$blog_post->post_id,
                'title'          => (string)$item->title,
                'attachment_url' => (string)$blog_post->attachment_url,
                'path'           => ($path) ? (string)$path : null,
            ]);
            
            return $attachment;
            
            
        } else {
            return false;
        }
    }
    
    /**
     * @param $url
     * @param $path
     *
     * @return bool|string
     */
    private function download($url, $path)
    {
        $filename    = pathinfo($path, PATHINFO_FILENAME).".".pathinfo($path,
                PATHINFO_EXTENSION);
        $storage_dir = App::get('path.storage');
        $target      = $storage_dir.DIRECTORY_SEPARATOR.$filename;
        
        for ($i = 1; App::file()->exists($target); $i++) {
            $filename = pathinfo($path, PATHINFO_FILENAME)."_$i."
                .pathinfo($path, PATHINFO_EXTENSION);
            $target   = $storage_dir.DIRECTORY_SEPARATOR.$filename;
        }
        
        try {
            $fp = fopen($target, 'w');
            if ($fp) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                $result = parse_url($url);
                
                curl_setopt($ch, CURLOPT_REFERER,
                    $result['scheme'].'://'.$result['host']);
                curl_setopt($ch, CURLOPT_USERAGENT,
                    'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0');
                $raw       = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code != 200) {
                    App::file()->delete($target);
                    
                    return false;
                }
                
                if ($raw) {
                    fwrite($fp, $raw);
                }
                
                fclose($fp);
                
                if (!$raw) {
                    App::file()->delete($target);
                    
                    return false;
                }
                
                $path = ltrim(App::file()->getUrl($target), '/');
                
                return $path;
            }
        } catch (\Exception $e) {
            App::message()->error($e->getMessage());
            
            return false;
        }
        
        return false;
    }
    
    /**
     * @param $attachment
     */
    public function add($attachment)
    {
        $this->attachments[] = $attachment;
    }
    
    /**
     * @return array
     */
    public function getAll()
    {
        return $this->attachments;
    }
    
}