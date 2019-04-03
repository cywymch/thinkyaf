<?php
use Yaf\Controller_Abstract;
use Cache\Cache;
/**
 * 
 * @author chen
 *
 */
class IndexController extends Controller_Abstract
{
    public function indexAction()
    {
        $obj = new Cache;
        $obj(33);
         $data = $this->getRequest()->getEnv();
         dump($data);
       echo 111;
       die();
    }
}