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

namespace Bundle\Component\Design;

use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;
use Globals;
use Logger;

/**
 * 치환 정의 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ReplaceCode
{
    // 기본 치환코드
    private $replaceSrc = [];

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->setReplaceCode();
    }

    /**
     * 기본 치환코드 세팅
     */
    private function setReplaceCode()
    {
        if (empty(Globals::get('gMall')) === true) {
            Globals::set('gMall', ComponentUtils::getMallInfo());
        }
        $gMall = Globals::get('gMall');
        if (ArrayUtils::isEmpty($gMall) === false) {
            $this->replaceSrc['{rc_companyNm}'] = [
                'desc' => __('회사명'),
                'val'  => Globals::get('gMall.companyNm'),
            ];
            $this->replaceSrc['{rc_businessNo}'] = [
                'desc' => __('사업자등록번호'),
                'val'  => Globals::get('gMall.businessNo'),
            ];
            $this->replaceSrc['{rc_ceoNm}'] = [
                'desc' => __('쇼핑몰 대표'),
                'val'  => Globals::get('gMall.ceoNm'),
            ];
            $this->replaceSrc['{rc_email}'] = [
                'desc' => __('쇼핑몰 이메일'),
                'val'  => Globals::get('gMall.email'),
            ];
            $this->replaceSrc['{rc_zoneCode}'] = [
                'desc' => __('쇼핑몰 우편번호'),
                'val'  => Globals::get('gMall.zoneCode'),
            ];
            $this->replaceSrc['{rc_address}'] = [
                'desc' => __('사업장 주소'),
                'val'  => Globals::get('gMall.address') . ' ' . Globals::get('gMall.addressSub'),
            ];
            $this->replaceSrc['{rc_phone}'] = [
                'desc' => __('쇼핑몰 전화'),
                'val'  => Globals::get('gMall.phone'),
            ];
            $this->replaceSrc['{rc_fax}'] = [
                'desc' => __('쇼핑몰 팩스'),
                'val'  => Globals::get('gMall.fax'),
            ];
            $this->replaceSrc['{rc_privateNm}'] = [
                'desc' => __('개인정보 보호책임자'),
                'val'  => Globals::get('gMall.privateNm'),
            ];
            $this->replaceSrc['{rc_privatePosition}'] = [
                'desc' => __('개인정보 보호책임자 직책'),
                'val'  => Globals::get('gMall.privatePosition'),
            ];
            $this->replaceSrc['{rc_privateDepartment}'] = [
                'desc' => __('개인정보 보호책임자 부서'),
                'val'  => Globals::get('gMall.privateDepartment'),
            ];
            $this->replaceSrc['{rc_privatePhone}'] = [
                'desc' => __('개인정보 보호책임자 전화'),
                'val'  => Globals::get('gMall.privatePhone'),
            ];
            $this->replaceSrc['{rc_privateEmail}'] = [
                'desc' => __('개인정보 보호책임자 이메일'),
                'val'  => Globals::get('gMall.privateEmail'),
            ];
            $this->replaceSrc['{rc_mallNm}'] = [
                'desc' => __('쇼핑몰 이름'),
                'val'  => Globals::get('gMall.mallNm'),
            ];
            $this->replaceSrc['{rc_mallNmEng}'] = [
                'desc' => __('쇼핑몰 영문이름'),
                'val'  => Globals::get('gMall.mallNmEng'),
            ];
            $this->replaceSrc['{rc_mallDomain}'] = [
                'desc' => __('쇼핑몰 도메인'),
                'val'  => Globals::get('gMall.mallDomain'),
            ];
            $this->replaceSrc['{rc_mallCategory}'] = [
                'desc' => __('쇼핑몰 대표카테고리'),
                'val'  => Globals::get('gMall.mallCategory'),
            ];
            $this->replaceSrc['{rc_mallTitle}'] = [
                'desc' => __('쇼핑몰 타이틀'),
                'val'  => Globals::get('gMall.mallTitle'),
            ];
            $this->replaceSrc['{rc_mallDescription}'] = [
                'desc' => __('쇼핑몰 설명'),
                'val'  => Globals::get('gMall.mallDescription'),
            ];
            $this->replaceSrc['{rc_mallKeyword}'] = [
                'desc' => __('쇼핑몰 키워드'),
                'val'  => Globals::get('gMall.mallKeyword'),
            ];
            $this->replaceSrc['{rc_centerPhone}'] = [
                'desc' => __('고객센터 전화'),
                'val'  => Globals::get('gMall.centerPhone'),
            ];
            $this->replaceSrc['{rc_centerFax}'] = [
                'desc' => __('고객센터 팩스'),
                'val'  => Globals::get('gMall.centerFax'),
            ];
            $this->replaceSrc['{rc_centerEmail}'] = [
                'desc' => __('고객센터 이메일'),
                'val'  => Globals::get('gMall.centerEmail'),
            ];
            $this->replaceSrc['{rc_centerHours}'] = [
                'desc' => __('고객센터 운용시간'),
                'val'  => Globals::get('gMall.centerHours'),
            ];
            $this->replaceSrc['{rc_footerLogo}'] = [
                'desc' => __('쇼핑몰 하단 로고'),
                'val'  => SkinUtils::getFooterLogoTag()[0]['tag'],
            ];
        }
        $this->replaceSrc['{rc_today}'] = [
            'desc' => __('오늘 날짜 YYYY년MM월DD일'),
            'val'  => DateTimeUtils::dateFormat('Y년 m월 d일', 'today'),
        ];
    }

    /**
     * init 후 파라미터로 넘어온 키 값만 남기고 unset 처리
     *
     * @param array $keys
     */
    public function initWithUnsetDiff(array $keys)
    {
        $this->init();
        ArrayUtils::unsetDiff($this->replaceSrc, $keys);
    }

    /**
     * replaceSrc 배열을 초기화하고 재설정하는 함수
     *
     */
    public function init()
    {
        unset($this->replaceSrc);
        $this->setReplaceCode();

        return $this;
    }

    /**
     * 자동발송메일 회원가입 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByJoin($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'] ? $replaceData['memNm'] : __('회원'),
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_smsFl}'] = [
            'desc' => __('SMS 수신동의 상태'),
            'val'  => $replaceData['smsFl'] === 'y' ? __('수신동의') : __('수신거부'),
        ];
        $this->replaceSrc['{rc_maillingFl}'] = [
            'desc' => __('이메일 수신동의 상태'),
            'val'  => $replaceData['maillingFl'] === 'y' ? __('수신동의') : __('수신거부'),
        ];
    }

    /**
     * 자동발송메일 가입승인 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByApproval($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_smsFl}'] = [
            'desc' => __('SMS 수신동의 상태'),
            'val'  => $replaceData['smsFl'] === 'y' ? __('수신동의') : __('수신거부'),
        ];
        $this->replaceSrc['{rc_maillingFl}'] = [
            'desc' => __('이메일 수신동의 상태'),
            'val'  => $replaceData['maillingFl'] === 'y' ? __('수신동의') : __('수신거부'),
        ];
        $this->replaceSrc['{rc_smsLastReceiveAgreementDt}'] = [
            'desc' => __('SMS 수신동의일'),
            'val'  => $replaceData['smsLastReceiveAgreementDt'],
        ];
        $this->replaceSrc['{rc_mailLastReceiveAgreementDt}'] = [
            'desc' => __('이메일 수신동의일'),
            'val'  => $replaceData['mailLastReceiveAgreementDt'],
        ];
    }

    /**
     * 이메일 수신거부
     *
     * @param $replaceData
     */
    public function setReplaceCodeByRejectEmail($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_modDt}'] = [
            'desc' => __('수신거부 처리완료일'),
            'val'  => $replaceData['modDt'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_email}'] = [
            'desc' => __('이메일'),
            'val'  => $replaceData['email'],
        ];
    }

    /**
     * 회원등급변경
     *
     * @param $replaceData
     */
    public function setReplaceCodeByGroupChange($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_groupNm}'] = [
            'desc' => __('등급명'),
            'val'  => $replaceData['groupNm'],
        ];
        $this->replaceSrc['{rc_grpLabel}'] = [
            'desc' => __('쇼핑몰페이지 회원등급 노출이름'),
            'val'  => $replaceData['grpLabel'],
        ];
        $this->replaceSrc['{rc_dcLine}'] = [
            'desc' => __('추가할인'),
            'val'  => $replaceData['dcLine'],
        ];
        $this->replaceSrc['{rc_dcPercent}'] = [
            'desc' => __('추가할인률'),
            'val'  => $replaceData['dcPercent'],
        ];
        $this->replaceSrc['{rc_dcExScm}'] = [
            'desc' => __('추가할인 적용제외 공급사'),
            'val'  => $replaceData['dcExScm'],
        ];
        $this->replaceSrc['{rc_dcExCategory}'] = [
            'desc' => __('추가할인 적용제외 카테고리'),
            'val'  => $replaceData['dcExCategory'],
        ];
        $this->replaceSrc['{rc_dcExBrand}'] = [
            'desc' => __('추가할인 적용제외 브랜드'),
            'val'  => $replaceData['dcExBrand'],
        ];
        $this->replaceSrc['{rc_dcExGoods}'] = [
            'desc' => __('추가할인 적용제외 상품'),
            'val'  => $replaceData['dcExGoods'],
        ];
        $this->replaceSrc['{rc_overlapDcLine}'] = [
            'desc' => __('중복할인'),
            'val'  => $replaceData['overlapDcLine'],
        ];
        $this->replaceSrc['{rc_overlapDcPercent}'] = [
            'desc' => __('중복할인률'),
            'val'  => $replaceData['overlapDcPercent'],
        ];
        $this->replaceSrc['{rc_overlapDcScm}'] = [
            'desc' => __('중복할인 적용 공급사'),
            'val'  => $replaceData['overlapDcScm'],
        ];
        $this->replaceSrc['{rc_overlapDcCategory}'] = [
            'desc' => __('중복할인 적용 카테고리'),
            'val'  => $replaceData['overlapDcCategory'],
        ];
        $this->replaceSrc['{rc_overlapDcBrand}'] = [
            'desc' => __('중복할인 적용 브랜드'),
            'val'  => $replaceData['overlapDcBrand'],
        ];
        $this->replaceSrc['{rc_overlapDcGoods}'] = [
            'desc' => __('중복할인 적용제외 상품'),
            'val'  => $replaceData['overlapDcGoods'],
        ];
        $this->replaceSrc['{rc_mileageLine}'] = [
            'desc' => __('추가적립마일리지'),
            'val'  => $replaceData['mileageLine'],
        ];
        $this->replaceSrc['{rc_mileagePercent}'] = [
            'desc' => __('추가적립마일리지 적립률'),
            'val'  => $replaceData['mileagePercent'],
        ];
        $this->replaceSrc['{rc_fixedRateOption}'] = [
            'desc' => __('구매금액 기준'),
            'val'  => $replaceData['fixedRateOption'],
        ];
        $this->replaceSrc['{rc_settleGb}'] = [
            'desc' => __('사용가능 결제수단'),
            'val'  => $replaceData['settleGb'],
        ];
        $this->replaceSrc['{rc_changeDt}'] = [
            'desc' => __('등급평가일'),
            'val'  => $replaceData['changeDt'],
        ];
        $this->replaceSrc['{rc_calcKeep}'] = [
            'desc' => __('등급유지기간'),
            'val'  => $replaceData['calcKeep'],
        ];
    }

    /**
     * 자동발송메일 정보수신동의 설정 변경내역 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByAgreement($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_smsFl}'] = [
            'desc' => __('SMS 수신동의 상태'),
            'val'  => $replaceData['smsFl'] === 'y' ? __('수신동의') : __('수신거부'),
        ];
        $this->replaceSrc['{rc_maillingFl}'] = [
            'desc' => __('이메일 수신동의 상태'),
            'val'  => $replaceData['maillingFl'] === 'y' ? __('수신동의') : __('수신거부'),
        ];
        $this->replaceSrc['{rc_regDt}'] = [
            'desc' => __('수신동의 변경일 (회원가입일)'),
            'val'  => $replaceData['regDt'],
        ];
        $this->replaceSrc['{rc_modDt}'] = [
            'desc' => __('수신동의 변경일'),
            'val'  => $replaceData['modDt'],
        ];
        $this->replaceSrc['{rc_mailAgreementDt}'] = [
            'desc' => __('이메일 수신동의일'),
            'val'  => DateTimeUtils::dateFormat('Y-m-d', $replaceData['mailAgreementDt']),
        ];
        $this->replaceSrc['{rc_smsAgreementDt}'] = [
            'desc' => __('SMS 수신동의일'),
            'val'  => DateTimeUtils::dateFormat('Y-m-d', $replaceData['smsAgreementDt']),
        ];
    }

    /**
     * 자동발송메일 비밀번호변경 안내 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByChangePassword($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_changeDt}'] = [
            'desc' => __('비밀번호변경일시'),
            'val'  => $replaceData['changeDt'],
        ];
    }

    /**
     * 자동발송메일 마일리지 지급 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByAddMileage($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('대상아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_mileage}'] = [
            'desc' => __('지급마일리지'),
            'val'  => $replaceData['mileage'],
        ];
        $this->replaceSrc['{rc_totalMileage}'] = [
            'desc' => __('보유마일리지'),
            'val'  => $replaceData['totalMileage'],
        ];
        $this->replaceSrc['{rc_deleteScheduleDt}'] = [
            'desc' => __('소멸예정일시'),
            'val'  => $replaceData['deleteScheduleDt'],
        ];
    }

    /**
     * 자동발송메일 마일리지 차감 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByRemoveMileage($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('대상아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_mileage}'] = [
            'desc' => __('차감마일리지'),
            'val'  => $replaceData['mileage'],
        ];
        $this->replaceSrc['{rc_totalMileage}'] = [
            'desc' => __('보유마일리지'),
            'val'  => $replaceData['totalMileage'],
        ];
    }

    /**
     * 자동발송메일 마일리지 소멸 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByDeleteMileage($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_mileage}'] = [
            'desc' => __('소멸마일리지'),
            'val'  => $replaceData['mileage'],
        ];
        $this->replaceSrc['{rc_totalMileage}'] = [
            'desc' => __('전체보유마일리지'),
            'val'  => $replaceData['totalMileage'],
        ];
        $this->replaceSrc['{rc_deleteScheduleDt}'] = [
            'desc' => __('소멸예정일시'),
            'val'  => $replaceData['deleteScheduleDt'],
        ];
    }

    /**
     * 자동발송메일 예치금 지급 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByAddDeposit($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_deposit}'] = [
            'desc' => __('지급예치금'),
            'val'  => $replaceData['deposit'],
        ];
        $this->replaceSrc['{rc_totalDeposit}'] = [
            'desc' => __('전체보유예치금'),
            'val'  => $replaceData['totalDeposit'],
        ];
    }

    /**
     * 자동발송메일 예치금 차감 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByRemoveDeposit($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_deposit}'] = [
            'desc' => __('차감예치금'),
            'val'  => $replaceData['deposit'],
        ];
        $this->replaceSrc['{rc_totalDeposit}'] = [
            'desc' => __('전체보유예치금'),
            'val'  => $replaceData['totalDeposit'],
        ];
    }

    /**
     * 자동발송메일 회원탈퇴 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByHackOut($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
    }

    /**
     * 자동발송메일 휴면회원전환 사전안내 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeBySleepNotice($replaceData)
    {
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_sleepScheduleDt}'] = [
            'desc' => __('휴면회원 처리예정일'),
            'val'  => $replaceData['sleepScheduleDt'],
        ];
        $this->replaceSrc['{rc_expirationFl}'] = [
            'desc' => __('개인정보유효기간'),
            'val'  => $replaceData['expirationFl'],
        ];
    }

    /**
     * 자동발송메일 비밀번호찾기 인증번호 치환코드 세팅
     * 자동발송메일 휴면회원해제 인증번호 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByCertification($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_certificationCode}'] = [
            'desc' => __('인증번호'),
            'val'  => $replaceData['certificationCode'],
        ];
    }

    /**
     * 자동발송메일 이메일 수신거부 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByRejectMailingFl($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_rejectDt}'] = [
            'desc' => __('수신거부 처리완료일'),
            'val'  => $replaceData['rejectDt'],
        ];
        $this->replaceSrc['{rc_memEmail}'] = [
            'desc' => __('이메일'),
            'val'  => $replaceData['memEmail'],
        ];
    }

    /**
     * 자동발송메일 게시판 답변 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByQna($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_memId}'] = [
            'desc' => __('아이디'),
            'val'  => $replaceData['memId'],
        ];
        $this->replaceSrc['{rc_boardName}'] = [
            'desc' => __('게시판 제목'),
            'val'  => $replaceData['boardName'],
        ];
        $this->replaceSrc['{rc_regDt}'] = [
            'desc' => __('게시글 등록일시'),
            'val'  => $replaceData['regDt'],
        ];
        $this->replaceSrc['{rc_subject}'] = [
            'desc' => __('질문제목'),
            'val'  => $replaceData['subject'],
        ];
        $this->replaceSrc['{rc_contents}'] = [
            'desc' => __('질문내용'),
            'val'  => $replaceData['contents'],
        ];
        $this->replaceSrc['{rc_answerTitle}'] = [
            'desc' => __('답변제목'),
            'val'  => $replaceData['answerTitle'],
        ];
        $this->replaceSrc['{rc_answerContents}'] = [
            'desc' => __('답변내용'),
            'val'  => $replaceData['answerContents'],
        ];
    }

    /**
     * 자동발송메일 주문내역안내메일 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByOrder($replaceData)
    {
        Logger::info(__METHOD__);
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_orderDt}'] = [
            'desc' => __('주문일시'),
            'val'  => $replaceData['orderDt'],
        ];
        $this->replaceSrc['{rc_orderNm}'] = [
            'desc' => __('주문자명'),
            'val'  => $replaceData['orderNm'],
        ];
        $this->replaceSrc['{rc_orderNo}'] = [
            'desc' => __('주문번호'),
            'val'  => $replaceData['orderNo'],
        ];
        $this->replaceSrc['{rc_settlePrice}'] = [
            'desc' => __('결제금액'),
            'val'  => $replaceData['settlePrice'],
        ];
        $this->replaceSrc['{rc_settleKind}'] = [
            'desc' => __('결제방법'),
            'val'  => $replaceData['settleKind'],
        ];
        $this->replaceSrc['{rc_goods}'] = [
            'desc' => __('주문상품정보'),
            'val'  => $replaceData['goods'],
        ];
        $this->replaceSrc['{rc_gift}'] = [
            'desc' => __('사은품정보'),
            'val'  => $replaceData['gift'],
        ];
        $this->replaceSrc['{rc_settleKind}'] = [
            'desc' => __('결제방법'),
            'val'  => $replaceData['settleKind'],
        ];
        $this->replaceSrc['{rc_totalGoodsPrice}'] = [
            'desc' => __('총 상품구매금액'),
            'val'  => $replaceData['totalGoodsPrice'],
        ];
        $this->replaceSrc['{rc_totalDeliveryCharge}'] = [
            'desc' => __('총 배송비'),
            'val'  => $replaceData['totalDeliveryCharge'],
        ];
        $this->replaceSrc['{rc_totalSumMemberDcPrice}'] = [
            'desc' => __('총 할인금액'),
            'val'  => $replaceData['totalSumMemberDcPrice'],
        ];
        $this->replaceSrc['{rc_useMileage}'] = [
            'desc' => __('사용 마일리지'),
            'val'  => $replaceData['useMileage'],
        ];
        $this->replaceSrc['{rc_useDeposit}'] = [
            'desc' => __('사용 예치금'),
            'val'  => $replaceData['useDeposit'],
        ];
        $this->replaceSrc['{rc_receiverNm}'] = [
            'desc' => __('받는 사람'),
            'val'  => $replaceData['receiverNm'],
        ];
        $this->replaceSrc['{rc_receiverZipcode}'] = [
            'desc' => __('구 우편번호 (6자리)'),
            'val'  => $replaceData['receiverZipcode'],
        ];
        $this->replaceSrc['{rc_receiverZonecode}'] = [
            'desc' => __('우편번호'),
            'val'  => $replaceData['receiverZonecode'],
        ];
        $this->replaceSrc['{rc_receiverAddress}'] = [
            'desc' => __('주소'),
            'val'  => $replaceData['receiverAddress'],
        ];
        $this->replaceSrc['{rc_receiverAddressSub}'] = [
            'desc' => __('상세주소'),
            'val'  => $replaceData['receiverAddressSub'],
        ];
        $this->replaceSrc['{rc_receiverPhone}'] = [
            'desc' => __('전화번호'),
            'val'  => $replaceData['receiverPhone'],
        ];
        $this->replaceSrc['{rc_receiverCellPhone}'] = [
            'desc' => __('휴대폰번호'),
            'val'  => $replaceData['receiverCellPhone'],
        ];
        $this->replaceSrc['{rc_receiverMemo}'] = [
            'desc' => __('배송메시지'),
            'val'  => $replaceData['receiverMemo'],
        ];
        $this->replaceSrc['{rc_receiverPhonePrefix}'] = [
            'desc' => __('전화번호 국가번호'),
            'val'  => $replaceData['receiverPhonePrefix'],
        ];
        $this->replaceSrc['{rc_receiverCellPhonePrefix}'] = [
            'desc' => __('휴대폰번호 국가번호'),
            'val'  => $replaceData['receiverCellPhonePrefix'],
        ];
        $this->replaceSrc['{rc_receiverPhonePrefixCode}'] = [
            'desc' => __('전화번호 국가코드'),
            'val'  => $replaceData['receiverPhonePrefixCode'],
        ];
        $this->replaceSrc['{rc_receiverCellPhonePrefixCode}'] = [
            'desc' => __('휴대폰번호 국가코드'),
            'val'  => $replaceData['receiverCellPhonePrefixCode'],
        ];
        $this->replaceSrc['{rc_receiverCountry}'] = [
            'desc' => __('수취인 국가'),
            'val'  => $replaceData['receiverCountry'],
        ];
        $this->replaceSrc['{rc_receiverCity}'] = [
            'desc' => __('수취인 도시'),
            'val'  => $replaceData['receiverCity'],
        ];
        $this->replaceSrc['{rc_receiverState}'] = [
            'desc' => __('수취인 주소'),
            'val'  => $replaceData['receiverState'],
        ];
        $this->replaceSrc['{rc_totalDeliveryInsuranceFee}'] = [
            'desc' => __('EMS 해외 총 보험료'),
            'val'  => $replaceData['totalDeliveryInsuranceFee'],
        ];
        $this->replaceSrc['{rc_receiverNmAdd}'] = [
            'desc' => __('받는 사람'),
            'val'  => $replaceData['receiverNmAdd'],
        ];
        $this->replaceSrc['{rc_receiverZipcodeAdd}'] = [
            'desc' => __('구 우편번호 (6자리)'),
            'val'  => $replaceData['receiverZipcodeAdd'],
        ];
        $this->replaceSrc['{rc_receiverZonecodeAdd}'] = [
            'desc' => __('우편번호'),
            'val'  => $replaceData['receiverZonecodeAdd'],
        ];
        $this->replaceSrc['{rc_receiverAddressAdd}'] = [
            'desc' => __('주소'),
            'val'  => $replaceData['receiverAddressAdd'],
        ];
        $this->replaceSrc['{rc_receiverAddressSubAdd}'] = [
            'desc' => __('상세주소'),
            'val'  => $replaceData['receiverAddressSubAdd'],
        ];
        $this->replaceSrc['{rc_receiverPhoneAdd}'] = [
            'desc' => __('전화번호'),
            'val'  => $replaceData['receiverPhoneAdd'],
        ];
        $this->replaceSrc['{rc_receiverCellPhoneAdd}'] = [
            'desc' => __('휴대폰번호'),
            'val'  => $replaceData['receiverCellPhoneAdd'],
        ];
        $this->replaceSrc['{rc_orderMemoAdd}'] = [
            'desc' => __('배송메시지'),
            'val'  => $replaceData['orderMemoAdd'],
        ];
    }

    /**
     * 자동발송메일 입금확인 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByIncash($replaceData)
    {
        Logger::debug(__METHOD__);
        $this->replaceSrc['{rc_paymentCheckDt}'] = [
            'desc' => __('입금확인일시'),
            'val'  => DateTimeUtils::dateFormat('Y-m-d H:i', $replaceData['paymentCheckDt']),
        ];
        $this->replaceSrc['{rc_orderNm}'] = [
            'desc' => __('주문자명'),
            'val'  => $replaceData['orderNm'],
        ];
        $this->replaceSrc['{rc_bankSender}'] = [
            'desc' => __('입금자명'),
            'val'  => $replaceData['bankSender'],
        ];
        $this->replaceSrc['{rc_orderNo}'] = [
            'desc' => __('주문번호'),
            'val'  => $replaceData['orderNo'],
        ];
        $this->replaceSrc['{rc_settlePrice}'] = [
            'desc' => __('입금금액'),
            'val'  => $replaceData['settlePrice'],
        ];
        $this->replaceSrc['{rc_bank}'] = [
            'desc' => __('입금은행'),
            'val'  => $replaceData['bank'],
        ];
        $this->replaceSrc['{rc_accountHolder}'] = [
            'desc' => __('예금주'),
            'val'  => $replaceData['accountHolder'],
        ];
        $this->replaceSrc['{rc_accountNumber}'] = [
            'desc' => __('계좌번호'),
            'val'  => $replaceData['accountNumber'],
        ];
    }

    /**
     * 자동발송메일 상품배송 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByDelivery($replaceData)
    {
        Logger::info(__METHOD__);
        $this->replaceSrc['{rc_orderNm}'] = [
            'desc' => __('주문자명'),
            'val'  => $replaceData['orderNm'],
        ];
        $this->replaceSrc['{rc_bankSender}'] = [
            'desc' => __('입금자명'),
            'val'  => $replaceData['bankSender'],
        ];
        $this->replaceSrc['{rc_orderNo}'] = [
            'desc' => __('주문번호'),
            'val'  => $replaceData['orderNo'],
        ];
        $this->replaceSrc['{rc_goods}'] = [
            'desc' => __('상품정보'),
            'val'  => $replaceData['goods'],
        ];
        $this->replaceSrc['{rc_receiverNm}'] = [
            'desc' => __('받는 사람'),
            'val'  => $replaceData['receiverNm'],
        ];
        $this->replaceSrc['{rc_receiverZipcode}'] = [
            'desc' => __('구 우편번호 (6자리)'),
            'val'  => $replaceData['receiverZipcode'],
        ];
        $this->replaceSrc['{rc_receiverZonecode}'] = [
            'desc' => __('우편번호'),
            'val'  => $replaceData['receiverZonecode'],
        ];
        $this->replaceSrc['{rc_receiverAddress}'] = [
            'desc' => __('주소'),
            'val'  => $replaceData['receiverAddress'],
        ];
        $this->replaceSrc['{rc_receiverAddressSub}'] = [
            'desc' => __('상세주소'),
            'val'  => $replaceData['receiverAddressSub'],
        ];
        $this->replaceSrc['{rc_receiverPhone}'] = [
            'desc' => __('전화번호'),
            'val'  => $replaceData['receiverPhone'],
        ];
        $this->replaceSrc['{rc_receiverCellPhone}'] = [
            'desc' => __('휴대폰번호'),
            'val'  => $replaceData['receiverCellPhone'],
        ];
        $this->replaceSrc['{rc_receiverMemo}'] = [
            'desc' => __('배송메시지'),
            'val'  => $replaceData['receiverMemo'],
        ];
        $this->replaceSrc['{rc_receiverPhonePrefix}'] = [
            'desc' => __('전화번호 국가번호'),
            'val'  => $replaceData['receiverPhonePrefix'],
        ];
        $this->replaceSrc['{rc_receiverCellPhonePrefix}'] = [
            'desc' => __('휴대폰번호 국가번호'),
            'val'  => $replaceData['receiverCellPhonePrefix'],
        ];
        $this->replaceSrc['{rc_receiverPhonePrefixCode}'] = [
            'desc' => __('전화번호 국가코드'),
            'val'  => $replaceData['receiverPhonePrefixCode'],
        ];
        $this->replaceSrc['{rc_receiverCellPhonePrefixCode}'] = [
            'desc' => __('휴대폰번호 국가코드'),
            'val'  => $replaceData['receiverCellPhonePrefixCode'],
        ];
        $this->replaceSrc['{rc_receiverCountry}'] = [
            'desc' => __('수취인 국가'),
            'val'  => $replaceData['receiverCountry'],
        ];
        $this->replaceSrc['{rc_receiverCity}'] = [
            'desc' => __('수취인 도시'),
            'val'  => $replaceData['receiverCity'],
        ];
        $this->replaceSrc['{rc_receiverState}'] = [
            'desc' => __('수취인 주소'),
            'val'  => $replaceData['receiverState'],
        ];
        $this->replaceSrc['{rc_receiverNmAdd}'] = [
            'desc' => __('받는 사람'),
            'val'  => $replaceData['receiverNmAdd'],
        ];
        $this->replaceSrc['{rc_receiverZipcodeAdd}'] = [
            'desc' => __('구 우편번호 (6자리)'),
            'val'  => $replaceData['receiverZipcodeAdd'],
        ];
        $this->replaceSrc['{rc_receiverZonecodeAdd}'] = [
            'desc' => __('우편번호'),
            'val'  => $replaceData['receiverZonecodeAdd'],
        ];
        $this->replaceSrc['{rc_receiverAddressAdd}'] = [
            'desc' => __('주소'),
            'val'  => $replaceData['receiverAddressAdd'],
        ];
        $this->replaceSrc['{rc_receiverAddressSubAdd}'] = [
            'desc' => __('상세주소'),
            'val'  => $replaceData['receiverAddressSubAdd'],
        ];
        $this->replaceSrc['{rc_receiverPhoneAdd}'] = [
            'desc' => __('전화번호'),
            'val'  => $replaceData['receiverPhoneAdd'],
        ];
        $this->replaceSrc['{rc_receiverCellPhoneAdd}'] = [
            'desc' => __('휴대폰번호'),
            'val'  => $replaceData['receiverCellPhoneAdd'],
        ];
        $this->replaceSrc['{rc_orderMemoAdd}'] = [
            'desc' => __('배송메시지'),
            'val'  => $replaceData['orderMemoAdd'],
        ];
    }

    /**
     * 장바구니 알림 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByCartRemind($replaceData)
    {
        $this->replaceSrc['{rc_memNm}'] = [
            'desc' => __('회원명'),
            'val'  => $replaceData['memNm'],
        ];
        $this->replaceSrc['{rc_cartRemindLink}'] = [
            'desc' => __('장바구니알림링크'),
            'val'  => $replaceData['cartRemindLink'],
        ];
    }

    /**
     * 자동발송메일 관리자보안 인증번호 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByAdminSecurity($replaceData)
    {
        $this->replaceSrc['{rc_certificationCode}'] = [
            'desc' => __('인증번호'),
            'val'  => $replaceData['certificationCode'],
        ];
    }

    /**
     * 공급사 상품 판매 및 배송을 위한 개인정보 제3자 제공 동의 치환코드 세팅
     *
     * @param $replaceData
     */
    public function setReplaceCodeByScmAgreement($replaceData)
    {
        $this->replaceSrc['{rc_scmNm}'] = [
            'desc' => __('공급사명'),
            'val'  => $replaceData['scmNm'],
        ];
    }

    /**
     * 해외몰 일 때 쇼핑몰명 치환
     *
     * @param $replaceData
     * */
    public function setReplaceCodeByGlobalMall($replaceData)
    {
        $this->replaceSrc['{rc_mallNm}'] = [
            'desc' => __('쇼핑몰 이름'),
            'val'  => $replaceData['mallNm'],
        ];
    }

    /**
     * 기본 치환코드 출력
     * @return array 치환코드배열
     */
    public function getDefinedCode()
    {
        return $this->replaceSrc;
    }

    /**
     * 치환코드 반환
     *
     * @param null $code
     *
     * @return string
     */
    public function getReplaceCode($code = null)
    {
        if ($code === null) {
            if (ArrayUtils::isEmpty($this->replaceSrc) === false) {
                foreach ($this->replaceSrc as $key => $val) {
                    $arrData[str_replace(
                        [
                            '{',
                            '}',
                        ], '', $key
                    )] = $val['val'];
                }
            }
        } else {
            $arrData = $this->replaceSrc['{' . $code . '}']['val'];
        }

        return gd_isset($arrData);
    }

    /**
     * 치환
     *
     * @param      $str
     * @param null $addArgs
     *
     * @return mixed
     */
    public function replace($str, $addArgs = null)
    {
        $source = [];
        $target = [];
        foreach ($this->replaceSrc as $key => $val) {
            $source[] = $key;
            $target[] = $val['val'];
        }
        $output = str_ireplace($source, $target, $str);
        if (ArrayUtils::isEmpty($addArgs) === false) {
            $output = $this->appendBracesAndReplace($output, $addArgs);
        }

        return $output;
    }

    /**
     * 배열 키 를 치환코드 형태로 변환하여 치환
     *
     * @param string $str
     * @param array  $args
     *
     * @return mixed
     */
    public function appendBracesAndReplace($str, array $args)
    {
        $source = $target = [];
        foreach ($args as $key => $arg) {
            $source[] = '{' . $key . '}';
            $target[] = $arg;
        }

        return str_ireplace($source, $target, $str);
    }
}
