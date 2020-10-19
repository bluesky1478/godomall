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

namespace Bundle\Component\Agreement;

use App;
use Logger;

/**
 * Class 약관 관련 코드 클래스
 *
 * @package Bundle\Component\Agreement
 * @author  yjwee
 */
class BuyerInformCode
{
    /** 이용약관 */
    const AGREEMENT = '001001';
    /** 개인정보취급방침 */
    const BASE_PRIVATE = '001002';
    /** 이용자 동의사항 */
    const PRIVATE_APPROVAL = '001003';
    /** 이용자 동의사항 선택항목 */
    const PRIVATE_APPROVAL_OPTION = '001006';
    /** 제3자 제공 */
    const PRIVATE_OFFER = '001004';
    /** 취급업무 위탁 */
    const PRIVATE_CONSIGN = '001005';
    /** 개인정보 수집이용 동의(비회원 주문) */
    const PRIVATE_GUEST_ORDER = '001007';
    /** 개인정보 수집이용 동의(비회원 게시글 등록) */
    const PRIVATE_GUEST_BOARD_WRITE = '001008';
    /** 개인정보 수집이용 동의(비회원 댓글 등록) */
    const PRIVATE_GUEST_COMMENT_WRITE = '001009';
    /** 회원/비회원 주문 시 상품 공급사 개인정보 제공 동의 */
    const PRIVATE_PROVIDER = '001010';
    /** 마케팅 활용을 위한 개인정보 수집 · 이용 동의 (비회원 주문 시) */
    const PRIVATE_MARKETING = '001011';
    /** 이용안내 */
    const BASE_GUIDE = '006001';
    /** 탈퇴안내 */
    const HACK_OUT_GUIDE = '007001';
    /** 회사소개 */
    const COMPANY = '010001';

    /** @var  string 약관 상세 코드 및 약관 상세 코드 프리픽스 */
    private $_informCd;
    /** @var  string 약관 기본명칭 */
    private $_informNm;
    /** @var  string 약관 그룹코드 */
    private $_groupCd;

    /**
     * @param null $code
     */
    function __construct($code = null)
    {
        Logger::info(__METHOD__ . ', ' . $code);
        if (is_null($code) === false) {
            $code = self::removeCodeSuffix($code);
            $this->initCode($code);
        }
    }

    /**
     * 코드에 해당하는 멤버변수 초기화 함수를 실행한다.
     *
     * @param $code
     */
    public function initCode($code)
    {
        switch ($code) {
            case self::AGREEMENT:
                $this->initAgreement();
                break;
            case self::BASE_PRIVATE:
                $this->initPrivate();
                break;
            case self::PRIVATE_APPROVAL:
                $this->initPrivateApproval();
                break;
            case self::PRIVATE_APPROVAL_OPTION:
                $this->initPrivateApprovalOption();
                break;
            case self::PRIVATE_OFFER:
                $this->initPrivateOffer();
                break;
            case self::PRIVATE_CONSIGN:
                $this->initPrivateConsign();
                break;
            case self::PRIVATE_GUEST_ORDER:
                $this->initPrivateGuestOrder();
                break;
            case self::PRIVATE_GUEST_BOARD_WRITE:
                $this->initPrivateGuestBoardWrite();
                break;
            case self::PRIVATE_GUEST_COMMENT_WRITE:
                $this->initPrivateGuestCommentWrite();
                break;
            case self::PRIVATE_PROVIDER:
                $this->initPrivateProvider();
                break;
            case self::BASE_GUIDE:
                $this->initBaseGuideCode();
                break;
            case self::HACK_OUT_GUIDE:
                $this->initHackOutGuideCode();
                break;
            case self::COMPANY:
                $this->initCompanyCode();
                break;
            case self::PRIVATE_MARKETING:
                $this->initPrivateMarketing();
                break;
        }
    }

