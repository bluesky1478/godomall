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

namespace Bundle\Component\Mobile;

use Framework\Http\Response;
use Globals;
use Request;
use Session;
use App;
use Bundle\Component\Design\DesignConnectUrl;
use Framework\Utility\StringUtils;


/**
 * 모바일샵 설정
 *
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class MobileShop
{
    const ERROR_VIEW = 'ERROR_VIEW';

    protected $db;

    public $arrFields = [];

    public $arrData = [];

    public $arrChecked = [];

    private $_mobileConfig;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 모바일 접속 체크. 모바일 기기에서 PC 로 접속한 경우 리다이렉트 처리
     *
     * (세션에 저장되는 값은 사용하지 않는 걸로 - 기존 deprecated 처리 주석 내용)
     */
    public function setMobileConnect()
    {
        // 모바일샵 세션 시작
        Session::set('mobileInfo.mode', 'front');
        Session::set('mobileInfo.mobile', false);
        Session::set('mobileInfo.browser', 'n');

        // 모바일 접속 선언
        $_is['access'] = true;
        $_is['mobile'] = Request::isMobileDevice();
        $_is['browser'] = Request::isModernBrowser();

        // 모바일 접속 예외 처리 페이지 (PG 결제시 비동기 페이지, 가상계좌 입금통보 와 같은 페이지)
        $excetionPage = [
            'pg_vbank_return.php',
            'pg_return_url.php',
            'pg_return_noti.php',
            'pg_return.php',
        ];

        // 도메인 접속에 따른 처리 모바일 관련 처리
        switch (App::getInstance('ControllerNameResolver')->getControllerRootDirectory()) {
            // 모바일 도메인으로 접속시 처리
            case 'mobile':
                // PC버전에서 모바일로 왔을 경우 세션 제거
                if (Session::has('pcView')) {
                    Session::del('pcView');
                }

                // 모바일샵 사용하지 않거나 HTML5 지원 브라우저가 아니면 PC 페이지로
                if ((Globals::get('gSite.mobile.config.mobileShopFl')) != 'y') {
                    // 예외 페이지 처리
                    if (in_array(Request::getFileUri(), $excetionPage) === false) {
                        App::getInstance('logger')->warning('모바일샵을 사용여부를 확인하시기 바랍니다.');
                        header('location:' . URI_HOME);
                        exit();
                    }
                }
                // 모바일 페이지 연결 여부 체크
                $this->redirectChk();

                // 모바일샵 설정
                $_tmpData = gd_policy('mobile.design');
                $_mcfg = array_merge(Globals::get('gSite.mobile.config'), $_tmpData);

                // 기본값으로 초기화
                gd_isset($_mcfg['mobileShopFl'], 'n');
                gd_isset($_mcfg['mobileShopGoodsFl'], 'same');
                gd_isset($_mcfg['mobileShopCategoryFl'], 'same');
                gd_isset($_mcfg['mobileShopSkin'], 'red_place');
                break;

            // 관리자 도메인으로 접속시 처리
            case 'admin':
                break;

            // 프론트 포함한 이외 도메인으로 접속시 처리
            default:
                // PC버전 클릭 체크
                if (Request::get()->has('pcView') === true) {
                    Session::set('pcView', true);
                    if (Request::get()->get('pcView') != 'y') {
                        Session::del('pcView');
                    }
                    Request::get()->del('pcView');
                }

                // 모바일 접속인데 일반샵으로 접속한 경우
                if ((Globals::get('gSite.mobile.config.mobileShopFl')) == 'y' && !Session::has('pcView') && !Request::isMobile() && Request::isMobileDevice()) {
                    $this->mobileConnectCheck();
                }

                // 모바일 페이지 연결 여부 체크
                $this->redirectChk();

                // 접속 여부
                $_is['access'] = false;

                // 모바일샵 설정
                $_mcfg = Globals::get('gSite.mobile.config');
                break;
        }

        // 모바일샵 접속 설정
        Session::set('mobileInfo.mode', ($_is['access'] === true ? 'mobile' : 'front'));
        Session::set('mobileInfo.mobile', ($_is['mobile'] === true ? true : false));
        Session::set('mobileInfo.browser', ($_is['browser'] === true ? 'y' : 'n'));

        $this->_mobileConfig = $_mcfg;
    }

    /**
     * 모바일샵 이동 체크
     */
    public function mobileConnectCheck()
    {

        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');

        // index인 경우 바로 이동
        if (($request->getDirectoryUri() == '' || $request->getDirectoryUri() == 'us' || $request->getDirectoryUri() == 'cn' || $request->getDirectoryUri() == 'jp') && ($request->getFileUri() == 'index.php' || $request->getFileUri() == '')) {
            $_tmp['retUrl'] = $session->has(SESSION_GLOBAL_MALL) ? URI_OVERSEAS_MOBILE : URI_MOBILE;
            if ($request->getQueryString()) {
                $_tmp['retUrl'] = $_tmp['retUrl'] . '?' . $request->getQueryString();
            }
            $logger->alert(sprintf('%s, move index location[%s]', __METHOD__, $_tmp['retUrl']));
            header('location:' . $_tmp['retUrl']);
            exit();
        }

        // 모바일 샵으로 이동 체크
        if ($this->mobilePageList($request->getFileUri(), $request->getDirectoryUri()) === true) {
            $_tmp['retUrl'] = ($session->has(SESSION_GLOBAL_MALL) ? URI_OVERSEAS_MOBILE : URI_MOBILE ) . $request->getDirectoryUri() . '/' . $request->getFileUri();
            if ($request->getQueryString()) {
                $_tmp['retUrl'] = $_tmp['retUrl'] . '?' . $request->getQueryString();
            }
            $logger->alert(sprintf('%s, move mobile page location[%s]', __METHOD__, $_tmp['retUrl']));
            header('location:' . $_tmp['retUrl']);
            exit();
        }
    }

    /**
     * 모바일 페이지 리스트
     *
     * @param string $pageNm   페이지 이름
     * @param string $folderNm 폴더명
     *
     * @return boolean 모바일 페이지 있는지의 여부
     */
    public function mobilePageList($pageNm, $folderNm)
    {
        $arrMobilePage = \App::getConfig('app.mobilepagelist')->toArray();

        $sameFl = false;
        foreach ($arrMobilePage as $key => $val) {
            if ($val['page'] == $pageNm && $val['folder'] == $folderNm) {
                $sameFl = true;
                continue;
            }
        }

        return $sameFl;
    }

    /**
     * 모바일 설정 반환.
     */
    public function getMobileConfig()
    {
        return $this->_mobileConfig;
    }

    /**
     * 모바일 연결 페이지(연결 가능한 페이지 체크)
     *
     * @param $param    url 파라미터
     * @param $mode     기존 시스템 파일 구분값
     * @return mixed
     */
    public function getMobileConnectData($param, $mode = null)
    {
        $param = rawurldecode($param);
        $uri = explode('.', explode('/', $param)[1])[0];
        if($mode == 'current'){
            $uri = explode('.',array_values(array_filter(explode('/', $param)))[1])[0];
        }
        $res['fl'] = false;

        // 모바일 연결 페이지 체크
        $designConnectUrl = new DesignConnectUrl();
        $skinCnf = gd_policy('design.skin');
        $frontSkinWork = $skinCnf['frontWork'];
        $mobileConnectPage = $designConnectUrl->getMobileConnectPageList($frontSkinWork, 1, 10);

        foreach($mobileConnectPage['all'] as $key => $val){
            $arrUrl = explode('.', $val['url']);
            if($uri == $arrUrl[0] && $val['connectFl'] == 'y'){
                $res['url'] = $val['connectPage'];
                $res['fl'] = true;
            }
        }

        return $res;
    }

    public function redirectChk()
    {
        $refererParam = explode('?', Request::server()->get('HTTP_REFERER'));
        parse_str($refererParam[1], $param);

        if(Request::isMobile() && stripos(Request::getQueryString(), 'htmid') !== false && $param['htmid'] != Request::get()->get('htmid')) {
            $res = $this->getMobileConnectData(Request::getQueryString());
            //$mallChk = array_values(array_filter(explode('/', $res['url'])));
            if ($res['fl'] === true) {
                $_tmp['retUrl'] = (Session::has(SESSION_GLOBAL_MALL) ? URI_OVERSEAS_MOBILE : URI_MOBILE) . $res['url'];
                echo '<script>location.href="'.$_tmp['retUrl'].'";</script>';
                exit();
            }
        }
    }

}
