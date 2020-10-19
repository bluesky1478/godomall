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

namespace Bundle\Controller\Admin\Design;

use Globals;

class LayerPageurlcopyController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 페이지주소복사
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */


//--- 모듈 호출


//--- 페이지리스팅
        $selectDir = gd_isset($_GET['selectDir'], 'goods');
        $arrMenus = array('goods' => __('상품'), 'order' => __('주문'), 'member' => __('회원'), 'mypage' => __('마이페이지'), 'board' => __('게시판'), 'event' => __('이벤트'), 'service' => __('고객서비스'), 'share' => __('공통'));

// skinAdmin 정의
        $skinAdmin = \App::load('\\Component\\Skin\\SkinAdmin');
        $skinAdmin->setSkin(Globals::get('gSkin.frontSkinWork'));

// 페이지주소
        $q_divs = array($selectDir);
        array_push($q_divs, '*');
        $data = (array)$skinAdmin->getContainer($q_divs);
        if ($selectDir == 'board') {
            $q_divs = array($selectDir);
            array_push($q_divs, '*');
            array_push($q_divs, '*');
            $data = array_merge($data, (array)$skinAdmin->getContainer($q_divs));
        }
        foreach ($data as $k => $v) {
            $data[$k]['link'] = '..' . $skinAdmin->getLinkPage($v['cno']);
        }

//--- 관리자 디자인 템플릿


        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('selectDir', $selectDir);
        $this->setData('arrMenus', $arrMenus);
        $this->setData('data', $data);


    }
}
