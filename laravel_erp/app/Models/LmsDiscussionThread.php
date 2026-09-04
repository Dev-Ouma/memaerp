<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['thread_title', 'course_code', 'author', 'replies_count', 'last_reply_by', 'last_activity', 'status'])]
final class LmsDiscussionThread extends Model
{
    protected $table = 'lms_discussion_threads';
}
