<?php
namespace PictureChat\FileBundle\Thumbnail\Exception;
final class FormatNotSupported extends \Exception {
    public function __construct (){
        parent::__construct( "Format not supported.", 10002);
    }
}