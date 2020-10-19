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
namespace Bundle\Controller\Admin\Policy;

use Component\Delivery\OverseasDelivery;
use Component\Member\Manager;
use Component\Naver\NaverPay;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Session;
use Request;
use Exception;
use Component\Category\CategoryAdmin;

/**
 * 배송 정책 설정 관리 페이지
 * [관리자 모드] 배송 정책 설정 관리 페이지
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryRegistController extends \Controller\Admin\Controller
{

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            // 해외배송 콤포넌트 호출
            $overseasDelivery = new OverseasDelivery();

            // --- 메뉴 설정
            if (Request::get()->get('sno') > 0) {
                $this->callMenu('policy', 'delivery', 'modify');
            } else {
                $this->callMenu('policy', 'delivery', 'regist');
            }

            // 배송비조건 상세내용
            $data = $delivery->getDataSnoDelivery(Request::get()->get('sno'));

            // 공급사로 로그인시 자신의 배송비 조건이 아닌 경우 튕기기
            if (Manager::isProvider() && Session::get('manager.scmNo') != $data['basic']['scmNo'] && empty($data['basic']['scmNo']) === false) {
                throw new AlertBackException(__('자신의 공급사 배송비조건만 수정하실 수 있습니다.'));
            }

            // 배송비 조건 초기화
            $data['basic']['deliveryMethodFl'] = $delivery->setDeliveryMethodData($data['basic']['deliveryMethodFl']);
            gd_isset($data['basic']['defaultFl'], 'n');
            gd_isset($data['basic']['scmFl'], ($data['basic']['scmNo'] > 1 ? '1' : '0'));
            gd_isset($data['basic']['collectFl'], 'pre');
            gd_isset($data['basic']['fixFl'], 'fixed');
            gd_isset($data['basic']['areaFl'], 'n');
            gd_isset($data['basic']['areaGroupBenefitFl'], 'n');
            gd_isset($data['basic']['unstoringFl'], 'same');
            gd_isset($data['basic']['goodsDeliveryFl'], 'y');
            gd_isset($data['basic']['returnFl'], 'same');
            gd_isset($data['basic']['rangeLimitFl'], 'n');
            if(!Request::get()->get('sno')){
                gd_isset($data['basic']['deliveryMethodFl']['delivery'], 'delivery');
            }
            gd_isset($data['basic']['dmVisitTypeFl'], 'same');
            gd_isset($data['basic']['dmVisitTypeDisplayFl'], 'n');
            gd_isset($data['basic']['rangeRepeat'], 'n');
            gd_isset($data['manage']['scmNo'], (Manager::isProvider() && !Request::get()->has('sno') ? Session::get('manager.scmNo') : $data['manage']['scmNo']));
            gd_isset($data['basic']['deliveryConfigType'], 'all');

            if (Request::get()->has('sno') === false) {
                $defaultPricePlusStandardValue = ['option', 'add', 'text'];
                gd_isset($data['basic']['pricePlusStandard'], implode(STR_DIVISION, $defaultPricePlusStandardValue));
            }

            // 지역별 배송비 리스트
            $mode['areaGroupList']['none'] = '지역별 추가배송비를 선택해주세요';
            $tempAreaGroupSelectedNo = 0;
            foreach ($data['areaList'] as $key => $val) {
                if ($val['defaultFl'] == 'y' && $tempAreaGroupSelectedNo <= $val['scmNo']) {
                    $mode['areaGroupSelected'] = $val['sno'];
                    $tempAreaGroupSelectedNo = $val['scmNo'];
                }
                $mode['areaGroupList'][$val['sno']] = $val['method'];
            }

            $mode['fix'] = [
                'fixed'  => __('고정배송비'),
                'free'   => __('배송비무료'),
                'price'  => __('금액별배송비'),
                'count'  => __('수량별배송비'),
                'weight' => __('무게별배송비'),
            ];
            $mode['price'] = ['order' => __('할인된 상품판매가의 합'), 'goods' => __('할인안된 상품판매가의 합')];
            $mode['print'] = ['above' => __('이상'), 'below' => __('이하')];

            $checked = [];
            foreach (explode(STR_DIVISION, $data['basic']['pricePlusStandard']) as $val) {
                $checked['pricePlusStandard'][$val] = 'checked="checked"';
            }
            foreach (explode(STR_DIVISION, $data['basic']['priceMinusStandard']) as $val) {
                $checked['priceMinusStandard'][$val] = 'checked="checked"';
            }

            $checked['defaultFl'][$data['basic']['defaultFl']] =
            $checked['freeFl'][$data['basic']['freeFl']] =
            $checked['goodsDeliveryFl'][$data['basic']['goodsDeliveryFl']] =
            $checked['sameGoodsDeliveryFl'][$data['basic']['sameGoodsDeliveryFl']] =
            $checked['areaFl'][$data['basic']['areaFl']] =
            $checked['areaGroupBenefitFl'][$data['basic']['areaGroupBenefitFl']] =
            $checked['collectFl'][$data['basic']['collectFl']] =
            $checked['returnFl'][$data['basic']['returnFl']] =
            $checked['unstoringFl'][$data['basic']['unstoringFl']] =
            $checked['rangeLimitFl'][$data['basic']['rangeLimitFl']] =
            $checked['scmFl'][$data['basic']['scmFl']] =
            $checked['dmVisitTypeFl'][$data['basic']['dmVisitTypeFl']] =
            $checked['dmVisitTypeDisplayFl'][$data['basic']['dmVisitTypeDisplayFl']] =
            $checked['dmVisitAddressUseFl'][$data['basic']['dmVisitAddressUseFl']] =
            $checked['rangeRepeat'][$data['basic']['rangeRepeat']] =
            $checked['addGoodsCountInclude'][$data['basic']['addGoodsCountInclude']] =
            $checked['deliveryConfigType'][$data['basic']['deliveryConfigType']] = 'checked="checked"';

            //배송 방식
            foreach($delivery->deliveryMethodList['list'] as $key => $value){
                $checked['deliveryMethodFl'][$data['basic']['deliveryMethodFl'][$value]] = 'checked="checked"';
            }
            $this->setData('deliveryMethodList', $delivery->deliveryMethodList);
            $this->setData('deliveryVisitPayFl', $delivery->deliveryVisitPayFl);
            if (in_array('visit', $data['basic']['deliveryMethodFl']) === false) {
                $disabled['deliveryVisitPayFl'] = 'disabled="disabled"';
            }
            //배송방식 기타명
            $deliveryMethodEtc = gd_policy('delivery.deliveryMethodEtc');
            $this->setData('deliveryMethodEtc', $deliveryMethodEtc['deliveryMethodEtc']);

            if (Request::get()->get('popupMode')) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            $this->setData('data', gd_htmlspecialchars($data));
            $this->setData('mode', $mode);
            $this->setData('checked', $checked);
            $this->setData('disabled', $disabled);

            // 배송비 과세 / 비과세 설정 config 불러오기
            $deliveryTaxPolicy = gd_policy('goods.tax');

            // 과세/비과세 선택여부
            $selectedDeliveryTax = $deliveryTaxPolicy['deliveryTaxPercent'];
            if (isset($data['basic']['taxPercent'])) {
                $selectedDeliveryTax = $data['basic']['taxPercent'];
            }
            $this->setData('selectedDeliveryTax', $selectedDeliveryTax);

            // 부가세율 셀렉트박스
            $deliveryTax = ArrayUtils::changeKeyValue($deliveryTaxPolicy['deliveryTax']);
            ksort($deliveryTax);

            $naverPay = new NaverPay();
            $useNaverPay = $naverPay->checkUse() ? 'y' : 'n';

            $this->setData('deliveryTax', $deliveryTax);
            $this->setData('useNaverPay', $useNaverPay);
            $this->setData('overseasName', $overseasDelivery->getDao()->getUseDeliveryCountry(Request::get()->get('sno')));

        } catch (Exception $e) {
            throw $e;
        }
    }
}
