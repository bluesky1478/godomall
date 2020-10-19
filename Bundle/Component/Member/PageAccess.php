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

namespace Bundle\Component\Member;


use Bundle\Component\Godo\GodoKakaoServerApi;
use Bundle\Component\Godo\GodoNaverServerApi;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Member\Util\MemberUtil;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\RedirectLoginException;
use Globals;
use Logger;
use Request;
use Session;
use Exception;

/**
 * Class PageAccess
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class PageAccess
{
    /**
     * PageAccess constructor.
     */
    function __construct()
    {
    }

    /**
     * 페이지별 접속환경
     * 코드 : 회원(x,0,레벨-순서)|비회원(0,g,p,x) , 0은 로그인 정보가 없는경우 (x-불가,g-비회원)
     * 모두 : 0|0
     * 회원 + 비회원 : 0|g
     * 비회원만 : x|g
     * 회원만 : 0|x
     *
     * @author artherot, sunny
     *
     * @param string $page 페이지(directory/filename)
     * @param string $dirName 최상위 폴더명
     *
     * @return array
     */
    public static function getPageAccess($page, $dirName = '')
    {
        // x:접속불가, 0~:접속가능(회원등급순번), g:Session Guest
        $pageAccess = [
            'intro/adult.php'                         => [
                'member'   => 'x',
                'guest'    => 'x',
                'intro'    => 'x',
                'pageType' => 'adult',
            ],
            'intro/member.php'                        => [
                'member'   => 'x',
                'guest'    => 'x',
                'intro'    => 'x',
                'pageType' => 'member',
            ],
            'intro/walkout.php'                       => [
                'member'   => 'x',
                'guest'    => 'x',
                'intro'    => 'x',
                'pageType' => 'walkout',
            ],
            'main/index.php'                          => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => '0',
            ],
            'goods/goods_list.php'                    => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => '0',
            ],
            'goods/goods_view.php'                    => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => '0',
            ],
            'goods/bandwagon_push.php'                => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => '0',
            ],
            'member/login.php'                        => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => '0',
            ],
            'member/login_ps.php'                     => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'member/find_id.php'                      => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/find_password.php'                => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/find_password_reset.php'          => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/find_password_reset_ps.php'       => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/find_ps.php'                      => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'member/user_certification.php'           => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/user_certification_ps.php'        => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/user_certification_confirm.php'   => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/wake.php'                         => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/wake_certification.php'           => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/wake_complete.php'                => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'authcellphone/dreamsecurity_result.php'  => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'ipin/ipin_main.php'                      => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'ipin/ipin_process.php'                   => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'ipin/ipin_apply.php'                     => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'ipin/ipin_result.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'member/find_reset.php'                   => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/join.php'                         => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/join_method.php'                  => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/join_agreement.php'               => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/join_ok.php'                      => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/join_wait.php'                    => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/member_ps.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'share/date_select_json.php'              => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'payco/payco_login.php'                   => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'payco/payco_connect.php'                 => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'payco/payco_disconnect.php'              => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'facebook/join_callback.php'              => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'facebook/login_callback.php'             => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'facebook/re_authentication_callback.php' => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'facebook/connect_callback.php'           => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'facebook/dis_connect.php'                => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'naver/naver_login.php'                   => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'kakao/kakao_login.php'                   => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'kakao/kakaoo_login.php'                   => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'wonder/wonder_login.php'                   => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'member/wonder_join_ok.php'                   => [
                'member' => 'x',
                'guest'  => '0',
                'intro'  => 'x',
            ],
            'mypage/my_page.php'                      => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/my_page_password.php'             => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/mypage_goods_qa.php'              => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/mypage_goods_review.php'          => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/mypage_qa.php'                    => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/hack_out.php'                     => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/index.php'                        => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/wish_list.php'                    => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/order_list.php'                   => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/order_view.php'                   => [
                'member' => '0',
                'guest'  => 'g',
                'intro'  => '0',
            ],
            'mypage/order_change.php'                 => [
                'member' => '0',
                'guest'  => 'g',
                'intro'  => '0',
            ],
            'mypage/coupon.php'                       => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/qna.php'                          => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/qna_goods.php'                    => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/qna_write.php'                    => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/review.php'                       => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/deposit.php'                      => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/mileage.php'                      => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/coupon_regist.php'                => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'mypage/shipping.php'                     => [
                'member' => '0',
                'guest'  => 'x',
                'intro'  => '0',
            ],
            'order/cart.php'                          => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => '0',
            ],
            'order/order.php'                         => [
                'member' => '0',
                'guest'  => 'g',
                'intro'  => '0',
            ],
            'order/order_end.php'                     => [
                'member' => '0',
                'guest'  => 'g',
                'intro'  => '0',
            ],
            'popup/popup_ps.php'                      => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'popup/popup.php'                         => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'popup/multi_popup_ps.php'                => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'popup/multi_popup.php'                   => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'payco/pg_return_ps.php'                  => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'payco/payco_area_delivery_ps.php'        => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'kcp/pg_vbank_return.php'                 => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'inicis/pg_vbank_return.php'              => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'inicis/pg_return_noti.php'               => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'allthegate/pg_vbank_return.php'          => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'lguplus/pg_vbank_return.php'             => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'cyrexpay/pg_return.php'                  => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'allpay/pg_return.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'mobilians/pg_notice.php'                 => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'billgate/pg_notice.php'                 => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'outconn/bank_sock.php'                   => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            '/check_ssl_free.php'                     => [ // 디렉토리가 없을 경우 /로 시작한다.
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/naver_summary.php'               => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/naver_all.php'                   => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/daum_some.php'                   => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/daum_all.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/payco_summary.php'                   => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/payco_all.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/paycosearch_all.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/naverpay_goods_link.php'          => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/tg.php'                          => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'partner/criteo.php'                    => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            '/robots.txt'                            => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'share/postcode_search_ps.php'              => [
                'member' => 'x',
                'guest'  => 'x',
                'intro'  => 'x',
            ],
            'myapp/myapp_login.php'                     => [
                'member' => '0',
                'guest'  => '0',
                'intro'  => 'x',
            ],
        ];

        if ($dirName == 'payment') {
            gd_isset(
                $pageAccess[$page],
                [
                    'member' => 'x',
                    'guest' => 'x',
                    'intro' => 'x',
                ]
            );
        } else {
            // 미정의된 페이지는 접속허용
            gd_isset(
                $pageAccess[$page],
                [
                    'member' => '0',
                    'guest' => '0',
                    'intro' => '0',
                ]
            );
        }

        return $pageAccess[$page];
    }

    /**
     * 회원 인증 체크
     * 회원로그인 되어있는지를 체크를 하며, 잘못된 로그인 일경우 회원 세션을 초기화함
     *
     * @static
     * @throws AlertRedirectException
     * @author artherot, sunny
     */
    public static function setCertification()
    {
        // 현재 접속 경로가 admin이 아닌 경우만 실행 처리 (관리자에서 프론트 세션체크를 진행해 원활한 관리자 기능 수행 어려움)
        if (\App::getController()->getRootDirecotory() != 'admin') {
            if (Session::has('member')) {
                if (Session::get('member') === null) {
                    Logger::error(__METHOD__ . ', session member is null, Referer=' . Request::getReferer());
                    Session::del('member');

                    return;
                }

                if (Session::get('member.memNo') === null) {
                    Logger::error(__METHOD__ . ', session member.memNo is null, Referer=' . Request::getReferer());
                    Session::del('member');

                    return;
                }

                // --- 회원인증 유지시간 체크(자동로그아웃)
                self::checkMemberExpireTime();

                // --- 회원 존재 여부 체크
                $data = MemberUtil::getMemberBySession("memId");

                // --- 정보가 없는 경우
                if (empty($data) || empty($data['memId'])) {
                    Logger::error(__METHOD__ . ', session member.memId is empty, Referer=' . Request::getReferer());
                    Session::del('member');
                } else {
                    // --- 비회원 로그아웃
                    MemberUtil::logoutGuest();
                    // --- 본인확인 정보 세션 삭제
                    if (Session::has('certAdult')) {
                        Session::del('certAdult');
                    }
                }
                // --- 카카오 아이디 인증 체크(자동로그아웃)
                $memberSessionInfo = Session::get(Member::SESSION_MEMBER_LOGIN);
                if(gd_is_plus_shop(PLUSSHOP_CODE_KAKAOLOGIN) === true && $memberSessionInfo['snsTypeFl'] == 'kakao') {
                    self::checkKakaoIdValidation($memberSessionInfo);
                }
            }
        }
    }

    /**
     * 페이지 접속 체크(회원 로그인)
     * 해당 조건에 부적합하게 되면 리다이렉트
     *
     * @throws AlertRedirectException
     */
    public static function chkPageAccess()
    {
        $globals = \App::getInstance('globals');
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');

        $isAccess = true;
        $accessType = '';
        $dirName = $request->getDirectoryByDepth();

        $thisPage = $request->getDirectoryUri() . '/' . $request->getFileUri();
        if ($thisPage == 'us/us' || $thisPage == 'cn/cn' || $thisPage == 'jp/jp') {
            $thisPage = '/';
        }
        $code = PageAccess::getPageAccess($thisPage, $dirName);
        $moveUrl = ''; // 접근불가일 때 이동할 경로

        $pathHome = $request->getDomainUrl() . DS;

        // 멀티상점인 경우 기본 패스에 해당 상점의 도메인(us|kr|jp|cn...) 추가
        $pathHome .= (empty($request->getMallNameByUri()) === false ? $request->getMallNameByUri() . DS : '');

        // 모바일샵 접속인 경우
        if ($request->isMobile()) {
            if ($session->has(SESSION_GLOBAL_MALL) && $globals->get('gSite.member.access.introMobileAccess') == 'adult') {
                $globals->set('gSite.member.access.introMobileAccess', 'member');
            }
            // 인트로사용 & 성인 접속허용
            if ($isAccess === true && $code['intro'] == '0' && $globals->get('gSite.member.access.introMobileUseFl') === 'y' && $globals->get('gSite.member.access.introMobileAccess') === 'adult') {
                if (!$session->has('certAdult') && (!$session->has('member') || ($session->has('member') && $session->get('member.adultFl') == 'n'))) {
                    $isAccess = false;
                    $returnUrl = preg_match('~intro/adult.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                    $moveUrl = $pathHome . 'intro/adult.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                    $logger->info(__METHOD__ . ', access mobile. has not adult certification. move[' . $moveUrl . ']');
                }
            } else if ($isAccess === true && $thisPage == 'intro/adult.php' && ($globals->get('gSite.member.access.introMobileUseFl') !== 'y' || $globals->get('gSite.member.access.introMobileAccess') !== 'adult')) {
                if (preg_match('~goods/goods_view.php~', $request->getReturnUrl()) == 1) {
                    // 성인상품은 성인인증하도록 허용
                } else {
                    $isAccess = false;
                    if ($globals->get('gSite.member.access.introMobileUseFl') === 'y' && $globals->get('gSite.member.access.introMobileAccess') === 'free') {
                        $moveUrl = $pathHome;
                        $code['member'] = $code['guest'] = '0';
                    } else {
                        $moveUrl = $pathHome . 'main/index.php';
                    }
                    $logger->info(__METHOD__ . ', access mobile. not use intro(adult). move[' . $moveUrl . ']');
                }
            }

            // 인트로사용 & 회원만 접속허용
            if ($isAccess === true && $code['intro'] == '0' && $globals->get('gSite.member.access.introMobileUseFl') === 'y' && $globals->get('gSite.member.access.introMobileAccess') === 'member') {
                // 비회원 로그아웃
                MemberUtil::logoutGuest();

                // 로그인 세션이 없는경우 회원전용 인트로로 이동
                if (!$session->has('member')) {
                    $isAccess = false;
                    $returnUrl = preg_match('~intro/member.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                    $moveUrl = $pathHome . 'intro/member.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                    $logger->info(__METHOD__ . ', access mobile. has not member session. move[' . $moveUrl . ']');
                }
            } else if ($isAccess === true && $thisPage == 'intro/member.php' && ($globals->get('gSite.member.access.introMobileUseFl') !== 'y' || $globals->get('gSite.member.access.introMobileAccess') !== 'member')) {
                $isAccess = false;
                if ($globals->get('gSite.member.access.introMobileUseFl') === 'y' && $globals->get('gSite.member.access.introMobileAccess') === 'free') {
                    $moveUrl = $pathHome;
                    $code['member'] = $code['guest'] = '0';
                } else {
                    $moveUrl = $pathHome . 'main/index.php';
                }
                $logger->info(__METHOD__ . ', access mobile. not use intro(member). move[' . $moveUrl . ']');
            }

            // 접속불가
            if ($isAccess === true && $code['intro'] == '0' && $globals->get('gSite.member.access.introMobileUseFl') === 'y' && $globals->get('gSite.member.access.introMobileAccess') === 'walkout') {
                if ($session->has('manager')) {
                    $isAccess = true;
                } else {
                    $isAccess = false;
                    $moveUrl = $pathHome . 'intro/walkout.php';
                    $logger->info(__METHOD__ . ', access mobile. use walkout option. move[' . $moveUrl . ']');
                }
            } else if ($isAccess === true && $thisPage == 'intro/walkout.php' && ($globals->get('gSite.member.access.introMobileUseFl') !== 'y' || $globals->get('gSite.member.access.introMobileAccess') !== 'walkout')) {
                $isAccess = false;
                if ($globals->get('gSite.member.access.introMobileUseFl') === 'y' && $globals->get('gSite.member.access.introMobileAccess') === 'free') {
                    $moveUrl = $pathHome;
                    $code['member'] = $code['guest'] = '0';
                } else {
                    $moveUrl = $pathHome . 'main/index.php';
                }
                $logger->info(__METHOD__ . ', access mobile. not use intro(walkout). move[' . $moveUrl . ']');
            }

        } // 일반샵 접속인 경우
        else {
            if ($session->has(SESSION_GLOBAL_MALL) && $globals->get('gSite.member.access.introFrontAccess') === 'adult') {
                $globals->set('gSite.member.access.introFrontAccess', 'member');
            } else {
                // 인트로사용 & 성인/회원만 접속허용
                if ($isAccess === true && $code['intro'] == '0' && $globals->get('gSite.member.access.introFrontUseFl') === 'y' && $globals->get('gSite.member.access.introFrontAccess') === 'adult') {
                    $logger->info(__METHOD__, [$session->get('member'), $session->get('certAdult')]);
                    if (!$session->has('certAdult') && (!$session->has('member') || ($session->has('member') && $session->get('member.adultFl') == 'n'))) {
                        $isAccess = false;
                        $returnUrl = preg_match('~intro/adult.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                        $moveUrl = $pathHome . 'intro/adult.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                        $logger->info(__METHOD__ . ', access front. has not adult certification. move[' . $moveUrl . ']');
                    }
                }
            }
            // 인트로사용 & 회원만 접속허용
            if ($isAccess === true && $code['intro'] == '0' && $globals->get('gSite.member.access.introFrontUseFl') === 'y' && $globals->get('gSite.member.access.introFrontAccess') === 'member') {
                // 비회원 로그아웃
                MemberUtil::logoutGuest();

                // 로그인 세션이 없는경우 회원전용 인트로로 이동
                if (!$session->has(Member::SESSION_MEMBER_LOGIN)) {
                    $isAccess = false;
                    $returnUrl = preg_match('~intro/member.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                    $moveUrl = $pathHome . 'intro/member.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                    $logger->info(__METHOD__ . ', access front. use intro. has not member session. move[' . $moveUrl . ']');
                }
            }

            // 접속불가
            if ($isAccess === true && $code['intro'] == '0' && $globals->get('gSite.member.access.introFrontUseFl') === 'y' && $globals->get('gSite.member.access.introFrontAccess') === 'walkout') {
                if ($session->has('manager')) {
                    $isAccess = true;
                } else {
                    $isAccess = false;
                    $moveUrl = $pathHome . 'intro/walkout.php';
                    $logger->info(__METHOD__ . ', access front. use walkout option. move[' . $moveUrl . ']');
                }
            }
        }

        // 회원만 접속허용
        if ($isAccess === true && $code['member'] != 'x' && $code['guest'] == 'x') {
            // --- 비회원 로그아웃
            MemberUtil::logoutGuest();
            // 회원 허용
            if ($isAccess === true && !$session->has('member')) {
                $isAccess = false;
                $returnUrl = preg_match('~member/login.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                $moveUrl = $pathHome . 'member/login.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                $logger->info(__METHOD__ . ', only access member. move[' . $moveUrl . ']');
            }
            // 회원등급
            if ($isAccess === true && $session->get('member.groupSort') < $code['member']) {
                $isAccess = false;
                $logger->info(__METHOD__ . ',  only access member. member group sort < ' . $code['member']);
            }
        }

        // 비회원만 접속허용 (로그인페이지, 회원 가입페이지 등등)
        if ($isAccess === true && $code['member'] == 'x' && $code['guest'] != 'x') {
            // 비회원 허용
            if ($isAccess === true && $session->has('member')) {
                $isAccess = false;
                $logger->info(__METHOD__ . ', only access guest. has member session');
            }
            // 게스트 허용
            if ($isAccess === true && $code['guest'] == 'g' && !$session->has('guest')) {
                $isAccess = false;
                $returnUrl = preg_match('~member/login.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                if ($request->getDirectoryUri() == 'order' && ($request->getFileUri() == 'order.php' || $request->getFileUri() == 'order_ps.php')) {
                    $moveUrl = $pathHome . 'member/login.php?guestOrder=1&returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                } else {
                    $moveUrl = $pathHome . 'member/login.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                }
                $logger->info(__METHOD__ . ', only access guest. has not guest session. move[' . $moveUrl . ']');
            }
        }

        // 모두(회원&비회원) 접속허용
        // 모두 접속 허용이면 $code 설정이 회원, 비회원 모두 x가 아니어야 하기 때문에 and 조건으로 변경
        if ($isAccess === true && ($code['member'] != 'x' || $code['guest'] != 'x')) {
            // 게스트 허용
            if ($isAccess === true && $code['guest'] == 'g' && MemberUtil::checkLogin() === false) {
                $isAccess = false;
                $returnUrl = preg_match('~member/login.php~', $request->getReturnUrl()) == 1 ? '' : $request->getReturnUrl();
                if ($request->getDirectoryUri() == 'order' && ($request->getFileUri() == 'order.php' || $request->getFileUri() == 'order_ps.php')) {
                    $moveUrl = $pathHome . 'member/login.php?guestOrder=1&returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                    if (stripos($request->getReferer(), 'goodsNo=') !== false) {
                        $orderReferer = explode('=', $request->getReferer());
                        $moveUrl .= '&goodsNo=' . $orderReferer[1];
                    }
                } else {
                    $moveUrl = $pathHome . 'member/login.php?returnUrl=' . urlencode($request->getReturnUrl($returnUrl));
                }
                $logger->info(__METHOD__ . ', access all. code guest == \'g\'. has not member, guest session. move[' . $moveUrl . ']');
            }
        }

        // 접근불가(location)
        if ($isAccess === false) {
            // admin referer 방지
            if (preg_match('~/admin/~', $request->getReferer())) {
                $request->getReferer('');
            }

            // Location
            if ($thisPage != '/' && preg_match('~' . $thisPage . '~', $moveUrl) == 1) {
                $moveUrl = '';
            }

            $moveUrl = (empty($moveUrl) === true ? $pathHome . 'main/index.php' : $moveUrl);
            $moveUrl = (empty($moveUrl) === true ? $request->getReferer() : $moveUrl);

            // 리턴페이지가 처리페이지 인경우 메인으로
            if (preg_match('~_ps.php~', $moveUrl) == 1) {
                $moveUrl = $pathHome . 'main/index.php';
            }

            // 리턴페이지가 제외 인경우 메인으로
            if ($code['member'] === 'x' || $code['guest'] == 'x') {
                $moveUrl = $pathHome . 'main/index.php';
            }

            // mypage 접근 불가 시 로그인페이지 이동
            if (preg_match('~/mypage/~', $request->getRequestUri())) {
                $moveUrl = $pathHome . 'member/login.php';
            }

            // 인트로(성인/회원),인트로(회원) 인경우
            if (in_array(
                $accessType,
                [
                    'adult',
                    'member',
                ]
            )) {
                $moveUrl = $pathHome . 'main/index.php';
            }
            // Move
            throw new AlertRedirectException(null, null, null, $moveUrl);
        }
    }

    /**
     * checkMemberExpireTime
     *
     * @static
     * @throws AlertRedirectException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public static function checkMemberExpireTime()
    {
        $globals = \App::getInstance('globals');
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        if ($globals->get('gSite.member.access.sessTimeUseFl') == 'y' && $globals->get('gSite.member.access.sessTime') > 0 && $session->has('expireTime')) {
            if (preg_match('/logout.php/', $request->getPhpSelf()) == 0) {
                if ($globals->get('gSite.member.access.sessTime') * 60 < time() - $session->get('expireTime')) {
                    if ($request->isMobile()) {
                        $pathHome = URI_OVERSEAS_MOBILE;
                    } else {
                        $pathHome = URI_OVERSEAS_HOME;
                    }

                    // 마이앱 로그아웃 스크립트
                    $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
                    if (Request::isMyapp() && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false) {
                        $myapp = \App::load('Component\\Myapp\\Myapp');
                        // 로그인 타입
                        if (Session::get(Member::SESSION_MYAPP_SNS_LOGIN) == 'y') {
                            $myappScript = $myapp->getAppBridgeScript('autoSnsLogout');
                            Session::del(Member::SESSION_MYAPP_SNS_LOGIN);
                        } else {
                            $myappScript = $myapp->getAppBridgeScript('autoLogout');
                        }
                        echo $myappScript;
                        exit;
                    }

                    $logger->info(__METHOD__ . ', ' . $pathHome);
                    throw new AlertRedirectException(__('오랜 시간동안 응답이 없어서 자동로그아웃 됩니다.'), null, null, $pathHome . 'member/logout.php?returnUrl=' . urlencode($request->getRequestUri()) . '&noMessage=y', 'top');
                }
                $session->set('expireTime', time());
            }
        }
    }

    /**
     * kakao 회원 인증
     * es_memberSns에 회원 데이터가 없다면 카카오 아이디 로그인 연결끊기 처리되어 재로그인 필요
     * @throws AlertRedirectException
     */
    public static function checkKakaoIdValidation($memberSessionInfo)
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $memberSns = \App::load('\\Component\\Member\\MemberSnsService');
        if(!$memberSns->getMemberSns($memberSessionInfo['memNo'])) {
            $logger->channel('kakao')->info(__METHOD__. ' : unRegister kakao member', $memberSessionInfo['memNo']);
            $session->del(Member::SESSION_MEMBER_LOGIN);
            throw new AlertRedirectException(__('유효하지 않은 계정입니다. 재로그인해주세요.'), null, null, '../member/logout.php?returnUrl=member/login.php');
        }
    }
}
