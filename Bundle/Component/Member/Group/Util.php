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

use App;
use Component\AbstractComponent;
use Component\Category\CategoryAdmin;
use Component\Goods\GoodsAdmin;
use Component\Scm\ScmAdmin;
use Component\Storage\Storage;
use DateTime;
use Exception;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use UserFilePath;
use DateTimeZone;

/**
 * 회원등급 유틸리티 클래스
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class Util extends \Component\AbstractComponent
{
    const PREFIX_UPLOAD_GROUP_ICON = 'ico_s_member_upload';
    const PREFIX_UPLOAD_GROUP_IMAGE = 'ico_member_upload';
    /** @var array 회원등급 정책 */
    protected $memberGroupConfig;

    /**
     * Util constructor.
     *
     * @param null $config
     */
    function __construct($config = null)
    {
        parent::__construct();
        $this->tableFunctionName = 'tableMemberGroup';

        if ($config == null) {
            $this->memberGroupConfig = gd_policy('member.group');
        }
    }

    /**
     * 등급 평가 기준 문장 리턴
     *
     * @static
     *
     * @param array $data 데이터
     *
     * @return string 문장
     */
    public static function getAppraisalString(array $data)
    {
        $tmp = [];
        if (empty($data['apprPointMore']) === false && empty($data['apprPointBelow']) === false) {
            array_push($tmp, sprintf(__('%s점 ~ %s점'), number_format($data['apprPointMore']), number_format($data['apprPointBelow'])));
        } elseif (empty($data['apprPointMore']) === false && empty($data['apprPointBelow']) === true) {
            array_push($tmp, sprintf(__('%s점 이상'), number_format($data['apprPointMore'])));
        } elseif (empty($data['apprPointMore']) === true && empty($data['apprPointBelow']) === false) {
            array_push($tmp, sprintf(__('%s점 미만'), number_format($data['apprPointBelow'])));
        } else {
            if (empty($data['apprFigureOrderPriceMore']) === false && empty($data['apprFigureOrderPriceBelow']) === false) {
                array_push($tmp, sprintf(__('%s만원 ~ %s만원 구매'), number_format($data['apprFigureOrderPriceMore']), number_format($data['apprFigureOrderPriceBelow'])));
            } elseif (empty($data['apprFigureOrderPriceMore']) === false && empty($data['apprFigureOrderPriceBelow']) === true) {
                array_push($tmp, sprintf(__('%s만원 이상 구매'), number_format($data['apprFigureOrderPriceMore'])));
            } elseif (empty($data['apprFigureOrderPriceMore']) === true && empty($data['apprFigureOrderPriceBelow']) === false) {
                array_push($tmp, sprintf(__('%s만원 미만 구매'), number_format($data['apprFigureOrderPriceBelow'])));
            }
            if (empty($data['apprFigureOrderRepeat']) === false) {
                array_push($tmp, sprintf(__('%s회 이상 구매'), number_format($data['apprFigureOrderRepeat'])));
            }
            if (empty($data['apprFigureReviewRepeat']) === false) {
                array_push($tmp, sprintf(__('%s회 이상 후기'), number_format($data['apprFigureReviewRepeat'])));
            }
        }
        if (count($tmp) > 0) {
            $evaluateStr = implode("\n", $tmp);
        } else {
            $evaluateStr = '';
        }

        return $evaluateStr;
    }

    /**
     * 등급 혜택 문장 리턴
     *
     * @param  array $data 데이터
     *
     * @return string 문장
     */
    public static function getBenefitString(array $data)
    {
        $tmp = [];
        if ($data['dcType'] == 'percent' && empty($data['dcPercent']) === false && $data['dcPercent'] > 0 && $data['fixedOrderTypeDc'] !== 'brand' && $data['overlapDcBankFl'] === 'n') {
            array_push($tmp, sprintf(__('추가 %s 할인'), $data['dcPercent'] . '%'));
        } elseif ($data['dcType'] == 'price' && empty($data['dcPrice']) === false && $data['dcPrice'] > 0) {
            array_push($tmp, sprintf(__('추가 %s원 할인'), number_format($data['dcPrice'])));
        }
        if ($data['overlapDcType'] == 'percent' && empty($data['overlapDcType']) === false && $data['overlapDcPercent'] > 0) {
            array_push($tmp, sprintf(__('중복 %s 할인'), $data['overlapDcPercent'] . '%'));
        } elseif ($data['overlapDcType'] == 'price' && empty($data['overlapDcType']) === false && $data['overlapDcPrice'] > 0) {
            array_push($tmp, sprintf(__('중복 %s원 할인'), number_format($data['overlapDcPrice'])));
        }
        if ($data['mileageType'] == 'percent' && empty($data['mileagePercent']) === false && $data['mileagePercent'] > 0) {
            array_push($tmp, sprintf(__('추가 %s 적립'), $data['mileagePercent'] . '%'));
        } elseif ($data['mileageType'] == 'price' && empty($data['mileagePrice']) === false && $data['mileagePrice'] > 0) {
            array_push($tmp, sprintf(__('추가 %s원 적립'), number_format($data['mileagePrice'])));
        }
        if (count($tmp) > 0) {
            $benefitStr = implode("\n", $tmp);
        } else {
            $benefitStr = '';
        }

        return $benefitStr;
    }

    /**
     * 회원등급 그룹 아이콘의 http 경로를 반환하는 함수
     *
     * @static
     *
     * @param null $sno
     *
     * @return array
     */
    public static function getGroupIconHttpPath($sno = null)
    {
        if (!is_null($sno)) {
            /** @var \Framework\Database\DBTool $db */
            $db = \App::load('DB');
            $groupInfo = $db->getData(DB_MEMBER_GROUP, $sno, 'sno');

            $imagePath = UserFilePath::data('commonimg', 'ico_noimg_16.gif')->www();
            if (strlen($groupInfo['groupIcon']) > 0) {
                $imagePath = UserFilePath::icon('group_icon')->www() . '/' . $groupInfo['groupIcon'];
            }

            return $imagePath;
        }
        $data = Storage::disk(Storage::PATH_CODE_GROUP_ICON, 'local')->listContents();

        $groupIcon = [];
        foreach ($data as $file) {
            if ($file['type'] == 'file' && strpos($file['filename'], 'upload') === false) {
                $groupIcon[] = Storage::disk(Storage::PATH_CODE_GROUP_ICON)->getHttpPath($file['path']);
            }
        }
        sort($groupIcon);

        return $groupIcon;
    }

    /**
     * 회원등급 그룹 이미지 http 경로를 반환하는 함수
     *
     * @static
     *
     * @param null $sno
     *
     * @return array
     */
    public static function getGroupImageHttpPath($sno = null)
    {
        if (!is_null($sno)) {
            /** @var \Framework\Database\DBTool $db */
            $db = \App::load('DB');
            $groupInfo = $db->getData(DB_MEMBER_GROUP, $sno, 'sno');

            $imagePath = UserFilePath::data('commonimg', 'ico_noimg_75.gif')->www();
            if ($groupInfo['groupImageGb'] == 'upload') {
                $imagePath = UserFilePath::icon('group_image')->www() . '/' . $groupInfo['groupImageUpload'];
            } elseif (strlen($groupInfo['groupImage']) > 0 && $groupInfo['groupImage'] != 'ico_noimg_75.gif') {
                $imagePath = UserFilePath::icon('group_image')->www() . '/' . $groupInfo['groupImage'];
            }

            return $imagePath;
        }
        $data = Storage::disk(Storage::PATH_CODE_GROUP_IMAGE, 'local')->listContents();
        $groupImage = [];
        foreach ($data as $file) {
            if ($file['type'] == 'file' && strpos($file['filename'], 'upload') === false) {
                $groupImage[] = Storage::disk(Storage::PATH_CODE_GROUP_IMAGE)->getHttpPath($file['path']);
            }
        }
        sort($groupImage);

        return $groupImage;
    }

    /**
     * 중복할인 공급사 정보
     *
     * @static
     *
     * @param $overlapDcScm
     *
     * @return array
     */
    public static function getOverlapDiscountScm($overlapDcScm)
    {
        $arrayData = [];
        $scmAdmin = new ScmAdmin();
        if (is_array($overlapDcScm)) {
            foreach ($overlapDcScm as $scmNo) {
                array_shift($overlapDcScm);
                if (gd_isset($scmNo, '') !== '') {
                    $arrayData[] = $scmAdmin->getScmInfo($scmNo, 'scmNo, companyNm');
                }
            }
        }

        return $arrayData;
    }

    /**
     * 중복할인 카테고리 정보
     *
     * @static
     *
     * @param $overlapDcCategory
     *
     * @return array
     */
    public static function getOverlapDiscountCategory($overlapDcCategory)
    {
        $arrayData = [];
        $categoryAdmin = new CategoryAdmin();
        if (is_array($overlapDcCategory)) {
            foreach ($overlapDcCategory as $cateCd) {
                array_shift($overlapDcCategory);
                if (gd_isset($cateCd, '') !== '') {
                    $arrayData[] = [
                        'cateCd' => $cateCd,
                        'cateNm' => $categoryAdmin->getCategoryPosition($cateCd),
                    ];
                }
            }
        }

        return $arrayData;
    }

    /**
     * 중복할인 브랜드 정보
     *
     * @static
     *
     * @param $overlapDcBrand
     *
     * @return array
     */
    public static function getOverlapDiscountBrand($overlapDcBrand)
    {
        $arrayData = [];
        $brandAdmin = new CategoryAdmin('brand');
        if (is_array($overlapDcBrand)) {
            foreach ($overlapDcBrand as $brandCd) {
                array_shift($overlapDcBrand);
                if (gd_isset($brandCd, '') !== '') {
                    $arrayData[] = [
                        'brandCd' => $brandCd,
                        'brandNm' => $brandAdmin->getCategoryPosition($brandCd, 'brand'),
                    ];
                }
            }
        }

        return $arrayData;
    }

    /**
     * 중복할인 상품정보
     *
     * @static
     *
     * @param $overlapDcGoods
     *
     * @return array
     */
    public static function getOverlapDiscountGoods($overlapDcGoods)
    {
        $arrayData = [];
        $goodsAdmin = new GoodsAdmin();
        if (is_array($overlapDcGoods)) {
            $overlapDcGoods = $goodsAdmin->getGoodsDataDisplay(implode(INT_DIVISION, $overlapDcGoods));
            foreach ($overlapDcGoods as $data) {
                array_shift($overlapDcGoods);
                $data['goodsImage'] = gd_html_goods_image($data['goodsNo'], $data['imageName'], $data['imagePath'], $data['imageStorage'], 50, $data['goodsNm'], '_blank');
                $data['goodsNm'] = strip_tags($data['goodsNm']);
                $arrayData[] = $data;
            }
        }

        return $arrayData;
    }

    /**
     * 추가할인적용 제외 특정 공급사
     *
     * @static
     *
     * @param $dcExScm
     *
     * @return array
     */
    public static function getDiscountScm($dcExScm)
    {

        $arrayData = [];
        $scmAdmin = new ScmAdmin();
        if (is_array($dcExScm)) {
            foreach ($dcExScm as $scmNo) {
                array_shift($dcExScm);
                if (gd_isset($scmNo, '') !== '') {
                    $arrayData[] = $scmAdmin->getScmInfo($scmNo, 'scmNo, companyNm');
                }
            }
        }

        return $arrayData;
    }

    /**
     * 추가할인적용제외 특정 상품
     *
     * @static
     *
     * @param $dcExGoods
     *
     * @return array
     */
    public static function getDiscountGoods($dcExGoods)
    {
        $arrayData = [];
        $goodsAdmin = new GoodsAdmin();
        if (is_array($dcExGoods)) {
            $dcExGoods = $goodsAdmin->getGoodsDataDisplay(implode('||', $dcExGoods));
            foreach ($dcExGoods as $data) {
                array_shift($dcExGoods);
                $data['goodsImage'] = gd_html_goods_image($data['goodsNo'], $data['imageName'], $data['imagePath'], $data['imageStorage'], 50, $data['goodsNm'], '_blank');
                $data['goodsNm'] = strip_tags($data['goodsNm']);
                $arrayData[] = $data;
            }
        }

        return $arrayData;
    }

    /**
     * 추가할인적용제외 특정 브랜드
     *
     * @static
     *
     * @param $dcExBrand
     *
     * @return array
     */
    public static function getDiscountBrand($dcExBrand)
    {
        $arrayData = [];
        $brandAdmin = new CategoryAdmin('brand');
        if (is_array($dcExBrand)) {
            foreach ($dcExBrand as $brandCd) {
                array_shift($dcExBrand);
                if (gd_isset($brandCd, '') !== '') {
                    $arrayData[] = [
                        'brandCd' => $brandCd,
                        'brandNm' => $brandAdmin->getCategoryPosition($brandCd),
                    ];
                }
            }
        }

        return $arrayData;
    }

    /**
     * 추가할인적용제외 특정 카테고리
     *
     * @static
     *
     * @param $dcExCategory
     *
     * @return array
     */
    public static function getDiscountCategory($dcExCategory)
    {
        $arrayData = [];
        $categoryAdmin = new CategoryAdmin();
        if (is_array($dcExCategory)) {
            foreach ($dcExCategory as $cateCd) {
                array_shift($dcExCategory);
                if (gd_isset($cateCd, '') !== '') {
                    $arrayData[] = [
                        'cateCd' => $cateCd,
                        'cateNm' => $categoryAdmin->getCategoryPosition($cateCd),
                    ];
                }
            }
        }

        return $arrayData;
    }

    /**
     * 기본그룹 여부를 확인하는 함수
     *
     * @static
     *
     * @param $groupSno
     *
     * @return mixed
     */
    public static function checkDefaultGroup($groupSno)
    {
        $emptyGroup = self::countMemberGroup() == 0;
        $defaultGroupInfo['disabled'] = $emptyGroup ? ['disabled' => true] : null;
        $isDefaultGroup = self::getDefaultGroupSno() == $groupSno;
        $defaultGroupInfo['default'] = $isDefaultGroup || $emptyGroup ? 'y' : 'n';

        return $defaultGroupInfo;
    }

    /**
     * 현재 등록된 그룹의 수 반환
     *
     * @return mixed
     */
    public static function countMemberGroup()
    {
        $db = App::load('DB');
        $strSQL = 'SELECT COUNT(sno) as memberGroupCount FROM ' . DB_MEMBER_GROUP;
        $data = $db->query_fetch($strSQL);

        return $data[0]['memberGroupCount'];
    }

    /**
     * 가입회원 등급 반환 함수
     *
     * @static
     * @return mixed
     */
    public static function getDefaultGroupSno()
    {
        $policy = gd_policy('member.group');
        gd_isset($policy['defaultGroupSno'], 1);

        return $policy['defaultGroupSno'];
    }

    /**
     * 등급 레벨 정보
     *
     * @param null $addWhere
     *
     * @return string|array
     */
    public static function getGroupName($addWhere = null)
    {
        $db = App::load('DB');
        $getData = [];
        $strSQL = 'SELECT sno, groupNm FROM ' . DB_MEMBER_GROUP;
        if ($addWhere) $strSQL .= " WHERE " . $addWhere;

        $result = $db->query($strSQL);
        while ($data = $db->fetch($result)) {
            $getData[$data['sno']] = $data['groupNm'];
        }

        return StringUtils::htmlSpecialCharsStripSlashes($getData);
    }

    /**
     * 그룹 등급별 결제 수단 항목 배열 반환
     *
     * @static
     *
     * @param string|null $key 결제수단 코드
     *
     * @return array
     */
    public static function getSettleGbData($key = null)
    {
        $data = [
            'gb'   =>__('무통장'),
            'pg' =>__('PG결제수단'),
            'gm'   =>__('마일리지 사용'),
            'gd' =>__('예치금 사용'),
        ];

        if ($key === null) {
            return $data;
        }

        return $data[$key];
    }

    public static function matchSettleGbDataToString($mValue)
    {
        if (is_array($mValue)) {
            $sValue = '';
            foreach ($mValue as $v) {
                $sValue .= $v;
            }
            if ($sValue == 'gbpggmgd') $sValue = 'all';
            if ($sValue == 'gbgmgd') $sValue = 'bank';
            if ($sValue == 'pggmgd') $sValue = 'nobank';

            return $sValue;
        } else {
            if ($mValue == 'all') $mValue = 'gbpggmgd';
            if ($mValue == 'bank') $mValue = 'gbgmgd';
            if ($mValue == 'nobank') $mValue = 'pggmgd';
            $aValue = array();
            $iMax = strlen($mValue);
            for ($iStep = 0; $iStep < $iMax; ) {
                $aValue[] = substr($mValue, $iStep, 2);
                $iStep += 2;
            }
            return $aValue;
        }
    }

    public static function getSettleGbStringData($key = null)
    {
        $data = [
            'all'    => __('제한없음'),
            'bank'   => __('무통장') . ' / ' . __('마일리지 사용') . ' / ' . __('예치금 사용'),
            'nobank' => __('PG결제수단') . ' / ' . __('마일리지 사용') . ' / ' . __('예치금 사용'),
            'gbpggm' => __('무통장') . ' / ' . __('PG결제수단') . ' / ' . __('마일리지 사용'),
            'gbpggd' => __('무통장') . ' / ' . __('PG결제수단') . ' / ' . __('예치금 사용'),
            'gbpg' => __('무통장') . ' / ' . __('PG결제수단'),
            'gbgm' => __('무통장') . ' / ' . __('마일리지 사용'),
            'gbgd' => __('무통장') . ' / ' . __('예치금 사용'),
            'pggm' => __('PG결제수단') . ' / ' . __('마일리지 사용'),
            'pggd' => __('PG결제수단') . ' / ' . __('예치금 사용'),
            'gmgd' => __('마일리지 사용') . ' / ' . __('예치금 사용'),
            'gb' => __('무통장'),
            'pg' => __('PG결제수단'),
            'gm' => __('마일리지 사용'),
            'gd' => __('예치금 사용'),
        ];

        if ($key === null) {
            return $data;
        }

        return $data[$key];
    }

    /**
     * 정률 할인/적립 시 구매금액 기준 항목 배열을 반환
     *
     * @static
     * @return array
     */
    public static function getFixedRateOptionData()
    {
        return [
            'option' =>__('옵션가'),
            'goods'  =>__('추가상품가'),
            'text'   =>__('텍스트옵션가'),
        ];
    }

    /**
     * 상품 할인/적립 혜택 제외 항목
     * @static
     * @return array
     */
    public static function getExceptBenefitData()
    {
        return [
            'add' =>__('회원 추가 할인혜택 적용 제외'),
            'overlap'  =>__('회원 중복 할인혜택 적용 제외'),
            'mileage'   =>__('회원 추가 마일리지 적립 적용 제외'),
            'coupon'   =>__('상품쿠폰 할인/적립 혜택 적용 제외'),
        ];
    }

    /**
     * 할인/적립 시 적용금액 기준
     *
     * @static
     * @return array
     */
    public static function getFixedRatePriceData()
    {
        return [
            'price' =>__('판매금액'),
            'settle'  =>__('결제금액'),
        ];
    }

    /**
     * 할인/적립 상품 기준
     *
     * @static
     * @return array
     */
    public static function getFixedOrderTypeData($key = null)
    {
        $data = [
            'option' =>__('옵션별'),
            'goods'  =>__('상품별'),
            'order'  =>__('주문별'),
            'brand'  =>__('브랜드별'),
        ];

        if ($key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * 추가할인적용제외, 중복할인적용제외 항목 배열을 반환
     *
     * @static
     * @return array
     */
    public static function getDcOptionData()
    {
        $setData = [
            'scm'      =>__('특정 공급사'),
            'category' =>__('특정 카테고리'),
            'brand'    =>__('특정 브랜드'),
            'goods'    =>__('특정 상품'),
        ];

        // 플러샵 이용에 따른 공급사 제외 처리
        if (!GodoUtils::isPlusShop(PLUSSHOP_CODE_SCM)) {
            unset($setData['scm']);
        }

        return $setData;
    }

    /**
     * @param array $memberGroupConfig
     */
    public function setMemberGroupConfig($memberGroupConfig)
    {
        $this->memberGroupConfig = $memberGroupConfig;
    }

    /**
     * 자동등급평가 설정 여부 확인 함수
     *
     * @return bool
     */
    public function isAutoAppraisal()
    {
        return $this->memberGroupConfig['automaticFl'] == 'y';
    }

    /**
     * 등급평가일 여부 확인 함수
     *
     * @param string $date
     * @return bool
     */
    public function isAppraisalDay($date = null)
    {
        // 일광시간제 체크
        $utc = new DateTimeZone("UTC");
        // 이전 평가일
        $dateTime = new DateTime($this->memberGroupConfig['autoAppraisalDateTime'], $utc);
        // 평가 될 기준일 (금일)
        $nowDateTime = new DateTime($date, $utc);
        // 설정된 기준 개월
        $calcCycleMonth = $this->memberGroupConfig['calcCycleMonth'];
        // 설정된 기준 일
        $calcCycleDay = $this->memberGroupConfig['calcCycleDay'];
        $diffMonth = $dateTime->diff($nowDateTime)->m;
        // 개월 기준 평가 확인
        $isAppraisalMonth = ($diffMonth >= $calcCycleMonth) || $diffMonth == 0;
        // 일 기준 평가 확인
        $isAppraisalDay = $nowDateTime->format('d') == $calcCycleDay;

        return $isAppraisalMonth && $isAppraisalDay;
    }

    /**
     * 회원등급 아이콘 업로드
     *
     * @throws Exception 업로드 오류
     * @return boolean
     */
    public function uploadGroupIcon()
    {
        $file = \Request::files()->get('fileGroupIcon');
        $file['width'] = 16;
        $this->uploadGroupSymbol($file, Storage::PATH_CODE_GROUP_ICON, self::PREFIX_UPLOAD_GROUP_ICON);
    }

    /**
     * 회원등급 이미지 업로드
     *
     * @throws Exception 업로드 오류
     * @return boolean
     */
    public function uploadGroupImage()
    {
        if (\Request::files()->has('fileGroupImage')) {
            $file = \Request::files()->get('fileGroupImage');
            $this->uploadGroupSymbol($file, Storage::PATH_CODE_GROUP_IMAGE, self::PREFIX_UPLOAD_GROUP_IMAGE);
        }
    }

    /**
     * 회원등급 이미지 및 아이콘 업로드
     *
     * @param array $file
     * @param       $storagePathCode
     * @param       $filePrefix
     *
     * @throws Exception
     * @return boolean
     */
    protected function uploadGroupSymbol(array $file, $storagePathCode, $filePrefix)
    {
        if (\Request::post()->get('sno', 0) < 1) {
            throw new Exception(__('회원등급번호가 없습니다.'));
        }
        if (gd_file_uploadable($file, 'image')) {
            $logoPath = $filePrefix . \Request::post()->get('sno', '') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $tmpImageFile = $file['tmp_name'];
            list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
            $option = [
                'width'     => $file['width'],
                'quality'   => 'high',
                'overWrite' => true,
            ];
            Storage::disk($storagePathCode, 'local')->upload($tmpImageFile, $logoPath, $option);
            if (Storage::disk($storagePathCode, 'local')->isFileExists($logoPath)) {
                \Request::post()->set($filePrefix, $logoPath);
            }
        } elseif ($file['name'] !== '') {
            \Logger::warning(__METHOD__ . ' 업로드가 불가능한 이미지 입니다.', $file);
        }
    }

    /**
     * 등급이미지의 웹경로를 반환
     *
     * @param $fileName
     *
     * @return string
     */
    public function groupImageToWebPath($fileName)
    {
        gd_isset($fileName, 'ico_noimg_75.gif');
        $path = UserFilePath::data('commonimg')->www() . '/ico_noimg_75.gif';
        if ($fileName !== 'ico_noimg_75.gif' && UserFilePath::icon('group_image', $fileName)->isExists()) {
            $path = UserFilePath::icon('group_image')->www() . '/' . $fileName;
        }

        return $path;
    }

    /**
     * 등급아이콘의 웹경로를 반환
     *
     * @param $fileName
     *
     * @return string
     */
    public function groupIconToWebPath($fileName)
    {
        gd_isset($fileName, 'ico_noimg_16.gif');
        $path = UserFilePath::data('commonimg')->www() . '/ico_noimg_16.gif';
        if ($fileName !== 'ico_noimg_16.gif' && UserFilePath::icon('group_icon', $fileName)->isExists()) {
            $path = UserFilePath::icon('group_icon')->www() . '/' . $fileName;
        }

        return $path;
    }
}