    /**
     * 멤버 변수에 이용약관 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initAgreement()
    {
        $this->_informCd = self::AGREEMENT;
        $this->_informNm = __('이용약관');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 개인정보취급방침 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function  initPrivate()
    {
        $this->_informCd = self::BASE_PRIVATE;
        $this->_informNm = __('개인정보처리방침');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 이용자 동의사항 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateApproval()
    {
        $this->_informCd = self::PRIVATE_APPROVAL;
        $this->_informNm = __('개인정보 수집 및 이용');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 이용자 동의사항 선택 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateApprovalOption()
    {
        $this->_informCd = self::PRIVATE_APPROVAL_OPTION;
        $this->_informNm = __('선택적 개인정보의 수집목적 및 이용목적');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 제3자 제공 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateOffer()
    {
        $this->_informCd = self::PRIVATE_OFFER;
        $this->_informNm = __('선택적 개인정보 제 3자 제공');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 취급업무 위탁 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateConsign()
    {
        $this->_informCd = self::PRIVATE_CONSIGN;
        $this->_informNm = __('선택적 개인정보 취급위탁');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 개인정보 수집이용 동의(비회원 주문) 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateGuestOrder()
    {
        $this->_informCd = self::PRIVATE_GUEST_ORDER;
        $this->_informNm = __('개인정보 수집이용 동의(비회원 주문)');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 개인정보 수집이용 동의(비회원 게시글 등록) 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateGuestBoardWrite()
    {
        $this->_informCd = self::PRIVATE_GUEST_BOARD_WRITE;
        $this->_informNm = __('개인정보 수집이용 동의(비회원 게시글 등록)');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 개인정보 수집이용 동의(비회원 댓글 등록) 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateGuestCommentWrite()
    {
        $this->_informCd = self::PRIVATE_GUEST_COMMENT_WRITE;
        $this->_informNm = __('개인정보 수집이용 동의(비회원 댓글 등록)');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 회원/비회원 주문 시 상품 공급사 개인정보 제공 동의 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateProvider()
    {
        $this->_informCd = self::PRIVATE_PROVIDER;
        $this->_informNm = __('회원/비회원 주문 시 상품 공급사 개인정보 제공 동의');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수에 이용안내 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initBaseGuideCode()
    {
        $this->_informCd = self::BASE_GUIDE;
        $this->_informNm = __('이용안내');
        $this->_groupCd = '006';
    }

    /**
     * 멤버 변수에 탈퇴안내 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initHackOutGuideCode()
    {
        $this->_informCd = self::HACK_OUT_GUIDE;
        $this->_informNm = __('탈퇴안내');
        $this->_groupCd = '007';
    }

    /**
     * 멤버 변수에 회사소개 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initCompanyCode()
    {
        $this->_informCd = self::COMPANY;
        $this->_informNm = __('회사소개');
        $this->_groupCd = '010';
    }

    /**
     * 멤버 변수에 마케팅 활용을 위한 개인정보 수집 · 이용 동의 (비회원 주문 시) 관련 데이터베이스 조회 데이터 설정
     *
     */
    public function initPrivateMarketing()
    {
        $this->_informCd = self::PRIVATE_MARKETING;
        $this->_informNm = __('마케팅 활용을 위한 개인정보 수집 · 이용 동의');
        $this->_groupCd = '001';
    }

    /**
     * 멤버 변수를 배열로 반환하는 함수
     *
     * @static
     *
     * @param null $code 코드 값을 전달할 경우 해당 코드의 데이터를 멤버변수에 설정하여 반환한다.
     *
     * @return array
     */
    public static function toArray($code = null)
    {
        $code = new BuyerInformCode($code);

        return [
            $code->_informCd,
            $code->_informNm,
            $code->_groupCd,
        ];
    }

    /**
     * 컬럼명을 키로 멤버 변수를 배열 데이터로 반환하는 함수
     *
     * @static
     *
     * @param null $code 코드 값을 전달할 경우 해당 코드의 데이터를 멤버변수에 설정하여 반환한다.
     *
     * @return array
     */
    public static function toKeyArray($code = null)
    {
        $code = new BuyerInformCode($code);

        return [
            'informCd' => $code->_informCd,
            'informNm' => $code->_informNm,
            'groupCd'  => $code->_groupCd,
        ];
    }

    /**
     * @return string
     */
    public function getGroupCd()
    {
        return $this->_groupCd;
    }

    /**
     * @return string
     */
    public function getInformCd()
    {
        return $this->_informCd;
    }

    /**
     * @return string
     */
    public function getInformNm()
    {
        return $this->_informNm;
    }

    /**
     * removeCodeSubfix
     *
     * @param $code
     *
     * @return string
     */
    public static function removeCodeSuffix($code)
    {
        if (strlen($code) > 6) {
            $code = substr($code, 0, 6);

            return $code;
        }

        return $code;
    }
}
