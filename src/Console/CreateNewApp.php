<?php

namespace Reddireccion\MultiApps\Console;

use Illuminate\Console\Command;
use Config;
use DB;
use File;
use Str;
/**
 * Creates a new namespace given an app name
 *
 * This command modify the entry points like index files and artisan command to use a constant called MULTI_APP_NAME
 * app.php file or /app path are copied to files and paths with the name of the new app 
 * and MULTI_APP_NAME is then replaced in all files that references to those files or paths
 * to allow multiple applications running and sharing under the same laravel framework code 
 *
 */
class CreateNewApp extends Command
{
    protected $connection="";
    protected $prefix="";
    protected $appName="";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:app {appName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new app namespace';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * copy public folder and add entry point to index file
     *
     * @return void
     */
    protected function copyPublicFolder(){
        $this->recurse_copy(base_path('public'),base_path('public_'.$this->appName));
        $newIndexPath = base_path('public_'.$this->appName.'/index.php');
        $content = File::get($newIndexPath);
        $idx = strpos($content,'define');
        $content = substr_replace($content, "define('MULTI_APP_NAME','".$this->appName."');\n", $idx,0);
        $content = str_replace('app.php', $this->appName.'.php', $content);
        File::put($newIndexPath,$content);
    }

    /**
     * add namespace to composer
     *
     * @return void
     */
    protected function addComposerNamespace(){
        $composerPath = base_path('composer.json');
        $content =File::get($composerPath);
        $idx=strpos($content,'"App\\');
        $content = substr_replace($content, "\"".Str::studly($this->appName)."\\\\\": \"".$this->appName."/\",\n\t\t\t", $idx,0);
        File::put($composerPath,$content);
    }

    /**
     * copy app folder and rename references to namespace
     *
     * @return void
     */
    protected function copyAppFolder(){
        $newAppPath=base_path($this->appName);
        $this->recurse_copy(base_path('app'),$newAppPath);
        $this->renameInDirectory($newAppPath,$this->appName);        
        $this->addCustomKernelBoostrappers($newAppPath,$this->appName);
    }

    /**
     * Add custom boostrappers in HTTP and Console Kernel files
     *
     * @param string $appPath path to the app folder
     * @param string $appName name of the new app to be used as namespece
     * @return void
     */
    protected function addCustomKernelBoostrappers($appPath,$appName){
        $content = File::get($appPath.'/Http/Kernel.php');
        $idx = strrpos($content,'];');
        $replace = <<<'EOT'
];
    
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Reddireccion\MultiApps\Foundation\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];
EOT;
        $content=substr_replace($content, $replace,$idx,0);
        File::put($appPath.'/Http/Kernel.php');
        $content = File::get($appPath.'/Console/Kernel.php');
        $idx = strrpos($content,'];');
        $replace = <<<'EOT'
];

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Reddireccion\MultiApps\Foundation\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];
EOT;
        $content=substr_replace($content, $replace,$idx,0);
        File::put($appPath.'/Console/Kernel.php');
    }

    /**
     * copy boostrap file and rename references to namespace
     *
     * @return void
     */
    protected function copyBoostrapFile(){
        $boostrapNewPath=base_path('bootstrap\\'.$this->appName.'.php');
        copy(base_path('bootstrap\\app.php'),$boostrapNewPath);

        $content=str_replace('App\\',Str::studly($this->appName).'\\',File::get($boostrapNewPath));
        $content=str_replace('App;',Str::studly($this->appName).';',$content);
        $content=str_replace('Illuminate\\Foundation\\Application','\\Reddireccion\\MultiApps\\Foundation\\Application',$content);
        File::put($boostrapNewPath,$content);
        $this->renameInFile($boostrapNewPath,$this->appName);

    }

    /**
     * copy artisan file, define entry point and rename reference to boostrap file
     *
     * @return void
     */
    protected function copyArtisanFile(){
        $artisanPath = base_path('artisan');
        $content = File::get($artisanPath);
        $idx = strpos($content, 'MULTI_APP_NAME');
        if($idx<0){
            $idx = strpos($content,'define');
            $content = substr_replace($content, "define('MULTI_APP_NAME','');", $idx,0);
            File::put($artisanPath,$content);
        }

        $newArtisanPath = base_path('artisan_'.$this->appName.'');
        copy(base_path('artisan'),$newArtisanPath);
        $content = File::get($newArtisanPath);
        $idx = strpos($content,'define');
        $content = str_replace("define('MULTI_APP_NAME','');", "define('MULTI_APP_NAME','".$this->appName."');", $content);
        $content = str_replace('app.php', $this->appName.'.php', $content);
        File::put($newArtisanPath,$content);        
    }

    /**
     * Copy storage folder for new app
     *
     * @return void
     */
    protected function copyStorageFolder(){
        $this->recurse_copy(base_path('storage/app'),base_path('storage/'.$this->appName));
    }

    /**
     * use entry point to reference for file system paths
     *
     * @return void
     */
    protected function updateFileSystemReferences(){
        $fileSystemConfigPath = base_path('config/filesystems.php');
        $content = File::get($fileSystemConfigPath);
        $content= str_replace("storage_path('app", "storage_path(MULTI_APP_NAME.'", $content);
        $content= str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        File::put($fileSystemConfigPath,$content);
    }

    /**
     * create new app config file and rename references to namespace and to env variables
     *
     * @return void
     */
    protected function createAppConfigFile(){
        $newAppConfig = base_path('config/'.$this->appName.'.php');
        copy(base_path('config/app.php'),$newAppConfig);
        $content = File::get($newAppConfig);
        $content=str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        $content=str_replace("App\\", Str::studly($this->appName)."\\", $content);
        File::put($newAppConfig,$content);
    }

    /**
     * rename references to env variables in session file.
     *
     * @return void
     */
    protected function updateSessionFileReferences(){
        $sessionConfigFile = base_path('config/session.php');
        $content = File::get($sessionConfigFile);
        $content=str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        File::put($sessionConfigFile,$content);
    }

    /**
     * rename references to env variables in database file.
     *
     * @return void
     */
    protected function updateDatabaseFileReferences(){
        $databaseConfigFile = base_path('config/database.php');
        $content = File::get($databaseConfigFile);
        $content=str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        File::put($databaseConfigFile,$content);
    }

    /**
     * create a subfolder in migrations with the name of the app
     *
     * @return void
     */
    protected function createMigrationsFolder(){
        File::makeDirectory(base_path('database/'.MULTI_APP_NAME));
    }



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->appName = $this->argument('appName');
        $this->copyPublicFolder();
        $this->addComposerNamespace();
        $this->copyAppFolder();
        $this->copyBoostrapFile();
        $this->copyArtisanFile();
        $this->copyStorageFolder();
        $this->updateFileSystemReferences();
        $this->createAppConfigFile();
        $this->updateSessionFileReferences();
        $this->updateDatabaseFileReferences();
        $this->createMigrationsFolder();

        $this->line('Ready.');
    }

    /**
     * Renames App namespece to the new namespace
     *
     * @param string $path path to a file
     * @param string $appName name of the new app to be used as namespece
     * @return mixed
     */
    public function renameInFile($path,$appName){
        $content=str_replace('App;',Str::studly($appName).';',str_replace('App\\',Str::studly($appName).'\\',File::get($path)));
        File::put($path,$content);
    }

    /**
     * Calls change App namespace to the new namespace recursively in all files in a given path 
     *
     * @param string $path path to a file or folder
     * @param string $appName name of the new app to be used as namespece
     * @return mixed
     */
    public function renameInDirectory($path,$appName){
        $basePath=$path.DIRECTORY_SEPARATOR;
        $dir = opendir($basePath);
        $namespace=Str::studly($appName);
        while (false!==($file=readdir($dir))){
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($basePath.$file) ) { 
                    $this->renameInDirectory($basePath.$file,$appName);
                }else{
                    $this->renameInFile($basePath.$file,$appName);
                }
            }
        }
    }

    /**
     * Copy the content of a folder to another
     *
     * @param string $src path to the source folder
     * @param string $dst path to the destination folder
     * @return mixed
     */
    public function recurse_copy($src,$dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 
}
