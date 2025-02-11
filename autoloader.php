<?php
function autoloader($className){
    $folderName = ".";
    $filePath = searchFile($folderName, $className . ".php");
    require_once $filePath[0];
}

function searchFile($folderName, $fileName)
{
    $found = array();
    $folderName = rtrim($folderName, "/");
    $dir = opendir($folderName);

    while(($file = readdir($dir)) !== false){
        $filePath = $folderName . DIRECTORY_SEPARATOR . $file;
        if($file === '.' || $file === '..') continue;
        if(is_file($filePath)){
            if(false !== strpos($fileName, $file)) $found[] = $filePath;
        }elseif(is_dir($filePath)){
            $res = searchFile($filePath, $fileName);
            $found = array_merge($found, $res);
        }
    }

    closedir($dir);
    return $found;
}

