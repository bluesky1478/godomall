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

namespace Bundle\Component\Database;

use Framework\Object\SingletonTrait;

/**
 * Class MemberTableField
 * @package Bundle\Component\Database
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class MemberTableField
{
    use SingletonTrait;

    public function __construct() { }

    /**
     * [회원] member 필드 기본값
     *
     * @author sunny
     * @return array member 테이블 필드 정보
     */
    public function tableMember()
    {
        $arrField = [
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'memId',
                'typ' => 's',
                'def' => null,
            ],
            // 아이디
            [
                'val' => 'groupSno',
                'typ' => 'i',
                'def' => 1,
            ],
            // 등급 sno
            [
                'val' => 'groupModDt',
                'typ' => 's',
                'def' => null,
            ],
            // 등급수정일
            [
                'val' => 'groupValidDt',
                'typ' => 's',
                'def' => null,
            ],
            // 등급유효일
            [
                'val' => 'memNm',
                'typ' => 's',
                'def' => '',
            ],
            // 이름
            [
                'val' => 'nickNm',
                'typ' => 's',
                'def' => '',
            ],
            // 닉네임
            [
                'val' => 'memPw',
                'typ' => 's',
                'def' => '',
            ],
            // 비밀번호
            [
                'val' => 'changePasswordDt',
                'typ' => 's',
                'def' => '',
            ],
            // 비밀번호변경일
            [
                'val' => 'guidePasswordDt',
                'typ' => 's',
                'def' => '',
            ],
            // 비밀번호변경안내일
            [
                'val' => 'appFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 가입승인
            [
                'val' => 'approvalDt',
                'typ' => 's',
                'def' => 'n',
            ],
            // 가입승인일
            [
                'val' => 'memberFl',
                'typ' => 's',
                'def' => 'personal',
            ],
            // 간편가입여부
            [
                'val' => 'simpleJoinFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 회원구분
            [
                'val' => 'entryBenefitOfferDt',
                'typ' => 's',
                'def' => null,
            ],
            // 가입혜택지급일
            [
                'val' => 'sexFl',
                'typ' => 's',
                'def' => null,
            ],
            // 성별
            [
                'val' => 'birthDt',
                'typ' => 's',
                'def' => null,
            ],
            // 생년월일
            [
                'val' => 'calendarFl',
                'typ' => 's',
                'def' => 's',
            ],
            // 양력,음력
            [
                'val' => 'birthEventFl',
                'typ' => 's',
                'def' => null,
            ],
            // 생일이벤트 제공 일자
            [
                'val' => 'email',
                'typ' => 's',
                'def' => null,
            ],
            // 이메일
            [
                'val' => 'zipcode',
                'typ' => 's',
                'def' => null,
            ],
            // 우편번호
            [
                'val' => 'zonecode',
                'typ' => 's',
                'def' => null,
            ],
            // 우편번호(5자리)
            [
                'val' => 'address',
                'typ' => 's',
                'def' => null,
            ],
            // 주소
            [
                'val' => 'addressSub',
                'typ' => 's',
                'def' => null,
            ],
            // 상세주소
            [
                'val' => 'phone',
                'typ' => 's',
                'def' => null,
            ],
            // 전화번호
            [
                'val' => 'cellPhone',
                'typ' => 's',
                'def' => null,
            ],
            // 휴대폰
            [
                'val' => 'fax',
                'typ' => 's',
                'def' => null,
            ],
            // 팩스번호
            [
                'val' => 'company',
                'typ' => 's',
                'def' => null,
            ],
            // 회사명
            [
                'val' => 'service',
                'typ' => 's',
                'def' => null,
            ],
            // 업체
            [
                'val' => 'item',
                'typ' => 's',
                'def' => null,
            ],
            // 종목
            [
                'val' => 'busiNo',
                'typ' => 's',
                'def' => null,
            ],
            // 사업자번호
            [
                'val' => 'ceo',
                'typ' => 's',
                'def' => null,
            ],
            // 대표자명
            [
                'val' => 'comZipcode',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장우편번호
            [
                'val' => 'comZonecode',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장우편번호(5자리)
            [
                'val' => 'comAddress',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장주소
            [
                'val' => 'comAddressSub',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장상세주소
            [
                'val' => 'mileage',
                'typ' => 'i',
                'def' => 0,
            ],
            // 마일리지
            [
                'val' => 'deposit',
                'typ' => 'i',
                'def' => 0,
            ],
            // 예치금
            [
                'val' => 'maillingFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 메일수신동의
            [
                'val' => 'smsFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // SMS수신동의
            [
                'val' => 'marriFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 결혼여부
            [
                'val' => 'marriDate',
                'typ' => 's',
                'def' => null,
            ],
            // 결혼기념일
            [
                'val' => 'job',
                'typ' => 's',
                'def' => null,
            ],
            // 직업
            [
                'val' => 'interest',
                'typ' => 's',
                'def' => null,
            ],
            // 관심분야
            [
                'val' => 'reEntryFl',
                'typ' => 's',
                'def' => null,
            ],
            // 재가입여부
            [
                'val' => 'entryDt',
                'typ' => 's',
                'def' => null,
            ],
            // 회원가입일
            [
                'val' => 'entryPath',
                'typ' => 's',
                'def' => null,
            ],
            // 로그인 제한
            [
                'val' => 'loginLimit',
                'typ' => 'j',
                'def' => null,
            ],
            // 가입경로
            [
                'val' => 'lastLoginDt',
                'typ' => 's',
                'def' => null,
            ],
            // 최종로그인
            [
                'val' => 'lastLoginIp',
                'typ' => 's',
                'def' => null,
            ],
            // 최종로그인IP
            [
                'val' => 'lastSaleDt',
                'typ' => 's',
                'def' => null,
            ],
            // 최종구매일
            [
                'val' => 'loginCnt',
                'typ' => 'i',
                'def' => 0,
            ],
            // 로그인횟수
            [
                'val' => 'saleCnt',
                'typ' => 'i',
                'def' => 0,
            ],
            // 구매횟수
            [
                'val' => 'saleAmt',
                'typ' => 'i',
                'def' => 0,
            ],
            // 총구매금액
            [
                'val' => 'memo',
                'typ' => 's',
                'def' => null,
            ],
            // 남기는말
            [
                'val' => 'recommId',
                'typ' => 's',
                'def' => null,
            ],
            // 추천인ID
            [
                'val' => 'recommFl',
                'typ' => 's',
                'def' => null,
            ],
            // 추천인등록여부
            [
                'val' => 'ex1',
                'typ' => 's',
                'def' => null,
            ],
            // 추가1
            [
                'val' => 'ex2',
                'typ' => 's',
                'def' => null,
            ],
            // 추가2
            [
                'val' => 'ex3',
                'typ' => 's',
                'def' => null,
            ],
            // 추가3
            [
                'val' => 'ex4',
                'typ' => 's',
                'def' => null,
            ],
            // 추가4
            [
                'val' => 'ex5',
                'typ' => 's',
                'def' => null,
            ],
            // 추가5
            [
                'val' => 'ex6',
                'typ' => 's',
                'def' => null,
            ],
            // 추가6
            [
                'val' => 'privateApprovalFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 개인정보동의 이용자 동의사항
            [
                'val' => 'privateApprovalOptionFl',
                'typ' => 's',
                'def' => null,
            ],
            // 개인정보동의 이용자 동의사항
            [
                'val' => 'privateOfferFl',
                'typ' => 's',
                'def' => null,
            ],
            // 개인정보동의 제3자 제공
            [
                'val' => 'privateConsignFl',
                'typ' => 's',
                'def' => null,
            ],
            // 개인정보동의 취급업무 위탁
            [
                'val' => 'foreigner',
                'typ' => 's',
                'def' => '1',
            ],
            // 내외국인구분
            [
                'val' => 'dupeinfo',
                'typ' => 's',
                'def' => '',
            ],
            // 중복가입확인정보
            [
                'val' => 'adultFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 성인여부
            [
                'val' => 'adultConfirmDt',
                'typ' => 's',
                'def' => '',
            ],
            // 성인인증등록시간
            [
                'val' => 'pakey',
                'typ' => 's',
                'def' => '',
            ],
            // 가상번호
            [
                'val' => 'rncheck',
                'typ' => 's',
                'def' => 'none',
            ],
            // 본인확인방법
            [
                'val' => 'adminMemo',
                'typ' => 's',
                'def' => null,
            ],
            // 관리자 메모
            [
                'val' => 'sleepFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 휴면회원 여부
            [
                'val' => 'sleepMailFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 휴면전환안내메일발송여부
            [
                'val' => 'sleepSmsFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 휴면해제일
            [
                'val' => 'sleepWakeDt',
                'typ' => 's',
                'def' => null,
            ],
            // 휴면전환안내SMS발송여부
            [
                'val' => 'expirationFl',
                'typ' => 's',
                'def' => '1',
            ],
            // 만 14세 이상 이용 동의 여부
            [
                'val' => 'under14ConsentFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 개인정보유효기간
        ];
        $arrField[] = [
            'val' => 'mallSno',
            'typ' => 'i',
            'def' => 1,
        ];      // 상점 번호
        $arrField[] = [
            'val' => 'pronounceName',
            'typ' => 's',
            'def' => null,
        ];    // 이름발음
        $arrField[] = [
            'val' => 'phoneCountryCode',
            'typ' => 's',
            'def' => 'kr',
        ];      // 전화번호 국가코드
        $arrField[] = [
            'val' => 'cellPhoneCountryCode',
            'typ' => 's',
            'def' => 'kr',
        ];      // 휴대폰 국가코드
        $arrField[] = [
            'val' => 'lifeMemberConversionDt',
            'typ' => 's',
            'def' => null,
        ];      // 평생회원 전환일

        return $arrField;
    }

    /**
     * [회원] member_group 필드 기본값
     *
     * @author sunny
     * @return array member_group 테이블 필드 정보
     */
    public function tableMemberGroup()
    {
        $arrField = [
            [
                'val' => 'groupNm',
                'typ' => 's',
                'def' => null,
            ],
            // 등급이름
            [
                'val' => 'groupSort',
                'typ' => 'i',
                'def' => '0',
            ],
            // 등급순서
            [
                'val' => 'groupMarkGb',
                'typ' => 's',
                'def' => null,
            ],
            // 등급표시구분
            [
                'val' => 'groupImageGb',
                'typ' => 's',
                'def' => null,
            ],
            // 등급이미지구분
            [
                'val' => 'groupIcon',
                'typ' => 's',
                'def' => null,
            ],
            // 등급아이콘
            [
                'val' => 'groupImage',
                'typ' => 's',
                'def' => null,
            ],
            // 등급이미지
            [
                'val' => 'groupIconUpload',
                'typ' => 's',
                'def' => '',
            ],
            // 등급아이콘직접등록
            [
                'val' => 'groupImageUpload',
                'typ' => 's',
                'def' => '',
            ],
            // 등급이미지직접등록
            [
                'val' => 'settleGb',
                'typ' => 's',
                'def' => null,
            ],
            // 이용결제수단
            [
                'val' => 'fixedRateOption',
                'typ' => 's',
                'def' => null,
            ],
            // 정률할인적립시구매금액기준
            [
                'val' => 'fixedRatePrice',
                'typ' => 's',
                'def' => null,
            ],
            // 추가 할인 상품 기준
            [
                'val' => 'fixedOrderTypeDc',
                'typ' => 's',
                'def' => null,
            ],
            // 할인/적립 시 적용금액 기준
            [
                'val' => 'dcLine',
                'typ' => 'd',
                'def' => null,
            ],
            // 추가할인기준치
            [
                'val' => 'dcType',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인타입
            [
                'val' => 'dcPercent',
                'typ' => 'd',
                'def' => null,
            ],
            // 추가할인률
            [
                'val' => 'dcPrice',
                'typ' => 'd',
                'def' => null,
            ],
            // 추가할인 브랜드 할인율
            [
                'val' => 'dcBrandInfo',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인금액
            [
                'val' => 'dcExOption',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인예외항목
            [
                'val' => 'dcExScm',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인예외공급사
            [
                'val' => 'dcExCategory',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인예외카테고리
            [
                'val' => 'dcExBrand',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인예외브랜드
            [
                'val' => 'dcExGoods',
                'typ' => 's',
                'def' => null,
            ],
            // 중복 할인 상품 기준
            [
                'val' => 'fixedOrderTypeOverlapDc',
                'typ' => 's',
                'def' => null,
            ],
            // 추가할인예외상품
            [
                'val' => 'overlapDcLine',
                'typ' => 'd',
                'def' => null,
            ],
            // 중복할인기준치
            [
                'val' => 'overlapDcType',
                'typ' => 's',
                'def' => null,
            ],
            // 중복할인타입
            [
                'val' => 'overlapDcPercent',
                'typ' => 'd',
                'def' => null,
            ],
            // 중복할인률
            [
                'val' => 'overlapDcPrice',
                'typ' => 'd',
                'def' => null,
            ],
            // 중복할인금액
            [
                'val' => 'overlapDcOption',
                'typ' => 's',
                'def' => null,
            ],
            // 중복할인항목
            [
                'val' => 'overlapDcScm',
                'typ' => 's',
                'def' => null,
            ],
            // 중복할인공급사
            [
                'val' => 'overlapDcCategory',
                'typ' => 's',
                'def' => null,
            ],
            // 중복할인카테고리
            [
                'val' => 'overlapDcBrand',
                'typ' => 's',
                'def' => null,
            ],
            // 중복할인브랜드
            [
                'val' => 'overlapDcGoods',
                'typ' => 's',
                'def' => null,
            ],
            // 중복할인상품
            [
                'val' => 'fixedOrderTypeMileage',
                'typ' => 's',
                'def' => null,
            ],
            // 추가 마일리지 적립 상품 기준
            [
                'val' => 'mileageLine',
                'typ' => 'i',
                'def' => null,
            ],
            // 추가적립기준치
            [
                'val' => 'mileageType',
                'typ' => 's',
                'def' => null,
            ],
            // 추가적립타입
            [
                'val' => 'mileagePercent',
                'typ' => 'd',
                'def' => null,
            ],
            // 추가적립률
            [
                'val' => 'mileagePrice',
                'typ' => 'd',
                'def' => null,
            ],
            // 추가마일리지
            [
                'val' => 'apprFigureOrderPriceFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 실적수치제구매횟수
            [
                'val' => 'apprFigureOrderRepeatFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 실적수치제구매횟수
            [
                'val' => 'apprFigureReviewRepeatFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 실적수치제구매후기
            [
                'val' => 'apprFigureOrderPriceMore',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제구매액이상
            [
                'val' => 'apprFigureOrderPriceBelow',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제구매액미만
            [
                'val' => 'apprFigureOrderRepeat',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제구매횟수
            [
                'val' => 'apprFigureReviewRepeat',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제후기횟수
            [
                'val' => 'apprPointMore',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적점수제이상
            [
                'val' => 'apprPointBelow',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적점수제미만
            [
                'val' => 'apprFigureOrderPriceMoreMobile',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제구매액이상모바일
            [
                'val' => 'apprFigureOrderPriceBelowMobile',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제구매액미만모바일
            [
                'val' => 'apprFigureOrderRepeatMobile',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제구매횟수모바일
            [
                'val' => 'apprFigureReviewRepeatMobile',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적수치제후기횟수모바일
            [
                'val' => 'apprPointMoreMobile',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적점수제이상모바일
            [
                'val' => 'apprPointBelowMobile',
                'typ' => 'i',
                'def' => null,
            ],
            // 실적점수제미만모바일
            [
                'val' => 'deliveryFree',
                'typ' => 's',
                'def' => null,
            ],
            // 배송비 무료 여부
            [
                'val' => 'groupCoupon',
                'typ' => 's',
                'def' => null,
            ],
            // 등급평가제외설정 여부
            [
                'val' => 'apprExclusionOfRatingFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 그룹별 쿠폰 제공 여부
            [
                'val' => 'regId',
                'typ' => 's',
                'def' => null,
            ],
            // 등록자
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ]
            // 등록자키
        ];

        return $arrField;
    }

    /**
     * [회원] member_hackout 필드 기본값
     *
     * @author sunny
     * @return array member_hackout 테이블 필드 정보
     */
    public function tableMemberHackout()
    {
        $arrField = [
            [
                'val' => 'hackType',
                'typ' => 's',
                'def' => '',
            ],
            // 탈퇴유형
            [
                'val' => 'rejoinFl',
                'typ' => 's',
                'def' => '',
            ],
            // 재가입 가능 여부
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => '',
            ],
            // 회원번호
            [
                'val' => 'memId',
                'typ' => 's',
                'def' => '',
            ],
            // 아이디
            [
                'val' => 'dupeinfo',
                'typ' => 's',
                'def' => null,
            ],
            // 중복가입확인정보
            [
                'val' => 'reasonCd',
                'typ' => 's',
                'def' => '',
            ],
            // 불편사항
            [
                'val' => 'reasonDesc',
                'typ' => 's',
                'def' => '',
            ],
            // 충고말씀
            [
                'val' => 'adminMemo',
                'typ' => 's',
                'def' => '',
            ],
            // 관리메모
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ],
            // 관리자 번호
            [
                'val' => 'managerId',
                'typ' => 's',
                'def' => '',
            ],
            // 관리자 아이디
            [
                'val' => 'managerIp',
                'typ' => 's',
                'def' => '',
            ],
            // 관리자 아이피
            [
                'val' => 'hackDt',
                'typ' => 's',
                'def' => '',
            ],
            // 탈퇴일
            [
                'val' => 'regIp',
                'typ' => 's',
                'def' => '',
            ],
            // 신청아이피
        ];
        $arrField[] = [
            'val' => 'mallSno',
            'typ' => 'i',
            'def' => 1,
        ];      // 상점번호

        return $arrField;
    }

    /**
     * [회원] member_mileage 필드 기본값
     *
     * @author artherot
     * @return array member_mileage 테이블 필드 정보
     */
    public function tableMemberMileage()
    {
        $arrField = [
            [
                'val' => 'memNo',
                'typ' => 's',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ],
            // 관리자 키값
            [
                'val' => 'managerId',
                'typ' => 's',
                'def' => null,
            ],
            // 관리자 아이디
            [
                'val' => 'handleMode',
                'typ' => 's',
                'def' => 'm',
            ],
            // 처리 모드 (m - 회원, o - 주문, b - 게시판, r - 추천인)
            [
                'val' => 'handleCd',
                'typ' => 's',
                'def' => null,
            ],
            // 처리 코드 (주문 번호, 게시판 코드)
            [
                'val' => 'handleNo',
                'typ' => 's',
                'def' => null,
            ],
            // 처리 번호 (상품 번호, 게시물 번호)
            [
                'val' => 'beforeMileage',
                'typ' => 'i',
                'def' => '0',
            ],
            // 이전 마일리지
            [
                'val' => 'afterMileage',
                'typ' => 'i',
                'def' => '0',
            ],
            // 이후 마일리지
            [
                'val' => 'mileage',
                'typ' => 'i',
                'def' => '0',
            ],
            // 마일리지
            [
                'val' => 'reasonCd',
                'typ' => 's',
                'def' => null,
            ],
            // 지급/차감 사유 코드
            [
                'val' => 'contents',
                'typ' => 's',
                'def' => null,
            ],
            // 지급/차감 사유
            [
                'val' => 'useHistory',
                'typ' => 's',
                'def' => '{}',
            ],
            // 마일리지 사용 내역
            [
                'val' => 'deleteFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 소멸여부(y,n), 사용완료(complete), 사용중(use)
            [
                'val' => 'deleteScheduleDt',
                'typ' => 's',
                'def' => null,
            ],
            // 소멸예정일
            [
                'val' => 'deleteDt',
                'typ' => 's',
                'def' => null,
            ],
            // 소멸일
            [
                'val' => 'regIp',
                'typ' => 's',
                'def' => null,
            ],
            // 등록시 IP
        ];

        return $arrField;
    }

    /**
     * [회원] es_memberNotificationLog 필드 기본값
     *
     * @
     * @return array
     */
    public function tableMemberNotificationLog()
    {
        $arrField = [
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'type',
                'typ' => 's',
                'def' => 'none',
            ],
            // 알림수단
            [
                'val' => 'typeLogSno',
                'typ' => 'i',
                'def' => null,
            ],
            // 알림수단내역번호
            [
                'val' => 'reasonCode',
                'typ' => 's',
                'def' => 'none',
            ],
            // 참조정보
        ];

        return $arrField;
    }

    /**
     * [회원] member_deposit 필드 기본값
     *
     * @author yjwee
     * @return array member_deposit 테이블 필드 정보
     */
    public function tableMemberDeposit()
    {
        $arrField = [
            [
                'val' => 'memNo',
                'typ' => 's',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ],
            // 관리자 키
            [
                'val' => 'managerId',
                'typ' => 's',
                'def' => null,
            ],
            // 관리자 아이디
            [
                'val' => 'handleMode',
                'typ' => 's',
                'def' => 'm',
            ],
            // 처리 모드 (m - 회원, o - 주문, b - 게시판, r - 추천인)
            [
                'val' => 'handleCd',
                'typ' => 's',
                'def' => null,
            ],
            // 처리 코드 (주문 번호, 게시판 코드)
            [
                'val' => 'handleNo',
                'typ' => 's',
                'def' => null,
            ],
            // 처리 번호 (상품 번호, 게시물 번호)
            [
                'val' => 'beforeDeposit',
                'typ' => 'i',
                'def' => '0',
            ],
            // 환불/반품/교환 처리 SNO
            [
                'val' => 'handleSno',
                'typ' => 'i',
                'def' => 0,
            ],
            // 이전 예치금
            [
                'val' => 'afterDeposit',
                'typ' => 'i',
                'def' => '0',
            ],
            // 이후 예치금
            [
                'val' => 'deposit',
                'typ' => 'i',
                'def' => '0',
            ],
            // 예치금
            [
                'val' => 'reasonCd',
                'typ' => 's',
                'def' => null,
            ],
            // 지급/차감사유 코드
            [
                'val' => 'contents',
                'typ' => 's',
                'def' => null,
            ],
            // 처리/차감사유
            [
                'val' => 'regIp',
                'typ' => 's',
                'def' => null,
            ]
            // 등록시 IP
        ];

        return $arrField;
    }

    /**
     * [회원] MEMBERCOUPON
     *
     * @author su
     * @return array MEMBERCOUPON
     */
    public function tableMemberCoupon()
    {
        $arrField = [
            [
                'val' => 'memberCouponNo',
                'typ' => 'i',
                'def' => '',
            ],
            // 회원쿠폰고유번호
            [
                'val' => 'couponNo',
                'typ' => 'i',
                'def' => '',
            ],
            // 쿠폰고유번호
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => '',
            ],
            // 회원고유번호
            [
                'val' => 'couponSaveAdminId',
                'typ' => 's',
                'def' => '',
            ],
            // 회원쿠폰발급자
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ],
            // 회원쿠폰발급자 키
            [
                'val' => 'orderNo',
                'typ' => 'i',
                'def' => '',
            ],
            // 회원쿠폰사용 주문번호
            [
                'val' => 'goodsNo',
                'typ' => 'i',
                'def' => '',
            ],
            // 회원쿠폰사용 상품번호
            [
                'val' => 'memberCouponStartDate',
                'typ' => 's',
                'def' => '',
            ],
            // 회원쿠폰사용시작일자
            [
                'val' => 'memberCouponEndDate',
                'typ' => 's',
                'def' => '',
            ],
            // 회원쿠폰사용만료일자
            [
                'val' => 'memberCouponCartDate',
                'typ' => 's',
                'def' => '',
            ],
            // 회원쿠폰장바구니사용일자
            [
                'val' => 'memberCouponUseDate',
                'typ' => 's',
                'def' => '',
            ],
            // 회원쿠폰사용일자
            [
                'val' => 'memberCouponState',
                'typ' => 's',
                'def' => 'y',
            ],
            // 수기주문에서의 회원쿠폰 적용 여부
            [
                'val' => 'orderWriteCouponState',
                'typ' => 's',
                'def' => 'y',
            ],
            // 생일쿠폰년도
            [
                'val' => 'birthDayCouponYear',
                'typ' => 'i',
                'def' => '',
            ],
            // 회원쿠폰사용여부 - 주문사용('use'),장바구니사용('cart'),사용안함('y')
            [
                'val' => 'regDt',
                'typ' => 's',
                'def' => null,
            ],
            // 등록일
            [
                'val' => 'modDt',
                'typ' => 's',
                'def' => null,
            ],
            // 수정일
        ];

        return $arrField;
    }

    /**
     * [회원] member_sns 필드 기본값
     *
     * @return array
     */
    public function tableMemberSns()
    {
        $arrField = [
            [
                'val' => 'mallSno',
                'typ' => 'i',
                'def' => 1,
            ],
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => 0,
            ],
            [
                'val' => 'appId',
                'typ' => 's',
                'def' => 'godo',
            ],
            [
                'val' => 'uuid',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'snsJoinFl',
                'typ' => 's',
                'def' => 'n',
            ],
            [
                'val' => 'snsTypeFl',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'connectFl',
                'typ' => 's',
                'def' => 'n',
            ],
            [
                'val' => 'accessToken',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'refreshToken',
                'typ' => 's',
                'def' => null,
            ],
        ];

        return $arrField;
    }

    /**
     * [회원] es_memberSleep 필드 기본값
     *
     * @author yjwee
     * @return array es_memberSleep 테이블 필드 정보
     */
    public function tableMemberSleep()
    {
        $arrField = [
            [
                'val' => 'sleepNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 휴면회원번호
            [
                'val' => 'sleepDt',
                'typ' => 's',
                'def' => null,
            ],
            // 휴면회원전환일
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'memId',
                'typ' => 's',
                'def' => null,
            ],
            // 아이디
            [
                'val' => 'memNm',
                'typ' => 's',
                'def' => null,
            ],
            // 이름
            [
                'val' => 'mileage',
                'typ' => 'i',
                'def' => 0.00,
            ],
            // 마일리지
            [
                'val' => 'deposit',
                'typ' => 'i',
                'def' => 0.00,
            ],
            // 예치금
            [
                'val' => 'groupSno',
                'typ' => 'i',
                'def' => 1,
            ],
            // 회원등급sno
            [
                'val' => 'email',
                'typ' => 's',
                'def' => null,
            ],
            // 이메일
            [
                'val' => 'cellPhone',
                'typ' => 's',
                'def' => null,
            ],
            // 휴대폰번호
            [
                'val' => 'phone',
                'typ' => 's',
                'def' => null,
            ],
            // 전화번호
            [
                'val' => 'entryDt',
                'typ' => 's',
                'def' => null,
            ],
            // 회원가입일
            [
                'val' => 'encryptData',
                'typ' => 's',
                'def' => null,
            ],
            // 회원정보
        ];

        return $arrField;
    }

    /**
     * es_memberStatistics 필드 기본값
     *
     * @
     */
    public function tableMemberStatistics()
    {
        $arrField = [
            [
                'val' => 'sno',
                'typ' => 'i',
                'def' => null,
            ],
            // 일련번호
            [
                'val' => 'statisticsDt',
                'typ' => 's',
                'def' => '0000-00-00',
            ],
            // 분석일자
            [
                'val' => 'total',
                'typ' => 'i',
                'def' => 0,
            ],
            // 전체회원수
            [
                'val' => 'male',
                'typ' => 'i',
                'def' => 0,
            ],
            // 남성회원수
            [
                'val' => 'female',
                'typ' => 'i',
                'def' => 0,
            ],
            // 여성회원수
            [
                'val' => 'genderOther',
                'typ' => 'i',
                'def' => 0,
            ],
            // 성별미확인회원수
            [
                'val' => 'age10',
                'typ' => 'i',
                'def' => 0,
            ],
            // 10대회원수
            [
                'val' => 'age20',
                'typ' => 'i',
                'def' => 0,
            ],
            // 20대회원수
            [
                'val' => 'age30',
                'typ' => 'i',
                'def' => 0,
            ],
            // 30대회원수
            [
                'val' => 'age40',
                'typ' => 'i',
                'def' => 0,
            ],
            // 40대회원수
            [
                'val' => 'age50',
                'typ' => 'i',
                'def' => 0,
            ],
            // 50대회원수
            [
                'val' => 'age60',
                'typ' => 'i',
                'def' => 0,
            ],
            // 60대회원수
            [
                'val' => 'age70',
                'typ' => 'i',
                'def' => 0,
            ],
            // 70대회원수
            [
                'val' => 'ageOther',
                'typ' => 'i',
                'def' => 0,
            ],
            // 연별미확인회원수
            [
                'val' => 'seoul',
                'typ' => 'i',
                'def' => 0,
            ],
            // 서울회원수
            [
                'val' => 'busan',
                'typ' => 'i',
                'def' => 0,
            ],
            // 부산회원수
            [
                'val' => 'daegu',
                'typ' => 'i',
                'def' => 0,
            ],
            // 대구회원수
            [
                'val' => 'incheon',
                'typ' => 'i',
                'def' => 0,
            ],
            // 인천회원수
            [
                'val' => 'gwangju',
                'typ' => 'i',
                'def' => 0,
            ],
            // 광주회원수
            [
                'val' => 'daejeon',
                'typ' => 'i',
                'def' => 0,
            ],
            // 대전회원수
            [
                'val' => 'ulsan',
                'typ' => 'i',
                'def' => 0,
            ],
            // 울산회원수
            [
                'val' => 'sejong',
                'typ' => 'i',
                'def' => 0,
            ],
            // 세종회원수
            [
                'val' => 'gyeonggi',
                'typ' => 'i',
                'def' => 0,
            ],
            // 경기회원수
            [
                'val' => 'gangwon',
                'typ' => 'i',
                'def' => 0,
            ],
            // 강원회원수
            [
                'val' => 'chungbuk',
                'typ' => 'i',
                'def' => 0,
            ],
            // 충북회원수
            [
                'val' => 'chungnam',
                'typ' => 'i',
                'def' => 0,
            ],
            // 충남회원수
            [
                'val' => 'jeonbuk',
                'typ' => 'i',
                'def' => 0,
            ],
            // 전북회원수
            [
                'val' => 'jeonnam',
                'typ' => 'i',
                'def' => 0,
            ],
            // 전남회원수
            [
                'val' => 'gyeongbuk',
                'typ' => 'i',
                'def' => 0,
            ],
            // 경북회원수
            [
                'val' => 'gyeongnam',
                'typ' => 'i',
                'def' => 0,
            ],
            // 경남회원수
            [
                'val' => 'jeju',
                'typ' => 'i',
                'def' => 0,
            ],
            // 제주회원수
            [
                'val' => 'areaOther',
                'typ' => 'i',
                'def' => 0,
            ],
            // 지역미확인회원수
        ];

        return $arrField;
    }

    /**
     * [통계] es_memberStatisticsDay 필드 기본 값
     *
     * @
     * @return array 테이블 필드 정보
     */
    public function tableMemberStatisticsDay()
    {
        $arrField = [
            [
                'val' => 'joinYM',
                'typ' => 'i',
                'def' => null,
            ],
            // 일별통계날짜
            [
                'val' => '1',
                'typ' => 's',
                'def' => null,
            ],
            // 1일
            [
                'val' => '2',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '3',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '4',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '5',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '6',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '7',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '8',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '9',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '10',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '11',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '12',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '13',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '14',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '15',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '16',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '17',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '18',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '19',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '20',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '21',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '22',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '23',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '24',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '25',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '26',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '27',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '28',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '29',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '30',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => '31',
                'typ' => 's',
                'def' => null,
            ],
            // 31일
        ];

        return $arrField;
    }

    /**
     * [회원] es_memberHistory 필드 기본값
     *
     * @author yjwee
     * @return array es_memberHistory 테이블 필드 정보
     */
    public function tableMemberHistory()
    {
        $arrField = [
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'processor',
                'typ' => 's',
                'def' => null,
            ],
            // 처리자
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ],
            // 처리자 키
            [
                'val' => 'processorIp',
                'typ' => 's',
                'def' => null,
            ],
            // ip주소
            [
                'val' => 'updateColumn',
                'typ' => 's',
                'def' => null,
            ],
            // 변경항목
            [
                'val' => 'beforeValue',
                'typ' => 's',
                'def' => null,
            ],
            // 변경전
            [
                'val' => 'afterValue',
                'typ' => 's',
                'def' => null,
            ],
            // 변경후
            [
                'val' => 'otherValue',
                'typ' => 'j',
                'def' => null,
            ],
            // 기타
        ];

        return $arrField;
    }

    /**
     * [회원] es_memberInvoiceInfo 필드 기본값
     *
     * @author haky
     * @return array es_memberHistory 테이블 필드 정보
     */
    public function tableMemberInvoiceInfo()
    {
        $arrField = [
            [
                'val' => 'sno',
                'typ' => 'i',
                'def' => null,
            ],
            // 일련 번호
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'company',
                'typ' => 's',
                'def' => null,
            ],
            // 회사명
            [
                'val' => 'service',
                'typ' => 's',
                'def' => null,
            ],
            // 업태
            [
                'val' => 'item',
                'typ' => 's',
                'def' => null,
            ],
            // 종목
            [
                'val' => 'taxBusiNo',
                'typ' => 's',
                'def' => null,
            ],
            // 세금계산서 사업자번호
            [
                'val' => 'ceo',
                'typ' => 's',
                'def' => null,
            ],
            // 대표자
            [
                'val' => 'comZipcode',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장 우편번호
            [
                'val' => 'comZonecode',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장 우편번호(5자리)
            [
                'val' => 'comAddress',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장 주소
            [
                'val' => 'comAddressSub',
                'typ' => 's',
                'def' => null,
            ],
            // 사업장 상세주소
            [
                'val' => 'email',
                'typ' => 's',
                'def' => null,
            ],
            // 이메일
            [
                'val' => 'cellPhone',
                'typ' => 's',
                'def' => null,
            ],
            // 핸드폰
            [
                'val' => 'cashBusiNo',
                'typ' => 's',
                'def' => null,
            ],
            // 현금영수증 사업자번호
            [
                'val' => 'regDt',
                'typ' => 's',
                'def' => null,
            ],
            // 등록일
            [
                'val' => 'modDt',
                'typ' => 's',
                'def' => null,
            ],
            // 수정일
        ];

        return $arrField;
    }

    /**
     * [회원] member_loginlog 필드 기본값
     *
     * @author sunny
     * @return array member_loginlog 테이블 필드 정보
     */
    public function tableMemberLoginlog()
    {
        $arrField = [
            [
                'val' => 'memNo',
                'typ' => 's',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'loginCnt',
                'typ' => 'i',
                'def' => 0,
            ],
            // 로그인횟수
            [
                'val' => 'loginCntMobile',
                'typ' => 'i',
                'def' => 0,
            ],
            // 모바일로그인여부
        ];

        return $arrField;
    }
    /**
     * [회원] member_simpleJoin 필드 기본값
     *
     * @author sunny
     * @return array member_simpleJoin 테이블 필드 정보
     */
    public function tableMemberSimpleJoinLog()
    {
        $arrField = [
            [
                'val' => 'eventType',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'memNo',
                'typ' => 's',
                'def' => null,
            ],
            // 회원 번호
            [
                'val' => 'memId',
                'typ' => 's',
                'def' => null,
            ],
            // 회원 아이디
            [
                'val' => 'appFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 회원 아이디
            [
                'val' => 'groupSno',
                'typ' => 'i',
                'def' => 1,
            ],
            // 회원 등급
            [
                'val' => 'memberCouponNo',
                'typ' => 's',
                'def' => null,
            ],
            // 가입시 발급 쿠폰
            [
                'val' => 'couponNm',
                'typ' => 's',
                'def' => null,
            ],
            // 가입시 발급 쿠폰명
            [
                'val' => 'mileage',
                'typ' => 'i',
                'def' => 0,
            ],
            // 가입시 제공 마일리지
            [
                'val' => 'regDt',
                'typ' => 's',
                'def' => null,
            ],
        ];

        return $arrField;
    }
    /**
     * [회원] member_simpleJoinPush 필드 기본값
     *
     * @author sunny
     * @return array member_simpleJoinPush 테이블 필드 정보
     */
    public function tableMemberSimpleJoinPushLog()
    {
        $arrField = [
            [
                'val' => 'eventType',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'regDt',
                'typ' => 's',
                'def' => null,
            ],
        ];

        return $arrField;
    }
}
