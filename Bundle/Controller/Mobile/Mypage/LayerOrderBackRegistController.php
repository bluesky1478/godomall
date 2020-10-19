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

namespace Bundle\Controller\Mobile\Mypage;

use Component\Goods\GoodsCate;
use Component\Page\Page;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Framework\Utility\DateTimeUtils;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderBackRegistController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // GET 리퀘스트
            $getValue = Request::get()->toArray();

            $this->setData('mode', $getValue['mode']);

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');

            // 주문 리스트 정보
            $orderData = $order->getOrderView($getValue['orderNo']);
            if ($orderData['orderChannelFl'] == 'naverpay') {
                throw new \Exception(__('잘못된 경로로 접근하셨습니다.'));
            }
            else if($orderData['orderChannelFl'] == 'etc'){
                throw new \Exception(__('(반품)신청이 불가한 주문입니다. 고객센터로 문의해주세요.'));
            }
            else {}
            $this->setData('orderData', $orderData);

            // 사용자 반품/교환/환불 신청 데이터 생성
            $orderData = $order->getOrderClaimList($orderData, $getValue['mode']);

            $ordersByRegisterDay = [];
            foreach ($orderData as $index => $item) {
                $ordersByRegisterDay[DateTimeUtils::dateFormat('Y-m-d', $item['regDt'])][] = $item;
                foreach ($item['goods'] as $gKey => $gVal) {
                    if (empty($gVal) === false && $gVal['goodsCnt'] > 0) {
                        for ($tmp = 0; $tmp < $gVal['goodsCnt']; $tmp++) {
                            $ordersByRegisterDay[DateTimeUtils::dateFormat('Y-m-d', $item['regDt'])][$index]['goods'][$gKey]['selectName'] = 'claimGoodsCnt[' . $gVal['sno'] .']';
                            $ordersByRegisterDay[DateTimeUtils::dateFormat('Y-m-d', $item['regDt'])][$index]['goods'][$gKey]['claimGoodsCnt'][$tmp + 1] = $tmp + 1;
                        }
                    }
                }
            }
            $this->setData('ordersByRegisterDay', $ordersByRegisterDay);

            // 치환데이터
            $this->setData('orderNo', $getValue['orderNo']);
            $this->setData('orderGoodsNo', $getValue['orderGoodsNo']);
            $this->setData('orderStatus', $getValue['orderStatus']);
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
