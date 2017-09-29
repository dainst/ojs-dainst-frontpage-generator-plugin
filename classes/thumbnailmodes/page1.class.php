<?php
namespace dfm;
class page1 extends generator {
    const title = "Second Page";
    const dependecies = "thumbnail_creator";
    function createThumbnail($thumbnailCreator, $fileToUpdate) {
        $files =  $thumbnailCreator->createImages($fileToUpdate, array(1));
        return isset($files[0]) ? $files[0] : '';
    }
}