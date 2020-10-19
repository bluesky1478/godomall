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

namespace Bundle\Controller\Admin\Share;

use App;

/**
 * 샵링커 연결페이지 콘트롤러
 */
class ShoplinkerController extends \Controller\Admin\Controller
{
    /**
     * @var string 고도앱에서 이나무공용으로 쓰는 키 - 고도몰5에서도 로그인부분 복호화키로 사용
     */
    protected $gdKey = "godoselly1106";

    /**
     * index
     *
     */
    public function index()
    {
        //--- 페이지 데이터
        try {
            // --- 메뉴 설정
            $this->callMenu('marketLink', 'shoplinker', 'shoplinkerDashboard');

            // 샵링커 설정 config 불러오기
            $shoplinkerInfo = gd_policy('shoplinker.config');

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_fluid_onlytop.php');

            if (empty($shoplinkerInfo) === true) {
                $this->setData('useShoplinker', 'n');
            } else {
//                $shoplinkerInfo['shopKey'] = $this->encrypt($shoplinkerInfo['shopKey']);
//                $shoplinkerInfo['slinkerKey'] = $this->encrypt($shoplinkerInfo['slinkerKey']);

                $this->setData('useShoplinker', 'y');
                $this->setData('data', gd_isset($shoplinkerInfo));
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    //128bit aes 암호화
    function encrypt($data)
    {
        $expected_length = 16 * (floor(strlen($data) / 16) +1);
        $padding_length = $expected_length - strlen($data);
        $data = $data . str_repeat(chr($padding_length), $padding_length);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $enc = openssl_encrypt($data, 'AES-128-ECB', $this->gdKey, 1, $iv);
        //$enc = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->gdKey, $data, MCRYPT_MODE_ECB, $iv);

        return strtoupper(bin2hex($enc));
    }

    //128bit aes 복호화
    function decrypt($data)
    {
        $data = hex2bin($data);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $dec = openssl_decrypt($data, 'AES-128-ECB', $this->gdKey, 1, $iv);
        //$dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->gdKey, $data, MCRYPT_MODE_ECB, $iv);
        $last = $dec[strlen($dec) - 1];
        $dec = substr($dec, 0, strlen($dec) - ord($last));
        $dec = rtrim($dec, "\x00..\x1F");

        return $dec;
    }

}
