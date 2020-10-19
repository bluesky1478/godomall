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

namespace Bundle\Component\Sms;

use Component\Delivery\Delivery;
use Component\Order\Order;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\UrlUtils;
use Globals;

/**
 * Class 주문/배송 SMS 자동 발송
 * 환불, 반품, 교환, 고객 교환/반품/환불 승인/거절 문자 발송
 *
 * @package Bundle\Component\Sms
 * @author  yjwee
 */
class SmsAutoOrder extends \Component\Sms\SmsAuto
{
    /** @deprecated SMS 발송 코드 타입 관리를 위해서 \Bundle\Sms\Code::REFUND 를 사용하세요. */
    const REFUND = 'REFUND';    //환불신청
    /** @deprecated SMS 발송 코드 타입 관리를 위해서 \Bundle\Sms\Code::BACK 를 사용하세요. */
    const BACK = 'BACK';    //반품신청
    /** @deprecated SMS 발송 코드 타입 관리를 위해서 \Bundle\Sms\Code::EXCHANGE 를 사용하세요. */
    const EXCHANGE = 'EXCHANGE';    //교환신청
    /** @deprecated SMS 발송 코드 타입 관리를 위해서 \Bundle\Sms\Code::ADMIN_APPROVAL 를 사용하세요. */
    const ADMIN_APPROVAL = 'ADMIN_APPROVAL';    //고객 교환/반품/환불 승인(관리자 승인처리)
    /** @deprecated SMS 발송 코드 타입 관리를 위해서 \Bundle\Sms\Code::ADMIN_REJECT 를 사용하세요. */
    const ADMIN_REJECT = 'ADMIN_REJECT';    //고객 교환/반품/환불 거절(관리자 거절처리)
    /** @var  Delivery $delivery 배송클래스 */
    protected $delivery;
    /** @var Order $order 주문클래스 */
    protected $order;
    /** @var  array $smsOrder SMS 발송 시 사용되는 주문정보 */
    protected $smsOrder;
    /** @var  array $sendFlags SMS 발송 대상 주문의 상태별 SMS 발송여부 */
    protected $sendFlags;
    /** @var  integer $orderNo SMS 발송 주문번호 */
    protected $orderNo;
    /** @var  integer $orderGoodsNo SMS 발송 주문의 주문상품번호 */
    protected $orderGoodsNo;
    /** @var string $orderFlagKey 주문의 상태별 발송확인 Key */
    protected $orderFlagKey;
    /** @var  integer $orderGoodsFlagKey 주문상품의 상태별 발송확인 Key */
    protected $orderGoodsFlagKey;
    protected $orderStatusDisplay;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (empty($config['smsAutoCodeType'])) {
            $logger = \App::getInstance('logger');
            $logger->info(__METHOD__, $config);
            throw new \Exception('SMS ' . __('발송 코드 정보는 필수 입니다.'));
        }
        $this->smsAutoCodeType = $config['smsAutoCodeType'];
        $this->order = is_object($config['order']) ? $config['order'] : \App::load('Component\\Order\\Order');
        $this->delivery = is_object($config['delivery']) ? $config['delivery'] : \App::load('Component\\Delivery\\Delivery');
    }

    /**
     * @inheritDoc
     */
    public function autoSend()
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__);
        $result = [];
        try {
            $this->loadOrder();
            $aBasicInfo = gd_policy('basic.info');
            $receiver['scmNo'] = DEFAULT_CODE_SCMNO;
            $receiver['memNo'] = StringUtils::strIsSet($this->smsOrder['memNo']);
            $receiver['memNm'] = StringUtils::strIsSet($this->smsOrder['orderName']);
            $receiver['smsFl'] = 'y';
            $receiver['cellPhone'] = StringUtils::strIsSet($this->smsOrder['orderCellPhone']);
            $smsInfo = [
                'orderName'      => StringUtils::strIsSet($this->smsOrder['orderName']),
                'orderNo'        => $this->orderNo,
                'orderCellPhone' => StringUtils::strIsSet($this->smsOrder['orderCellPhone']),
                'rc_mallNm' => Globals::get('gMall.mallNm'),
                'shopUrl' => $aBasicInfo['mallDomain']
            ];
            if (empty($this->orderStatusDisplay) === false) {
                $smsInfo['userExchangeStatus'] = $this->orderStatusDisplay;
            }
            $this->smsType = SmsAutoCode::ORDER;
            $this->receiver = $receiver;
            $this->replaceArguments = $smsInfo;
            $result = parent::autoSend();
            $this->order->saveOrderSendInfoResult($this->orderNo, $this->sendFlags);
        } catch (\Throwable $e) {
            $logger->error($e->getTraceAsString());     // 예외 발생시 주문로직이 정지해버리기때문에 로그 기록만 하도록 처리
        }

        return $result;
    }

    /**
     * SMS 발송에 필요한 주문정보를 검증하고 조회하는 함수
     *
     * @throws \Exception
     */
    protected function loadOrder()
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__ . ' orderNo[' . $this->orderNo . '], orderGoodsNo[' . $this->orderGoodsNo . ']');
        if (empty($this->orderNo)) {
            throw new \Exception('SMS ' . __('발송을 위한 주문정보 조회를 위해 주문번호가 필요합니다.'));
        }
        if (empty($this->orderGoodsNo)) {
            throw new \Exception(__('상품별 상태에 따른 SMS 발송을 위해 주문상품번호가 필요합니다.'));
        }
        $this->orderFlagKey = 'sms_' . $this->smsAutoCodeType;
        $this->orderGoodsFlagKey = $this->orderFlagKey . '_' . $this->orderGoodsNo;
        $this->smsOrder = $this->order->getOrderDataSend($this->orderNo);
        if (empty($this->smsOrder)) {
            throw new \Exception(__('주문번호') . '[' . $this->orderNo . ']' . __('의 주문정보가 없습니다.'));
        }
        $logger->debug(__METHOD__, $this->smsOrder);
        $this->loadOrderGoods();
        if ($this->smsAutoCodeType == Code::INVOICE_CODE) {
            $this->loadDelivery();
        }
        $this->sendFlags = [];
        if (empty($this->smsOrder['sendMailSmsFl']) === false) {
            $this->sendFlags = ArrayUtils::xmlToArray($this->smsOrder['sendMailSmsFl']);
        }
        StringUtils::strIsSet($this->sendFlags[$this->orderFlagKey], 'n');
        StringUtils::strIsSet($this->sendFlags[$this->orderGoodsFlagKey], 'n');
        $isSend = true;
        if (StringUtils::strIsSet($this->sendFlags[$this->orderFlagKey], 'y') === 'y') {
            $isSend = false;
        } else if ($this->checkAllGoodsSendFlag()) {
            $this->sendFlags[$this->orderFlagKey] = 'y';
        }
        if ($isSend === false) {
            throw new \Exception(__('이미 SMS 가 모두 발송된 주문') . '[' . $this->orderNo . ']' . __('입니다.'));
        }
        if (StringUtils::strIsSet($this->sendFlags[$this->orderGoodsFlagKey], 'y') === 'y') {
            $isSend = false;
        } else {
            $this->sendFlags[$this->orderGoodsFlagKey] = 'y';
        }
        if ($isSend === false) {
            throw new \Exception(__('이미 SMS 가 발송된 주문상품') . '[' . $this->orderGoodsNo . ']' . __('입니다.'));
        }
    }

    /**
     * 주문상품정보 조회
     */
    protected function loadOrderGoods()
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__);
        $orderGoods = $this->order->getOrderGoodsData($this->orderNo, null, null, null, 'user');
        $goodsKey = key($orderGoods);
        $this->smsOrder['goods'] = $orderGoods;
        if ($this->smsAutoCodeType == Code::ADMIN_APPROVAL || $this->smsAutoCodeType == Code::ADMIN_REJECT) {
            foreach ($orderGoods as $index => $orderGood) {
                foreach ($orderGood as $index2 => $item2) {
                    if ($this->orderGoodsNo == $item2['sno']) {
                        $logger->info(__METHOD__ . ' ' . $this->orderNo . ' - ' . $item2['userHandleFlStr']);
                        $userHandleFlStr = $item2['userHandleFlStr'];
                        $userHandleFlStr = str_replace(__('승인'), '', $userHandleFlStr);
                        $userHandleFlStr = str_replace(__('거절'), '', $userHandleFlStr);
                        $this->orderStatusDisplay = $userHandleFlStr;
                        $logger->info(__METHOD__ . ' ' . $this->orderNo . ' - ' . $this->orderStatusDisplay);
                    }
                }
            }
        } else {
            if (isset($this->smsOrder['goods'][$goodsKey])) {
                $this->smsOrder['goods'] = $orderGoods[$goodsKey];
            }
        }
    }

    /**
     * 주문 배송정보 조회
     */
    protected function loadDelivery()
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__);
        $company = $this->delivery->getDeliveryCompany();
        $companySno = ArrayUtils::getSubArrayByKey($company, 'sno');
        $companyNames = ArrayUtils::getSubArrayByKey($company, 'companyName');
        $traceUrls = ArrayUtils::getSubArrayByKey($company, 'traceUrl');
        $arrDelivery = array_combine($companySno, $companyNames);
        $arrDeliveryTrace = array_combine($companySno, $traceUrls);
        foreach ($this->smsOrder['goods'] as $index => &$good) {
            $invoiceCompanySno = $good['invoiceCompanySno'];
            if (empty($invoiceCompanySno) || $invoiceCompanySno < 1) {
                continue;
            }
            $good['invoiceCompanyName'] = $arrDelivery[$invoiceCompanySno];
            $good['invoiceLink'] = str_replace('__INVOICENO__', $good['invoiceNo'], $arrDeliveryTrace[$invoiceCompanySno]);
        }
    }

    /**
     * 주문의 주문상품이 전부 현재 발송하려는 SMS가 발송되었는지 체크하는 함수
     *
     * @return bool
     */
    protected function checkAllGoodsSendFlag()
    {
        $result = true;
        $goods = $this->smsOrder['goods'];
        foreach ($goods as $index => $good) {
            $goodsFlagKey = $this->orderFlagKey . '_' . $good['sno'];
            $sendFlag = $this->sendFlags[$goodsFlagKey];
            StringUtils::strIsSet($sendFlag, 'n');
            if ($sendFlag !== 'y') {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * @param int $orderNo
     */
    public function setOrderNo($orderNo)
    {
        $this->orderNo = $orderNo;
    }

    /**
     * @param int $orderGoodsNo
     */
    public function setOrderGoodsNo($orderGoodsNo)
    {
        $this->orderGoodsNo = $orderGoodsNo;
    }
}
