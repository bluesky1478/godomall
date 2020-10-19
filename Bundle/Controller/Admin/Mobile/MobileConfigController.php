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

namespace Bundle\Controller\Admin\Mobile;

use Exception;
use Request;

/**
 * 모바일샵 기본 설정 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MobileConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        //--- 메뉴 설정
        $this->callMenu('mobile', 'basic', 'config');

        //--- 모바일샵 기본 설정 config 불러오기
        $data = gd_policy('mobile.config');
        try {
            //--- 기본값 설정
            gd_isset($data['mobileShopFl'], 'n');
            gd_isset($data['mobileShopGoodsFl'], 'same');
            gd_isset($data['mobileShopCategoryFl'], 'same');
            gd_isset($data['mobileShopIcon'], '/data/commonimg/' . MOBILE_SHOP_ICON);

            $checked = [];
            $checked['mobileShopFl'][$data['mobileShopFl']] = $checked['mobileShopGoodsFl'][$data['mobileShopGoodsFl']] = $checked['mobileShopCategoryFl'][$data['mobileShopCategoryFl']] = 'checked="checked"';

            // 모바일샵 미리보기 체크
            $browserCheck = Request::isModernBrowser();
        } catch (Exception $e) {
            echo ($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('browserCheck', $browserCheck);
        $this->setData('checked', $checked);
    }
}
