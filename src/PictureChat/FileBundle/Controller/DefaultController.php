<?php

namespace PictureChat\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use PictureChat\FileBundle\Entity\File;
use PictureChat\FileBundle\Form\FileType;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/upload", name="picturechat_fileupload")
     * @Method({"POST"})
     * @Template()
     */
    public function uploadAction(Request  $r)
    {
        $data = new File();
        $data->setUser($this->getUser());
        
        $form = $this->createForm(new FileType(), $data);
        
        $form->submit($r);
        
        if ( $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($data);
            $this->getDoctrine()->getManager()->flush();
        }
        
        // create bse thumbs
        $sizes = array(
            'xxs' => array(90,'auto'),
            'xs' => array(160,'auto'),
            's' => array(200,'auto'),
            'm' => array(500,'auto'),
            'l' => array(640,'auto'),            
        ); 
        
        $path = "gaufrette://file_upload_fs/".$data->getFileName();
                    
        \PictureChat\FileBundle\Thumbnail\Thumbnails::createThumb( $path, $path . "_s", $sizes['s'][0], $sizes['s'][1]);      
        \PictureChat\FileBundle\Thumbnail\Thumbnails::createThumb( $path, $path . "_m", $sizes['m'][0], $sizes['m'][1]);      
        
        //return $this->redirect($this->generateUrl("picturechat_home"));
        return new Response();
    }
    
    /**
     * @Route("/t/{size}/{filename}", name="picturechat_thumbnail")
     * @Route("/t/{size}/{filename}/down", name="picturechat_thumbnail_download")
     * @Method({"GET"})
     * @Template()
     * @Cache(expires="next month")
     */
    public function thumbnailAction($filename,$size, Request $r) {
        $sizes = array(
            'xxs' => array(90,'auto'),
            'xs' => array(160,'auto'),
            's' => array(200,'auto'),
            'm' => array(500,'auto'),
            'l' => array(640,'auto'),            
        );                
        
        if ( !array_key_exists($size, $sizes))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();

        $file = $this->getDoctrine()->getRepository('PictureChatFileBundle:File')->findOneBy(array('fileName' => $filename));                               
        
        if ( $file) {
            $path = "gaufrette://file_upload_fs/".$file->getFileName();
            $path_thumb = "gaufrette://file_upload_fs/".$file->getFileName()."_".$size;
            
            if ( !file_exists($path_thumb)) {
                \PictureChat\FileBundle\Thumbnail\Thumbnails::createThumb( $path, $path_thumb, $sizes[$size][0], $sizes[$size][1]);
            }
            
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
        
        // Generate response
        $response = new Response();

        // Set headers
        $response->setMaxAge(2419200);
        $response->setLastModified($file->getDate());        
        $expire = clone $file->getDate();
        $expire->add(\DateInterval::createFromDateString("next month"));
        $response->setExpires($expire);
        
        
        $response->headers->set('Content-type', $file->getFileName());
        
        if ( $r->get('_route') == "picturechat_thumbnail_download")
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $size . "_" . basename($file->getFileName()) . '";');
        else 
            $response->headers->set('Content-Disposition', 'filename="' . basename($file->getFileName()) . '";');

        if ($response->isNotModified($r)) {
            return $response;
        }
        
        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(
                readfile($path_thumb)
                );
        
        return $response;
    }       
    
    /**
     * @Route("/remove", name="picturechat_file_delete")
     * @Template()
     */
    public function removeAction (Request $r) {
        try {
            $file = $this->getDoctrine()->getRepository('PictureChatFileBundle:File')->find($r->get("id"));
            
            $filename = $file->getFileName();
            
            $this->getDoctrine()->getManager()->remove($file);
            $this->getDoctrine()->getManager()->flush();
            
            $exts = array( "", "_xxs", "_xs", "_s", "_m", "_l");
            $base_path = "gaufrette://file_upload_fs/".$filename;
            foreach ( $exts as $ext ) {
                if (file_exists($base_path.$ext)) {
                    unlink($base_path.$ext);
                }
            }
            
            return new Response("OK");
            
        } catch (\Exception $ex) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
    }
}
