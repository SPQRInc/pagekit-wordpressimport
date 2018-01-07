<?php

namespace Spqr\Wordpressimport\Post;

/**
 * Class WordpressPost
 *
 * @package Spqr\Wordpressimport\Post
 */
class WordpressPost
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
    public $date;
    
    /**
     * @var
     */
    public $content;
    
    /**
     * @var
     */
    public $excerpt;
    
    /**
     * @var
     */
    public $comments_enabled;
    
    /**
     * @var array
     */
    public $comments = [];
    
    /**
     * @var
     */
    public $thumbnail;
    
    /**
     * @var
     */
    public $status;
    
    /**
     * @param array $post
     *
     * @return $this
     */
    public function create(array $post)
    {
        $this->setValues($post);
        
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