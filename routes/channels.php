<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

// Channel publik untuk siaran langsung pembaruan klasemen
Broadcast::channel('standings', function () {
    return true;
});
