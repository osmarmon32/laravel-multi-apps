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
    protected  $connection="";
    protected  $prefix="";
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appName = $this->argument('appName');
        //copy public folder and add entry point to index file
        $this->recurse_copy(base_path('public'),base_path('public_'.$appName));
        $newIndexPath = base_path('public_'.$appName.'/index.php');
        $content = File::get($newIndexPath);
        $idx = strpos($content,'define');
        $content = substr_replace($content, "define('MULTI_APP_NAME','".$appName."');\n", $idx,0);
        File::put($newIndexPath,$content);
        //add namespace to composer
        $composerPath = base_path('composer.json');
        $content =File::get($composerPath);
        $idx=strpos($content,'"App\\');
        $content = substr_replace($content, "\"".Str::studly($appName)."\\\\\": \"".$appName."/\",\n\t\t\t", $idx,0);
        File::put($composerPath,$content);
        //copy app folder and rename references to namespace
        $this->recurse_copy(base_path('app'),base_path($appName));
        $this->renameInDirectory(base_path($appName),$appName);
        //copy boostrap file and rename references to namespace
        $boostrapNewPath=base_path('bootstrap\\'.$appName.'.php');
        copy(base_path('bootstrap\\app.php'),$boostrapNewPath);
        $this->renameInFile($boostrapNewPath,$appName);
        //copy artisan file, define entry point and rename reference to boostrap file
        $newArtisanPath = base_path('artisan_'.$appName.'');
        copy(base_path('artisan'),$newArtisanPath);
        $content = File::get($newArtisanPath);
        $idx = strpos($content,'define');
        $content = substr_replace($content, "define('MULTI_APP_NAME','".$appName."');", $idx,0);
        $content = str_replace('app.php', $appName.'.php', $content);
        File::put($newArtisanPath,$content);
        //Copy storage folder for new app
        $this->recurse_copy(base_path('storage/app'),base_path('storage/'.$appName));
        //use entry point to reference for file system paths
        $fileSystemConfigPath = base_path('config/filesystems.php');
        $content = File::get($fileSystemConfigPath);
        $content= str_replace("storage_path('app", "storage_path(MULTI_APP_NAME.'", $content);
        $content= str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        File::put($fileSystemConfigPath,$content);
        //create new app config file and rename references to namespace and to env variables
        $newAppConfig = base_path('config/'.$appName.'.php');
        copy(base_path('config/app.php'),$newAppConfig);
        $content = File::get($newAppConfig);
        $content=str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        $content=str_replace("App\\", Str::studly($appName)."\\", $content);
        File::put($newAppConfig,$content);
        //rename references to env variables in session file.
        //TODO
        $sessionConfigFile = base_path('config/session.php');
        $content = File::get($sessionConfigFile);
        $content=str_replace("env('", "env(MULTI_APP_NAME.'_", $content);
        File::put($sessionConfigFile,$content);


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
