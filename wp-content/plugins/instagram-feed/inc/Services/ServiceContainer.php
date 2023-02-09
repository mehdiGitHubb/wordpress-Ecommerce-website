<?php

namespace InstagramFeed\Services;

use Smashballoon\Stubs\Services\ServiceProvider;

class ServiceContainer extends ServiceProvider {
	public function register() {
		( new ShortcodeService() )->register();
	}
}