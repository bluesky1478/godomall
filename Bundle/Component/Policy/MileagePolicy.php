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

namespace Bundle\Component\Policy;


use Globals;
use Message;

/**
 * Class MileagePolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class MileagePolicy extends \Component\Policy\Policy
{
    /**
     * @inheritDoc
     */
    public function saveMileageBasic(array $getValue)
    {
        gd_isset($getValue['payUsableFl'], 'y');
        gd_isset($getValue['name'], '마일리지');
        gd_isset($getValue['unit'], '원');
        gd_isset($getValue['expiryFl'], 'n');
        gd_isset($getValue['expiryDays'], 0);
        gd_isset($getValue['expiryBeforeDays'], '30');
        gd_isset($getValue['expirySms'], 1);
        gd_isset($getValue['expiryEmail'], 1);
        gd_isset($getValue['goodsPrice'], 1);
        gd_isset($getValue['optionPrice'], 0);
        gd_isset($getValue['addGoodsPrice'], 0);
        gd_isset($getValue['textOptionPrice'], 0);
        gd_isset($getValue['goodsDcPrice'], 0);
        gd_isset($getValue['memberDcPrice'], 0);
        gd_isset($getValue['couponDcPrice'], 0);

        if ($getValue['expiryFl'] == 'y' && $getValue['expiryDays'] < 1) {
            throw new \Exception('마일리지 유효기간을 설정하세요.');
        }

        unset($getValue['mode']);

        gd_remove_comma($getValue['expiryDays'], '-');
        gd_remove_comma($getValue['expiryBeforeDays'], '-');

        if ($this->setValue('member.mileageBasic', $getValue) != true) {
            throw new \Exception(__('마일리지 기본 설정 정보 저장이 실패했습니다.'), 500);
        } else {
            $give = Globals::get('gSite.member.mileageGive');
            if (gd_isset($give['updateGiveFl'], 'self') != 'self' || $getValue['payUsableFl'] == 'n') {
                $give['updateGiveFl'] = 'basic';
                $give['giveFl'] = $getValue['payUsableFl'];
                $this->saveMileageGive($give);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function saveMileageGive(array $getValue)
    {
        gd_isset($getValue['updateGiveFl'], 'self'); // 지급 설정의 주체(self: 지급설정, basic: 기본설정)
        gd_isset($getValue['giveFl'], 'y'); // 지급 여부
        gd_isset($getValue['giveType'], 'price'); // 지급 방법
        if ($getValue['giveType'] == 'price') { // 구매금액의 %
            gd_isset($getValue['goods'], 0); // %
            unset($getValue['goodsPriceUnit']);
            unset($getValue['goodsMileage']);
            unset($getValue['cntMileage']);
        } else if ($getValue['giveType'] == 'priceUnit') { // 구매금액당 지급
            gd_isset($getValue['goodsPriceUnit'], 0); // 구매금액당
            gd_isset($getValue['goodsMileage'], 0); // 원 지급
            unset($getValue['goods']);
            unset($getValue['cntMileage']);
        } else if ($getValue['giveType'] == 'cntUnit') { // 수량(개)당 지급
            gd_isset($getValue['cntMileage'], 0); // 원 지급
            unset($getValue['goods']);
            unset($getValue['goodsPriceUnit']);
            unset($getValue['goodsMileage']);
        }
        gd_isset($getValue['excludeFl'], 'y'); // 마일리지 사용시 지급예외 여부
        gd_isset($getValue['delayFl'], ''); // 지급 유예기능 사용여부
        if ($getValue['delayFl'] == 'y') { // 지급 유예기능 사용여부
            gd_isset($getValue['delayDay'], '7'); // 지급 유예일자
        } else {
            unset($getValue['delayDay']);
        }
        gd_isset($getValue['joinFl'], 'y'); // 신규회원가입시 지급 여부
        gd_isset($getValue['joinAmount'], 0); // 신규회원가입시 지급 마일리지
        gd_isset($getValue['emailFl'], 'n'); // 신규회원가입 이메일 수신동의시 지급 여부
        gd_isset($getValue['emailAmount'], 0); // 신규회원가입 이메일 수신동의시 지급 마일리지
        gd_isset($getValue['smsFl'], 'n'); // 신규회원가입 SMS 수신동의시 지급 여부
        gd_isset($getValue['smsAmount'], 0); // 신규회원가입 SMS 수신동의시 지급 마일리지
        gd_isset($getValue['recommJoinAmount'], 0); // 추천인 등록시 신규회원 지급 마일리지
        gd_isset($getValue['recommAmount'], 0); // 추천인 등록시 추천인에게 지급 마일리지
        gd_isset($getValue['recommCountFl'], 'n'); // 추천인 등록시 추천인에게 지급횟수 제한 여부
        gd_isset($getValue['recommCount'], 0); // 추천인 등록시 추천인에게 지급횟수
        gd_isset($getValue['birthAmount'], 0); // 생일자 지급 마일리지
        unset($getValue['mode']);

        if ($getValue['recommCount'] > 99999) {
            throw new \Exception('지급횟수는 99,999회까지 설정 가능합니다.', 500);
        }

        $basic = gd_policy('member.mileageBasic');
        if ($getValue['updateGiveFl'] == 'self' && $getValue['giveFl'] == 'y' && $basic['payUsableFl'] == 'n') {
            throw new \Exception(__('마일리지 기본 설정이 사용안함 상태입니다. 지급 설정 사용함으로 변경을 할 수 없습니다.'), 500);
        }

        gd_remove_comma($getValue['goods'], '-');
        gd_remove_comma($getValue['joinAmount'], '-');
        gd_remove_comma($getValue['emailAmount'], '-');
        gd_remove_comma($getValue['smsAmount'], '-');
        gd_remove_comma($getValue['recommJoinAmount'], '-');
        gd_remove_comma($getValue['recommAmount'], '-');
        gd_remove_comma($getValue['recommCount'], '-');
        gd_remove_comma($getValue['birthAmount'], '-');

        if ($this->setValue('member.mileageGive', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'), 500);
        }
    }

    /**
     * @inheritDoc
     */
    public function saveMileageUse(array $getValue)
    {
        //--- 기본값 설정
        gd_isset($getValue['minimumHold'], '0');
        gd_isset($getValue['orderAbleLimit'], '0');
        gd_isset($getValue['standardPrice'], 'goodsPrice');
        gd_isset($getValue['minimumLimit'], '0');
        gd_isset($getValue['maximumLimit'], '0');
        gd_isset($getValue['maximumLimitUnit'], 'percent');
        gd_isset($getValue['maximumLimitDeliveryFl'], 'y');
        unset($getValue['mode']);

        if ($getValue['maximumLimitUnit'] === 'mileage') {
            if ($getValue['maximumLimit'] < $getValue['minimumLimit']) {
                throw new \Exception(__('사용 마일리지 제한 금액을 확인 하세요.'));
            }
        }
        if ($getValue['maximumLimitUnit'] === 'percent') {
            if ($getValue['maximumLimit'] > 100) {
                throw new \Exception(__('최대 사용 마일리지 금액을 확인 하세요.'));
            }
        }

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['minimumHold'], '-');
        gd_remove_comma($getValue['orderAbleLimit'], '-');
        gd_remove_comma($getValue['minimumLimit'], '-');
        gd_remove_comma($getValue['maximumLimit'], '-');

        if ($this->setValue('member.mileageUse', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

}
