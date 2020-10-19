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
use Request;

/**
 * Class LayerDeliveryCountryGroupController
 *
 * @package Bundle\Controller\Admin\Policy
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerDeliveryCountriesController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     */
    public function index()
    {
        // 국가데이터 가져오기
        $countriesCode = MallDAO::getInstance()->selectCountries();

        // 주소용 국가코드 셀렉트 박스 데이터
        $countryAddress = [];
        $countryAddress[''] = '- ' . __('선택') . ' -';
        foreach ($countriesCode as $key => $val) {
            $countryAddress[$val['code']] = $val['countryNameKor'] . '(' . $val['countryName'] . ')';
        }
        $this->setData('countryAddress', $countryAddress);

        // 해외배송 콤포넌트 호출
        $overseasDelivery = new OverseasDelivery();

        // 배송그룹내 국가 리스트
        $countriesData = $overseasDelivery->getDao()->selectCountries(Request::get()->get('sno'), 'basicKey');
        $this->setData('countriesData', $countriesData);

        // 레이어 레이아웃 설정
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
