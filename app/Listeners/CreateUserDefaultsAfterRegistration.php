<?php

namespace App\Listeners;

use App\Services\UserSetupService;
use Illuminate\Auth\Events\Registered;

class CreateUserDefaultsAfterRegistration
{
    public function __construct(
        protected UserSetupService $userSetupService
    ) {}

    public function handle(Registered $event): void
    {
        $this->userSetupService->createDefaults($event->user);
    }
}