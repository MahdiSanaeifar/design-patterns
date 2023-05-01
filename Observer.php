<?php


interface ObserverSubject
{
    /* Methods */
    public function attach(ObserverObjects $observer);
    public function detach(ObserverObjects $observer);
    public function notify();
}

interface ObserverObjects
{
    /* Methods */
    public function update(ObserverSubject $subject);
}

/**
 * Class Comment
 */
class AddedComment implements ObserverSubject
{
    /**
     * Array of the observers
     *
     * @var array
     */
    protected $observers = [];
    /**
     * The comment text that was just added for our pretend blog comment
     * @var string
     */
    public $comment_text;
    /**
     * The ID for the blog post that this just added blog comment relates to
     * @var int
     */
    public $post_id;
    /**
     * Comment constructor - save the $comment_text (for the recently submitted comment) and the $post_id that this blog comment relates to.
     * @param $comment_text
     * @param $post_id
     */
    public function __construct($comment_text, $post_id)
    {
        $this->comment_text = $comment_text;
        $this->post_id = $post_id;
    }
    /**
     * Add an observer (such as EmailAuthor, EmailOtherCommentators or IncrementCommentCount) to $this->observers so we can cycle through them later
     * @param ObserverObjects $observer
     * @return AddedComment
     */
    public function attach(ObserverObjects $observer)
    {
        $key = spl_object_hash($observer);
        $this->observers[$key] = $observer;
        return $this;
    }
    /**
     * Remove an observer from $this->observers
     * @param ObserverObjects $observer
     */
    public function detach(ObserverObjects $observer)
    {
        $key = spl_object_hash($observer);
        unset($this->observers[$key]);
    }
    /**
     * Go through all of the $this->observers and fire the ->update() method.
     *
     * (In Laravel and other frameworks this would often be called the ->handle() method.)
     *
     * @return mixed
     */
    public function notify()
    {
        /** @var ObserverObjects $observer */
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}


/**
 * Class EmailAuthor
 * When ->update is called it should email the author of the blog post id.
 *
 */
class EmailAuthor implements ObserverObjects
{
    public function update(ObserverSubject $subject)
    {
        echo __METHOD__ . " Emailing the author of post id: " . $subject->post_id . " that someone commented with : " . $subject->comment_text . "<br>";
    }
}
/**
 * Class EmailOtherCommentators
 * When ->update() is called it should email other comment authors who have also commented on this blog post
 */
class EmailOtherCommentators implements ObserverObjects
{
    public function update(ObserverSubject $subject)
    {
        echo __METHOD__ . " Emailing all other comment authors who commented on " . $subject->post_id . " that someone commented with : " . $subject->comment_text . "<br>";
    }
}
/**
 * Class IncrementCommentCount
 * Add 1 to the comment count column for the blog post.
 *
 * update blogposts.comment_count = comment_count + 1 where id = ?
 */
class IncrementCommentCount implements ObserverObjects
{
    public function update(ObserverSubject $subject)
    {
        echo __METHOD__ . " Updating comment count to + 1 for blog post id: " . $subject->post_id . "<br>";
    }
}


$new_comment = 'hello, world';
$blog_post_id = 123;
// create a blog post here...
echo "Created Blog Post <br>";
// you could actually save the blog post in an observer too BTW. But often in the real world, I find this won't work as well, as you need to actually send the whole BlogPostComment (or whatever object you have) to the observers and it just makes things clearer if you have already created and saved that item in the DB already.
echo "Adding observers to subject <br>";
$addedComment = new AddedComment($new_comment, $blog_post_id); // << the subject
$addedComment->attach(new IncrementCommentCount())->attach(new EmailOtherCommentators())->attach(new EmailAuthor());  // << adding the 3 observers
echo "<pre>";
print_r($addedComment);
echo "Now going to notify() them... <br>";
$addedComment->notify();
echo "Done\n";
