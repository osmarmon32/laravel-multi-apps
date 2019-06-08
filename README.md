# laravel-multi-apps
Run multple laravel applications using the same instance
Installation:
1.- Copy this content to packages/reddireccion/multiapps (create the folder structure if does not exists)
2.- Add the namespace to the composer file
	autoload
		psr-4
			"Reddireccion\\MultiApps\\":"packages/reddireccion/multiapps"
3.- Add the service provider Reddireccion\MultiApps\MultiAppServiceProvider::class to your config/app.php