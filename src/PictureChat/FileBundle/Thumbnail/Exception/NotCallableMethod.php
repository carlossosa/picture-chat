<?php
namespace PictureChat\FileBundle\Thumbnail\Exception;
final class NotCallableMethod extends \Exception {
    public function __construct ($method){
        parent::__construct( "Method {$method} isn't callable.", 10004);
    }
}