<?php

namespace Kommercio\Models\Interfaces;

use Kommercio\Models\User;

interface StoreManagedInterface{
    public function checkStorePermissionByUser(User $user);
}