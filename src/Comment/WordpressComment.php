<?php

namespace Spqr\Wordpressimport\Comment;

/**
 * Class WordpressComment
 *
 * @package Spqr\Wordpressimport\Comment
 */
class WordpressComment
{
    /**
     * @var
     */
    public $id;
    
    /**
     * @var string
     */
    public $author = '';
    
    /**
     * @var string
     */
    public $email = '';
    
    /**
     * @var
     */
    public $url;
    
    /**
     * @var
     */
    public $ip;
    
    /**
     * @var
     */
    public $content;
    
    /**
     * @var
     */
    public $created;
    
    /**
     * @var
     */
    public $status;
    
    /**
     * @var
     */
    public $parent_id;
    
    /**
     * @var array
     */
    public $children = [];
    
    
    /**
     * @param array $comment
     *
     * @return $this
     */
    public function create(array $comment)
    {
        $this->setValues($comment);
        
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