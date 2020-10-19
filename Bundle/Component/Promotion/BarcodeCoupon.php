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

use Component\Coupon\Coupon;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Exception;
use Session;

/**
 * ===================================================================================
 * 2019.10.15
 *  - 바코드 기능 사용 불가 (기능 제거)
 *  - 바코드 기능 제거 작업으로 기존 레거시 보장을 위해 해당 class 및 함수 유지하되 로직 제거
 * ===================================================================================
 */
class BarcodeCoupon extends Barcode
{
    const ERR_MSG_REQUIRE       = '필수 항목이 누락되었습니다.',
        ERR_MSG_USED_COUPON     = '이미 사용된 쿠폰입니다.',
        ERR_MSG_INVALID_COUPON  = '사용할 수 없는 쿠폰입니다.',
        ERR_MSG_LEFT_TIME       = '쿠폰 사용 기간을 확인해주세요.',
        ERR_MSG_DUPL_BARCODE    = '이미 생성된 바코드입니다.',
        ERR_MSG_EMPTY_GENERATE  = '바코드를 발급할 쿠폰이 없습니다.<br />쿠폰의 유효 기간 또는 사용 여부를 확인해주세요.',
        ERR_MSG_EXCEPTION_ADD   = '바코드 생성 중 오류가 발생하였습니다',
        ERR_MSG_EXCEPTION_DEL   = '바코드 삭제 중 오류가 발생하였습니다';

    private $barcodeClass;

    protected $usableCoupon         = ['product', 'order'];
    protected $usableCouponKinds    = ['sale']; //===> 현재 할인만 가능
    protected $usableCouponPayment  = ['all'];
    protected $usableCouponOrdertypes = ['order'];

    // 디비 접속
    /** @var \Framework\Database\DBTool $db */
    protected $db;
    protected $couponNo         = array();
    protected $validationType   = 'generate'; //유효성 체크 종류 (generate : 바코드 생성)
    protected $couponList = array();
    protected $memCouponList = array();

    public $barcodeAuthKey      = '';

    public function __construct(Array $couponNo = [])
    {
        parent::__construct();
        $this->couponNo = $couponNo;
        $this->barcodeClass = new Barcode();
    }

    public function getResultData()
    {
        $resultData = [
            'coupon'    => $this->couponList,
            'memCoupon' => $this->memCouponList
        ];

        return $resultData;
    }

    public function getCommonValidationOfCoupon($couponInfo = array())
    {
        return ['isSuccess'=>true];
    }

    public function setCouponAfterValidation($validationType = 'generate', $memCouponNo = null)
    {
        return $this;
    }

    public function couponBarcodeGenarator($type = 'generate')
    {
        $this->setResultMessage(true, 'success');
        return $this->getResultMessage();
    }

    public function deleteBarcodeCoupon(int $memCouponNo = 0)
    {
        $this->setResultMessage(true, 'success');
        return $this->getResultMessage();
    }

    public function addBarcodeCoupon(int $memCouponNo, int $memNo, int $couponNo)
    {
        return null;
    }

    public function getBarcodeCouponByMemCouponNo(int $memCouponNo)
    {
        return [];
    }

    public function getBarcodeCouponByCouponNo(int $couponNo)
    {
        return [];
    }

    public function getBarcodeCouponByAuthKey($authKey)
    {
        return [];
    }

    public function getMemberCouponList(int $couponNo, int $memCouponNo = 0)
    {
        return [];
    }

    public function useBarocdeCoupon($barcodeAuthKey)
    {
        $this->setResultMessage(true, 'success');
        return $this->getResultMessage();
    }

    public function getUsedBarcodeFl($authKey = null)
    {
        return false;
    }

    public function getUsableBarcodeDate()
    {
        return false;
    }
}
