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
        
        $files = $this->getDoctrine()->getManager()->getRepository('PictureChatFileBundle:File')->findBy( array(), array('id'=>'DESC'));
                   
        return array('form' => $form->createView(), 'files' => $files);
    }
}
