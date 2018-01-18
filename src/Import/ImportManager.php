<?php

namespace Spqr\Wordpressimport\Import;

use Pagekit\Application as App;
use Pagekit\Blog\Model\Post;
use Pagekit\Blog\Model\Comment;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class ImportManager
 *
 * @package Spqr\Wordpressimport\Import
 */
class ImportManager
{
    /**
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * @var
     */
    protected $posts;
    
    /**
     * @var
     */
    protected $attachments;
    
    /**
     * @var array
     */
    protected $file = [];
    
    /**
     * @var string
     */
    protected $filepath = '';
    
    
    /**
     * Constructor.
     *
     * @param mixed $output
     */
    public function __construct($output = null)
    {
        $this->output      = $output
            ? : new StreamOutput(fopen('php://output', 'w'));
        $this->posts       = new PostManager;
        $this->attachments = new AttachmentManager;
    }
    
    /**
     * @param  array $file
     *
     * @return bool
     */
    public function import(array $file)
    {
        $this->file = $file;
        
        $this->prepare();
        $this->run();
        $this->clean();
        
        return true;
    }
    
    /**
     * @return bool
     */
    private function prepare()
    {
        $this->output->writeln(__('Preparing.'));
        
        if (!App::module('blog')) {
            throw new \RuntimeException(__('Blog extension is not installed.'));
        }
        
        if (!extension_loaded('simplexml')) {
            throw new \RuntimeException(__('Simplexml is not installed.'));
        }
        
        if (!is_array($this->file) || empty($this->file)) {
            throw new \RuntimeException(__('No file given.'));
        }
        
        $this->filepath = $this->file['path'].DIRECTORY_SEPARATOR
            .$this->file['filename'];
        
        if (!App::file()->exists($this->filepath)) {
            throw new \RuntimeException(__('File can not be found.'));
        }
        
        return true;
    }
    
    /**
     * @return bool
     */
    private function run()
    {
        $this->output->writeln(__('Importing.'));
        
        $xml = simplexml_load_file($this->filepath);
        
        if (!empty($wp_attachments = $this->attachments->getFromXML($xml))) {
            foreach ($wp_attachments as $wp_attachment) {
                $this->output->writeln(__('Importing attachment %id%.',
                    ['%id%' => $wp_attachment->id]));
            }
        }
        
        if (!empty($wp_posts = $this->posts->getFromXML($xml))) {
            foreach ($wp_posts as $wp_post) {
                $this->output->writeln(__('Importing blog post %id%.',
                    ['%id%' => $wp_post->id]));
                
                $post = Post::create([
                    'user_id'        => App::user()->id,
                    'title'          => $wp_post->title,
                    'slug'           => App::filter($wp_post->title, 'slugify'),
                    'status'         => $wp_post->status,
                    'date'           => $wp_post->date,
                    'content'        => $this->posts->replaceImages($wp_post->content,
                        $wp_attachments),
                    'excerpt'        => $this->posts->replaceImages($wp_post->excerpt,
                        $wp_attachments),
                    'comment_status' => $wp_post->comments_enabled,
                ]);
                
                $post->save(); // Todo: Is this necessary?
                $post->set('title',
                    App::module('blog')->config('posts.show_title'));
                $post->set('markdown', false);
                
                if (!empty($wp_post->thumbnail)
                    && is_int($wp_post->thumbnail)
                ) {
                    $thumb = $this->attachments->get($wp_post->thumbnail);
                    $post->set('image', [
                        'src' => $thumb->path,
                        'alt' => $thumb->title,
                    ]);
                    
                }
                
                $post->save();
                
                if ($wp_post->comments_enabled && !empty($wp_post->comments)
                    && is_array($wp_post->comments)
                ) {
                    $this->createComments($post->id, $wp_post->comments);
                }
            }
        }
        
        return true;
    }
    
    /**
     * @param       $post_id
     * @param array $comments
     * @param int   $depth
     * @param int   $parent_id
     */
    private function createComments(
        $post_id,
        array $comments,
        $depth = 0,
        $parent_id = 0
    ) {
        foreach ($comments as $key => $val) {
            
            $comment = Comment::create([
                'post_id'   => $post_id,
                'parent_id' => $parent_id,
                'user_id'   => 0,
                'author'    => $val->author,
                'email'     => $val->email,
                'url'       => $val->url,
                'ip'        => $val->ip,
                'content'   => $val->content,
                'created'   => $val->created,
                'status'    => $val->status,
            ]);
            
            $comment->save();
            
            if (is_array($val->children) && !empty($val->children)) {
                $this->createComments($post_id, $val->children, ($depth + 1),
                    $comment->id);
            }
        }
    }
    
    /**
     * @return bool
     */
    private function clean()
    {
        $this->output->writeln(__('Cleaning up.'));
        
        try {
            App::file()->delete($this->filepath);
            $this->output->writeln(__('Deleted temp file.'));
            
        } catch (\Exception $e) {
            throw new \RuntimeException(__('Unable to delete temp file.'));
        }
        
        return true;
    }
    
}