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
namespace Bundle\Controller\Admin\Goods;

use Exception;
use Framework\Utility\ImageUtils;
use Component\Member\Group\Util;
use Globals;
use Request;
use Session;

/**
 * 상품 등록 / 수정 페이지
 */
class GoodsRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        // --- 메뉴 설정
        if (Request::get()->has('goodsNo')) { // 수정인 경우
            $this->callMenu('goods', 'goods', 'modify');
        } else { // 등록인 경우
            $this->callMenu('goods', 'goods', 'register');
        }

        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $paycoConfig = $dbUrl->getConfig('payco', 'config');
        // --- 각 설정값

        $conf['currency'] = Globals::get('gCurrency');
        $conf['mileage'] = Globals::get('gSite.member.mileageGive'); // 마일리지 지급 여부
        $conf['mileageBasic'] = Globals::get('gSite.member.mileageBasic'); // 마일리지 기본설정
        $conf['mileage'] = Globals::get('gSite.member.mileageGive'); // 마일리지 지급 여부
        $conf['goods'] = gd_policy('mileage.goods'); // 상품 관련 마일리지 설정
        $conf['image'] = gd_policy('goods.image'); // 이미지 설정
        $conf['tax'] = gd_policy('goods.tax'); // 과세/비과세 설정
        $conf['mobile'] = gd_policy('mobile.config'); // 모바일샵 설정
        $conf['qrcode'] = gd_policy('promotion.qrcode'); // QR코드 설정

        $conf['mileageBasic']['mileageText'] = '판매가';
        if ($conf['mileageBasic']['optionPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+옵션가';
        if ($conf['mileageBasic']['addGoodsPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+추가상품가';
        if ($conf['mileageBasic']['textOptionPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+텍스트옵션가';
        if ($conf['mileageBasic']['goodsDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+상품할인가';
        if ($conf['mileageBasic']['memberDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+회원할인가';
        if ($conf['mileageBasic']['couponDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+쿠폰할인가';

        // 지급마일리지 설정
        gd_isset($conf['mileage']['giveType'], 'price'); // 지급 기준
        if ($conf['mileage']['giveType'] == 'price') { // 구매금액의 %
            gd_isset($conf['mileage']['goods'], 0); // %
        } else if ($conf['mileage']['giveType'] == 'priceUnit') { // 구매금액당 지급
            gd_isset($conf['mileage']['goodsPriceUnit'], 0); // 구매금액당
            gd_isset($conf['mileage']['goodsMileage'], 0); // 원 지급
        } else if ($conf['mileage']['giveType'] == 'cntUnit') { // 수량(개)당 지급
            gd_isset($conf['mileage']['cntMileage'], 0); // 원 지급
        }

        // 이미지 사이즈 설정
        foreach ($conf['image'] as $k => $v) {
            foreach ($v as $key => $value) {
                if (stripos($key, 'size') === 0) {
                    if ($conf['image']['imageType'] == 'fixed') {
                        $conf['image'][$k]['fixed'.$key] = [$value, $conf['image'][$k]['h' . $key]];
                        unset($conf['image'][$k]['h' . $key]);
                    }
                }
            }
            if (stripos($k, 'imageType') === 0) {
                $imageType = $conf['image']['imageType'];
                unset($conf['image']['imageType']);
            }
        }

        $tmpStorageConf = gd_policy('basic.storage'); // 저장소 설정
        $defaultImageStorage = '';
        if (gd_is_provider() === true) {
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            $scmNo = Session::get('manager.scmNo');
            $scmData = $scmAdmin->getScm($scmNo);
        }
        // 저장소 디폴트 없을 경우
        if (empty($tmpStorageConf['storageDefault']) === true) {
            $tmpStorageConf['storageDefault'] = array('imageStorage0' => array('goods'));
        }
        if (is_null($getValue['goodsNo']) && is_null($getValue['applyNo'])) {
            foreach ($tmpStorageConf['storageDefault'] as $index => $item) {
                if (in_array('goods', $item)) {
                    if (gd_isset($scmData['imageStorage'])) {
                        // 공급사 이미지 저장소 위치
                        $defaultImageStorage = $scmData['imageStorage'];
                    } else {
                        $defaultImageStorage = $tmpStorageConf['httpUrl'][$index];
                    }
                }
            }
        }
        foreach ($tmpStorageConf['httpUrl'] as $key => $val) {
            $conf['storage'][$val] = $tmpStorageConf['storageName'][$key];
        }
        unset($tmpStorageConf);

        // --- 이미지 설정 순서 변경
        ImageUtils::sortImageConf($conf['image']);

        // --- 모듈 설정
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $populate = \App::load('\\Component\\Goods\\Populate');
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

        // --- 상품 설정
        if (gd_isset($getValue['applyNo'])) {
            // --- 상품 정보 관리로 접속된 경우 (등록 수정시 다른 상품의 적용을 누른 경우 - 즉 다른 상품의 정보가 입력이 됨)
            $applyGoodsCopy = true;

            try {
                $data = $goods->getDataGoods($getValue['applyNo'], $conf['tax'], true);

                // 이미지 설정 초기화
                //unset($data['data']['image']); // 이미지 초기화
                //unset($data['data']['imageStorage']); // 이미지 저장소 초기화
                //unset($data['data']['imagePath']); // 이미지 저장 경로 초기화
                //unset($data['data']['optionIcon']); // 옵션 추가 노출 초기화

                // 수정인 경우
                if (gd_isset($getValue['goodsNo'])) {
                    $tmpData = $goods->getGoodsInfo($getValue['goodsNo'], 'g.imageStorage,g.imagePath'); // 기존 상품 정보
                    $data['data']['mode'] = 'modify';
                    $data['data']['goodsNo'] = $getValue['goodsNo'];
                    //$data['data']['imageStorage'] = $tmpData['imageStorage'];
                    //$data['data']['imagePath'] = $tmpData['imagePath'];
                    unset($tmpData);

                    // 등록인 경우
                } else {
                    $data['data']['mode'] = 'register';
                    $data['data']['goodsNo'] =
                    $data['data']['seoTagSno'] = null;
                    //$data['data']['imageStorage'] = 'local';
                    //$data['data']['imagePath'] = null;
                }

            } catch (Except $e) {
                //$e->actLog();
                echo($e->ectMessage);
            }
        } else {
            // --- 일반적인 경우
            $applyGoodsCopy = false;
            try {
                $data = $goods->getDataGoods(gd_isset($getValue['goodsNo']), $conf['tax']);
            } catch (Exception $e) {
                throw $e;
            }
        }

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        $this->addCss(
            [
                '../script/jquery/colorpicker/colorpicker.css',
            ]
        );
        $this->addScript(
            [
                'jquery/jquery.multi_select_box.js',
            ]
        );

        $conf['storage']['url'] = __("URL 직접입력");
        if (empty($defaultImageStorage) === false) {
            $data['data']['imageStorage'] = $defaultImageStorage;
        }

        // 회원그룹리스트
        $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);

        // 할인금액 기준
        $fixedGoodsDiscount = Util::getFixedRateOptionData();
        unset($fixedGoodsDiscount['goods']);

        // 메인 상품진열 정보
        //$displayTheme = $goods->getAdminListDisplayTheme('main', 'layer');
        $mainDisplayTheme = $goods->getAdminMainDisplayTheme();

        // 테마설정 정보
        $themeConfig = $goods->getAdminThemeConfig($mainDisplayTheme);

        // 인기상품 노출 정보
        //$populateInfo = $populate->getPopulateList();
        $populateInfo = $populate->getPopulateData();

        //상품 혜택 정보
        if (gd_isset($getValue['applyNo'])) {
            $goodsBenefitData = $goodsBenefit->getGoodsLink($getValue['applyNo']);
            if (empty($goodsBenefitData['data'])) {
                $data['data']['goodsBenefitSetFl'] = 'n';
                $data['checked']['goodsBenefitSetFl']['y'] = '';
                $data['checked']['goodsBenefitSetFl']['n'] = 'checked="checked"';
            }
        }else {
            if (Request::get()->has('goodsNo')) { // 수정인 경우
                $goodsBenefitData = $goodsBenefit->getGoodsLink($data['data']['goodsNo']);
                if (empty($goodsBenefitData['data'])) {
                    $goodsBenefitData = $goodsBenefit->getGoodsEndLink($data['data']['goodsNo']);
                }
            }
        }

        // 상품 수정인 경우
        if($getValue['goodsNo']){
            foreach($mainDisplayTheme as $mdThemeKey => $mdThemeVal){
                foreach($themeConfig as $tcKey => $tcVal){
                    // 탭진열형일때
                    if ($tcVal['displayType'] == '07' && ($mdThemeVal['themeCd'] == $tcVal['themeCd'])) {
                        $arrGoodsNo = explode(STR_DIVISION, $mdThemeVal['goodsNo']); // 탭구분기호로 나눔
                        $detailSet = stripslashes($tcVal['detailSet']);  // 슬래시 제거
                        $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                        for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                            $arrVal = $arrGoodsNo[$i];
                            $tabThemeGoodsNo = explode(INT_DIVISION, $arrVal);
                            if (in_array($getValue['goodsNo'], $tabThemeGoodsNo)) {
                                $mainDisplayTheme[$mdThemeKey]['checkSno'] = $mdThemeVal['sno'];
                            }
                        }
                    } else {
                        $arrTabGoodsNo = explode(INT_DIVISION, $mdThemeVal['goodsNo']);
                        $exceptArrGoodsNo = explode(INT_DIVISION, $mdThemeVal['exceptGoodsNo']);
                        if ((in_array($getValue['goodsNo'], $arrTabGoodsNo) && !in_array($getValue['goodsNo'], $exceptArrGoodsNo)) || ($mdThemeVal['sortAutoFl'] == 'y' && !in_array($getValue['goodsNo'], $exceptArrGoodsNo))) {
                            $mainDisplayTheme[$mdThemeKey]['checkSno'] = $mdThemeVal['sno'];
                        }
                    }
                }
            }
            // 인기상품 포함 상태
            foreach($populateInfo as $pKey => $pVal){
                $populateArrGoodsNo = explode(INT_DIVISION, $pVal['goodsNo']);
                $populateExceptArrGoodsNo = explode(INT_DIVISION, $pVal['except_goodsNo']);
                //if(in_array($getValue['goodsNo'], $populateArrGoodsNo) || (!in_array($getValue['goodsNo'], $populateExceptArrGoodsNo) || $pVal['range'] == 'all')){
                if((in_array($getValue['goodsNo'], $populateArrGoodsNo) && !in_array($getValue['goodsNo'], $populateExceptArrGoodsNo)) || ($pVal['range'] == 'all' && !in_array($getValue['goodsNo'], $populateExceptArrGoodsNo))){
                    $populateInfo[$pKey]['checkSno'] = $pVal['sno'];
                }
            }
        }

        // 기존상품 복사인 경우
        if($getValue['applyNo']){
            foreach($mainDisplayTheme as $mdThemeKey => $mdThemeVal){
                foreach($themeConfig as $tcKey => $tcVal) {
                    // 탭진열형일때
                    if ($tcVal['displayType'] == '07' && ($mdThemeVal['themeCd'] == $tcVal['themeCd'])) {
                        $copyArrGoodsNo = explode(STR_DIVISION, $mdThemeVal['goodsNo']); // 탭구분기호로 나눔
                        $detailSet = stripslashes($tcVal['detailSet']);  // 슬래시 제거
                        $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                        for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                            $copyStrGoodsNo = $copyArrGoodsNo[$i];
                            $copyTabThemeGoodsNo = explode(INT_DIVISION, $copyStrGoodsNo);
                            if (in_array($getValue['applyNo'], $copyTabThemeGoodsNo)) {
                                $mainDisplayTheme[$mdThemeKey]['checkSno'] = $mdThemeVal['sno'];
                            }
                        }
                    } else {
                        $copyGoodsNo = explode(INT_DIVISION, $mdThemeVal['goodsNo']);
                        $copyExceptArrGoodsNo = explode(INT_DIVISION, $mdThemeVal['exceptGoodsNo']);
                        if ((in_array($getValue['applyNo'], $copyGoodsNo) && !in_array($getValue['applyNo'], $copyExceptArrGoodsNo)) || ($mdThemeVal['sortAutoFl'] == 'y' && !in_array($getValue['applyNo'], $copyExceptArrGoodsNo))) {
                            $mainDisplayTheme[$mdThemeKey]['checkSno'] = $mdThemeVal['sno'];
                        }
                    }
                }
            }

            // 인기상품 포함 상태
            foreach($populateInfo as $pKey => $pVal){
                $copyPopulateArrGoodsNo = explode(INT_DIVISION, $pVal['goodsNo']);
                $copyPopulateExceptArrGoodsNo = explode(INT_DIVISION, $pVal['except_goodsNo']);
                //if((in_array($getValue['applyNo'], $copyPopulateArrGoodsNo)) && (!in_array($getValue['applyNo'], $copyPopulateExceptArrGoodsNo)) || $pVal['range'] == 'all')){
                if((in_array($getValue['applyNo'], $copyPopulateArrGoodsNo) && !in_array($getValue['applyNo'], $copyPopulateExceptArrGoodsNo)) || ($pVal['range'] == 'all' && !in_array($getValue['applyNo'], $copyPopulateExceptArrGoodsNo))){
                    $populateInfo[$pKey]['checkSno'] = $pVal['sno'];
                }
            }
        }

        // 재고 수정 권한에 따른 상품 재고 비활성화
        if (empty($getValue['goodsNo']) === false && Session::get('manager.functionAuth.goodsStockModify') != 'y') {
            $disabled['stockCnt'] = $disabled['option_stockCntApply'] = $disabled['optionY[stockCnt][]'] = 'disabled="disabled"';
        }

        // KC인증 정보 처리
        $data['data']['kcmarkInfo'] = json_decode($data['data']['kcmarkInfo'], true);
        gd_isset($data['data']['kcmarkInfo']['kcmarkFl'], 'n');
        $data['checked']['kcmarkFl'][$data['data']['kcmarkInfo']['kcmarkFl']] = 'checked="checked"';
        if ($data['data']['kcmarkInfo']['kcmarkFl'] === 'n') {
            $display = 'display-none';
        }

        $this->setData('conf', $conf);
        $this->setData('cate', $cate);
        $this->setData('data', gd_htmlspecialchars($data['data']));
        $this->setData('checked', $data['checked']);
        $this->setData('display', $display);
        $this->setData('selected', $data['selected']);
        $this->setData('popupMode', gd_isset($getValue['popupMode']));
        $this->setData('applyGoodsCopy', $applyGoodsCopy);
        $this->setData('applyNo', $getValue['applyNo']);
        $this->setData('goodsStateList', $goods->getGoodsStateList());
        $this->setData('goodsPermissionList', $goods->getGoodsPermissionList());
        $this->setData('goodsColorList', $goods->getGoodsColorList(true));
        $this->setData('goodsPayLimit', $goods->getGoodsPayLimit());
        $this->setData('goodsImportType', $goods->getGoodsImportType());
        $this->setData('goodsSellType', $goods->getGoodsSellType());
        $this->setData('goodsAgeType', $goods->getGoodsAgeType());
        $this->setData('goodsGenderType', $goods->getGoodsGenderType());
        $this->setData('fixedSales', $goods->getFixedSales());
        $this->setData('fixedOrderCnt', $goods->getFixedOrderCnt());
        $this->setData('hscode', $goods->getHscode());
        $this->setData('kcmarkDivFl', $goods->getKcmarkcode());
        $this->setData('imageInfo', gd_policy('goods.image'));
        $this->setData('imageType', $imageType);
        $this->setData('groupCnt', $groupList['cnt']);
        $this->setData('groupList', $groupList['data']);
        $this->setData('fixedGoodsDiscount', $fixedGoodsDiscount);
        $this->setData('exceptBenefit', Util::getExceptBenefitData());
        $this->setData('imageType', $imageType);
        $this->setData('fbGoodsImage', gd_isset($data['fbGoodsImage']));
        $this->setData('displayTheme', $mainDisplayTheme);
        $this->setData('populateInfo', $populateInfo);
        $this->setData('goodsBenefitData', $goodsBenefitData['data']);
        $this->setData('goodsBenefitUse', $goodsBenefit->getConfig());
        $this->setData('paycoFl', $paycoConfig['paycoFl']);
        $this->setData('disabled', $disabled);
        $this->setData('msgDate1', gd_installed_date('2019-04-03'));

        // 옵션에 따라 다른 화면 표시
        // 공급사와 동일한 페이지 사용
        $optionType = gd_policy('goods.option_1903');
        if($optionType['use'] == 'y'){
            //상품재고개선 기능 사용 시 아래 파일 사용(기본)
            $this->getView()->setPageName('goods/goods_register_option_stock.php');
        }else{
            //이전 튜닝한 업체를 위하여 레거시 보장을 위해 아래 파일 사용
            $this->getView()->setPageName('goods/goods_register.php');
        }

        //seo태그 개별설정
        $this->getView()->setDefine('seoTagFrm',  'share/seo_tag_each.php');

    }
}
