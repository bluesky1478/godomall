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

namespace Bundle\Controller\Front\Mypage;

use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Cookie;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderRefundRegistController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 리퀘스트 데이터 (post는 기존 전체 환불일 경우)
            if (Request::get()->toArray()) {
                $reqValue = Request::get()->toArray();
            } else {
                $reqValue = Request::post()->toArray();
                $this->setData('orderGoodsNo', $reqValue['orderGoodsNo']);
                $this->setData('orderStatus', $reqValue['orderStatus']);
            }

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');

            // 모드 설정
            $this->setData('mode', $reqValue['mode']);

            // 주문 리스트 정보
            $orderData = $order->getOrderView($reqValue['orderNo']);
            if ($orderData['orderChannelFl'] == 'naverpay') {
                throw new \Exception(__('잘못된 경로로 접근하셨습니다.'));
            }
            else if($orderData['orderChannelFl'] == 'etc'){
                throw new \Exception(__('(환불)신청이 불가한 주문입니다. 고객센터로 문의해주세요.'));
            }
            else {}

            // 사용자 반품/교환/환불 신청 데이터 생성
            $orderData = $order->getOrderClaimList($orderData, $reqValue['mode']);

            $this->setData('orderData', $orderData);
            $this->setData('orderNo', $reqValue['orderNo']);
            $this->setData('returnUrl', Request::getReferer());

            // 사유
            $reasonCode = gd_array_change_key_value(gd_code('04001'));
            $this->setData('userHandleReason', gd_isset($reasonCode));

            // 환불 계좌 은행
            $bankNmCode = gd_array_change_key_value(gd_code('04002'));
            $this->setData('userRefundBankName', gd_isset($bankNmCode));

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
