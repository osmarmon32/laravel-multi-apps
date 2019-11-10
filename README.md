# laravel-multi-apps
Run multple laravel applications using the same instance
Installation:
1.- Copy this content to packages/reddireccion/multiapps (create the folder structure if does not exists)
2.- Add the namespace to the composer file
	autoload
		psr-4
			"Reddireccion\\MultiApps\\":"packages/reddireccion/multiapps"
3.- Add the service provider Reddireccion\MultiApps\MultiAppServiceProvider::class to your config/app.php



vendor\laravel\framework\src\Illuminate\Auth\Notifications\ResetPassword.php
	app.url
vendor\laravel\framework\src\Illuminate\Auth\Passwords\PasswordBrokerManager.php
	app.key
vendor\laravel\framework\src\Illuminate\Database\DatabaseServiceProvider.php
	app.faker_locale
vendor\laravel\framework\src\Illuminate\Encryption\EncryptionServiceProvider.php
	app.key
vendor\laravel\framework\src\Illuminate\Foundation\Application.php
	app.providers
	app.locale
	basepath
vendor\laravel\framework\src\Illuminate\Foundation\Bootstrap\LoadConfiguration.php
	app.env
	app.timezone
	app config file
vendor\laravel\framework\src\Illuminate\Foundation\Bootstrap\RegisterFacades.php
	app.aliases
vendor\laravel\framework\src\Illuminate\Foundation\Bootstrap\SetRequestForConsole.php
	app.url
vendor\laravel\framework\src\Illuminate\Foundation\Console\KeyGenerateCommand.php
	app.key
	app.cipher
vendor\laravel\framework\src\Illuminate\Foundation\Console\stubs\markdown.stub
	app.name
vendor\laravel\framework\src\Illuminate\Foundation\Exceptions\Handler.php
	app.debug
vendor\laravel\framework\src\Illuminate\Foundation\Exceptions\WhoopsHandler.php
	app.debug_blacklist
	app.editor
vendor\laravel\framework\src\Illuminate\Foundation\helpers.php
	app.debug
vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php
	app.url
vendor\laravel\framework\src\Illuminate\Log\LogManager.php
	app.name
vendor\laravel\framework\src\Illuminate\Mail\resources\views\html\message.blade.php
	app.name
	app.url
vendor\laravel\framework\src\Illuminate\Mail\resources\views\markdown\message.blade.php
	app.name
vendor\laravel\framework\src\Illuminate\Notifications\resources\views\email.blade.php
	app.name
vendor\laravel\framework\src\Illuminate\Routing\RoutingServiceProvider.php
	app.key
vendor\laravel\framework\src\Illuminate\Translation\TranslationServiceProvider.php
	app.fallback_locale
vendor\laravel\passport\resources\views\authorize.blade.php
	app.name
vendor\laravel\passport\src\Console\ClientCommand.php
	app.name
vendor\laravel\passport\src\Console\InstallCommand.php
	app.name
vendor\laravel\passport\src\Http\Controllers\HandlesOAuthErrors.php
	app.debug