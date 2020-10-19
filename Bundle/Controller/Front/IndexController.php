<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Front;

/**
 * 사이트 접속 페이지
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
class IndexController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // main/index 파일을 호출
        // naver 정책에 의해 index 파일 무조건 해당 위치로
        $this->getView()->setPageName(gd_entryway('front'));
    }
}
