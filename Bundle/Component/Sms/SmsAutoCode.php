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

/**
 * SMS 자동발송 코드별 기본 설정 값 초기화하는 클래스이며 기존 Sms::setSmsAutoCode 함수를 대체하는 클래스
 * 2017-02-17 yjwee 생성자에서 실제 Sms 발송을 하지 않더라도 BoardAdmin 클래스를 생성하여 불필요한 로직이 호출되던 부분을 수정함.
 *
 * @package Bundle\Component\Sms
 * @author  yjwee
 */
class SmsAutoCode
{
    const ORDER = 'order';
    const MEMBER = 'member';
    const PROMOTION = 'promotion';
    const ADMIN = 'admin';
    const BOARD = 'board';
    protected $codes = [];
    protected $boardAdmin;

    /**
     * 자동 sms 관련 설정 값 초기화
     *
     */
    protected function initialize()
    {
        if (empty($this->codes)) {
            $this->initOrder();
            $this->initMember();
            $this->initPromotion();
            $this->initBoard();
            $this->initAdmin();
        }
    }

    /**
     * 주문 관련 설정 값 초기화
     *
     */
    protected function initOrder()
    {
        //@formatter:off
        $this->codes[self::ORDER] = [
            [
                'code'       => Code::ORDER,
                'text'       => __('주문접수'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin',
                'desc'       => __('무통장 입금 주문 건의 주문접수 시'),
            ],
            [
                'code'       => Code::INCASH,
                'text'       => __('입금확인'),
                'orderCheck' => 'y', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('무통장 입금 주문 건의 입금확인 및 카드결제 시'),
            ],
            [
                'code'       => Code::ACCOUNT,
                'text'       => __('입금요청'),
                'orderCheck' => 'y', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => __('무통장 입금 주문 건의 주문접수 시'),
            ],
            [
                'code'       => Code::DELIVERY,
                'text'       => __('상품배송 안내'),
                'orderCheck' => 'y', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('배송중으로 배송상태 변경 시'),
            ],
            [
                'code'       => Code::INVOICE_CODE,
                'text'       => __('송장번호 안내'),
                'orderCheck' => 'y', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'deliveryCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('배송중으로 배송상태 변경 시'),
            ],
            [
                'code'       => Code::DELIVERY_COMPLETED,
                'text'       => __('배송완료'),
                'orderCheck' => 'y', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('배송완료로 배송상태 변경 시'),
            ],
            [
                'code'       => Code::CANCEL,
                'text'       => __('주문취소'),
                'orderCheck' => 'y', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('취소상태로 주문상태 변경 시'),
            ],
            [
                'code'       => Code::REPAY,
                'text'       => __('환불완료'),
                'orderCheck' => 'y', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('환불상태로 주문상태 변경 시'),
            ],
            [
                'code'       => Code::REPAYPART,
                'text'       => __('카드 부분취소'),
                'orderCheck' => 'y', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member_admin_provider',
                'desc'       => __('카드 부분취소 시'),
            ],
            [
                'code'       => Code::SOLD_OUT,
                'text'       => __('상품품절'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'admin_provider',
                'desc'       => __('회원주문으로 인해 상품품절 시'),
            ],
        ];
        //@formatter:on
        $this->codes[self::ORDER][] = $this->getDefaultCode(\Component\Sms\Code::EXCHANGE, __('고객 교환신청'), 'member_admin', __('마이페이지에서 고객이 교환신청 시'));
        $this->codes[self::ORDER][] = $this->getDefaultCode(\Component\Sms\Code::BACK, __('고객 반품신청'), 'member_admin', __('마이페이지에서 고객이 반품신청 시'));
        $this->codes[self::ORDER][] = $this->getDefaultCode(\Component\Sms\Code::REFUND, __('고객 환불신청'), 'member_admin', __('마이페이지에서 고객이 환불신청 시'));
        //@formatter:off
        $check = ['orderCheck' => 'y', 'nightCheck' => 'y',];
        //@formatter:on
        $this->codes[self::ORDER][] = $this->getDefaultCode(\Component\Sms\Code::ADMIN_APPROVAL, __('고객 교환/반품/환불신청 승인'), 'member_admin', __('운영자가 승인처리 시'), $check);
        $this->codes[self::ORDER][] = $this->getDefaultCode(\Component\Sms\Code::ADMIN_REJECT, __('고객 교환/반품/환불신청 거절'), 'member_admin', __('운영자가 거절처리 시'), $check);
    }

    /**
     * 회원 관련 설정 값 초기화
     *
     */
    protected function initMember()
    {
        //@formatter:off
        $this->codes[self::MEMBER] = [
            $this->getDefaultCode(\Component\Sms\Code::JOIN, __('회원가입'), 'member_admin', __('회원가입 시 발송'), ['disapprovalCheck' => 'y']),
            $this->getDefaultCode(\Component\Sms\Code::APPROVAL, __('가입승인'), 'member', __('가입승인 완료 시 발송')),
            $this->getDefaultCode(\Component\Sms\Code::PASS_AUTH, __('비밀번호 찾기 인증번호'), 'member', __('비밀번호 찾기 요청 시 발송')),
            [
                'code'       => \Component\Sms\Code::SLEEP_INFO,
                'text'       => __('휴면회원 전환 사전안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '10',
            ],
            [
                'code'       => \Component\Sms\Code::SLEEP_INFO_TODAY,
                'text'       => __('휴면회원 전환 안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '10',
            ],
            [
                'code'       => \Component\Sms\Code::SLEEP_AUTH,
                'text'       => __('휴면회원 해제 인증번호'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::AGREEMENT2YPERIOD,
                'text'       => __('수신동의여부확인'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '8',
            ],
            [
                'code'       => \Component\Sms\Code::GROUP_CHANGE,
                'text'       => __('회원등급 변경안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '8',
            ],
            [
                'code'       => \Component\Sms\Code::MILEAGE_PLUS,
                'text'       => __('마일리지 지급안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::MILEAGE_MINUS,
                'text'       => __('마일리지 차감안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::MILEAGE_EXPIRE,
                'text'       => __('마일리지 소멸안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '9',
            ],
            [
                'code'       => \Component\Sms\Code::DEPOSIT_PLUS,
                'text'       => __('예치금 지급안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::DEPOSIT_MINUS,
                'text'       => __('예치금 차감안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
        ];
        //@formatter:on
    }

    /**
     * 프로모션 관련 설정 값 초기화
     *
     */
    protected function initPromotion()
    {
        //@formatter:off
        $this->codes[self::PROMOTION] = [
            [
                'code'       => \Component\Sms\Code::COUPON_ORDER_FIRST,
                'text'       => __('첫 구매 축하 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_ORDER,
                'text'       => __('구매 감사 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_BIRTH,
                'text'       => __('생일 축하 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '8',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_JOIN,
                'text'       => __('회원가입 축하 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_LOGIN,
                'text'       => __('출석 체크 감사 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_MEMBER_MODIFY,
                'text'       => __('회원 정보 이벤트 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_WAKE,
                'text'       => __('휴면회원 해제 감사 쿠폰'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_MANUAL,
                'text'       => __('수동쿠폰 발급 안내'),
                'orderCheck' => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member_admin',
                'desc'       => '',
            ],
            [
                'code'       => \Component\Sms\Code::COUPON_WARNING,
                'text'       => __('쿠폰만료 안내'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'y', 'couponCheck' => 'y',
                'sendType'   => 'member',
                'desc'       => '',
                'reserveHour'=> '11',
            ],
            [
                'code'       => \Component\Sms\Code::BIRTH,
                'text'       => __('생일축하'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'y', 'couponCheck' => 'n',
                'sendType'   => 'member',
                'desc'       => __('생일회원 체크 후 발송'),
                'reserveHour'=> '10',
            ],
        ];
        //@formatter:on
    }

    /**
     * 관리자 관련 설정 값 초기화
     *
     */
    protected function initAdmin()
    {
        //@formatter:off
        $this->codes[self::ADMIN] = [
            [
                'code'       => \Component\Sms\Code::SETTLE_BANK,
                'text'       => __('무통장 입금은행 정보 변경'),
                'orderCheck' => 'n', 'nightCheck' => 'n', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'   => 'admin',
                'desc'       => '',
            ],
        ];
        //@formatter:on
    }

    /**
     * 게시판 관련 설정 값 초기화
     *
     */
    protected function initBoard()
    {
        $this->boardAdmin = \App::load('Component\Board\BoardAdmin');
        $boardList = $this->boardAdmin->selectList(null, 0, null, 'asc');
        $this->codes[self::BOARD] = [];
        foreach ($boardList as $row) {
            $_provider = '';
            if ($row['bdGoodsFl'] == 'y') { //상품연동된것만
                $_provider = '_provider';
            }
            $memberContents = sprintf(__("[%s]\n%s님의 %s에 답변이 등록되었습니다."), '{shopName}', '{rc_mallNm}', $row['bdNm']);
            $adminContents = sprintf(__("%s게시판에 %s님이 새로운 글을 등록했습니다."), $row['bdNm'], '{rc_mallNm}');
            $providerContents = sprintf(__("[%s]\n%s게시판에 %s님이 새로운 글을 등록했습니다."), '{shopName}', $row['bdNm'], '{rc_mallNm}');
            //@formatter:off
            $this->codes[self::BOARD][$row['bdId']] = [
                'code'             => $row['bdId'],
                'text'             => $row['bdNm'],
                'memberContents'   => $memberContents,
                'adminContents'    => $adminContents,
                'providerContents' => $providerContents,
                'orderCheck'       => 'n', 'nightCheck' => 'y', 'agreeCheck' => 'n', 'couponCheck' => 'n',
                'sendType'         => 'member_admin' . $_provider,
                'smsType'          => 'board',
                'desc'             => '',
            ];
            //@formatter:on
        }
    }

    /**
     * 설정 값 기본 데이터 반환 함수
     *
     * @param        $code
     * @param        $text
     * @param        $sendType
     * @param string $desc
     * @param array  $check
     *
     * @return array
     */
    protected function getDefaultCode($code, $text, $sendType, $desc = '', array $check = [])
    {
        $defaultCheck = [
            'orderCheck'       => 'n',
            'nightCheck'       => 'n',
            'agreeCheck'       => 'n',
            'couponCheck'      => 'n',
            'disapprovalCheck' => 'n',
        ];
        foreach ($defaultCheck as $index => $item) {
            if (key_exists($index, $check)) {
                $defaultCheck[$index] = $check[$index];
            }
        }
        $default = [
            'code'     => $code,
            'text'     => $text,
            'sendType' => $sendType,
            'desc'     => $desc,
        ];
        $result = array_merge($default, $defaultCheck);

        return $result;
    }

    /**
     * SMS 자동발송 설정을 반환하는 함수
     * @deprecated 기존 Sms::setSmsAutoCode 를 사용하던 곳에서 쓰기위해 만든 함수
     * @uses       SmsAutoCode::getCodes
     * @static
     * @return array
     */
    public static function getSmsAutoCode()
    {
        $smsAutoCode = new SmsAutoCode();

        return $smsAutoCode->getCodes();
    }

    /**
     * SMS 자동발송 설정을 반환하는 함수
     *
     * @return array
     */
    public function getCodes()
    {
        $this->initialize();

        return $this->codes;
    }
}
