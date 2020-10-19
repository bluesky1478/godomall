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
namespace Bundle\Controller\Admin\Goods;

use Globals;

/**
 * 사은품 증정 정책 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class GiftConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'gift', 'config');

        // --- 사은품 증정 정책 config 불러오기
        $data = gd_policy('goods.gift');

        // --- 기본값 설정
        if (empty($data['giftFl'])) {
            $data['giftFl'] = 'n';
        }

        $checked = [];
        $checked['giftFl'][$data['giftFl']] = 'checked="checked"';

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
    }
}
