<?php

namespace Spqr\Wordpressimport\Attachment;

/**
 * Class WordpressAttachment
 *
 * @package Spqr\Wordpressimport\Attachment
 */
class WordpressAttachment
{
    /**
     * @var
     */
    public $id;
    
    /**
     * @var string
     */
    public $title = '';
    
    /**
     * @var
     */
    public $attachment_url;
    
    /**
     * @var
     */
    public $path;
    
    /**
     * @param array $attachment
     *
     * @return $this
     */
    public function create(array $attachment)
    {
        $this->setValues($attachment);
        
        return $this;
    }
    
    /**
     * @param array $values
     */
    private function setValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this->setValue($name, $value);
        }
    }
    
    /**
     * @param $name
     * @param $value
     */
    public function setValue($name, $value)
    {
        if (!property_exists($this, $name)
            && !isset
            ($this->$name)
        ) {
            return;
        }
        
        $this->$name = $value;
    }
    
}