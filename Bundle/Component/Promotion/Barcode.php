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
use Session;
use Exception;

/**
 * ===================================================================================
 * 2019.10.15
 *  - 바코드 기능 사용 불가 (기능 제거)
 *  - 바코드 기능 제거 작업으로 기존 레거시 보장을 위해 해당 class 및 함수 유지하되 로직 제거
 * ===================================================================================
 *
 */
class Barcode
{
    const ERR_MESSAGE_REQUIRE = '필수 항목이 누락되었습니다.';
    const DEFAULT_CODE_TYPE = 'TYPE_CODE_128_C';
    const DEFAULT_GENERATOR_TYPE = 'HTML';

    public $resultMessage = '';
    public $isSuccess = false;
    public $errorMessage = '';

    //Output type
    protected $generatorTypeList = [
        'HTML',
        'SVG',
        'JPG',
        'PNG'
    ];

    protected $managerId;
    protected $db;
    protected $barcodeAuthKey = ''; //고유한 바코드 인증키
    protected $shopSno; //바코드 생성 시 필요한 값
    protected $memCouponNo = ''; //바코드 생성 시 필요한 값
    protected $barcodeNo = '';
    protected $couponNo = '';
    protected $memberNo   = '';
    protected $generatorType = '';
    protected $codeType = '';
    protected $writeLogFl  = true;

    public function __construct($memCouponNo=null)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $globals = \App::getInstance('globals');
        $this->shopSno = $globals->get('gLicense.godosno');
        $this->managerId = Session::get('manager.managerId');

        if (empty($this->generatorType) === true) {
            $this->setGeneratorType(self::DEFAULT_GENERATOR_TYPE);
        }
        if (empty($memCouponNo) === false) {
            $this->memCouponNo = $memCouponNo;
        }
        $this->codeType     = self::DEFAULT_CODE_TYPE;
    }

    public function setCouponNo($couponNo)
    {
        $this->couponNo = $couponNo;
        return $this;
    }

    public function setMemberCouponNo($memCouponNo)
    {
        $this->memCouponNo = $memCouponNo;
        return $this;
    }

    public function setBarcodeNo($barcodeNo)
    {
        $this->barcodeNo = $barcodeNo;
        return $this;
    }

    public function setCodeType($codeType)
    {
        $this->codeType = $codeType;
        return $this;
    }

    public function setGeneratorType($generatorType='HTML')
    {
        $generatorType = strtoupper($generatorType);
        if (in_array($this->generatorTypeList, $generatorType) === false) {
            throw new Exception(__(self::ERR_MESSAGE_REQUIRE));
        }
        $this->generatorType = $generatorType;
        return $this;
    }

    public function setBarcodeAuthKey($barcodeAuthKey)
    {
        $this->barcodeAuthKey = $barcodeAuthKey;
        return $this;
    }

    public function getBarcodeAuthKey()
    {
        return $this->barcodeAuthKey;
    }

    public function setWriteLogFl(bool $flag)
    {
        $this->writeLogFl = $flag;
        return $this;
    }

    public function addBarcodeGroup(int $couponNo = 0)
    {
        return 0;
    }

    public function authKeyGenerator($memCouponNo = null)
    {
        return $this->barcodeAuthKey;
    }

    public function barcodeGenerator()
    {
        return '';
    }

    public function updateBarcodeCount($type = 'plus')
    {
        return 0;
    }

    public function getBarcodeInfoByNo(int $barcodeNo=0, int $couponNo=0)
    {
        return [];
    }

    public function addBarcodeLog($mode, $updateData, $prevData = null)
    {
        return $this;
    }

    public function deleteBarcode($barcodeNo = array())
    {
        $this->setResultMessage(true, 'success');
        return $this->getResultMessage();
    }

    public function setResultMessage($isSuccss, $msg='', $error='')
    {
        $this->isSuccess            = $isSuccss;
        $this->resultMessage        = $msg;
        $this->errorMessage         = $error;
    }

    public function getResultMessage()
    {
        return  [
            'isSuccess'  => $this->isSuccess,
            'msg'   => $this->resultMessage,
            'error'   => $this->errorMessage
        ];
    }
}
