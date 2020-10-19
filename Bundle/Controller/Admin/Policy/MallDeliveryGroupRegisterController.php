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

namespace Bundle\Controller\Admin\Policy;


use Component\Delivery\OverseasDelivery;
use Component\Mall\MallDAO;
use Component\Policy\DesignSkinPolicy;
use Framework\Utility\UrlUtils;
use Request;

/**
 * Class MallConfigController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MallDeliveryGroupRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 관리자 메뉴 설정
        $this->callMenu('policy', 'multiMall', 'mallDeliveryGroupRegist');

        // 주소용 국가코드 셀렉트 박스 데이터
        $countryAddress = [];
        $countryAddress[''] = '- ' . __('선택') . ' -';
        foreach (MallDAO::getInstance()->selectCountries() as $key => $val) {
            $countryAddress[$val['code']] = $val['countryNameKor'] . '(' . $val['countryName'] . ')';
        }
        $this->setData('countryAddress', $countryAddress);

        // 해외배송 콤포넌트 호출
        $overseasDelivery = new OverseasDelivery();

        // 배송그룹내 국가 리스트
        if (Request::get()->has('sno')) {
            $data = $overseasDelivery->getCoutryGroupView(Request::get()->get('sno'));
            $this->setData('data', reset($data['group']));
        }

        $this->setData('adminList', UrlUtils::getAdminListUrl());
    }
}
