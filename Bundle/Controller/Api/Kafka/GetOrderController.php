<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Api\Kafka;

use Component\Validator\Validator;
use Component\Database\DBTableField;
use Framework\Security\XXTEA;
use Component\Naver\NaverPay;

/**
 * 고도 주문상품 정보수집 API
 *
 * @author Lee Nam ju <lnjts@godo.co.kr>
 */
class GetOrderController extends \Controller\Api\Controller
{
    public function index()
    {
        set_time_limit(15);

        $this->db = \App::load('DB');
        $mode = \Request::post()->get('mode');
        try {
            if ($mode == 'naver') {
                $orderNo = $this->getNaverOrderNo();
            }
            else if($mode == 'payco') {
                $orderNo = $this->getPaycoOrderNo();
            }
            else if ($mode == 'godo') {
                $orderNo = $this->getGodoOrderNo();
            }
            else if ($mode == 'kakaopay') {
                $orderNo = $this->getKakaopayOrderNo();
            }
            else {
                throw new \Exception('Invalid mode '.$mode);
            }

            $response['data'] =  $this->getOrderData($orderNo);
        } catch (\Throwable $e) {
            $response['header']['code'] = 401;
            $response['header']['message'] = __($e->getMessage());
            $this->responseData($response);
        }

        if (empty($response['data']['items'])) {
            $response['header']['code'] = 401;
            $response['header']['message'] = __('주문이 없습니다.');
            $this->responseData($response);
        }

        $response['header']['code'] = 200;
        $this->responseData($response);

    }

    protected function getGodoOrderNo()
    {
        $orderNo = \Request::post()->get('orderNo');
        if (empty($orderNo)) {
            throw new \Exception('주문번호 없음');
        }

        return $orderNo;
    }

    protected function getPaycoOrderNo()
    {
        $encData = \Request::post()->get('enc');
        $request = unserialize(\Encryptor::decrypt($encData));
        if (empty($request)) {
            throw new \Exception('복호화 실패');
        }
        $orderNo = $request['orderNo'];
        if (empty($orderNo)) {
            throw new \Exception('복호화 실패(주문번호 없음)');
        }

        return $orderNo;
    }

    protected function getKakaopayOrderNo()
    {
        // 복호화
        $encryption = \App::getInstance('encryption');
        $pgSetting = gd_pgs('pk');
        $pgConfig = new KakaopayConfig($pgSetting);

        $encData = \Request::post()->get('data');

        $sKey = $pgConfig->gdKey;
        $encryption->initialize(
            array(
                'driver' => 'openssl',
                'cipher' => 'aes-256',
                'mode' => 'cbc',
                'key' => $sKey,
            )
        );

        $decrypttext = $encryption->decrypt($encData);
        $request = json_decode($decrypttext, true);

        if (empty($request)) {
            throw new \Exception('복호화 실패');
        }
        $orderNo = $request['partner_order_id'];
        if (empty($orderNo)) {
            throw new \Exception('복호화 실패(주문번호 없음)');
        }

        return $orderNo;
    }

    protected function getNaverOrderNo()
    {
        $encData = \Request::post()->get('enc');
        $naverpay = new NaverPay();
        $config = $naverpay->getConfig();
        $xxtea = new XXTEA();
        $xxtea->setKey($config['cryptkey']);
        $request = unserialize($xxtea->decrypt(base64_decode($encData)));
        if (empty($request)) {
            throw new \Exception('복호화 실패');
        }
        $orderNo = $request['data']['PO_MerchantCustomCode1'];
        if (empty($orderNo)) {
            throw new \Exception('복호화 실패(주문번호 없음)');
        }

        return $orderNo;
    }


    protected function getOrderData($orderNo)
    {
        $this->db->strField = 'orderNo, memNo,orderStatus,  orderChannelFl, orderTypeFl,  orderGoodsCnt,settlePrice, useMileage, useDeposit, totalGoodsPrice, totalDeliveryCharge, totalGoodsDcPrice, totalMemberDcPrice, totalMemberOverlapDcPrice, totalGoodsMileage, totalCouponGoodsDcPrice, realTaxSupplyPrice, realTaxVatPrice, realTaxFreePrice, firstSaleFl, settleKind, pgName, pgResultCode, pgCancelFl, regDt as orderDate, paymentDt, modDt , mallSno, totalCouponOrderDcPrice, totalCouponDeliveryDcPrice';

        $arrBind = null;
        $this->db->strWhere = ' orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . implode(' ', $query);
        $response['data']['order'] = $this->db->query_fetch($strSQL, $arrBind, false);

        if (empty($response['data']['order'])) {
            return null;
        }

        $this->db->strField = 'sno, orderNo, orderCd,handleSno,orderStatus, goodsNo, goodsCd, goodsModelNo, goodsNm, goodsCnt, goodsPrice, optionPrice, fixedPrice, costPrice, optionSno, optionInfo, goodsTaxInfo, brandCd, makerNm, originNm, regDt, modDt,taxSupplyGoodsPrice,taxVatGoodsPrice,taxFreeGoodsPrice,realTaxSupplyGoodsPrice,realTaxVatGoodsPrice,realTaxFreeGoodsPrice,divisionUseDeposit,divisionUseMileage,divisionGoodsDeliveryUseDeposit,divisionGoodsDeliveryUseMileage,divisionCouponOrderDcPrice,divisionCouponOrderMileage,addGoodsCnt,addGoodsPrice,optionCostPrice,optionTextPrice,goodsDcPrice,memberDcPrice,memberOverlapDcPrice,couponGoodsDcPrice,goodsDeliveryCollectPrice,goodsMileage,memberMileage,couponGoodsMileage,goodsDeliveryCollectFl,minusDepositFl,minusRestoreDepositFl,minusMileageFl,minusRestoreMileageFl,plusMileageFl,plusRestoreMileageFl,minusStockFl,minusRestoreStockFl,cancelDt,finishDt,statisticsGoodsFl,statisticsOrderFl,checkoutData,invoiceCompanySno';

        $arrBind = null;
        $this->db->strWhere = ' orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . implode(' ', $query);
        $response['data']['items'] = $this->db->query_fetch($strSQL, $arrBind, false);

        return $response;
    }

    public function responseData($arrayData)
    {
        if (empty($arrayData['data']) === false) {
            $key = str_pad('godomall5##&_' . \Globals::get('gLicense.godosno'), 24, 0);

            \Encryptor::setKey($key);
            \Encryptor::setMode(MCRYPT_MODE_ECB);
            \Encryptor::setCipher(MCRYPT_RIJNDAEL_128);
            $arrayData['data'] = \Encryptor::encryptSimple(json_encode($arrayData['data'], JSON_UNESCAPED_UNICODE));
        }

        $data = json_encode($arrayData, JSON_UNESCAPED_UNICODE);
        print_r($data);

//        $data =  $this->decryptData($data);
//        print_r($data);
        exit;
    }

    public function decryptData($arrayData)
    {
        $jsonData = json_decode($arrayData, true);
        if (empty($jsonData['data']) === false) {
            $key = str_pad('godomall5##&_' . \Globals::get('gLicense.godosno'), 24, 0);

            \Encryptor::setKey($key);
            \Encryptor::setMode(MCRYPT_MODE_ECB);
            \Encryptor::setCipher(MCRYPT_RIJNDAEL_128);
            $jsonData = \Encryptor::decryptSimple($jsonData['data']);
        }

        return $jsonData;
    }
}
