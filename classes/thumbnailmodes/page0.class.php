<?php
namespace dfm;
class page0 extends generator {
    const title = "First page";
    const dependecies = "thumbnail_creator";
    function createThumbnail($thumbnailCreator, $fileToUpdate) {
        $files =  $thumbnailCreator->createImages($fileToUpdate, array(0));
        return isset($files[0]) ? $files[0] : '';
    }
}