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

namespace Bundle\Controller\Front\Order;

use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Globals;
use Request;
use Session;
use App;
use UserFilePath;

/**
 * 장바구니 내 견적서
 * @author Lee Hakyoung <haky2@godo.co.kr>
 */
class CartEstimateController extends \Controller\Front\Controller
{
    public function index() {
        try {
            if (gd_is_plus_shop(PLUSSHOP_CODE_CARTESTIMATE) === false ) {
                throw new AlertCloseException(__('장바구니 내 견적서 기능을 사용하려면 플러스샵 설치가 필요합니다.'));
            }

            $cartConfig = gd_policy('order.cart');

            if ($cartConfig['estimateUseFl'] != 'y') {
                throw new AlertCloseException(__('장바구니 내 견적서 사용설정이 되어있지 않습니다.'));
            }

            if (gd_is_login()) {
                $this->setData('memNm', Session::get('member.memNm'));
            }

            // 장바구니 정보
            $cart = App::load('\\Component\\Cart\\Cart');
            $cartInfo = $cart->getCartGoodsData(Request::post()->get('cartSno'));

            // 견적서 내 상품번호 및 가격 정의
            $estimateNo = $totalPrice = 0;
            foreach ($cartInfo as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $gKey => $gVal) {
                        $cartInfo[$sKey][$dKey][$gKey]['estimateNo'] = ++$estimateNo;

                        // 판매가 적용 설정
                        if ($cartConfig['memberDiscount'] == 'y' && $cartConfig['goodsDiscount'] == 'y') {
                            $cartInfo[$sKey][$dKey][$gKey]['price']['goodsPrice'] = $gVal['price']['goodsPrice'] - (($gVal['price']['goodsMemberDcPrice'] + $gVal['price']['goodsMemberOverlapDcPrice'] + $gVal['price']['goodsDcPrice']) / $gVal['goodsCnt']);
                        } elseif ($cartConfig['memberDiscount'] == 'y') {
                            $cartInfo[$sKey][$dKey][$gKey]['price']['goodsPrice'] = $gVal['price']['goodsPrice'] - (($gVal['price']['goodsMemberDcPrice'] + $gVal['price']['goodsMemberOverlapDcPrice']) / $gVal['goodsCnt']);
                        } elseif ($cartConfig['goodsDiscount'] == 'y') {
                            $cartInfo[$sKey][$dKey][$gKey]['price']['goodsPrice'] = $gVal['price']['goodsPrice'] - ($gVal['price']['goodsDcPrice'] / $gVal['goodsCnt']);
                        }

                        if (empty($gVal['addGoods']) == false) {
                            foreach ($gVal['addGoods'] as $aKey => $aVal) {
                                if ($cartConfig['memberDiscount'] == 'y') {
                                    $cartInfo[$sKey][$dKey][$gKey]['addGoods'][$aKey]['addGoodsPrice'] = $aVal['addGoodsPrice'] - (($aVal['addGoodsMemberDcPrice'] + $aVal['addGoodsMemberOverlapDcPrice']) / $aVal['addGoodsCnt']);
                                }
                                $totalPrice += $cartInfo[$sKey][$dKey][$gKey]['addGoods'][$aKey]['addGoodsPrice'] * $aVal['addGoodsCnt'];
                            }
                        }

                        $totalPrice += ($cartInfo[$sKey][$dKey][$gKey]['price']['goodsPrice'] + $gVal['price']['optionPrice'] + $gVal['price']['optionTextPrice']) * $gVal['goodsCnt'];
                    }
                }
            }

            $this->setData('totalPrice', $totalPrice);
            $this->setData('cartInfo', $cartInfo);

            // 입금 계좌 정보
            $order = App::load('\\Component\\Order\\OrderAdmin');
            $orderInfo = $order->getBankPolicyList();
            foreach ($orderInfo['data'] as $key => $val) {
                if ($val['defaultFl'] == 'y') {
                    $this->setData('bankAccount', $orderInfo['data'][$key]);
                }
            }

            // 인감 이미지는 기존은 세금계산서 정보에, 현재는 기본 정보에 등록됨
            $taxInvoice = gd_policy('order.taxInvoice');
            $basicData = gd_policy('basic.info');
            if (empty($taxInvoice['taxStampIamge']) === false) {
                $sealPath = UserFilePath::data('etc', $taxInvoice['taxStampIamge'])->www();
            } else if (empty($basicData['stampImage']) === false) {
                $sealPath = UserFilePath::data('etc', $basicData['stampImage'])->www();
            } else {
                $sealPath = '';
            }

            // 세금계산서 이용안내
            if (gd_isset($taxInvoice['taxInvoiceUseFl']) == 'y') {
                $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                if ($taxInvoice['taxinvoiceInfoUseFl'] == 'y') {
                    $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                }
            }

            unset($taxInvoice, $basicData);
            $this->setData('sealPath', gd_isset($sealPath));

            $this->setData('gMall', gd_htmlspecialchars(Globals::get('gMall')));
        }
        catch (Exception $e) {
            throw $e;
        }
    }
}
