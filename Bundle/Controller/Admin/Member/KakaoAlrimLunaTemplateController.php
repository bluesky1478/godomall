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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Member;

use Component\Sms\Sms;
use Component\Member\KakaoAlrimLuna;
use Framework\Utility\ComponentUtils;

/**
 * 카카오 알림톡 설정
 *
 */
class KakaoAlrimLunaTemplateController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {

        $request = \App::getInstance('request');
        // --- 메뉴 설정
        $this->callMenu('member', 'kakaoAlrim', 'kakaoAlrimTemplate');

        $kakaoSetting = gd_policy('kakaoAlrimLuna.config');

        if($kakaoSetting['useFlag'] == 'y' && !empty($kakaoSetting['lunaCliendId']) && !empty($kakaoSetting['lunaClientKey'])){
            $lunaUse = 'y';
        }else{
            $lunaUse = 'n';
        }

        $this->setData('lunaUse', $lunaUse);
    }
}
