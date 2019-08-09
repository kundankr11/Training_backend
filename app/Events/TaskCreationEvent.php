<?php

namespace App\Events;

use App\Vmuser;
use App\task;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;

class TaskCreationEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $notification_title;
    public $notification_description;
    public $task;


    /**
     * Create a new event instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct(task $task)
    {
       
        $this->notification_title = "A new Task has been by"." ".$task->assigner;
        $this->notification_description = $task->taskDes;
        $this->task = $task;

    }

    public function broadcastOn()
    {
        return ['login1.'.$this->task->assignee];
    }
    public function broadcastAs()
{
    return 'TaskCreated';
}
}