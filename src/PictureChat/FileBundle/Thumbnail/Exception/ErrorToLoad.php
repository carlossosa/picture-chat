<?php
namespace PictureChat\FileBundle\Thumbnail\Exception;
final class ErrorToLoad extends \Exception {
    public function __construct ($path){
        parent::__construct( "Error to load {$path}.", 10003);
    }
}