<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$oldThemeName = 'uncode';
$newThemeName = 'growbig';
$folderPrefix = 'wp-themes';
$childSuffix = '-child';

$dirs = array_filter(glob('*') , 'is_dir');
$newDirectories = [];

function expandDirectories($base_dir)
{
    $directories = array();
    foreach (scandir($base_dir) as $file)
    {
        if ($file == '.' || $file == '..') continue;
        $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($dir))
        {
            $directories[] = $dir;
            $directories = array_merge($directories, expandDirectories($dir));
        }
    }
    return $directories;
}

function dir_is_empty($dirname)
{
    if (!is_dir($dirname)) return false;
    foreach (scandir($dirname) as $file)
    {
        if (!in_array($file, array(
            '.',
            '..',
            '.svn',
            '.git'
        ))) return false;
    }
    return true;
}

function changeFolderName($newFolderName, $oldFolderName, $oldPath, $folderPrefix, $oldThemeName, $newThemeName)
{

    echo 'Oude mapnaam: ' . $oldFolderName . '<br>';
    echo 'Nieuwe mapnaam: ' . $newFolderName . '<br>';

    $newFolderPath = str_replace($oldFolderName, $newFolderName, $oldPath);
    if (rename($folderPrefix . '/' . $oldFolderName, $folderPrefix . '/' . $newFolderName))
    {
        $newDirectories[] = $newFolderPath;
        return true;
    }
    else
    {
        return false;
    }
}

function changeFileName($oldFileName, $directory, $oldThemeName, $newThemeName)
{

    $directory = $directory . '/';
    $newFileName = str_replace($oldThemeName, $newThemeName, $oldFileName);


    echo '______________________' . '<br>';
    echo 'Oude naam van bestand: ' . $oldFileName . '<br>';
    echo 'Nieuwe naam van bestand: ' . $newFileName . '<br>';
    echo 'Huidige map: ' . $directory . '<br>';
    echo 'Oud pad: ' . $directory . $oldFileName .'<br>';
    echo 'Nieuwe pad: ' . $directory . $newFileName . '<br>';
    echo '______________________' . '<br>';


    if (!rename($directory . $oldFileName, $directory . $newFileName))
    {
        echo "Fail<br>";
    }
    else
    {
        echo 'Success<br>';
        
    }

}

function changeFileContent($filePath, $findStr, $replaceStr)
{


    if (!file_put_contents($filePath, str_replace($findStr, $replaceStr, file_get_contents($filePath))))
    {
        return false;
    }
    else
    {
        return true;
    }
}

function getDirectoryFiles($path, $unsupportedFiles)
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
 
    $files = array();
    $newFiles = array();

    foreach ($rii as $file)
    {
        if (!$file->isDir())
        {
            array_push($newFiles, $file);
        }
    }
    foreach ($unsupportedFiles as $fileExtension)
    {
        foreach ($rii as $file)
        {
            if (!$file->isDir())
            {

                if (strpos($file->getFilename() , $fileExtension))
                {
                    if (!in_array($file, $files, true))
                    {
                        array_push($files, $file);
                    }
                }
            }
        }
    }
    return array_diff($newFiles, $files);
}

function getSubdirectories($path)
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
 
    $files = array();
    $newFiles = array();

    foreach ($rii as $file)
    {
        if (!$file->isDir())
        {
            array_push($newFiles, $file);
        }
    }  


    return $newFiles;
}

//  ** GrowbigNameChanger **
//  Voor elke map in de lijst met alle mappen
// 1. Bekijken of de map bestanden heeft -> anders direct naam aanpassen
// 2. Bestanden in map ophalen
// 3. Als de map bestanden heeft worden hiervan eerst alle contents vervangen
// Kijken of de contents de specifieke string bevatten
// 4. Hierna worden alle namen van de bestanden vervangen door de namen van het nieuwe thema
// 5. Hierna word voor elk bestand de bestandnaam vervangen als hij de oude string bevat


echo "<b>Wordt uitgevoerd in:</b> " . dirname(__FILE__) . "<br>";
echo "<b>Oude naam:</b> " . $oldThemeName . "<br>";
echo "<b>Nieuwe naam:</b> " . $newThemeName . "<br>";

// Kijken of -child directory bestaat, anders aanmaken
if (in_array(dirname(__FILE__) . '/' . $folderPrefix . '/' . $oldThemeName . $childSuffix, expandDirectories(dirname(__FILE__))))
{
    echo '<br> Het child thema van ' . $oldThemeName . $childSuffix . ' bestaat en wordt veranderd naar: <b>' . $newThemeName . $childSuffix . '</b><br>';
    changeFolderName($newThemeName . $childSuffix, $oldThemeName . $childSuffix, dirname(__FILE__) . '/' . $folderPrefix . '/' . $oldThemeName . $childSuffix, $folderPrefix, $oldThemeName, $newThemeName);
}else{

    echo 'Child theme komt niet voor!';
}

