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

namespace Bundle\Controller\Mobile\Mypage;

use App;
use Component\Member\Util\MemberUtil;
use Exception;
use Request;

/**
 * 배송지 등록 레이어
 *
 * @package Bundle\Controller\Mobile\Mypage
 * @author  <kookoo135@godo.co.kr>
 */
class LayerShippingAddressRegistController extends \Controller\Mobile\Controller
{
    public function index()
    {
        try {
            if (!MemberUtil::isLogin()) {
                $this->js("alert('" . __('로그인을 하셔야 이용하실 수 있습니다.') . "'); top.location.href = '../member/login.php';");
            }

            // Order 콤포넌트 호출
            $order = App::load(\Component\Order\Order::class);

            // 국가데이터 가져오기
            $countriesCode = $order->getUsableCountriesList();

            // 전화용 국가코드 셀렉트 박스 데이터
            foreach ($countriesCode as $key => $val) {
                if ($val['callPrefix'] > 0) {
                    $countryPhone[$val['code']] = __($val['countryNameKor']) . '(+' . $val['callPrefix'] . ')';
                }
            }
            $this->setData('countryPhone', $countryPhone);

            // 주소용 국가코드 셀렉트 박스 데이터
            $countryAddress = [];
            foreach ($countriesCode as $key => $val) {
                $countryAddress[$val['code']] = __($val['countryNameKor']) . '(' . $val['countryName'] . ')';
            }
            $this->setData('countryAddress', $countryAddress);

            if (Request::get()->has('sno') && is_numeric(Request::get()->get('sno'))) {
                $data = $order->getShippingAddressData(Request::get()->get('sno'));
                $this->setData('data', $data);
                $this->setData('mode', 'shipping_modify');
            } else {
                if (empty($order->getShippingDefaultFlYn()) === true) {
                    $data['defaultFl'] = 'y';
                    $data['defaultFlDisabled'] = true;
                    $this->setData('data', $data);
                }
                $this->setData('mode', 'shipping_regist');
            }
            $this->setData('shippingNo', gd_isset(Request::get()->get('shippingNo'), 0));
            $this->setData('type', Request::get()->get('type'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }
}
