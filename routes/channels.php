<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-login1.5', function () {
    return true;
});