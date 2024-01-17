<?php

namespace App\Traits;

use App\Models\Comment\Comment;

/**
 * Add this trait to your User model so
 * that you can retrieve the comments for a user.
 */
trait Commenter {
    /**
     * Returns all comments that this user has made.
     */
    public function comments() {
        return $this->morphMany(Comment::class, 'commenter');
    }

    /**
     * Returns only approved comments that this user has made.
     */
    public function approvedComments() {
        return $this->morphMany(Comment::class, 'commenter')->where('approved', true);
    }
}
