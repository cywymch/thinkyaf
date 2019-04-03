<?php
use Yaf\Controller_Abstract;
/**
 * 
 * @author chen
 *
 */
class IndexController extends Controller_Abstract
{
    public function indexAction()
    {
         $data = $this->getRequest()->getEnv();
         dump($data);
       echo 111;
       die();
    }
}