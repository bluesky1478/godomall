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

namespace Bundle\Component\Delivery;

use Component\Database\DBTableField;
use Framework\Debug\Exception\Except;
use Component\Validator\Validator;
use Exception;
use Request;
use Globals;
use Session;

/**
 * 장바구니내 배송비 계산
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DeliveryCart extends \Component\Delivery\Delivery
{
    protected $db;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 장바구니에서 기본 체크할 배송정책 기본 정보
     *
     * @param array $arrSno 상품 배송정보 sno
     * @return array 배송정책 정보
     */
    public function getDataDeliveryWithGoodsNo($arrSno)
    {
        if (empty($arrSno) === true || ! is_array($arrSno)) {
            return false;
        }

        $arrBind = [];
        foreach ($arrSno as $deliverySno) {
            $param[] = '?';
            $this->db->bind_param_push($arrBind, 'i', $deliverySno);
        }

        if (empty($param) === true) {
            return false;
        }

        $arrInclude = [
            'scmNo',
            'method',
            'collectFl',
            'fixFl',
            'freeFl',
            'pricePlusStandard',
            'priceMinusStandard',
            'goodsDeliveryFl',
            'areaFl',
            'areaGroupNo',
            'scmCommissionDelivery',
            'taxFreeFl',
            'taxPercent',
            'rangeLimitFl',
            'rangeLimitWeight',
            'rangeRepeat',
            'addGoodsCountInclude',
            'deliveryMethodFl',
            'deliveryVisitPayFl',
            'deliveryConfigType',
            'dmVisitAddressUseFl',
            'sameGoodsDeliveryFl',
        ];
        $arrField = DBTableField::setTableField('tableScmDeliveryBasic', $arrInclude, null, 'sdb');
        $strSQL = 'SELECT sdb.sno, ' . implode(', ', $arrField) . ', sm.scmCommissionDelivery FROM ' . DB_SCM_DELIVERY_BASIC . ' AS sdb LEFT JOIN ' . DB_SCM_MANAGE . ' AS sm ON sdb.scmNo = sm.scmNo WHERE sdb.sno IN (' . implode(' , ', $param) . ') ORDER BY sdb.sno ASC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = [];
        $tmpFreeScmNo = [];
        foreach ($getData as $key => $val) {
            foreach ($arrInclude as $fVal) {
                if (in_array($fVal, ['pricePlusStandard', 'priceMinusStandard'])) {
                    $setData[$val['sno']][$fVal] = explode(STR_DIVISION, $val[$fVal]);
                } else {
                    // 무료 배송인 경우에만 해당 공급사 무료 체크 활성화
                    if ($fVal === 'freeFl') {
                        if ($val['fixFl'] === 'free') {
                            $setData[$val['sno']][$fVal] = $val[$fVal];
                            // 해당 공급사 무료 인경우 체크를 해서
                            if ($val[$fVal] === 'y') {
                                $tmpFreeScmNo[] = $setData[$val['sno']]['scmNo'];
                            }
                        } else {
                            $setData[$val['sno']][$fVal] = '';
                        }
                    } else {
                        $setData[$val['sno']][$fVal] = $val[$fVal];
                    }
                }
            }
            $setData[$val['sno']]['charge'] = $this->_getSnoDeliveryCharge($val['sno'], $val['deliveryConfigType']);
            $setData[$val['sno']]['areaGroupList'] = $this->_getAreaDeliveryData($setData[$val['sno']]['areaGroupNo']);
        }

        if (empty($tmpFreeScmNo) === false) {
            foreach ($setData as $key => $val){
                if (in_array($val['scmNo'], $tmpFreeScmNo)) {
                    $setData[$key]['wholeFreeFl'] = 'y';
                }
            }
        }

        return $setData;
    }

    /**
     * 배송정책별 배송비 설정내역
     *
     * @param int $basicKey 배송정책 Key
     * @return array 배송비 설정내역
     */
    private function _getSnoDeliveryCharge($basicKey, $deliveryConfigType)
    {
        if (empty($basicKey) === true) {
            return false;
        }

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $basicKey);

        $arrInclude = ['method', 'unitStart', 'unitEnd', 'price', 'message'];
        $arrField = DBTableField::setTableField('tableScmDeliveryCharge', $arrInclude);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_SCM_DELIVERY_CHARGE . ' WHERE basicKey = ? ORDER BY basicKey ASC, sno ASC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $getData = gd_htmlspecialchars_stripslashes($getData);
        if ($deliveryConfigType == 'etc') {
            $tmp = [];
            foreach ($getData as $val) {
                $tmp[$val['method']][] = $val;
            }
            $getData = $tmp;
        }

        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 지역별 그룹NO로 지역별 배송비 리스트를 반환한다.
     * 지역별 배송비 계산시 주소가 긴 부분부터 체크해야 하는 조건이 있다.
     * 지역별 배송비 리스트 중 주소가 긴 부분을 먼저 체크하고 가격을 책정하고 break 하는 부분이 있어서...
     *
     * @param $areaGroupSno
     *
     * @return mixed
     */
    private function _getAreaDeliveryData($areaGroupSno)
    {
        // 주소의 길이로 재 정렬 (긴 -> 짧)
        $staticLength = 0;
        $tmpAreaGroupLists = [];
        $areaGroupLists = $this->getSnoDeliveryArea($areaGroupSno);
        if (empty($areaGroupLists) === false) {
            foreach ($areaGroupLists as $tmpGroup) {
                if ($staticLength <= strlen($tmpGroup['addArea'])) {
                    $tmpAreaGroupLists[] = $tmpGroup;
                } else {
                    array_unshift($tmpAreaGroupLists, $tmpGroup);
                }
                $staticLength = strlen($tmpGroup['addArea']);
            }
        }

        return array_reverse($tmpAreaGroupLists);
    }
}
