<?php
namespace Bundle\Controller\Mobile\Member\Ipin;

use Framework\StaticProxy\Proxy\Session;
use Globals;
use Logger;
use Request;
use Component\Member\Ipin\NiceNuguyaOivs;

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

/**
 * Class IPINCheckRequestController
 * @package Controller\Mobile\Member\Ipin
 * @author  yjwee
 */
class IPINCheckRequestController extends \Controller\Mobile\Controller
{
    public function index()
    {
        //#######################################################################################
        //#####
        //#####	나이스아이핀(대체인증키) 서비스 샘플 페이지 소스				한국신용정보(주)
        //#####
        //#####	================================================================================
        //#####
        //#####	* 본 페이지는 귀사의 화면에 맞게 수정하십시오.
        //#####	  단, Head 영역에 설정된 Javascript를 수정하거나 변경하시면 사용할 수 없습니다.
        //#####
        //#######################################################################################

        $ipinData = gd_policy('member.ipin');
        Logger::info(__METHOD__);
        Logger::debug(__METHOD__, $ipinData);
        //========================================================================================
        //=====	▣ 회원사 ID, 사이트식별정보 설정 : 계약시에 발급된 회원사 ID를 설정하십시오. ▣
        //========================================================================================

        //        $NiceId = $ipinData['siteCode'];
        //        $SIKey = $ipinData['sitePass'];
        $NiceId = 'B269';
        $SIKey = '15917609';

        //========================================================================================
        //=====	▣ 반환 결과를 수신할 URL을 설정하십시오. (단, 페이지는 그대로 사용하십시오)
        //=====	   한신정 서비스에 전달되어 사용되므로 반드시 절대 URL 경로를 설정하셔야 합니다.
        //========================================================================================

        //EX) http://귀사의도메인/NiceCheckPopup.php
        $phpSelf = Request::getPhpSelf();
        $self_filename = basename($phpSelf);
        $loc = strpos($phpSelf, $self_filename);
        $sub_path = substr($phpSelf, 0, $loc);

        $serverPort = Request::server()->get('SERVER_PORT');
        $Port = ($serverPort == 80) ? '' : $serverPort;
        if (strlen($Port) > 0) $Port = ':' . $Port;
        $Protocol = (Request::isSecure() ? 'https://' : 'http://');

        $ReturnURL = $Protocol . Request::getHost() . $Port . $sub_path . 'nice_check_call_back.php';

        $strOrderNo = date('Ymd') . rand(100000000000, 999999999999); //주문번호 20자리 .. 매 요청마다 중복되지 않도록 유의

        // 해킹방지를 위해 요청정보 세션에 저장
        Session::set('sess_OrderNo', $strOrderNo);
        Session::set('sess_callType', Request::get()->get('callType'));

        $Nice = new NiceNuguyaOivs();
        $PingInfo = $Nice->getPingInfo();

        $this->setData('Protocol', $Protocol);
        $this->setData('NiceId', $NiceId);
        $this->setData('SIKey', $SIKey);
        $this->setData('PingInfo', $PingInfo);
        $this->setData('ReturnURL', $ReturnURL);
        $this->setData('strOrderNo', $strOrderNo);

        /* @formatter:off */
        $this->setData(
            'headerScript', [
                $Protocol . 'secure.nuguya.com/nuguya/nice.nuguya.oivs.crypto.js'
                ,
                $Protocol . 'secure.nuguya.com/nuguya/nice.nuguya.oivs.msgg.utf8.js'
                ,
                $Protocol . 'secure.nuguya.com/nuguya/nice.nuguya.oivs.util.js',
            ]
        );
        /* @formatter:on */
    }
}
