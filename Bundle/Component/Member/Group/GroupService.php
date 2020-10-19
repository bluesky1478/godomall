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
namespace Bundle\Component\Member\Group;

use Component\Member\Manager;
use Component\Validator\Validator;
use Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Session;

/**
 * Class GroupService
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class GroupService
{
    /** @var  GroupDAO */
    protected $groupDAO;
    /** @var  Util */
    protected $groupUtil;

    /**
     * GroupService constructor.
     *
     * @param GroupDAO|null $groupDAO  등급 DB
     * @param Util          $groupUtil 등급유틸
     */
    public function __construct(GroupDAO $groupDAO = null, Util $groupUtil = null)
    {
        if ($groupDAO === null) {
            $groupDAO = new GroupDAO();
        }
        $this->groupDAO = $groupDAO;

        if ($groupUtil === null) {
            $groupUtil = new Util();
        }
        $this->groupUtil = $groupUtil;
    }

    /**
     * 등급 등록 및 수정
     *
     * @throws Exception 등록오류
     */
    public function saveGroup()
    {
        if (\Request::post()->get('groupMarkGb') === 'icon' && \Request::post()->get('groupIcon', '') === '') {
            \Request::post()->set('groupMarkGb', 'text');
        }
        if (\Request::post()->get('groupMarkGb') === 'upload') {
            if (\Request::post()->get('fileGroupIconDeleteFl') === 'y') {
                \Request::files()->set('fileGroupIcon', ['name' => '']);
                \Request::post()->set('groupIconUpload', '');
            }
            if (\Request::files()->get('fileGroupIcon')['name'] === '' && \Request::post()->get('groupIconUpload', '') === '') {
                \Request::post()->set('groupMarkGb', 'text');
            }
        }
        if (\Request::post()->get('groupImageGb') === 'image' && \Request::post()->get('groupImage', '') === '') {
            \Request::post()->set('groupImageGb', 'none');
        }
        if (\Request::post()->get('groupImageGb') === 'upload') {
            if (\Request::post()->get('fileGroupImageDeleteFl') === 'y') {
                \Request::files()->set('fileGroupImage', ['name' => '']);
                \Request::post()->set('groupImageUpload', '');
            }
            if (\Request::files()->get('fileGroupImage')['name'] === '' && \Request::post()->get('groupImageUpload', '') === '') {
                \Request::post()->set('groupImageGb', 'none');
            }
        }
        if (\Request::post()->get('settleGb')) {
            \Request::post()->set('settleGb', $this->groupUtil->matchSettleGbDataToString(\Request::post()->get('settleGb')));
        }
        $manager = Session::get(Manager::SESSION_MANAGER_LOGIN);
        \Request::post()->set('regId', $manager['managerId']);
        \Request::post()->set('managerNo', $manager['sno']);

        $group = \Request::post()->all();

        //@formatter:off
        $commaRemoverFilter = ['dcLine', 'overlapDcLine', 'mileageLine',];
        $jsonEncodeFilter = ['fixedRateOption', 'dcExCategory', 'dcExOption', 'dcExBrand', 'dcExGoods', 'dcExScm',
                             'overlapDcCategory', 'overlapDcOption', 'overlapDcBrand', 'overlapDcGoods', 'overlapDcScm',];
        //@formatter:on
        ArrayUtils::commaRemover($group, $commaRemoverFilter);
        $group = ArrayUtils::jsonEncode($group, $jsonEncodeFilter);
        $group['groupCoupon'] = implode(INT_DIVISION, $group['couponNo']);
        $group['dcBrandInfo'] = str_replace('\'', '', json_encode($group['dcBrandInfo'], JSON_UNESCAPED_UNICODE)); // 추가 할인 브랜드별 할인

        \Request::post()->setData($group);

        $validator = $this->validateGroup();

        $fields = $validator->getEleName();
        if (\Request::post()->get('sno', 0) > 0) {
            $this->groupDAO->updateGroup($group, $fields);
        } else {
            $group['groupSort'] = $this->groupDAO->selectNewGroupSort();
            $sno = $this->groupDAO->insertGroup($group, $fields);
            \Request::post()->set('sno', $sno);
        }

        $this->groupUtil->uploadGroupIcon();
        $this->groupUtil->uploadGroupImage();

        $group = [];
        if (\Request::post()->has(Util::PREFIX_UPLOAD_GROUP_ICON)) {
            $group['groupIconUpload'] = \Request::post()->get(Util::PREFIX_UPLOAD_GROUP_ICON);
        }
        if (\Request::post()->has(Util::PREFIX_UPLOAD_GROUP_IMAGE)) {
            $group['groupImageUpload'] = \Request::post()->get(Util::PREFIX_UPLOAD_GROUP_IMAGE);
        }

        if (count($group) > 0) {
            $group['sno'] = \Request::post()->get('sno');
            $this->groupDAO->updateGroup($group, array_keys($group));
        }
    }

    /**
     * 회원등급별 평가기준 검증 및 저장
     *
     * @throws Exception
     */
    public function saveGroupStandard()
    {
        $this->validateAppraisalSystem();

        $manager = Session::get(Manager::SESSION_MANAGER_LOGIN);
        \Request::post()->set('regId', $manager['managerId']);
        \Request::post()->set('managerNo', $manager['sno']);
        $groupSno = $this->getGroupsSno();
        $groups = [];
        foreach ($groupSno as $sno) {
            $groups[] = $this->getStandard($sno);
        }
        foreach ($groups as $index => $group) {
            $fields = [];
            if (\Request::post()->get('apprSystem') == 'figure') {
                $validator = $this->validateStandardFigure($group);
                $this->validateAppraisalFigure($group);
                $this->checkOverlapFigure($group, $groups);
                $fields = $validator->getEleName();
            } elseif (\Request::post()->get('apprSystem') == 'point') {
                $validator = $this->validateStandardPoint($group);
                $this->validateAppraisalPoint($group);
                $this->checkOverlapPoint($group, $groups);
                $fields = $validator->getEleName();
            }
            $this->groupDAO->updateGroup($group, $fields);
        }
    }

    /**
     * 실적 수치제 평가 기준 입력 값 검증
     *
     * @param array $group
     *
     * @return Validator
     * @throws Exception
     */
    public function validateStandardFigure(array $group)
    {
        $validator = new Validator();
        $validator->init();
        $validator->add('sno', 'number');
        $validator->add('apprFigureOrderPriceFl', 'yn');
        $validator->add('apprFigureOrderRepeatFl', 'yn');
        $validator->add('apprFigureReviewRepeatFl', 'yn');
        $validator->add('apprFigureOrderPriceMore', 'number');
        $validator->add('apprFigureOrderPriceBelow', 'number');
        $validator->add('apprFigureOrderRepeat', 'number');
        $validator->add('apprFigureReviewRepeat', 'number');
        $validator->add('apprFigureOrderPriceMoreMobile', 'number');
        $validator->add('apprFigureOrderPriceBelowMobile', 'number');
        $validator->add('apprFigureOrderRepeatMobile', 'number');
        $validator->add('apprFigureReviewRepeatMobile', 'number');
        if ($validator->act($group, true) === false) {
            \Logger::error(__METHOD__, $validator->errors);
            throw new Exception(implode('<br/>', $validator->errors));
        }

        return $validator;
    }

    /**
     * 실적 점수제 평가 기준 입력 값 검증
     *
     * @param array $group
     *
     * @return Validator
     * @throws Exception
     */
    public function validateStandardPoint(array $group)
    {
        $validator = new Validator();
        $validator->init();
        $validator->add('sno', 'number');
        $validator->add('apprPointMore', 'number');
        $validator->add('apprPointBelow', 'number');
        $validator->add('apprPointMoreMobile', 'number');
        $validator->add('apprPointBelowMobile', 'number');
        if ($validator->act($group, true) === false) {
            \Logger::error(__METHOD__, $validator->errors);
            throw new Exception(implode('<br/>', $validator->errors));
        }

        return $validator;
    }

    /**
     * 기본 등급인 일반회원은 제외 처리 후 등급 번호 반환
     *
     * @return array
     */
    protected function getGroupsSno()
    {
        $groupsSno = \Request::post()->get('sno', []);
        if (($key = array_search(1, $groupsSno)) !== false) {
            unset($groupsSno[$key]);
        }

        return $groupsSno;
    }

    /**
     * 실적 수치제 입력 값 검증
     *
     * @param array $group
     *
     * @throws Exception
     */
    public function validateAppraisalFigure(array $group)
    {
        if ($group['apprFigureOrderPriceFl'] == 'y') {
            if ($group['apprFigureOrderPriceMore'] < 1 || $group['apprFigureOrderPriceBelow'] < 1) {
                throw new Exception(__('주문금액은 1만원 이상이어야 합니다.'));
            }
            if ($group['apprFigureOrderPriceMore'] >= $group['apprFigureOrderPriceBelow']) {
                throw new Exception(__('쇼핑몰 전체실적 주문금액 범위를 다시 입력해 주세요.'));
            }
            if ($group['apprFigureOrderPriceMoreMobile'] > 0 || $group['apprFigureOrderPriceBelowMobile'] > 0) {
                if ($group['apprFigureOrderPriceMoreMobile'] >= $group['apprFigureOrderPriceBelowMobile']) {
                    throw new Exception(__('모바일샵 추가실적 주문금액 범위를 다시 입력해 주세요.'));
                }
            }
        }
        if ($group['apprFigureOrderRepeatFl'] == 'y' && $group['apprFigureOrderRepeat'] < 1) {
            throw new Exception(__('상품주문건수는 1회 이상이어야 합니다.'));
        }
        if ($group['apprFigureReviewRepeatFl'] == 'y' && $group['apprFigureReviewRepeat'] < 1) {
            throw new Exception(__('주문상품후기는 1회 이상이어야 합니다.'));
        }
    }

    /**
     * 실적 점수제 입력 값 검증
     *
     * @param array $group
     *
     * @throws Exception
     */
    public function validateAppraisalPoint(array $group)
    {
        if ($group['apprPointMore'] < 1 || $group['apprPointBelow'] < 1) {
            throw new Exception(__('실적 점수는 1점 이상이어야 합니다.'));
        }
        if ($group['apprPointMore'] >= $group['apprPointBelow']) {
            throw new Exception(__('쇼핑몰 전체실적 점수 범위를 다시 입력해 주세요.'));
        }
        if ($group['apprPointMoreMobile'] > 0 || $group['apprPointBelowMobile'] > 0) {
            if ($group['apprPointMoreMobile'] >= $group['apprPointBelowMobile']) {
                throw new Exception(__('모바일샵 추가실적 점수 범위를 다시 입력해 주세요.'));
            }
        }
    }

    /**
     * 실적 수치제 입력 값 중복 체크
     *
     * @param array $target
     * @param array $groups
     *
     * @throws Exception
     */
    public function checkOverlapFigure(array $target, array $groups)
    {
        foreach ($groups as $group) {
            if ($target['sno'] == $group['sno']) {
                continue;
            }
            $messages = [];
            if ($target['apprFigureOrderPriceFl'] == 'y') {
                if ($target['apprFigureOrderPriceMore'] >= $group['apprFigureOrderPriceMore'] && $target['apprFigureOrderPriceMore'] < $group['apprFigureOrderPriceBelow']) {
                    $messages[] = __('주문금액 시작 값 중복입니다.');
                }
                if ($target['apprFigureOrderPriceBelow'] > $group['apprFigureOrderPriceMore'] && $target['apprFigureOrderPriceBelow'] < $group['apprFigureOrderPriceBelow']) {
                    $messages[] = __('주문금액 종료 값 중복입니다.');
                }
            }
            if ($target['apprFigureOrderRepeatFl'] == 'y' && $target['apprFigureOrderRepeat'] == $group['apprFigureOrderRepeat']) {
                $messages[] = __('상품주문건수 기준이 중복입니다.');
            }
            if ($target['apprFigureReviewRepeatFl'] == 'y' && $target['apprFigureReviewRepeat'] == $group['apprFigureReviewRepeat']) {
                $messages[] = __('주문상품후기 기준이 중복입니다.');
            }
            if (count($messages) > 0) {
                throw new Exception(implode('<br/>', $messages));
            }
        }
    }

    /**
     * 실적 점수제 입력 값 중복 체크
     *
     * @param array $target
     * @param array $groups
     *
     * @throws Exception
     */
    public function checkOverlapPoint(array $target, array $groups)
    {
        foreach ($groups as $group) {
            if ($target['sno'] == $group['sno']) {
                continue;
            }
            $messages = [];
            if ($target['apprPointMore'] >= $group['apprPointMore'] && $target['apprPointMore'] < $group['apprPointBelow']) {
                $messages[] = __('쇼핑몰 전체실적 점수 시작 값 중복입니다.');
            }
            if ($target['apprPointBelow'] > $group['apprPointMore'] && $target['apprPointBelow'] < $group['apprPointBelow']) {
                $messages[] = __('쇼핑몰 전체실적 점수 종료 값 중복입니다.');
            }
            if (count($messages) > 0) {
                throw new Exception(implode('<br/>', $messages));
            }
        }
    }

    /**
     * 회원등급 평가방법 검증
     *
     * @throws Exception
     */
    public function validateAppraisalSystem()
    {
        if (\Request::post()->has('apprSystem') === false) {
            throw new Exception(__('회원등급 평가방법이 선택되지 않았습니다.'));
        }
        if (\Request::post()->get('apprSystem') != 'figure' && \Request::post()->get('apprSystem') != 'point') {
            throw new Exception(__('존재하지 않는 회원등급 평가방법입니다.'));
        }
    }

    /**
     * 등급별 평가 기준 데이터 반환
     *
     * @param integer $sno 등급번호
     *
     * @return array 평가 기준
     */
    public function getStandard($sno)
    {
        $apprFigureOrderPriceFl = \Request::post()->get('apprFigureOrderPriceFl', [])[$sno];
        $apprFigureOrderRepeatFl = \Request::post()->get('apprFigureOrderRepeatFl', [])[$sno];
        $apprFigureReviewRepeatFl = \Request::post()->get('apprFigureReviewRepeatFl', [])[$sno];

        return [
            'sno'                             => $sno,
            'apprFigureOrderPriceFl'          => gd_isset($apprFigureOrderPriceFl, 'n'),
            'apprFigureOrderRepeatFl'         => gd_isset($apprFigureOrderRepeatFl, 'n'),
            'apprFigureReviewRepeatFl'        => gd_isset($apprFigureReviewRepeatFl, 'n'),
            'apprFigureOrderPriceMore'        => \Request::post()->get('apprFigureOrderPriceMore', [])[$sno],
            'apprFigureOrderPriceBelow'       => \Request::post()->get('apprFigureOrderPriceBelow', [])[$sno],
            'apprFigureOrderRepeat'           => \Request::post()->get('apprFigureOrderRepeat', [])[$sno],
            'apprFigureReviewRepeat'          => \Request::post()->get('apprFigureReviewRepeat', [])[$sno],
            'apprPointMore'                   => \Request::post()->get('apprPointMore', [])[$sno],
            'apprPointBelow'                  => \Request::post()->get('apprPointBelow', [])[$sno],
            'apprFigureOrderPriceMoreMobile'  => \Request::post()->get('apprFigureOrderPriceMoreMobile', [])[$sno],
            'apprFigureOrderPriceBelowMobile' => \Request::post()->get('apprFigureOrderPriceBelowMobile', [])[$sno],
            'apprFigureOrderRepeatMobile'     => \Request::post()->get('apprFigureOrderRepeatMobile', [])[$sno],
            'apprFigureReviewRepeatMobile'    => \Request::post()->get('apprFigureReviewRepeatMobile', [])[$sno],
            'apprPointMoreMobile'             => \Request::post()->get('apprPointMoreMobile', [])[$sno],
            'apprPointBelowMobile'            => \Request::post()->get('apprPointBelowMobile', [])[$sno],
        ];
    }

    /**
     * 등급 저장 데이터 검증
     *
     * @return Validator
     * @throws Exception 검증 오류
     */
    public function validateGroup()
    {
        $validator = new Validator();
        $validator->init();
        $validator->setIgnoreIssetByAct(true);
        if (\Request::post()->get('sno', 0) > 0) {
            $validator->add('sno', '', true);
        }
        $validator->add('groupNm', 'require', true);
        $validator->add('groupSort', 'number');
        $validator->add('groupMarkGb', 'require', true);
        $validator->add('groupImageGb', 'require', true);
        $validator->add('apprExclusionOfRatingFl', '');
        $validator->add('groupIcon', '');
        $validator->add('groupImage', '');
        $validator->add('groupIconUpload', '');
        $validator->add('groupImageUpload', '');
        $validator->add('settleGb', 'require');
        $validator->add('fixedRateOption', '');
        $validator->add('fixedRatePrice', '');
        $validator->add('fixedOrderTypeDc', '');
        $validator->add('dcExOption', '');
        $validator->add('dcExScm', '');
        $validator->add('dcExCategory', '');
        $validator->add('dcExBrand', '');
        $validator->add('dcExGoods', '');
        $validator->add('fixedOrderTypeOverlapDc', '');
        $validator->add('overlapDcOption', '');
        $validator->add('overlapDcScm', '');
        $validator->add('overlapDcCategory', '');
        $validator->add('overlapDcBrand', '');
        $validator->add('overlapDcGoods', '');
        $validator->add('fixedOrderTypeMileage', '');
        $validator->add('dcLine', 'double');
        $validator->add('dcType', '');
        $validator->add('dcPercent', 'double', false, '', 0, 100);
        $validator->add('deliveryFree', '');
        if (\Request::post()->get('dcType') === 'price') {
            $validator->add('dcPrice', 'double');
        }
        $validator->add('overlapDcType', '');
        $validator->add('overlapDcLine', 'double', false, '{'.__('중복할인').'}');
        $validator->add('overlapDcPercent', 'double', false, '', 0, 100);
        if (\Request::post()->get('overlapDcType') === 'price') {
            $validator->add('overlapDcPrice', 'double');
        }
        $validator->add('mileageLine', 'double', false, '{'.__('추가 마일리지 적립').'}');
        $validator->add('mileageType', '');
        $validator->add('mileagePercent', 'double', false, '', 0, 100);
        if (\Request::post()->get('mileageType') === 'price') {
            $validator->add('mileagePrice', 'double');
        }

        // 추가할인 브랜별 무통장 할인율 검증
        if (\Request::post()->get('fixedOrderTypeDc') == 'brand' && \Request::post()->get('dcBrandInfo')) {
            $dcBrandInfo = json_decode(\Request::post()->get('dcBrandInfo'));
            $dcBrandPercent = [];
            foreach ($dcBrandInfo as $dcKey => $dcInfo) {
                // 브랜드별 할인
                if ($dcKey === 'goodsDiscount') {
                    foreach ($dcInfo as $key => $brandDiscount) {
                        $dcBrandPercent[$key] += $brandDiscount;
                    }
                }
            }

            foreach ($dcBrandPercent as $key => $sumDcBrandPercent) {
                $sumDcPercent = $sumDcBrandPercent + \Request::post()->get('overlapDcPercent');
                \Request::post()->set('sumDcPercent_'.$key, $sumDcPercent);
                $validator->add('sumDcPercent_'.$key, 'memberDcDouble', false, '{'.__('추가할인과 중복할인의').'}', 0, 100);
            }
        } else {
            $sumDcPercent = \Request::post()->get('dcPercent') + \Request::post()->get('overlapDcPercent');
            \Request::post()->set('sumDcPercent', $sumDcPercent);
            $validator->add('sumDcPercent', 'memberDcDouble', false, '{'.__('추가할인과 중복할인의').'}', 0, 100);
        }

        $validator->add('deliveryFreeFl', 'yn');
        $validator->add('groupCoupon', '');
        $validator->add('regId', 'userid');
        $validator->add('managerNo', '');
        $validator->add('dcBrandInfo', '');

        $group = \Request::post()->all();
        if ($validator->act($group, true) === false) {
            // 추가할인 브랜별 무통장 할인율 검증
            if (\Request::post()->get('dcBrandInfo')) {
                $validator->errors = array_unique($validator->errors);
            }
            \Logger::error(__METHOD__, $validator->errors);
            throw new Exception(implode("\n", $validator->errors));
        }
        if ($this->groupDAO->countGroupName(\Request::post()->get('groupNm'), \Request::post()->get('sno', 0)) > 0) {
            throw new Exception(sprintf(__('%s는 이미 등록된 등급이름입니다'), \Request::post()->get('groupNm')));
        }
        \Request::post()->setData($group);

        return $validator;
    }

    /**
     * 등급 데이터 조회
     *
     * @return array|object 등급정보
     * @throws Exception
     */
    public function getGroup()
    {
        if (\Request::request()->get('sno', 0) < 1) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), __('회원번호')));
        }
        $group = $this->groupDAO->selectGroup(\Request::request()->get('sno'));

        $tmp = $this->groupUtil->getAppraisalString($group);
        $group['evaluateStr'] = str_replace("\n", ', ', (empty($tmp) ? __('미설정') : $tmp));

        $group['fixedRateOption'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['fixedRateOption']), true);
        $group['dcExCategory'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['dcExCategory']), true);
        $group['dcExOption'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['dcExOption']), true);
        $group['dcExBrand'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['dcExBrand']), true);
        $group['dcExGoods'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['dcExGoods']), true);
        $group['dcExScm'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['dcExScm']), true);
        $group['overlapDcCategory'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['overlapDcCategory']), true);
        $group['overlapDcOption'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['overlapDcOption']), true);
        $group['overlapDcBrand'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['overlapDcBrand']), true);
        $group['overlapDcGoods'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['overlapDcGoods']), true);
        $group['overlapDcScm'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['overlapDcScm']), true);
        $group['dcBrandInfo'] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($group['dcBrandInfo']), true);

        $group['dcExCategory'] = $this->groupUtil->getDiscountCategory($group['dcExCategory']);
        $group['dcExBrand'] = $this->groupUtil->getDiscountBrand($group['dcExBrand']);
        $group['dcExGoods'] = $this->groupUtil->getDiscountGoods($group['dcExGoods']);
        $group['dcExScm'] = $this->groupUtil->getDiscountScm($group['dcExScm']);
        $group['overlapDcCategory'] = $this->groupUtil->getOverlapDiscountCategory($group['overlapDcCategory']);
        $group['overlapDcBrand'] = $this->groupUtil->getOverlapDiscountBrand($group['overlapDcBrand']);
        $group['overlapDcGoods'] = $this->groupUtil->getOverlapDiscountGoods($group['overlapDcGoods']);
        $group['overlapDcScm'] = $this->groupUtil->getDiscountScm($group['overlapDcScm']);


        $group['groupIconHtml'] = $this->groupUtil->groupIconToWebPath(null);
        $group['uploadGroupIconHtml'] = $this->groupUtil->groupIconToWebPath(null);
        if ($group['groupIcon'] !== '') {
            $group['groupIconHtml'] = $this->groupUtil->groupIconToWebPath($group['groupIcon']);
        }
        if ($group['groupIconUpload'] !== '') {
            $group['uploadGroupIconHtml'] = $this->groupUtil->groupIconToWebPath($group['groupIconUpload']);
        }

        $group['groupImageHtml'] = $this->groupUtil->groupImageToWebPath(null);
        $group['uploadGroupImageHtml'] = $this->groupUtil->groupImageToWebPath(null);
        if ($group['groupImage'] !== '') {
            $group['groupImageHtml'] = $this->groupUtil->groupImageToWebPath($group['groupImage']);
        }
        if ($group['groupImageUpload'] !== '') {
            $group['uploadGroupImageHtml'] = $this->groupUtil->groupImageToWebPath($group['groupImageUpload']);
        }

        gd_isset($group['groupMarkGb'], 'text');
        gd_isset($group['groupImageGb'], 'none');
        gd_isset($group['apprExclusionOfRatingFl'], 'n');
        gd_isset($group['settleGb'], 'all');
        gd_isset($group['dcType'], 'percent');
        gd_isset($group['overlapDcType'], 'percent');
        gd_isset($group['mileageType'], 'percent');
        gd_isset($group['fixedRatePrice'], 'price');
        gd_isset($group['fixedOrderTypeDc'], 'option');
        gd_isset($group['fixedOrderTypeOverlapDc'], 'option');
        gd_isset($group['fixedOrderTypeMileage'], 'option');

        return $group;
    }

    /**
     * 등급이름 중복체크
     *
     * @return bool
     * @throws Exception
     */
    public function checkOverlapGroupName()
    {
        $groupName = \Request::request()->get('groupNm', '');
        if ($groupName == '') {
            throw new Exception(__('등급명이 없습니다.'));
        }
        $count = $this->groupDAO->countGroupName($groupName, \Request::request()->get('sno', 0));

        return $count > 0;
    }
}
