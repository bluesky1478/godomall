<?php

namespace Bundle\Controller\Admin\Service;


class GlobalTranslationController extends \Controller\Admin\Controller
{
    /**
     * index
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('service', 'overseas', 'translate');
    }
}
