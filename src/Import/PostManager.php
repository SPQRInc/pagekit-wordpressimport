<?php

namespace Spqr\Wordpressimport\Import;

use Pagekit\Application as App;
use Pagekit\Blog\Model\Post;
use Pagekit\Blog\Model\Comment;
use Spqr\Wordpressimport\Post\WordpressPost;
use Spqr\Wordpressimport\Comment\WordpressComment;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Class PostManager
 *
 * @package Spqr\Wordpressimport\Import
 */
class PostManager
{
    /**
     * @var array
     */
    protected $posts = [];
    
    /**
     * @param int $id
     *
     * @return bool|mixed
     */
    public function get(int $id)
    {
        foreach ($this->posts as $post) {
            if ($post->id === $id) {
                return $post;
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
            $post = $this->normalize($item);
            if ($post) {
                $this->add($post);
            }
        }
        
        return $this->getAll();
    }
    
    /**
     * @param $item
     *
     * @return \Spqr\Wordpressimport\Post\WordpressPost|bool
     */
    private function normalize($item)
    {
        $content
                   = $item->children('http://purl.org/rss/1.0/modules/content/');
        $excerpt
                   = $item->children('http://wordpress.org/export/1.2/excerpt/');
        $blog_post = $item->children('http://wordpress.org/export/1.2/');
        
        
        if ($blog_post->post_type == 'post') {
            switch ($blog_post->status) {
                case 'publish' :
                    $status = Post::STATUS_PUBLISHED;
                    break;
                case 'pending' :
                    $status = Post::STATUS_PENDING_REVIEW;
                    break;
                case 'future' :
                    $status = Post::STATUS_PUBLISHED;
                    break;
                case 'draft' :
                    $status = Post::STATUS_DRAFT;
                    break;
                case 'auto-draft' :
                    $status = Post::STATUS_DRAFT;
                    break;
                case 'private' :
                    $status = Post::STATUS_PUBLISHED;
                    // In this case we need to add roles
                    break;
                case 'trash' :
                    $status = Post::STATUS_UNPUBLISHED;
                    break;
                case 'inherit' :
                    $status = Post::STATUS_UNPUBLISHED;
                    break;
                default :
                    $status = Post::STATUS_UNPUBLISHED;
            }
            
            switch ($blog_post->comment_status) {
                case 'open' :
                    $comments_enabled = true;
                    break;
                case 'close' :
                    $comments_enabled = false;
                    break;
                default :
                    $comments_enabled = (bool)App::module('blog')
                        ->config('posts.comments_enabled');
            }
            
            $comments = [];
            
            if ($comments_enabled && !empty($blog_post->comment)) {
                
                foreach ($blog_post->comment as $comment) {
                    if ($comment->comment_type != 'pingback'
                        || $comment->comment_type != 'trackback'
                    ) {
                        switch ($comment->comment_approved) {
                            case '1' :
                                $comment_status = Comment::STATUS_APPROVED;
                                break;
                            case '0' :
                                $comment_status = Comment::STATUS_PENDING;
                                break;
                            case 'spam' :
                                $comment_status = Comment::STATUS_SPAM;
                                break;
                            default :
                                $comment_status = Comment::STATUS_PENDING;
                        }
                        
                        $wp_c                                = new WordPressComment;
                        $comments[(int)$comment->comment_id] = $wp_c->create([
                            'id'        => (int)$comment->comment_id,
                            'parent_id' => (int)$comment->comment_parent,
                            'author'    => (string)$comment->comment_author,
                            'email'     => (string)$comment->comment_author_email,
                            'url'       => (string)$comment->comment_author_url,
                            'ip'        => (string)$comment->comment_author_IP,
                            'created'   => new \DateTime($comment->comment_date),
                            'content'   => (string)$comment->comment_content,
                            'status'    => $comment_status,
                            'children'  => [],
                        ]);
                        
                        ksort($comments);
                        $nested_comments = $this->buildTree($comments);
                    }
                }
            } else $nested_comments = [];
            
            foreach ($blog_post->postmeta as $meta) {
                if ($meta->meta_key == '_thumbnail_id') {
                    $thumbnail = (int)$meta->meta_value;
                }
            }
            
            $wp = new WordpressPost;
            
            $post = $wp->create([
                'id'               => (int)$blog_post->post_id,
                'title'            => (string)$item->title,
                'date'             => new \DateTime($blog_post->post_date),
                'content'          => nl2br((string)$content->encoded),
                'excerpt'          => nl2br((string)$excerpt->encoded),
                'status'           => (int)$status,
                'comments_enabled' => (bool)$comments_enabled,
                'comments'         => $nested_comments,
                'thumbnail'        => (!empty($thumbnail)) ? (int)$thumbnail
                    : null,
            ]);
            
            return $post;
            
        } else {
            return false;
        }
    }
    
    /**
     * @param array $elements
     * @param int   $parentId
     *
     * @return array
     */
    private function buildTree(array $elements, $parentId = 0)
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[$element->id] = $element;
            }
        }
        
        return $branch;
    }
    
    /**
     * @param \Spqr\Wordpressimport\Post\WordpressPost $post
     */
    public function add(WordpressPost $post)
    {
        $this->posts[] = $post;
    }
    
    /**
     * @return array
     */
    public function getAll()
    {
        return $this->posts;
    }
    
    /**
     * @param $content
     * @param $attachments
     *
     * @return bool|mixed|string
     */
    public function replaceImages($content, $attachments)
    {
        
        if (!empty($content)) {
            $dom = HtmlDomParser::str_get_html($content, true, true,
                DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT,
                DEFAULT_SPAN_TEXT);
            
            $attachment_url = array_column($attachments, 'attachment_url');
            
            foreach ($dom->find('img') as $image) {
                $key = array_search($image->attr['src'], $attachment_url);
                
                if ($key !== false) {
                    $image->attr['src'] = $attachments[$key]->path;
                }
            }
            
            return $dom->save();
        }
        
        return false;
    }
    
}