<?php

namespace PictureChat\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use PictureChat\FileBundle\Form\FileType;
use PictureChat\FileBundle\Entity\File;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="picturechat_home")
     * @Template()
     */
    public function indexAction( Request $request)
    {
        
        $data = new File();
        $data->setUser($this->getUser());
        
        $form = $this->createForm(new FileType(), $data, array(
            'action' => $this->generateUrl('picturechat_fileupload'),
            'method' => 'POST',
        ));
        
        
        $files = $this->getDoctrine()->getManager()->getRepository('PictureChatFileBundle:File')->findBy( array(), array('id'=>'DESC')/*, 5*/);
        
        if ( $files)
            $request->getSession()->set('file_last_id', $files[0]->getId());
        
        return array('form' => $form->createView(), 'files' => $files);
    }
    
    /**
     * @Route("/new_files.json", name="picturechat_new_files")
     * @Template()
     */
    public function newfilesAction(Request $r){
        $files = $this->getDoctrine()->getRepository('PictureChatFileBundle:File')->filesChangedBy(
              $r->getSession()->get( 'file_last_id', 0)
          );
        
        if ( $files)
            $r->getSession()->set('file_last_id', $files[0]->getId());
        
        $array_to_json = array();
        
        foreach ( $files as $file )
            $array_to_json[] = $file->getId();
        
        return new \Symfony\Component\HttpFoundation\JsonResponse(
                    $array_to_json
                );
    }
    
    /**
     * @Route("/index.js", name="picturechat_home_js")
     * @Template("PictureChatHomeBundle:Default:index.js.twig")
     * @Cache(expires="next week")
     */
    public function indexjsAction(Request $r){
        return new \Symfony\Component\HttpFoundation\Response($this->renderView('PictureChatHomeBundle:Default:index.js.twig'), 200, array('Content-Type'=>'text/javascript'));
    }
    
    /**
     * @Route("/index.css", name="picturechat_home_css")
     * @Template("PictureChatHomeBundle:Default:index.css.twig")
     * @Cache(expires="next week")
     */
    public function indexcssAction(Request $r){
        return new \Symfony\Component\HttpFoundation\Response($this->renderView('PictureChatHomeBundle:Default:index.css.twig'), 200, array('Content-Type'=>'text/css'));
    }
    
    /**
     * @Route("/thumbblock/{id}_template.html", name="picturechat_thumbblock")
     * @Template()
     * @Cache(expires="next month")
     */
    public function thumbblockAction ($id) {
        $file = $this->getDoctrine()->getRepository('PictureChatFileBundle:File')->find($id);
        
        if ( $file) {
            return array('file' => $file);
        } else {
            return new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
    }
    
    /**
     * @Route("/thumbblocks.html", name="picturechat_thumbblocks")
     * @Template()
     */
    public function thumbblocksAction (Request $r) {
            return array('ids' => $r->get('ids'));
    }
}
