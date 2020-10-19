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

namespace Bundle\Controller\Admin\Marketing;

use Component\PlusShop\PlusReview\PlusReviewConfig;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\GodoUtils;
use Globals;

class PaycoConfigController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 네이버 쇼핑 설정
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        //--- 메뉴 설정
        $this->callMenu('marketing','paycoShopping','config');

        //--- 페이지 데이터
        try {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $data = $dbUrl->getConfig('payco', 'config');

            gd_isset($data['paycoFl'], 'n');

            $checked = array();
            $checked['paycoFl'][$data['paycoFl']] = 'checked="checked"';
        }
        catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->setData('data',gd_isset($data));
        $this->setData('checked',gd_isset($checked));
        $this->setData('godo',(Globals::get('gLicense')));

        if(gd_policy('basic.info')['mallDomain']) $this->setData('mallDomain',"http://".gd_policy('basic.info')['mallDomain']."/");
        else $this->setData('mallDomain',URI_HOME);

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);


    }
}
