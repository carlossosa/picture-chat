<?php

namespace PictureChat\FileBundle\Thumbnail\Exception;

final class FileNotFound extends \Exception {
    public function __construct ($path) {
        parent::__construct( "Image in {$path} not found.", 10001);
    }
}