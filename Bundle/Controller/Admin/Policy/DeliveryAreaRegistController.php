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

use Component\Member\Manager;
use Framework\Debug\Exception\AlertBackException;
use Session;
use Request;
use Exception;

/**
 * 배송 정책 설정 관리 페이지
 * [관리자 모드] 배송 정책 설정 관리 페이지
 *
 * @package Bundle\Controller\Admin\Provider\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryAreaRegistController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function index()
    {
        // --- 배송 정책 설정 데이터
        try {
            // --- 모듈 호출
            $delivery = \App::load('\\Component\\Delivery\\Delivery');

            if (Request::get()->has('sno')) {
                // --- 메뉴 설정
                $this->callMenu('policy', 'delivery', 'area_modify');

                $data = $delivery->getSnoDeliveryAreaGroup(Request::get()->get('sno'));
                $areaData = $delivery->getSnoDeliveryArea(Request::get()->get('sno'));

                // 공용인경우 해당 추가배송비정책사용하는 배송정책이있는지 확인
                if ($data['scmNo'] == '0') {
                    $useCount = $delivery->checkAreaDeliveryInBasicDelivery(Request::get()->get('sno'));
                } else {
                    $useCount = 0;
                }
                $editFl = 'T';
            } else {
                // --- 메뉴 설정
                $this->callMenu('policy', 'delivery', 'area_regist');

                $useCount = 0;
                $editFl = 'F';
                $data['count'] = $delivery->getCountAreaGroupDelivery();
            }

            // 공급사로 로그인시 자신의 배송비 조건이 아닌 경우 튕기기
            if (Manager::isProvider() && Session::get('manager.scmNo') != $data['scmNo'] && empty($data['basic']['scmNo']) === false) {
                throw new AlertBackException(__('자신의 공급사 배송비조건만 수정하실 수 있습니다.'));
            }

            //--- 주소 설정
            $searchSidoArr = [
                '' => '시/도 선택',
            ];
            $godo = \App::load('\\Component\\Godo\\GodoCenterServerApi');
            $sido = json_decode($godo->getCurlDataAddDelivery('newAreaSido'), 1);
            if (gd_isset($sido['godojuso']['data'])) {
                foreach ($sido['godojuso']['data']['item'] as $val) {
                    if($val['sido_code'] == 36) continue; // 세종특별자치시는 패스
                    $key = $val['sido_code'].'|'.$val['sido_name'];
                    $searchSidoArr[$key] = $val['sido_name'];
                }
            }

            //검색설정
            $sortList = [
                'addArea asc'   => sprintf(__('주소지%s'),'↓' ),
                'addArea desc'  => sprintf(__('주소지%s'),'↑' ),
                'addRegDt asc'        => sprintf(__('등록일%s'),'↓' ),
                'addRegDt desc'       => sprintf(__('등록일%s'),'↑' ),
                'addPice asc'   => sprintf(__('추가배송비%s'),'↓' ),
                'addPrice desc' => sprintf(__('등록일%s'),'↑' ),
            ];

            gd_isset($data['defaultFl'], 'n');
            gd_isset($data['scmFl'], ($data['scmNo'] > 1 ? '1' : '0'));
            $checked['defaultFl'][$data['defaultFl']] =
            $checked['scmFl'][$data['scmFl']] = 'checked="checked"';
            if ($data['scmNo'] == '0') {
                $checked['scmUseFl'][1] = 'checked="checked"';
            } else {
                $checked['scmUseFl'][0] = 'checked="checked"';
            }

            if (Request::get()->get('popupMode') == true) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            } else {
                $this->getView()->setDefine('layout', 'layout_basic.php');
            }

            $this->setData('popupMode', Request::get()->get('popupMode'));
            $this->setData('searchSidoArr', $searchSidoArr);
            $this->setData('sortList', $sortList);
            $this->setData('data', gd_htmlspecialchars($data));
            $this->setData('useCount', $useCount);
            $this->setData('editFl', $editFl);
            $this->setData('checked', $checked);
            $this->setData('areaData', $areaData);
            $this->setData('isAjax', Request::get()->has('iframe'));

        } catch (Exception $e) {
            throw $e;
        }
    }
}
