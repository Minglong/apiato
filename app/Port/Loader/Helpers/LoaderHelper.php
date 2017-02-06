<?php

namespace App\Port\Loader\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * Class LoaderHelper
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class LoaderHelper
{

    /**
     * Get the containers namespace value from the containers config file
     *
     * @return  string
     */
    public function getContainersNamespace()
    {
        return Config::get('hello.containers.namespace');
    }

    /**
     * Get the containers names
     *
     * @return  array
     */
    public function getContainersNames()
    {
        $containersNames = [];

        foreach ($this->getContainersPaths() as $containersPath) {
            $containersNames[] = basename($containersPath);
        }

        return $containersNames;
    }

    /**
     * Get the port folders names
     *
     * @return  array
     */
    public function getPortFoldersNames()
    {
        $portFoldersNames = [];

        foreach ($this->getPortPaths() as $portFoldersPath) {
            $portFoldersNames[] = basename($portFoldersPath);
        }

        return $portFoldersNames;
    }

    /**
     * get containers directories paths
     *
     * @return  mixed
     */
    public function getContainersPaths()
    {
        $containersPaths = File::directories(app_path('Containers'));

        // TODO: preparing some code to implement the Ships concept to Porto

//        // Search for Ships "_SHIP" of Containers
//        foreach ($containersPaths as $containersPath) {
//
//            if (preg_match('/_SHIP/',$containersPath)){
//
//                // append the Ship directories to the Containers directory, since it will contain containers
//                $shipContainers = File::directories($containersPath);
//                $containersPaths = array_merge($containersPaths, $shipContainers);
//            }
//        }

        return $containersPaths;
    }

    /**
     * @return  mixed
     */
    public function getPortPaths()
    {
        return File::directories(app_path('Port'));
    }

    /**
     * build and return an object of a class from its file path
     *
     * @param $filePathName
     *
     * @return  mixed
     */
    public function getClassObjectFromFile($filePathName)
    {
        $classString = $this->getClassFullNameFromFile($filePathName);

        $object = new $classString;

        return $object;
    }

    /**
     * get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class"
     *
     * @param $filePathName
     *
     * @return  string
     */
    public function getClassFullNameFromFile($filePathName)
    {
        return $this->getClassNamespaceFromFile($filePathName) . '\\' . $this->getClassNameFromFile($filePathName);
    }

    /**
     * get the class namespace form file path using token
     *
     * @param $filePathName
     *
     * @return  null|string
     */
    protected function getClassNamespaceFromFile($filePathName)
    {
        $src = file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        } else {
            return $namespace;
        }
    }

    /**
     * get the class name form file path using token
     *
     * @param $filePathName
     *
     * @return  mixed
     */
    protected function getClassNameFromFile($filePathName)
    {
        $php_code = file_get_contents($filePathName);

        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes[0];
    }

}
