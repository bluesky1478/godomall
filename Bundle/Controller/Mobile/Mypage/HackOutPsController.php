<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-03-02
 * Time: 오전 10:47
 */

namespace Bundle\Controller\Mobile\Mypage;


class HackOutPsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Mypage\HackOutPsController $front */
        $front = \App::load('\\Controller\\Front\\Mypage\\HackOutPsController');
        $front->index();

        $this->setData($front->getData());
    }
}