if (in_array(dirname(__FILE__) . '/' . $folderPrefix . '/' . $oldThemeName, expandDirectories(dirname(__FILE__))))
{
    echo '<br> Het thema van ' . $oldThemeName . ' bestaat en wordt veranderd naar: <b>' . $newThemeName . '</b><br>';
    changeFolderName($newThemeName, $oldThemeName, dirname(__FILE__) . '/' . $folderPrefix . '/' . $oldThemeName, $folderPrefix, $oldThemeName, $newThemeName);
}else{

    echo 'Parent theme komt niet voor';
}

$unsupportedArray = array(
    'gif',
    'png',
    'jpg'
);

$parentFiles = getDirectoryFiles('/Users/thomasbijl/Desktop/GrowbigNameChanger/wp-themes/growbig', $unsupportedArray);

$childFiles = getDirectoryFiles('/Users/thomasbijl/Desktop/GrowbigNameChanger/wp-themes/growbig-child', $unsupportedArray);


$childDirectories = getSubdirectories('/Users/thomasbijl/Desktop/GrowbigNameChanger/wp-themes/growbig-child/');
$parentDirectories = getSubdirectories('/Users/thomasbijl/Desktop/GrowbigNameChanger/wp-themes/growbig/');

?>
<br>
<b>Padnamen (Bestanden die gewijzigd kunnen worden):</b>
<pre>
    <?php
foreach ($parentFiles as $childFile)
{

    if (strpos(file_get_contents($childFile->getPath() . '/' . $childFile->getFilename()) , $oldThemeName) !== false)
    {
       
        if (changeFileContent($childFile->getPath() . '/' . $childFile->getFilename() , $oldThemeName, $newThemeName))
        {
            echo 'is aangepast';
        }
        
    }

    if (strpos(file_get_contents($childFile->getPath() . '/' . $childFile->getFilename()) , ucfirst($oldThemeName)) !== false)
    {
       
        if (changeFileContent($childFile->getPath() . '/' . $childFile->getFilename() , ucfirst($oldThemeName), ucfirst($newThemeName)))
        {
            echo 'is aangepast';
        }
        
    }

    if (strpos(file_get_contents($childFile->getPath() . '/' . $childFile->getFilename()) , strtoupper($oldThemeName)) !== false)
    {
       
        if (changeFileContent($childFile->getPath() . '/' . $childFile->getFilename() , strtoupper($oldThemeName), strtoupper($newThemeName)))
        {
            echo 'is aangepast';
        }
        
    }

}

foreach ($childFiles as $childFile)
{

    // Lowercase
    if (strpos(file_get_contents($childFile->getPath() . '/' . $childFile->getFilename()) , $oldThemeName) !== false)
    {
        echo 'Bestand: ' . $childFile->getFilename() . ' kan bewerkt worden! <br>';
        if (changeFileContent($childFile->getPath() . '/' . $childFile->getFilename() , $oldThemeName, $newThemeName))
        {
            echo 'is aangepast';
        }
        
    }

    // Sentencecase
    if (strpos(file_get_contents($childFile->getPath() . '/' . $childFile->getFilename()) , ucfirst($oldThemeName)) !== false)
    {
        echo 'Bestand: ' . $childFile->getFilename() . ' kan bewerkt worden! <br>';
        if (changeFileContent($childFile->getPath() . '/' . $childFile->getFilename() , ucfirst($oldThemeName), ucfirst($newThemeName)))
        {
            echo 'is aangepast';
        }
        
    }
// UPPERCASE
    if (strpos(file_get_contents($childFile->getPath() . '/' . $childFile->getFilename()) , strtoupper($oldThemeName)) !== false)
    {
        echo 'Bestand: ' . $childFile->getFilename() . ' kan bewerkt worden! <br>';
        if (changeFileContent($childFile->getPath() . '/' . $childFile->getFilename() , strtoupper($oldThemeName), strtoupper($newThemeName)))
        {
            echo 'is aangepast';
        }
        
    }

}

?>



<?php 

// foreach($childDirectories as $childDirectory){
//     print_r($childDirectory);
// }


foreach($parentDirectories as $childDirectory){
    echo 'Bestandnaam: ' . $childDirectory->getFileName() . ' <br>';
    echo 'Naam die vervangen wordt: ' . $oldThemeName . ' <br>';
    echo 'Is een match: ' . strpos($childDirectory->getFileName(), $oldThemeName) . '<br>';
     
     if(strpos($childDirectory->getFileName(), $oldThemeName) === 0){
        
        echo  $childDirectory->getPathname(). '<br>';
        // function changeFileName($oldFileName, $directory, $oldThemeName, $newThemeName)
           changeFileName($childDirectory->getFilename(),$childDirectory->getPath(),$oldThemeName,$newThemeName);
    }else{
        //echo  $childDirectory->getPathname(). '<br>';
    }


   
}
?>
</pre>
