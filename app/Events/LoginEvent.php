<?php

namespace App\Events;

use App\Vmuser;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;

class LoginEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $message;
    public $notification_title;
    public $notification_description;


    /**
     * Create a new event instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct(Vmuser $user)
    {
        $this->message  = "liked your status";
        $this->user = $user;
        $this->notification_title = "New Login";
        $this->notification_description = "You Have Logged in ----- many times";


    }

    public function broadcastOn()
    {
        
        return new PrivateChannel('login1.'.$this->user->id);
    }
    public function broadcastAs()
{
    return 'Login';
}
}