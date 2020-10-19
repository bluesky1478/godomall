<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Promotion;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Globals;
use LogHandler;
use Request;
use Session;

/**
 * ===================================================================================
 * 2019.10.15
 *  - 바코드 기능 사용 불가 (기능 제거)
 *  - 바코드 기능 제거 작업으로 기존 레거시 보장을 위해 해당 class 및 함수 유지하되 로직 제거
 * ===================================================================================
 */
class BarcodeAdmin extends \Component\Promotion\BarcodeCoupon
{
    protected $search = [];
    protected $checekd = [];
    protected $arrWhere = [];
    protected $arrBind = [];
    protected $fieldTypes = [];

    public function __construct(array $couponNo = [])
    {
        parent::__construct($couponNo);
    }

    public function getSearchBarcode()
    {
        return [
            'search' => $this->search,
            'checked' => $this->checked
        ];
    }

    public function getSearchWhere($key)
    {
        return $this->arrWhere[$key];
    }

    public function setSearchWhere($key, $where)
    {
        $this->arrWhere[$key] = $where;
    }

    public function setSearchBarcode(array $getValue = [], string $type = 'barcode')
    {
        return $this;
    }

    public function getBarcodeList($type='list', &$page = null)
    {
        if ($type !== 'list') {
            return 0;
        } else {
            return [];
        }
    }

    public function getUsedBarcodeCouponList(bool $isTotal=false, &$page = null)
    {
        if ($isTotal === true) {
            return 0;
        } else {
            return [];
        }
    }

    public function convertCouponOptionName($option, $couponInfo=[])
    {
        $convertOptions = [
            'kind'  => [
                'online'    => '일반쿠폰',
                'offline'   => '페이퍼쿠폰'
            ],
            'useType' => [
                'product'   => '상품',
                'order'     => '주문'
            ],
            'deviceType'    => [
                'all'       => 'PC+모바일',
                'pc'        => 'PC',
                'mobile'    => '모바일'
            ],
            'saveType'      => [
                'down'      => '다운로드',
                'auto'      => '자동발급',
                'manual'    => '수동발급'
            ],
            'benefit'       => [
                'sale'      => '상품할인',
                'add'       => '마일리지 적립',
                'delivery'  => '배송비 할인'
            ],
            'benefitUnit'   => [
                'percent'   => '%',
                'fix'       => '원'
            ],
            'status'        => [
                'y'         => '발급중',
                'n'         => '발급중지'
            ]
        ];
        return $convertOptions;
    }

    public function getCouponList(string $type='list', &$page = null)
    {
        if ($type !== 'list') {
            return 0;
        } else {
            return [];
        }
    }

    public function getBarcodeCouponInfo($memberCouponNo = array(), $fields = '') {
        return [];
    }

    private function bindingArrayToString(array $params)
    {
        $tmpWhere = [];
        foreach ($params as $param) {
            if (empty($param) === true) continue;
            $tmpWhere[] = '?';
            if (gettype($param) === 'integer') {
                $this->db->bind_param_push($this->arrBind, 'i', $param);
            } else {
                $this->db->bind_param_push($this->arrBind, 's', $param);
            }
        }
        $bindParams = implode(',', $tmpWhere);
        return $bindParams;
    }

    public function getBarcodeMenuDisplay() {
        return 'n';
    }

}
