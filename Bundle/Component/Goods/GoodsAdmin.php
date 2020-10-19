<?php

/**
 * 상품 class
 *
 * 상품 관련 관리자 Class
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
namespace Bundle\Component\Goods;

use Component\Member\Group\Util as GroupUtil;
use Component\Member\Manager;
use Component\Page\Page;
use Component\Storage\Storage;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Debug\Exception\HttpException;
use Framework\Debug\Exception\AlertBackException;
use Framework\File\FileHandler;
use Framework\Utility\ImageUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\ArrayUtils;
use Encryptor;
use Globals;
use LogHandler;
use UserFilePath;
use Request;
use Exception;
use Session;
use App;

//use Component\File\DataFileFactory;

class GoodsAdmin extends \Component\Goods\Goods
{

    const ECT_INVALID_ARG = 'GoodsAdmin.ECT_INVALID_ARG';

    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';

    const TEXT_USELESS_VALUE = '%s은(는) 사용할 수 없습니다.';

    const TEXT_NOT_EXIST_VALUE = '%s 필수 항목이 존재하지 않습니다.';

    const TEXT_NOT_EXIST_OPTION = '옵션 항목이 존재하지 않습니다.';

    const TEXT_ERROR_VALUE = '조건에 대해 처리중 오류가 발생했습니다.';

    const TEXT_ERROR_BATCH = '일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.';

    const DEFAULT_PC_CUSTOM_SOLDOUT_OVERLAY_PATH = '/data/icon/goods_icon/custom/soldout_overlay';

    const DEFAULT_MOBILE_CUSTOM_SOLDOUT_OVERLAY_PATH = '/data/icon/goods_icon/custom/soldout_overlay_mobile';

    public $goodsNo;

    public $imagePath;

    public $etcIcon;

    public $naverConfig;

    public $daumConfig;

    public $paycoConfig;

    protected $goodsGridConfigList; // 상품그리드 디폴트 설정 항목

    /**
     * 생성자
     */
    public function __construct()
    {
        set_time_limit(RUN_TIME_LIMIT);

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        parent::__construct();

        // 기타 아이콘 설정
        $this->etcIcon = array('mileage' => __('마일리지'), 'coupon' => __('쿠폰'), 'soldout' => __('품절'), 'option' => __('옵션보기'));

        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $this->naverConfig = $dbUrl->getConfig('naver', 'config');
        $this->daumConfig = $dbUrl->getConfig('daumcpc', 'config');
        $this->paycoConfig = $dbUrl->getConfig('payco', 'config');

        if (gd_is_provider()) {
            $manager = \App::load('\\Component\\Member\\Manager');
            $managerInfo = $manager->getManagerInfo(\Session::get('manager.sno'));
            \Session::set("manager.scmPermissionInsert", $managerInfo['scmPermissionInsert']);
            \Session::set("manager.scmPermissionModify", $managerInfo['scmPermissionModify']);
            \Session::set("manager.scmPermissionDelete", $managerInfo['scmPermissionDelete']);
        }

        //상품테이블 분리 관련
        if(empty($this->goodsTable) === true) {
            $this->goodsDivisionFl=  gd_policy('goods.config')['divisionFl'] == 'y' ? true : false;
            if($this->goodsDivisionFl) $this->goodsTable = DB_GOODS_SEARCH;
            else $this->goodsTable = DB_GOODS;
        }

    }

    /**
     * 새로운 상품 번호 출력
     *
     * @return string 새로운 상품 번호
     */
    protected function getNewGoodsno()
    {
        $data = $this->getGoodsInfo(null, 'if(max(goodsNo) > 0, (max(goodsNo) + 1), ' . DEFAULT_CODE_GOODSNO . ') as newGoodsNo');

        //기존 상품 코드 있는 경우 가지고 와서 비교 후 상품 코드 정의. 파일 상품 코드가 클 경우 파일 상품 코드+1
        $goodsNo = \FileHandler::read(\UserFilePath::get('config', 'goods'));
        if($goodsNo - $data['newGoodsNo'] >= 0) {
            $data['newGoodsNo'] =  $goodsNo+1;
        }

        //최종 상품 코드 파일 저장
        \FileHandler::write(\UserFilePath::get('config', 'goods'), $data['newGoodsNo']);

        return $data['newGoodsNo'];
    }

    /**
     * 상품 번호를 Goods 테이블에 저장
     *
     * @return string 저장된 상품 번호
     */
    protected function doGoodsNoInsert()
    {
        $newGoodsNo = $this->getNewGoodsno();
        $this->db->set_insert_db(DB_GOODS, 'goodsNo', array('i', $newGoodsNo), 'y');
        if($this->goodsDivisionFl) {
            //검색테이블 추가
            $this->db->set_insert_db(DB_GOODS_SEARCH, 'goodsNo', array('i', $newGoodsNo), 'y');
        }

        return $newGoodsNo;
    }

    /**
     * 다중 카테고리 유효성 체크
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function getGoodsCategoyCheck($arrData, $goodsNo)
    {
        // 부모 카테고리 여부 체크
        foreach ($arrData['cateCd'] as $key => $val) {
            $length = strlen($val);
            for ($i = 1; $i <= ($length / DEFAULT_LENGTH_CATE); $i++) {
                $tmpCateCd[] = substr($val, 0, ($i * DEFAULT_LENGTH_CATE));
            }
        }

        $tmpCateCd = array_unique($tmpCateCd);
        $arrData['cateCd'] = array_merge($arrData['cateCd'], $tmpCateCd);



        // 중복 카테고리 정리
        $arrData['cateCd'] = array_unique($arrData['cateCd']);

        if (empty($goodsNo)) {
            $strWhere = ' AND goodsNo != \'' . $goodsNo . '\'';
        } else {
            $strWhere = '';
        }

        // 상품 순서 설정 (최대값 + 1을 순서로함)
        $strSQL = "SELECT IF(MAX(glc.goodsSort) > 0, (MAX(glc.goodsSort) + 1), 1) AS sort,MIN(glc.goodsSort) - 1 as reSort, glc.cateCd,cg.sortAutoFl,cg.sortType FROM ".DB_GOODS_LINK_CATEGORY." AS glc INNER JOIN ".DB_CATEGORY_GOODS." AS cg ON cg.cateCd = glc.cateCd WHERE glc.cateCd IN  ('" . implode('\',\'', $arrData['cateCd']) . "') GROUP BY glc.cateCd";
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            if($data['sortAutoFl'] =='y')  $getData[$data['cateCd']] = 0;
            else  {
                if($data['sortType'] =='bottom') $getData[$data['cateCd']] = $data['reSort'];
                else  $getData[$data['cateCd']] = $data['sort'];
            }
        }

        $category = \App::load('\\Component\\Category\\CategoryAdmin');

        foreach ($arrData['cateCd'] as $key => $val) {

            list($cateInfo) = $category->getCategoryData($val);

            $arrData['goodsSort'][$key] = $getData[$val];

            // 추가된 부모 카테고리 노출 여부
            gd_isset($arrData['cateLinkFl'][$key], 'n');

            // 노출 카테고리 배열화
            if ($arrData['cateLinkFl'][$key] == 'y') {
                $arrView[] = $key;
            }
        }
        if (isset($arrView) === false) {
            return null;
        }

        // 노출 카테고리와 부모 카테고리 설정
        foreach ($arrView as $key => $val) {
            $length = strlen($arrData['cateCd'][$val]);
            for ($i = 1; $i <= ($length / DEFAULT_LENGTH_CATE); $i++) {
                $tmp[] = substr($arrData['cateCd'][$val], 0, ($i * DEFAULT_LENGTH_CATE));
            }
        }

        // 노출 카테고리와 부모 카테고리 추출
        $extract['sno'] = array();
        $extract['cateCd'] = array();
        $extract['cateLinkFl'] = array();
        $extract['goodsSort'] = array();
        foreach ($arrData['cateCd'] as $key => $val) {
            if (in_array($val, $tmp)) {
                $extract['sno'][] = gd_isset($arrData['sno'][$key]);
                $extract['cateCd'][] = $arrData['cateCd'][$key];
                $extract['cateLinkFl'][] = $arrData['cateLinkFl'][$key];
                $extract['goodsSort'][] = $arrData['goodsSort'][$key];
            }
        }

        return $extract;
    }

    /**
     * 브랜드 유효성 체크
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function getGoodsBrandCheck($brandCd, $arrData, $goodsNo)
    {
        $chkReturn = false;
        if (empty($arrData) === false) {
            foreach ($arrData as $key => $val) {
                if (strlen($val['cateCd']) === strlen($brandCd) && $val['cateLinkFl'] == 'n') {
                    continue;
                }
                $setData['sno'][$key] = $val['sno'];
                $setData['cateCd'][$key] = $val['cateCd'];
                $setData['cateLinkFl'][$key] = $val['cateLinkFl'];
                $setData['goodsSort'][$key] = $val['goodsSort'];
            }
            if (ArrayUtils::last($setData['cateCd']) == $brandCd) {
                // return $setData;
                $chkReturn = true;
            } else {
                unset($arrData, $setData);
                $setData = array();
            }
        }

        // 새로운 브랜드 코드 설정
        $length = strlen($brandCd) / DEFAULT_LENGTH_BRAND;
        for ($i = 1; $i <= $length; $i++) {
            $setData['cateCd'][] = substr($brandCd, 0, ($i * DEFAULT_LENGTH_BRAND));
        }

        // 중복 브랜드 정리
        $setData['cateCd'] = array_unique($setData['cateCd']);
        if (isset($setData['sno'])) {
            if (count($setData['cateCd']) === count($setData['sno']) && $chkReturn === true) {
                return $setData;
            }
        }

        $strSQL = "SELECT IF(MAX(glb.goodsSort) > 0, (MAX(glb.goodsSort) + 1), 1) AS sort,MIN(glb.goodsSort) - 1 as reSort, glb.cateCd,cb.sortAutoFl,cb.sortType FROM ".DB_GOODS_LINK_BRAND." AS glb INNER JOIN ".DB_CATEGORY_BRAND." AS cb ON cb.cateCd = glb.cateCd WHERE glb.cateCd IN  ('" . implode('\',\'', $setData['cateCd']) . "') GROUP BY glb.cateCd";
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            if($data['sortAutoFl'] =='y')  $getData[$data['cateCd']] = 0;
            else  {
                if($data['sortType'] =='bottom') $getData[$data['cateCd']] = $data['reSort'];
                else  $getData[$data['cateCd']] = $data['sort'];
            }
        }

        // 새로운 브랜드 link 값
        foreach ($setData['cateCd'] as $key => $val) {
            if (empty($getData[$val])) {
                $setData['goodsSort'][$key] = gd_isset($setData['goodsSort'][$key], 1);
            } else {
                $setData['goodsSort'][$key] = gd_isset($setData['goodsSort'][$key], ($getData[$val]));
            }

            if ($brandCd == $val) {
                $setData['cateLinkFl'][$key] = 'y';
            } else {
                $setData['cateLinkFl'][$key] = 'n';
            }

            gd_isset($setData['sno'][$key], '');
            // $setData['sno'][$key] = '';
        }

        return $setData;
    }

    /**
     * 상품의 등록 및 수정에 관련된 정보 (관리자 사용)
     *
     * @param integer $goodsNo 상품 번호
     * @param array $taxConf 과세 / 비과세 정보
     * @param boolean $applyFl 관리자 상품 복사 여부
     * @return array 해당 상품 데이타
     */
    public function getDataGoods($goodsNo = null, $taxConf, $applyFl = false)
    {
        $checked = $selected = [];

        // --- 사은품 증정 정책 config 불러오기
        $giftConf = gd_policy('goods.gift');

        // --- 등록인 경우
        if (is_null($goodsNo)) {
            // 기본 정보
            $data['mode'] = 'register';
            $data['goodsNo'] = null;
            $data['scmNo'] = (string)Session::get('manager.scmNo');

            if (Session::get('manager.isProvider')) {
                $scm = \App::load('\\Component\\Scm\\ScmAdmin');
                $scmInfo = $scm->getScmInfo($data['scmNo'], 'companyNm,scmCommission');
                $data['scmNoNm'] = $scmInfo['companyNm'];
                $data['commission'] = $scmInfo['scmCommission'];
            }

            // 옵션 설정
            $data['optionCnt'] = 0;
            $data['optionValCnt'] = 0;

            // 기본값 설정
            DBTableField::setDefaultData('tableGoods', $data, $taxConf);

            // 사은품
            $data['gift'] = null;

            //글로벌설정
            if($this->gGlobal['isUse']) {
                foreach($this->gGlobal['useMallList'] as $k => $v) {
                    $checked['goodsNmFl'][$v['sno']] = "checked='checked'";
                    $checked['shortDescriptionFl'][$v['sno']] = "checked='checked'";
                }
            }

            $data['hscode'] = ['kr'=>''];

            // --- 수정인 경우
        } else {
            // 기본 정보
            $data = $this->getGoodsInfo($goodsNo); // 상품 기본 정보
            if (Session::get('manager.isProvider')) {
                if ($data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            $data['link'] = $this->getGoodsLinkCategory($goodsNo); // 카테고리 연결 정보
            $data['addInfo'] = $this->getGoodsAddInfo($goodsNo); // 추가항목 정보
            $data['option'] = $this->getGoodsOption($goodsNo, $data); // 옵션 & 가격 정보
            $data['optionIcon'] = $this->getGoodsOptionIcon($goodsNo); // 옵션 추가 노출
            $data['optionText'] = $this->getGoodsOptionText($goodsNo); // 텍스트 옵션 정보
            $data['image'] = $this->getGoodsImage($goodsNo); // 이미지 정보
            $data['goodsIcon'] = $this->getGoodsDetailIcon($goodsNo); // 아이콘 정보
            $data['mode'] = 'modify';
            $data['applyGoodsImageStorage'] = $data['imageStorage']; // 기존상품 이미지 저장소 정보
            // 상품 필수 정보
            $data['goodsMustInfo'] = json_decode(gd_htmlspecialchars_stripslashes($data['goodsMustInfo']),true);
            foreach($data['goodsMustInfo'] as $key => $val){
                foreach($val as $k => $v){
                    $data['goodsMustInfo'][$key][$k]['infoTitle'] = gd_htmlspecialchars_decode($v['infoTitle']);
                    $data['goodsMustInfo'][$key][$k]['infoValue'] = gd_htmlspecialchars_decode($v['infoValue']);
                }
            }


            // 옵션 설정
            if ($data['optionFl'] == 'y' && $data['option'] && $data['optionName']) {
                $data['optionName'] = explode(STR_DIVISION, $data['optionName']);
                $data['optionCnt'] = count($data['optionName']);
                $data['optionValCnt'] = count($data['option']) - 1;
            } else {
                $data['optionName'] = null;
                $data['optionCnt'] = 0;
                $data['optionValCnt'] = 0;
            }

            // 배송 설정
            $tmp = explode(INT_DIVISION, $data['deliveryAdd']);
            unset($data['deliveryAdd']);
            $data['deliveryAdd']['cnt'] = gd_isset($tmp[0], 0);
            $data['deliveryAdd']['price'] = gd_isset($tmp[1], 0);
            unset($tmp);
            $tmp = explode(INT_DIVISION, $data['deliveryGoods']);
            unset($data['deliveryGoods']);
            $data['deliveryGoods']['cnt'] = gd_isset($tmp[0], 0);
            $data['deliveryGoods']['price'] = gd_isset($tmp[1], 0);
            unset($tmp);


            // 기본값 설정
            DBTableField::setDefaultData('tableGoods', $data, $taxConf);

            // 관련 상품 정보
            $data['relationGoodsNo'] = $this->getGoodsDataDisplay($data['relationGoodsNo']);
            $data['relationGoodsEach'] = explode(STR_DIVISION, $data['relationGoodsEach']);
            if ($data['relationGoodsDate']) $data['relationGoodsDate'] = json_decode(gd_htmlspecialchars_stripslashes($data['relationGoodsDate']), true);


            //추가 상품 정보
            if ($data['addGoodsFl'] === 'y' && empty($data['addGoods']) === false) {

                $data['addGoods'] = json_decode(gd_htmlspecialchars_stripslashes($data['addGoods']), true);
                $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');
                if ($data['addGoods']) {
                    foreach ($data['addGoods'] as $k => $v) {
                        if($v['addGoods']) {
                            $data['addGoods'][$k]['addGoodsApplyCount'] = $addGoodsApplyCount = $this->db->getCount(DB_ADD_GOODS, 'addGoodsNo', 'WHERE applyFl !="y"  AND addGoodsNo IN ("' . implode('","', $v['addGoods']) . '")');

                            foreach ($v['addGoods'] as $k1 => $v1) {
                                $tmpField[] = 'WHEN \'' . $v1 . '\' THEN \'' . sprintf("%0".strlen(count($v['addGoods']))."d",$k1) . '\'';
                            }

                            $sortField = ' CASE addGoodsNo ' . implode(' ', $tmpField) . ' ELSE \'\' END ';
                            unset($tmpField);

                            $data['addGoods'][$k]['addGoodsList'] = $addGoods->getInfoAddGoodsGoods($v['addGoods'], null, $sortField);
                        }
                    }
                }
            }

            // 사은품 정보
            if ($giftConf['giftFl'] == 'y') {
                $gift = \App::load('\\Component\\Gift\\GiftAdmin');
                $data['gift'] = $gift->getGiftPresentInGoods($data['goodsNo'], $data['cateCd'], $data['brandCd']);
            } else {
                $data['gift'] = null;
            }

            if ($data['goodsColor']) $data['goodsColor'] = explode(STR_DIVISION, $data['goodsColor']);

            if ($data['brandCd']) {
                $brandCate = \App::load('\\Component\\Category\\BrandAdmin');
                $data['brandCdNm'] = $brandCate->getCategoryData($data['brandCd'], '', 'cateNm')[0]['cateNm'];

            }

            if (gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && $data['purchaseNo']) {
                $purchase = \App::load('\\Component\\Goods\\Purchase');
                $purchaseInfo = $purchase->getInfoPurchase($data['purchaseNo'],'purchaseNm,delFl');
                if($purchaseInfo['delFl'] =='n') $data['purchaseNoNm'] = $purchaseInfo['purchaseNm'];
            }

            if ($data['scmNo']) {
                $scm = \App::load('\\Component\\Scm\\ScmAdmin');
                $data['scmNoNm'] = $scm->getScmInfo($data['scmNo'], 'companyNm')['companyNm'];
            }


            if ($data['goodsPermissionGroup']) {
                $data['goodsPermissionGroup'] = explode(INT_DIVISION, $data['goodsPermissionGroup']);
                $memberGroupName = GroupUtil::getGroupName("sno IN ('" . implode("','", $data['goodsPermissionGroup']) . "')");
                $data['goodsPermissionGroup'] = $memberGroupName;
            }

            if ($data['goodsAccessGroup']) {
                $data['goodsAccessGroup'] = explode(INT_DIVISION, $data['goodsAccessGroup']);
                $memberGroupName = GroupUtil::getGroupName("sno IN ('" . implode("','", $data['goodsAccessGroup']) . "')");
                $data['goodsAccessGroup'] = $memberGroupName;
            }

            if($this->gGlobal['isUse']) {
                $tmpGlobalData = $this->getDataGoodsGlobal($data['goodsNo']);
                $globalData = array_combine(array_column($tmpGlobalData,'mallSno'),$tmpGlobalData);

                foreach($this->gGlobal['useMallList'] as $k => $v) {
                    if(!$globalData[$v['sno']]['goodsNm']) {
                        $checked['goodsNmFl'][$v['sno']] = "checked='checked'";
                    }

                    if(!$globalData[$v['sno']]['shortDescription']) {
                        $checked['shortDescriptionFl'][$v['sno']] = "checked='checked'";
                    }
                }

                $data['globalData'] = $globalData;

            }

            //HS코드
            if ($data['hscode']) $data['hscode'] = json_decode(gd_htmlspecialchars_stripslashes($data['hscode']), true);
            else $data['hscode'] = ['kr'=>''];

            //
            $data['mileageGroupInfo'] = array_filter(explode(INT_DIVISION, $data['mileageGroupInfo']));

            //
            $data['fixedGoodsDiscount'] = array_filter(explode(STR_DIVISION, $data['fixedGoodsDiscount']));

            $data['exceptBenefit'] = array_filter(explode(STR_DIVISION, $data['exceptBenefit']));
            $data['exceptBenefitGroupInfo'] = array_filter(explode(INT_DIVISION, $data['exceptBenefitGroupInfo']));
        }
        if (empty($data['mileageGroupMemberInfo']) === false) {
            $data['mileageGroupMemberInfo'] = json_decode($data['mileageGroupMemberInfo'], true);
            foreach ($data['mileageGroupMemberInfo']['mileageGoodsUnit'] as $key => $val) {
                $selected['mileageGroupMemberInfo']['mileageGoodsUnit'][$key][$val] = 'selected="selected"';
            }
        }
        if (empty($data['goodsDiscountGroupMemberInfo']) === false) {
            $data['goodsDiscountGroupMemberInfo'] = json_decode($data['goodsDiscountGroupMemberInfo'], true);
            foreach ($data['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'] as $key => $val) {
                $selected['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key][$val] = 'selected="selected"';
            }
        }

        // 사은품 설정 여부
        $data['giftConf'] = $giftConf['giftFl'];

        // 배송 설정
        gd_isset($data['deliveryAdd']['cnt'], 0);
        gd_isset($data['deliveryAdd']['price'], 0);
        gd_isset($data['deliveryGoods']['cnt'], 0);
        gd_isset($data['deliveryGoods']['price'], 0);
        gd_isset($data['deliveryAddArea'], STR_DIVISION);

        //브랜드관련
        gd_isset($data['brandCdNm'], '');

        //그룹관련
        gd_isset($data['goodsPermissionGroup']);
        gd_isset($data['goodsPermission'], key($this->goodsPermissionList));


        // 최대 / 최소 수량
        if ($data['minOrderCnt'] == '1' && $data['maxOrderCnt'] == 0) {
            $data['maxOrderChk'] = 'n';
            $data['maxOrderCnt'] = null;
        } else {
            $data['maxOrderChk'] = 'y';
        }

        // 판매기간
        if (gd_isset($data['salesStartYmd']) != '0000-00-00 00:00:00' && gd_isset($data['salesEndYmd']) != '0000-00-00 00:00:00') {
            $data['salesDateFl'] = 'y';
        } else {
            $data['salesDateFl'] = 'n';
        }

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) $data['scmFl'] = 'n';
        else $data['scmFl'] = 'y';

        //외부동영상 설정 사이즈
        if ($data['externalVideoWidth'] > 0 && $data['externalVideoHeight']) $data['externalVideoSizeFl'] = 'n';
        else $data['externalVideoSizeFl'] = 'y';


        //
        gd_isset($data['mileageGroup'], 'all');
        gd_isset($data['goodsDiscountGroup'], 'all');
        gd_isset($data['exceptBenefitGroup'], 'all');
        gd_isset($data['goodsBenefitSetFl'], 'n');
        gd_isset($data['benefitUseType'], 'nonLimit');

        $checked['seoTagFl'][$data['seoTagFl']] = $checked['naverFl'][$data['naverFl']] = $checked['paycoFl'][$data['paycoFl']] = $checked['daumFl'][$data['daumFl']] = $checked['optionImageDisplayFl'][$data['optionImageDisplayFl']] = $checked['optionImagePreviewFl'][$data['optionImagePreviewFl']] = $checked['naverAgeGroup'][$data['naverAgeGroup']] = $checked['goodsDescriptionSameFl'][$data['goodsDescriptionSameFl']] = $checked['payLimitFl'][$data['payLimitFl']] = $checked['goodsNmFl'][$data['goodsNmFl']] = $checked['restockFl'][$data['restockFl']] = $checked['restockFl'][$data['restockFl']] = $checked['cateCd'][$data['cateCd']] = $checked['mileageFl'][$data['mileageFl']] = $checked['optionFl'][$data['optionFl']] = $checked['optionDisplayFl'][$data['optionDisplayFl']] = $checked['optionTextFl'][$data['optionTextFl']] = $checked['goodsDisplayFl'][$data['goodsDisplayFl']] = $checked['goodsSellFl'][$data['goodsSellFl']] = $checked['goodsDisplayMobileFl'][$data['goodsDisplayMobileFl']] = $checked['goodsSellMobileFl'][$data['goodsSellMobileFl']] = $checked['taxFreeFl'][$data['taxFreeFl']] = $checked['stockFl'][$data['stockFl']] = $checked['soldOutFl'][$data['soldOutFl']] = $checked['maxOrderChk'][$data['maxOrderChk']] = $checked['deliveryFl'][$data['deliveryFl']] = $checked['deliveryFree'][$data['deliveryFree']] = $checked['relationSameFl'][$data['relationSameFl']] = $checked['relationFl'][$data['relationFl']] = $checked['qrCodeFl'][$data['qrCodeFl']] = $checked['goodsPermission'][$data['goodsPermission']] = $checked['onlyAdultFl'][$data['onlyAdultFl']] = $checked['goodsDiscountFl'][$data['goodsDiscountFl']] = $checked['salesDateFl'][$data['salesDateFl']] = $checked['addGoodsFl'][$data['addGoodsFl']] = $checked['imgDetailViewFl'][$data['imgDetailViewFl']] = $checked['externalVideoFl'][$data['externalVideoFl']] = $checked['goodsState'][$data['goodsState']] = $checked['scmFl'][$data['scmFl']] = $checked['externalVideoSizeFl'][$data['externalVideoSizeFl']] = $checked['mileageGroup'][$data['mileageGroup']] = $checked['goodsDiscountGroup'][$data['goodsDiscountGroup']] = $checked['exceptBenefitGroup'][$data['exceptBenefitGroup']] = $checked['benefitUseType'][$data['benefitUseType']] = $checked['goodsBenefitSetFl'][$data['goodsBenefitSetFl']] ='checked="checked"';
        $checked['goodsAccess'][$data['goodsAccess']] =$checked['goodsPermissionPriceStringFl'][$data['goodsPermissionPriceStringFl']] = $checked['onlyAdultDisplayFl'][$data['onlyAdultDisplayFl']] = $checked['onlyAdultImageFl'][$data['onlyAdultImageFl']] = $checked['goodsAccessDisplayFl'][$data['goodsAccessDisplayFl']] = $checked['naverFl'][$data['naverFl']] = $checked['optionImageDisplayFl'][$data['optionImageDisplayFl']] = $checked['optionImagePreviewFl'][$data['optionImagePreviewFl']] = $checked['naverAgeGroup'][$data['naverAgeGroup']] = $checked['goodsDescriptionSameFl'][$data['goodsDescriptionSameFl']] = $checked['payLimitFl'][$data['payLimitFl']] = $checked['goodsNmFl'][$data['goodsNmFl']] = $checked['restockFl'][$data['restockFl']] = $checked['restockFl'][$data['restockFl']] = $checked['cateCd'][$data['cateCd']] = $checked['mileageFl'][$data['mileageFl']] = $checked['optionFl'][$data['optionFl']] = $checked['optionDisplayFl'][$data['optionDisplayFl']] = $checked['optionTextFl'][$data['optionTextFl']] = $checked['goodsDisplayFl'][$data['goodsDisplayFl']] = $checked['goodsSellFl'][$data['goodsSellFl']] = $checked['goodsDisplayMobileFl'][$data['goodsDisplayMobileFl']] = $checked['goodsSellMobileFl'][$data['goodsSellMobileFl']] = $checked['taxFreeFl'][$data['taxFreeFl']] = $checked['stockFl'][$data['stockFl']] = $checked['soldOutFl'][$data['soldOutFl']] = $checked['maxOrderChk'][$data['maxOrderChk']] = $checked['deliveryFl'][$data['deliveryFl']] = $checked['deliveryFree'][$data['deliveryFree']] = $checked['relationSameFl'][$data['relationSameFl']] = $checked['relationFl'][$data['relationFl']] = $checked['qrCodeFl'][$data['qrCodeFl']] = $checked['goodsPermission'][$data['goodsPermission']] = $checked['onlyAdultFl'][$data['onlyAdultFl']] = $checked['goodsDiscountFl'][$data['goodsDiscountFl']] = $checked['salesDateFl'][$data['salesDateFl']] = $checked['addGoodsFl'][$data['addGoodsFl']] = $checked['imgDetailViewFl'][$data['imgDetailViewFl']] = $checked['externalVideoFl'][$data['externalVideoFl']] = $checked['goodsState'][$data['goodsState']] = $checked['scmFl'][$data['scmFl']] = $checked['externalVideoSizeFl'][$data['externalVideoSizeFl']] = $checked['cultureBenefitFl'][$data['cultureBenefitFl']]  = 'checked="checked"';

        //페이스북 상품 피드 설정값
        $facebookAd = \App::load('\\Component\\Marketing\\FacebookAd');
        $goodsFeed = $facebookAd->getFbGoodsFeedData($goodsNo);
        $getData['fbGoodsImage'] = $facebookAd->getFaceBookGoodsImage($goodsNo);
        gd_isset($goodsFeed['useFl'], 'n');
        $checked['fbUseFl'][$goodsFeed['useFl']] = 'checked="checked"';

        // 상품 아이콘 설정
        $this->db->strField = implode(', ', DBTableField::setTableField('tableManageGoodsIcon', null, 'iconUseFl'));
        $this->db->strWhere = 'iconUseFl = \'y\'';
        $this->db->strOrder = 'sno DESC';
        $data['icon'] = $this->getManageGoodsIconInfo();

        /* 2018.03.06 아이콘 별도 테이블 분리로 인한 주석처리
        // 기간제한용
        if (!empty($data['goodsIconCdPeriod'])) {
            $goodsIconCd = explode(INT_DIVISION, $data['goodsIconCdPeriod']);
            unset($data['goodsIconCdPeriod']);
            foreach ($goodsIconCd as $key => $val) {
                $checked['goodsIconCdPeriod'][$val] = 'checked="checked"';
            }
        }

        // 무제한용
        if (!empty($data['goodsIconCd'])) {
            $goodsIconCd = explode(INT_DIVISION, $data['goodsIconCd']);
            unset($data['goodsIconCd']);
            foreach ($goodsIconCd as $key => $val) {
                $checked['goodsIconCd'][$val] = 'checked="checked"';
            }
        }
        */

        if ($data['goodsIcon']) {
            foreach ($data['goodsIcon'] AS $k => $v) {
                if ($v['iconKind'] == 'pe') {           // 기간 제한용
                    $checked['goodsIconCdPeriod'][$v['goodsIconCd']] = 'checked="checked"';
                    $data['goodsIconStartYmd'] = $v['goodsIconStartYmd'];
                    $data['goodsIconEndYmd'] = $v['goodsIconEndYmd'];
                } else if ($v['iconKind'] == 'un') {    // 무제한용
                    $checked['goodsIconCd'][$v['goodsIconCd']] = 'checked="checked"';
                }
            }
        }

        //결제수단제한
        if ($data['payLimitFl'] == 'y') {
            $payLimit = explode(STR_DIVISION, $data['payLimit']);
            foreach ($payLimit as $k => $v) {
                $checked['payLimit'][$v] = 'checked="checked"';
            }
            unset($payLimit);
        }

        // 상품 상세 이용안내
        $inform = \App::load('\\Component\\Agreement\\BuyerInform');
        $detailInfo = array('detailInfoDelivery', 'detailInfoAS', 'detailInfoRefund', 'detailInfoExchange');
        $data['detail'] = $inform->getGoodsInfoCode($data['mode'], $data['scmNo']);

        // 상품 상세 이용안내 기본값 설정
        if ($data['mode'] == "register" && isset($data['detail']['default'])) {
            foreach ($data['detail']['default'] as $key => $val) {
                $data[$key] = $val;
            }
            foreach ($data['detail']['defaultInformNm'] as $key => $val) {
                $data[$key . 'InformNm'] = $val;
            }
            foreach ($data['detail']['defaultInformContent'] as $key => $val) {
                $data[$key . 'InformContent'] = $val;
            }
            unset($data['detail']['default'], $data['detail']['defaultInformNm'], $data['detail']['defaultInformContent']);
        } else if ($data['mode'] == "modify") {

            foreach ($detailInfo as $val) {
                $infoData = gd_buyer_inform($data[$val]);
                $data[$val . 'InformNm'] = $infoData['informNm'];
                $data[$val . 'InformContent'] = $infoData['content'];
            }
        }

        // 상품 상세 이용안내 입력여부 설정
        foreach ($detailInfo as $val) {
            if ($data[$val . 'Fl'] == '') { //레거시 적용
                if (!empty($data[$val])) {
                    $checked[$val . 'Fl']['selection'] = 'checked="checked"';
                    $data[$val . 'Fl'] = 'selection';
                } else {
                    $checked[$val . 'Fl']['no'] = 'checked="checked"';
                    $data[$val . 'Fl'] = 'no';
                }
            } else {
                $checked[$val . 'Fl'][$data[$val . 'Fl']] = 'checked="checked"';
                $data[$val . 'Fl'] = $data[$val . 'Fl'];
            }
        }

        $selected['naverImportFlag'][$data['naverImportFlag']] =$selected['naverProductFlag'][$data['naverProductFlag']] =$selected['naverGender'][$data['naverGender']] = $selected['mileageGoodsUnit'][$data['mileageGoodsUnit']] = $selected['goodsDiscountUnit'][$data['goodsDiscountUnit']] = $selected['newGoodsRegFl'][$data['newGoodsRegFl']] = $selected['newGoodsDateFl'][$data['newGoodsDateFl']] ="selected";

        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        $data['seoTag']['target'] = 'goods';
        $data['seoTag']['config'] = $seoTag->seoConfig['tag'];
        $data['seoTag']['replaceCode'] = $seoTag->seoConfig['replaceCode']['goods'];
        if($data['seoTagSno']) {
            $data['seoTag']['data'] = $seoTag->getSeoTagData($data['seoTagSno'], null, false, ['path' => 'goods/goods_view.php', pageCode => $data['goodsNo']]);
            if ($applyFl === true) {
                unset($data['seoTag']['data']['sno']);
            }
        }

        // 무게&용량 소수점이 .00 일 경우 제거
        $data['goodsWeight'] = str_replace('.00', '', $data['goodsWeight']);
        $data['goodsVolume'] = str_replace('.00', '', $data['goodsVolume']);

        $getData['data'] = $data;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * 상품의 등록 및 수정에 관련된 정보 (관리자 사용, 옵션 정보만 불러옴)
     *
     * @param integer $goodsNo 상품 번호
     * @return array 해당 상품의 옵션 데이터
     */
    public function getDataGoodsOption($goodsNo = null)
    {
        $checked = $selected = [];

        // 기본 정보
        $data = $this->getGoodsInfo($goodsNo); // 상품 기본 정보
        if (Session::get('manager.isProvider')) {
            if ($data['scmNo'] != Session::get('manager.scmNo')) {
                throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
            }
        }

        $data['option'] = $this->getGoodsOption($goodsNo, $data); // 옵션 & 가격 정보
        $data['optionIcon'] = $this->getGoodsOptionIcon($goodsNo); // 옵션 추가 노출
        $data['image'] = $this->getGoodsImage($goodsNo); // 이미지 정보
        $data['mode'] = 'modify';

        // 옵션 설정
        if ($data['optionFl'] == 'y' && $data['option'] && $data['optionName']) {
            $data['optionName'] = explode(STR_DIVISION, $data['optionName']);
            $data['optionCnt'] = count($data['optionName']);
            $data['optionValCnt'] = count($data['option']) - 1;
        } else {
            $data['optionName'] = null;
            $data['optionCnt'] = 0;
            $data['optionValCnt'] = 0;
        }

        // 기본값 설정
        DBTableField::setDefaultData('tableGoods', $data, $taxConf);

        $checked['optionImageDisplayFl'][$data['optionImageDisplayFl']] = $checked['optionImagePreviewFl'][$data['optionImagePreviewFl']] = $checked['optionDisplayFl'][$data['optionDisplayFl']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * 상품의 등록 및 수정에 관련된 정보 (관리자 사용, 임시테이블의 옵션 정보만 불러옴)
     *
     * @param integer $goodsNo 상품 번호
     * @param integer $tmpSession 세션 번호
     * @return array 해당 상품의 옵션 데이터
     */
    public function getDataGoodsOptionTemp($goodsNo = null, $tmpSession)
    {
        if($goodsNo != null){
            $getData = $this->getDataGoodsOption($goodsNo);
        }
        unset($getData['data']['optionName']); //옵션이름 배열
        unset($getData['data']['optionDisplayFl']); //옵션 노출방식
        unset($getData['data']['optionImagePreviewFl']); //옵션 이미지 노출 설정 미리보기 사용
        unset($getData['data']['optionImageDisplayFl']); //옵션 이미지 노출 설정 상세 이미지에 추가
        unset($getData['data']['optionCnt']); //옵션 개수
        unset($getData['data']['option']); //실제 옵션 배열

        //옵션이름 불러오기
        $arrField = DBTableField::setTableField('tableGoodsOptionTemp');
        $arrWhere[] = 'session=?';
        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $this->db->bind_param_push($arrBind, 's', $tmpSession);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION_TEMP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        for($i=1; $i<=5; $i++){
            if(!empty($data[0]['optionValue'.$i])){
                $tmpOptionName[] = $data[0]['optionValue'.$i];
            }
        }
        $getData['data']['optionName'] = $tmpOptionName; //옵션 이름 설정
        $getData['data']['optionCnt'] = count($tmpOptionName);

        unset($getData['checked']['optionDisplayFl']);
        unset($getData['checked']['optionImagePreviewFl']);
        unset($getData['checked']['optionImageDisplayFl']);

        $getData['checked']['optionDisplayFl'][$data[0]['optionDisplayFl']] = 'checked';
        $getData['checked']['optionImagePreviewFl'][$data[0]['optionImagePreviewFl']] = 'checked';
        $getData['checked']['optionImageDisplayFl'][$data[0]['optionImageDisplayFl']] = 'checked';

        //option 생성
        foreach($data as $k => $v){
            $optionTemp[$k]['sno'] = '';
            $optionTemp[$k]['optionNo'] = $k+1;
            $optionTemp[$k]['optionPrice'] = $v['optionPrice'];
            $optionTemp[$k]['optionCostPrice'] = $v['optionCostPrice'];
            $optionTemp[$k]['optionCode'] = $v['optionCode'];
            $optionTemp[$k]['optionViewFl'] = $v['optionViewFl'];
            $optionTemp[$k]['optionSellFl'] = $v['optionStockFl'];
            $optionTemp[$k]['stockCnt'] = $v['stockCnt'];
            $optionValueTemp = explode(STR_DIVISION, $v['optionValueText']);
            $optionTemp[$k]['optionValue1'] = $optionValueTemp[0];
            $optionTemp[$k]['optionValue2'] = $optionValueTemp[1];
            $optionTemp[$k]['optionValue3'] = $optionValueTemp[2];
            $optionTemp[$k]['optionValue4'] = $optionValueTemp[3];
            $optionTemp[$k]['optionValue5'] = $optionValueTemp[4];
            $optionTemp[$k]['optionMemo'] = $v['optionMemo'];
            $optionTemp[$k]['optionSellCode'] = $v['optionStockCode'];
            $optionTemp[$k]['optionDeliveryCode'] = $v['optionDeliveryCode'];
            $optionTemp[$k]['optionDeliveryFl'] = $v['optionDeliveryFl'];
            $optionTemp[$k]['sellStopFl'] = $v['sellStopFl'];
            $optionTemp[$k]['sellStopStock'] = $v['sellStopStock'];
            $optionTemp[$k]['confirmRequestFl'] = $v['confirmRequestFl'];
            $optionTemp[$k]['confirmRequestStock'] = $v['confirmRequestStock'];
        }
        $getData['data']['option'] = $optionTemp;

        //optVal 생성
        foreach($data as $k => $v){
            $optValTemp[] = explode(STR_DIVISION, $v['optionValueText']);
        }
        foreach($optValTemp as $k => $v){
            for($i=0; $i<count($tmpOptionName); $i++){
                $optValTempExp[$i][] = $v[$i];
            }
        }
        for($i=1; $i<=5; $i++){
            $optVal[] = array();
        }
        foreach($optValTempExp as $k => $v){
            $optVal[$k+1] = array_unique($v);
        }

        $getData['data']['option']['optVal'] = $optVal;

        $getData['data']['optionIcon'] = '';
        $getData['data']['image'] = '';
        $getData['data']['mode'] = 'modify';
        $getData['data']['optionFl'] = 'y';
        $getData['data']['optionCnt'] = count($tmpOptionName);
        $getData['data']['optionValCnt'] = count($optVal);

        // 상품 리스트 품절, 노출 PC/mobile, 미노출 PC/mobile 카운트 쿼리
        if($goodsAdminGridMode == 'goods_list') {
            $dataStateCount = [];
            $dataStateCountQuery = [
                'pcDisplayCnt' => " g.goodsDisplayFl='y'",
                'mobileDisplayCnt' => " g.goodsDisplayMobileFl='y'",
                'pcNoDisplayCnt' => " g.goodsDisplayFl='n'",
                'mobileNoDisplayCnt' => " g.goodsDisplayMobileFl='n'",
            ];
            foreach ($dataStateCountQuery as $stateKey => $stateVal) {
                if($page->hasRecodeCache($stateKey)) {
                    $dataStateCount[$stateKey]  = $page->getRecodeCache($stateKey);
                    continue;
                }
                $dataStateSQL = " SELECT COUNT(g.goodsNo) AS cnt FROM " . $this->goodsTable . " as g WHERE  " . $stateVal . " AND g.delFl ='n'" . $scmWhereString;
                $dataStateCount[$stateKey] = $this->db->query_fetch($dataStateSQL)[0]['cnt'];
                $page->recode[$stateKey] = $dataStateCount[$stateKey];
            }
            // 품절의 경우 OR 절 INDEX 경유하지 않기에 별도 쿼리 실행 - DBA
            //                    if(!\Request::get()->get('__soldOutCnt')) {
            if($page->hasRecodeCache('soldOutCnt') === false) {
                $dataStateSoldOutSql = "select sum(cnt) as cnt from ( SELECT count(1) AS cnt FROM  " . $this->goodsTable . "  as g1 WHERE   g1.soldOutFl = 'y' AND g1.delFl ='n' union all SELECT count(1) AS cnt FROM  " . $this->goodsTable . "  as g2 WHERE  g2.soldOutFl = 'n' and g2.stockFl = 'y' AND g2.totalStock <= 0  AND g2.delFl ='n') gQ";
                $dataStateCount['soldOutCnt'] = $this->db->query_fetch($dataStateSoldOutSql)[0]['cnt'];
                $page->recode['soldOutCnt'] = $dataStateCount['soldOutCnt'];
            }
            else {
                $dataStateCount['soldOutCnt'] = $page->getRecodeCache('soldOutCnt');
            }
            $getData['stateCount'] = $dataStateCount;
        }

        //기본값이 없을 경우
        if(empty($getData['checked']['optionDisplayFl'])) $getData['checked']['optionDisplayFl'] = 's'; //옵션 노출방식

        return $getData;
    }

    public function getDataGoodsOptionGrid(){
        // 상품리스트 그리드 설정
        $goodsOptionAdminGrid = \App::load('\\Component\\Goods\\GoodsAdminGrid');
        $goodsOptionAdminGridMode = $goodsOptionAdminGrid->getGoodsAdminGridMode();
        $this->goodsOptionGridConfigList = $goodsOptionAdminGrid->getSelectGoodsOptionGridConfigList($goodsOptionAdminGridMode, 'all');

        if (empty($this->goodsOptionGridConfigList) === false) {
            $getData['goodsOptionGridConfigList'] = $this->goodsOptionGridConfigList;
            $getData['goodsOptionGridConfigListDisplayFl'] = false; // 그리드 추가 진열 레이어 노출 여부
            foreach($gridAddDisplayArray as $displayPassVal) {
                if(array_key_exists($displayPassVal, $getData['goodsOptionGridConfigList']['display']) === true) {
                    $getData['goodsOptionGridConfigListDisplayFl'] = true; // 그리드 추가 진열 레이어 노출 사용
                    break;
                }
            }
            if($goodsOptionAdminGridMode == 'goods_option_list') {
                $getData['goodsOptionGridConfigList']['btn'] = '수정';
            }
        }

        return $getData;
    }

    /**
     * 상품 아이콘 정보(관리자사용)
     *
     * @return array 상품 아이콘 정보
     */
    public function getManageGoodsIconInfo()
    {
        if (is_null($this->db->strField)) {
            $arrField = DBTableField::setTableField('tableManageGoodsIcon');
            $this->db->strField = 'sno, ' . implode(', ', $arrField);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_GOODS_ICON . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        return gd_htmlspecialchars_stripslashes(gd_isset($data));
    }

    /**
     * 상품 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoGoods($arrData)
    {
        // 상품명 체크
        if (Validator::required(gd_isset($arrData['goodsNm'])) === false) {
            throw new \Exception(__('상품명은 필수 항목 입니다.'), 500);

        }

        // 상품명 NFC로 변경
        $arrData['goodsNm'] = StringUtils::convertStringToNFC($arrData['goodsNm']);
        if ($arrData['goodsNmFl'] == 'e') {
            if (empty($arrData['goodsNmMain']) === false) {
                $arrData['goodsNmMain'] = StringUtils::convertStringToNFC($arrData['goodsNmMain']);
            }
            if (empty($arrData['goodsNmList']) === false) {
                $arrData['goodsNmList'] = StringUtils::convertStringToNFC($arrData['goodsNmList']);
            }
            if (empty($arrData['goodsNmDetail']) === false) {
                $arrData['goodsNmDetail'] = StringUtils::convertStringToNFC($arrData['goodsNmDetail']);
            }
            if (empty($arrData['goodsNmPartner']) === false) {
                $arrData['goodsNmPartner'] = StringUtils::convertStringToNFC($arrData['goodsNmPartner']);
            }
        }

        $updateFl = "n";

        //기존정보 복사인경우 이미지 경로 초기화
        if($arrData['applyGoodsCopy']) {
            $arrData['imagePath'] = null;
            unset($arrData['optionY']['sno']);
            //unset($arrData['imageDB']['sno']);
            //unset($arrData['imageDB']['imageCode']);
        }
        // 공급자 아이디
        gd_isset($arrData['scmNo'], DEFAULT_CODE_SCMNO);

        if (empty($arrData['brandCdNm'])) unset($arrData['brandCd']);

        // 대표 카테고리 설정
        if (isset($arrData['cateCd']) == false && is_array($arrData['link'])) {
            foreach ($arrData['link']['cateCd'] as $key => $val) {
                if ($arrData['link']['cateLinkFl'][$key] == 'y') {
                    $arrData['cateCd'] = $val;
                    break;
                }
            }
        }

        // 매입처 삭제 체크시 매입처 초기화
        if ($arrData['purchaseNoDel'] == 'y') $arrData['purchaseNo'] = '';

        // 브랜드 삭제 체크시 브랜드 초기화
        if ($arrData['brandCdDel'] == 'y') $arrData['brandCd'] = '';

        // KC인증 정보 JSON처리
        $arrData['kcmarkInfo']['kcmarkDt'] = $arrData['kcmarkDt'][0];
        unset($arrData['kcmarkDt']);
        $arrData['kcmarkInfo'] = json_encode($arrData['kcmarkInfo']);

        //대표 색상
        if (is_array($arrData['goodsColor'])) {
            $arrData['goodsColor'] = implode(STR_DIVISION, $arrData['goodsColor']);
        } else {
            $arrData['goodsColor'] = "";
        }

        //구매 가능 권한 설정
        if ($arrData['goodsPermission'] === 'group' && is_array($arrData['memberGroupNo'])) {
            $arrData['goodsPermissionGroup'] = implode(INT_DIVISION, $arrData['memberGroupNo']);
        } else $arrData['goodsPermissionGroup'] = '';

        //구매불가 고객 가격 대체문구 사용 사용안함일경우 초기화
        if (empty($arrData['goodsPermissionPriceStringFl'])) $arrData['goodsPermissionPriceStringFl'] = 'n';

        //접근 가능 권한 설정
        if ($arrData['goodsAccess'] === 'group' && is_array($arrData['accessMemberGroupNo'])) {
            $arrData['goodsAccessGroup'] = implode(INT_DIVISION, $arrData['accessMemberGroupNo']);
        } else $arrData['goodsAccessGroup'] = '';

        //미인증 고객 상품 노출함 사용안함일경우 초기화
        if (empty($arrData['onlyAdultDisplayFl'])) $arrData['onlyAdultDisplayFl'] = 'n';

        //미인증 고객 상품 이미지 노출함  사용안함일경우 초기화
        if (empty($arrData['onlyAdultImageFl'])) $arrData['onlyAdultImageFl'] = 'n';

        //접근불가 고객 상품 노출함 사용안함일경우 초기화
        if (empty($arrData['goodsAccessDisplayFl'])) $arrData['goodsAccessDisplayFl'] = 'n';

        //통합설정인 경우 개별설정값 초기화
        if ($arrData['mileageFl'] == 'c') $arrData['mileageGoods'] = '';

        //상품할인설정 사용안함일경우 초기화
        if ($arrData['goodsDiscountFl'] == 'n') $arrData['goodsDiscount'] = '';

        //pc모바일상세설명
        if (empty($arrData['goodsDescriptionSameFl'])) $arrData['goodsDescriptionSameFl'] = 'n';

        if (empty($arrData['optionImagePreviewFl'])) $arrData['optionImagePreviewFl'] = 'n';
        if (empty($arrData['optionImageDisplayFl'])) $arrData['optionImageDisplayFl'] = 'n';

        if (empty($arrData['restockFl'])) $arrData['restockFl'] = 'n';

        //추가상품
        if ($arrData['addGoodsFl'] == 'y' && is_array($arrData['goodsNoData'])) {
            $goodsNoData = [];
            if (is_array($arrData['goodsNoData'])) {
                if (gd_isset($arrData['addGoodsGroupTitle']) && is_array($arrData['addGoodsGroupTitle'])) {
                    $startGoodsNo = 0;
                    foreach ($arrData['addGoodsGroupCnt'] as $k => $v) {
                        $goodsNoData = array_slice($arrData['goodsNoData'], $startGoodsNo, $v);

                        $addGoods[$k]['title'] = $arrData['addGoodsGroupTitle'][$k];
                        if ($arrData['addGoodsGroupMustFl'][$k]) $addGoods[$k]['mustFl'] = 'y';
                        else  $addGoods[$k]['mustFl'] = 'n';
                        $addGoods[$k]['addGoods'] = $goodsNoData;

                        $startGoodsNo += $v;
                    }
                }
            }


            if ($addGoods) $arrData['addGoods'] = json_encode(gd_htmlspecialchars($addGoods), JSON_UNESCAPED_UNICODE);
        } else {
            $arrData['addGoods'] = "";
        }

        if ($arrData['payLimitFl'] == 'y') {

            $arrData['payLimit'] = implode(STR_DIVISION, $arrData['payLimit']);

            unset($payLimit);
            unset($payLimitArr);

        } else {
            $arrData['payLimit'] = "";
        }


        //판매기간
        if ($arrData['salesDateFl'] == 'y' && is_array($arrData['salesDate'])) {
            $arrData['salesStartYmd'] = $arrData['salesDate'][0];
            $arrData['salesEndYmd'] = $arrData['salesDate'][1];
        } else {
            $arrData['salesStartYmd'] = '';
            $arrData['salesEndYmd'] = '';
        }


        //외부 동영상
        if ($arrData['externalVideoSizeFl'] == 'y') {
            $arrData['externalVideoWidth'] = 0;
            $arrData['externalVideoHeight'] = 0;
        }

        // 상품 필수 정보 처리
        $arrData['goodsMustInfo'] = '';
        if (isset($arrData['addMustInfo']) && is_array($arrData['addMustInfo']) && is_array($arrData['addMustInfo']['infoTitle'])) {
            $tmpGoodsMustInfo = array();
            $i = 0;
            foreach ($arrData['addMustInfo']['infoTitle'] as $mKey => $mVal) {
                foreach ($mVal as $iKey => $iVal) {
                    $tmpGoodsMustInfo['line' . $i]['step' . $iKey]['infoTitle'] = $iVal;
                    $tmpGoodsMustInfo['line' . $i]['step' . $iKey]['infoValue'] = $arrData['addMustInfo']['infoValue'][$mKey][$iKey];
                }
                $i++;
            }

            $arrData['goodsMustInfo'] = json_encode(gd_htmlspecialchars($tmpGoodsMustInfo), JSON_UNESCAPED_UNICODE);

            unset($arrData['addMustInfo'], $tmpGoodsMustInfo, $tmpGoodsMustInfo);
        }

        // 옵션 사용여부에 따른 재정의 및 배열 삭제

        $optionType = gd_policy('goods.option_1903');
        if($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y'){
        }else {
            if ($arrData['optionFl'] == 'y') {
                //옵션이 있었는지 확인 -> 원복함 sf2000 2019-06-03
                //$goodsCopyData = $this->getDataGoodsOption(gd_isset($arrData['applyNo']));
                //$goodsCopyData = $goodsCopyData['data'];
                // 신규 폼 + 상품 복사 + 원본 상품에 옵션이 있다면 기존 원본 상품의 옵션 복사
                //&& $goodsCopyData['optionFl'] == 'y' && empty($arrData['optionTempSession'])


                // 신규 폼 + 상품 복사라면
                if($optionType['use'] == 'y' && $arrData['applyGoodsCopy']){

                    // 옵션의 존재여부에 따른 체크
                    if (empty($arrData['optionY']['optionName']) === false && empty($arrData['optionY']['optionValue']) === false) {
                        unset($arrData['optionY']['optionNo']);
                        foreach ($arrData['optionY']['optionValueText'] as $k => $v) {
                            $arrData['optionY']['optionNo'][] = $k + 1;
                            $tmpOptionText = explode(STR_DIVISION, $v);
                            foreach ($tmpOptionText as $k1 => $v1) {
                                $arrData['optionY']['optionValue' . ($k1 + 1)][] = trim($v1);
                            }
                        }

                        unset($arrData['optionY']['optionValueText']);

                        $arrData['option'] = $arrData['optionY'];
                        $arrData['optionDisplayFl'] = $arrData['option']['optionDisplayFl'];
                        $arrData['optionName'] = implode(STR_DIVISION, $arrData['option']['optionName']);

                        if ($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y') {
                            if (count($arrData['option']['optionCnt']) == 1) {
                                $arrData['optionDisplayFl'] = 's';
                            }
                        }

                        unset($arrData['option']['optionDisplayFl']);
                        unset($arrData['option']['optionName']);

                        foreach($arrData['option']['optionSellFl'] as $k => $v){
                            if($v != 'n' && $v != 'y'){
                                $arrData['option']['optionSellCode'][$k] = $arrData['option']['optionSellFl'][$k];
                                $arrData['option']['optionSellFl'][$k] = 't';
                            }
                        }
                        foreach($arrData['option']['optionDeliveryFl'] as $k => $v){
                            if($v != 'normal'){
                                $arrData['option']['optionDeliveryCode'][$k] = $arrData['option']['optionDeliveryFl'][$k];
                                $arrData['option']['optionDeliveryFl'][$k] = 't';
                            }
                        }
                    }else{
                        //복사 원본 상품 정보 가져오기
                        $goodsCopyData = $this->getDataGoodsOption(gd_isset($arrData['applyNo']));
                        $goodsCopyData = $goodsCopyData['data'];
                        $arrData['option']['optionDisplayFl'] = $goodsCopyData['optionDisplayFl'];
                        $totalOptions = 1;
                        for($goodsCopyOptionIndex=1;$goodsCopyOptionIndex<=5;$goodsCopyOptionIndex++){
                            $cnt = count($goodsCopyData['option']['optVal'][$goodsCopyOptionIndex]);
                            if($cnt > 0){
                                $tmpOptionCnt[$goodsCopyOptionIndex] = $cnt;
                                $totalOptions *= $cnt;
                            }
                        }
                        $arrData['option']['optionCnt'] = $tmpOptionCnt;
                        $arrData['option']['optionName'] = $goodsCopyData['optionName'];

                        $optionKey = $optionVal = 0;
                        foreach($goodsCopyData['option']['optVal'] as $val){
                            foreach($val as $innerKey => $innerVal){
                                $arrData['option']['optionValue'][$optionKey][$optionVal] = $innerVal;
                                $optionVal ++;
                            }
                            $optionVal = 0;
                            $optionKey ++ ;
                        }

                        for($goodsCopyOptionIndex=0;$goodsCopyOptionIndex<$totalOptions;$goodsCopyOptionIndex++){
                            $arrData['option']['optionCostPrice'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionCostPrice'];
                            $arrData['option']['optionPrice'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionPrice'];
                            $arrData['option']['stockCnt'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['stockCnt'];
                            $arrData['option']['optionViewFl'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionViewFl'];
                            $arrData['option']['optionSellFl'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionSellFl'];
                            $arrData['option']['optionSellCode'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionSellCode'];
                            $arrData['option']['optionDeliveryFl'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionDeliveryFl'];
                            $arrData['option']['optionDeliveryCode'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionDeliveryCode'];
                            $arrData['option']['optionCode'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionCode'];
                            $arrData['option']['optionMemo'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionMemo'];
                            $arrData['option']['sno'][$goodsCopyOptionIndex] = '';
                            $arrData['option']['optionNo'][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionNo'];
                            for($goodsCopyOptioninnerIndex=1;$goodsCopyOptioninnerIndex<=count($arrData['option']['optionCnt']);$goodsCopyOptioninnerIndex++) {
                                $arrData['option']['optionValue'.$goodsCopyOptioninnerIndex][$goodsCopyOptionIndex] = $goodsCopyData['option'][$goodsCopyOptionIndex]['optionValue'.$goodsCopyOptioninnerIndex];
                            }
                        }

                        $arrData['optionDisplayFl'] = $goodsCopyData['optionDisplayFl'];
                        $arrData['optionImagePreviewFl'] = $goodsCopyData['optionImagePreviewFl'];
                        $arrData['optionImageDisplayFl'] = $goodsCopyData['optionImageDisplayFl'];
                        $arrData['optionName'] = implode(STR_DIVISION, $goodsCopyData['optionName']);

                        foreach($goodsCopyData['optionIcon'] as $key => $val){
                            $arrData['optionYIcon']['goodsImage'][0][$key] = '';
                            $arrData['optionYIcon']['goodsImageText'][0][$key] = '';
                            if (strtolower(substr($val['goodsImage'],0,4)) =='http' ) {
                                $arrData['optionYIcon']['goodsImageText'][0][$key] = $val['goodsImage'];
                            }else{
                                $arrData['optionYIcon']['goodsImage'][0][$key] = $val['goodsImage'];
                            }
                        }
                    }
                    //기존 폼 일 경우 또는 상품 복사가 아닌 경우
                } else {
                    // 옵션의 존재여부에 따른 체크
                    if (isset($arrData['optionY']) === false || isset($arrData['optionY']['optionName']) === false || isset($arrData['optionY']['optionValue']) === false) {
                        if ($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y') {
                        } else {
                            throw new \Exception(__("옵션값을 확인해주세요."), 500);
                        }
                    }
                    unset($arrData['optionY']['optionNo']);
                    foreach ($arrData['optionY']['optionValueText'] as $k => $v) {
                        $arrData['optionY']['optionNo'][] = $k + 1;
                        $tmpOptionText = explode(STR_DIVISION, $v);
                        foreach ($tmpOptionText as $k1 => $v1) {
                            $arrData['optionY']['optionValue' . ($k1 + 1)][] = trim($v1);
                        }
                    }

                    unset($arrData['optionY']['optionValueText']);

                    $arrData['option'] = $arrData['optionY'];
                    $arrData['optionDisplayFl'] = $arrData['option']['optionDisplayFl'];
                    $arrData['optionName'] = implode(STR_DIVISION, $arrData['option']['optionName']);

                    if ($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y') {
                        if (count($arrData['option']['optionCnt']) == 1) {
                            $arrData['optionDisplayFl'] = 's';
                        }
                    }

                    unset($arrData['option']['optionDisplayFl']);
                    unset($arrData['option']['optionName']);

                    foreach($arrData['option']['optionSellFl'] as $k => $v){
                        if($v != 'n' && $v != 'y'){
                            $arrData['option']['optionSellCode'][$k] = $arrData['option']['optionSellFl'][$k];
                            $arrData['option']['optionSellFl'][$k] = 't';
                        }
                    }
                    foreach($arrData['option']['optionDeliveryFl'] as $k => $v){
                        if($v != 'normal'){
                            $arrData['option']['optionDeliveryCode'][$k] = $arrData['option']['optionDeliveryFl'][$k];
                            $arrData['option']['optionDeliveryFl'][$k] = 't';
                        }
                    }
                }
            } else {

                $arrData['optionN']['stockCnt'][0] = $arrData['stockCnt'];
                $arrData['optionN']['optionPrice'][0] = 0;
                $arrData['optionN']['reset'][0] = true;
                $arrData['option'] = $arrData['optionN'];
                $arrData['optionName'] = "";
            }
        }
        unset($arrData['optionY']);
        unset($arrData['optionN']);


        // 텍스트 옵션 필수 여부 기본값 설정
        if ($arrData['optionTextFl'] == 'y') {
            // 텍스트 옵션 값이 있는 지를 체크함
            if (isset($arrData['optionText']) === false || empty($arrData['optionText']['optionName'][0]) === true) {
                // 옵션명이나 값이 없는 경우 사용안함 처리
                $arrData['optionTextFl'] = 'n';
                if (isset($arrData['optionText']) === true) {
                    unset($arrData['optionText']);
                }
            } else {
                for ($i = 0; $i < count($arrData['optionText']['optionName']); $i++) {
                    gd_isset($arrData['optionText']['mustFl'][$i], 'n');
                }
            }
        }

        // 최대 / 최소 수량
        if ($arrData['maxOrderChk'] == 'n') {
            $arrData['fixedOrderCnt'] = 'option';
            $arrData['maxOrderCnt'] = 0;
            $arrData['minOrderCnt'] = 1;
        }

        // goodsNo 처리
        if ($arrData['mode'] == 'register') {
            $arrData['goodsNo'] = $this->doGoodsNoInsert();
        } else {
            // goodsNo 체크
            if (Validator::required(gd_isset($arrData['goodsNo'])) === false) {
                throw new \Exception(__('상품번호는 필수 항목입니다.'), 500);
            }
        }
        $this->goodsNo = $arrData['goodsNo'];

        if(!Session::get('manager.isProvider')) { // 본사만 실행

            // 상품등록 및 수정시에 메인상품진열 저장
            $strWhere = "kind = ?";
            $this->db->bind_param_push($arrBind['bind'], 's', 'main');
            $strSQL = "SELECT sno, themeCd, sortAutoFl, goodsNo, fixGoodsNo, exceptGoodsNo FROM " . DB_DISPLAY_THEME . " WHERE " .$strWhere;
            $themeAllData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

            // 메인상품진열 루프
            foreach($themeAllData as $themeKey => $themeVal){
                $chkGoodsNo[0] = $arrData['goodsNo'];    // 상품값

                // 상품진열이 선택 되었다면
                if (in_array($themeVal['sno'], $arrData['displayThemeSno'])) {
                    $val = $themeVal['sno'];
                    $arrBind = [];
                    $strWhere = "sno = ?";
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val);
                    $strSQL = "SELECT themeCd, sortAutoFl, goodsNo, exceptGoodsNo FROM " . DB_DISPLAY_THEME . " WHERE " . $strWhere;
                    $themeData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                    unset($arrBind);

                    $arrBind = [];
                    $strWhere = "themeCd = ?";
                    $this->db->bind_param_push($arrBind['bind'], 's', $themeData['themeCd']);
                    $strSQL = "SELECT themeCd, displayType, detailSet FROM " . DB_DISPLAY_THEME_CONFIG . " WHERE " . $strWhere;
                    $themeConfData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                    unset($arrBind);

                    $tmpThemeGoodsNo = [];
                    $tmpGoodsNo = explode(STR_DIVISION, $themeData['goodsNo']);
                    foreach ($tmpGoodsNo as $goodsNoData) {
                        $goodsNo = explode(INT_DIVISION, $goodsNoData);
                        $tmpThemeGoodsNo = array_merge($tmpThemeGoodsNo, $goodsNo);
                    }
                    $goodsNoArr = array_unique($tmpThemeGoodsNo);

                    // 탭진열형일때
                    if($themeConfData['displayType'] == '07' && ($themeConfData['themeCd'] == $themeData['themeCd'])){
                        if(!in_array($arrData['goodsNo'], $goodsNoArr)) {
                            $chkArrGoodsNo = explode(STR_DIVISION, $themeData['goodsNo']); // 탭구분기호로 나눔
                            $detailSet = stripslashes($themeConfData['detailSet']);  // 슬래시 제거
                            $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                            for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                                $arrVal = $chkArrGoodsNo[$i];
                                $tabThemeGoodsNo = explode(INT_DIVISION, $arrVal);

                                if (!empty($tabThemeGoodsNo[0])) {
                                    $tabGoodsNo = array_unique(array_merge($chkGoodsNo, $tabThemeGoodsNo));
                                } else {
                                    $tabGoodsNo = $chkGoodsNo;
                                }
                                $impTabGoodsNo[] = implode(INT_DIVISION, $tabGoodsNo);
                            }

                            // 탭진열형 상태로 묶은 상품데이터
                            $newTabGoodsNo = implode(STR_DIVISION, $impTabGoodsNo);
                            $arrBind['param'][] = 'goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $newTabGoodsNo);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                            unset($impTabGoodsNo);
                            unset($arrBind);
                        }
                    }else{
                        // 자동진열일때
                        if ($themeData['sortAutoFl'] == 'y') {
                            $newExceptGoodsNo = $themeData['exceptGoodsNo'] . INT_DIVISION . $arrData['goodsNo'];
                            $strExceptGoodsNo = explode(INT_DIVISION, $newExceptGoodsNo);
                            $newArrExceptGoodsNo = array_values(array_unique(array_diff($strExceptGoodsNo,$chkGoodsNo)));
                            $newStrExceptGoodsNo = implode(INT_DIVISION, $newArrExceptGoodsNo);
                            $arrBind['param'][] = 'exceptGoodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                            unset($arrBind);
                            // 수동진열일때
                        } else {
                            if(!empty($themeData['goodsNo'])){
                                if(!in_array($arrData['goodsNo'], $goodsNoArr)) {
                                    $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                    $goodsNo = $chkStrGoodsNo . INT_DIVISION . $themeData['goodsNo'];
                                    $arrGoodsNo = explode(INT_DIVISION, $goodsNo);
                                    $newArrStrGoodsNo = array_unique($arrGoodsNo);
                                    $newStrGoodsNo = implode(INT_DIVISION, $newArrStrGoodsNo);

                                    $arrBind['param'][] = 'goodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                                    unset($arrBind);
                                }
                            }else {
                                $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                $arrGoodsNo = explode(INT_DIVISION, $chkStrGoodsNo);
                                $newArrStrGoodsNo = array_values(array_unique($arrGoodsNo));
                                $newStrGoodsNo = implode(INT_DIVISION, $newArrStrGoodsNo);

                                $arrBind['param'][] = 'goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                        }
                    }
                } else {
                    $goodsNo = explode(INT_DIVISION, $themeVal['goodsNo']); // 선택되지 않은 메인상품진열의 상품코드값

                    $arrBind = [];
                    $strWhere = "themeCd = ?";
                    $this->db->bind_param_push($arrBind['bind'], 's', $themeVal['themeCd']);
                    $strSQL = "SELECT themeCd, displayType, detailSet FROM ".DB_DISPLAY_THEME_CONFIG." WHERE ".$strWhere;
                    $themeCnfData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                    unset($arrBind);

                    // 탭진열형일때
                    if ($themeCnfData['displayType'] == '07' && ($themeCnfData['themeCd'] == $themeVal['themeCd'])) {
                        $nChkArrGoodsNo = explode(STR_DIVISION, $themeVal['goodsNo']); // 탭구분기호로 나눔
                        $detailSet = stripslashes($themeCnfData['detailSet']);  // 슬래시 제거
                        $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                        for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                            $nChkGoodsNo = $nChkArrGoodsNo[$i];
                            $nChkTabThemeGoodsNo = explode(INT_DIVISION, $nChkGoodsNo);
                            $nChkTabArrGoodsNo = '';
                            if (!empty($nChkTabThemeGoodsNo[0])) {
                                $nChkTabArrGoodsNo = array_values(array_unique(array_diff($nChkTabThemeGoodsNo, $chkGoodsNo)));
                            }
                            $nChkTabStrGoodsNo[] = implode(INT_DIVISION, $nChkTabArrGoodsNo);
                        }

                        // 탭진열형 상태로 묶은 상품데이터
                        $nChkNewTabGoodsNo = implode(STR_DIVISION, $nChkTabStrGoodsNo);
                        $arrBind['param'][] = 'goodsNo = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $nChkNewTabGoodsNo);
                        $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $themeVal['sno'] . '"', $arrBind['bind']);
                        unset($arrBind);
                        unset($nChkTabStrGoodsNo);
                    } else {
                        // 자동진열일때
                        if ($themeVal['sortAutoFl'] == 'y') {
                            $resetExceptGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                            if (!empty($themeVal['exceptGoodsNo'])) {
                                $nChkStrExceptGoodsNo = $themeVal['exceptGoodsNo'] . INT_DIVISION . $resetExceptGoodsNo;
                                $nChkOverlapExceptGoodsNo = explode(INT_DIVISION, $nChkStrExceptGoodsNo);
                                $nChkStrArrGoodsNo = array_values(array_unique($nChkOverlapExceptGoodsNo));
                                $nChkNewStrExceptGoodsNo = implode(INT_DIVISION,$nChkStrArrGoodsNo);
                                $arrBind['param'][] = 'exceptGoodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $nChkNewStrExceptGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $themeVal['sno'] . '"', $arrBind['bind']);
                                unset($arrBind);
                            } else {
                                $arrBind['param'][] = 'exceptGoodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $resetExceptGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $themeVal['sno'] . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                            // 수동진열일때
                        } else {
                            $fixGoodsNo = explode(INT_DIVISION, $themeVal['fixGoodsNo']);
                            $goodsNo =  explode(INT_DIVISION, $themeVal['goodsNo']);
                            if (!empty($themeVal['fixGoodsNo'])) {
                                $resetGoodsNo = array_unique(array_diff($goodsNo, $chkGoodsNo));
                                $newStrGoodsNo = trim(implode(INT_DIVISION, $resetGoodsNo));

                                $resetFixGoodsNo = array_unique(array_diff($fixGoodsNo, $chkGoodsNo)); // 고정값
                                $newFixGoodsNo = trim(implode(INT_DIVISION, $resetFixGoodsNo));

                                $arrBind['param'][] = 'goodsNo = ?';
                                $arrBind['param'][] = 'fixGoodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                $this->db->bind_param_push($arrBind['bind'], 's', $newFixGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $themeVal['sno'] . '"', $arrBind['bind']);
                                unset($arrBind);
                            } else {
                                $nChkNewExceptGoodsNo = array_unique(array_diff($goodsNo, $chkGoodsNo));
                                $nChkStrGoodsNo = trim(implode(INT_DIVISION, $nChkNewExceptGoodsNo));
                                $arrBind['param'][] = 'goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $nChkStrGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $themeVal['sno'] . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                        }
                    }
                }
            }

            $strSQL = "SELECT `sno`, `range`, `goodsNo`, `except_goodsNo` FROM " . DB_POPULATE_THEME;
            $populateThemeData = $this->db->query_fetch($strSQL, null, false);

            if (Validator::required(gd_isset($arrData['goodsNo'])) === false) {
                throw new \Exception(__('상품 정보가 없습니다.'), 500);

            }
            // 인기상품 노출관리 루프
            foreach($populateThemeData as $pthemeKey => $pthemeVal){
                $chkGoodsNo[0] = $arrData['goodsNo'];    // 상품값

                // 인기상품 노출명이 선택되었다면
                if (in_array($pthemeVal['sno'], $arrData['populateListSno'])) {

                    // 선택된 인기상품노출명 루프
                    foreach($arrData['populateListSno'] as $key => $val){
                        $arrBind = [];
                        $strWhere = "sno = ?";
                        $this->db->bind_param_push($arrBind['bind'], 'i', $val);
                        $strSQL = "SELECT `range`,`goodsNo`, `except_goodsNo` FROM " . DB_POPULATE_THEME . " WHERE " . $strWhere;
                        $chkPopulatethemeData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                        unset($arrBind);

                        $exceptGoodsNo = explode(INT_DIVISION, $chkPopulatethemeData['except_goodsNo']); // 선택된 인기상품포함상태의 예외상품값

                        // 인기상품포함상태의 수집범위가 전체상품일떄
                        if($chkPopulatethemeData['range'] == 'all'){
                            if(!empty($chkPopulatethemeData['except_goodsNo'])) {
                                $newExceptGoodsNo = $chkPopulatethemeData['except_goodsNo'] . INT_DIVISION . $arrData['goodsNo'];
                                $strExceptGoodsNo = explode(INT_DIVISION, $newExceptGoodsNo);
                                $newArrExceptGoodsNo = array_unique(array_diff($strExceptGoodsNo, $chkGoodsNo));
                                $newStrExceptGoodsNo = implode(INT_DIVISION, $newArrExceptGoodsNo);
                                $arrBind['param'][] = 'except_goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                                $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                                unset($arrBind);
                            } else {
                                $arrBind['param'][] = 'except_goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['goodsNo']);
                                $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                        } else {
                            $chkNewExceptGoodsNo = $chkPopulatethemeData['except_goodsNo'] . INT_DIVISION . $arrData['goodsNo'];
                            $chkStrExceptGoodsNo = explode(INT_DIVISION, $chkNewExceptGoodsNo);
                            $chkNewArrExceptGoodsNo = array_values(array_unique(array_diff($chkStrExceptGoodsNo, $chkGoodsNo)));
                            $chkNewStrExceptGoodsNo = implode(INT_DIVISION, $chkNewArrExceptGoodsNo);

                            $arrBind['param'][] = 'except_goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $chkNewStrExceptGoodsNo);
                            $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                            unset($arrBind);

                            if(!empty($chkPopulatethemeData['goodsNo'])) {
                                $goodsNo = $arrData['goodsNo'] . INT_DIVISION . $chkPopulatethemeData['goodsNo'];
                                $arrGoodsNo = explode(INT_DIVISION, $goodsNo);
                                $newArrStrGoodsNo = array_values(array_unique($arrGoodsNo));
                                $newStrGoodsNo = implode(INT_DIVISION, $newArrStrGoodsNo);

                                $arrBind['param'][] = 'goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                                unset($arrBind);
                            } else {
                                $arrBind['param'][] = 'goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['goodsNo']);
                                $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $val . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                        }
                    }
                } else {
                    // 선택되지않은 인기상품노출명의 수집번위가 전체 상품일때
                    if ($pthemeVal['range'] == 'all') {
                        if (!empty($pthemeVal['except_goodsNo'])) {
                            $nChkStrExceptGoodsNo = $pthemeVal['except_goodsNo'] . INT_DIVISION . $arrData['goodsNo'];
                            $nChkOverlapExceptGoodsNo = explode(INT_DIVISION, $nChkStrExceptGoodsNo);
                            $nChkStrArrGoodsNo = array_values(array_unique($nChkOverlapExceptGoodsNo));
                            $nChkNewStrExceptGoodsNo = implode(INT_DIVISION, $nChkStrArrGoodsNo);

                            $arrBind['param'][] = 'except_goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $nChkNewStrExceptGoodsNo);
                            $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $pthemeVal['sno'] . '"', $arrBind['bind']);
                            unset($arrBind);
                        } else {
                            $arrBind['param'][] = 'except_goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['goodsNo']);
                            $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $pthemeVal['sno'] . '"', $arrBind['bind']);
                            unset($arrBind);
                        }
                    } else {
                        $nChkGoodsNo = explode(INT_DIVISION, $pthemeVal['goodsNo']); // 선택되지 않은 인기상품노출명의 상품코드값

                        $newGoodsNo = array_values(array_unique(array_diff($nChkGoodsNo, $chkGoodsNo)));
                        $strGoodsNo = implode(INT_DIVISION, $newGoodsNo);
                        $arrBind['param'][] = 'goodsNo = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $strGoodsNo);
                        $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $pthemeVal['sno'] . '"', $arrBind['bind']);
                        unset($arrBind);

                        if($pthemeVal['range'] != 'goods') {
                            if (!empty($pthemeVal['except_goodsNo'])) {
                                $newNchkExceptGoodsNo = $pthemeVal['except_goodsNo'] . INT_DIVISION . $arrData['goodsNo'];
                                $overlapExceptGoodsNo = explode(INT_DIVISION, $newNchkExceptGoodsNo);
                                $strArrGoodsNo = array_values(array_unique($overlapExceptGoodsNo));
                                $newStrExceptGoodsNo = implode(INT_DIVISION, $strArrGoodsNo);

                                $arrBind['param'][] = 'except_goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                                $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $pthemeVal['sno'] . '"', $arrBind['bind']);
                                unset($arrBind);
                            } else {
                                $arrBind['param'][] = 'except_goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['goodsNo']);
                                $this->db->set_update_db(DB_POPULATE_THEME, $arrBind['param'], 'sno = "' . $pthemeVal['sno'] . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                        }
                    }
                }
            }
        }

        // 이미지 저장 경로 설정
        $this->imagePath = $arrData['imagePath'];
        if (empty($arrData['imagePath']) && ($arrData['mode'] == 'register' || $arrData['applyGoodsCopy'])) {
            $this->imagePath = $arrData['imagePath'] = DIR_GOODS_IMAGE . $arrData['goodsNo'] . '/';
        }

        $getLink = $getBrand = $getAddInfo = $getOption = $getOptionText = $getOptionAddName = $getOptionAddValue = array();
        if ($arrData['mode'] == 'modify') {
            $getLink = $this->getGoodsLinkCategory($arrData['goodsNo']); // 카테고리 정보
            $getBrand = $this->getGoodsLinkBrand($arrData['goodsNo']); // 브랜드 정보
            $getAddInfo = $this->getGoodsAddInfo($arrData['goodsNo']); // 상품 추가 정보
            $getOption = $this->getGoodsOption($arrData['goodsNo'], $arrData); // 옵션/가격 정보
            if ($getOption) {
                foreach ($getOption as $k => $v) {
                    $getOption[$k]['optionPrice'] = gd_money_format($v['optionPrice'], false);
                }
            }

            $getOptionText = $this->getGoodsOptionText($arrData['goodsNo']); // 텍스트 옵션 정보
            if ($getOptionText) {
                foreach ($getOptionText as $k => $v) {
                    $getOption[$k]['addPrice'] = gd_money_format($v['addPrice'], false);
                }
            }

            $getGoods = $this->getGoodsInfo($arrData['goodsNo']); // 상품정보

            unset($getOption['optVal']); // 옵션값은 삭제
        }


        // 브랜드 설정
        if ((empty($arrData['brandCd']) && empty($arrData['brand']) === false) || gd_isset($arrData['brandSelect']) == 'y') {
            $arrData['brandCd'] = ArrayUtils::last($arrData['brand']);
        }
        $arrData['brandLink'] = array();
        if (gd_isset($arrData['brandCd'])) {
            $arrData['brandLink'] = $this->getGoodsBrandCheck($arrData['brandCd'], $getBrand, $arrData['goodsNo']);
        }

        $useCateCd = array_slice($arrData['link']['cateCd'], 0, count($arrData['link']['fixSort']));
        //카테고리 순서 관련
        $strSQL = "SELECT glc.cateCd, IF(MAX(glc.goodsSort) > 0, (MAX(glc.goodsSort) + 1), 1) AS sort,MIN(glc.goodsSort) - 1 as reSort, MAX(glc.fixSort) + 1 as fixSort, glc.cateCd,cg.sortAutoFl,cg.sortType FROM ".DB_GOODS_LINK_CATEGORY." AS glc INNER JOIN ".DB_CATEGORY_GOODS." AS cg ON cg.cateCd = glc.cateCd WHERE glc.cateCd IN  ('" . implode('\',\'', $arrData['link']['cateCd']) . "') GROUP BY glc.cateCd ORDER BY FIELD (glc.cateCd, '" . implode('\',\'', $arrData['link']['cateCd']) . "')";
        $result = $this->db->query($strSQL);
        while ($sortData = $this->db->fetch($result)) {

            // 상단고정
            if ($arrData['goodsSortTop'] == 'y' && in_array($sortData['cateCd'], $useCateCd) === false) {
                $arrData['link']['fixSort'][] = $sortData['fixSort'];
            }
            if ($sortData['sortAutoFl'] == 'y') $arrData['link']['goodsSort'][] = 0;
            else  {
                if ($sortData['sortType'] == 'bottom') $arrData['link']['goodsSort'][] = $sortData['reSort'];
                else $arrData['link']['goodsSort'][] = $sortData['sort'];
            }
        }
        // 카테고리 정보
        $compareLink = $this->db->get_compare_array_data($getLink, $arrData['link'],true,array_keys($arrData['link']));

        // 브랜드 정보
        $compareBrand = $this->db->get_compare_array_data($getBrand, $arrData['brandLink']);

        // 상품 추가 정보
        $compareAddInfo = $this->db->get_compare_array_data($getAddInfo, gd_isset($arrData['addInfo']));

        // 옵션 가격 정보
        $compareOption = $this->db->get_compare_array_data($getOption, $arrData['option'], true, array_keys($arrData['option']), 'tableGoodsOption');

        // 전체 재고량
        if($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y') {
        }else{
            if (isset($arrData['option']['stockCnt'])) $arrData['totalStock'] = array_sum($arrData['option']['stockCnt']);
            else $arrData['totalStock'] = $arrData['stockCnt'];
        }

        // 텍스트 옵션 정보
        if ($arrData['optionTextFl'] != 'y') {
            unset($arrData['optionText']);
        }
        $compareOptionText = $this->db->get_compare_array_data($getOptionText, gd_isset($arrData['optionText']));

        // 공통 키값
        $arrDataKey = array('goodsNo' => $arrData['goodsNo']);
        $compareData = [];

        // 카테고리 정보 저장
        $cateLog = $this->db->set_compare_process(DB_GOODS_LINK_CATEGORY, $arrData['link'], $arrDataKey, $compareLink);
        if ($cateLog && $arrData['mode'] == 'modify') {
            $updateFl = "y";
            $this->setGoodsLog('category', $arrData['goodsNo'], $getLink, $cateLog);
        }

        // 브랜드 정보 저장
        $this->db->set_compare_process(DB_GOODS_LINK_BRAND, $arrData['brandLink'], $arrDataKey, $compareBrand);

        // 상품 추가정보 저장
        $addInfoLog = $this->db->set_compare_process(DB_GOODS_ADD_INFO, $arrData['addInfo'], $arrDataKey, $compareAddInfo);
        if ($addInfoLog && $arrData['mode'] == 'modify') {
            $updateFl = "y";
            $this->setGoodsLog('addInfo', $arrData['goodsNo'], $getAddInfo, $addInfoLog);
        }

        // 운영자 상품 재고 수정 권한에 따른 처리
        $optionUpdateData = null;
        if ($arrData['mode'] == 'modify' && Session::get('manager.functionAuth.goodsStockModify') != 'y') {
            $goodsOptionField = DBTableField::tableGoodsOption();
            foreach ($goodsOptionField as $oKey => $oVal) {
                if ($oVal['val'] != 'stockCnt') {
                    $optionUpdateData[] = $oVal['val'];
                }
            }
            unset($goodsOptionField);
        }

        if ($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y') {
        }else{
            // 옵션 가격 정보
            $optionLog = $this->db->set_compare_process(DB_GOODS_OPTION, $arrData['option'], $arrDataKey, $compareOption, $optionUpdateData);
            if ($optionLog && $arrData['mode'] == 'modify') {
                $updateFl = "y";
                $this->setGoodsLog('option', $arrData['goodsNo'], $getOption, $optionLog);
            }
            unset($optionUpdateData);
        }

        // 텍스트 옵션 정보 저장
        $optionTextLog = $this->db->set_compare_process(DB_GOODS_OPTION_TEXT, $arrData['optionText'], $arrDataKey, $compareOptionText);
        if ($optionTextLog && $arrData['mode'] == 'modify') {
            $updateFl = "y";
            $this->setGoodsLog('optionText', $arrData['goodsNo'], $getOptionText, $optionTextLog);
        }

        // SEO 데이터 처리
        $seoTagData = $arrData['seoTag'];
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        if (empty($arrData['seoTagFl']) === false) {
            $seoTagData['sno'] = $arrData['seoTagSno'];
            $seoTagData['pageCode'] = $arrData['goodsNo'];
            $arrData['seoTagSno'] = $seoTag->saveSeoTagEach('goods',$seoTagData);
        }


        // 카테고리 삭제 시 카테고리 필드값 null처리
        if(empty($arrData['cateCd'])) $arrData['cateCd'] = '';

        //서로등록 수동 + 선택상품 사용함 이면
        //            if($arrData['relationFl'] == 'm'/* && $arrData['relationSameFl'] != 'n'*/) {
        $strSQL = ' SELECT goodsNo, relationGoodsNo, relationGoodsEach FROM ' . DB_GOODS . ' WHERE relationGoodsNo LIKE concat(\'%\',?,\'%\') AND concat (relationFl, relationSameFl) != \'mn\'';
        $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        if(empty($res[0])){
            $res[0] = $res;
        }
        unset($arrBind);

        foreach ($res as $k => $v) {
            $tmpRelatedGoodsList['original'] = $res[$k]['goodsNo'];
            $tmpRelatedGoodsList['goodsNo'] = array_filter(explode(INT_DIVISION, $res[$k]['relationGoodsNo']));
            if (empty($res['relationGoodsEach']) || count(explode(INT_DIVISION, $res[$k]['relationGoodsEach'])) != count($tmpRelatedGoodsList['goodsNo'])) {
                $res[$k]['relationGoodsEach'] = str_pad('', count($tmpRelatedGoodsList['goodsNo']) * 3, 'y' . STR_DIVISION);
            }
            $tmpRelatedGoodsList['each'] = array_filter(explode(STR_DIVISION, $res[$k]['relationGoodsEach']));

            foreach ($tmpRelatedGoodsList['goodsNo'] as $key => $value) {
                if ($value == $arrData['goodsNo']) {
                    unset($tmpRelatedGoodsList['goodsNo'][$key]);
                    unset($tmpRelatedGoodsList['each'][$key]);
                }
            }

            //업데이트 처리
            $this->db->set_update_db(DB_GOODS, "relationGoodsNo = '" . implode(INT_DIVISION, $tmpRelatedGoodsList['goodsNo']) . "', relationGoodsEach = '" . implode(STR_DIVISION, $tmpRelatedGoodsList['each']) . "'", "goodsNo = '{$tmpRelatedGoodsList['original']}'");
            unset($tmpRelatedGoodsList);
        }
        //            }
        // 관련 상품 설정
        if ($arrData['relationFl'] == 'm') {
            if (isset($arrData['relationGoodsNo'])) {

                $arrData['relationCnt'] = '0';

                foreach ($arrData['relationGoodsNo'] as $k => $v) {

                    if ($v == $arrData['goodsNo']) {
                        unset($arrData['relationGoodsNo'][$k]);
                    } else {
                        if (gd_isset($arrData['relationGoodsNoStartYmd'][$k])) {
                            $relationGoodsDate[$v]['startYmd'] = $arrData['relationGoodsNoStartYmd'][$k];
                        }
                        if (gd_isset($arrData['relationGoodsNoEndYmd'][$k])) {
                            $relationGoodsDate[$v]['endYmd'] = $arrData['relationGoodsNoEndYmd'][$k];
                        }

                        //서로 등록 관련
                        $strSQL = ' SELECT COUNT(*) AS cnt,relationGoodsNo FROM ' . DB_GOODS . ' WHERE relationGoodsNo LIKE concat(\'%\',?,\'%\') AND goodsNo = ?';
                        $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);
                        $this->db->bind_param_push($arrBind, 's', $v);
                        $res = $this->db->query_fetch($strSQL, $arrBind, false);
                        unset($arrBind);
                        $tmpCnt = $res['cnt'];
                        $tmpRelationGoodsNo = $res['relationGoodsNo'];


                        //서로 등록인 경우
                        if ($arrData['relationSameFl'] == 'y' && $tmpCnt == 0) {
                            //상품수정일 변경유무 추가
                            if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') {
                                $this->db->setModDtUse(false);
                            }
                            $this->db->set_update_db(DB_GOODS, "relationFl = 'm', relationGoodsNo = concat(relationGoodsNo,if( CHAR_LENGTH(relationGoodsNo) = 0, '', '" . INT_DIVISION . "' ) ,'" . $arrData['goodsNo'] . "')", "goodsNo = '{$v}'");
                        }

                        //일부 상품 서로 등록인 경우
                        if ($arrData['relationSameFl'] == 's' || $arrData['relationSameFl'] == 'y') {
                            //$this->db->set_update_db(DB_GOODS, "relationFl = 'm', relationSameFl='s', relationGoodsNo = concat(relationGoodsNo,if( CHAR_LENGTH(relationGoodsNo) = 0, '', '" . INT_DIVISION . "' ) ,'" . $arrData['goodsNo'] . "'), relationGoodsEach = concat(relationGoodsEach,if( CHAR_LENGTH(relationGoodsEach) = 0, '', '" . INT_DIVISION . "' ) ,'" . $arrData['relationEach'][$k] . "')", "goodsNo = '{$v}'");
                            $tmpRelatedGoods['goodsNo'][] = $v;
                            $tmpRelatedGoods['each'][] = empty($arrData['relationEach'][$k]) ? 'y' : $arrData['relationEach'][$k];
                        }

                        //서로 등록이 아닌 경우
                        if ($arrData['relationSameFl'] == 'n' && $tmpCnt > 0) {
                            //$tmpRelationGoodsNo = str_replace(INT_DIVISION, '', str_replace($arrData['goodsNo'], "", $tmpRelationGoodsNo));
                            //$this->db->set_update_db(DB_GOODS, "relationFl = '" . $relationFl . "', relationGoodsNo = replace(relationGoodsNo,'" . $arrData['goodsNo'] . "','')", "goodsNo = '{$v}'");
                            //$this->db->set_update_db(DB_GOODS, "relationGoodsNo = replace(relationGoodsNo,'" . $arrData['goodsNo'] . "','')", "goodsNo = '{$v}'");
                        }
                    }
                }

                //관련상품 목록 처리
                if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS, "relationFl = 'm', relationSameFl = 's', relationGoodsNo = '".implode(INT_DIVISION, $tmpRelatedGoods['goodsNo'])."', relationGoodsEach = '".implode(STR_DIVISION, $tmpRelatedGoods['each'])."'", "goodsNo = '{$arrData['goodsNo']}'");

                foreach($tmpRelatedGoods['goodsNo'] as $k => $v) {
                    $strSQL = ' SELECT relationGoodsNo, relationGoodsEach FROM ' . DB_GOODS . ' WHERE goodsNo = ?';
                    $this->db->bind_param_push($arrBind, 's', $v);
                    $res = $this->db->query_fetch($strSQL, $arrBind, false);
                    unset($arrBind);

                    $tmpRelatedGoodsList['goodsNo'] = array_filter(explode(INT_DIVISION, $res['relationGoodsNo']));
                    if(empty($res['relationGoodsEach']) || count(explode(INT_DIVISION, $res['relationGoodsEach'])) != count($tmpRelatedGoodsList['goodsNo'])){
                        $res['relationGoodsEach'] = str_pad('', count($tmpRelatedGoodsList['goodsNo']) * 3, 'y'.STR_DIVISION);
                    }
                    $tmpRelatedGoodsList['each'] = array_filter(explode(STR_DIVISION, $res['relationGoodsEach']));
                    if(!in_array($arrData['goodsNo'], $tmpRelatedGoodsList['goodsNo']) && $tmpRelatedGoods['each'][$k] == 'y'){
                        //사용함인데 상품이 등록되어 있지 않을 경우
                        $tmpRelatedGoodsList['goodsNo'][] = $arrData['goodsNo'];
                        $tmpRelatedGoodsList['each'][] = 'y';
                    }else if($tmpRelatedGoods['each'][$k] != 'y'){
                        foreach($tmpRelatedGoodsList['goodsNo'] as $key => $value){
                            if($value == $arrData['goodsNo']){
                                //사용안함이면 삭제 처리
                                unset($tmpRelatedGoodsList['goodsNo'][$key]);
                                unset($tmpRelatedGoodsList['each'][$key]);
                            }
                        }
                    }

                    //업데이트 처리
                    if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
                        $this->db->setModDtUse(false);
                    }
                    $this->db->set_update_db(DB_GOODS, "relationFl='m', relationSameFl='s'", "goodsNo = '{$v}' AND (relationFl='n' OR relationSameFl='n')");

                    if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
                        $this->db->setModDtUse(false);
                    }
                    $this->db->set_update_db(DB_GOODS, "relationGoodsNo = '" . implode(INT_DIVISION, $tmpRelatedGoodsList['goodsNo']) . "', relationGoodsEach = '" . implode(STR_DIVISION, $tmpRelatedGoodsList['each']) . "'", "goodsNo = '{$v}' AND relationFl='m'");
                    unset($tmpRelatedGoodsList);
                }

                //서로등록 설정 저장
                $relationEach = implode(STR_DIVISION, $arrData['relationEach']);
                $arrData['relationGoodsEach'] = $relationEach;
                if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS, "relationGoodsEach = '" . $relationEach . "'", "goodsNo = '{$v}'");

                $arrData['relationGoodsDate'] = json_encode($relationGoodsDate);

                $arrData['relationGoodsNo'] = implode(INT_DIVISION, $arrData['relationGoodsNo']);

            } else {
                //$arrData['relationFl'] = 'n';
                $arrData['relationCnt'] = '0';
                $arrData['relationGoodsNo'] = null;
            }
        }
        if ($arrData['relationFl'] == 'a') {
            $strSQL = ' SELECT goodsNo, relationGoodsNo, relationGoodsEach FROM ' . DB_GOODS . ' WHERE relationGoodsNo LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);
            $res = $this->db->query_fetch($strSQL, $arrBind, false);
            unset($arrBind);

            foreach($res as $resKey => $resValue){
                $tmpRelatedGoodsList['goodsNo'] = array_filter(explode(INT_DIVISION, $resValue['relationGoodsNo']));
                $tmpRelatedGoodsList['each'] = array_filter(explode(STR_DIVISION, $resValue['relationGoodsEach']));

                foreach($tmpRelatedGoodsList['goodsNo'] as $key => $value){
                    if($value == $arrData['goodsNo']){
                        unset($tmpRelatedGoodsList['goodsNo'][$key]);
                        unset($tmpRelatedGoodsList['each'][$key]);
                    }
                }
                //업데이트 처리
                if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS, "relationGoodsNo = '" . implode(INT_DIVISION, $tmpRelatedGoodsList['goodsNo']) . "', relationGoodsEach = '" . implode(STR_DIVISION, $tmpRelatedGoodsList['each']) . "'", "goodsNo = '{$resValue['goodsNo']}'");
                unset($tmpRelatedGoodsList);
            }

            $arrData['relationCnt'] = '0';
            $arrData['relationGoodsNo'] = null;
        }
        if ($arrData['relationFl'] == 'n') {
            $arrData['relationCnt'] = '0';
            $arrData['relationGoodsNo'] = null;
        }

        if(isset($arrData['goodsIconCdPeriod']) || isset($arrData['goodsIconCd'])) { // 상품 정보에 기존 아이콘 정보 호출
            $originGoodsIconData = $this->getGoodsDetailIcon($arrData['goodsNo']);
            $originGoodsIconTableData = [];
            foreach($originGoodsIconData as $originIconKey => $originIconValue) {
                $originGoodsIconTableData['goodsIconCd'][] = $originIconValue['goodsIconCd'];
                $originGoodsIconTableData['goodsIconStartYmd'] = $originIconValue['goodsIconStartYmd'];
                $originGoodsIconTableData['goodsIconEndYmd'] = $originIconValue['goodsIconEndYmd'];
            }
        }

        // 아이콘 설정 (기간제한 아이콘)
        if (isset($arrData['goodsIconCdPeriod'])) {

            $this->setGoodsIcon($arrData['goodsIconCdPeriod'], 'pe', $arrData['goodsNo'], $arrData['goodsIconStartYmd'], $arrData['goodsIconEndYmd'], $arrData['benefitSno']);

        } else {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);
            $this->db->bind_param_push($arrBind, 's', 'pe');
            $this->db->set_delete_db(DB_GOODS_ICON, 'goodsNo = ? AND iconKind = ?', $arrBind);
            unset($arrBind);
        }

        // 아이콘 설정 (무제한 아이콘)
        if (isset($arrData['goodsIconCd'])) {
            $this->setGoodsIcon($arrData['goodsIconCd'], 'un', $arrData['goodsNo'], $arrData['goodsIconStartYmd'], $arrData['goodsIconEndYmd'], $arrData['benefitSno']);
        } else {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);
            $this->db->bind_param_push($arrBind, 's', 'un');
            $this->db->set_delete_db(DB_GOODS_ICON, 'goodsNo = ? AND iconKind = ?', $arrBind);
            unset($arrBind);
        }


        /* 아이콘 별도테이블 저장으로 인한 주석
        if (isset($arrData['goodsIconCdPeriod'])) {
            $arrData['goodsIconCdPeriod'] = implode(INT_DIVISION, $arrData['goodsIconCdPeriod']);
        } else {
            $arrData['goodsIconCdPeriod'] = "";
        }
        if (isset($arrData['goodsIconCd'])) {
            $arrData['goodsIconCd'] = implode(INT_DIVISION, $arrData['goodsIconCd']);
        } else {
            $arrData['goodsIconCd'] ="";
        }
        */

        //
        $arrData['mileageGroupMemberInfo'] = str_replace('\'', '', json_encode($arrData['mileageGroupMemberInfo'], JSON_UNESCAPED_UNICODE));
        $arrData['mileageGroupInfo'] = @implode(INT_DIVISION, $arrData['mileageGroupInfo']);
        $arrData['fixedGoodsDiscount'] = @implode(STR_DIVISION, $arrData['fixedGoodsDiscount']);
        $arrData['goodsDiscountGroupMemberInfo'] = str_replace('\'', '', json_encode($arrData['goodsDiscountGroupMemberInfo'], JSON_UNESCAPED_UNICODE));
        $arrData['exceptBenefit'] = @implode(STR_DIVISION, $arrData['exceptBenefit']);
        if (empty($arrData['exceptBenefit']) === true) $arrData['exceptBenefitGroup'] = '';
        if ($arrData['exceptBenefitGroup'] == 'group') {
            $arrData['exceptBenefitGroupInfo'] = @implode(INT_DIVISION, $arrData['exceptBenefitGroupInfo']);
        } else {
            $arrData['exceptBenefitGroupInfo'] = '';
        }

        //상품 혜택 정보
        if(!Session::get('manager.isProvider')) {
            $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
            if ($arrData['benefitSno'] > 0 && $arrData['goodsBenefitSetFl'] == 'y') {

                $benefitData = $goodsBenefit->getGoodsBenefit($arrData['benefitSno']);
                //종료가 된 혜택은 개별로 리셋
                if ($arrData['applyNo'] && $benefitData['benefitUseType'] == "periodDiscount" && strtotime($benefitData['periodDiscountEnd']) < strtotime("now")) {
                    $arrData['benefitSno'] = 0;
                    $arrData['goodsBenefitSetFl'] = 'n';
                }

                $exceptKey = array('sno', 'benefitNm', 'goodsIconCd', 'benefitScheduleNextSno', 'goodsDiscountFl', 'benefitScheduleFl', 'modDt', 'regDt');
                foreach (DBTableField::tableGoodsBenefit() as $value) {
                    if (in_array($value['val'], $exceptKey)) {
                        continue;
                    }
                    $arrData[$value['val']] = '';
                }
                $arrData['goodsDiscountFl'] = 'n';
                $goodsBenefit->addGoodsLink($arrData['benefitSno'], $arrData['goodsNo']);
            } else if ($arrData['goodsBenefitSetFl'] == 'n') { //상품 혜택 링크 삭제
                $goodsBenefit->delGoodsLink($arrData['goodsNo']);
            }
        }


        $arrData['hscode'] = array_filter(array_map('trim',$arrData['hscode']));

        if($arrData['hscode']) {
            $hscode = [];
            foreach($arrData['hscodeNation'] as $k => $v) {
                $hscode[$v] = $arrData['hscode'][$k];
            }
            $arrData['hscode'] = json_encode(gd_htmlspecialchars($hscode), JSON_UNESCAPED_UNICODE);
            unset($hscode);
        } else {
            $arrData['hscode'] = "";
        }

        if ($arrData['mode'] == 'modify') {

            DBTableField::setDefaultData('tableGoods', array_keys($arrData));

            $result = [];
            // 'orderGoodsCnt', 'cartCnt', 'wishCnt' 제외 추가
            $expectField = array('modDt', 'regDt', 'applyDt', 'applyFl', 'applyMsg', 'delDt', 'applyType', 'hitCnt', 'orderCnt', 'orderGoodsCnt', 'reviewCnt', 'cartCnt', 'wishCnt');

            if (Session::get('manager.functionAuth.goodsStockModify') != 'y') {
                $expectField[] = 'totalStock';
            }

            // 기존 정보를 변경
            foreach ($getGoods as $key => $val) {
                if ($val != $arrData[$key] && !in_array($key, $expectField)) {
                    $result[$key] = $arrData[$key];
                }
            }
            if(isset($arrData['goodsIconCdPeriod']) || isset($arrData['goodsIconCd'])) { // 상품 정보에 기존 아이콘 정보 호출
                $getGoods['goodsIconCd'] = $originGoodsIconTableData['goodsIconCd'];
                $getGoods['goodsIconStartYmd'] = $originGoodsIconTableData['goodsIconStartYmd'];
                $getGoods['goodsIconEndYmd'] = $originGoodsIconTableData['goodsIconEndYmd'];
            }

            if ($result) {
                $updateFl = "y";
                $this->setGoodsLog('goods', $arrData['goodsNo'], $getGoods, $result);
            }
        }


        //공급사이면서 자동승인이 아닌경우 상품 승인신청 처리
        if (Session::get('manager.isProvider') && (($arrData['mode'] == 'modify' && Session::get('manager.scmPermissionModify') == 'c') || ($arrData['mode'] == 'register' && Session::get('manager.scmPermissionInsert') == 'c'))) {
            if (($arrData['mode'] == 'modify' && $updateFl == 'y') || $arrData['mode'] == 'register') {
                $arrData['applyFl'] = 'a';
                $arrData['applyDt'] = date('Y-m-d H:i:s');
            }

            $arrData['applyType'] = strtolower(substr($arrData['mode'], 0, 1));

        } else  $arrData['applyFl'] = 'y';

        //'orderGoodsCnt', 'cartCnt', 'wishCnt' 제외 추가
        $arrExclude[] = 'hitCnt';
        $arrExclude[] = 'orderCnt';
        $arrExclude[] = 'orderGoodsCnt';
        $arrExclude[] = 'reviewCnt';
        $arrExclude[] = 'cartCnt';
        $arrExclude[] = 'wishCnt';

        if(Session::get('manager.isProvider')) { // 공급사 일 때 제외 항목(제외하지 않으면 초기화됨)
            $arrExclude[] = 'displayThemeSno';
            $arrExclude[] = 'populateListSno';
            $arrExclude[] = 'payLimitFl';
            $arrExclude[] = 'payLimit';
            $arrExclude[] = 'mileageFl';
            $arrExclude[] = 'mileageGroup';
            $arrExclude[] = 'mileageGoods';
            $arrExclude[] = 'mileageGoodsUnit';
            $arrExclude[] = 'mileageGroupInfo';
            $arrExclude[] = 'mileageGroupMemberInfo';
            $arrExclude[] = 'goodsBenefitSetFl';
            $arrExclude[] = 'goodsDiscountFl';
            $arrExclude[] = 'benefitUseType';
            $arrExclude[] = 'newGoodsRegFl';
            $arrExclude[] = 'newGoodsDate';
            $arrExclude[] = 'newGoodsDateFl';
            $arrExclude[] = 'periodDiscountStart';
            $arrExclude[] = 'periodDiscountEnd';
            $arrExclude[] = 'goodsDiscountFl';
            $arrExclude[] = 'goodsDiscount';
            $arrExclude[] = 'goodsDiscountUnit';
            $arrExclude[] = 'fixedGoodsDiscount';
            $arrExclude[] = 'goodsDiscountGroup';
            $arrExclude[] = 'goodsDiscountGroupMemberInfo';
            $arrExclude[] = 'exceptBenefit';
            $arrExclude[] = 'exceptBenefitGroup';
            $arrExclude[] = 'exceptBenefitGroupInfo';
        }

        // 운영자 기능권한 처리
        if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsSalesDate') != 'y') {
            $arrExclude[] = 'salesStartYmd';
            $arrExclude[] = 'salesEndYmd';
        }
        // 상품 정보 저장
        if ($arrData['mode'] == 'modify') {
            // 운영자 기능권한 처리
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsCommission') != 'y') {
                $arrExclude[] = 'commission';
            }
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsNm') != 'y') {
                $arrExclude[] = 'goodsNmFl';
                $arrExclude[] = 'goodsNm';
                $arrExclude[] = 'goodsNmMain';
                $arrExclude[] = 'goodsNmList';
                $arrExclude[] = 'goodsNmDetail';
            }
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsPrice') != 'y') {
                $arrExclude[] = 'goodsPrice';
            }
            if (Session::get('manager.functionAuth.goodsStockModify') != 'y') {
                $arrExclude[] = 'totalStock';
            }
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoods', array_keys($arrData)), $arrData, 'update', null, $arrExclude);
        } else {
            // 운영자 기능권한 처리
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsCommission') != 'y') {
                if ($arrData['scmNo'] != DEFAULT_CODE_SCMNO) {
                    $scm = \App::load('\\Component\\Scm\\ScmAdmin');
                    $scmInfo = $scm->getScmInfo($arrData['scmNo'], 'scmCommission');
                    $arrData['commission'] = $scmInfo['scmCommission'];
                }
            }
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoods', array_keys($arrData)), $arrData, 'update', null, $arrExclude);
        }

        if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
            $this->db->setModDtUse(false);
        }
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo']);
        $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
        unset($arrBind);

        if($this->goodsDivisionFl) {
            if ($arrData['modDtUse'] == 'n' && $arrData['mode'] == 'modify') { //상품수정일 변경유무 추가
                $this->db->setModDtUse(false);
            }
            //검색테이블 업데이트
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoodsSearch', array_keys($arrData)), $arrData, 'update', null, $arrExclude);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo']);
            $this->db->set_update_db(DB_GOODS_SEARCH, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            unset($arrBind);
        }

        if($this->gGlobal['isUse']) {

            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);
            $this->db->set_delete_db(DB_GOODS_GLOBAL, 'goodsNo = ?', $arrBind);
            unset($arrBind);

            if($arrData['globalData']) {
                foreach($arrData['globalData'] as $k => $v) {
                    if(array_filter(array_map('trim',$v))) {
                        $globalData = $v;
                        $globalData['mallSno'] = $k;
                        $globalData['goodsNo'] = $arrData['goodsNo'];

                        $arrBind = $this->db->get_binding(DBTableField::tableGoodsGlobal(), $globalData, 'insert');
                        $this->db->set_insert_db(DB_GOODS_GLOBAL, $arrBind['param'], $arrBind['bind'], 'y');
                        unset($arrBind);
                    }
                }
            }
        }
        // --- 상품 이미지 정보 저장
        if ($arrData['imageStorage'] == 'url') {
            $imageMode = $arrData['image'];
        } else {
            $imageMode = Request::files()->toArray()['image'];
        }

        if($arrData['imageStorage'] != 'url' && $arrData['applyGoodsCopy'] && $arrData['applyGoodsimagePath']) {
            $this->db->set_delete_db(DB_GOODS_IMAGE, "goodsNo = '".$arrData['goodsNo']."'");
            Storage::disk(Storage::PATH_CODE_GOODS, $arrData['imageStorage'])->deleteDir($this->imagePath);
            Storage::copy(Storage::PATH_CODE_GOODS, $arrData['applyGoodsimageStorage'], $arrData['applyGoodsimagePath'], $arrData['imageStorage'], $this->imagePath);
        }

        $this->imageUploadGoods($imageMode, $arrData['imageStorage'], $arrData['imageSize'], gd_isset($arrData['imageResize']), gd_isset($arrData['imageDB']), $arrData['goodsNo'],$arrData['mode']);

        if($arrData['mobileappFl'] === true){
            //모바일앱에서 접근시 상품이미지 처리
            $this->mobileapp_imageUploadGoods($arrData);
            //1시간이 지난 임시 이미지 삭제
            $this->mobileapp_removeTempImage();
        }

        // --- 상품 옵션 추가노출 저장
        if($optionType['use'] == 'y' && $arrData['optionFl'] == 'y' && $arrData['optionReged'] != 'y'){
        }else {
            if ($arrData['optionFl'] == 'y') {
                if ($arrData['option']['optionImageDeleteFl']) {
                    foreach ($arrData['option']['optionImageDeleteFl'][0] as $k => $v) {
                        if ($v == 'y') {
                            $arrBind = [];
                            $this->db->bind_param_push($arrBind, 'i', $arrData['goodsNo']);
                            $this->db->bind_param_push($arrBind, 's', $arrData['option']['optionValue'][0][$k]);
                            $this->db->set_delete_db(DB_GOODS_OPTION_ICON, 'goodsNo = ? AND optionValue = ?', $arrBind);
                            unset($arrData['optionYIcon']['goodsImage'][0][$k]);
                            unset($arrBind);
                        }
                    }
                }

                //새창에서 파일을 등록할 경우 임시로 업로드 된 파일 처리
                if($optionType['use'] == 'y'){
                    //임시로 등록된 파일 가져오기
                    $arrBind = [];
                    $strWhere = 'session = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', $arrData['optionTempSession']);
                    $strSQL = "SELECT * FROM " . DB_GOODS_OPTION_ICON_TEMP . " WHERE " . $strWhere;
                    $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], true);
                    foreach($getData as $key => $value){
                        if ((substr($value['goodsImage'], 0, 4) == 'http' || empty($value['goodsImage'])) && $value['isUpdated'] == 'y') {
                            //옵션 항목 수정
                            $strWhereOption = 'optionValue="'.$value['optionValue'].'" AND goodsNo="'.$arrData['goodsNo'].'"';
                            $strSQL = "SELECT count(*) cnt FROM " . DB_GOODS_OPTION_ICON . " WHERE " . $strWhereOption;
                            $getDataOrder = $this->db->query_fetch($strSQL, null, false);
                            if ($getDataOrder['cnt'] > 0) {
                                $arrBindOption['param'][] = 'goodsImage = ?';
                                $this->db->bind_param_push($arrBindOption['bind'], 's', $value['goodsImage']);
                                $strWhereOption = 'optionValue="'.$value['optionValue'].'" AND goodsNo="'.$arrData['goodsNo'].'"';
                                $this->db->set_update_db(DB_GOODS_OPTION_ICON, $arrBindOption['param'], $strWhereOption, $arrBindOption['bind']);
                            } else {
                                $arrBindOption['param'][] = 'goodsNo';
                                $arrBindOption['param'][] = 'optionValue';
                                $arrBindOption['param'][] = 'goodsImage';

                                $this->db->bind_param_push($arrBindOption['bind'], 's', $arrData['goodsNo']);
                                $this->db->bind_param_push($arrBindOption['bind'], 's', $value['optionValue']);
                                $this->db->bind_param_push($arrBindOption['bind'], 's', $value['goodsImage']);

                                $this->db->set_insert_db(DB_GOODS_OPTION_ICON, $arrBindOption['param'], $arrBindOption['bind'], 'y');
                            }
                            unset($arrBindOption);
                        }
                        $optionImgPath = App::getUserBasePath() . '/data/goods/option_temp';
                        $optionFile['name']['goodsImage'][0][$value['optionNo']]= $value['goodsImage'];
                        $optionFile['tmp_name']['goodsImage'][0][$value['optionNo']]= $optionImgPath.DIRECTORY_SEPARATOR.$value['goodsImage'];
                        $optionFile['type']['goodsImage'][0][$value['optionNo']]= mime_content_type($optionImgPath.DIRECTORY_SEPARATOR.$value['goodsImage']);
                        if(empty($value['goodsImage'])){
                            $iconTmpCode = '4';
                        }else{
                            $iconTmpCode = '0';
                        }
                        $optionFile['error']['goodsImage'][0][$value['optionNo']] = $iconTmpCode;
                        $optionFile['size']['goodsImage'][0][$value['optionNo']] = filesize($optionImgPath.DIRECTORY_SEPARATOR.$value['goodsImage']);
                    }
                } else {
                    $optionFile = Request::files()->get('optionYIcon');
                }
                if(!empty($optionFile)) {
                    $this->imageUploadIcon($optionFile, $arrData['optionYIcon'], $arrData['option']['optionValue'], $arrData['imageStorage']);
                }
                if($optionType['use'] == 'y'){
                    foreach($getData as $key => $value){
                        @unlink($optionImgPath.DIRECTORY_SEPARATOR.$value['goodsImage']);
                    }
                    $this->db->bind_param_push($arrBind, 's', $arrData['optionTempSession']);
                    $this->db->set_delete_db(DB_GOODS_OPTION_ICON_TEMP, 'session = ?', $arrBind['bind']);
                    $this->db->set_delete_db(DB_GOODS_OPTION_TEMP, 'session = ?', $arrBind['bind']);
                    unset($arrBind);

                    //파일명에 맞춰 옵션명 변경
                    $tempOptionList = array_unique($arrData['option']['optionValue1']);

                    foreach($tempOptionList as $optionTempKey => $optionTempVal){
                        $optionUnique[] = $optionTempVal;
                    }
                    foreach($arrData['optionYIcon']['goodsImage'][0] as $iconKey => $iconVal){
                        $arrBind['param'][] = 'optionValue = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $optionUnique[$iconKey]);
                        $strWhere = 'goodsImage="'.$iconVal.'" AND goodsNo="'.$arrData['goodsNo'].'"';
                        $this->db->set_update_db(DB_GOODS_OPTION_ICON, $arrBind['param'], $strWhere, $arrBind['bind']);
                        unset($arrBind);
                    }
                }
            } else {
                // 상품 옵션 아이콘 테이블 지우기
                $this->db->bind_param_push($arrBind, 'i', $arrData['goodsNo']);
                $this->db->set_delete_db(DB_GOODS_OPTION_ICON, 'goodsNo = ?', $arrBind);
                unset($arrBind);
            }
        }

        if($arrData['soldOutFl'] == 'y') {
            $arrData['applyFl'] = 'n';
        }

        if ($arrData['mode'] == 'register') {
            $this->setGoodsUpdateEp($arrData['applyFl'], $arrData['goodsNo'], true);
        } else {
            $this->setGoodsUpdateEp($arrData['applyFl'], $arrData['goodsNo']);
        }

        // 페이스북 제품 피드 설정 저장
        $fbImageMode = Request::files()->toArray()['imageFb'];
        $facebookAd = \App::load('\\Component\\Marketing\\FacebookAd');
        $fbImageArr = $this->imageUploadFbFeed($fbImageMode, $arrData['imageStorage'], gd_isset($arrData['imageFbDB']), $arrData['goodsNo'], $arrData['mode']); // 페이스북 피드 이미지 값.
        $facebookAd->setFacebookGoodsFeedData($arrData['goodsNo'], $arrData['fbUseFl'], $fbImageArr);

        return $arrData['applyFl'];
    }

    /**
     * 상품 정보 저장 - 모바일앱
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoGoods_mobileapp_modify($arrData)
    {
        // 상품명, 상품번호 체크
        if (Validator::required(gd_isset($arrData['goodsNm'])) === false) {
            throw new \Exception(__('상품명은 필수 항목 입니다.'), 500);
        }
        if (Validator::required(gd_isset($arrData['goodsNo'])) === false) {
            throw new \Exception(__('상품번호는 필수 항목입니다.'), 500);
        }

        // 상품명 NFC로 변경
        $arrData['goodsNm'] = StringUtils::convertStringToNFC($arrData['goodsNm']);
        if ($arrData['goodsNmFl'] == 'e') {
            if (empty($arrData['goodsNmMain']) === false) {
                $arrData['goodsNmMain'] = StringUtils::convertStringToNFC($arrData['goodsNmMain']);
            }
            if (empty($arrData['goodsNmList']) === false) {
                $arrData['goodsNmList'] = StringUtils::convertStringToNFC($arrData['goodsNmList']);
            }
            if (empty($arrData['goodsNmDetail']) === false) {
                $arrData['goodsNmDetail'] = StringUtils::convertStringToNFC($arrData['goodsNmDetail']);
            }
            if (empty($arrData['goodsNmPartner']) === false) {
                $arrData['goodsNmPartner'] = StringUtils::convertStringToNFC($arrData['goodsNmPartner']);
            }
        }

        // 옵션 사용여부에 따른 재정의 및 배열 삭제
        if ($arrData['optionFl'] == 'y') {
            // 옵션의 존재여부에 따른 체크
            if (isset($arrData['optionY']) === false || isset($arrData['optionY']['optionName']) === false || isset($arrData['optionY']['optionValue']) === false) {
                throw new \Exception(__("옵션값을 확인해주세요."), 500);
            }
            unset($arrData['optionY']['optionNo']);
            foreach($arrData['optionY']['optionValueText'] as $k => $v) {
                $arrData['optionY']['optionNo'][] = $k+1;
                $tmpOptionText = explode(STR_DIVISION,$v);
                foreach($tmpOptionText as $k1 => $v1) {
                    $arrData['optionY']['optionValue'.($k1+1)][] = $v1;
                }
            }

            unset($arrData['optionY']['optionValueText']);

            $arrData['option'] = $arrData['optionY'];
            $arrData['optionDisplayFl'] = $arrData['option']['optionDisplayFl'];
            $arrData['optionName'] = implode(STR_DIVISION, $arrData['option']['optionName']);

            if (count($arrData['option']['optionCnt']) == 1) {
                $arrData['optionDisplayFl'] = 's';
            }

            unset($arrData['option']['optionDisplayFl']);
            unset($arrData['option']['optionName']);
        }
        else {
            $arrData['optionN']['stockCnt'][0] = $arrData['stockCnt'];
            $arrData['option'] = $arrData['optionN'];
        }
        unset($arrData['optionY']);
        unset($arrData['optionN']);


        $getOption = array();
        $getOption = $this->getGoodsOption($arrData['goodsNo'], $arrData); // 옵션/가격 정보
        if ($getOption) {
            foreach ($getOption as $k => $v) {
                $getOption[$k]['optionPrice'] = gd_money_format($v['optionPrice'], false);
            }
        }
        unset($getOption['optVal']); // 옵션값은 삭제

        // 옵션 가격 정보
        $compareOption = $this->db->get_compare_array_data($getOption, $arrData['option'], true, array_keys($arrData['option']), 'tableGoodsOption');

        // 전체 재고량
        if (isset($arrData['option']['stockCnt'])) {
            $arrData['totalStock'] = array_sum($arrData['option']['stockCnt']);
        }
        else {
            $arrData['totalStock'] = $arrData['stockCnt'];
        }

        // 운영자 상품 재고 수정 권한에 따른 처리
        $optionUpdateData = null;
        if (Session::get('manager.functionAuth.goodsStockModify') != 'y') {
            if ($compareOption == 'update') {
                $goodsOptionField = DBTableField::tableGoodsOption();
                foreach ($goodsOptionField as $oKey => $oVal) {
                    if ($oVal['val'] != 'stockCnt') {
                        $optionUpdateData[] = $oVal['val'];
                    }
                }
                unset($goodsOptionField);
            }
        }

        // 옵션 가격 정보
        $arrDataKey = array('goodsNo' => $arrData['goodsNo']);
        $optionLog = $this->db->set_compare_process(DB_GOODS_OPTION, $arrData['option'], $arrDataKey, $compareOption, $optionUpdateData);
        if ($optionLog) {
            $this->setGoodsLog('option(mobileapp)', $arrData['goodsNo'], $getOption, $optionLog);
        }
        unset($optionUpdateData);

        // 상품 수정 로그 저장
        $arrField = DBTableField::setTableField('tableGoods_mobileappModify', array_keys($arrData));
        $arrField = implode(',', $arrField);
        $getGoods = $this->getGoodsInfo($arrData['goodsNo'], $arrField);
        $result = [];
        $exceptField = array('modDt', 'regDt', 'applyDt', 'applyFl', 'applyMsg', 'delDt', 'applyType', 'hitCnt', 'orderCnt', 'reviewCnt');
        if (Session::get('manager.functionAuth.goodsStockModify') != 'y') {
            $expectField[] = 'totalStock';
        }

        // 기존 정보를 변경
        foreach ($getGoods as $key => $val) {
            if ($val != $arrData[$key] && !in_array($key, $exceptField)) {
                $result[$key] = $arrData[$key];
            }
        }

        if ($result) {
            $this->setGoodsLog('goods(mobileapp)', $arrData['goodsNo'], $getGoods, $result);
        }
        unset($arrField, $getGoods, $result);

        $arrExclude = [];
        // 운영자 상품 재고 수정 권한에 따른 처리
        if (Session::get('manager.functionAuth.goodsStockModify') != 'y') {
            $arrExclude[] = 'totalStock';
        }

        // 상품 정보 저장
        $arrBind = $this->db->get_binding(DBTableField::tableGoods_mobileappModify(), $arrData, 'update', null, $arrExclude);
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo']);
        $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
        unset($arrBind);

        if($this->goodsDivisionFl) {
            $arrExclude = array('purchaseNo', 'purchaseGoodsNm', 'goodsCd', 'cateCd', 'goodsSearchWord', 'goodsOpenDt', 'onlyAdultFl', 'onlyAdultDisplayFl', 'goodsAccess', 'goodsAccessGroup', 'goodsAccessDisplayFl', 'goodsColor', 'brandCd', 'makerNm', 'originNm', 'hscode', 'goodsModelNo', 'mileageFl', 'mileageGoods', 'addGoodsFl', 'optionTextFl', 'addGoodsFl', 'deliverySno', 'goodsIconCdPeriod', 'goodsIconCd', 'memo', 'orderCnt', 'orderGoodsCnt', 'hitCnt', 'cartCnt', 'wishCnt', 'reviewCnt', 'delFl', 'applyFl', 'applyType', 'applyDt', 'naverFl', 'daumFl', 'paycoFl');
            // 운영자 상품 재고 수정 권한에 따른 처리
            if (Session::get('manager.functionAuth.goodsStockModify') != 'y') {
                $arrExclude[] = 'totalStock';
            }

            //검색테이블 저장
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsSearch(), $arrData, 'update', null, $arrExclude);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo']);
            $this->db->set_update_db(DB_GOODS_SEARCH, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            unset($arrBind);
        }

        if($arrData['soldOutFl'] == 'y') {
            $arrData['applyFl'] = 'n';
        }
        else {
            $arrData['applyFl'] = 'y';
        }

        $this->setGoodsUpdateEp($arrData['applyFl'], $arrData['goodsNo']);
    }
    /**
     * 상품 옵션 추가노출 정보 저장
     *
     * @param array $arrFileData 저장할 _FILES[optionYIcon] _POST[optionYIcon]
     * @param array $arrData 기존 정보
     * @param array $arrOptionValue 옵션 값 정보
     * @param string $strImageStorage 저장소
     */
    protected function imageUploadIcon($arrFileData, $arrData, $arrOptionValue, $strImageStorage)
    {
        // --- 이미지 종류
        $tmpImage = gd_policy('goods.image');

        if(Request::post()->get('optionImageAddUrl') !='y' && $strImageStorage != 'url') {
            unset( $arrData['goodsImageText']);
        }

        // --- 저장이 아닌 URL로 직접 넣는 경우
        if ($strImageStorage == 'url') {

            // 기존 이미지 정보 삭제
            unset($arrData['iconImage']);
            unset($arrData['goodsImage']);

            // 기존 이미지 정보를 URL 이미지 정보로 대체
            $arrData['iconImage'] = $arrData['iconImageText'];
            $arrData['goodsImage'] = $arrData['goodsImageText'];

            // --- 저장 위치가 로컬 또는 외부인 경우
        } else {
            $iconType = array('icon' => 'iconImage', 'goods' => 'goodsImage');

            foreach ($iconType as $key => $val) {
                if (isset($arrFileData['name']) === false) {
                    continue;
                }
                if ($arrFileData['name'][$val]) {
                    foreach ($arrFileData['name'][$val] as $fKey => $fVal) {
                        foreach ($fVal as $vKey => $vVal) {

                            if (gd_file_uploadable($arrFileData, 'image', $val, $fKey, $vKey) === true) {
                                if ($val == 'iconImage') {
                                    $targetImageSize = GOODS_ICON_SIZE;
                                    $targetPreFix = 'addIcon_';
                                } else {
                                    $targetImageSize = $tmpImage['detail']['size1'];
                                    $targetPreFix = 'addGoods_';
                                }

                                $imageExt = strrchr($vVal, '.');

                                $newImageName = base64_encode($arrOptionValue[$fKey][$vKey]).$imageExt; // 이미지 공백 제거
                                $targetImageFile = $this->imagePath . $newImageName;
                                $thumbnailImageFile = $this->imagePath . $targetPreFix . $newImageName;
                                $tmpImageFile = $arrFileData['tmp_name'][$val][$fKey][$vKey];
                                $tmpInfo['optionNo'][$fKey][$vKey] = $fKey;
                                $tmpInfo['optionValue'][$fKey][$vKey] = $arrOptionValue[$fKey][$vKey];
                                $tmpInfo[$key . 'Image'][$fKey][$vKey] = $targetPreFix . $newImageName;

                                Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile, ['width' => $targetImageSize]);

                                // GD 이용한 썸네일 이미지 저장
                                if ($val == 'goodsImage') {
                                    // GD 이용한 썸네일 이미지 저장
                                    $thumbnailImageFile = $this->imagePath . PREFIX_GOODS_THUMBNAIL . $targetPreFix . $newImageName;
                                    Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile, ['width' => preg_replace('/[^0-9]/', '', PREFIX_GOODS_THUMBNAIL)]);
                                }

                            }

                            if(Request::post()->get('optionImageAddUrl') =='y' && empty($tmpInfo[$key . 'Image'][$fKey][$vKey]) === true && empty($arrData['goodsImage'][$fKey][$vKey]) === true  && empty($arrData['goodsImageText'][$fKey][$vKey]) === false && empty($arrData['optionImageDeleteFl'][$fKey][$vKey]) === true ) {
                            //if(Request::post()->get('optionImageAddUrl') =='y' && empty($tmpInfo[$key . 'Image'][$fKey][$vKey]) === true && empty($arrData['goodsImageText'][$fKey][$vKey]) === false && empty($arrData['optionImageDeleteFl'][$fKey][$vKey]) === true ) {
                                $tmpInfo[$key . 'Image'][$fKey][$vKey]  = $arrData['goodsImageText'][$fKey][$vKey];
                            }

                        }
                    }
                }
            }
        }

        unset($arrData['iconImageText']);
        unset($arrData['goodsImageText']);
        $arrData['optionValue'] = $arrOptionValue;
        $arrField = DBTableField::setTableField('tableGoodsOptionIcon', null, array('goodsNo'));
        $arrField[] = 'sno';

        // 색상표 코드 체크
        foreach ($arrData['optionValue'] as $key => $val) {
            foreach ($val as $cKey => $cVal) {
                foreach ($arrField as $fVal) {
                    if (gd_isset($tmpInfo[$fVal][$key][$cKey])) {
                        $arrData[$fVal][$key][$cKey] = $tmpInfo[$fVal][$key][$cKey];
                    }
                }
                if (!gd_isset($arrData['goodsImage'][$key][$cKey])) {

                    foreach ($arrField as $fVal) {
                        unset($arrData[$fVal][$key][$cKey]);
                    }
                }
            }
        }

        foreach ($arrData['optionValue'] as $key => $val) {
            foreach ($val as $cKey => $cVal) {
                foreach ($arrField as $fVal) {
                    $iconInfo[$fVal][] = gd_isset($arrData[$fVal][$key][$cKey]);
                }
            }
        }
        unset($arrData);



        // 기본 상품의 이미지 정보
        $getImage = $this->getGoodsOptionIcon($this->goodsNo);


        // 기존 이미지 정보와 새로운 이미지 정보를 비교
        $getImageCompare = $this->db->get_compare_array_data($getImage, gd_isset($iconInfo));


        // 공통 키값
        $arrDataKey = array('goodsNo' => $this->goodsNo);

        // 이미지 디비 처리
        $this->db->set_compare_process(DB_GOODS_OPTION_ICON, gd_isset($iconInfo), $arrDataKey, $getImageCompare);
    }

    /**
     * 상품 옵션 이미지 정보 저장
     *
     * @param array $arrFileData 저장할 _FILES[optionYIcon] _POST[optionYIcon]
     * @param array $arrData 기존 정보
     * @param array $arrOptionValue 옵션 값 정보
     * @param string $strImageStorage 저장소
     */
    protected function imageUploadIconTemp($arrFileData, $arrData, $arrOptionValue)
    {
        // --- 이미지 종류
        $tmpImage = gd_policy('goods.image');

        if(Request::post()->get('optionImageAddUrl') !='y' && $strImageStorage != 'url') {
            unset( $arrData['goodsImageText']);
        }

        // --- 저장이 아닌 URL로 직접 넣는 경우
        if ($strImageStorage == 'url') {

            // 기존 이미지 정보 삭제
            unset($arrData['iconImage']);
            unset($arrData['goodsImage']);

            // 기존 이미지 정보를 URL 이미지 정보로 대체
            $arrData['iconImage'] = $arrData['iconImageText'];
            $arrData['goodsImage'] = $arrData['goodsImageText'];

            // --- 저장 위치가 로컬 또는 외부인 경우
        } else {


            $iconType = array('icon' => 'iconImage', 'goods' => 'goodsImage');


            foreach ($iconType as $key => $val) {
                if (isset($arrFileData['name']) === false) {
                    continue;
                }
                if ($arrFileData['name'][$val]) {
                    foreach ($arrFileData['name'][$val] as $fKey => $fVal) {
                        foreach ($fVal as $vKey => $vVal) {

                            if (gd_file_uploadable($arrFileData, 'image', $val, $fKey, $vKey) === true) {
                                if ($val == 'iconImage') {
                                    $targetImageSize = GOODS_ICON_SIZE;
                                    $targetPreFix = 'addIcon_';
                                } else {
                                    $targetImageSize = $tmpImage['detail']['size1'];
                                    $targetPreFix = 'addGoods_';
                                }

                                $imageExt = strrchr($vVal, '.');

                                $newImageName = base64_encode($arrOptionValue[$fKey][$vKey]).$imageExt; // 이미지 공백 제거
                                $targetImageFile = $this->imagePath . $newImageName;
                                $thumbnailImageFile = $this->imagePath . $targetPreFix . $newImageName;
                                $tmpImageFile = $arrFileData['tmp_name'][$val][$fKey][$vKey];
                                $tmpInfo['optionNo'][$fKey][$vKey] = $fKey;
                                $tmpInfo['optionValue'][$fKey][$vKey] = $arrOptionValue[$fKey][$vKey];
                                $tmpInfo[$key . 'Image'][$fKey][$vKey] = $targetPreFix . $newImageName;

                                Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile, ['width' => $targetImageSize]);

                                // GD 이용한 썸네일 이미지 저장
                                if ($val == 'goodsImage') {
                                    // GD 이용한 썸네일 이미지 저장
                                    $thumbnailImageFile = $this->imagePath . PREFIX_GOODS_THUMBNAIL . $targetPreFix . $newImageName;
                                    Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile, ['width' => preg_replace('/[^0-9]/', '', PREFIX_GOODS_THUMBNAIL)]);
                                }

                            }

                            if(Request::post()->get('optionImageAddUrl') =='y' && empty($tmpInfo[$key . 'Image'][$fKey][$vKey]) === true && empty($arrData['goodsImage'][$fKey][$vKey]) === true  && empty($arrData['goodsImageText'][$fKey][$vKey]) === false && empty($arrData['optionImageDeleteFl'][$fKey][$vKey]) === true ) {
                                $tmpInfo[$key . 'Image'][$fKey][$vKey]  = $arrData['goodsImageText'][$fKey][$vKey];
                            }

                        }
                    }
                }
            }
        }

        unset($arrData['iconImageText']);
        unset($arrData['goodsImageText']);
        $arrData['optionValue'] = $arrOptionValue;
        $arrField = DBTableField::setTableField('tableGoodsOptionIcon', null, array('goodsNo'));
        $arrField[] = 'sno';

        // 기본 상품의 이미지 정보
        $getImage = $this->getGoodsOptionIcon($this->goodsNo);


        // 기존 이미지 정보와 새로운 이미지 정보를 비교
        $getImageCompare = $this->db->get_compare_array_data($getImage, gd_isset($iconInfo));

        // 공통 키값
        $arrDataKey = array('goodsNo' => $this->goodsNo);

        // 이미지 디비 처리
        $this->db->set_compare_process(DB_GOODS_OPTION_ICON, gd_isset($iconInfo), $arrDataKey, $getImageCompare);
    }

    /**
     * 상품 이미지 저장
     *
     * @param array $arrFileData 저장할 _FILES[image]
     * @param string $strImageStorage 저장소
     * @param array $arrImageSize 이미지 리사이즈 정보
     * @param array $arrImageResize 이미지 리사이즈 사용여부
     * @param array $imageInfo 수정의 경우 기존 이미지 정보
     */
    public function imageUploadGoods($arrFileData, $strImageStorage, $arrImageSize, $arrImageResize, $imageInfo,$goodsNo, $mode = null)
    {

        // --- 이미지 종류
        $tmpImage = gd_policy('goods.image');

        // 설정된 상품 이미지 사이즈가 없는 경우, 넘어온 이미지 사이즈가 없는 경우 리턴
        if (empty($tmpImage) === true || empty($arrImageSize) === true) {
            return;
        }

        // 각 이미지별 사이즈 추출
        foreach ($tmpImage as $key => $val) {
            $image['file'][] = $key;
            $image['addKey'][$key] = $tmpImage[$key]['addKey'];
            // if (gd_isset($arrImageResize[$key]) == 'y') {
            $image['size'][$key] = $arrImageSize[$key];
            // } else {
            // $image['size'][$key] = 0;
            // }
            $image['resize'][$key] = gd_isset($arrImageResize[$key]);
        }

        //썸네일이미지생성관련
        $thumbImageSize = $tmpImage['list']['size1'];
        $thumbImageHeightSize = $tmpImage['list']['hsize1'];

        foreach($imageInfo['imageCode'] as $k => $v) {
            if(is_array($v)) {
                foreach($v as $k1 => $v1) {
                    $imageInfo['imageCode'][$k.$k1] = $v1;
                }
            }
        }

        $storage = Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage);
        // --- 저장이 아닌 URL로 직접 넣는 경우
        if ($strImageStorage == 'url') {
            foreach ($image['file'] as $val) {
                $i = 0;
                foreach ($arrFileData['imageUrl' . ucfirst($val)] as $fKey => $fVal) {
                    if($fVal) {
                        $size = explode(INT_DIVISION, $image['size'][$val]);
                        $tmpInfo['sno'][] = gd_isset($imageInfo['imageCode'][$val.$i]);
                        $tmpInfo['imageNo'][] = $i;
                        $tmpInfo['imageSize'][] = $size[0];
                        $tmpInfo['imageHeightSize'][] = $size[1];
                        $tmpInfo['imageKind'][] = $val;
                        $tmpInfo['imageName'][] = $fVal;
                        $tmpInfo['imageRealSize'][] = '';
                        $i++;
                    }
                }
            }

            // --- 저장 위치가 로컬 또는 외부인 경우
        } else {
            foreach ($image['file'] as $val) {

                // 이미지 리사이즈 인경우
                if ($image['resize'][$val] == 'y') {
                    $i = 0;
                    if (empty($arrFileData['name']['imageOriginal']) === false) {

                        foreach ($arrFileData['name']['imageOriginal'] as $fKey => $fVal) {
                            if (empty($fVal) || ($image['addKey'][$val] == 'n' && $fKey != 0)) { // 화일이 없거나. addKey가 n 인 경우 imageOriginal 첫번째 이외의 배열
                                continue;
                            }

                            if (gd_file_uploadable($arrFileData, 'image', 'imageOriginal', $fKey) === true) {
                                $size = explode(INT_DIVISION, $image['size'][$val]);
                                $imageExt = strrchr($arrFileData['name']['imageOriginal'][$fKey], '.');
                                //$newImageName = str_replace(' ', '', trim(substr($arrFileData['name']['imageOriginal'][$fKey], 0, -strlen($imageExt)))) . '_' . $val . $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기
                                $saveImageName = $goodsNo . '_' . $val .'_'.$i.rand(1,100). $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기
                                $targetImageFile = $this->imagePath . $saveImageName;
                                $tmpImageFile = $arrFileData['tmp_name']['imageOriginal'][$fKey];
                                $tmpInfo['sno'][] = gd_isset($imageInfo['imageCode'][$val.$i]);
                                $tmpInfo['imageNo'][] = $i;
                                $tmpInfo['imageSize'][] = $size[0];
                                $tmpInfo['imageHeightSize'][] = $size[1];
                                $tmpInfo['imageKind'][] = $val;
                                $tmpInfo['imageName'][] = $saveImageName;
                                $tmpInfo['imageRealSize'][] = implode(',', array());

                                // GD 이용한 화일 리사이징
                                $storage->upload($tmpImageFile, $targetImageFile, ['width' => $image['size'][$val], 'height' => $size[1]]);

                                // GD 이용한 썸네일 이미지 저장
                                $thumbnailImageFile = $this->imagePath . PREFIX_GOODS_THUMBNAIL . $saveImageName;
                                $storage->upload($tmpImageFile, $thumbnailImageFile, ['width' => $thumbImageSize, 'height' => $thumbImageHeightSize]);
                            }
                            $i++;
                        }
                    }
                    // 이미지 직접 올리는 경우
                } else {

                    $i = 0;
                    $imageNo = 0;

                    if (empty($arrFileData['name']['image' . ucfirst($val)]) === false) {
                        foreach ($arrFileData['name']['image' . ucfirst($val)] as $fKey => $fVal) {
                            if (gd_file_uploadable($arrFileData, 'image', 'image' . ucfirst($val), $fKey) === true) {
                                $size = explode(INT_DIVISION, $image['size'][$val]);
                                $imageExt = strrchr($fVal, '.');
                                //$newImageName = str_replace(' ', '', trim(substr($fVal, 0, -strlen($imageExt)))) . '_' . $val . $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기
                                $saveImageName = $goodsNo . '_' . $val .'_'.$i.rand(1,100). $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기

                                $targetImageFile = $this->imagePath . $saveImageName;
                                $tmpImageFile = $arrFileData['tmp_name']['image' . ucfirst($val)][$fKey];
                                list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
                                $tmpInfo['sno'][] = gd_isset($imageInfo['imageCode'][$val.$i]);
                                $tmpInfo['imageNo'][] = $i;
                                $tmpInfo['imageSize'][] = $size[0];
                                $tmpInfo['imageHeightSize'][] = $size[1];
                                $tmpInfo['imageKind'][] = $val;
                                $tmpInfo['imageName'][] = $saveImageName;
                                $tmpInfo['imageRealSize'][] = implode(',', $tmpSize);
                                // 이미지 저장
                                $storage->upload($tmpImageFile, $targetImageFile);

                                // GD 이용한 썸네일 이미지 저장
                                $thumbnailImageFile = $this->imagePath . PREFIX_GOODS_THUMBNAIL . $saveImageName;
                                $storage->upload($tmpImageFile, $thumbnailImageFile, ['width' => $image['size'][$val]]);
                                $imageNo++;
                            } else {

                                if($imageInfo['imageCode'][$val.$i] && empty($imageInfo['imageUrlFl'][$val.$i]) === true && empty($imageInfo['imageDelFl'][$val.$i]) === true) {

                                    $imageIndex = array_search($imageInfo['imageCode'][$val.$i], $imageInfo['sno']);
                                    $tmpInfo['sno'][] = $imageInfo['sno'][$imageIndex];
                                    $tmpInfo['imageNo'][] = $imageNo;
                                    $tmpInfo['imageSize'][] = $imageInfo['imageSize'][$imageIndex];
                                    $tmpInfo['imageHeightSize'][] = gd_isset($imageInfo['imageHeightSize'][$imageIndex],0);
                                    $tmpInfo['imageKind'][] = $imageInfo['imageKind'][$imageIndex];
                                    $tmpInfo['imageName'][] = $imageInfo['imageName'][$imageIndex];
                                    $tmpInfo['imageRealSize'][] = $imageInfo['imageRealSize'][$imageIndex];
                                    $imageNo++;
                                }
                            }

                            $i++;
                        }
                        if(Request::post()->get('imageAddUrl') =='y') {
                            if(Request::post()->get('image')['image'.ucfirst($val)]) {

                                if(!in_array($val,['detail','magnify']) && in_array($val,$tmpInfo['imageKind'])) {
                                    continue;
                                }

                                if(!in_array($val,$tmpInfo['imageKind']) && (!in_array($val,['detail','magnify']) || ($i == 1 && in_array($val,['detail','magnify']) && empty($imageInfo['imageUrlFl'][$val.'0']) === false))) {
                                    $i = 0;
                                }

                                $urlImageTmp = Request::post()->get('image')['image'.ucfirst($val)];

                                foreach ($urlImageTmp as $fKey => $fVal) {
                                    if (strtolower(substr($fVal,0,4)) =='http' && empty($imageInfo['imageUrlDelFl'][$val.$i]) === true) {
                                        $size = explode(INT_DIVISION, $image['size'][$val]);
                                        $tmpInfo['sno'][] =  gd_isset($imageInfo['imageCode'][$val.$i]);
                                        $tmpInfo['imageNo'][] = $imageNo;
                                        $tmpInfo['imageSize'][] = $size[0];
                                        $tmpInfo['imageHeightSize'][] = gd_isset($size[1],0);
                                        $tmpInfo['imageKind'][] = $val;
                                        $tmpInfo['imageName'][] = $fVal;
                                        $tmpInfo['imageRealSize'][] = '';
                                        $imageNo++;
                                    }
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 기본 상품의 이미지 정보
        $getImage = StringUtils::trimValue($this->getGoodsImage($this->goodsNo));
        if(!$getImage) unset($tmpInfo['sno']);

        // 기존 이미지 정보와 새로운 이미지 정보를 비교
        //imageNo를 빼고 기존과 새로운 이미지 비교를 하기위한 작업
        $_tmpInfo = $tmpInfo;
        $_getImage = $getImage;
        unset($_tmpInfo['imageNo']);
        foreach($_getImage as &$val) {
            unset($val['imageNo']);
        }
        $_getImageCompare = $this->db->get_compare_array_data($_getImage, gd_isset($_tmpInfo));
        if ($_getImage &&  $strImageStorage != 'url' ) {
            foreach ($_getImage as $k => $v) {
                if ($_getImageCompare[$v['sno']] == 'update' || $_getImageCompare[$v['sno']] == 'delete') {
                    if(in_array($v['imageName'],$tmpInfo['imageName']) === false || empty($tmpInfo) === true) {
                        $storage->delete($this->imagePath . PREFIX_GOODS_THUMBNAIL . $v['imageName']);
                        $storage->delete($this->imagePath . $v['imageName']);
                    }
                }
            }
        }

        $getImageCompare = $this->db->get_compare_array_data($getImage, gd_isset($tmpInfo));
        // 공통 키값
        $arrDataKey = array('goodsNo' => $this->goodsNo);

        // 이미지 디비 처리
        $goodsImageLog = $this->db->set_compare_process(DB_GOODS_IMAGE, gd_isset($tmpInfo), $arrDataKey, $getImageCompare);
        if($strImageStorage == 'local'  && empty($tmpInfo['imageName']) === false && strpos($this->imagePath,  $this->goodsNo) !== false ) {
            $goodsImageDir = UserFilePath::data('goods', $this->imagePath);
            if ($openDir = opendir($goodsImageDir)) {
                while (($goodsImageNm = readdir($openDir)) !== false) {
                    if (!in_array(str_replace(PREFIX_GOODS_THUMBNAIL,'',$goodsImageNm),$tmpInfo['imageName']) && substr(strtolower(str_replace(PREFIX_GOODS_THUMBNAIL,'',$goodsImageNm)),0,3) !='add' && substr(strtolower(str_replace(PREFIX_GOODS_THUMBNAIL,'',$goodsImageNm)),0,3) !='fbg') {
                        $storage->delete($this->imagePath . PREFIX_GOODS_THUMBNAIL .$goodsImageNm);
                        $storage->delete($this->imagePath . $goodsImageNm);
                    }
                }
            }
        }
        if ($goodsImageLog && $mode == 'modify') {
            $this->setGoodsLog('image', $this->goodsNo, $getImage, $goodsImageLog);
        }

    }

    /**
     * 모바일앱 - 상품 이미지 저장
     *
     * @param array $arrData
     */
    public function mobileapp_imageUploadGoods($arrData)
    {
        if(trim($arrData['mobileapp_imageOriginal']) === ''){
            return false;
        }

        //저장소
        $strImageStorage = $arrData['imageStorage'];
        $arrImageSize = $arrData['imageSize'];
        $arrImageResize = $arrData['imageResize'];
        $imageInfo = $arrData['imageDB'];
        $goodsNo = $arrData['goodsNo'];

        // --- 이미지 종류
        $tmpImage = gd_policy('goods.image');

        // 설정된 상품 이미지 사이즈가 없는 경우, 넘어온 이미지 사이즈가 없는 경우 리턴
        if (empty($tmpImage) === true) {
            return;
        }

        // 각 이미지별 사이즈 추출
        foreach ($tmpImage as $key => $val) {
            $image['file'][] = $key;
            $image['addKey'][$key] = $tmpImage[$key]['addKey'];
            $image['size'][$key] = $arrImageSize[$key];
            $image['resize'][$key] = gd_isset($arrImageResize[$key]);
        }

        //썸네일이미지생성관련
        $thumbImageSize = $tmpImage['list']['size1'];
        $thumbImageHeightSize = $tmpImage['list']['hsize1'];

        foreach($imageInfo['imageCode'] as $k => $v) {
            if(is_array($v)) {
                foreach($v as $k1 => $v1) {
                    $imageInfo['imageCode'][$k.$k1] = $v1;
                }
            }
        }

        $storage = Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage);
        $tempMobileappPath = Request::server()->get('DOCUMENT_ROOT').'/data/mobileapp/'.$arrData['mobileapp_imageOriginal'];
        foreach ($image['file'] as $val) {
            foreach (glob($tempMobileappPath) as $oldFile) {
                $size = explode(INT_DIVISION, $image['size'][$val]);
                $imageExt = strrchr($oldFile, '.');

                $saveImageName = $goodsNo . '_' . $val . '_' . $i . rand(1, 100) . $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기
                $targetImageFile = $this->imagePath . $saveImageName;

                $tmpInfo['sno'][] = gd_isset($imageInfo['imageCode'][$val . $i]);
                $tmpInfo['imageNo'][] = $i;
                $tmpInfo['imageSize'][] = $size[0];
                $tmpInfo['imageHeightSize'][] = $size[1];
                $tmpInfo['imageKind'][] = $val;
                $tmpInfo['imageName'][] = $saveImageName;
                $tmpInfo['imageRealSize'][] = implode(',', array());

                // GD 이용한 화일 리사이징
                $storage->upload($oldFile, $targetImageFile, ['width' => $image['size'][$val], 'height' => $size[1]]);

                // GD 이용한 썸네일 이미지 저장
                $thumbnailImageFile = $this->imagePath . PREFIX_GOODS_THUMBNAIL . $saveImageName;
                $storage->upload($oldFile, $thumbnailImageFile, ['width' => $thumbImageSize, 'height' => $thumbImageHeightSize]);
            }
        }

        // 공통 키값
        $arrDataKey = array('goodsNo' => $this->goodsNo);

        // 이미지 디비 처리
        $this->db->set_compare_process(DB_GOODS_IMAGE, gd_isset($tmpInfo), $arrDataKey, array());

        @unlink($tempMobileappPath);
    }

    /**
     * 모바일앱 - 상품설명 이미지 저장
     *
     * @param string $goodsDescription
     */
    public function mobileapp_imageUploadDescription(&$goodsDescription)
    {
        $device_uid = \Cookie::get('device_uid');
        $tempMobileappPath = Request::server()->get('DOCUMENT_ROOT').'/data/mobileapp/'.$device_uid.'_mobileapp_goodsDescription*';
        $editorPath = PATH_EDITOR_RELATIVE . '/goods/';
        $uploadDir = Request::server()->get('DOCUMENT_ROOT') . $editorPath;
        if (!is_dir($uploadDir)) {
            $old = umask(0);
            mkdir($uploadDir, 0777, true);
            umask($old);
        }
        foreach (glob($tempMobileappPath) as $oldFile) {
            $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
            $newFileName = substr(md5(microtime()), 0, 16) . '_' . $key . '.' . $ext;
            $newFile = $uploadDir . $newFileName;

            if(rename($oldFile, $newFile)){
                @chmod($newFile, 0707);
                if(file_exists($newFile)){
                    $goodsDescription .= '<p><img src="'.$editorPath.$newFileName.'" title="'.$newFileName.'" class="js-smart-img"><br style="clear:both;"></p>';
                    @unlink($oldFile);
                }
            }
        }
    }

    /**
     * 1시간이 지난 임시 이미지 삭제
     */
    public function mobileapp_removeTempImage()
    {
        $maxDate = strtotime('-1 hours');
        $tempImagePath = Request::server()->get('DOCUMENT_ROOT').'/data/mobileapp/';
        foreach (glob($tempImagePath) as $file) {
            $fileDate = filemtime($file);

            if($maxDate > $fileDate){
                @unlink($file);
            }
        }
    }

    /**
     * 페이스북 상품 피드 이미지 저장.
     * @param array $arrFileData 저장할 파일 이미지
     * @param string $strImageStorage 저장소
     * @param array @imageInfo 수정의 경우 기존 이미지 정보
     */
    public function imageUploadFbFeed($arrFileData, $strImageStorage, $imageInfo, $goodsNo, $mode = null)
    {
        $storage = Storage::disk(Storage::PATH_CODE_GOODS, $strImageStorage);

        //URL 직접 입력
        if($strImageStorage == 'url') {
            $imageTmp = Request::post()->get('imageFb')['imageFbGoodsURL'];
            if($imageTmp) {
                foreach($imageTmp as $key => $val) {
                    if(strtolower(substr($val,0,4)) =='http' && empty($imageInfo['imageUrlDelFl'][$val]) === true) {
                        $tmpInfo[] = $val;
                    }
                }
            }
        } else { // 저장위치가 로컬 또는 url 입력으로 외부인 경우 && 이미지 직접 업로드
            $i = 0;
            if (empty($arrFileData['name']['imageFbGoods']) === false) {
                foreach ($arrFileData['name']['imageFbGoods'] as $fKey => $fVal) {
                    if (gd_file_uploadable($arrFileData, 'image', 'imageFbGoods', $fKey) === true) { // 새로 입력된 이미지 저장.
                        $imageExt = strrchr($fVal, '.');
                        $saveImageName = 'fbGoods_'.$i.rand(1,100). $imageExt;
                        $tmpInfo[] = $saveImageName;
                        $targetImageFile = $this->imagePath . $saveImageName;
                        $tmpImageFile = $arrFileData['tmp_name']['imageFbGoods'][$fKey];

                        // 이미지 저장
                        $storage->upload($tmpImageFile, $targetImageFile);

                        //썸네일 이미지 저장
                        $thumbnailImageFile = $this->imagePath . PREFIX_GOODS_THUMBNAIL . $saveImageName;
                        $storage->upload($tmpImageFile, $thumbnailImageFile, ['width' => 100, 'height' => 100]);

                    } else { // 기존값.
                        $imgName = gd_isset($imageInfo['imageName'][$i], '');
                        if($imageInfo['imageDelFl'][$imgName] != 'y' && (empty($imgName)===false)){// 삭제할 데이터가 아닌 값만 입력.
                            $tmpInfo[] = $imgName;
                        }
                    }
                    $i++;
                }
                if(Request::post()->get('imageFbAddUrl') =='y') { // URL 직접 입력.
                    if (Request::post()->get('imageFb')['imageFbGoods']) {
                        $urlImageTmp = Request::post()->get('imageFb')['imageFbGoods'];
                        foreach($urlImageTmp as $key => $val) {
                            if(strtolower(substr($val,0,4)) =='http' && empty($imageInfo['imageUrlDelFl'][$val]) === true) {
                                $tmpInfo[] = $val;
                            }
                        }
                    }
                }
            }
        }
        if(empty($imageInfo['imageDelFl']) === false){
            foreach($imageInfo['imageDelFl'] as $key => $val) {
                $storage->delete($this->imagePath . PREFIX_GOODS_THUMBNAIL . $key);
                $storage->delete($this->imagePath . $key);
            }
        }
        $fbImageInfo = "";
        $arrSize = sizeof($tmpInfo)-1;
        $j = 0;

        if(empty($tmpInfo) === false) {
            foreach ($tmpInfo as $key => $val) {
                if($j == $arrSize){
                    $fbImageInfo .= $val;
                }else {
                    $fbImageInfo .= $val . STR_DIVISION;
                }
                $j++;
            }
        }
        return $fbImageInfo;
    }

    /**
     * 상품 복사
     *
     * @param integer $goodsNo 상품번호
     */
    public function setCopyGoods($goodsNo)
    {
        // 새로운 상품 번호
        $newGoodsNo = $this->getNewGoodsno();

        // 이미지 저장소 및 이미지 경로 정보
        $strWhere = 'g.goodsNo = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $goodsNo);
        $this->db->strWhere = $strWhere;
        $data = $this->getGoodsInfo(null, 'g.goodsNm, g.imageStorage, g.imagePath', $this->arrBind);
        $newImagePath = DIR_GOODS_IMAGE . $newGoodsNo . '/';

        // 상품 관련 테이블 복사, 해당 테이블 순서 변경하지 마세요
        $arrGoodsTable[] = DB_SEO_TAG; // SEO 태그
        $arrGoodsTable[] = DB_GOODS; // 상품 기본 정보
        if($this->goodsDivisionFl) $arrGoodsTable[] = DB_GOODS_SEARCH; // 상품 기본 정보
        $arrGoodsTable[] = DB_GOODS_GLOBAL; // 상품 글로벌 관련 정보
        $arrGoodsTable[] = DB_GOODS_ADD_INFO; // 상품 추가 정보
        $arrGoodsTable[] = DB_GOODS_LINK_CATEGORY; // 상품 카테고리 연결 및 정렬
        $arrGoodsTable[] = DB_GOODS_LINK_BRAND; // 상품 브랜드 연결 및 정렬
        $arrGoodsTable[] = DB_GOODS_OPTION; // 상품 옵션
        $arrGoodsTable[] = DB_GOODS_OPTION_ICON; // 상품 옵션 추가 노출 (칼라코드,아이콘,상품이미지)
        $arrGoodsTable[] = DB_GOODS_OPTION_TEXT; // 상품 텍스트 옵션
        $arrGoodsTable[] = DB_GOODS_IMAGE; // 상품 이미지
        $arrGoodsTable[] = DB_GOODS_ICON; // 상품 아이콘


        $seoInsertId = '';
        foreach ($arrGoodsTable as $goodsTableNm) {
            // 등록된 필드명 로드
            $tmp = explode('_', $goodsTableNm);
            $functionNm = StringUtils::strToCamel('table_' . preg_replace('/[A-Z]/', '_' . strtolower('\\0'), $tmp[1]));
            if ($functionNm == 'TableGoods') {
                $fieldData = DBTableField::setTableField($functionNm, null, array('goodsNo', 'regDt', 'modDt', 'orderCnt', 'orderGoodsCnt', 'hitCnt', 'cartCnt', 'wishCnt', 'reviewCnt', 'plusReviewCnt'));
                //$addField = ',imagePath';
                //$addData = ',\'' . $newImagePath . '\'';
            } else if($functionNm == 'TableGoodsSearch') {
                $fieldData = DBTableField::setTableField($functionNm, null, array('goodsNo', 'regDt', 'modDt', 'orderCnt', 'orderGoodsCnt', 'hitCnt', 'cartCnt', 'wishCnt', 'reviewCnt', 'plusReviewCnt'));
            } else {
                if ($functionNm == 'TableSeoTag') {
                    $excludeField = ['sno', 'regDt', 'modDt'];
                } else {
                    $excludeField = ['goodsNo', 'regDt', 'modDt'];
                }
                $fieldData = DBTableField::setTableField($functionNm, null, $excludeField);
                $addField = '';
                $addData = '';
            }
            $insertFieldData = $fieldData;

            if ($functionNm == 'TableSeoTag') {
                $pageCodeKey = array_search('pageCode', $insertFieldData);
                $insertFieldData[$pageCodeKey] = '\'' . $newGoodsNo . '\'';
                $strSQL = 'INSERT INTO ' . $goodsTableNm . ' (regDt, ' . implode(', ', $fieldData) . $addField . ') SELECT now(),' . implode(', ', $insertFieldData) . $addData . ' FROM ' . $goodsTableNm . ' WHERE path = \'goods/goods_view.php\' AND pageCode = ' . $goodsNo;
            } else {
                if ($functionNm == 'TableGoods') {
                    $seoTagSnoKey = array_search('seoTagSno', $insertFieldData);
                    $insertFieldData[$seoTagSnoKey] = $seoInsertId;
                    unset($seoInsertId);
                }
                $strSQL = 'INSERT INTO ' . $goodsTableNm . ' (regDt,goodsNo, ' . implode(', ', $fieldData) . $addField . ') SELECT now(),\'' . $newGoodsNo . '\',' . implode(', ', $insertFieldData) . $addData . ' FROM ' . $goodsTableNm . ' WHERE goodsNo = ' . $goodsNo;
            }

            $this->db->query($strSQL);
            $seoInsertId = $this->db->insert_id();
        }

        $strUpdateSQL = "UPDATE " . DB_GOODS . " SET imagePath = '" . $newImagePath . "' WHERE goodsNo = '" . $newGoodsNo . "' ";
        $this->db->query($strUpdateSQL);

        unset($this->arrBind);


        if (Session::get('manager.isProvider')) {
            $applyFl = $this->setGoodsApplyUpdate($newGoodsNo, 'register');
        }

        //상품 헤택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefit->goodsBenefitCopy($goodsNo,$newGoodsNo);

        // 전체 로그를 저장합니다.
        $addLogData = $goodsNo . ' -> ' . $newGoodsNo . ' 상품 복사' . chr(10);
        LogHandler::wholeLog('goods', null, 'copy', $newGoodsNo, $data['goodsNm'], $addLogData);

        // --- 이미지 복사 처리
        if($data['imageStorage'] !='url') {
            Storage::copy(Storage::PATH_CODE_GOODS, $data['imageStorage'], $data['imagePath'], $data['imageStorage'], $newImagePath);
            // --- 상품 이미지 복사시 함께 복사된 페이스북 피드 이미지 제거 처리
            $facebookAd = \App::load('\\Component\\Marketing\\FacebookAd');
            $fbImage = $facebookAd->getFaceBookGoodsImage($goodsNo);
            $storage = Storage::disk(Storage::PATH_CODE_GOODS, $data['imageStorage']);
            if($fbImage) {
                foreach ($fbImage as $key => $val) {
                    $storage->delete($newImagePath . PREFIX_GOODS_THUMBNAIL . $val);
                    $storage->delete($newImagePath . $val);
                }
            }
        }
        return $newGoodsNo;
    }

    /**
     * 상품 완전 삭제
     *
     * @param integer $goodsNo 상품번호
     */
    public function setDeleteGoods($goodsNo)
    {
        // 이미지 저장소 및 이미지 경로 정보
        $strWhere = 'g.goodsNo = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $goodsNo);
        $this->db->strWhere = $strWhere;
        $data = $this->getGoodsInfo(null, 'g.goodsNm, g.imageStorage, g.imagePath, g.goodsDescription', $this->arrBind);

        // 상품 관련 테이블 삭제
        $arrGoodsTable[] = DB_GOODS_ADD_INFO; // 상품 추가 정보
        $arrGoodsTable[] = DB_GOODS_LINK_BRAND; // 상품 브랜드 연결 및 정렬
        $arrGoodsTable[] = DB_GOODS_LINK_CATEGORY; // 상품 카테고리 연결 및 정렬
        $arrGoodsTable[] = DB_GOODS_OPTION; // 상품 옵션
        $arrGoodsTable[] = DB_GOODS_OPTION_ICON; // 상품 옵션 추가 노출 (칼라코드,아이콘,상품이미지)
        $arrGoodsTable[] = DB_GOODS_OPTION_TEXT; // 상품 텍스트 옵션
        $arrGoodsTable[] = DB_GOODS_IMAGE; // 상품 이미지
        $arrGoodsTable[] = DB_SEO_TAG; // SEO 태그
        $arrGoodsTable[] = DB_GOODS; // 상품 기본 정보
        $arrGoodsTable[] = DB_GOODS_GLOBAL; // global 상품 기본 정보
        $arrGoodsTable[] = DB_GOODS_ICON; // 상품 아이콘
        $arrGoodsTable[] = DB_CART; // 장바구니
        if($this->goodsDivisionFl) $arrGoodsTable[] = DB_GOODS_SEARCH; // 상품 검색 정보

        foreach ($arrGoodsTable as $goodsTableNm) {
            if ($goodsTableNm == DB_SEO_TAG) {
                $this->db->set_delete_db($goodsTableNm, 'pageCode = ?', $this->arrBind);
            } else {
                $this->db->set_delete_db($goodsTableNm, 'goodsNo = ?', $this->arrBind);
            }
        }
        unset($this->arrBind);

        // 전체 로그를 저장합니다.
        LogHandler::wholeLog('goods', null, 'delete', $goodsNo, $data['goodsNm']);

        // --- 이미지 삭제 처리
        //        debug($data['imagePath']);
        //        exit;
        if($data['imageStorage'] =='local' && $data['imagePath']) {
            Storage::disk(Storage::PATH_CODE_GOODS, $data['imageStorage'])->deleteDir($data['imagePath']);
        }
    }


    /**
     * 일괄상품 수정에서 상품 승인 관련 상품 테이블 업데이트
     *
     */
    public function setGoodsApplyUpdate($goodsNo, $mode, $modDtUse = null)
    {
        //공급사이면서 자동승인이 아닌경우 상품 승인신청 처리
        if (Session::get('manager.isProvider') && (($mode == 'modify' && Session::get('manager.scmPermissionModify') == 'c') || ($mode == 'register' && Session::get('manager.scmPermissionInsert') == 'c'))) {
            $applyData['applyFl'] = 'a';
            $applyData['applyDt'] = date('Y-m-d H:i:s');
            $applyData['applyType'] = strtolower(substr($mode, 0, 1));


        } else  $applyData['applyFl'] = 'y';


        $arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoods', array_keys($applyData)), $applyData, 'update');

        if (is_array($goodsNo)) {
            $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "')";

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS, $arrBind['param'], $strWhere, $arrBind['bind']);
            if($this->goodsDivisionFl) {
                //상품수정일 변경유무 추가
                if ($modDtUse == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS_SEARCH, $arrBind['param'], $strWhere, $arrBind['bind']);
            }
        } else {
            $this->db->bind_param_push($arrBind['bind'], 's', $goodsNo);

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            if($this->goodsDivisionFl) {
                //상품수정일 변경유무 추가
                if ($modDtUse == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS_SEARCH, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            }
        }
        unset($arrBind);

        //네이버관련 업데이트
        if ($mode == 'register' && $applyData['applyFl'] == 'y') {
            $this->setGoodsUpdateEp($applyData['applyFl'], $goodsNo, true);
        } else {
            $this->setGoodsUpdateEp($applyData['applyFl'], $goodsNo);
        }

        return $applyData['applyFl'];
    }

    /**
     * 상품 승인 관련 상품 로그 테이블 업데이트
     * 수정인경우 로그 쌓임
     *
     */
    public function setGoodsLog($mode, $goodsNo, $prevData, $updateData)
    {

        $arrData['mode'] = $mode;
        $arrData['goodsNo'] = $goodsNo;
        $arrData['managerId'] = (string)Session::get('manager.managerId');
        $arrData['managerNo'] = Session::get('manager.sno');
        $arrData['prevData'] = json_encode($prevData, JSON_UNESCAPED_UNICODE);
        $arrData['updateData'] = json_encode($updateData, JSON_UNESCAPED_UNICODE);

        //공급사이면서 자동승인이 아닌경우 상품 승인신청 처리
        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionModify') == 'c') {
            $arrData['applyFl'] = 'a';
        } else  $arrData['applyFl'] = 'y';


        $arrBind = $this->db->get_binding(DBTableField::tableLogGoods(), $arrData, 'insert');
        $this->db->set_insert_db(DB_LOG_GOODS, $arrBind['param'], $arrBind['bind'], 'y');

        unset($arrData);

    }


    /**
     * 상품 네이버 업데이트
     * @param $applyFl
     * @param $goodsNo
     * @param bool $registerFl
     * @return bool
     */
    public function setGoodsUpdateEp($applyFl, $goodsNo, $registerFl = false)
    {
        if ($this->naverConfig['naverFl'] == 'y' || $this->daumConfig['useFl'] == 'y' || $this->paycoConfig['paycoFl'] == 'y') {

            if (empty($registerFl)) {
                if ($applyFl == 'y') {
                    $arrData['class'] = 'U';
                }
                else {
                    $arrData['class'] = 'D';
                }
            } else {
                $arrData['class'] = 'I';
            }
            if (is_array($goodsNo)) {
                $arrGoodsNo = $goodsNo;
            } else {
                $arrGoodsNo = array($goodsNo);
            }
            foreach ($arrGoodsNo as $k => $v) {

                $arrData['mapid'] = $v;

                $arrBind = [];
                $strSQL = "SELECT sno FROM " . DB_GOODS_UPDATET_NAVER . " WHERE  mapid = ? ";
                $this->db->bind_param_push($arrBind, 's', $v);
                $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
                unset($arrBind);

                if (empty($registerFl) && count($tmp) == 0 || $registerFl) { //신규상품이면
                    $arrBind = $this->db->get_binding(DBTableField::tableGoodsUpdateNaver(), $arrData, 'insert', array_keys($arrData));
                    $this->db->set_insert_db(DB_GOODS_UPDATET_NAVER, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrData);
                    unset($arrBind);
                } else {
                    if (is_array($tmp) &&  count($tmp) > 1) {  //중복된 상품번호 삭제
                        for($i=1;$i<count($tmp);$i++){
                            $arrBind=[];
                            $this->db->bind_param_push($arrBind, 'i', $tmp[$i]['sno']);
                            $this->db->set_delete_db(DB_GOODS_UPDATET_NAVER, ' sno=?', $arrBind);
                        }
                    }
                    $arrBind = $this->db->get_binding(DBTableField::tableGoodsUpdateNaver(), $arrData, 'update', array_keys($arrData));
                    $this->db->bind_param_push($arrBind['bind'], 'i', $tmp['sno']);
                    $this->db->set_update_db(DB_GOODS_UPDATET_NAVER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                    unset($arrBind);
                }
            }

        } else {
            return true;
        }


    }

    /**
     * 상품 로그데이터 추출 및 가공
     * @param int $goodsNo - 상품번호
     * @param bool $superFl - 본사 여부(옵션 변경 값만 추출 - 공급사상품승인관리 레거시보장)
     * @return array $getData
     */
    public function getAdminListGoodsLog($goodsNo, $superFl = false)
    {
        $arrField = DBTableField::setTableField('tableLogGoods');
        $arrBind = [];
        $strWhere = 'goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        $strSQL = 'SELECT sno,regDt, ' . implode(', ', $arrField) . ' FROM ' . DB_LOG_GOODS . ' WHERE ' . $strWhere . ' ORDER BY sno DESC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if ($getData) {
            foreach ($getData as $key => $val) {
                switch ($val['mode']) {
                    case 'category':
                        $tmp = [];
                        $tmpStr = [];
                        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
                        $prevData = json_decode(gd_htmlspecialchars_stripslashes($val['prevData']), true);
                        if ($prevData) {
                            foreach ($prevData as $k => $v) {
                                if ($v['cateLinkFl'] == 'y') $tmp[$v['sno']] = $cate->getCategoryPosition($v['cateCd']);
                            }

                            $getData[$key]['prevData'] = $tmp;
                            $getData[$key]['prevDataSet'] = implode("<br/>", $tmp);
                            unset($tmp);
                        }

                        $updateData = json_decode(gd_htmlspecialchars_stripslashes($val['updateData']), true);
                        if ($updateData) {
                            foreach ($updateData as $catemode => $cateinfo) {

                                foreach ($cateinfo as $k => $v) {
                                    if ($catemode == 'delete') {
                                        $tmp[$catemode][] = $getData[$key]['prevData'][$v];
                                    } else {
                                        if ($v['cateLinkFl'] == 'y') $tmp[$catemode][] = $cate->getCategoryPosition($v['cateCd']);
                                    }
                                }
                                $tmpStr[] = "[" . $catemode . "]:<br/>" . implode("<br/>", $tmp[$catemode]) . "<br/>";

                            }

                            $getData[$key]['updateData'] = $tmp;
                            $getData[$key]['updateDataSet'] = implode("<br/>", $tmpStr);
                        }

                        break;

                    case 'goods':

                        $goodsField = DBTableField::getFieldNames('tableGoods');

                        $prevTmpStr = [];
                        $updateTmpStr = [];
                        $prevData = json_decode($val['prevData'], true);
                        $getData[$key]['prevData'] = $prevData;

                        $updateData = json_decode($val['updateData'], true);
                        $getData[$key]['updateData'] = $updateData;

                        if ($updateData) {
                            foreach ($updateData as $k => $v) {
                                if(!$v) continue; // 값이 없을 경우
                                if($k == 'mileageGroupMemberInfo' || $k == 'goodsDiscountGroupMemberInfo') { // 마일리지적립회원정보 회원할인정보 값이 없을 경우
                                    if(ArrayUtils::removeEmpty((array)$v) == true) continue;
                                }
                                if($k == 'goodsIconCd'|| $k == 'goodsIconStartYmd' || $k == 'goodsIconEndYmd') { // 아이콘의 경우 배열형태로 있기 때문에 변환
                                    if(empty($v) == false) {
                                        if(is_array($v)) {
                                            $v = implode(',', $v);
                                            $updateData[$k] = $v;
                                        }
                                        $goodsField[$k] = DBTableField::getFieldNames('tableGoodsIcon')[$k];
                                    }
                                    if(empty($prevData['goodsIconCd']) == false && is_array($prevData['goodsIconCd'])) {
                                        $prevData['goodsIconCd'] = implode(',', $prevData['goodsIconCd']);
                                    }
                                }
                                if($k == 'periodDiscountStart' || $k =='periodDiscountEnd') { // 할인 시작, 종료일자
                                    $periodDisCountLength = explode(':', $v);
                                    if($periodDisCountLength > 3) {
                                        $v = $v . ':00'; // json 데이터로 필드값 저장 시 날짜형식의 데이터가 안맞는 경우가 강제치환
                                    }
                                }
                                if($prevData[$k] != $v || $superFl == false) { // 기존 <> 수정 로그 데이터가 다를 경우, 공급사일 경우
                                    $prevTmpStr[] = $goodsField[$k] . " : " . $prevData[$k];
                                    $updateTmpStr[] = $goodsField[$k] . " : " . $v;
                                }
                            }
                        }
                        $getData[$key]['prevDataSet'] = implode("<br/>", $prevTmpStr);
                        $getData[$key]['updateDataSet'] = implode("<br/>", $updateTmpStr);

                        break;
                    default  :

                        $tmp = [];
                        $tmpStr = [];
                        $prevData = json_decode(gd_htmlspecialchars_stripslashes($val['prevData']), true);
                        $updateData = json_decode(gd_htmlspecialchars_stripslashes($val['updateData']), true);

                        // 옵션 비교 데이터 배열
                        $compareOptionFieldArray = ['optionNo', 'optionPrice', 'optionCostPrice', 'optionCode', 'optionViewFl', 'optionSellFl', 'stockCnt', 'optionValue1', 'optionValue2', 'optionValue3', 'optionValue4', 'optionValue5', 'optionMemo', 'optionSellCode', 'optionDeliveryFl', 'optionDeliveryCode'];
                        // 텍스트 옵션 비교 데이터 배열
                        $compareTextOptionFieldArray = ['optionName', 'mustFl', 'addPrice', 'inputLimit'];
                        $imageField = array('magnify' => __('확대 이미지'), 'detail' => __('상세 이미지'), 'list' => __('리스트 이미지'), 'main' => __('리스트 이미지'), 'add1' => __('추가이미지'), 'add2' => __('추가이미지'), 'add3' => __('추가이미지'), 'add4' => __('추가이미지'), 'add5' => __('추가이미지'));

                        if ($prevData) {
                            foreach ($prevData as $k => $v) {
                                $tmp[$v['sno']] = $v;
                                if ($val['mode'] == 'addInfo') {
                                    $tmpStr[] = $v['infoTitle'] . ":" . $v['infoValue'];
                                } else if ($val['mode'] == 'option') {
                                    // 기존 정보를 변경한 옵션만 추출 - 본사
                                    $compareCheck = $compareStatusViewCheck = false;
                                    if($superFl == true) { // 본사일 경우 - 변경 옵션만 비교 후 추출
                                        foreach($v as $compareKey => $compareVal) {
                                            if(in_array($compareKey, $compareOptionFieldArray)) {
                                                if($compareKey == 'optionPrice' || $compareKey == 'optionCostPrice') {
                                                    $compareVal = gd_money_format($compareVal, false);
                                                }
                                                if($compareVal != $updateData['update'][$k][$compareKey]) {
                                                    if($v['optionViewFl'] != $updateData['update'][$k]['optionViewFl'] || $v['optionSellFl'] != $updateData['update'][$k]['optionSellFl']) {
                                                        $compareStatusViewCheck = true;
                                                    }
                                                    $compareCheck = true;
                                                }
                                            }
                                        }
                                    }
                                    if($compareCheck == true || $superFl == false) { // 본사(비교데이터검증) , 공급사(기존레거시)
                                        $optionName = [];
                                        for($i = 1; $i <= 5; $i++) {
                                            if($v['optionValue' . $i]) $optionName[] = $v['optionValue' . $i];
                                        }
                                        if(!$v['optionViewFl'] && !$v['optionSellFl']) continue;
                                        if($optionName) $tmpStr[] = implode(",", $optionName) . " : " . gd_money_format($v['optionPrice'], false) . " / " . $v['stockCnt'] . "개";
                                        if(($compareStatusViewCheck == true && $compareCheck == true) || $superFl == false) $tmpStr[] = "옵션 상태 변경 : " . $v['optionViewFl'] . " / " . $v['optionSellFl'] . " / " . $v['stockCnt'] . "개";
                                    }
                                } else if ($val['mode'] == 'optionText') {
                                    // 기존 정보를 변경한 텍스트옵션만 추출 - 본사
                                    $compareCheck = false;
                                    if($superFl == true) { // 본사일 경우 - 변경 텍스트 옵션 값만 비교 후 추출
                                        foreach($v as $compareKey => $compareVal) {
                                            if(in_array($compareKey, $compareTextOptionFieldArray)) {
                                                if($compareKey == 'addPrice') {
                                                    $compareVal = gd_money_format($compareVal, false);
                                                }
                                                if($compareVal != $updateData['update'][$k][$compareKey]) {
                                                    $compareCheck = true;
                                                }
                                            }
                                        }
                                    }
                                    if($compareCheck == true || $superFl == false) { // 본사(비교데이터검증) , 공급사(기존레거시)
                                        if($v['mustFl'] == 'y') { // 텍스트옵션 필수여부
                                            $mustFlStr = ' / 필수';
                                        } else {
                                            $mustFlStr = '';
                                        }
                                        $tmpStr[] = $v['optionName'] . " : " . gd_money_format($v['addPrice'], false) . " / " . $v['inputLimit'] . "자 제한" . $mustFlStr;
                                    }
                                } else if ($val['mode'] == 'image') {
                                    $tmpStr[] = $imageField[$v['imageKind']] . " : " . $v['imageName'] . " / " . $v['imageSize'] . "size";
                                }
                            }
                            $getData[$key]['prevData'] = $tmp;
                            $getData[$key]['prevDataSet'] = implode("<br/>", $tmpStr);
                            unset($tmp);
                            unset($tmpStr);
                        }

                        if ($updateData) {
                            foreach ($updateData as $addinfomode => $addinfo) {
                                $tmpData = [];
                                foreach ($addinfo as $k => $v) {
                                    if ($addinfomode == 'delete') {
                                        $v = $getData[$key]['prevData'][$v];
                                    }

                                    $tmp[$addinfomode][] = $v;

                                    if ($val['mode'] == 'addInfo') {
                                        $tmpData[] = $v['infoTitle'] . ":" . $v['infoValue'];
                                    } else if ($val['mode'] == 'option') {
                                        // 기존 정보를 변경한 옵션만 추출 - 본사
                                        $compareCheck = $compareStatusViewCheck = false;
                                        if($superFl == true) { // 본사일 경우 - 변경 텍스트 옵션 값만 비교 후 추출
                                            foreach($v as $compareKey => $compareVal) {
                                                if(in_array($compareKey, $compareOptionFieldArray)) {
                                                    if($compareVal != $prevData[$k][$compareKey]) {
                                                        if($v['optionViewFl'] != $prevData[$k]['optionViewFl'] || $v['optionSellFl'] != $prevData[$k]['optionSellFl']) {
                                                            $compareStatusViewCheck = true;
                                                        }
                                                        $compareCheck = true;
                                                    }
                                                }
                                            }
                                        }
                                        if($compareCheck == true || $superFl == false) { // 본사(비교데이터검증) , 공급사(기존레거시)
                                            $optionName = [];
                                            for($i = 1; $i <= 5; $i++) {
                                                if($v['optionValue' . $i]) $optionName[] = $v['optionValue' . $i];
                                            }
                                            if(!$v['optionViewFl'] && !$v['optionSellFl']) continue;
                                            if($optionName) $tmpData[] = implode(",", $optionName) . " : " . $v['optionPrice'] . " / " . $v['stockCnt'] . "개";
                                            if(($compareStatusViewCheck == true && $compareCheck == true) || $superFl == false) $tmpData[] = "옵션 상태 변경 : " . $v['optionViewFl'] . " / " . $v['optionSellFl'] . " / " . $v['stockCnt'] . "개";
                                        }
                                    } else if ($val['mode'] == 'optionText') {
                                        // 기존 정보를 변경한 텍스트옵션만 추출 - 본사
                                        $compareCheck = false;
                                        if($superFl == true) { // 본사일 경우 - 변경 텍스트 옵션 값만 비교 후 추출
                                            foreach($v as $compareKey => $compareVal) {
                                                if(in_array($compareKey, $compareTextOptionFieldArray)) {
                                                    if($compareVal != $prevData[$k][$compareKey]) {
                                                        $compareCheck = true;
                                                    }
                                                }
                                            }
                                        }
                                        if($compareCheck == true || $superFl == false) { // 본사(비교데이터검증) , 공급사(기존레거시)
                                            if($v['mustFl'] == 'y') { // 텍스트옵션 필수여부
                                                $mustFlStr = ' / 필수';
                                            } else {
                                                $mustFlStr = '';
                                            }
                                            $tmpData[] = $v['optionName'] . " : " . gd_money_format($v['addPrice'], flase) . " / " . $v['inputLimit'] . "자 제한" . $mustFlStr;
                                        }
                                    } else if ($val['mode'] == 'image') {
                                        $tmpData[] = $imageField[$v['imageKind']] . " : " . $v['imageName'] . " / " . $v['imageSize'] . "size";
                                    }
                                }
                                if($tmpData) {
                                    $tmpStr[] = "[" . $addinfomode . "]:<br/>" . implode("<br/>", $tmpData) . "<br/>";
                                }
                            }
                            $getData[$key]['updateData'] = $tmp;
                            $getData[$key]['updateDataSet'] = implode("<br/>", $tmpStr);
                        }
                        break;
                }
            }
        }
        return $getData;
    }

    /**
     * setDelStateGoods
     *
     * @param $goodsNo
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setDelStateGoods($goodsNo, $modDtUse = null)
    {
        if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsDelete') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }

        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionDelete') == 'c') {
            $applyFl = "a";
            $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "')";

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS, array("applyFl = '".$applyFl."' , applyType = 'd'"), $strWhere);
            if($this->goodsDivisionFl) {

                //상품수정일 변경유무 추가
                if ($modDtUse == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS_SEARCH, array("applyFl = '".$applyFl."' , applyType = 'd'"), $strWhere);
            }
        } else {
            $applyFl = "y";
            $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "')";

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS, array("delFl = 'y',delDt = '" . date('Y-m-d H:i:s') . "'"), $strWhere);
            if($this->goodsDivisionFl) {
                //상품수정일 변경유무 추가
                if ($modDtUse == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS_SEARCH, array("delFl = 'y'"), $strWhere);
            }
        }

        //네이버쇼핑사용인경우
        //   if ($this->naverConfig['naverFl'] == 'y') {

        $this->setGoodsUpdateEp("n", $goodsNo);

        return $applyFl;

        // }

    }

    /**
     * setSoldOutGoods
     *
     * @param $goodsNo
     * @param $modDtUse
     */
    public function setSoldOutGoods($goodsNo, $modDtUse = null)
    {

        $strSQL = 'SELECT goodsNo FROM ' . DB_GOODS . ' g  WHERE soldOutFl = "n" AND goodsNo IN ("'.implode('","',$goodsNo).'")';
        $tmpData = $this->db->query_fetch($strSQL);

        //상품수정일 변경유무 추가
        if ($modDtUse == 'n') {
            $this->db->setModDtUse(false);
        }
        $this->db->set_update_db(DB_GOODS, "soldOutFl = 'y'","goodsNo IN ('" . implode("','", array_column($tmpData, 'goodsNo')) . "')");
        if($this->goodsDivisionFl) {

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS_SEARCH, "soldOutFl = 'y'", "goodsNo IN ('" . implode("','", array_column($tmpData, 'goodsNo')) . "')");
        }

        //로그남김
        foreach ($tmpData as $k => $v) {
            $this->setGoodsLog("goods", $v['goodsNo'], array("soldOutFl" => 'n'), array("soldOutFl" => "y"));
        }

        $applyFl = $this->setGoodsApplyUpdate(array_column($tmpData, 'goodsNo'), 'modify', $modDtUse);
        return $applyFl;

    }


    /**
     * 상품승인
     *
     * @param $goodsNo
     * @param $modDtUse
     */
    public function setApplyGoods($goodsNo, $mode = null, $modDtUse = null)
    {

        $arrBind = [];
        $arrUpdate[] = "applyFl = 'y'";
        if ($mode == 'd') $arrUpdate[] = "delFl = 'y'";
        $this->db->bind_param_push($arrBind, 's', $goodsNo);

        //상품수정일 변경유무 추가
        if ($modDtUse == 'n') {
            $this->db->setModDtUse(false);
        }

        $this->db->set_update_db(DB_GOODS, $arrUpdate, 'goodsNo = ?', $arrBind);
        if($this->goodsDivisionFl) {
            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS_SEARCH, $arrUpdate, 'goodsNo = ?', $arrBind);
        }

        if ($mode != 'd') {

            $arrBind = [];
            $arrUpdate[] = "applyFl = 'y'";
            $this->db->bind_param_push($arrBind, 's', $goodsNo);
            $this->db->bind_param_push($arrBind, 's', 'a');
            $this->db->set_update_db(DB_LOG_GOODS, $arrUpdate, 'goodsNo = ? and applyFl = ?', $arrBind);

        }

    }


    /**
     * 상품반려
     *
     * @param $goodsNo
     */
    public function setApplyRejectGoods($goodsNo, $applyMsg)
    {
        $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "')";
        $this->db->set_update_db(DB_GOODS, array("applyFl = 'r' ,applyMsg = '" . $applyMsg . "'"), $strWhere);
        if($this->goodsDivisionFl) $this->db->set_update_db(DB_GOODS_SEARCH, array("applyFl = 'r'"), $strWhere);

        $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "') AND applyFl = 'a'";
        if($this->goodsDivisionFl) $this->db->set_update_db(DB_LOG_GOODS, array("applyFl = 'r'"), $strWhere);
    }


    /**
     * 상품철회
     *
     * @param $goodsNo
     */
    public function setApplyWithdrawGoods($goodsNo)
    {
        $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "')";
        $this->db->set_update_db(DB_GOODS, array("applyFl = 'n'"), $strWhere);
        if($this->goodsDivisionFl) $this->db->set_update_db(DB_GOODS_SEARCH, array("applyFl = 'n'"), $strWhere);

        $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "') AND applyFl = 'a'";
        if($this->goodsDivisionFl) $this->db->set_update_db(DB_LOG_GOODS, array("applyFl = 'n'"), $strWhere);
    }


    /**
     * 자주쓰는 상품 옵션의 등록 및 수정에 관련된 정보
     *
     * @param integer $dataSno 수정의 경우 레코드 sno
     * @return array 자주쓰는 상품 옵션 정보
     */
    public function getDataManageOption($dataSno = null)
    {
        // --- 등록인 경우
        if (is_null($dataSno)) {
            // 기본 정보
            $data['mode'] = 'option_register';
            $data['sno'] = null;
            $data['optionCnt'] = 0;

            // 기본값 설정
            DBTableField::setDefaultData('tableManageGoodsOption', $data);

            // --- 수정인 경우
        } else {
            $this->db->bind_param_push($this->arrBind, 'i', $dataSno);

            $this->db->strField = 'sno, ' . implode(', ', DBTableField::setTableField('tableManageGoodsOption'));
            $this->db->strWhere = 'sno = ?';

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_GOODS_OPTION . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $this->arrBind, false);

            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }


            // 기본값 설정
            DBTableField::setDefaultData('tableManageGoodsOption', $data);

            // 기본 정보
            $data['mode'] = 'option_modify';
            $data['optionName'] = explode(STR_DIVISION, $data['optionName']);
            $data['optionCnt'] = count($data['optionName']);
        }

        // --- 기본값 설정
        gd_isset($data['stockFl'], 'n');

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) {
            $data['scmFl'] = "n";
        } else {
            $data['scmFl'] = "y";
        }


        $checked = [];
        $checked['scmFl'][$data['scmFl']] = $checked['optionDisplayFl'][$data['optionDisplayFl']] = 'checked="checked"';

        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 자주쓰는 상품 옵션 관리 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoManageOption($arrData)
    {
        // 옵션 관리 명 체크
        if (Validator::required(gd_isset($arrData['optionManageNm'])) === false) {
            throw new \Exception(__('옵션 관리 명은 필수 항목 입니다.'), 500);
        }

        // 테그 제거
        $arrData['optionManageNm'] = strip_tags($arrData['optionManageNm']);

        // 옵션 사용여부에 따른 재정의 및 배열 삭제
        if (gd_isset($arrData['optionName'])) {
            if (is_array($arrData['optionName'])) {
                $arrData['optionName'] = implode(STR_DIVISION, $arrData['optionName']);
            }
        } else {
            throw new \Exception(sprintf(self::TEXT_REQUIRE_VALUE, '옵션명'), 500);
        }
        if (gd_isset($arrData['optionValue'])) {
            foreach ($arrData['optionValue'] as $key => $val) {
                if ($val == '[object Window]') { // 상품 상세에서 바로 등록시
                    unset($arrData['optionValue'][$key]);
                    continue;
                }
                if (is_array($arrData['optionValue'][$key])) {
                    $arrData['optionValue'][$key] = implode(STR_DIVISION, $arrData['optionValue'][$key]);
                }
            }
        } else {
            throw new \Exception(__('옵션값은 필수 항목입니다.'), 500);
        }
        unset($arrData['optionCnt']);

        // insert , update 체크
        if ($arrData['mode'] == 'option_modify') {
            $chkType = 'update';
        } else {
            $chkType = 'insert';
        }

        // 옵션 관리 정보
        $i = 1;
        foreach ($arrData['optionValue'] as $key => $val) {
            $arrData['optionValue' . $i] = $val;
            $i++;
        }
        unset($arrData['optionValue']);

        // 옵션 관리 정보 저장
        if (in_array($chkType, array('insert', 'update'))) {
            $arrBind = $this->db->get_binding(DBTableField::tableManageGoodsOption(), $arrData, $chkType);
            if ($chkType == 'insert') {
                $this->db->set_insert_db(DB_MANAGE_GOODS_OPTION, $arrBind['param'], $arrBind['bind'], 'y');
            }
            if ($chkType == 'update') {
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
                $this->db->set_update_db(DB_MANAGE_GOODS_OPTION, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
            unset($arrBind);
        }
    }

    /**
     * 자주쓰는 상품 옵션 관리 복사
     *
     * @param integer $dataSno 복사할 레코드 sno
     */
    public function setCopyManageOption($dataSno)
    {
        // 옵션 관리 정보 복사
        $arrField = DBTableField::setTableField('tableManageGoodsOption');
        $strSQL = 'INSERT INTO ' . DB_MANAGE_GOODS_OPTION . ' (' . implode(', ', $arrField) . ', regDt) SELECT ' . implode(', ', $arrField) . ', now() FROM ' . DB_MANAGE_GOODS_OPTION . ' WHERE sno = ' . $dataSno;
        $this->db->query($strSQL);
    }

    /**
     * 자주쓰는 상품 옵션 관리 삭제
     *
     * @param integer $dataSno 삭제할 레코드 sno
     */
    public function setDeleteManageOption($dataSno)
    {
        // 옵션 관리 정보 삭제
        $this->db->bind_param_push($arrBind, 'i', $dataSno);
        $this->db->set_delete_db(DB_MANAGE_GOODS_OPTION, 'sno = ?', $arrBind);
    }

    /**
     * 상품 아이콘의 등록 및 수정에 관련된 정보
     *
     * @param integer $iconSno 상품 아이콘 sno
     * @return array 상품 아이콘 정보
     */
    public function getDataManageGoodsIcon($iconSno = null)
    {
        // --- 등록인 경우
        if (is_null($iconSno)) {
            // 기본 정보
            $data['mode'] = 'icon_register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableManageGoodsIcon', $data);

            // --- 수정인 경우
        } else {
            $this->db->strWhere = 'sno = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $iconSno);
            $tmp = $this->getManageGoodsIconInfo($iconSno);
            $data = $tmp[0];
            $data['mode'] = 'icon_modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableManageGoodsIcon', $data);
        }

        $checked = array();
        $checked['iconPeriodFl'][$data['iconPeriodFl']] = $checked['iconUseFl'][$data['iconUseFl']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }


    /**
     * 상품 아이콘 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoManageGoodsIcon($arrData)
    {
        // 아이콘명 체크
        if (gd_isset($arrData['iconNm']) === null || gd_isset($arrData['iconNm']) == '') {
            throw new \Exception(__('아이콘 이름은 필수 항목입니다.'), 500);
        } else {
            if (gd_is_html($arrData['iconNm']) === true) {
                throw new \Exception(__('아이콘 이름에 태크는 사용할 수 없습니다.'), 500);
            }
        }

        // iconCd 처리
        if ($arrData['mode'] == 'icon_register') {
            $this->db->strField = 'if(max(iconCd) IS NOT NULL, max(iconCd), \'icon0000\') as maxCd';
            $maxCd = $this->getManageGoodsIconInfo();
            $arrData['iconCd'] = 'icon' . sprintf('%04d', ((int)str_replace('icon', '', $maxCd[0]['maxCd']) + 1));
        } else {
            // iconCd 체크
            if (gd_isset($arrData['iconCd']) === null) {
                throw new \Exception(__('상품 아이콘 코드는 필수 항목입니다.'), 500);
            }
            // 아이콘 sno 체크
            if (gd_isset($arrData['sno']) === null) {
                throw new \Exception(__('아이콘 번호는 필수 항목입니다.'), 500);
            }
        }

        // 아이콘 이미지 처리
        $iconImage = Request::files()->get('iconImage');
        if (gd_file_uploadable($iconImage, 'image') === true) {
            // 이미지 업로드
            $imageExt = strrchr($iconImage['name'], '.');
            $arrData['iconImage'] = str_replace(' ', '', trim(substr($iconImage['name'], 0, -strlen($imageExt)))) . $imageExt; // 이미지명 공백 제거
            $targetImageFile = $arrData['iconImage'];
            $tmpImageFile = $iconImage['tmp_name'];

            Storage::disk(Storage::PATH_CODE_GOODS_ICON, 'local')->upload($tmpImageFile, $targetImageFile);
        } else {
            if (empty($arrData['iconImageTemp'])) {
                throw new \Exception(__('아이콘 이미지는 필수 항목입니다.'), 500);
            }
            $arrData['iconImage'] = $arrData['iconImageTemp'];
        }

        // 공통 키값
        $arrDataKey = array('iconCd' => $arrData['iconCd']);

        // 상품 아이콘의 기존 정보
        $getIcon = array();
        if ($arrData['mode'] == 'icon_modify') {
            $this->db->strWhere = 'sno = ? AND iconCd = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $arrData['sno']);
            $this->db->bind_param_push($this->arrBind, 's', $arrData['iconCd']);
            $getIcon = $this->getManageGoodsIconInfo();
        }

        // 상품 아이콘 정보 저장
        foreach ($arrData as $key => $val) {
            $tmpData[$key][] = $val;
        }

        $compareIcon = $this->db->get_compare_array_data($getIcon, $tmpData, false);
        $this->db->set_compare_process(DB_MANAGE_GOODS_ICON, $tmpData, $arrDataKey, $compareIcon);
    }

    /**
     * 기타 아이콘 정보 저장 (마일리지, 품절)
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoManageEtcIcon($arrData)
    {
        $iconImage = Request::files()->get('iconImage');
        // 아이콘 이미지 처리
        if (gd_file_uploadable($iconImage, 'image') === true) {
            // 이미지 업로드
            $tmpImageFile = $iconImage['tmp_name'];
            $targetImageFile = 'icon_' . $arrData['iconType'] . '.gif';
            //DataFileFactory::create('local')->move('icon', $tmpImageFile, $targetImageFile, true);
            Storage::disk(Storage::PATH_CODE_GOODS_ICON, 'local')->upload($tmpImageFile, $targetImageFile);

        }
    }

    /**
     * 상품 아이콘 삭제
     *
     * @param integer $dataSno 삭제할 레코드 sno
     * @return array 상품 아이콘 정보
     */
    public function setDeleteManageGoodsIcon($dataSno)
    {
        // 이미지 이름 가지고 오기
        $strWhere = 'sno = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $dataSno);

        $this->db->strField = 'iconCd, iconNm, iconImage';
        $this->db->strWhere = $strWhere;
        $data = $this->getManageGoodsIconInfo();


        if (!empty($data[0]['iconImage'])) {
            Storage::disk(Storage::PATH_CODE_GOODS_ICON, 'local')->delete($data[0]['iconImage']);
        }

        // 옵션 관리 정보 삭제
        $this->db->set_delete_db(DB_MANAGE_GOODS_ICON, $strWhere, $this->arrBind);
        unset($this->arrBind);

        // 전체 로그를 저장합니다.
        LogHandler::wholeLog('icon', null, 'delete', $data[0]['iconCd'], $data[0]['iconNm']);
    }

    /**
     * 관리자 상품 리스트를 위한 검색 정보
     */
    public function setSearchGoods($getValue = null, $list_type = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        // 통합 검색
        /* @formatter:off */
        $this->search['combineSearch'] = [
            'goodsNm' => __('상품명'),
            'goodsNo' => __('상품코드'),
            'goodsCd' => __('자체상품코드'),
            'goodsSearchWord' => __('검색 키워드'),
            '__disable1' => '==========',
            'makerNm' => __('제조사'),
            'originNm' => __('원산지'),
            'goodsModelNo' => __('모델번호'),
            'hscode' => __('HS코드'),
            'addInfo' => __('추가항목'),
            '__disable2' => '==========',
            'memo' => '관리자 메모',
        ];
        /* @formatter:on */

        if(gd_is_provider() === false) {
            $this->search['combineSearch']['companyNm'] = __('공급사명');
            if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) $this->search['combineSearch']['purchaseNm'] = __('매입처명');
        }

        // 검색을 위한 bind 정보
        $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');
        $fieldTypeOption = DBTableField::getFieldTypes('tableGoodsOption');
        $fieldTypeLink = DBTableField::getFieldTypes('tableGoodsLinkCategory');
        $fieldTypeIcon = DBTableField::getFieldTypes('tableGoodsIcon');

        //검색설정
        $this->search['sortList'] = array(
            'g.goodsNo desc' => __('등록일 ↓'),
            'g.goodsNo asc' => __('등록일 ↑'),
            'goodsNm asc' => __('상품명 ↓'),
            'goodsNm desc' => __('상품명 ↑'),
            'goodsPrice asc' => __('판매가 ↓'),
            'goodsPrice desc' => __('판매가 ↑'),
            'companyNm asc' => __('공급사 ↓'),
            'companyNm desc' => __('공급사 ↑'),
            'makerNm asc' => __('제조사 ↓'),
            'makerNm desc' => __('제조사 ↑'),
            'orderGoodsCnt desc' => __('결제 ↑'),
            'hitCnt desc' => __('조회 ↑'),
            'orderRate desc' => __('구매율 ↑'),
            'cartCnt desc' => __('담기 ↑'),
            'wishCnt desc' => __('관심 ↑'),
            'reviewCnt desc' => __('후기 ↑')
        );

        //삭제일추가
        $deleteDaySort = array (
            'delDt desc, g.goodsNo desc' => __('삭제일 ↓'),
            'delDt asc' => __('삭제일 ↑'),
        );

        if ($getValue['delFl'] == 'y') {
            $this->search['sortList'] = array_merge($deleteDaySort , $this->search['sortList']);
        }

        // --- 검색 설정
        $this->search['sort'] = gd_isset($getValue['sort'], 'g.goodsNo desc');
        $this->search['detailSearch'] = gd_isset($getValue['detailSearch']);
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['cateGoods'] = ArrayUtils::last(gd_isset($getValue['cateGoods']));
        $this->search['displayTheme'] = gd_isset($getValue['displayTheme']);
        $this->search['brand'] = ArrayUtils::last(gd_isset($getValue['brand']));
        $this->search['brandCd'] = gd_isset($getValue['brandCd']);
        $this->search['brandCdNm'] = gd_isset($getValue['brandCdNm']);
        $this->search['purchaseNo'] = gd_isset($getValue['purchaseNo']);
        $this->search['purchaseNoNm'] = gd_isset($getValue['purchaseNoNm']);
        $this->search['goodsPrice'][] = gd_isset($getValue['goodsPrice'][0]);
        $this->search['goodsPrice'][] = gd_isset($getValue['goodsPrice'][1]);
        $this->search['mileage'][] = gd_isset($getValue['mileage'][0]);
        $this->search['mileage'][] = gd_isset($getValue['mileage'][1]);
        $this->search['optionFl'] = gd_isset($getValue['optionFl']);
        $this->search['mileageFl'] = gd_isset($getValue['mileageFl']);
        $this->search['optionTextFl'] = gd_isset($getValue['optionTextFl']);
        $this->search['goodsDisplayFl'] = gd_isset($getValue['goodsDisplayFl']);
        $this->search['goodsDisplayMobileFl'] = gd_isset($getValue['goodsDisplayMobileFl']);
        $this->search['goodsSellFl'] = gd_isset($getValue['goodsSellFl']);
        $this->search['goodsSellMobileFl'] = gd_isset($getValue['goodsSellMobileFl']);
        $this->search['stockFl'] = gd_isset($getValue['stockFl']);
        $this->search['stock'] = gd_isset($getValue['stock']);
        $this->search['stockStateFl'] = gd_isset($getValue['stockStateFl'], 'all');
        $this->search['soldOut'] = gd_isset($getValue['soldOut']);
        $this->search['sellStopFl'] = gd_isset($getValue['sellStopFl']);
        $this->search['confirmRequestFl'] = gd_isset($getValue['confirmRequestFl']);
        $this->search['goodsIconCdPeriod'] = gd_isset($getValue['goodsIconCdPeriod']);
        $this->search['goodsIconCd'] = gd_isset($getValue['goodsIconCd']);
        $this->search['goodsColor'] = gd_isset($getValue['goodsColor']);
        $this->search['deliveryFl'] = gd_isset($getValue['deliveryFl']);
        $this->search['deliveryFree'] = gd_isset($getValue['deliveryFree']);

        $this->search['goodsDisplayMobileFl'] = gd_isset($getValue['goodsDisplayMobileFl']);
        $this->search['goodsSellMobileFl'] = gd_isset($getValue['goodsSellMobileFl']);
        $this->search['mobileDescriptionFl'] = gd_isset($getValue['mobileDescriptionFl']);
        $this->search['delFl'] = gd_isset($getValue['delFl'], 'n');

        $this->search['addGoodsFl'] = gd_isset($getValue['addGoodsFl']);
        $this->search['categoryNoneFl'] = gd_isset($getValue['categoryNoneFl']);
        $this->search['brandNoneFl'] = gd_isset($getValue['brandNoneFl']);
        $this->search['purchaseNoneFl'] = gd_isset($getValue['purchaseNoneFl']);

        $this->search['goodsDeliveryFl'] = gd_isset($getValue['goodsDeliveryFl']);
        $this->search['goodsDeliveryFixFl'] = gd_isset($getValue['goodsDeliveryFixFl'], array('all'));

        $this->search['scmFl'] = gd_isset($getValue['scmFl'], Session::get('manager.isProvider') ? 'y' : 'all');
        if ($this->search['scmFl'] == 'y' && !isset($getValue['scmNo']) && !Session::get('manager.isProvider')) $this->search['scmFl'] = "all";
        $this->search['scmNo'] = gd_isset($getValue['scmNo'], (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm'],(string) Session::get('manager.companyNm'));
        $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'], '-1');
        if ($getValue['delFl'] == 'y') {
            $this->search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'delDt');
        }
        $this->search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'regDt');

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-7 day')));
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));
        }


        $this->search['applyType'] = gd_isset($getValue['applyType'], 'all');
        $this->search['applyFl'] = gd_isset($getValue['applyFl'], 'all');
        $this->search['naverFl'] = gd_isset($getValue['naverFl']);
        $this->search['paycoFl'] = gd_isset($getValue['paycoFl']);
        $this->search['daumFl'] = gd_isset($getValue['daumFl']);
        $this->search['eventThemeSno'] = gd_isset($getValue['eventThemeSno']);
        $this->search['event_text'] = gd_isset($getValue['event_text']);
        $this->search['eventGroup'] = gd_isset($getValue['eventGroup']);
        $this->search['eventGroupSelectList'] = gd_isset($getValue['eventGroupSelectList']);
        $this->search['restockFl'] = gd_isset($getValue['restockFl'], 'all');
        // 상품혜택관리 검색 정보
        $this->search['goodsBenefitSno'] = gd_isset($getValue['goodsBenefitSno']);
        $this->search['goodsBenefitNm'] = gd_isset($getValue['goodsBenefitNm']);
        $this->search['goodsBenefitDiscount'] = gd_isset($getValue['goodsBenefitDiscount']);
        $this->search['goodsBenefitDiscountGroup'] = gd_isset($getValue['goodsBenefitDiscountGroup']);
        $this->search['goodsBenefitPeriod'] = gd_isset($getValue['goodsBenefitPeriod']);
        $this->search['goodsBenefitNoneFl'] = gd_isset($getValue['goodsBenefitNoneFl']);

        $this->search['optionSellFl'] = gd_isset($getValue['optionSellFl']);
        $this->search['optionDeliveryFl'] = gd_isset($getValue['optionDeliveryFl']);

        $this->search['stockType'] = gd_isset($getValue['stockType']);
        $this->checked['daumFl'][$this->search['daumFl']] = $this->checked['naverFl'][$this->search['naverFl']] = $this->checked['paycoFl'][$this->search['paycoFl']] = $this->checked['purchaseNoneFl'][$this->search['purchaseNoneFl']] = $this->checked['stockStateFl'][$this->search['stockStateFl']] = $this->checked['addGoodsFl'][$this->search['addGoodsFl']] = $this->checked['applyType'][$this->search['applyType']] = $this->checked['applyFl'][$this->search['applyFl']] = $this->checked['goodsDeliveryFl'][$this->search['goodsDeliveryFl']] = $this->checked['categoryNoneFl'][$this->search['categoryNoneFl']] = $this->checked['brandNoneFl'][$this->search['brandNoneFl']] = $this->checked['scmFl'][$this->search['scmFl']] = $this->checked['optionFl'][$this->search['optionFl']] = $this->checked['mileageFl'][$this->search['mileageFl']] = $this->checked['optionTextFl'][$this->search['optionTextFl']] = $this->checked['goodsDisplayFl'][$this->search['goodsDisplayFl']] = $this->checked['goodsSellFl'][$this->search['goodsSellFl']] = $this->checked['goodsDisplayMobileFl'][$this->search['goodsDisplayMobileFl']] = $this->checked['goodsSellMobileFl'][$this->search['goodsSellMobileFl']] = $this->checked['stockFl'][$this->search['stockFl']] = $this->checked['soldOut'][$this->search['soldOut']] = $this->checked['goodsIconCdPeriod'][$this->search['goodsIconCdPeriod']] = $this->checked['goodsIconCd'][$this->search['goodsIconCd']] = $this->checked['deliveryFl'][$this->search['deliveryFl']] = $this->checked['deliveryFree'][$this->search['deliveryFree']] = $this->checked['goodsDisplayMobileFl'][$this->search['goodsDisplayMobileFl']] = $this->checked['goodsSellMobileFl'][$this->search['goodsSellMobileFl']] = $this->checked['mobileDescriptionFl'][$this->search['mobileDescriptionFl']] = $this->checked['restockFl'][$this->search['restockFl']] = $this->checked['goodsBenefitNoneFl'][$this->search['goodsBenefitNoneFl']] = $this->checked['sellStopFl'][$this->search['sellStopFl']] = $this->checked['confirmRequestFl'][$this->search['confirmRequestFl']] = $this->checked['optionSellFl'][$this->search['optionSellFl']] = $this->checked['optionDeliveryFl'][$this->search['optionDeliveryFl']] = 'checked="checked"';

        foreach ($this->search['goodsDeliveryFixFl'] as $k => $v) {
            $this->checked['goodsDeliveryFixFl'][$v] = 'checked="checked"';
        }

        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";

        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = $this->selected['eventGroup'][$this->search['eventGroup']] = $this->selected['stockType'][$this->search['stockType']] = "selected='selected'";

        //삭제상품여부
        if ($this->search['delFl']) {
            $this->arrWhere[] = 'g.delFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['delFl'], $this->search['delFl']);
        }

        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1] && $mode != 'layer') {
            if ($this->goodsDivisionFl === true && $this->search['searchDateFl'] == 'delDt') {
                $this->arrWhere[] = $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['delDt'], $this->search['searchDate'][0] . ' 00:00:00');
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['delDt'], $this->search['searchDate'][1] . ' 23:59:59');
            } else {
                $this->arrWhere[] = 'g.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
            }
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('goodsNm', 'goodsNo', 'goodsCd', 'goodsSearchWord');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(g.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$keyNm], $this->search['keyword']);
                }

                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {

                if ($this->search['key'] == 'companyNm') {
                    $this->arrWhere[] = 's.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else if ($this->search['key'] == 'purchaseNm') {
                    $this->arrWhere[] = 'p.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                else if($this->search['key'] == 'addInfo') {
                }
                else if($this->search['key'] == 'hscode'){
                    if($this->search['keyword']){
                        $this->arrWhere[] = " hscode != '' AND json_extract(hscode,'$.*') LIKE concat('%',?,'%')";
                        $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                    }
                }
                else {
                    $this->arrWhere[] = 'g.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$this->search['key']], $this->search['keyword']);
                }

            }
        }

        // 카테고리 검색
        if ($this->search['cateGoods']) {
            $this->arrWhere[] = 'gl.cateCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeLink['cateCd'], $this->search['cateGoods']);
            $this->arrWhere[] = 'gl.cateLinkFl = "y"';
        }

        //카테고리 미지정
        if ($this->search['categoryNoneFl']) {
            $this->arrWhere[] = 'g.cateCd  = ""';
        }


        // 브랜드 검색
        if (($this->search['brandCd'] && $this->search['brandCdNm']) || $this->search['brand']) {
            if (!$this->search['brandCd'] && $this->search['brand'])
                $this->search['brandCd'] = $this->search['brand'];
            if($this->search['brandNoneFl']) { // 선택값 있고 미지정일 때
                $this->arrWhere[] = '(g.brandCd != ? OR (g.brandCd  = "" or g.brandCd IS NULL))';
            } else {
                $this->arrWhere[] = 'g.brandCd = ?';
            }
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['brandCd'], $this->search['brandCd']);
        } else if (((!$this->search['brandCd'] && !$this->search['brandCdNm']) || !$this->search['brand']) && $this->search['brandNoneFl']) { //브랜드 미지정
            $this->arrWhere[] = '(g.brandCd  = "" or g.brandCd IS NULL)';
        } else {
            $this->search['brandCd'] = '';
        }


        // 메인상품진열 상품 검색 Sno
        if ($this->search['displayTheme']) {
            // 메인분류 1,2차에 따른 조건 생성 함수
            $strDisplayThemeWhere = $this->searchGoodsListDisplayThemeData($this->search['displayTheme'][0], $this->search['displayTheme'][1]);
            if($strDisplayThemeWhere) {
                $this->arrWhere[] = implode(' AND ', $strDisplayThemeWhere);
            }
        }

        // 매입처 검색
        if (($this->search['purchaseNo'] && $this->search['purchaseNoNm'])) {
            if (is_array($this->search['purchaseNo'])) {
                foreach ($this->search['purchaseNo'] as $val) {
                    if($this->search['purchaseNoneFl']) { // 선택값 있고 미지정일 때
                        $tmpWhere[] = 'g.purchaseNo != ?';
                    } else {
                        $tmpWhere[] = 'g.purchaseNo = ?';
                    }
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                if($this->search['purchaseNoneFl']) { // 선택값 있고 미지정일 때
                    $this->arrWhere[] = '((' . implode(' AND ', $tmpWhere) . ') OR (g.purchaseNo IS NULL OR g.purchaseNo  = "" OR g.purchaseNo  <= 0 OR p.delFl = "y"))';
                } else {
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                }
                unset($tmpWhere);
            }
        }
        // 매입처 미지정 (선택 값 없고 미적용상품일 때)
        else if ((!$this->search['purchaseNo'] && !$this->search['purchaseNoNm']) && $this->search['purchaseNoneFl']) {
            $this->arrWhere[] = '(g.purchaseNo IS NULL OR g.purchaseNo  = "" OR g.purchaseNo  <= 0 OR p.delFl = "y")';
        }

        //추가상품 사용
        if ($this->search['addGoodsFl']) {
            $this->arrWhere[] = 'g.addGoodsFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['addGoodsFl'], $this->search['addGoodsFl']);
        }

        // 상품가격 검색
        if ($this->search['goodsPrice'][0] || $this->search['goodsPrice'][1]) {

            if($this->search['goodsPrice'][0]) {
                $this->arrWhere[] = 'g.goodsPrice >= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsPrice'], $this->search['goodsPrice'][0]);
            }

            if($this->search['goodsPrice'][1]) {
                $this->arrWhere[] = 'g.goodsPrice <= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsPrice'], $this->search['goodsPrice'][1]);
            }
        }

        // 마일리지 검색
        if ($this->search['mileage'][0] || $this->search['mileage'][1]) {

            $mileage = gd_policy('member.mileageGive')['goods'];

            if($this->search['mileage'][0]) {
                $this->arrWhere[] = 'if( g.mileageFl ="c", '.$mileage.',  g.mileageGoods ) >= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['mileageGoods'], $this->search['mileage'][0]);
            }

            if($this->search['mileage'][1]) {
                $this->arrWhere[] = 'if( g.mileageFl ="c", '.$mileage.',  g.mileageGoods ) <= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['mileageGoods'], $this->search['mileage'][1]);
            }
        }

        // 재고검색
        if ($this->search['stock'][0] || $this->search['stock'][1]) {

            $tmpStockType = $this->search['stockType'] == 'option' ? 'go.stockCnt' : 'g.totalStock';
            if($this->search['stock'][0]) {
                $this->arrWhere[] = $tmpStockType . ' >= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['totalStock'], $this->search['stock'][0]);
            }

            if($this->search['stock'][1]) {
                $this->arrWhere[] = $tmpStockType . ' <= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['totalStock'], $this->search['stock'][1]);
            }
        }

        // 옵션 사용 여부 검색
        if ($this->search['optionFl']) {
            $this->arrWhere[] = 'g.optionFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['optionFl'], $this->search['optionFl']);
        }
        // 마일리지 정책 검색
        if ($this->search['mileageFl']) {
            $this->arrWhere[] = 'g.mileageFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['mileageFl'], $this->search['mileageFl']);
        }
        // 텍스트옵션 사용 여부 검색
        if ($this->search['optionTextFl']) {
            $this->arrWhere[] = 'g.optionTextFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['optionTextFl'], $this->search['optionTextFl']);
        }
        // 상품 출력 여부 검색
        if ($this->search['goodsDisplayFl']) {
            $this->arrWhere[] = 'g.goodsDisplayFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsDisplayFl'], $this->search['goodsDisplayFl']);
        }
        // 상품 판매 여부 검색
        if ($this->search['goodsSellFl']) {
            $this->arrWhere[] = 'g.goodsSellFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsSellFl'], $this->search['goodsSellFl']);
        }
        // 무한정 판매 여부 검색
        if ($this->search['stockFl']) {
            $this->arrWhere[] = 'g.stockFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['stockFl'], $this->search['stockFl']);
        }
        if ($this->search['stockStateFl'] != 'all') {
            switch ($this->search['stockStateFl']) {
                case 'n': {
                    $this->arrWhere[] = 'g.stockFl = ?';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['stockFl'], 'n');
                    break;
                }
                case 'u' : {
                    $this->arrWhere[] = '(g.stockFl = ? and g.totalStock > 0)';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['stockFl'], 'y');
                    break;
                }
                case 'z' : {
                    $this->arrWhere[] = '(g.stockFl = ? and g.totalStock = 0)';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['stockFl'], 'y');
                    break;
                }

            }
        }

        // 품절상품 여부 검색
        if ($this->search['soldOut']) {
            if ($this->search['soldOut'] == 'y') {
                $this->arrWhere[] = '( g.soldOutFl = \'y\' OR (g.stockFl = \'y\' AND g.totalStock <= 0 ))';
            }
            if ($this->search['soldOut'] == 'n') {
                $this->arrWhere[] = '( g.soldOutFl = \'n\' AND (g.stockFl = \'n\' OR (g.stockFl = \'y\' AND g.totalStock > 0)) )';
            }
        }

        // 판매중지수량 여부 검색
        if ($this->search['sellStopFl']) {
            if ($this->search['sellStopFl'] == 'y') {
                $this->arrWhere[] = 'go.sellStopFl = \'y\'';
            }
            if ($this->search['sellStopFl'] == 'n') {
                $this->arrWhere[] = 'go.sellStopFl = \'n\'';
            }
        }

        // 확인요청수량 여부 검색
        if ($this->search['confirmRequestFl']) {
            if ($this->search['confirmRequestFl'] == 'y') {
                $this->arrWhere[] = 'go.confirmRequestFl = \'y\'';
            }
            if ($this->search['confirmRequestFl'] == 'n') {
                $this->arrWhere[] = 'go.confirmRequestFl = \'n\'';
            }
        }

        // 옵션품절상태 여부 검색
        if ($this->search['optionSellFl']) {
            if ($this->search['optionSellFl'] == 'y' || $this->search['optionSellFl'] == 'n') {
                $this->arrWhere[] = 'go.optionSellFl = \''.$this->search['optionSellFl'].'\'';
            }
            else  if ($this->search['optionSellFl'] != '') {
                $this->arrWhere[] = 'go.optionSellFl = \'t\'';
                $this->arrWhere[] = 'go.optionSellCode = \''.$this->search['optionSellFl'].'\'';
            }
        }

        // 옵션품절상태 여부 검색
        if ($this->search['optionDeliveryFl']) {
            if ($this->search['optionDeliveryFl'] == 'normal') {
                $this->arrWhere[] = 'go.optionDeliveryFl = \''.$this->search['optionDeliveryFl'].'\'';
            }
            else  if ($this->search['optionDeliveryFl'] != '') {
                $this->arrWhere[] = 'go.optionDeliveryFl = \'t\'';
                $this->arrWhere[] = 'go.optionDeliveryCode = \''.$this->search['optionDeliveryFl'].'\'';
            }
        }

        //상품 헤택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefitUse = $goodsBenefit->getConfig();

        if ($list_type == 'excel') {
            $tmpIcon = [];
            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $tmpIcon[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'pe\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
            }

            //무제한 아이콘
            if ($this->search['goodsIconCd']) {
                if($goodsBenefitUse == 'y'){
                    $tmpIcon[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }else{
                    $tmpIcon[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }
            }

            if (count($tmpIcon) > 0) {
                $this->arrWhere[] = '(' . implode(' OR ', $tmpIcon) . ')';
                unset($tmpIcon);
            }
        }

        // 아이콘(무제한) 여부 검색
        if ($this->search['goodsColor']) {
            $tmp = [];
            foreach ($this->search['goodsColor'] as $k => $v) {
                $tmp[] = 'g.goodsColor LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsColor'], $v);
            }
            $this->arrWhere[] = '(' . implode(" OR ", $tmp) . ')';
        }

        // 모바일 상품 출력 여부 검색
        if ($this->search['goodsDisplayMobileFl']) {
            $this->arrWhere[] = 'g.goodsDisplayMobileFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsDisplayMobileFl'], $this->search['goodsDisplayMobileFl']);
        }

        // 모바일 상품 판매 여부 검색
        if ($this->search['goodsSellMobileFl']) {
            $this->arrWhere[] = 'g.goodsSellMobileFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsSellMobileFl'], $this->search['goodsSellMobileFl']);
        }


        // 모바일 상세 설명 여부 검색
        if ($this->search['mobileDescriptionFl'] == 'y') {
            $this->arrWhere[] = 'g.goodsDescriptionMobile != \'\' AND g.goodsDescriptionMobile IS NOT NULL';
        } else if ($this->search['mobileDescriptionFl'] == 'n') {
            $this->arrWhere[] = '(g.goodsDescriptionMobile = \'\' OR g.goodsDescriptionMobile IS NULL)';
        }
        //공급사
        if ($this->search['scmFl'] != 'all') {
            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'g.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'g.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['scmNo'], $this->search['scmNo']);

                $this->search['scmNo'] = array($this->search['scmNo']);
                $this->search['scmNoNm'] = array($this->search['scmNoNm']);

            }
        }

        //승인구분
        if ($this->search['applyType'] != 'all') {
            $this->arrWhere[] = 'g.applyType = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['applyType'], $this->search['applyType']);
        }

        //승인상태
        if ($this->search['applyFl'] != 'all') {
            $this->arrWhere[] = 'g.applyFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['applyFl'], $this->search['applyFl']);
        }

        //배송관련
        $tmpFixFl = array_flip($this->search['goodsDeliveryFixFl']);
        unset($tmpFixFl['all']);
        if (count($tmpFixFl) || $this->search['goodsDeliveryFl']) {
            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $deliveryData = $delivery->getDeliveryGoods($this->search);

            if (is_array($deliveryData)) {
                foreach ($deliveryData as $val) {
                    $tmpWhere[] = 'g.deliverySno = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val['sno']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            }
        }

        // 네이버쇼핑상품 출력 여부 검색
        if ($this->search['naverFl']) {
            $this->arrWhere[] = 'g.naverFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['naverFl'], $this->search['naverFl']);
        }

        // 페이코쇼핑상품 출력 여부 검색
        if ($this->search['paycoFl']) {
            $this->arrWhere[] = 'g.paycoFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['paycoFl'], $this->search['paycoFl']);
        }

        // 다음쇼핑하우상품 출력 여부 검색
        if ($this->search['daumFl']) {
            $this->arrWhere[] = 'g.daumFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['daumFl'], $this->search['daumFl']);
        }

        //기획전 검색
        if ($this->search['eventThemeSno']) {
            $eventGoodsNoArray = array();
            $eventThemeData = $this->getDisplayThemeInfo($this->search['eventThemeSno']);
            if($eventThemeData['displayCategory'] === 'g'){
                //그룹형인경우

                $eventGroupTheme = \App::load('\\Component\\Promotion\\EventGroupTheme');
                $eventGroupData = $eventGroupTheme->getSimpleData($this->search['eventThemeSno']);
                $this->search['eventGroupSelectList'] = $eventGroupData;
                foreach($eventGroupData as $key => $eventGroupArr){
                    if((int)$eventGroupArr['sno'] === (int)$this->search['eventGroup']){
                        $eventGoodsNoArray = @explode(STR_DIVISION, $eventGroupArr['groupGoodsNo']);
                        break;
                    }
                }
            }
            else {
                //일반형인경우
                $eventGoodsNoArray = @explode(INT_DIVISION, $eventThemeData['goodsNo']);
            }

            if(count($eventGoodsNoArray) > 0){
                $this->arrWhere[] = "(g.goodsNo IN ('" . implode("',' ", $eventGoodsNoArray) . "'))";
            }
            unset($eventGoodsNoArray);
        }

        // 재입고 알림 사용 여부
        if ($this->search['restockFl'] != 'all') {
            $this->arrWhere[] = 'g.restockFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['restockFl'], $this->search['restockFl']);
        }

        // 상품 혜택 검색
        if ($this->search['goodsBenefitSno']) {
            if (is_array($this->search['goodsBenefitSno'])) { // 배열일 경우
                foreach ($this->search['goodsBenefitSno'] as $val) {
                    if ($this->search['goodsBenefitNoneFl'] == 'y') { // 선택한 상품 미지정
                        $tmpWhere[] = 'gbl.benefitSno != ?';
                    } else {
                        $tmpWhere[] = 'gbl.benefitSno = ?';
                    }
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                if ($this->search['goodsBenefitNoneFl'] == 'y') {
                    if($getValue['applyPath'] == '/goods/goods_batch_mileage.php' || $getValue['applyPath'] == '/goods/goods_batch_link.php' || $getValue['applyPath'] == '/goods/goods_batch_stock.php') {
                        $this->arrWhere[] = '((' . implode(' AND ', $tmpWhere) . ') OR ( gbl.benefitSno = \'\' OR gbl.benefitSno IS NULL)) ';
                    } else {
                        $this->arrWhere[] = '((' . implode(' AND ', $tmpWhere) . ') OR ( gbl.benefitSno = \'\' OR gbl.benefitSno IS NULL)) GROUP BY g.goodsNo ';
                    }
                } else {
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                }
                unset($tmpWhere);
            } else { // 기존 단일선택 레거시 보장
                $this->arrWhere[] = 'gbl.benefitSno = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['goodsBenefitSno']);
            }
        } else if(!$this->search['goodsBenefitSno'] && $this->search['goodsBenefitNoneFl'] == 'y') { // 혜택 선택 값 없고 미적용상품일 때
            $this->arrWhere[] = '(gbl.benefitSno  = "" or gbl.benefitSno IS NULL)';
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }


    /**
     * 상품 정보 세팅
     *
     * @param string  $getData       상품정보
     * @param string  $imageType     이미지 타입
     * @param boolean $optionFl      옵션 출력 여부 - true or false (기본 false)
     * @param boolean $couponPriceFl 쿠폰가격 출력 여부 - true or false (기본 false)
     * @param integer $viewWidthSize 실제 출력할 이미지 사이즈 (기본 null)
     */
    protected function setAdminListGoods(&$getData,$addField = null,$mode = null)
    {
        $getValue = Request::get()->toArray();

        //상품 헤택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

        /// 그리드1 - 상품리스트 그리드 설정
        $goodsAdminGrid = \App::load('\\Component\\Goods\\GoodsAdminGrid');
        $goodsAdminGridMode = $goodsAdminGrid->getGoodsAdminGridMode(); // 현재 그리드 페이지 추출
        $this->goodsGridConfigList  = $goodsAdminGrid->getSelectGoodsGridConfigList($goodsAdminGridMode); // 선택항목 추출
        $goodsGridConfigDisplayList = $goodsAdminGrid->getSelectGoodsGridConfigList($goodsAdminGridMode,'display'); // 추가진열항목추가 값 추출(진열리스트, 아이콘, 색상)

        // 그리드2 - addField 데이터 선택항목 + 기본항목 차집합 데이터
        $addFieldGridData = $goodsAdminGrid->getGoodsAdminAddField($this->goodsGridConfigList);

        // 그리드3 - 상품리스트 또는 삭제상품리스트 그리드 선택항 목이 있을 경우 필드 추가
        if($goodsAdminGridMode == 'goods_list' || $goodsAdminGridMode == 'goods_list_delete') {
            if(empty($addFieldGridData) == false || empty($goodsGridConfigDisplayList) == false) {
                // 모드, 차집합 데이터, 추가진열데이터
                $setFieldGrid = $this->setGoodsGridDataAddField($goodsAdminGridMode, $addFieldGridData, $goodsGridConfigDisplayList);
            }
        }

        // 그리드4 - 조회항목추가(그리드 선택항목)
        if($addField) {
            $addField = $addField . $setFieldGrid['addFieldGrid'];
        } else {
            $addField = $setFieldGrid['addFieldGrid'];
        }

        //검색테이블에서 검색 후 상품정보 가져옴
        $strField = "g.goodsNo,g.applyFl,g.goodsNm, g.imageStorage, g.imagePath,g.goodsPrice,g.goodsDisplayFl,g.goodsDisplayMobileFl,g.goodsSellFl,g.goodsSellMobileFl,g.totalStock,g.stockFl,g.soldOutFl,g.regDt,g.modDt,g.delDt,g.applyType,g.applyMsg,g.applyDt,g.scmNo,g.purchaseNo,g.goodsDescription" . $addField;
        $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_GOODS . ' g WHERE goodsNo IN ("'.implode('","',array_column($getData, 'goodsNo')).'")';
        $tmpGoodsData = $this->db->query_fetch($strSQL, null);
        $goodsData = array_combine (array_column($tmpGoodsData, 'goodsNo'),$tmpGoodsData);

        /* 이미지 설정 */
        $strImageSQL = 'SELECT goodsNo,imageName FROM ' . DB_GOODS_IMAGE . ' g  WHERE imageKind = "List" AND goodsNo IN ("'.implode('","',array_column($getData, 'goodsNo')).'")';
        $tmpImageData = $this->db->query_fetch($strImageSQL);
        $imageData = array_combine (array_column($tmpImageData, 'goodsNo'), array_column($tmpImageData, 'imageName'));

        $strScmSQL = 'SELECT scmNo,companyNm FROM ' . DB_SCM_MANAGE . ' g  WHERE scmNo IN ("'.implode('","',array_column($goodsData, 'scmNo')).'")';
        $tmpScmData = $this->db->query_fetch($strScmSQL);
        $scmData = array_combine (array_column($tmpScmData, 'scmNo'), array_column($tmpScmData, 'companyNm'));


        if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
            $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n" AND purchaseNo IN ("' . implode('","', array_column($goodsData, 'purchaseNo')) . '")';
            $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
            $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
        }

        $deliveryData = [];
        if($mode =='delivery' || (in_array('g.deliverySno', $addFieldGridData) == true && $mode == null)) {
            $strDeliverySQL = 'SELECT sdb.sno , sdb.method as deliveryNm  FROM ' . DB_SCM_DELIVERY_BASIC . ' sdb  WHERE sdb.sno IN ("'.implode('","',array_column($goodsData, 'deliverySno')).'")';
            $tmpDeliveryData = $this->db->query_fetch($strDeliverySQL);
            $deliveryData = array_combine (array_column($tmpDeliveryData, 'sno'), array_column($tmpDeliveryData, 'deliveryNm'));
        }
        // 상품 가격 관리, 상품 마일리지/할인 관리, 상품 품절/노출/재고 관리, 상품 이동/복사/삭제 관리 등
        if($mode == 'image' || (in_array('g.brandCd', $addFieldGridData) == true && $mode == null)) {
            $strBrandNmSQL = 'SELECT cateCd, cateNm as brandCd FROM ' . DB_CATEGORY_BRAND . ' WHERE cateCd IN ("' . implode('","', array_column($goodsData, 'brandCd')) . '")';
            $tmpBrandNmData = $this->db->query_fetch($strBrandNmSQL);
            $brandNmData = array_combine (array_column($tmpBrandNmData, 'cateCd'), array_column($tmpBrandNmData, 'brandCd'));
        }

        /* 아이콘 설정 (무제한) */
        $strIconSQL = 'SELECT goodsNo, GROUP_CONCAT( g.goodsIconCd SEPARATOR "||") AS goodsIconCd FROM ' . DB_GOODS_ICON . ' g  WHERE iconKind = "un" AND goodsNo IN ("'.implode('","',array_column($getData, 'goodsNo')).'") GROUP BY goodsNo';
        $tmpIconData = $this->db->query_fetch($strIconSQL);
        $iconDataUn = array_combine (array_column($tmpIconData, 'goodsNo'), array_column($tmpIconData, 'goodsIconCd'));

        /* 아이콘 설정 (기간제한) */
        $strIconSQL = 'SELECT goodsNo, GROUP_CONCAT( g.goodsIconCd SEPARATOR "||") AS goodsIconCd, goodsIconStartYmd, goodsIconEndYmd FROM ' . DB_GOODS_ICON . ' g  WHERE iconKind = "pe" AND goodsNo IN ("'.implode('","',array_column($getData, 'goodsNo')).'") GROUP BY goodsNo';
        $tmpIconData = $this->db->query_fetch($strIconSQL);
        $iconDataPe = array_combine (array_column($tmpIconData, 'goodsNo'), array_column($tmpIconData, 'goodsIconCd'));
        $goodsIconStartYmd = array_combine (array_column($tmpIconData, 'goodsNo'), array_column($tmpIconData, 'goodsIconStartYmd'));
        $goodsIconEndYmd = array_combine (array_column($tmpIconData, 'goodsNo'), array_column($tmpIconData, 'goodsIconEndYmd'));


        foreach ($getData as $key => & $val) {
            if($goodsData[$val['goodsNo']]) $val = $val+$goodsData[$val['goodsNo']];

            //상품 혜택 사용시 해당 정보 가져옴
            // 아이콘 테이블 분리로 인한 추가
            $tmpGoodsIcon = [];
            $iconList = $this->getGoodsDetailIcon($val['goodsNo']);
            foreach ($iconList as $iconKey => $iconVal) {
                if ($iconVal['iconKind'] == 'pe') {
                    $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                }
                if ($iconVal['iconKind'] == 'un') {
                    $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                }
            }

            $val['goodsIconCd'] = $iconDataUn[$val['goodsNo']];
            $val = $goodsBenefit->goodsDataReset($val, $mode);
            $val['goodsIconCdPeriod'] = $this->getGoodsIcon($iconDataPe[$val['goodsNo']]);
            $val['goodsIconCd'] = $this->getGoodsIcon($val['goodsIconCd']);
            $val['goodsBenefitIconCd'] = $this->getGoodsIcon($val['goodsBenefitIconCd']);
            $val['goodsIconStartYmd'] = $goodsIconStartYmd[$val['goodsNo']];
            $val['goodsIconEndYmd'] = $goodsIconEndYmd[$val['goodsNo']];
            $val['imageName']= $imageData[$val['goodsNo']];
            $val['scmNm']= $scmData[$val['scmNo']];
            $val['purchaseNm']= $purchaseData[$val['purchaseNo']];
            $val['deliveryNm']= $deliveryData[$val['deliverySno']];
            $val['brandNm']= $brandNmData[$val['brandCd']];

            // 그리드5 - 그리드 조회 항목에 따른 데이터 재가공
            if($goodsAdminGridMode == 'goods_list' || $goodsAdminGridMode == 'goods_list_delete') {
                if(empty($addFieldGridData) == false || empty($goodsGridConfigDisplayList) == false) {
                    // 그리드 항목 추가에 따른 데이터가공 - 상품정보, 추가 진열(display) 항목 데이터, 추가진열항목 개별 DB 데이터
                    $val = $this->setGoodsGridDataAddFieldConvert($val, $goodsGridConfigDisplayList, $setFieldGrid['goodsDisplayAddDbData']);
                }
            }

            if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {

                if($getValue['timeSaleStartDt'] && $getValue['timeSaleEndDt']) {
                    $addTimeSaleWhere = ' AND (ts.startDt <  "'.$getValue['timeSaleEndDt'].'" AND ts.EndDt > "'.$getValue['timeSaleStartDt'].'")';
                }

                $strScmSQL = 'SELECT sno FROM ' . DB_TIME_SALE . ' ts WHERE FIND_IN_SET('.$val['goodsNo'].', REPLACE(ts.goodsNo,"'.INT_DIVISION.'",","))  AND ts.endDt > "'.date('Y-m-d H:i:s').'"'.$addTimeSaleWhere;
                $tmpScmData = $this->db->query_fetch($strScmSQL,null,false);
                $val['timeSaleSno'] = gd_isset($tmpScmData['sno']);
            }



        }
        unset($imageData, $scmData, $deliveryData, $brandNmData);
    }
    /**
     * 관리자 상품 리스트
     *
     * @param string $mode 일반리스트 인지 레이어 리스트인지 구부 (null or layer) or orderWrite
     * @param integer $pageNum 레이어 리스트의 경우 페이지당 리스트 수
     * @return array 상품 리스트 정보
     */
    public function getAdminListGoods($mode = null, $pageNum = 5)
    {
        gd_isset($this->goodsTable,DB_GOODS);

        // --- 검색 설정
        $getValue = Request::get()->toArray();

        gd_isset($getValue['delFl'], 'n');
        // --- 정렬 설정
        if ($getValue['delFl'] == 'y') {
           $sort = gd_isset($getValue['sort'], 'delDt desc , g.goodsNo desc');
           if ($this->goodsDivisionFl) {
               $join[] = ' LEFT JOIN ' . DB_GOODS . ' as goods ON g.goodsNo = goods.goodsNo ';
           }
       } else {
            $sort = gd_isset($getValue['sort'], 'g.goodsNo desc');
       }
        $this->setSearchGoods($getValue);

        //상품 헤택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefitUse = $goodsBenefit->getConfig();

        //수기주문일시
        /*
        if($mode === 'orderWrite'){
            $this->goodsTable = DB_GOODS;

            $this->setSearchGoodsOrderWrite($param);
        }*/

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], $pageNum);
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        $page = \App::load('\\Component\\Page\\Page', $getValue['page'],0,0,$getValue['pageNum']);
        $page->setCache(true); // 페이지당 리스트 수
        if ($mode != 'layer') {
            $page->setUrl(\Request::getQueryString());
        }

        // 현 페이지 결과
        if (!empty($this->search['cateGoods']) || !empty($this->search['displayTheme'][1])) {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_CATEGORY . ' as gl ON g.goodsNo = gl.goodsNo ';
        }

        if(($getValue['key'] == 'companyNm' && $getValue['keyword']) || strpos($sort, "companyNm") !== false) $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true )  {
            if( $getValue['key'] == 'purchaseNm' && $getValue['keyword']) {
                $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = g.purchaseNo and p.delFl = "n"';
            } else if($getValue['purchaseNoneFl'] =='y') {
                $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = g.purchaseNo';
            }
        }

        //추가정보 검색
        if($getValue['key'] == 'addInfo' && $getValue['keyword']) {
            $addInfoQuery = "(SELECT goodsNo,count(*) as addInfoCnt FROM ".DB_GOODS_ADD_INFO." WHERE infoTitle LIKE concat('%','".$this->db->escape($getValue['keyword'])."','%') GROUP BY goodsNo)";
            $join[] = ' LEFT JOIN ' . $addInfoQuery . ' as gai ON  gai.goodsNo = g.goodsNo';
            $this->arrWhere[] = " addInfoCnt  > 0";
        }

        //상품 혜택 검색
        if (!empty($this->search['goodsBenefitSno']) || $this->search['goodsBenefitNoneFl'] == 'y') {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_BENEFIT . ' as gbl ON g.goodsNo = gbl.goodsNo ';
        }

        //상품 혜택 아이콘 검색
        if ($goodsBenefitUse == 'y' && $this->search['goodsIconCd']) {
            $join[] = 'LEFT JOIN
            (
            select t1.goodsNo,t1.benefitSno,t1.goodsIconCd
            from ' . DB_GOODS_LINK_BENEFIT . ' as t1,
            (select goodsNo, min(linkPeriodStart) as min_start from ' . DB_GOODS_LINK_BENEFIT . ' where ((benefitUseType=\'periodDiscount\' or benefitUseType=\'newGoodsDiscount\') AND linkPeriodStart < NOW() AND linkPeriodEnd > NOW()) or benefitUseType=\'nonLimit\'  group by goodsNo) as t2
            where t1.linkPeriodStart = t2.min_start and t1.goodsNo = t2.goodsNo
            ) as gbs on g.goodsNo = gbs.goodsNo ';
        }

        //상품 아이콘 테이블추가
        if ($this->search['goodsIconCdPeriod'] || $this->search['goodsIconCd']) {
            if ($this->search['goodsIconCdPeriod'] && !$this->search['goodsIconCd']) {
                $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
            } else {
                if ($goodsBenefitUse == 'y'){
                    $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo OR (gbs.benefitSno = gi.benefitSno AND gi.iconKind = \'pr\')';
                }else {
                    $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
                }
            }
            // 검색 조건에 아이콘 검색이 있는 경우 group by 추가
            $this->db->strGroup = "g.goodsNo ";
            $goodsIconStrGroup = " GROUP BY g.goodsNo";
        }

        $this->db->strField = "g.goodsNo";
        // 구매율의 경우 계산 필드를 삽입을 위해 변경
        if($sort == 'orderRate desc') {
            $this->db->strField = "g.goodsNo, round(((g.orderGoodsCnt / g.hitCnt)*100), 2) as orderRate";
        }
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        // 검색 조건에 메인분류 검색이 있는 경우 group by 추가
        if(!empty($this->search['displayTheme'][1])) {
            $this->db->strGroup = "g.goodsNo ";
            $mainDisplayStrGroup = " GROUP BY g.goodsNo";
        }

        //상품 아이콘 기간제한 & 무제한 모두 검색시 index 태우기 위해 UNION 추가
        if ($this->search['goodsIconCdPeriod'] && $this->search['goodsIconCd']) {
            $page->recode['union_start'] = $page->recode['start'] + 10;
            $this->db->strLimit = '0 ,' . $page->recode['union_start'];

            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $this->arrWhere[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            $query = $query2 = $this->db->query_complete();

            $strSQL = 'SELECT goodsNo FROM ((SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query) . ') UNION';

            //무제한 아이콘 검색
            if ($this->search['goodsIconCd']) {
                if ($goodsBenefitUse == 'y') {
                    $query2['where'] = str_replace('gi.goodsIconCd = ? AND gi.iconKind = \'pe\'', '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')', $query2['where']);
                }else{
                    $query2['where'] = str_replace('gi.goodsIconCd = ? AND gi.iconKind = \'pe\'', '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )', $query2['where']);
                }
                foreach ($this->arrBind as $bind_key => $bind_val) {
                    if ($bind_key > 0) {
                        if ($this->search['goodsIconCdPeriod'] != $bind_val) {
                            $this->arrBind[count($this->arrBind)] = $bind_val;
                            $this->arrBind[0] .= 's';
                        }
                    }
                }
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                if ($goodsBenefitUse == 'y') {
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }

                $strSQL .= '(SELECT ' . array_shift($query2) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query2) . '))a order by goodsNo desc LIMIT ' .  $page->recode['start'] . ',' . $getValue['pageNum'];
            }
        } else {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $this->arrWhere[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            //무제한 아이콘
            if ($this->search['goodsIconCd']) {
                if ($goodsBenefitUse == 'y') {
                    $this->arrWhere[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }else{
                    $this->arrWhere[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }

                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query);

        }

        // 빠른 이동/복사/삭제 인 경우 검색이 없으면 리턴
        if ($mode == 'batch' && empty($this->arrWhere)) {
            $data = null;
        } else {
            $data = $this->db->query_fetch($strSQL, $this->arrBind);
            /* 검색 count 쿼리 */
            if($page->hasRecodeCache('total') === false){
                $totalCountSQL = ' SELECT COUNT(g.goodsNo) AS totalCnt FROM ' . $this->goodsTable . ' as g  ' . implode('', $join) . '  WHERE ' . implode(' AND ', $this->arrWhere) . $mainDisplayStrGroup;
                $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind);
            }
            /* 검색 count 쿼리 */
            if ($this->search['goodsIconCdPeriod'] && $this->search['goodsIconCd']) {
                //무제한 아이콘 검색
                if ($this->search['goodsIconCd']) {
                    $this->arrWhere2 = $this->arrWhere;
                    foreach ($this->arrWhere2 as $k => $v) {
                        if(strpos($v, 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'') !== false) {
                            if ($goodsBenefitUse == 'y') {
                                $this->arrWhere2[$k] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                            }else{
                                $this->arrWhere2[$k] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )';
                            }
                        }
                    }
                }

                //상품 아이콘 검색시 카운트
                $totalCountSQL =  ' SELECT COUNT(1) as totalCnt FROM (( SELECT g.goodsNo FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere) . $goodsIconStrGroup. ') UNION';

                if ($this->search['goodsIconCd']) {
                    $totalCountSQL .=  ' ( SELECT g.goodsNo FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere2) . $goodsIconStrGroup. ')) tbl';
                }
            } else {
                // 검색 조건에 메인분류 검색이 있는 경우 아이콘 group by 비움
                if ($mainDisplayStrGroup) {
                    $goodsIconStrGroup = '';
                }
                $totalCountSQL = ' SELECT COUNT(1) as totalCnt FROM ( SELECT g.goodsNo FROM ' . $this->goodsTable . ' as g  ' . implode('', $join) . '  WHERE ' . implode(' AND ', $this->arrWhere) . $mainDisplayStrGroup. $goodsIconStrGroup . ') AS tbl';
            }
            if($page->hasRecodeCache('total') === false) {
                $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind);
            }

            unset($this->arrBind);

            $page->recode['total'] = $dataCount[0]['totalCnt']; //검색 레코드 수

            if (Session::get('manager.isProvider')) { // 전체 레코드 수
                if($page->hasRecodeCache('amount') === false) {
                    $page->recode['amount'] = $this->db->getCount($this->goodsTable, 'goodsNo', 'WHERE delFl=\'' . $getValue['delFl'] . '\'  AND scmNo = \'' . Session::get('manager.scmNo') . '\'');
                }
                $scmWhereString = " AND g.scmNo = '" . (string)Session::get('manager.scmNo') . "'"; // 공급사인 경우
            }  else {
                if($page->hasRecodeCache('amount') === false) {
                    $page->recode['amount'] = $this->db->getCount($this->goodsTable, 'goodsNo', 'WHERE delFl=\'' . $getValue['delFl'] . '\'');
                }
            }

            // 아이콘  설정
            if (empty($data) === false) {
                $this->setAdminListGoods($data,",g.goodsBenefitSetFl, g.optionFl");
            }

            // 상품그리드
            if($mode == null) {
                // 상품리스트 그리드 설정
                $goodsAdminGrid = \App::load('\\Component\\Goods\\GoodsAdminGrid');
                $goodsAdminGridMode = $goodsAdminGrid->getGoodsAdminGridMode();
                $this->goodsGridConfigList = $goodsAdminGrid->getSelectGoodsGridConfigList($goodsAdminGridMode, 'all');
                if (empty($this->goodsGridConfigList) === false) {
                    $getData['goodsGridConfigList'] = $this->goodsGridConfigList;
                    $gridAddDisplayArray = ['best', 'main', 'cate']; // 그리드 추가진열 레이어 노출 항목
                    $getData['goodsGridConfigListDisplayFl'] = false; // 그리드 추가 진열 레이어 노출 여부
                    foreach($gridAddDisplayArray as $displayPassVal) {
                        if(array_key_exists($displayPassVal, $getData['goodsGridConfigList']['display']) === true) {
                            $getData['goodsGridConfigListDisplayFl'] = true; // 그리드 추가 진열 레이어 노출 사용
                            break;
                        }
                    }
                    if($goodsAdminGridMode == 'goods_list') {
                        $getData['goodsGridConfigList']['btn'] = '수정';
                    }
                }

                // 상품 리스트 품절, 노출 PC/mobile, 미노출 PC/mobile 카운트 쿼리
                if($goodsAdminGridMode == 'goods_list') {
                    $dataStateCount = [];
                    $dataStateCountQuery = [
                        'pcDisplayCnt' => " g.goodsDisplayFl='y'",
                        'mobileDisplayCnt' => " g.goodsDisplayMobileFl='y'",
                        'pcNoDisplayCnt' => " g.goodsDisplayFl='n'",
                        'mobileNoDisplayCnt' => " g.goodsDisplayMobileFl='n'",
                    ];
                    foreach ($dataStateCountQuery as $stateKey => $stateVal) {
                        if($page->hasRecodeCache($stateKey)) {
                            $dataStateCount[$stateKey]  = $page->getRecodeCache($stateKey);
                            continue;
                        }
                        $dataStateSQL = " SELECT COUNT(g.goodsNo) AS cnt FROM " . $this->goodsTable . " as g WHERE  " . $stateVal . " AND g.delFl ='n'" . $scmWhereString;
                        $dataStateCount[$stateKey] = $this->db->query_fetch($dataStateSQL)[0]['cnt'];
                        $page->recode[$stateKey] = $dataStateCount[$stateKey];
                    }
                    // 품절의 경우 OR 절 INDEX 경유하지 않기에 별도 쿼리 실행 - DBA
                    //                    if(!\Request::get()->get('__soldOutCnt')) {
                    if($page->hasRecodeCache('soldOutCnt') === false) {
                        $dataStateSoldOutSql = "select sum(cnt) as cnt from ( SELECT count(1) AS cnt FROM  " . $this->goodsTable . "  as g1 WHERE   g1.soldOutFl = 'y' AND g1.delFl ='n' union all SELECT count(1) AS cnt FROM  " . $this->goodsTable . "  as g2 WHERE  g2.soldOutFl = 'n' and g2.stockFl = 'y' AND g2.totalStock <= 0  AND g2.delFl ='n') gQ";
                        $dataStateCount['soldOutCnt'] = $this->db->query_fetch($dataStateSoldOutSql)[0]['cnt'];
                        $page->recode['soldOutCnt'] = $dataStateCount['soldOutCnt'];
                    }
                    else {
                        $dataStateCount['soldOutCnt'] = $page->getRecodeCache('soldOutCnt');
                    }
                    $getData['stateCount'] = $dataStateCount;
                }
            }
        }

        $page->setPage(null,['soldOutCnt','pcDisplayCnt','mobileDisplayCnt','pcNoDisplayCnt','mobileNoDisplayCnt']);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * 관리자 상품 리스트 엑셀 다운로드  generator 사용
     *
     * @param string $mode 일반리스트 인지 레이어 리스트인지 구부 (null or layer)
     * @param integer $pageNum 레이어 리스트의 경우 페이지당 리스트 수
     * @return array 상품 리스트 정보
     */
    public function getAdminListGoodsExcel($getValue,$page,$pageLimit)
    {
        //$page / $pageLimit 해당 정보가 없을경우 튜닝한 업체이므로 기존형태로 반환해줘야함

        gd_isset($this->goodsTable,DB_GOODS);

        $sort = gd_isset($getValue['sort'], 'g.goodsNo desc');

        // 삭제상품 리스트 다운시
        if ($getValue['delFl'] == 'y') {
            if ($this->goodsDivisionFl) {
                $join[] = ' LEFT JOIN ' . DB_GOODS . ' as goods ON g.goodsNo = goods.goodsNo ';
            }
        }

        $this->setSearchGoods($getValue, 'excel');
        if($getValue['goodsNo'] && is_array($getValue['goodsNo'])) {
            $this->arrWhere[] = 'g.goodsNo IN (' . implode(',', $getValue['goodsNo']) . ')';
        }

        // 현 페이지 결과
        if (!empty($this->search['cateGoods'])) {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_CATEGORY . ' as gl ON g.goodsNo = gl.goodsNo ';
        }

        if($getValue['key'] == 'companyNm' && $getValue['keyword']) $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true )  {
            if( $getValue['key'] == 'purchaseNm' && $getValue['keyword']) {
                $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = g.purchaseNo and p.delFl = "n"';
            } else if($getValue['purchaseNoneFl'] =='y') {
                $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = g.purchaseNo';
            }
        }

        //상품 아이콘 테이블추가
        if ($this->search['goodsIconCdPeriod'] || $this->search['goodsIconCd']) {
            $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';

            // 검색 조건에 아이콘 검색이 있는 경우 group by 추가
            $this->db->strGroup = "g.goodsNo ";
            $goodsIconStrGroup = " GROUP BY g.goodsNo";
        }

        if($page =='0') {
            if (empty($join) == false) {
                $tmpJoinQuery = implode('', $join);
            }

            //상품 아이콘 검색시 카운트
            if ($this->search['goodsIconCdPeriod'] || $this->search['goodsIconCd']) {
                $strSQL = ' SELECT COUNT(tbl.goodsNo) as cnt FROM ( SELECT g.goodsNo, COUNT(g.goodsNo) AS cnt FROM ' . $this->goodsTable . ' as g ' . $tmpJoinQuery .' WHERE ' . implode(' AND ', $this->arrWhere) . $goodsIconStrGroup . ') AS tbl';
            } else {
                $strSQL = ' SELECT COUNT(g.goodsNo) AS cnt FROM ' . $this->goodsTable . ' as g ' . $tmpJoinQuery .' WHERE ' . implode(' AND ', $this->arrWhere);
            }
            $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $result['totalCount'] = $res['cnt']; // 전체
        }

        if($pageLimit) {
            $this->db->strField = "g.goodsNo";
        } else {
            $this->db->strField = "g.*";
        }
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        if($pageLimit) $this->db->strLimit = (($page * $pageLimit)) . "," . $pageLimit;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query) ;

        $preSQL =  $this->db->prepare($strSQL);
        $this->db->bind_param($preSQL, $this->arrBind);
        $goodsSQL = $this->db->getBindingQueryString($strSQL, $this->arrBind);
        if($pageLimit)  $result['goodsList'] = $this->db->query_fetch_generator('SELECT g.* FROM ('.$goodsSQL.') gs INNER JOIN '.DB_GOODS.' as g ON g.goodsNo = gs.goodsNo');
        else $result = $this->db->query_fetch('SELECT g.* FROM ('.$goodsSQL.') gs INNER JOIN '.DB_GOODS.' as g ON g.goodsNo = gs.goodsNo');

        return $result;
    }

    /**
     * 관리자 상품 옵션 리스트
     *
     * @param string $mode 일반리스트 인지 레이어 리스트인지 구부 (null or layer)
     * @return array 상품 옵션 리스트 정보
     */
    public function getAdminListOption($mode = null)
    {
        $getValue = Request::get()->toArray();

        // --- 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableManageGoodsOption');

        //검색설정
        /* @formatter:off */
        $this->search['sortList'] = array(
            'mgo.regDt desc' => __('등록일 ↓'),
            'mgo.regDt asc' => __('등록일 ↑'),
            'mgo.modDt desc' => __('수정일 ↓'),
            'mgo.modDt asc' => __('수정일 ↑'),
            'optionManageNm asc' => __('옵션 관리명 ↓'),
            'optionManageNm desc' => __('옵션 관리명 ↑'),
            'companyNm asc' => __('공급사 ↓'),
            'companyNm desc' => __('공급사 ↑')
        );
        /* @formatter:on */


        // --- 검색 설정
        $this->search['sort'] = gd_isset($getValue['sort'], 'mgo.regDt desc');
        $this->search['detailSearch'] = gd_isset($getValue['detailSearch']);
        $this->search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'], '-1');
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['optionDisplayFl'] = gd_isset($getValue['optionDisplayFl'], '');

        $this->search['scmFl'] = gd_isset($getValue['scmFl'], Session::get('manager.isProvider') ? 'n' : 'all');
        $this->search['scmNo'] = gd_isset($getValue['scmNo'], (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm'],(string) Session::get('manager.companyNm'));
        $this->search['sno'] = gd_isset($getValue['sno']);

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['optionDisplayFl'][$this->search['optionDisplayFl']] = $this->checked['scmFl'][$getValue['scmFl']] = "checked='checked'";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";


        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1] && $mode != 'layer') {
            $this->arrWhere[] = 'mgo.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }


        if ($this->search['scmFl'] != 'all') {
            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'mgo.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'mgo.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], (string)Session::get('manager.scmNo'));
            }

        }

        if ($this->search['sno']) {
            if (is_array($this->search['sno'])) {
                foreach ($this->search['sno'] as $val) {
                    $tmpWhere[] = 'mgo.sno = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'mgo.sno = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['sno'], $this->search['sno']);
            }
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('optionManageNm', 'optionName');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldType[$keyNm], $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType[$this->search['key']], $this->search['keyword']);
            }
        }
        // 옵션표시 검색
        if ($this->search['optionDisplayFl']) {
            $this->arrWhere[] = 'optionDisplayFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['optionDisplayFl'], $this->search['optionDisplayFl']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 정렬 설정
        $sort = $this->search['sort'];

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], 10);
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }


        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        if (Session::get('manager.isProvider')) $strSQL = ' SELECT COUNT(sno) AS cnt FROM ' . DB_MANAGE_GOODS_OPTION . ' WHERE scmNo = \'' . Session::get('manager.scmNo') . '\'';
        else $strSQL = ' SELECT COUNT(sno) AS cnt FROM ' . DB_MANAGE_GOODS_OPTION;
        $res = $this->db->query_fetch($strSQL, null, false);

        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수

        // 현 페이지 결과
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = mgo.scmNo ';
        $this->db->strField = "mgo.*,s.companyNm as scmNm";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        if($mode !='layer') $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(sno) AS cnt FROM ' . DB_MANAGE_GOODS_OPTION . ' as mgo ' . $this->db->strJoin;
        if($this->db->strWhere) {
            $strSQL .= ' WHERE ' . $this->db->strWhere;
        }

        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_GOODS_OPTION . ' as mgo ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * 관리자 상품 아이콘 리스트
     *
     * @return array 상품 리스트 정보
     */
    public function getAdminListGoodsIcon()
    {
        $getValue = Request::get()->toArray();

        //검색설정
        /* @formatter:off */
        $this->search['sortList'] = array(
            'iconNm asc' => __('아이콘 이름 ↓'),
            'iconNm desc' => __('아이콘 이름 ↑'),
            'regDt desc' => __('등록일 ↓'),
            'regDt asc' => __('등록일 ↑'),
            'modDt asc' => __('수정일 ↓'),
            'modDt desc' => __('수정일 ↑')
        );
        /* @formatter:on */

        $this->search['sort'] = gd_isset($getValue['sort'], 'regDt desc');
        $this->search['iconNm'] = gd_isset($getValue['iconNm']);
        $this->search['iconPeriodFl'] = gd_isset($getValue['iconPeriodFl']);
        $this->search['iconUseFl'] = gd_isset($getValue['iconUseFl']);

        $this->search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'], '-1');

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));
        }

        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";

        $this->checked['iconPeriodFl'][$this->search['iconPeriodFl']] = $this->checked['iconUseFl'][$this->search['iconUseFl']] = 'checked="checked"';


        //처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 아이콘 이름 검색
        if ($this->search['iconNm']) {
            $this->arrWhere[] = 'iconNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['iconNm']);
        }

        // 상품 아이콘 기간 사용 여부 검색
        if ($this->search['iconPeriodFl']) {
            $this->arrWhere[] = 'iconPeriodFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['iconPeriodFl']);
        }

        // 상품 아이콘 사용 여부 검색
        if ($this->search['iconUseFl']) {
            $this->arrWhere[] = 'iconUseFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['iconUseFl']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 정렬 설정
        $sort = $this->search['sort'];

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $strSQL = 'SELECT count(*) as cnt  FROM ' . DB_MANAGE_GOODS_ICON ;
        list($result) = $this->db->query_fetch($strSQL);
        $totalCnt = $result['cnt'];

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $totalCnt; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = 'SELECT count(*) as cnt  FROM ' . DB_MANAGE_GOODS_ICON;
        if($this->db->strWhere) {
            $strSQL .= ' WHERE ' . $this->db->strWhere;
        }
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $data = $this->getManageGoodsIconInfo();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * 관리자 상품 순서 변경 리스트
     *
     * @param string $cateMode category, brand 구분
     * @param boolean $isPage 페이징 여부
     * @return array 상품 리스트 정보
     */
    public function getAdminListSort($cateMode = 'category', $isPage = true, $linkUse = 'n')
    {
        gd_isset($this->goodsTable,DB_GOODS);

        $getValue = Request::get()->toArray();

        // 카테고리 종류에 따른 설정
        if ($cateMode == 'category') {
            $dbTable = DB_GOODS_LINK_CATEGORY;
        } else {
            $dbTable = DB_GOODS_LINK_BRAND;
        }

        $goodsWhere = "g.delFl = 'n' AND g.applyFl = 'y'";

        // --- 검색 설정
        $this->search['cateGoods'] = ArrayUtils::last(gd_isset($getValue['cateGoods']));

        // 카테고리 검색
        if ($this->search['cateGoods']) {
            $this->arrWhere[] = 'gl.cateCd = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['cateGoods']);
            if ($linkUse == 'n') {
                $this->arrWhere[] = 'gl.cateLinkFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', 'y');
            }

        } else {
            return '';
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }


        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);
        gd_isset($getValue['sort'], "goodsNo desc");

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $sort[] = "gl.fixSort desc , gl.goodsSort desc";
        if ($getValue['sort']) $sort[] = $getValue['sort'];


        // 현 페이지 결과
        $join[] = ' INNER JOIN ' . $this->goodsTable . ' as g ON gl.goodsNo = g.goodsNo AND '.$goodsWhere;

        $this->db->strField = 'gl.goodsSort, gl.fixSort, gl.goodsNo';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = implode(',', $sort);
        if ($isPage) $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $dbTable . ' gl ' . implode(' ', $query);

        // 빠른 이동/복사/삭제 인 경우 검색이 없으면 리턴
        if (empty($this->arrWhere) === true) {
            $data = null;
        } else {
            $data = $this->db->query_fetch($strSQL, $this->arrBind);

            /* 검색 count 쿼리 */
            $totalCountSQL =  ' SELECT COUNT(gl.goodsNo) AS totalCnt FROM ' . $dbTable . ' as gl '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere);
            $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind,false);
            unset($this->arrBind, $this->arrWhere);

            // 검색 레코드 수
            $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수
            $page->setPage();

            // 아이콘  설정
            if (empty($data) === false) {
                $this->setAdminListGoods($data,",g.goodsBenefitSetFl");
            }

        }

        $getData['fixCount'] = $this->db->getCount($dbTable, 'goodsNo', 'WHERE fixSort > 0 AND cateLinkFl = "y" AND cateCd=\'' .$this->search['cateGoods'] . '\'');

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);

        return $getData;
    }

    /**
     * 관리자 상품 순서 변경 처리
     *
     * @param string $getData 변경 데이타
     */
    public function setGoodsSortChange($getData)
    {
        $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $navi = $displayConfig->getDateNaviDisplay();
        $getData['goodsNo'] = array_values($getData['goodsNoData']);


        // 데이타 체크
        if (isset($getData['goodsNo']) === false || isset($getData['cateCd']) === false) {
            throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
        }

        // 카테고리 종류에 따른 설정
        if ($getData['cateMode'] == 'category') {
            $dbTable = DB_GOODS_LINK_CATEGORY;
        } else {
            $dbTable = DB_GOODS_LINK_BRAND;
        }

        $addWhere = '';
        if ($getData['cateMode'] == 'category' || ($getData['cateMode'] == 'brand' && $navi['data']['brand']['linkUse'] != 'y')) {
            $addWhere = ' AND cateLinkFl = "y" ';
        }

        //기존 잘못연결된 n 링크 삭제
        /*$strSQL = 'DELETE FROM ' . $dbTable . ' where cateLinkFl = "n" AND cateCd=\'' . $getData['cateCd'] . '\'';
        $this->db->query($strSQL);*/

        if ($getData['sortAutoFl'] == 'y') { //자동 진열인 경우

            if ($getData['pageNow'] > 1) {
                $strSQL = 'UPDATE ' . $dbTable . ' SET goodsSort = 0  where cateCd=\'' . $getData['cateCd'] . '\'';
                $this->db->query($strSQL);
            } else {
                $strSQL = 'UPDATE ' . $dbTable . ' SET goodsSort = 0 ,fixSort = 0  where cateCd=\'' . $getData['cateCd'] . '\'';
                $this->db->query($strSQL);
            }

            if ($getData['sortFix']) {
                foreach ($getData['sortFix'] as $key => $val) {
                    $tmpField[] = 'WHEN \'' . $val . '\' THEN \'' . sprintf('%05s', $key+1) . '\'';
                }

                $strSetSQL = 'SET @newSort := 0;';
                $this->db->query($strSetSQL);

                $sortField = ' CASE goodsNo ' . implode(' ', $tmpField) . ' ELSE \'\' END ';

                $strSQL = 'UPDATE '.$dbTable.' SET fixSort = ( @newSort := @newSort+1 )
                            WHERE  cateCd="'.$getData['cateCd'].'"  AND (goodsNo = \'' . implode('\' OR goodsNo = \'', $getData['sortFix']) . '\') ' . $addWhere . ' ORDER BY (' . $sortField . ') DESC';
                $this->db->query($strSQL);

            }

        } else { //수동정렬인 경우

            //1.해당 카테고리 진열상품 재 정렬
            $strSetSQL = 'SET @newSort := 0;';
            $this->db->query($strSetSQL);

            $strSQL = 'UPDATE '.$dbTable.' SET goodsSort = ( @newSort := @newSort+1 )
                                WHERE goodsNo
                                IN
                                (
                                  SELECT goodsNo
                                  FROM '.$this->goodsTable.'
                                  WHERE delFl = "n" AND applyFl = "y"

                                ) AND cateCd="'.$getData['cateCd'].'" ' . $addWhere . ' ORDER BY goodsSort ASC';

            $this->db->query($strSQL);


            //2.해당 카테고리 미진열 상품 정렬 수정
            $strSQL = "SELECT @newSort";
            $maxGoodsSort = $this->db->query_fetch($strSQL,null,false)['@newSort'];

            $strSetSQL = 'SET @newSort := '.$maxGoodsSort.';';
            $this->db->query($strSetSQL);

            $strSQL = 'UPDATE '.$dbTable.' SET goodsSort = ( @newSort := @newSort+1 )
                                WHERE goodsNo
                                NOT IN
                                (
                                  SELECT goodsNo
                                  FROM '.$this->goodsTable.'
                                  WHERE delFl = "n" AND applyFl = "y"
                                ) AND cateCd="'.$getData['cateCd'].'" ORDER BY goodsSort ASC';

            $this->db->query($strSQL);

            //3.현재 페이지 카테고리 정렬값 변경
            $totalGoodsSort = $getData['totalGoodsSort']+1;

            $nextSort =  $prevSort = [];
            $fixCount = count($getData['sortFix']);
            foreach($getData['goodsSort'] as  $k => $v) {

                $strWhere = "goodsNo = '".$getData['goodsNo'][$k]."' AND cateCd='".$getData['cateCd']."'";

                if(is_array($getData['sortFix']) && in_array($getData['goodsNo'][$k],$getData['sortFix'])) {
                    $fixSort = $fixCount;
                    $fixCount--;
                } else {
                    $fixSort = "0";
                }

                $this->db->set_update_db($dbTable, array("goodsSort = '".($totalGoodsSort-$v)."',fixSort = '".$fixSort."'"), $strWhere);

                if($v < $getData['startNum']) {
                    $prevSort[$v] = $getData['goodsNo'][$k];
                } else if ($v >= $getData['startNum']+$getData['pagePnum']) {
                    $nextSort[$v] = $getData['goodsNo'][$k];
                }
            }

            ksort($prevSort);
            krsort($nextSort);

            if($prevSort) {
                $sortCnt = 0;
                foreach($prevSort as $k => $v) {
                    $strWhere = "goodsNo NOT IN ('" . implode("','", $prevSort) . "') AND cateCd='".$getData['cateCd']."' AND goodsSort <= ".($totalGoodsSort-$k)." AND goodsSort >= ".($totalGoodsSort-$sortCnt-$getData['pagePnum']);
                    $this->db->set_update_db($dbTable, array("goodsSort = goodsSort-1 "), $strWhere);
                    $sortCnt++;
                }
            }

            if($nextSort) {
                $sortCnt = 0;
                foreach($nextSort as $k => $v) {
                    $strWhere = "goodsNo NOT IN ('" . implode("','", $nextSort) . "') AND cateCd='".$getData['cateCd']."' AND goodsSort < ".($totalGoodsSort-($getData['pagePnum']-$sortCnt))." AND goodsSort >= ".($totalGoodsSort-$k);
                    $this->db->set_update_db($dbTable, array("goodsSort = goodsSort+1 "), $strWhere);
                    $sortCnt++;
                }
            }
        }

        if ($getData['pageNow'] > 1 && $getData['sortFix']) {
            $strSQL = "UPDATE " . $dbTable . " SET fixSort = fixSort+".count($getData['sortFix'])."  where cateCd='" . $getData['cateCd'] . "' AND fixSort > 0 AND goodsNo NOT IN ('" . implode("','", $getData['sortFix']) . "') ";
            $this->db->query($strSQL);
        }
    }

    /**
     * 관리자 상품 일괄 관리 리스트 - 상품기준
     *
     * @param string $mode 리스트에 이미지 출력여부 (null or image)
     * @return array 상품 리스트 정보
     */
    public function getAdminListBatch($mode = null)
    {

        // --- 검색 설정
        $getValue = Request::get()->toArray();

        gd_isset($getValue['delFl'], 'n');
        $sort = gd_isset($getValue['sort'], 'g.goodsNo desc');
        $changeGoodsTableFl = false;

        if ($mode == 'restock') {
            if ($this->goodsTable == DB_GOODS_SEARCH) {
                $changeGoodsTableFl = true;
                $this->goodsTable = DB_GOODS;
            }
            gd_isset($getValue['pageNum'], 20);
        }

        $this->setSearchGoods($getValue);

        switch ($mode) {
            case 'icon':
                $addField = ', g.goodsColor ';
                break;
            case 'delivery':
                $addField = ",g.deliverySno";
                break;
            case 'restock':
                if (gd_isset($getValue['pagelink'])) {
                    $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
                }
                $addField = ",g.restockFl";
                break;
            case 'image' :
                $addField = ", g.benefitUseType, g.newGoodsRegFl, g.newGoodsDate, g.newGoodsDateFl, g.periodDiscountStart, g.periodDiscountEnd, g.fixedGoodsDiscount, g.goodsDiscountGroupMemberInfo";
                break;
            default :
                $addField = '';
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page'],0,0,$getValue['pageNum']);
        $page->setCache(true); // 페이지당 리스트 수
        if ($mode != 'layer') {
            $page->setUrl(\Request::getQueryString());
        }

        //상품 헤택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefitUse = $goodsBenefit->getConfig();

        // 현 페이지 결과
        if (!empty($this->search['cateGoods'])) {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_CATEGORY . ' as gl ON g.goodsNo = gl.goodsNo ';
        }

        if(($getValue['key'] == 'companyNm' && $getValue['keyword']) || strpos($sort, "companyNm") !== false) $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true )  {
            if($getValue['key'] == 'purchaseNm' && $getValue['keyword']) {
                $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = g.purchaseNo and p.delFl = "n"';
            } else if($getValue['purchaseNoneFl'] =='y') {
                $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = g.purchaseNo';
            }
        }

        //상품 혜택 검색
        if (!empty($this->search['goodsBenefitSno'])|| $this->search['goodsBenefitNoneFl'] == 'y') {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_BENEFIT . ' as gbl ON gbl.goodsNo = g.goodsNo ';
        }

        //상품 혜택 아이콘 검색
        if ($goodsBenefitUse == 'y' && $this->search['goodsIconCd']) {
            $join[] = 'LEFT JOIN
            (
            select t1.goodsNo,t1.benefitSno,t1.goodsIconCd
            from ' . DB_GOODS_LINK_BENEFIT . ' as t1,
            (select goodsNo, min(linkPeriodStart) as min_start from ' . DB_GOODS_LINK_BENEFIT . ' where ((benefitUseType=\'periodDiscount\' or benefitUseType=\'newGoodsDiscount\') AND linkPeriodStart < NOW() AND linkPeriodEnd > NOW()) or benefitUseType=\'nonLimit\'  group by goodsNo) as t2
            where t1.linkPeriodStart = t2.min_start and t1.goodsNo = t2.goodsNo
            ) as gbs on g.goodsNo = gbs.goodsNo ';
        }

        //상품 아이콘 테이블추가
        if ($this->search['goodsIconCdPeriod'] || $this->search['goodsIconCd']) {
            if ($this->search['goodsIconCdPeriod'] && !$this->search['goodsIconCd']) {
                $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
            } else {
                if ($goodsBenefitUse == 'y'){
                    $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo OR (gbs.benefitSno = gi.benefitSno AND gi.iconKind = \'pr\')';
                }else{
                    $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
                }

            }
            // 검색 조건에 아이콘 검색이 있는 경우 group by 추가
            $this->db->strGroup = "g.goodsNo ";
            $goodsIconStrGroup = " GROUP BY g.goodsNo";
        }

        //추가정보 검색
        if($getValue['key'] == 'addInfo' && $getValue['keyword']) {
            $addInfoQuery = "(SELECT goodsNo,count(*) as addInfoCnt FROM ".DB_GOODS_ADD_INFO." WHERE infoValue LIKE concat('%','".$this->db->escape($getValue['keyword'])."','%') GROUP BY goodsNo)";
            $join[] = ' LEFT JOIN ' . $addInfoQuery . ' as gai ON  gai.goodsNo = g.goodsNo';
            $this->arrWhere[] = " addInfoCnt  > 0";
        }

        if(strpos($sort, "costPrice") !== false || strpos($sort, "fixedPrice") !== false) $join[] = ' INNER JOIN ' . DB_GOODS . ' as gs ON gs.goodsNo = g.goodsNo ';

        $this->arrWhere[] = "g.applyFl !='a'";

        $this->db->strField = "g.goodsNo";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $this->db->strOrder = $sort;

        // 검색 전체를 일괄 수정을 할경우 필요한 값
        $getData['batchAll']['join'] = Encryptor::encrypt($this->db->strJoin);
        $getData['batchAll']['where'] = Encryptor::encrypt($this->db->strWhere);
        $getData['batchAll']['bind'] = Encryptor::encrypt(json_encode($this->arrBind, JSON_UNESCAPED_UNICODE));

        //상품 아이콘 기간제한 & 무제한 모두 검색시 index 태우기 위해 UNION 추가
        if ($this->search['goodsIconCdPeriod'] && $this->search['goodsIconCd']) {
            $page->recode['union_start'] = $page->recode['start'] + 10;
            $this->db->strLimit = '0 ,' . $page->recode['union_start'];

            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $this->arrWhere[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            $query = $query2 = $this->db->query_complete();

            $strSQL = 'SELECT goodsNo FROM ((SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query) . ') UNION';

            //무제한 아이콘 검색
            if ($this->search['goodsIconCd']) {
                if ($goodsBenefitUse == 'y'){
                    $query2['where'] = str_replace('gi.goodsIconCd = ? AND gi.iconKind = \'pe\'', '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')', $query2['where']);
                }else{
                    $query2['where'] = str_replace('gi.goodsIconCd = ? AND gi.iconKind = \'pe\'', '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )', $query2['where']);
                }


                foreach ($this->arrBind as $bind_key => $bind_val) {
                    if ($bind_key > 0) {
                        if ($this->search['goodsIconCdPeriod'] != $bind_val) {
                            $this->arrBind[count($this->arrBind)] = $bind_val;
                            $this->arrBind[0] .= 's';
                        }
                    }
                }
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                if ($goodsBenefitUse == 'y') {
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }

                $strSQL .= '(SELECT ' . array_shift($query2) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query2) . '))a order by goodsNo desc LIMIT ' .  $page->recode['start'] . ',' . $getValue['pageNum'];
            }
        } else {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $this->arrWhere[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            //무제한 아이콘
            if ($this->search['goodsIconCd']) {
                if ($goodsBenefitUse == 'y'){
                    $this->arrWhere[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }else{
                    $this->arrWhere[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }

                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query);
        }

        $data = $this->db->query_fetch($strSQL, $this->arrBind);    // 상품코드만 가져옴

        /* 검색 count 쿼리 */
        if ($this->search['goodsIconCdPeriod'] && $this->search['goodsIconCd']) {

            //무제한 아이콘 검색
            if ($this->search['goodsIconCd']) {
                $this->arrWhere2 = $this->arrWhere;
                foreach ($this->arrWhere2 as $k => $v) {
                    if(strpos($v, 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'') !== false) {
                        if ($goodsBenefitUse == 'y'){
                            $this->arrWhere2[$k] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\'  OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                        }else{
                            $this->arrWhere2[$k] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\'  )';
                        }

                    }
                }
            }

            //상품 아이콘 검색시 카운트
            $totalCountSQL =  ' SELECT COUNT(1) as totalCnt FROM (( SELECT g.goodsNo FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere) . $goodsIconStrGroup. ') UNION';

            if ($this->search['goodsIconCd']) {
                $totalCountSQL .=  ' ( SELECT g.goodsNo FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere2) . $goodsIconStrGroup. ')) tbl';
            }

        } else {
            $totalCountSQL =  ' SELECT COUNT(1) as totalCnt FROM ( SELECT g.goodsNo FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere) . $goodsIconStrGroup. ') tbl';
        }
        $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind,false);

        unset($this->arrBind);

        $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수

        if($page->hasRecodeCache('amount') === false) {
            if (Session::get('manager.isProvider') || $mode === 'delivery') { // 전체 레코드 수
                $page->recode['amount'] = $this->db->getCount($this->goodsTable, 'goodsNo', 'WHERE applyFl !="a" AND delFl=\'' . $getValue['delFl'] . '\'  AND scmNo = \'' . Session::get('manager.scmNo') . '\'');
            } else {
                $page->recode['amount'] = $this->db->getCount($this->goodsTable, 'goodsNo', 'WHERE applyFl !="a" AND  delFl=\'' . $getValue['delFl'] . '\'');
            }
        }

        $page->setPage();

        // 아이콘  설정
        if (empty($data) === false) {
            $this->setAdminListGoods($data,",g.brandCd, g.fixedPrice,g.mileageFl, g.mileageGroup, g.mileageGoods,g.mileageGoodsUnit, g.costPrice,g.goodsDiscountFl,g.goodsDiscount,g.goodsDiscountUnit, g.goodsDiscountGroup,naverFl,g.goodsBenefitSetFl,paycoFl,daumFl".$addField,$mode);
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        unset($this->arrBind);

        if ($mode == 'icon') {
            // 상품 아이콘 설정
            $this->db->strField = implode(', ', DBTableField::setTableField('tableManageGoodsIcon', null, 'iconUseFl'));
            $this->db->strWhere = 'iconUseFl = \'y\'';
            $this->db->strOrder = 'sno DESC';
            $getData['icon'] = $this->getManageGoodsIconInfo();
        }

        if ($mode == 'restock' && $changeGoodsTableFl) {
            $this->goodsTable = DB_GOODS_SEARCH;
        }


        return $getData;
    }

    /**
     * 관리자 상품 일괄 관리 리스트 - 옵션 기준
     *
     * @param string $mode 리스트에 이미지 출력여부 (null or image)
     * @return array 상품 리스트 정보
     */
    public function getAdminListOptionBatch($mode = null)
    {
        gd_isset($this->goodsTable,DB_GOODS);

        // --- 검색 설정
        $getValue = Request::get()->toArray();


        gd_isset($getValue['delFl'], 'n');
        $sort = gd_isset($getValue['sort'], 'g.goodsNo desc');

        $this->setSearchGoods($getValue);


        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page'],0,0,$getValue['pageNum']);
        $page->setCache(true); // 페이지당 리스트 수

        //상품 헤택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefitUse = $goodsBenefit->getConfig();

        // 현 페이지 결과
        $join[] = ' LEFT JOIN (SELECT * FROM ' . DB_GOODS_OPTION . ' ORDER BY goodsNo DESC, optionNo ASC) as go ON go.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' as gta ON go.goodsNo = gta.goodsNo ';


        if (!empty($this->search['cateGoods'])) {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_CATEGORY . ' as gl ON go.goodsNo = gl.goodsNo ';
        }

        //상품 혜택 검색
        if (!empty($this->search['goodsBenefitSno'])|| $this->search['goodsBenefitNoneFl'] == 'y') {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_BENEFIT . ' as gbl ON go.goodsNo = gbl.goodsNo ';
            $sort .= ' , go.optionNo ASC'; // 혜택 join 시 정렬 추가
        }

        //상품 혜택 아이콘 검색
        if ($goodsBenefitUse == 'y' && $this->search['goodsIconCd']) {
            $join[] = 'LEFT JOIN
            (
            select t1.goodsNo,t1.benefitSno,t1.goodsIconCd
            from ' . DB_GOODS_LINK_BENEFIT . ' as t1,
            (select goodsNo, min(linkPeriodStart) as min_start from ' . DB_GOODS_LINK_BENEFIT . ' where ((benefitUseType=\'periodDiscount\' or benefitUseType=\'newGoodsDiscount\') AND linkPeriodStart < NOW() AND linkPeriodEnd > NOW()) or benefitUseType=\'nonLimit\'  group by goodsNo) as t2
            where t1.linkPeriodStart = t2.min_start and t1.goodsNo = t2.goodsNo
            ) as gbs on g.goodsNo = gbs.goodsNo ';
        }

        //상품 아이콘 테이블추가
        if ($this->search['goodsIconCdPeriod'] || $this->search['goodsIconCd']) {
            if ($this->search['goodsIconCdPeriod'] && !$this->search['goodsIconCd']) {
                $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
            } else {
                if ($goodsBenefitUse == 'y'){
                    $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo OR (gbs.benefitSno = gi.benefitSno AND gi.iconKind = \'pr\')';
                }else{
                    $join[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
                }

            }
            $sort .= ', go.optionNo asc'; // 아이콘 검색 시 정렬 추가
        }

        $this->arrWhere[] = "g.applyFl !='a'";

        //추가정보 검색
        if($getValue['key'] == 'addInfo' && $getValue['keyword']) {
            $addInfoQuery = "(SELECT goodsNo,count(*) as addInfoCnt FROM ".DB_GOODS_ADD_INFO." WHERE infoValue LIKE concat('%','".$this->db->escape($getValue['keyword'])."','%') GROUP BY goodsNo)";
            $join[] = ' LEFT JOIN ' . $addInfoQuery . ' as gai ON  gai.goodsNo = g.goodsNo';
            $this->arrWhere[] = " addInfoCnt  > 0";
        }
        if(($getValue['key'] == 'companyNm' && $getValue['keyword']) || strpos($sort, "companyNm") !== false) $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';

        $this->db->strField = "STRAIGHT_JOIN go.sno,go.optionNo,go.optionValue1,go.optionValue2,go.optionValue3,go.optionValue4,go.optionValue5,go.optionPrice,go.optionCostPrice,go.goodsNo,go.optionViewFl,go.optionSellFl,go.stockCnt,go.optionSellFl,go.optionSellCode,go.optionDeliveryFl,go.optionDeliveryCode,go.sellStopFl,go.sellStopStock,go.confirmRequestFl,go.confirmRequestStock,g.optionFl";
        $this->db->strField .= ",g.goodsCd,gta.taxFreeFl,gta.fixedPrice,gta.costPrice,gta.commission,gta.goodsModelNo,g.brandCd,g.makerNm,g.mileageFl,gta.goodsDiscountFl,gta.goodsBenefitSetFl,g.deliverySno";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 전체를 일괄 수정을 할경우 필요한 값
        $getData['batchAll']['join'] = Encryptor::encrypt($this->db->strJoin);
        $getData['batchAll']['where'] = Encryptor::encrypt($this->db->strWhere);
        $getData['batchAll']['bind'] = Encryptor::encrypt(json_encode($this->arrBind));

        //상품 아이콘 기간제한 & 무제한 모두 검색시 index 태우기 위해 UNION 추가
        if ($this->search['goodsIconCdPeriod'] && $this->search['goodsIconCd']) {
            $page->recode['union_start'] = $page->recode['start'] + 10;
            $this->db->strLimit = '0 ,' . $page->recode['union_start'];

            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $this->arrWhere[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            $query = $query2 = $this->db->query_complete();

            $strSQL = '(SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query) . ') UNION';

            //무제한 아이콘 검색
            if ($this->search['goodsIconCd']) {
                if ($goodsBenefitUse == 'y'){
                    $query2['where'] = str_replace('gi.goodsIconCd = ? AND gi.iconKind = \'pe\'', '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')', $query2['where']);
                }else{
                    $query2['where'] = str_replace('gi.goodsIconCd = ? AND gi.iconKind = \'pe\'', '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )', $query2['where']);
                }


                foreach ($this->arrBind as $bind_key => $bind_val) {
                    if ($bind_key > 0) {
                        if ($this->search['goodsIconCdPeriod'] != $bind_val) {
                            $this->arrBind[count($this->arrBind)] = $bind_val;
                            $this->arrBind[0] .= 's';
                        }
                    }
                }
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                if ($goodsBenefitUse == 'y') {
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }

                $strSQL .= '(SELECT ' . array_shift($query2) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query2) . ') order by goodsNo desc, optionNo asc LIMIT ' .  $page->recode['start'] . ',' . $getValue['pageNum'];
            }
        } else {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

            //기간제한 아이콘
            if ($this->search['goodsIconCdPeriod']) {
                $this->arrWhere[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCdPeriod']);
                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            //무제한 아이콘
            if ($this->search['goodsIconCd']) {
                if ($goodsBenefitUse == 'y'){
                    $this->arrWhere[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }else{
                    $this->arrWhere[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\' )';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsIconCd']);
                }

                $this->db->strWhere = implode(' AND ', $this->arrWhere);
            }

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query);
        }

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        /* 검색 count 쿼리 */
        $join[0] = ' LEFT JOIN ' . DB_GOODS_OPTION . ' go ON go.goodsNo = g.goodsNo LEFT JOIN '.DB_GOODS.' gt ON go.goodsNo = gt.goodsNo ';
        if ($this->search['goodsIconCdPeriod'] && $this->search['goodsIconCd']) {

            //무제한 아이콘 검색
            if ($this->search['goodsIconCd']) {
                $this->arrWhere2 = $this->arrWhere;
                foreach ($this->arrWhere2 as $k => $v) {
                    if(strpos($v, 'gi.goodsIconCd = ? AND gi.iconKind = \'pe\'') !== false) {
                        if ($goodsBenefitUse == 'y'){
                                $this->arrWhere2[$k] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\'  OR gi.goodsIconCd = ? AND gi.iconKind = \'pr\')';
                        }else{
                            $this->arrWhere2[$k] = '(gi.goodsIconCd = ? AND gi.iconKind = \'un\'  )';
                        }
                    }
                }
            }

            //상품 아이콘 검색시 카운트
            $searchCountSQL =  ' SELECT COUNT(sno) AS totalCnt FROM (( SELECT go.sno FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere) . ') UNION';

            if ($this->search['goodsIconCd']) {
                $searchCountSQL .=  ' ( SELECT go.sno FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere2) . ')) tbl';
            }
        } else {
            $searchCountSQL =  ' SELECT COUNT(*) AS totalCnt FROM ' . $this->goodsTable . ' as g  '.implode('', $join).'  WHERE '.implode(' AND ', $this->arrWhere);
        }

        if($page->hasRecodeCache('total') === false) {
            $dataCount = $this->db->query_fetch($searchCountSQL, $this->arrBind, false);
        }

        unset($this->arrBind);

        $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수
        if (Session::get('manager.isProvider')) { // 전체 레코드 수
            $totalCountSQL = "SELECT COUNT(go.sno) AS totalCnt FROM ".$this->goodsTable." as g   LEFT JOIN ".DB_GOODS_OPTION." as go ON go.goodsNo = g.goodsNo   WHERE g.delFl = 'n' AND g.applyFl !='a' AND g.scmNo ='".Session::get('manager.scmNo')."' ";
        }  else {
            $totalCountSQL = "SELECT COUNT(go.sno) AS totalCnt FROM ".$this->goodsTable." as g   LEFT JOIN ".DB_GOODS_OPTION." as go ON go.goodsNo = g.goodsNo   WHERE g.delFl = 'n' AND g.applyFl !='a'";
        }
        if($page->hasRecodeCache('amount') === false) {
            $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind, false);
            $page->recode['amount'] = $dataCount['totalCnt']; //검색 레코드 수
        }

        $page->setPage();

        // 아이콘  설정
        if (empty($data) === false) {
            $this->setAdminListGoods($data,",g.goodsBenefitSetFl",$mode);
        }

        // 상품리스트 그리드 설정
        $goodsBatchStockAdminGrid = \App::load('\\Component\\Goods\\GoodsAdminGrid');
        $goodsBatchStockAdminGridMode = $goodsBatchStockAdminGrid->getGoodsAdminGridMode();
        $this->goodsBatchStockGridConfigList = $goodsBatchStockAdminGrid->getSelectGoodsBatchStockGridConfigList($goodsBatchStockAdminGridMode, 'all');

        if (empty($this->goodsBatchStockGridConfigList) === false) {
            $getData['goodsBatchStockGridConfigList'] = $this->goodsBatchStockGridConfigList;
            $getData['goodsBatchStockGridConfigListDisplayFl'] = false; // 그리드 추가 진열 레이어 노출 여부
            foreach($gridAddDisplayArray as $displayPassVal) {
                if(array_key_exists($displayPassVal, $getData['goodsBatchStockGridConfigList']['display']) === true) {
                    $getData['goodsBatchStockGridConfigListDisplayFl'] = true; // 그리드 추가 진열 레이어 노출 사용
                    break;
                }
            }
            if($goodsBatchStockAdminGridMode == 'goods_batch_stock_list') {
                $getData['goodsBatchStockGridConfigList']['btn'] = '수정';
            }
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * 상품 가격 일괄 변경
     *
     * @param array $getData 일괄 처리 정보
     */
    public function setBatchPrice($getData)
    {
        // 정보 체크
        if ($getData['pricePercent'] !='' && $getData['pricePercent']  >= 0 ) //계산 수식 일괄 적용
        {
            $arrKey = array('confPrice', 'markType', 'plusMinus', 'targetPrice', 'queryAll');
            $allKey = array_keys($getData);
            foreach ($arrKey as $val) {
                if (!in_array($val, $allKey)) {
                    throw new Exception(__('가격 일괄 수정할 필수 항목이 존재하지 않습니다.'));
                }
                if (!gd_isset($getData[$val])) {
                    throw new Exception(__('가격 일괄 수정할 필수 항목이 존재하지 않습니다.'));
                }
            }

            // (기능권한)상품> 판매가 수정
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsPrice') != 'y') {
                if ($getData['targetPrice'] == 'goodsPrice') {
                    throw new Exception(__('판매가 수정 권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
                }
            }

            $trunc = Globals::get('gTrunc.goods');
            $getData['roundType'] = $trunc['unitRound'];
            $getData['roundUnit'] = $trunc['unitPrecision'];


            // 할인/할증 관련 배열
            $arrPlusMinus = array('p' => '+', 'm' => '-');

            // 계산할 금액
            if ($getData['markType'] == 'p') {
                $pricePercent = ' ( ' . $getData['confPrice'] . ' * ' . ($getData['pricePercent'] / 100) . ' )';
            } else {
                $pricePercent = $getData['pricePercent'];
            }

            // 1차 계산식
            $expression = $getData['confPrice'] . ' ' . $arrPlusMinus[$getData['plusMinus']] . $pricePercent;

            // 올림
            if ($getData['roundType'] == 'ceil') {
                $roundUnit = $getData['roundUnit'] * 10;
                // 2차 계산식
                $expression = '( ( CEILING( ( ( ' . $expression . ' ) / ' . $roundUnit . ' ) ) ) * ' . $roundUnit . ' )';
            }

            // 반올림
            if ($getData['roundType'] == 'round') {
                $roundUnit = strlen($getData['roundUnit']);
                if($getData['roundUnit'] == '0.1') $roundUnit = 0;
                // 2차 계산식
                $expression = 'ROUND( ( ' . $expression . ' ), -' . $roundUnit . ' )';
            }

            // 버림
            if ($getData['roundType'] == 'floor') {
                $roundUnit = $getData['roundUnit'] * 10;
                // 2차 계산식
                $expression = '( ( FLOOR( ( ( ' . $expression . ' ) / ' . $roundUnit . ' ) ) ) * ' . $roundUnit . ' )';
            }

            // 일괄 변경 처리
            $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));

            $strSQL = 'SELECT goodsNo,goodsNm,' . $expression . ' as price FROM ' . DB_GOODS . '  WHERE goodsNo IN (' . implode(',', $arrGoodsNo) . ') AND ' . $expression . ' < 0 ';
            $data = $this->db->query_fetch($strSQL);

            if ($getData['isPrice']) {
                return $data;
            }

            if ($data) {
                $tmpGoodsNo = array_flip($arrGoodsNo);
                foreach ($data as $k => $v) {
                    if (in_array($v['goodsNo'], $arrGoodsNo)) {
                        unset($arrGoodsNo[$tmpGoodsNo[$v['goodsNo']]]);
                    }
                }
            }

            // 계산식 완료
            $expression = $getData['targetPrice'] . '= ' . $expression;
            $return = $this->setBatchUpdateSql($expression, $arrGoodsNo, $getData['confPrice'] . "," . $getData['targetPrice'], $getData['modDtUse']);


        } else //선택된값 필드값 적용
        {
            $arrKeys = array('fixedPrice', 'costPrice');
            // (기능권한)상품> 판매가 수정
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsPrice') != 'y');
            else {
                array_push($arrKeys, 'goodsPrice');
            }
            $return = $this->setBatchUpdate($getData, $arrKeys);
        }

        return $return;
    }


    /**
     * 상품 아이콘 일괄 변경
     *
     * @param array $getData 일괄 처리 정보
     */
    public function setBatchIcon($getData)
    {
        // 정보 체크
        if ($getData['termsFl'] == 'y') //계산 수식 일괄 적용
        {

            // 일괄 변경 처리
            $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));

            //상품 아이콘 일괄저장
            if ($getData['type'] == 'icon') {

                //무제한용
                if (is_array($getData['icon']['goodsIconCd'])) {
                    foreach ($arrGoodsNo as $k => $v) {
                        $applyFl = $this->setGoodsIcon($getData['icon']['goodsIconCd'], 'un', $v, '0000-00-00', '0000-00-00', 0);
                    }
                }

                //기간제한용
                if (is_array($getData['icon']['goodsIconCdPeriod'])) {
                    foreach ($arrGoodsNo as $k => $v) {
                        $applyFl = $this->setGoodsIcon($getData['icon']['goodsIconCdPeriod'], 'pe', $v, $getData['icon']['goodsIconStartYmd'], $getData['icon']['goodsIconEndYmd'], 0);
                    }
                }
            }

            if ($getData['type'] == 'color') {

                if ($getData['goodsColor']) $goodsColor = implode(STR_DIVISION, $getData['goodsColor']);

                switch ($getData['colorType']) {
                    case 'add':
                        $expression = "goodsColor = CONCAT(goodsColor,IF(goodsColor = '','', '" . STR_DIVISION . "'),'" . $goodsColor . "')";
                        break;
                    case 'update':
                        $expression = "goodsColor = '" . $goodsColor . "'";
                        break;
                    case 'del':
                        $expression = "goodsColor = ''";
                        break;
                }

                $filed = "goodsColor";
                $applyFl = $this->setBatchUpdateSql($expression, $arrGoodsNo, $filed, $getData['modDtUse']);
            }

        } else //선택된값 아이콘 필드 적용
        {
            foreach ($getData['arrGoodsNo'] as $k => $v) {

                //무제한 아이콘
                if (is_array($getData['goodsIconCd'][$v])) {
                    $applyFl = $this->setGoodsIcon($getData['goodsIconCd'][$v], 'un', $v, '0000-00-00', '0000-00-00', 0);
                } else {
                    $arrBind = [];
                    $this->db->bind_param_push($arrBind, 's', $v);
                    $this->db->bind_param_push($arrBind, 's', 'un');
                    $this->db->set_delete_db(DB_GOODS_ICON, 'goodsNo = ? AND iconKind = ?', $arrBind);
                    unset($arrBind);
                }

                //기간제한 아이콘
                if (is_array($getData['goodsIconCdPeriod'][$v])) {
                    $applyFl = $this->setGoodsIcon($getData['goodsIconCdPeriod'][$v], 'pe', $v, '0000-00-00', '0000-00-00', 0);
                } else {
                    $arrBind = [];
                    $this->db->bind_param_push($arrBind, 's', $v);
                    $this->db->bind_param_push($arrBind, 's', 'pe');
                    $this->db->set_delete_db(DB_GOODS_ICON, 'goodsNo = ? AND iconKind = ?', $arrBind);
                    unset($arrBind);
                }
            }
        }

        return $applyFl;
    }

    /**
     * 상품 마일리지 일괄 변경
     *
     * @param array $getData 일괄 처리 정보
     */
    public function setBatchMileage($getData)
    {
        if (gd_isset($getData['batchAll']) == 'y') {
            if (!is_array($getData['queryAll'])) {
                throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
            }
            foreach ($getData['queryAll'] as $key => $val) {
                if ($key == 'bind') {
                    $query[$key] = json_decode(Encryptor::decrypt($val));
                } else {
                    $query[$key] = Encryptor::decrypt($val);
                }
            }
            gd_trim($query);

            $strSQL = 'SELECT g.goodsNo FROM ' . DB_GOODS . ' g ' . $query['join'] . ' WHERE ' . $query['where'] . ' ORDER BY g.goodsNo ASC';
            $data = $this->db->query_fetch($strSQL, $query['bind']);
            unset($query);

            $arrGoodsNo = array();
            foreach ($data as $key => $val) {
                $arrGoodsNo[] = $val['goodsNo'];
            }
        } else {
            $arrGoodsNo = $getData['arrGoodsNo'];
        }


        if ($getData['type'] == 'mileage') //마일리지 설정인 경우
        {
            $setData['mileageFl'] = $getData['mileageFl'];
            $setData['mileageGroup'] = $getData['mileageGroup'];
            $setData['mileageGroupInfo'] = @implode(INT_DIVISION, $getData['mileageGroupInfo']);
            $setData['mileageGoods'] = $getData['mileageGoods'];
            $setData['mileageGoodsUnit'] = $getData['mileageGoodsUnit'];
            $setData['mileageGroupMemberInfo'] = str_replace('\'', '', json_encode($getData['mileageGroupMemberInfo'], JSON_UNESCAPED_UNICODE));
        } else //상품할인 설정,혜택 제외 설정인 경우
        {
            //상품 혜택 적용 여부
            $setData['goodsBenefitSetFl'] = $getData['goodsBenefitSetFl'];

            if($setData['goodsBenefitSetFl'] == 'n' ){ //상품 혜택 개별 설정

                $setData['benefitUseType'] = $getData['benefitUseType'];
                if($getData['benefitUseType'] == 'newGoodsDiscount'){ //신상품할인
                    $setData['newGoodsDate'] = $getData['newGoodsDate'];
                    $setData['newGoodsRegFl'] = $getData['newGoodsRegFl'];
                    $setData['newGoodsDateFl'] = $getData['newGoodsDateFl'];
                }
                else if($getData['benefitUseType'] == 'periodDiscount'){ // 특정기간 할인
                    $setData['periodDiscountStart'] = $getData['periodDiscountStart'];
                    $setData['periodDiscountEnd'] = $getData['periodDiscountEnd'];
                }

                $setData['exceptBenefit'] = @implode(STR_DIVISION, $getData['exceptBenefit']);
                $setData['exceptBenefitGroup'] = $getData['exceptBenefitGroup'];
                $setData['exceptBenefitGroupInfo'] = @implode(INT_DIVISION, $getData['exceptBenefitGroupInfo']);

                $setData['goodsDiscountFl'] = $getData['goodsDiscountFl'];
                $setData['fixedGoodsDiscount'] = @implode(STR_DIVISION, $getData['fixedGoodsDiscount']);
                $setData['goodsDiscountGroup'] = $getData['goodsDiscountGroup'];
                $setData['goodsDiscount'] = $getData['goodsDiscount'];
                $setData['goodsDiscountUnit'] = $getData['goodsDiscountUnit'];
                $setData['goodsDiscountGroupMemberInfo'] = str_replace('\'', '', json_encode($getData['goodsDiscountGroupMemberInfo'], JSON_UNESCAPED_UNICODE));

            }else{ //상품 혜택 적용
                $setData['goodsDiscountFl'] = 'n';

                if($getData['benefitSno'] > 0){
                    $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
                    foreach($arrGoodsNo as $key => $val){
                        $goodsBenefit -> addGoodsLink($getData['benefitSno'],$val);
                    }
                }
            }
        }

        $applyFl = $this->setBatchGoods($arrGoodsNo, array_keys($setData), array_values($setData), $getData['modDtUse']);

        return $applyFl;

    }

    public function setBatchGoodsNo($batchAll = null, $arrGoodsNo = null, $queryAll = null)
    {
        // where 문 설정
        if (gd_isset($batchAll) == 'y') {
            if (!is_array($queryAll)) {
                throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
            }


            foreach ($queryAll as $key => $val) {
                if ($key == 'bind') {
                    $query[$key] = json_decode(Encryptor::decrypt($val));
                } else {
                    $query[$key] = Encryptor::decrypt($val);
                }
            }

            $strSQL = 'SELECT g.goodsNo FROM ' . DB_GOODS . ' g ' . $query['join'] . ' WHERE ' . $query['where'] . ' ORDER BY g.goodsNo ASC';
            $data = $this->db->query_fetch($strSQL, $query['bind']);
            unset($query);

            $arrGoodsNo = array();
            foreach ($data as $key => $val) {
                $arrGoodsNo[] = $val['goodsNo'];
            }
        } else {
            if (!is_array($arrGoodsNo)) {
                throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
            }
        }

        return $arrGoodsNo;
    }

    /**
     * 상품 일괄 변경 처리
     *
     * @param string $expression 처리할 수식
     * @param string $batchAll 검색 전체 수정 또는 선택 상품 수정
     * @param array $arrGoodsNo 선택 상품의 경우 goodsNo 의 배열 값
     * @param array $queryAll 검색 전체의 경우 암호화된 쿼리문 배열
     * @param string $modDtUse 상품수정일 변경유무
     */

    public function setBatchUpdateSql($expression, $arrGoodsNo = null, $fieldInfo = null, $modDtUse = null)
    {

        // 일괄 처리를 위한 where 문 배열 및 로그처리
        $logCode = array();
        $logCodeNm = array();
        $addLogData = '일괄처리한 수식 : ' . $expression . chr(10);
        if (preg_match('/^mileage+/', $expression)) {
            $logSubType = 'mileage';
        } else {
            $logSubType = 'price';
        }

        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionModify') == 'c') {
            $applyFl = 'a';
            $tmp[] = "applyFl = '" . $applyFl . "'";
            $tmp[] = "applyDt = '" . date('Y-m-d H:i:s') . "'";
            $tmp[] = "applyType = 'm'";
        } else {
            $applyFl = 'y';
            $tmp[] = "applyFl = '" . $applyFl . "'";
        }

        if ($tmp) $expression .= "," . implode(",", $tmp);

        //승인관련 하여 이전 데이터 체크
        foreach ($arrGoodsNo as $key => $val) {

            $goodsNo = $this->db->escape($val);
            $goodsData = $this->getGoodsInfo($goodsNo, $fieldInfo);
            $strWhere = "goodsNo = '" . $goodsNo . "'";

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS, $expression, $strWhere);

            $updateData = $this->getGoodsInfo($goodsNo, $fieldInfo);
            $this->setGoodsLog("goods", $goodsNo, $goodsData, $updateData);
            unset($arrBind);

        }
        if($this->goodsDivisionFl) {
            //일괄 검색 정보 업데이트
            $this->updateGoodsSearch($arrGoodsNo);
        }

        //네이버관련 업데이트
        // if ($this->naverConfig['naverFl'] == 'y') {
        $this->setGoodsUpdateEp($applyFl, $arrGoodsNo);
        // }


        //$strWhere = 'goodsNo IN (' . implode(',', $arrGoodsNo) . ')';
        //$this->db->set_update_db(DB_GOODS, $expression, $strWhere);


        foreach ($logCode as $key => $val) {
            LogHandler::wholeLog('goods', $logSubType, 'batch', $val, $logCodeNm[$val], $addLogData);
        }

        return $applyFl;

    }

    /**
     * 옵션 일괄 변경 처리
     *
     * @param string $expression 처리할 수식
     * @param string $batchAll 검색 전체 수정 또는 선택 상품 수정
     * @param array $arrGoodsNo 선택 상품의 경우 goodsNo 의 배열 값
     * @param array $queryAll 검색 전체의 경우 암호화된 쿼리문 배열
     */

    public function setBatchUpdateOptionSql($expression, $batchAll = null, $arrGoodsNo = null, $queryAll = null)
    {
        // where 문 설정
        if (gd_isset($batchAll) == 'y') {
            if (!is_array($queryAll)) {
                throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
            }


            foreach ($queryAll as $key => $val) {
                if ($key == 'bind') {
                    $query[$key] = json_decode(Encryptor::decrypt($val));
                } else {
                    $query[$key] = Encryptor::decrypt($val);
                }
            }

            $strSQL = 'SELECT go.sno FROM ' . DB_GOODS_OPTION . ' go ' . $query['join'] . ' WHERE ' . $query['where'] . ' ORDER BY go.sno ASC';
            $data = $this->db->query_fetch($strSQL, $query['bind']);
            unset($query);

            $arrGoodsNo = array();
            foreach ($data as $key => $val) {
                $arrGoodsNo[] = $val['sno'];
            }
        } else {
            if (!is_array($arrGoodsNo)) {
                throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
            }
        }

        // 일괄 처리를 위한 where 문 배열 및 로그처리
        $logCode = array();
        $logCodeNm = array();
        $addLogData = '일괄처리한 수식 : ' . $expression . chr(10);
        if (preg_match('/^mileage+/', $expression)) {
            $logSubType = 'mileage';
        } else {
            $logSubType = 'price';
        }


        $strWhere = 'sno IN (' . implode(',', $arrGoodsNo) . ')';

        $this->db->set_update_db(DB_GOODS_OPTION, $expression, $strWhere);

        foreach ($logCode as $key => $val) {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('goods', $logSubType, 'batch', $val, $logCodeNm[$val], $addLogData);
        }

        return $arrGoodsNo;

    }


    /**
     * 상품 정보 비교 후 일괄 변경
     *
     * @param array $getData 상품 정보
     * @param array $arrKey 비교할 필드
     */
    public function setBatchUpdate($getData, $arrKey)
    {
        $arrBatch = array();
        $strWhere = 'goodsNo = ?';

        // bind를 위한 필드 정보
        $fieldData = DBTableField::getBindField('tableGoods', $arrKey);

        // 기존 정보를 변경
        foreach ($getData['arrGoodsNo'] as $key => $val) {
            $arrBatch[$val]['sno'][] = $val;

            foreach ($arrKey as $keyNm) {
                $arrBatch[$val][$keyNm][] = $getData[$keyNm][$val];
            }
        }


        // 상품별로 구분해서 변경된 정보만 update
        $updateGoodsNo = [];
        foreach ($arrBatch as $goodsNo => $arrData) {

            // 로그 데이타 초기화
            $strLogData = '';

            // 기존 상품의 옵션 값 정보
            $tmpData = $this->getGoodsInfo($goodsNo); // 옵션/가격 정보

            foreach ($arrKey as $k => $v) {
                $goodsData[$v] = $tmpData[$v];
            }
            $goodsData['sno'] = $tmpData['goodsNo'];


            // 값을 비교후 update 인 경우만 처리함
            $compareOption = $this->db->get_compare_array_data(array($goodsData), $arrData, false, $arrKey); // 3번째 false 값 바꾸지 마세요... 주의

            foreach ($compareOption as $optionSno => $optionResult) {
                // 결과가 수정인 경우만 처리
                if ($optionResult != 'update') {
                    continue;
                }


                // 정보 초기화
                $arrBinding = array();

                // bind 데이터
                foreach ($arrData['sno'] as $key => $val) {
                    if ($val != $goodsNo) {
                        continue;
                    }
                    foreach ($fieldData as $fKey => $fVal) {
                        $arrBinding[$fVal['val']] = $arrData[$fVal['val']][$key];
                    }
                }

                // 디비 저장
                $arrBind = $this->db->get_binding($fieldData, $arrBinding, $optionResult);


                $this->db->bind_param_push($arrBind['bind'], 'i', $goodsNo);

                //상품수정일 변경유무 추가
                if ($getData['modDtUse'] == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS, $arrBind['param'], $strWhere, $arrBind['bind']);
                unset($arrBind);

                $this->setGoodsLog("goods", $goodsNo, $goodsData, $arrBinding);
                $updateGoodsNo[] = $goodsNo;
            }


            // 전체 로그를 저장합니다.
            if (empty($strLogData) === false) {
                $addLogData = $stockLogData . $strLogData;
                LogHandler::wholeLog('goods', 'stock', 'batch', $goodsNo, $getData['goodsNm'][$goodsNo], $addLogData);
            }
        }
        if($this->goodsDivisionFl) {
            //일괄 검색 정보 업데이트
            $this->updateGoodsSearch($updateGoodsNo);
        }

        //상태 일괄 업데이트
        $applyFl = $this->setGoodsApplyUpdate($updateGoodsNo, 'modify', $getData['modDtUse']);
        return $applyFl;

    }

    public function setBatchUpdateOption($getData, $arrKey, $arrKeyNm = array())
    {
        $arrBatch = array();
        $strWhere = 'sno = ?';


        // bind를 위한 필드 정보
        $fieldData = DBTableField::getBindField('tableGoodsOption', $arrKey);

        // 기존 정보를 변경
        foreach ($getData['optionSno'] as $key => $val) {

            foreach ($arrKey as $keyNm) {
                if ($getData[$keyNm][$key]) $arrBatch[$val][$keyNm][] = $getData[$keyNm][$key];
            }

            if (is_array($arrBatch[$val])) $arrBatch[$val]['sno'][] = $val;

            if ($key == 'arrGoodsNo') $goodsNo[$val] = $getData['arrGoodsNo'][$key];

        }

        // 상품별로 구분해서 변경된 정보만 update
        $updateGoodsNo = [];
        foreach ($arrBatch as $sno => $arrData) {

            // 로그 데이타 초기화
            $strLogData = '';


            // 기존 상품의 옵션 값 정보
            $tmpData = $this->getGoodsOptionInfo($sno); // 옵션/가격 정보


            foreach ($arrKey as $k => $v) {
                $optionData[$v] = $tmpData[$v];
            }
            $optionData['sno'] = $tmpData['sno'];


            // 값을 비교후 update 인 경우만 처리함
            $compareOption = $this->db->get_compare_array_data(array($optionData), $arrData, false, $arrKey); // 3번째 false 값 바꾸지 마세요... 주의

            foreach ($compareOption as $optionSno => $optionResult) {
                // 결과가 수정인 경우만 처리
                if ($optionResult != 'update') {
                    continue;
                }


                // 정보 초기화
                $arrBinding = array();

                // bind 데이터
                foreach ($arrData['sno'] as $key => $val) {
                    if ($val != $sno) {
                        continue;
                    }
                    foreach ($fieldData as $fKey => $fVal) {
                        $arrBinding[$fVal['val']] = $arrData[$fVal['val']][$key];
                    }
                }

                // 디비 저장
                $arrBind = $this->db->get_binding($fieldData, $arrBinding, $optionResult);
                $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
                $this->db->set_update_db(DB_GOODS_OPTION, $arrBind['param'], $strWhere, $arrBind['bind']);

                $arrBinding['sno'] = $sno;

                $this->setGoodsLog("option", $goodsNo[$sno], array($optionData), array('update' => array($arrBinding)));
                $updatetGoodsNo[] = $goodsNo[$sno];

                unset($arrBind);

            }
        }
    }

    /*
     * 가격 마일리지 재고 수정
     */
    public function setBatchStock($getData)
    {

        $totalStockGoodsNo = [];
        if ($getData['termsFl'] == 'n') //개별수정 조건
        {
            $goodsData = [];
            $optionData = [];
            $arrGoodsNo = [];
            $goodsDisplayFl = [];
            $goodsDisplayMobileFl = [];
            $goodsSellFl = [];
            $goodsSellMobileFl = [];
            $arrOptionSno = [];
            $soldOutFl = [];
            $stockFl = [];
            foreach ($getData['arrGoodsNo'] as $k => $v) {
                $tmp = explode("_", $v);
                if (!in_array($tmp[0], $arrGoodsNo)) {
                    $arrGoodsNo[$tmp[0]] = $tmp[0];
                    $goodsDisplayFl[$tmp[0]] = $getData['goods']['goodsDisplayFl'][$tmp[0]];
                    $goodsDisplayMobileFl[$tmp[0]] = $getData['goods']['goodsDisplayMobileFl'][$tmp[0]];
                    $goodsSellFl[$tmp[0]] = $getData['goods']['goodsSellFl'][$tmp[0]];
                    $goodsSellMobileFl[$tmp[0]] = $getData['goods']['goodsSellMobileFl'][$tmp[0]];
                    $soldOutFl[$tmp[0]] = $getData['goods']['soldOutFl'][$tmp[0]];
                }

                $arrOptionSno[$k] = $tmp[1];
                $optionViewFl[$k] = gd_isset($getData['option']['optionViewFl'][$tmp[0]][$tmp[1]],'y');
                $optionSellFl[$k] = gd_isset($getData['option']['optionSellFl'][$tmp[0]][$tmp[1]],'y');
                $optionDeliveryFl[$k] = gd_isset($getData['option']['deliveryFl'][$tmp[0]][$tmp[1]], 'y');
                $optionSellStopFl[$k] = gd_isset($getData['option']['sellStopFl'][$tmp[0]][$tmp[1]], 'y');
                $optionSellStopStock[$k] = $getData['option']['sellStopStock'][$tmp[0]][$tmp[1]];
                $optionConfirmRequestFl[$k] = gd_isset($getData['option']['confirmRequestFl'][$tmp[0]][$tmp[1]], 'y');
                $optionConfirmRequestStock[$k] = $getData['option']['confirmRequestStock'][$tmp[0]][$tmp[1]];
                if ($getData['option']['stockFl'][$tmp[0]][$tmp[1]]) {
                    $stockFl[$tmp[0]] = 'y';
                    switch ($getData['option']['stockFl'][$tmp[0]][$tmp[1]]) {
                        case 'p':
                            $stockCnt[$k] = $getData['option']['stockCntFix'][$tmp[0]][$tmp[1]] + $getData['option']['stockCnt'][$tmp[0]][$tmp[1]];
                            break;
                        case 'm':
                            $stockCnt[$k] = $getData['option']['stockCntFix'][$tmp[0]][$tmp[1]] - $getData['option']['stockCnt'][$tmp[0]][$tmp[1]];
                            break;
                        case 'c':
                            $stockCnt[$k] = $getData['option']['stockCnt'][$tmp[0]][$tmp[1]];
                            break;
                    }

                    $totalStockGoodsNo[] = $tmp[0];
                } else {
                    $stockCnt[$k] = $getData['option']['stockCntFix'][$tmp[0]][$tmp[1]];
                    $stockFl[$tmp[0]] = $getData['option']['stockFlOrg'][$tmp[0]][$tmp[1]]; // 재고 수정을 하지않은경우 stockFl값을 기존값으로 업데이트하기 위함
                }

            }

            //옵션 일괄 변경
            $optionData['optionSno'] = $arrOptionSno;
            $optionData['optionViewFl'] = $optionViewFl;

            foreach($optionSellFl  as $k => $v){
                if($optionSellFl[$k] !=  'y' && $optionSellFl[$k] !=  'n' && $optionSellFl[$k] !=  ''){
                    $optionData['optionSellFl'][$k] = 't';
                    $optionData['optionSellCode'][$k] = $optionSellFl[$k];
                }else if($optionSellFl[$k] !=  ''){
                    $optionData['optionSellFl'][$k]  = $optionSellFl[$k];
                }
                if($optionDeliveryFl[$k] !=  'normal' && $optionDeliveryFl[$k] !=  ''){
                    $optionData['optionDeliveryFl'][$k] = 't';
                    $optionData['optionDeliveryCode'][$k] = $optionDeliveryFl[$k];
                }else if($optionSellFl[$k] !=  ''){
                    $optionData['optionDeliveryFl'][$k]  = $optionDeliveryFl[$k];
                }
            }

            $optionData['sellStopFl'] = $optionSellStopFl;
            $optionData['sellStopStock'] = $optionSellStopStock;
            $optionData['confirmRequestFl'] = $optionConfirmRequestFl;
            $optionData['confirmRequestStock'] = $optionConfirmRequestStock;
            $optionData['stockCnt'] = $stockCnt;
            $optionData['arrGoodsNo'] = $arrGoodsNo;

            $this->setBatchUpdateOption($optionData, array('optionViewFl', 'optionSellFl', 'optionSellCode', 'optionDeliveryFl', 'optionDeliveryCode', 'sellStopFl', 'sellStopStock', 'confirmRequestFl', 'confirmRequestStock', 'stockCnt'));

            //상품 일괄변경
            $goodsData['arrGoodsNo'] = $arrGoodsNo;
            $goodsData['goodsSellFl'] = $goodsSellFl;
            $goodsData['goodsSellMobileFl'] = $goodsSellMobileFl;
            $goodsData['soldOutFl'] = $soldOutFl;
            $goodsData['stockFl'] = $stockFl;
            $goodsData['goodsDisplayFl'] = $goodsDisplayFl;
            $goodsData['goodsDisplayMobileFl'] = $goodsDisplayMobileFl;
            $goodsData['modDtUse'] = $getData['modDtUse'];


            $applyFl = $this->setBatchUpdate($goodsData, array('goodsSellFl', 'goodsDisplayFl','goodsSellMobileFl', 'goodsDisplayMobileFl', 'soldOutFl','stockFl'));

        } else { //전체 조건 수정

            $arrOptionSno = [];
            $arrGoodsNo = [];
            if ($getData['arrGoodsNo']) {
                foreach ($getData['arrGoodsNo'] as $k => $v) {
                    $tmp = explode("_", $v);
                    $arrOptionSno[$k] = $tmp[1];
                    $arrGoodsNo[$k] = $tmp[0];

                }

            }

            //전체 선택 한 경우
            if (gd_isset($getData['batchAll']) == 'y') {
                if (!is_array($getData['queryAll'])) {
                    throw new \Exception(__('조건에 대해 처리중 오류가 발생했습니다.'), 500);
                }

                foreach ($getData['queryAll'] as $key => $val) {
                    if ($key == 'bind') {
                        $query[$key] = json_decode(Encryptor::decrypt($val));
                    } else {
                        $query[$key] = Encryptor::decrypt($val);
                    }
                }

                $strSQL = 'SELECT g.goodsNo,go.sno FROM ' . $this->goodsTable . ' g ' . $query['join'] . ' WHERE ' . $query['where'] . ' ORDER BY go.sno ASC';
                $data = $this->db->query_fetch($strSQL, $query['bind']);
                unset($query);

                $arrGoodsNo = array();
                foreach ($data as $key => $val) {
                    $arrGoodsNo[$val['goodsNo']] = $val['goodsNo'];
                    $arrOptionSno[] = $val['sno'];
                }
            }


            $tmp = [];
            if (gd_isset($getData['optionSellFl']) || gd_isset($getData['optionViewFl']) || gd_isset($getData['optionDeliveryFl']) || gd_isset($getData['optionSellStopFl']) || gd_isset($getData['optionSellStopStock']) || gd_isset($getData['optionConfirmRequestFl']) || gd_isset($getData['optionConfirmRequestStock']) || gd_isset($getData['optionStockFl']) || gd_isset($getData['optionStockCnt'])) {
                if (gd_isset($getData['optionViewFl'])) $tmp[] = "optionViewFl = '" . $getData['optionViewFl'] . "'";
                if (gd_isset($getData['optionSellFl'])){
                    if($getData['optionSellFl'] != 'y' && $getData['optionSellFl'] != 'n'  && $getData['optionSellFl'] != ''){
                        $tmp[] = "optionSellFl = 't'";
                        $tmp[] = "optionSellCode = '" . $getData['optionSellFl'] . "'";
                    }else  if($getData['optionSellFl'] != ''){
                        $tmp[] = "optionSellFl = '" . $getData['optionSellFl'] . "'";
                    }
                }
                if (gd_isset($getData['optionDeliveryFl'])){
                    if($getData['optionDeliveryFl'] != 'normal' && $getData['optionDeliveryFl'] != 'n'  && $getData['optionDeliveryFl'] != ''){
                        $tmp[] = "optionDeliveryFl = 't'";
                        $tmp[] = "optionDeliveryCode = '" . $getData['optionDeliveryFl'] . "'";
                    }else  if($getData['optionDeliveryFl'] != ''){
                        $tmp[] = "optionDeliveryFl = '" . $getData['optionDeliveryFl'] . "'";
                    }
                }
                if (gd_isset($getData['optionSellStopFl'])) $tmp[] = "sellStopFl = '".$getData['optionSellStopFl']."'";
                if (gd_isset($getData['optionSellStopStock'])) $tmp[] = "sellStopStock = '".$getData['optionSellStopStock']."'";
                if (gd_isset($getData['optionConfirmRequestFl'])) $tmp[] = "confirmRequestFl = '".$getData['optionConfirmRequestFl']."'";
                if (gd_isset($getData['optionConfirmRequestStock'])) $tmp[] = "confirmRequestStock = '".$getData['optionConfirmRequestStock']."'";

                if (gd_isset($getData['optionStockFl']) || gd_isset($getData['optionStockCnt'])) {
                    switch ($getData['optionStockFl']) {
                        case 'p':
                            $tmp[] = "stockCnt = stockCnt + " . $getData['optionStockCnt'];
                            break;
                        case 'm':
                            $tmp[] = "stockCnt = stockCnt - " . $getData['optionStockCnt'];
                            break;
                        case 'c':
                            $tmp[] = "stockCnt =  " . $getData['optionStockCnt'];
                            break;
                    }

                    $totalStockGoodsNo = $arrGoodsNo;
                }

                $this->setBatchUpdateOptionSql(implode(",", $tmp), null, gd_isset($arrOptionSno), null);

            }


            $tmp = [];
            if (gd_isset($getData['goodsDisplayMobileFl']) || gd_isset($getData['goodsSellMobileFl']) || gd_isset($getData['goodsDisplayFl']) || gd_isset($getData['goodsSellFl']) || gd_isset($getData['stockLimit']) || gd_isset($getData['soldOutFl']) || gd_isset($getData['optionStockFl']) || gd_isset($getData['optionStockCnt'])) {
                if (gd_isset($getData['goodsDisplayFl'])) $tmp[] = "goodsDisplayFl = '" . $getData['goodsDisplayFl'] . "'";
                if (gd_isset($getData['goodsDisplayMobileFl'])) $tmp[] = "goodsDisplayMobileFl = '" . $getData['goodsDisplayMobileFl'] . "'";
                if (gd_isset($getData['goodsSellFl'])) $tmp[] = "goodsSellFl = '" . $getData['goodsSellFl'] . "'";
                if (gd_isset($getData['goodsSellMobileFl'])) $tmp[] = "goodsSellMobileFl = '" . $getData['goodsSellMobileFl'] . "'";
                if (gd_isset($getData['soldOutFl'])) $tmp[] = "soldOutFl = '" . $getData['soldOutFl'] . "'";
                if (gd_isset($getData['stockLimit']) == 'y') {
                    $tmp[] = "stockFl = 'n'";
                } else {
                    if (gd_isset($getData['optionStockFl']) || gd_isset($getData['optionStockCnt'])) {
                        $tmp[] = "stockFl = 'y'";
                    }
                }


                $applyFl =   $this->setBatchUpdateSql(implode(",", $tmp), gd_isset($arrGoodsNo), "goodsDisplayFl,goodsSellFl,goodsDisplayMobileFl,goodsSellMobileFl,stockFl,soldOutFl", $getData['modDtUse']);
            }


        }

        //전체 재고량 체크
        if ($totalStockGoodsNo) {
            foreach ($totalStockGoodsNo as $k => $v) {
                $stockLogData = $this->setGoodsStock($v);
                LogHandler::wholeLog('goods', 'stock', 'batch', $v, $v, $stockLogData);
            }
        }

        return $applyFl;

    }



    /**
     * 일괄 배송정보 변경
     *
     * @param array $arrGoodsNo 일괄 처리할 goodsNo 배열
     * @param array $fieldInfo 일괄 처리할 field 명 (string or array)
     * @param array $valueInfo 일괄 처리할 field 값 (string or array or null)
     */
    public function setBatchDelivery($getData)
    {
        $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));

        $applyFl = $this->setBatchGoods($arrGoodsNo, 'deliverySno', $getData['deliverySno'], $getData['modDtUse']);
        return $applyFl;
    }

    /**
     * 일괄 네이버쇼핑 노출 변경
     *
     * @param array $arrGoodsNo 일괄 처리할 goodsNo 배열
     * @param array $fieldInfo 일괄 처리할 field 명 (string or array)
     * @param array $valueInfo 일괄 처리할 field 값 (string or array or null)
     */
    public function setBatchNaverConfig($getData)
    {
        $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));
        $applyFl = $this->setBatchGoods($arrGoodsNo, 'naverFl', $getData['naverFl'], $getData['modDtUse']);
        return $applyFl;
    }

    /**
     * 일괄 페이코쇼핑 노출 변경
     *
     * @param array $arrGoodsNo 일괄 처리할 goodsNo 배열
     * @param array $fieldInfo 일괄 처리할 field 명 (string or array)
     * @param array $valueInfo 일괄 처리할 field 값 (string or array or null)
     */
    public function setBatchPaycoConfig($getData)
    {
        $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));
        $applyFl = $this->setBatchGoods($arrGoodsNo, 'paycoFl', $getData['paycoFl'], $getData['modDtUse']);
        return $applyFl;
    }

    /**
     * 일괄 다음쇼핑하우 노출 변경
     *
     * @param array $getData 일괄 처리할 goodsNo 배열
     * @return string $applyFl
     * @throws Exception
     */
    public function setBatchDaumConfig($getData)
    {
        $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));
        $applyFl = $this->setBatchGoods($arrGoodsNo, 'daumFl', $getData['daumFl'], $getData['modDtUse']);
        return $applyFl;
    }

    /**
     * 일괄 상품 정보 변경
     *
     * @param array $arrGoodsNo 일괄 처리할 goodsNo 배열
     * @param array $fieldInfo 일괄 처리할 field 명 (string or array)
     * @param array $valueInfo 일괄 처리할 field 값 (string or array or null)
     * @param string $modDtUse 상품수정일 변경 유무
     */
    protected function setBatchGoods($arrGoodsNo, $fieldInfo, $valueInfo = null, $modDtUse = null)
    {
        // 상품 정보 변경할 항목 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo) || empty($fieldInfo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }


        // field 및 field 값 처리
        if (is_array($fieldInfo) === false) {
            $fieldInfo = array($fieldInfo);
            $valueInfo = array($valueInfo);
        }

        // bind를 위한 field 값 배열화
        foreach ($fieldInfo as $key => $val) {
            $arrData[$val] = $valueInfo[$key];
        }

        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionModify') == 'c') {
            $arrData['applyFl'] = 'a';
            $arrData['applyDt'] = date('Y-m-d H:i:s');
            $arrData['applyType'] = "m";
        } else  $arrData['applyFl'] = 'y';


        //승인관련 하여 이전 데이터 체크
        foreach ($arrGoodsNo as $key => $val) {

            $goodsNo = $this->db->escape($val);

            $goodsData = $this->getGoodsInfo($goodsNo, implode(",", $fieldInfo),null,null,true);

            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoods', array_keys($arrData)), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $goodsNo);

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }
            $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);

            $this->setGoodsLog("goods", $goodsNo, $goodsData, $arrData);

            unset($arrBind);
        }
        if($this->goodsDivisionFl && $fieldInfo != 'restockFl') {
            //일괄 검색 정보 업데이트
            $this->updateGoodsSearch($arrGoodsNo);
        }

        //네이버관련 업데이트
        if (($this->naverConfig['naverFl'] == 'y' || $this->daumConfig['useFl'] == 'y' || $this->paycoConfig['paycoFl'] == 'y') && $fieldInfo != 'restockFl') {
            $this->setGoodsUpdateEp($arrData['applyFl'], $arrGoodsNo);
        }

        // 상품 정보 변경
        //$arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoods', $fieldInfo), $arrData, 'update');
        //$strWhere = 'goodsNo IN (' . implode(',', $goodsNo) . ')';
        //$this->db->set_update_db(DB_GOODS, $arrBind['param'], $strWhere, $arrBind['bind']);

        return $arrData['applyFl'];

    }

    /**
     * 빠른 이동/복사/삭제 - 카테고리 연결
     *
     * @param array $arrGoodsNo 연결할 goodsNo 배열
     * @param array $arrCategoryCd 연결할 카테고리 코드 배열
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setBatchLinkCategory($arrGoodsNo, $arrCategoryCd, $modDtUse = null)
    {
        // 연결할 goodsNo 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }

        // 연결할 카테고리 체크
        if (is_array($arrCategoryCd) === false || empty($arrCategoryCd)) {
            throw new \Exception(__('연결할 카테고리는 필수 항목입니다.'), 500);
        }

        // 카테고리 정보 저장
        $updateGoodsNo = [];
        foreach ($arrGoodsNo as $key => $goodsNo) {

            // 기존 카테고리 정보
            $arrData = array();
            $getLink = $this->getGoodsLinkCategory($goodsNo);

            if (is_array($getLink)) {
                foreach ($getLink as $cKey => $cVal) {
                    foreach ($cVal as $field => $value) {
                        $arrData[$field][] = $value;
                    }
                }
            }

            // 연결할 카테고리 값 정의
            $originCateCd = $arrData;
            foreach ($arrCategoryCd as $cateKey => $cateVal) {
                // 대표 카테고리를 선택하지 않았을 경우, 최상단 카테고리로 설정
                if ($cateKey === 0) $cateCd = $cateVal;

                $existCateCd = array_search($cateVal, $arrData['cateCd']);
                if (is_numeric($existCateCd)) {
                    if (in_array($cateVal, $originCateCd['cateCd']) && $originCateCd['cateLinkFl'][$existCateCd] !== 'y') {
                        // 기존 등록된 카테고리 연결값 변경
                        $tmpLinkFl[$cateVal] = 'y';
                    } else if ($arrData['cateLinkFl'][$existCateCd] === 'n') {
                        // 상,하위 카테고리를 동시에 연결할 경우
                        $arrData['cateLinkFl'][$existCateCd] = 'y';
                    }
                } else {
                    $length = strlen($cateVal);
                    for ($i = 1; $i <= ($length / DEFAULT_LENGTH_CATE); $i++) {
                        $tmpCateCd = substr($cateVal, 0, ($i * DEFAULT_LENGTH_CATE));
                        $arrData['cateCd'][] = $tmpCateCd;

                        if ($tmpCateCd == $cateVal) {
                            $arrData['cateLinkFl'][] = 'y';
                            $tmpLinkFl[$tmpCateCd] = 'y';
                        } else {
                            $arrData['cateLinkFl'][] = 'n';
                        }
                    }
                }
            }

            // 다중 카테고리 유효성 체크
            $chkData = $this->getGoodsCategoyCheck($arrData, $goodsNo);

            // 유효성 체크 후 추가할 데이터만 insert
            foreach ($chkData['cateCd'] as $cKey => $cVal) {

                if (empty($chkData['sno'][$cKey])) {
                    $getData['goodsNo'] = $goodsNo;
                    $getData['cateCd'] = $chkData['cateCd'][$cKey];
                    $getData['cateLinkFl'] = $chkData['cateLinkFl'][$cKey];
                    $getData['goodsSort'] = $chkData['goodsSort'][$cKey];

                    $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkCategory(), $getData, 'insert');
                    $this->db->set_insert_db(DB_GOODS_LINK_CATEGORY, $arrBind['param'], $arrBind['bind'], 'y');

                    $updateData[] = $getData;
                    unset($arrBind, $getData);
                } else {
                    if(in_array($cVal,array_keys($tmpLinkFl)) && $chkData['cateLinkFl'][$cKey] != $tmpLinkFl[$cVal]) {
                        $arrBind = [];
                        $arrUpdate[] = 'cateLinkFl =?';
                        $this->db->bind_param_push($arrBind, 's', $tmpLinkFl[$cVal]);
                        $this->db->bind_param_push($arrBind, 's', $chkData['sno'][$cKey]);
                        $this->db->set_update_db(DB_GOODS_LINK_CATEGORY, $arrUpdate, 'sno = ?', $arrBind);
                        unset($arrUpdate);
                        unset($arrBind);
                    }
                }
            }

            if ($updateData) $this->setGoodsLog('category', $goodsNo, $getLink, array('update' => $updateData));

            // 대표 카테고리 설정
            $getData = $this->getGoodsInfo($goodsNo, 'cateCd');
            if (empty(Request::post()->get('categoryRepresent')) === false) {
                $this->setBatchGoods(array($goodsNo), 'cateCd', Request::post()->get('categoryRepresent'), $modDtUse);
            } else if (empty($getData['cateCd'])) {
                $this->setBatchGoods(array($goodsNo), 'cateCd', $cateCd, $modDtUse);
            } else {
                $updateGoodsNo[] = $goodsNo;
            }

        }

        //상태 일괄 업데이트
        $applyFl = $this->setGoodsApplyUpdate($updateGoodsNo, 'modify', $modDtUse);

        return $applyFl;

    }

    /**
     * 빠른 이동/복사/삭제 - 카테고리 이동
     * 카테고리 해제 -> 해당 카테고리 연결 -> 대표 카테고리 설정
     *
     * @param array $arrGoodsNo 이동할 goodsNo 배열
     * @param array $arrCategoryCd 이동할 카테고리 코드 배열
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setBatchMoveCategory($arrGoodsNo, $arrCategoryCd, $modDtUse)
    {

        // 이동할 goodsNo 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }

        // 이동할 카테고리 체크
        if (is_array($arrCategoryCd) === false || empty($arrCategoryCd)) {
            throw new \Exception(__('연결할 카테고리는 필수 항목입니다.'), 500);
        }

        // 카테고리 해제를 함
        $this->setBatchUnlinkCategory($arrGoodsNo, null, $modDtUse);

        // 이동할 카테고리 정보
        $arrData = array();
        foreach ($arrCategoryCd as $cateKey => $cateVal) {
            if ($cateKey === 0) $cateCd = $cateVal;
            $existCateCd = array_search($cateVal, $arrData['cateCd']);

            if (is_numeric($existCateCd)) {
                if ($arrData['cateLinkFl'][$existCateCd] == 'n') {
                    $arrData['cateLinkFl'][$existCateCd] = 'y';
                }
            } else {
                $length = strlen($cateVal);
                for ($j = 1; $j <= ($length / DEFAULT_LENGTH_CATE); $j++) {
                    $tmpCateCd = substr($cateVal, 0, ($j * DEFAULT_LENGTH_CATE));
                    $arrData['cateCd'][] = $tmpCateCd;
                    if ($tmpCateCd == $cateVal) {
                        $arrData['cateLinkFl'][] = 'y';
                    } else {
                        $arrData['cateLinkFl'][] = 'n';
                    }
                }
            }
        }

        // 카테고리 정보 저장
        foreach ($arrGoodsNo as $key => $goodsNo) {

            // 다중 카테고리 유효성 체크
            $setData = $this->getGoodsCategoyCheck($arrData, $goodsNo);

            // 공통 키값
            $arrDataKey = array('goodsNo' => $goodsNo);

            // 카테고리 정보 저장
            $cateLog = $this->db->set_compare_process(DB_GOODS_LINK_CATEGORY, $setData, $arrDataKey, null);
            if ($cateLog) {
                $this->setGoodsLog('category', $goodsNo, '', $cateLog);
            }

            // 대표 카테고리 여부를 체크후 없으면 선택된 카테고리 중 최상단 카테고리를 대표 카테고리로 설정
            if (empty(Request::post()->get('categoryRepresent')) === false) {
                $applyFl = $this->setBatchGoods(array($goodsNo), 'cateCd', Request::post()->get('categoryRepresent'), $modDtUse);
            } else {
                $applyFl = $this->setBatchGoods($arrGoodsNo, 'cateCd', $cateCd, $modDtUse);
            }
        }

        return $applyFl;
    }

    /**
     * 빠른 이동/복사/삭제 - 카테고리 복사
     * 상품 복사 -> 해당 카테고리 이동
     *
     * @param array $arrGoodsNo 복사할 goodsNo 배열
     * @param array $arrCategoryCd 연결할 카테고리 코드 배열
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setBatchCopyCategory($arrGoodsNo, $arrCategoryCd, $modDtUse = null)
    {
        // 복사할 goodsNo 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }

        // 상품 복사
        foreach ($arrGoodsNo as $key => $goodsNo) {
            $arrNewGoodsNo[] = $this->setCopyGoods($goodsNo);
        }
        unset($arrGoodsNo);

        // 카테고리 이동
        $applyFl = $this->setBatchMoveCategory($arrNewGoodsNo, $arrCategoryCd, $modDtUse);
        return $applyFl;
    }

    /**
     * 빠른 이동/복사/삭제 - 브랜드 교체
     *
     * @param array $arrGoodsNo 교체할 goodsNo 배열
     * @param array $brandCd 교체할 브랜드 코드
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setBatchLinkBrand($arrGoodsNo, $brandCd, $modDtUse = null)
    {
        // 교체할 goodsNo 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }

        // 교체할 브랜드 체크
        if (empty($brandCd)) {
            throw new \Exception(__('연결할 브랜드는 필수 항목입니다.'), 500);
        }

        // 브랜드 해제를 함
        $this->setBatchUnlinkBrand($arrGoodsNo, null, $modDtUse);

        // 카테고리 정보 저장
        foreach ($arrGoodsNo as $key => $goodsNo) {

            // 다중 카테고리 유효성 체크
            $arrData = $this->getGoodsBrandCheck($brandCd, null, $goodsNo);

            // 공통 키값
            $arrDataKey = array('goodsNo' => $goodsNo);

            // 브랜드 정보 저장
            $this->db->set_compare_process(DB_GOODS_LINK_BRAND, $arrData, $arrDataKey, null);
        }

        // 일괄 상품 정보 변경
        $applyFl = $this->setBatchGoods($arrGoodsNo, 'brandCd', $brandCd, $modDtUse);
        return $applyFl;
    }

    /**
     * 빠른 이동/복사/삭제 - 카테고리 해제
     *
     * @param array $arrGoodsNo 해제할 goodsNo 배열
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setBatchUnlinkCategory($arrGoodsNo,$cateCd, $modDtUse = null)
    {
        // 해제할 goodsNo 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }

        // goodsNo escape 처리 (혹시나 해서.. ^^;)
        foreach ($arrGoodsNo as $key => $val) {
            $goodsNo[] = $this->db->escape($val);
        }

        // 일괄 상품 정보 변경
        if($cateCd) {
            //카테고리 부분 업데이트 필요
            $strSQL = 'UPDATE ' . DB_GOODS . ' SET cateCd = null WHERE goodsNo IN (' . implode(',', $goodsNo) . ') AND cateCd IN ("' . implode('","', $cateCd) . '")';
            $this->db->query($strSQL);
            if($this->goodsDivisionFl) {
                $strSQL = 'UPDATE ' . DB_GOODS_SEARCH . ' SET cateCd = null WHERE goodsNo IN (' . implode(',', $goodsNo) . ') AND cateCd IN ("' . implode('","', $cateCd) . '")';
                $this->db->query($strSQL);
            }

        } else {
            $applyFl =  $this->setBatchGoods($arrGoodsNo, 'cateCd', null, $modDtUse);
        }

        // 카테고리 링크 삭제
        if($cateCd) $strWhere = 'goodsNo IN (' . implode(',', $goodsNo) . ') AND cateCd IN ("' . implode('","', $cateCd) . '")';
        else  $strWhere = 'goodsNo IN (' . implode(',', $goodsNo) . ')';
        $this->db->set_delete_db(DB_GOODS_LINK_CATEGORY, $strWhere);

        return $applyFl;
    }

    /**
     * 빠른 이동/복사/삭제 - 브랜드 해제
     *
     * @param array $arrGoodsNo 해제할 goodsNo 배열
     * @param string $modDtUse 상품수정일 변경유무
     */
    public function setBatchUnlinkBrand($arrGoodsNo,$cateCd, $modDtUse = null)
    {
        // 해제할 goodsNo 체크
        if (is_array($arrGoodsNo) === false || empty($arrGoodsNo)) {
            throw new \Exception(__('일괄 처리할 데이터오류로 인해 처리가 되지 않습니다.'), 500);
        }

        // goodsNo escape 처리 (혹시나 해서.. ^^;)
        foreach ($arrGoodsNo as $key => $val) {
            $goodsNo[] = $this->db->escape($val);
        }

        // 일괄 상품 정보 변경
        if($cateCd) {
            //브랜드 부분 업데이트 필요
            $strSQL = 'UPDATE ' . DB_GOODS . ' SET brandCd = null WHERE goodsNo IN (' . implode(',', $goodsNo) . ') AND brandCd IN (' . implode(',', $cateCd) . ')';
            $this->db->query($strSQL);
            if($this->goodsDivisionFl) {
                $strSQL = 'UPDATE ' . DB_GOODS_SEARCH . ' SET brandCd = null WHERE goodsNo IN (' . implode(',', $goodsNo) . ') AND brandCd IN (' . implode(',', $cateCd) . ')';
                $this->db->query($strSQL);
            }
        } else {
            $applyFl =  $this->setBatchGoods($arrGoodsNo, 'brandCd', null, $modDtUse);
        }

        // 카테고리 링크 삭제setBatchUnlinkBrand
        if($cateCd) $strWhere = 'goodsNo IN (' . implode(',', $goodsNo) . ') AND cateCd IN (' . implode(',', $cateCd) . ')';
        else  $strWhere = 'goodsNo IN (' . implode(',', $goodsNo) . ')';
        $this->db->set_delete_db(DB_GOODS_LINK_BRAND, $strWhere);

        return $applyFl;
    }


    /**
     * 재입고 알림 사용 상태 일괄 변경
     *
     * @param  array   $getData
     * @return integer $result
     */
    public function setBatchRestockStatus($getData)
    {
        $arrGoodsNo = $this->setBatchGoodsNo(gd_isset($getData['batchAll']), gd_isset($getData['arrGoodsNo']), gd_isset($getData['queryAll']));
        $applyFl = $this->setBatchGoods($arrGoodsNo, 'restockFl', $getData['restockBatchStatus']);

        return $applyFl;
    }

    /**
     * getAdminListDisplayMain
     *
     * @param string $kind
     * @return mixed
     */
    public function getAdminListDisplayTheme($kind = 'main', $mode = null)
    {
        if($kind === 'event'){
            unset($this->arrBind, $this->arrWhere);
            $this->arrBind = $this->arrWhere = [];
        }

        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchDisplayTheme($getValue);
        $this->arrWhere[] = sprintf("kind = '%s' ", $kind);
        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'dt.regDt desc';
        }

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], '10');
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        //상품리스트_메인상품진열 정렬_쇼핑몰유형
        if($getValue['popupMode'] == '1'){
            $sort = $getValue['orderBy'];
        }

        $this->db->strField = " count(*) as cnt";
        $this->db->strWhere = sprintf("kind = '%s' ", $kind);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DISPLAY_THEME . ' as dt ' . implode(' ', $query);
        list($result) = $this->db->query_fetch($strSQL);
        //   $this->arrBind = null;
        $totalCnt = $result['cnt'];

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $totalCnt; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        if($kind =='main') {
            // 현 페이지 결과
            $join[] = ' INNER JOIN ' . DB_DISPLAY_THEME_CONFIG . ' as dtc ON (dtc.themeCd = dt.themeCd OR dtc.themeCd = dt.mobileThemeCd) ';
            $join[] = ' LEFT OUTER JOIN ' . DB_MANAGER . ' as m ON m.sno = dt.managerNo ';

            $this->db->strJoin = implode('', $join);

            $this->db->strField = "dt.* , dtc.themeNm as displayThemeNm,m.managerId,m.managerNm,m.managerNickNm,m.isDelete";
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
            $this->db->strOrder = $sort;
            if($mode !='layer') $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

            // 검색 카운트
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_DISPLAY_THEME .' as dt ' . implode('', $join) . ' WHERE ' . $this->db->strWhere;
            $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $page->recode['total'] = $res['cnt']; // 검색 레코드 수
            $page->setPage();
            $page->setUrl(\Request::getQueryString());

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DISPLAY_THEME . ' as dt ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $this->arrBind);
        } else {
            $this->db->strField = "dt.*   ,m.managerId,m.managerNm,m.managerNickNm,m.isDelete";
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
            $this->db->strOrder = $sort;
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

            // 검색 카운트
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_DISPLAY_THEME .' as dt LEFT OUTER JOIN ' . DB_MANAGER . ' as m ON m.sno = dt.managerNo' . ' WHERE ' . $this->db->strWhere;
            $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $page->recode['total'] = $res['cnt']; // 검색 레코드 수
            $page->setPage();
            $page->setUrl(\Request::getQueryString());

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DISPLAY_THEME . ' as dt LEFT OUTER JOIN ' . DB_MANAGER . ' as m ON m.sno = dt.managerNo ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $this->arrBind);
        }
        Manager::displayListData($data);

        foreach ($data as &$val) {
            if ($val['kind'] == 'event') {
                if(!is_object($eventGroupTheme)){
                    $eventGroupTheme = \App::load('\\Component\\Promotion\\EventGroupTheme');
                }
                $val['eventSaleUrl'] = $this->getEventSaleUrl($val['sno'], false);
                $val['MobileEventSaleUrl'] = $this->getEventSaleUrl($val['sno'], true);
                $val['writer'] = sprintf('%s <br>(%s)', $val['managerNm'], $val['managerId']);
                $_device = gd_isset($val['pcFl'], 'y') . gd_isset($val['mobileFl'], 'y');
                switch ($_device) {
                    case 'yy' :
                        $val['displayDeviceText'] = __('PC+모바일');
                        break;
                    case 'yn' :
                        $val['displayDeviceText'] = __('PC쇼핑몰');
                        break;
                    case 'ny' :
                        $val['displayDeviceText'] = __('모바일');
                        break;
                }

                $nowDate = strtotime(date("Y-m-d H:i:s"));
                $displayStartDate = strtotime($val['displayStartDate']);
                $displayEndDate = strtotime($val['displayEndDate']);
                if ($nowDate < $displayStartDate) {
                    $val['statusText'] = __('대기');
                } else if ($nowDate > $displayStartDate && $nowDate < $displayEndDate) {
                    $val['statusText'] = __('진행중');
                } else if ($nowDate > $displayEndDate) {
                    $val['statusText'] = __('종료');
                } else {
                    $val['statusText'] = __('오류');
                }

                switch($val['displayCategory']){
                    case 'g' :
                        $eventGroupArray = $eventGroupTheme->getSimpleData($val['sno']);
                        $val['eventGroupArray'] = $eventGroupArray;
                        $val['displayCategoryText'] = '그룹형';
                        break;

                    case 'n' : default :
                    $val['displayCategoryText'] = '일반형';
                    break;
                }
            }
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * setSearchThemeConfig
     *
     * @param $searchData
     * @param int|string $searchPeriod
     */
    public function setSearchDisplayTheme($searchData, $searchPeriod = '-1')
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableDisplayTheme');

        $this->search['combineSearch'] = array('all' => __('=통합검색='), 'themeNm' => __('분류명'), 'themeDescription' => __('분류 설명'));
        $this->search['cateSearch'] = array('themeNm' => __('분류명'), 'themeDescription' => __('분류 설명'));
        $this->search['eventSaleListSelect'] = array('all' => __('=통합검색='), 'themeNm' => __('기획전명'), 'writer' => __('등록자'));    //기획전

        //검색설정
        $this->search['sortList'] = array(
            'dt.regDt asc' => __('등록일 ↓'),
            'dt.regDt desc' => __('등록일 ↑'),
            'dt.themeNm asc' => __('테마명 ↓'),
            'dt.themeNm desc' => __('테마명 ↑'),
            'displayThemeNm asc' => __('선택테마 ↓'),
            'displayThemeNm desc' => __('선택테마 ↑')
        );

        $this->search['eventSaleSortList'] = array(
            'dt.regDt asc' => __('등록일 ↓'),
            'dt.regDt desc' => __('등록일 ↑'),
            'dt.displayStartDate asc' => __('시작일 ↓'),
            'dt.displayStartDate desc' => __('시작일 ↑'),
            'dt.displayEndDate asc' => __('종료일 ↓'),
            'dt.displayEndDate desc' => __('종료일 ↑'),
            'dt.themeNm asc' => __('기획전명 ↓'),
            'dt.themeNm desc' => __('기획전명 ↑'),
        );

        // -
        $this->search['sort'] = gd_isset($searchData['sort'], 'dt.regDt desc');
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');

        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['displayFl'] = gd_isset($searchData['displayFl'], 'all');
        $this->search['mobileFl'] = gd_isset($searchData['mobileFl'], 'all');
        $this->search['device'] = gd_isset($searchData['device']);
        $this->search['displayCategory'] = gd_isset($searchData['displayCategory']);
        $this->search['statusText'] = gd_isset($searchData['statusText']);
        $this->search['sno'] = gd_isset($searchData['sno']);
        if ($this->search['device'] && $searchData['device'] != 'all') {
            $_pcFl = substr($searchData['device'], 0, 1);
            $_mobileFl = substr($searchData['device'], 1, 1);
            $this->arrWhere[] = 'dt.pcFl = ?';
            $this->arrWhere[] = 'dt.mobileFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['pcFl'], $_pcFl);
            $this->db->bind_param_push($this->arrBind, $fieldType['mobileFl'], $_mobileFl);
        }

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['mobileFl'][$searchData['mobileFl']] = $this->checked['displayFl'][$searchData['displayFl']] = $this->checked['displayCategory'][$searchData['displayCategory']] = $this->checked['device'][$searchData['device']] = $this->checked['statusText'][$searchData['statusText']] = "checked='checked'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 'dt.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 테마명 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('dt.themeNm', 'dt.themeDescription');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                if ($this->search['key'] == 'writer') {
                    $this->arrWhere[] = '(m.managerId LIKE concat(\'%\',?,\'%\') OR m.managerNm LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else {
                    $this->arrWhere[] = 'dt.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
            }
        }

        // 구매 상품 범위 검색
        if ($this->search['displayFl'] != 'all') {
            $this->arrWhere[] = 'displayFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['displayFl'], $this->search['displayFl']);
        }

        // 쇼핑몰유형
        if ($this->search['mobileFl'] != 'all') {
            $this->arrWhere[] = 'dt.mobileFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['mobileFl'], $this->search['mobileFl']);
        }

        //진열유형
        if ($this->search['displayCategory']) {
            $this->arrWhere[] = 'dt.displayCategory = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['displayCategory'], $this->search['displayCategory']);
        }

        //진행상태
        if ($this->search['statusText']) {
            $nowDate = date("Y-m-d H:i:s");
            switch($this->search['statusText']){
                //대기
                case 'product':
                    $this->arrWhere[] = '? < dt.displayStartDate';
                    $this->db->bind_param_push($this->arrBind, $fieldType['displayStartDate'], $nowDate);
                    break;

                //진행중
                case 'order':
                    $this->arrWhere[] = '(? > dt.displayStartDate && ? < dt.displayEndDate)';
                    $this->db->bind_param_push($this->arrBind, $fieldType['displayStartDate'], $nowDate);
                    $this->db->bind_param_push($this->arrBind, $fieldType['displayEndDate'], $nowDate);
                    break;

                //종료
                case 'delivery':
                    $this->arrWhere[] = '? > dt.displayEndDate';
                    $this->db->bind_param_push($this->arrBind, $fieldType['displayEndDate'], $nowDate);
                    break;
            }
        }

        //sno로 검색
        if ((int)$this->search['sno'] > 0) {
            $this->arrWhere[] = 'dt.sno = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['sno']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }

    /**
     * getDataThemeCongif
     *
     * @param null $sno
     * @return mixed
     * @internal param null $themeCd
     */
    public function getDataDisplayTheme($sno = null,$kind='main')
    {
        // --- 등록인 경우
        if (!$sno) {
            // 기본 정보
            $data['mode'] = 'register';
            $data['kind'] = $kind;
            // 기본값 설정
            DBTableField::setDefaultData('tableDisplayTheme', $data);

            // --- 수정인 경우
        } else {
            // 테마 정보
            $data = $this->getDisplayThemeInfo($sno);

            if ($data['kind'] == 'event') $goodsNoData = explode(INT_DIVISION, $data['goodsNo']);
            else $goodsNoData = explode(STR_DIVISION, $data['goodsNo']);

            if ($goodsNoData) {
                unset($data['goodsNo']);

                if($data['kind'] === 'event' && $data['displayCategory'] === 'g'){
                    $eventGroupTheme = \App::load('\\Component\\Promotion\\EventGroupTheme');

                    //기획전 그룹형일 경우
                    $data['eventGroup'] = $eventGroupTheme->getDataEventGroupList($sno);
                }
                else {
                    $strSQL = 'SELECT iconNm,iconImage,iconCd FROM ' . DB_MANAGE_GOODS_ICON .' WHERE iconUseFl = "y"';
                    $tmpIcon = $this->db->query_fetch($strSQL);
                    foreach($tmpIcon as $k => $v ) {
                        $setIcon[$v['iconCd']]['iconImage'] = $v['iconImage'];
                        $setIcon[$v['iconCd']]['iconNm'] = $v['iconNm'];
                    }

                    //상품 혜택 모듈
                    $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

                    foreach ($goodsNoData as $k => $v) {
                        if ($v) {
                            if ($data['kind'] == 'event') {
                                if (gd_isset($this->getGoodsDataDisplay($v))) {
                                    $data['goodsNo'][$k] = $this->getGoodsDataDisplay($v);
                                }
                            } else {
                                $data['goodsNo'][$k] = $this->getGoodsDataDisplay($v);
                            }

                            //시스템아이콘, 운영자노출아이콘추가
                            foreach ($data['goodsNo'][$k] as $key => $val) {
                                //상품혜택 사용시 해당 변수 재설정
                                $val = $goodsBenefit->goodsDataReset($val);

                                $tmpGoodsIcon = [];
                                $iconList = $this->getGoodsDetailIcon($val['goodsNo']); // 상품 아이콘 테이블 분리
                                foreach ($iconList as $iconKey => $iconVal) {
                                    if ($iconVal['iconKind'] == 'pe') {
                                        if (empty($iconVal['goodsIconStartYmd']) === false && empty($iconVal['goodsIconEndYmd']) === false && empty($iconVal['goodsIconCd']) === false && strtotime($iconVal['goodsIconStartYmd']) <= time() && strtotime($iconVal['goodsIconEndYmd']) >= time()) {
                                            $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                                        }
                                    }

                                    if ($iconVal['iconKind'] == 'un') {
                                        $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                                    }
                                }

                                if ($tmpGoodsIcon) {
                                    $tmpGoodsIcon = ArrayUtils::removeEmpty($tmpGoodsIcon); // 빈 배열 정리

                                    foreach ($tmpGoodsIcon as $iKey => $iVal) {
                                        if (isset($setIcon[$iVal])) {
                                            $icon = UserFilePath::icon('goods_icon', $setIcon[$iVal]['iconImage']);
                                            if ($icon->isFile()) {
                                                $val['goodsIcon'] .= gd_html_image($icon->www(), $setIcon[$iVal]['iconNm']) . ' ';
                                            }
                                        }
                                    }
                                }
                                if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
                                    $val['goodsIcon'] .= gd_html_image(UserFilePath::icon('goods_icon')->www() . '/' . 'icon_soldout.gif', '품절상품');
                                }
                                if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                                    $strScmSQL = 'SELECT sno FROM ' . DB_TIME_SALE . ' ts WHERE FIND_IN_SET('.$val['goodsNo'].', REPLACE(ts.goodsNo,"'.INT_DIVISION.'",","))  AND ts.endDt > "'.date('Y-m-d H:i:s').'"';
                                    $tmpScmData = $this->db->query_fetch($strScmSQL,null,false);
                                    $val['timeSaleSno'] = gd_isset($tmpScmData['sno']);
                                    if($val['timeSaleSno']) {
                                        $val['goodsIcon'] .=  " <img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' /> ";
                                    }
                                }

                                $data['goodsNo'][$k][$key]['goodsIcon'] = $val['goodsIcon'];
                            }

                        } else {
                            $data['goodsNo'][$k] =[];
                        }
                    }
                }
            }

            if ($data['fixGoodsNo']) {
                $fixGoodsNo = explode(STR_DIVISION, $data['fixGoodsNo']);
                unset($data['fixGoodsNo']);
                foreach ($fixGoodsNo as $k => $v) {
                    if ($v) {
                        $data['fixGoodsNo'][$k] = explode(INT_DIVISION, $v);
                    }
                }
            }

            $data['mode'] = 'modify';
            if ($data['displayStartDate']) {
                $nowDate = strtotime(date("Y-m-d H:i:s"));
                $_displayStartDate = strtotime($data['displayStartDate']);
                $_displayEndDate = strtotime($data['displayEndDate']);
                if ($nowDate < $_displayStartDate) {
                    $data['status'] = 'wait';
                } else if ($nowDate > $_displayStartDate && $nowDate < $_displayEndDate) {
                    $data['status'] = 'active';
                } else if ($nowDate > $_displayEndDate) {
                    $data['status'] = 'end';
                } else {
                    $data['status'] = 'error';
                }

                $displayStartDateObj = date_create($data['displayStartDate']);
                $displayEndDateObj = date_create($data['displayEndDate']);
                unset($data['displayStartDate']);
                unset($data['displayEndDate']);
                $data['displayStartDate']['date'] = date_format($displayStartDateObj, "Y-m-d");
                $data['displayStartDate']['time'] = date_format($displayStartDateObj, "H:i:s");
                $data['displayEndDate']['date'] = date_format($displayEndDateObj, "Y-m-d");
                $data['displayEndDate']['time'] = date_format($displayEndDateObj, "H:i:s");
            }

            $data['eventSaleUrl'] = $this->getEventSaleUrl(Request::get()->get('sno'), false, true);
            $data['mobileEventSaleUrl'] = $this->getEventSaleUrl(Request::get()->get('sno'), true, true);

            gd_isset($data['pcFl'], 'y');
            gd_isset($data['mobileFl'], 'y');

            if($data['sortAutoFl'] =='y') {

                if($data['exceptGoodsNo']) {
                    $data['exceptGoodsNo'] = $this->getGoodsDataDisplay($data['exceptGoodsNo']);
                }

                if($data['exceptCateCd']) {
                    $cate = \App::load('\\Component\\Category\\CategoryAdmin');
                    $tmp['code'] = explode(INT_DIVISION, $data['exceptCateCd']);
                    foreach ($tmp['code'] as $val) {
                        $tmp['name'][] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val));
                    }

                    $data['exceptCateCd'] = $tmp;
                    unset($tmp);
                }

                if($data['exceptBrandCd']) {
                    $brand = \App::load('\\Component\\Category\\BrandAdmin');
                    $tmp['code'] = explode(INT_DIVISION, $data['exceptBrandCd']);
                    foreach ($tmp['code'] as $val) {
                        $tmp['name'][] = gd_htmlspecialchars_decode($brand->getCategoryPosition($val));
                    }

                    $data['exceptBrandCd'] = $tmp;
                    unset($tmp);
                }

                if($data['exceptScmNo']) {
                    $scm = \App::load('\\Component\\Scm\\ScmAdmin');
                    $data['exceptScmNo'] =  $scm->getScmSelectList($data['exceptScmNo']);
                }
            }

            // 기본값 설정
            DBTableField::setDefaultData('tableDisplayTheme', $data);
        }

        if($data['kind'] === 'event') {
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');
            $data['seoTag']['target'] = 'event';
            $data['seoTag']['config'] = $seoTag->seoConfig['tag'];
            $data['seoTag']['replaceCode'] = $seoTag->seoConfig['replaceCode']['event'];
            if(empty($data['seoTagSno']) === false) {
                $data['seoTag']['data'] = $seoTag->getSeoTagData($data['seoTagSno']);
            }
        }

        $getData['data'] = $data;

        $checked = array();
        $device = gd_isset($data['pcFl'], 'y') . gd_isset($data['mobileFl'], 'y');
        $checked['device'][$device] = 'checked="checked"';

        $checked['seoTagFl'][$data['seoTagFl']] = $checked['sortAutoFl'][$data['sortAutoFl']] = $checked['moreTopFl'][$data['moreTopFl']] = $checked['moreBottomFl'][$data['moreBottomFl']] = $checked['mobileFl'][$data['mobileFl']] = $checked['displayFl'][$data['displayFl']] = $checked['descriptionSameFl'][$data['descriptionSameFl']] = $checked['displayCategory'][$data['displayCategory']] = "checked='checked'";
        $selected['themeCd'][$data['themeCd']] = "selected='selected'";
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;
        return $getData;
    }


    /**
     * saveInfoThemeConfig
     *
     * @param $arrData
     * @throws Exception
     */
    public function saveInfoDisplayTheme($arrData)
    {
        gd_isset($arrData['kind'], 'main');

        if ($arrData['kind'] == 'event') {
            $arrData['displayFl'] = 'y';
            $arrData['mobileFl'] = $arrData['pcFl'] = 'n';
            $arrData['pcFl'] = substr($arrData['device'], 0, 1);
            $arrData['mobileFl'] = substr($arrData['device'], 1, 1);
        }

        $arrData['managerNo'] = Session::get('manager.sno');
        if ($arrData['displayStartDate'] && $arrData['displayEndDate']) {
            $arrData['displayStartDate'] = $arrData['displayStartDate']['date'] . ' ' . $arrData['displayStartDate']['time'];
            $arrData['displayEndDate'] = $arrData['displayEndDate']['date'] . ' ' . $arrData['displayEndDate']['time'];
        }

        // 테마명 체크
        if (Validator::required(gd_isset($arrData['themeNm'])) === false) {
            throw new \Exception(__('테마명은 필수 항목입니다.'), 500);
        }

        $goodsNoData = [];
        if (is_array($arrData['goodsNoData'])) {
            if (gd_isset($arrData['tabGoodsCnt']) && is_array($arrData['tabGoodsCnt'])) {
                $startGoodsNo = 0;
                foreach ($arrData['tabGoodsCnt'] as $k => $v) {
                    $goodsNoData[] = array_slice($arrData['goodsNoData'], $startGoodsNo, $v);
                    $startGoodsNo += $v;
                }
            } else {
                $goodsNoData[] = $arrData['goodsNoData'];
            }
        } else {
            foreach ($arrData['tabGoodsCnt'] as $k => $v) {
                $goodsNoData[$k] = [];
            }
        }

        if($arrData['sortAutoFl'] =='y') {
            $arrData['goodsNo'] = "";

            if(in_array('goods',$arrData['presentExceptFl']) && $arrData['exceptGoods'] ) {
                $arrData['exceptGoodsNo'] =  implode(INT_DIVISION, $arrData['exceptGoods']);
            }

            if(in_array('category',$arrData['presentExceptFl']) && $arrData['exceptCategory'] ) {
                $arrData['exceptCateCd'] =  implode(INT_DIVISION, $arrData['exceptCategory']);
            }

            if(in_array('brand',$arrData['presentExceptFl']) && $arrData['exceptBrand'] ) {
                $arrData['exceptBrandCd'] =  implode(INT_DIVISION, $arrData['exceptBrand']);
            }

            if(in_array('scm',$arrData['presentExceptFl']) && $arrData['exceptScm'] ) {
                $arrData['exceptScmNo'] =  implode(INT_DIVISION, $arrData['exceptScm']);
            }

            unset($arrData['exceptGoods']);
            unset($arrData['exceptCategory']);
            unset($arrData['exceptBrand']);
            unset($arrData['exceptScm']);

        }  else {
            foreach ($goodsNoData as $k => $v) {
                $goodsNoData[$k] = implode(INT_DIVISION, $v);
            }
            $arrData['goodsNo'] = implode(STR_DIVISION, $goodsNoData);

            unset($arrData['exceptGoodsNo']);
            unset($arrData['exceptCateCd']);
            unset($arrData['exceptBrandCd']);
            unset($arrData['exceptScmNo']);
        }


        $fixGoodsNo = explode(STR_DIVISION, $arrData['fixGoodsNo']);
        //정렬관련
        /*
        if ($arrData['sort']) {

            if (is_array($fixGoodsNo) && gd_isset($fixGoodsNo)) {
                foreach ($fixGoodsNo as $key => $value) {
                    $sortFix = explode(INT_DIVISION, $value);

                    if (is_array($sortFix))

                        foreach ($sortFix as $k => $v) {
                            if ($v) {
                                $sortNum = array_search($v, $goodsNoData[$key]);
                                $fixSortArray[$key][$sortNum] = $v;
                                unset($goodsNoData[$key][$sortNum]);
                            }
                        }

                }
            }

            $sotrGoodsNo = [];
            foreach ($goodsNoData as $key => $value) {
                if ($value) {
                    $goodsArray = [];
                    $goodsList = $this->getGoodsDataDisplay(implode(INT_DIVISION, $value), $arrData['sort']);

                    foreach ($goodsList as $k => $v) {
                        $goodsArray[] = $v['goodsNo'];
                    }

                    if ($fixSortArray[$key]) {
                        $tmp_pre = [];
                        foreach ($fixSortArray[$key] as $k => $v) {
                            $tmp_pre = array_slice($goodsArray, 0, $k);
                            $tmp_next = array_slice($goodsArray, $k);
                            $tmp_pre[] = $v;
                            $goodsArray = array_merge($tmp_pre, $tmp_next);
                        }
                    }
                    $sotrGoodsNo[] = implode(INT_DIVISION, $goodsArray);
                } else {
                    $sotrGoodsNo[] = '';
                }

            }

            $arrData['goodsNo'] = implode(STR_DIVISION, $sotrGoodsNo);

        } else {

            foreach ($goodsNoData as $k => $v) {
                $goodsNoData[$k] = implode(INT_DIVISION, $v);
            }
            $arrData['goodsNo'] = implode(STR_DIVISION, $goodsNoData);

        }
        */

        foreach($arrData['imageDel'] as $k => $v) {
            Storage::disk(Storage::PATH_CODE_DISPLAY)->delete($arrData[$k]);
            $arrData[$k] = '';
        }

        if($arrData['kind'] === 'event') {
            $seoTagData = $arrData['seoTag'];
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');
            if (empty($arrData['seoTagFl']) === false) {
                $seoTagData['sno'] = $arrData['seoTagSno'];
                $seoTagData['pageCode'] = $arrData['sno'];
                $arrData['seoTagSno'] = $seoTag->saveSeoTagEach('event',$seoTagData);
            }
        }

        // 테마명 정보 저장
        if ($arrData['mode'] == 'main_modify') {
            $arrBind = $this->db->get_binding(DBTableField::tableDisplayTheme(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);

        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableDisplayTheme(), $arrData, 'insert');
            $this->db->set_insert_db(DB_DISPLAY_THEME, $arrBind['param'], $arrBind['bind'], 'y');
            $arrData['sno'] = $this->db->insert_id();
        }

        //기획전 그룹형 등록
        if($arrData['kind'] === 'event' && $arrData['displayCategory'] === 'g'){
            $eventGroupTheme = \App::load('\\Component\\Promotion\\EventGroupTheme');

            //기존데이터 삭제
            if(count($arrData['eventGroupDeleteNo']) > 0){
                foreach($arrData['eventGroupDeleteNo'] as $key => $eventGroupNo){
                    if(trim($eventGroupNo) !== ''){
                        $eventGroupTheme->deleteOriginalEventData($eventGroupNo);
                    }
                }
            }

            //등록
            if(count($arrData['eventGroupTmpNo']) > 0){
                foreach($arrData['eventGroupTmpNo'] as $key => $eventGroupTempNo){
                    if(trim($eventGroupTempNo) !== ''){
                        $groupInsertSnoArray[$eventGroupTempNo] = $eventGroupTheme->saveEventGroupTheme($eventGroupTempNo, $arrData['sno']);
                    }
                }
            }

            //순서정렬
            if(count($arrData['eventGroupTmpNo']) > 0 || count($arrData['eventGroupNo']) > 0){
                foreach($arrData['eventGroupTmpNo'] as $key => $eventGroupTempNo){
                    if(trim($groupInsertSnoArray[$eventGroupTempNo]) !== ''){
                        $eventGroupTheme->updateGroupThemeSort($groupInsertSnoArray[$eventGroupTempNo], (int)$key+1);
                    }
                    else if(trim($arrData['eventGroupNo'][$key]) !== ''){
                        $eventGroupTheme->updateGroupThemeSort($arrData['eventGroupNo'][$key], (int)$key+1);
                    }
                    else {}
                }
            }
            unset($groupInsertSnoArray);
        }

        unset($arrBind);

        //이미지명
        $filesValue = Request::files()->toArray();


        if ($filesValue) {
            $fileData = [];
            foreach ($filesValue as $k => $v) {
                $fileDate = $v;
                if ($fileDate['name']) {
                    if (gd_file_uploadable($fileDate, 'image') === true) {  // 이미지 업로드
                        $imageExt = strrchr($v['name'], '.');
                        $fileData[$k] = $arrData['sno']."_".$k. $imageExt; // 이미지명 공백 제거
                        $targetImageFile = $fileData[$k];
                        $tmpImageFile = $v['tmp_name'];
                        Storage::disk(Storage::PATH_CODE_DISPLAY)->upload($tmpImageFile, $targetImageFile);
                    } else {
                        throw new \Exception(__('이미지파일만 가능합니다.'));
                    }
                }
            }

            if($fileData) {
                $arrBind = $this->db->get_binding(DBTableField::getBindField('tableDisplayTheme', array_keys($fileData)), $fileData, 'update');
                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
        }

        $this->setRefreshThemeConfig($arrData['kind']);


        if ($arrData['mode'] == 'main_modify') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('display_main', null, 'modify', $arrData['sno'], $arrData['themeNm']);
        }
    }

    public function saveInfoDisplayThemeImage($arrFileData)
    {


        if ($arrFileData) {
            if (gd_file_uploadable($arrFileData, 'image')) {


                $imageExt = strrchr($arrFileData['name'], '.');
                $newImageName =  $giftNo.'_'.rand(1,100) .  $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기

                $targetImageFile = $this->imagePath . $newImageName;
                $thumbnailImageFile[] = $this->imagePath . PREFIX_GIFT_THUMBNAIL_SMALL . $newImageName;
                $thumbnailImageFile[] = $this->imagePath . PREFIX_GIFT_THUMBNAIL_LARGE . $newImageName;
                $tmpImageFile = $arrFileData['tmp_name'];

                //                $this->storageHandler->upload($tmpImageFile, $strImageStorage, $targetImageFile);
                //                $this->storageHandler->uploadThumbImage($tmpImageFile, $strImageStorage, $thumbnailImageFile[0],'50');
                //                $this->storageHandler->uploadThumbImage($tmpImageFile, $strImageStorage, $thumbnailImageFile[1],'100');
                Storage::disk(Storage::PATH_CODE_GIFT, $strImageStorage)->upload($tmpImageFile, $targetImageFile);
                Storage::disk(Storage::PATH_CODE_GIFT, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile[0], ['width' => 50]);
                Storage::disk(Storage::PATH_CODE_GIFT, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile[1], ['width' => 100]);

                return $newImageName;
            }
        }

    }

    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function getJsonListDisplayTheme($mobileFl = 'y', $themeCd = 'B')
    {

        $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $getData = $displayConfig->getInfoThemeConfigCate($themeCd, $mobileFl);

        if (count($getData) > 0) {
            return json_encode(gd_htmlspecialchars_stripslashes($getData));
        } else {
            return false;
        }

    }


    /**
     * refreshThemeConfig
     *
     * @param $themeCd
     */
    public function setRefreshThemeConfig($kind = 'main')
    {

        if ($kind == 'main') {
            $themeCate = "B";
        } else {
            $themeCate = "F";
        }

        $strSQL = "UPDATE " . DB_DISPLAY_THEME_CONFIG . " SET useCnt = 0 WHERE themeCate = '" . $themeCate . "'";
        $this->db->query($strSQL);

        $strSQL = 'SELECT COUNT(themeCd) as count ,themeCd FROM ' . DB_DISPLAY_THEME . ' WHERE kind = "' . $kind . '" GROUP BY themeCd';
        $data = $this->db->query_fetch($strSQL, null);

        if($data) {
            foreach ($data as $k => $v) {
                if ($v['themeCd']) {
                    $arrBind = [];
                    $arrUpdate[] = 'useCnt =' . $v['count'];
                    $this->db->bind_param_push($arrBind, 's', $v['themeCd']);
                    $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                    unset($arrUpdate);
                    unset($arrBind);
                }
            }
        }

        // 모바일 테마 적용 개수 오류 수정
        $strSQL = 'SELECT COUNT(mobileThemeCd) as count ,mobileThemeCd FROM ' . DB_DISPLAY_THEME . ' WHERE kind = "' . $kind . '" GROUP BY mobileThemeCd';
        $data = $this->db->query_fetch($strSQL, null);

        if($data) {
            foreach ($data as $k => $v) {
                if ($v['mobileThemeCd']) {
                    $arrBind = [];
                    $arrUpdate[] = 'useCnt =' . $v['count'];
                    $this->db->bind_param_push($arrBind, 's', $v['mobileThemeCd']);
                    $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                    unset($arrUpdate);
                    unset($arrBind);
                }
            }
        }

        if ($kind == 'event') {
            $strSQL = 'SELECT COUNT(pcThemeCd) as count ,pcThemeCd as themeCd FROM ' . DB_TIME_SALE . ' GROUP BY pcThemeCd';
            $pcData = $this->db->query_fetch($strSQL, null);
            if($pcData) {
                foreach ($pcData as $k => $v) {
                    if ($v['themeCd']) {
                        $arrBind = [];
                        $arrUpdate[] = 'useCnt = useCnt + ' . $v['count'];
                        $this->db->bind_param_push($arrBind, 's', $v['themeCd']);
                        $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                        unset($arrUpdate);
                        unset($arrBind);
                    }
                }
            }

            $strSQL = 'SELECT COUNT(mobileThemeCd) as count ,mobileThemeCd as themeCd FROM ' . DB_TIME_SALE . ' GROUP BY mobileThemeCd';
            $mobileData = $this->db->query_fetch($strSQL, null);
            if($mobileData) {
                foreach ($mobileData as $k => $v) {
                    if ($v['themeCd']) {
                        $arrBind = [];
                        $arrUpdate[] = 'useCnt = useCnt + ' . $v['count'];
                        $this->db->bind_param_push($arrBind, 's', $v['themeCd']);
                        $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                        unset($arrUpdate);
                        unset($arrBind);
                    }
                }
            }
        }
    }


    /**
     * deleteDisplayMain
     *
     * @param $sno
     */
    public function setDeleteDisplayTheme($sno, $themeCd)
    {

        $strSQL = 'SELECT goodsNo, kind, displayCategory FROM ' . DB_DISPLAY_THEME . ' WHERE sno = "' . $sno . '" LIMIT 1';
        $themeData = $this->db->query_fetch($strSQL, null)[0];

        $this->db->bind_param_push($arrBind['bind'], 's', $sno);
        $this->db->set_delete_db(DB_DISPLAY_THEME, 'sno = ?', $arrBind['bind']);

        if($themeData['kind'] === 'event'){
            //기획전 그룹형 그룹 삭제
            if($themeData['displayCategory'] === 'g'){
                $eventGroupTheme = \App::load('\\Component\\Promotion\\EventGroupTheme');
                $eventGroupTheme->deleteEventGroupTheme($sno);
            }
            //기획전 관련설정 삭제
            $otherEventData = gd_policy('promotion.event');
            if(in_array($sno, $otherEventData['otherEventNo'])){
                $deleteArraykey = array_search($sno, $otherEventData['otherEventNo']);
                unset($otherEventData['otherEventNo'][$deleteArraykey]);
                $otherEventData['otherEventNo'] = array_values($otherEventData['otherEventNo']);

                $policy = \App::load('\\Component\\Policy\\Policy');
                $policy->saveEventConfig($otherEventData);
            }

            // seo태그 삭제
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');
            $seoPath = array_flip($seoTag->seoConfig['commonPage']);
            $seoTag->deleteSeoTag(['path' => $seoPath[$themeData['kind']], 'pageCode' => $sno]);
        }

        $this->setRefreshThemeConfig($themeData['kind']);

    }

    /**
     * 검색페이지 테마 체크
     *
     * @param $themeCd
     */
    public function setRefreshSearchThemeConfig($pcThemeCd, $mobileThemeCd)
    {
        $strSQL = "UPDATE " . DB_DISPLAY_THEME_CONFIG . " SET useCnt = 0 WHERE themeCate = 'A'";
        $this->db->query($strSQL);

        $strSQL = "UPDATE " . DB_DISPLAY_THEME_CONFIG . " SET useCnt = 1 WHERE themeCd = '" . $pcThemeCd . "'";
        $this->db->query($strSQL);

        $strSQL = "UPDATE " . DB_DISPLAY_THEME_CONFIG . " SET useCnt = 1 WHERE themeCd = '" . $mobileThemeCd . "'";
        $this->db->query($strSQL);

    }

    /**
     * 모바일샵 메인 상품 진열 설정의 등록 및 수정에 관련된 정보
     *
     * @param integer $dataSno 상품 테마 sno
     * @return array 모바일샵 메인 상품 진열 및 테마 정보
     */
    public function getDataDisplayThemeMobile($dataSno = null)
    {
        // --- 등록인 경우
        if (is_null($dataSno)) {
            // 기본 정보
            $data['mode'] = 'display_theme_mobile_register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableDisplayThemeMobile', $data);

            // --- 수정인 경우
        } else {
            $data = $this->getDisplayThemeMobileInfo($dataSno);
            $data['goodsNo'] = $this->getGoodsDataDisplay($data['goodsNo']);
            $data['mode'] = 'display_theme_mobile_modify';
            // 기본값 설정
            DBTableField::setDefaultData('tableDisplayThemeMobile', $data);
        }

        $checked = array();
        $checked['themeUseFl'][$data['themeUseFl']] = $checked['listType'][$data['listType']] = $checked['imageCd'][$data['imageCd']] = $checked['imageFl'][$data['imageFl']] = $checked['goodsNmFl'][$data['goodsNmFl']] = $checked['priceFl'][$data['priceFl']] = $checked['mileageFl'][$data['mileageFl']] = $checked['soldOutFl'][$data['soldOutFl']] = $checked['soldOutIconFl'][$data['soldOutIconFl']] = $checked['iconFl'][$data['iconFl']] = $checked['shortDescFl'][$data['shortDescFl']] = $checked['brandFl'][$data['brandFl']] = $checked['makerFl'][$data['makerFl']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;
        return $getData;
    }

    /**
     * 모바일샵 메인 상품 설정 저장
     *
     * @param array $arrData 저장할 정보의 배열
     * @throws Exception
     */
    public function saveInfoDisplayThemeMobile($arrData)
    {
        // 상품 테마명 체크
        if (Validator::required(gd_isset($arrData['themeNm'])) === false) {
            throw new \Exception(__('상품 테마명은 필수 항목입니다.'), 500);
        }

        // 상품번호 배열 재정렬
        if (gd_isset($arrData['goodsNo'])) {
            if (is_array($arrData['goodsNo'])) {
                $arrData['goodsNo'] = implode(INT_DIVISION, $arrData['goodsNo']);
            }
        } else {
            throw new \Exception(__('진열할 상품은 필수 항목입니다.'), 500);
        }

        // 이미지 폴더의 체크
        $imagePath = UserFilePath::data('mobile');
        if ($imagePath->isDir() === false) {
            @mkdir($imagePath);
            @chmod($imagePath, 0707);
        }

        // 이미지 삭제
        if (isset($arrData['imageDel']) === true) {
            foreach ($arrData['imageDel'] as $val) {
                DataFileFactory::create('local')->setImageDelete('mobile', 'mobile', $arrData[$val . 'Tmp'], 'file');
                $arrData[$val . 'Tmp'] = '';
            }
            unset($arrData['imageDel']);
        }

        // 이미지 업로드

        $files = Request::files()->toArray();
        foreach ($files as $key => $val) {
            if (gd_file_uploadable($files[$key], 'image') === true) {
                $arrData[$key] = DataFileFactory::create('local')->saveFile($files[$key]['name'], $files[$key]['tmp_name']);
            } else {
                if (empty($arrData[$key . 'Tmp']) === false) {
                    $arrData[$key] = $arrData[$key . 'Tmp'];
                }
            }
        }

        // insert , update 체크
        if ($arrData['mode'] == 'display_theme_mobile_modify') {
            $chkType = 'update';
        } else {
            $chkType = 'insert';
        }

        // 정보 저장
        if (in_array($chkType, array('insert', 'update'))) {
            $arrBind = $this->db->get_binding(DBTableField::tableDisplayThemeMobile(), $arrData, $chkType);
            if ($chkType == 'insert') {
                $this->db->set_insert_db(DB_DISPLAY_THEME_MOBILE, $arrBind['param'], $arrBind['bind'], 'y');
            }
            if ($chkType == 'update') {
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
                $this->db->set_update_db(DB_DISPLAY_THEME_MOBILE, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
            unset($arrBind);
        }
    }


    /**
     * setSearchCondition
     *
     */
    public function getDateSearchDisplay()
    {
        $selected = array();
        //검색페이지 상품진열
        $getData['goods'] = gd_policy('search.goods');
        if ($getData['goods']) {

            $getData['goods']['sort'] = $getData['goods']['sort'];
            $selected['goods']['mobileThemeCd'][$getData['goods']['mobileThemeCd']] = $selected['goods']['pcThemeCd'][$getData['goods']['pcThemeCd']] = "selected";


            foreach ($getData['goods']['searchType'] as $k => $v) {
                $checked['goods']['searchType'][$v] = "checked";
            }

        } else {
            $checked['goods']['searchType']['keyword'] = "checked";
            $getData['goods']['sort'] = "regDt desc";

        }

        $getData['terms'] = gd_policy('search.terms');
        if(getData['terms']) {
            // 검색창 통합 조건 선택
            if($getData['terms']['settings']) {
                foreach ($getData['terms']['settings'] as $k => $v) {
                    $checked['terms']['settings'][$v] = "checked";
                }
            } else {
                $checked['terms']['settings']['goodsNm'] = "checked";
            }
        } else {
            $checked['terms']['settings']['goodsNm'] = "checked";
        }

        //상품 검색 키워드 설정
        $getData['keyword'] = gd_policy('search.keyword');
        if ($getData['keyword']) {

            $checked['keyword']['keywordFl'][$getData['keyword']['keywordFl']] = "checked";


        } else {
            $checked['keyword']['keywordFl']['n'] = "checked";
        }

        // 최근 검색어 설정
        $getData['recentKeyword'] = gd_policy('search.recentKeyword');
        $selected['recentKeyword']['pcCount'][$getData['recentKeyword']['pcCount']] = $selected['recentKeyword']['mobileCount'][$getData['recentKeyword']['mobileCount']] = "selected";

        //인기검색어 설정
        $getData['hitKeyword'] = gd_policy('search.hitKeyword');


        //QUICK검색 설정
        $getData['quick'] = gd_policy('search.quick');
        if ($getData['quick']) {

            $checked['quick']['mobileFl'][$getData['quick']['mobileFl']] = $checked['quick']['area'][$getData['quick']['area']] = $checked['quick']['quickFl'][$getData['quick']['quickFl']] = "checked";
            foreach ($getData['quick']['searchType'] as $k => $v) {
                $checked['quick']['searchType'][$v] = "checked";
            }

        } else {
            $checked['quick']['searchType']['keyword'] = $checked['quick']['area']['right'] = $checked['quick']['quickFl']['n'] = "checked";
        }


        $getData['set']['searchType'] = array(
            'keyword' => __('검색어'),
            'category' => __('카테고리'),
            'brand' => __('브랜드'),
            'price' => __('가격'),
            'delivery' => __('무료배송'),
            'regdt' => __('최근등록상품'),
            'color' => __('대표색상'),
            'icon' => __('아이콘')
        );
        $getData['set']['terms'] = array(
            'goodsNm' => '상품명',
            'brandNm' => '브랜드',
            'goodsNo' => '상품코드',
            'makerNm' => '제조사',
            'originNm' => '원산지',
            'goodsSearchWord' => '검색키워드'
        );
        $data['data'] = $getData;
        $data['checked'] = $checked;
        $data['selected'] = $selected;

        return $data;
    }

    /**
     * 품절상품진열 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoDisplaySoldOut($arrData)
    {
        $filesValue = Request::files()->toArray();


        $imageArr = array('soldout_overlay', 'soldout_icon', 'soldout_price');


        $image_path = UserFilePath::icon('goods_icon')->www();


        foreach ($arrData['pc'] as $k => $v) {
            if($k == 'deleteOverlayCustomImage' && $v == 'y') {
                @unlink(UserFilePath::getBasePath().self::DEFAULT_PC_CUSTOM_SOLDOUT_OVERLAY_PATH);
            }

            if (in_array($k, $imageArr)) {

                $fileDate = $filesValue['pc_' . $k];

                if ($v == 'custom') {

                    $targetImageFile = '/custom/' . $k;
                    if ($fileDate['name'] && gd_file_uploadable($fileDate, 'image') === true) {
                        // 이미지 업로드
                        $tmpImageFile = $fileDate['tmp_name'];
                        Storage::disk(Storage::PATH_CODE_GOODS_ICON, 'local')->upload($tmpImageFile, $targetImageFile);
                    }
                    $arrData['pc'][$k . '_img'] = $image_path . $targetImageFile;
                } else {
                    switch ($k) {
                        case 'soldout_overlay':
                            $arrData['pc'][$k . '_img'] = $image_path . '/soldout-' . $v . '.png';
                            break;
                        case 'soldout_icon':
                            $arrData['pc'][$k . '_img'] = $arrData['pc'][$k . '_img'] = $image_path . '/' . 'icon_soldout.gif';
                            break;
                    }
                }

            }
        }

        if($arrData['isMobile']) $addText = "m-";
        else  $addText = "";

        foreach ($arrData['mobile'] as $k => $v) {
            if($k == 'deleteOverlayCustomImage' && $v == 'y') {
                @unlink(UserFilePath::getBasePath().self::DEFAULT_MOBILE_CUSTOM_SOLDOUT_OVERLAY_PATH);
            }

            if (in_array($k, $imageArr)) {
                $fileDate = $filesValue['mobile_' . $k];

                if ($v == 'custom') {

                    $targetImageFile = '/custom/' . $k . '_mobile';
                    if ($fileDate['name'] && gd_file_uploadable($fileDate, 'image') === true) {
                        // 이미지 업로드
                        $tmpImageFile = $fileDate['tmp_name'];
                        Storage::disk(Storage::PATH_CODE_GOODS_ICON, 'local')->upload($tmpImageFile, $targetImageFile);

                    }
                    $arrData['mobile'][$k . '_img'] = $image_path . $targetImageFile;
                } else {
                    switch ($k) {
                        case 'soldout_overlay':
                            $arrData['mobile'][$k . '_img'] = $image_path . '/'.$addText.'soldout-' . $v . '.png';
                            break;
                        case 'soldout_icon':
                            $arrData['mobile'][$k . '_img'] = $arrData['mobile'][$k . '_img'] = $image_path . '/' . $addText.'icon_soldout.gif';
                            break;
                    }
                }

            }
        }


        gd_set_policy('soldout.pc', $arrData['pc']);
        gd_set_policy('soldout.mobile', $arrData['mobile']);

    }

    /**
     * getDateSoldOutDisplay
     *
     */
    public function getDateSoldOutDisplay()
    {
        $getData['pc'] = gd_policy('soldout.pc');
        if (!$getData['pc']) {

            $getData['pc']['soldout_overlay'] = "0";
            $getData['pc']['soldout_icon'] = "disable";
            $getData['pc']['soldout_price'] = "price";

        }

        $checked['pc']['soldout_overlay'][$getData['pc']['soldout_overlay']] = $checked['pc']['soldout_icon'][$getData['pc']['soldout_icon']] = $checked['pc']['soldout_price'][$getData['pc']['soldout_price']] = "checked";

        if(file_exists(UserFilePath::getBasePath().self::DEFAULT_PC_CUSTOM_SOLDOUT_OVERLAY_PATH)){
            $getData['pc']['soldout_overlay_custom_exists'] = 'y';
        }
        else {
            $getData['pc']['soldout_overlay_custom_exists'] = 'n';
        }
        $getData['mobile'] = gd_policy('soldout.mobile');


        if (!$getData['mobile']) {

            $getData['mobile']['soldout_overlay'] = "0";
            $getData['mobile']['soldout_icon'] = "disable";
            $getData['mobile']['soldout_price'] = "price";

        }

        $checked['mobile']['soldout_overlay'][$getData['mobile']['soldout_overlay']] = $checked['mobile']['soldout_icon'][$getData['mobile']['soldout_icon']] = $checked['mobile']['soldout_price'][$getData['mobile']['soldout_price']] = "checked";

        if(file_exists(UserFilePath::getBasePath().self::DEFAULT_MOBILE_CUSTOM_SOLDOUT_OVERLAY_PATH)){
            $getData['mobile']['soldout_overlay_custom_exists'] = 'y';
        }
        else {
            $getData['mobile']['soldout_overlay_custom_exists'] = 'n';
        }

        $getData['pc']['defaultCustomSoldoutOverlayPath'] = self::DEFAULT_PC_CUSTOM_SOLDOUT_OVERLAY_PATH;
        $getData['mobile']['defaultCustomSoldoutOverlayPath'] = self::DEFAULT_MOBILE_CUSTOM_SOLDOUT_OVERLAY_PATH;
        $data['data'] = $getData;
        $data['checked'] = $checked;


        return $data;
    }

    /**
     * 선택상품 복구
     * setGoodsReStore
     *
     * @param $arrData
     */
    public function setGoodsReStore($arrData)
    {

        $this->setBatchGoods($arrData['goodsNo'], array('goodsDisplayFl', 'goodsDisplayMobileFl', 'goodsSellFl', 'goodsSellMobileFl', 'delFl'), array($arrData['goodsDisplayFl'], $arrData['goodsDisplayFl'], $arrData['goodsSellFl'], $arrData['goodsSellFl'], 'n'));

    }

    public function getGoodsByImageName($imageName, $isPaging = false, $req = null)
    {
        if ($isPaging === false) {
            $limitQuery = ' LIMIT 1000 ';
        } else {
            $nowPage = $req['page'] ?? 1;
            $limit = 10;
            $offset = ($nowPage - 1) * $limit;
            $limitQuery = ' LIMIT ' . $offset . ' , ' . $limit;

            $totalCountQuery = $strSQL = "SELECT  count(*) as cnt
                FROM " . DB_GOODS_IMAGE . " as  gi
                LEFT JOIN " . DB_GOODS . " as g ON gi.goodsno = g.goodsno ";

            $totalCountQuery .= " WHERE gi.imageName='" . $imageName . "' AND g.delFl = 'n' ";
            $totalCount = $this->db->query_fetch($totalCountQuery, null, false)['cnt'];

            $page = new Page($nowPage, $totalCount, $totalCount, $limit, 10);
            $pageHtml = $page->getPage('layer_list_search(\'PAGELINK\')');;
        }

        $strSQL = "SELECT   g.goodsNo,g.goodsNm, g.regDt , g.imageStorage, g.imagePath, gi.imageName  , gi.imageKind
                FROM " . DB_GOODS_IMAGE . " as  gi
                LEFT JOIN " . DB_GOODS . " as g ON gi.goodsno = g.goodsno ";

        $strSQL .= "WHERE gi.imageName='" . $imageName . "'  AND g.delFl = 'n' ";
        $strSQL .= $limitQuery;
        $data = $this->db->query_fetch($strSQL, null);

        if ($isPaging) {
            $listNo = $totalCount - $offset;
            foreach ($data as &$row) {
                $row['no'] = $listNo;
                $listNo--;
            }
            $result['list'] = $data;
            $result['page'] = $pageHtml;
            $result['totalCnt'] = $totalCount;
            $result['searchCnt'] = $totalCount;
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * getEventSaleUrl
     * 기획전 url
     * @param $sno
     * @param bool $isMobile
     * @return string
     * @internal param bool $isAbsolutePath
     * @internal param bool $mobileFl
     */
    public function getEventSaleUrl($sno, $isMobile = false)
    {
        $domain = $isMobile ? URI_MOBILE : URI_HOME;
        return $domain . 'goods' . DS . 'event_sale.php?sno=' . $sno;
    }

    public function getTmpGoodsImage($req = null, $isPaging = true , $isGroupByImageName = true)
    {
        $page = $req['page'] ?? 1;
        if ($isPaging) {
            $limit = $req['pageNum'] ?? 10;
            $offset = ($page - 1) * $limit;
        } else {
            $limit = 1000; //제한
            $offset = 0;
        }

        $arrBind = [];
        if ($req['searchField'] && $req['searchKeyword']) {
            $searchWhere[] = $req['searchField'] . " LIKE concat('%',?,'%')";
            $this->db->bind_param_push($arrBind, 's', $req['searchKeyword']);
        }

        if ($req['imageName']) {
            if(is_array($req['imageName'])){
                foreach($req['imageName'] as $val){
                    $val = addslashes($val);
                    $this->db->bind_param_push($arrBind, 's', $val);
                }
                $searchWhere[] = "a.imageName in ('".implode("','",$req['imageName'])."')";
            }
            else{
                $searchWhere[] = "a.imageName LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $req['imageName']);
            }
        }

        if($isGroupByImageName){
            if ($req['isApplyGoods'] == 'y') {
                $searchWhere[] = "b.applyGoodsCount is not null ";
            }
            else if ($req['isApplyGoods'] == 'n') {
                $searchWhere[] = "b.applyGoodsCount is null ";
            }

            $query = "select  distinct a.imageName, ifnull (b.applyGoodsCount,0) as applyGoodsCount FROM  ".DB_TMP_GOODS_IMAGE." as a LEFT OUTER JOIN
(select imageName , count(imageName) as applyGoodsCount from ".DB_TMP_GOODS_IMAGE." where 1 ";
            $query.= " AND status = 'ready' group by imageName ";
            $query.= " ) as b ON a.imageName = b.imageName ";
            if ($searchWhere) {
                $query .= ' WHERE ' . implode(' AND ', $searchWhere);
            }
            $query .= '  LIMIT ' . $offset . ',' . $limit;
            $data = $this->db->query_fetch($query, $arrBind);
            $searchQuery = "select  count(distinct a.imageName) as cnt FROM  ".DB_TMP_GOODS_IMAGE." as a LEFT OUTER JOIN
(select imageName , count(imageName) as applyGoodsCount from ".DB_TMP_GOODS_IMAGE." where 1 ";
            $searchQuery.= " AND status = 'ready' group by imageName ";
            $searchQuery.= " ) as b ON a.imageName = b.imageName ";
            if ($searchWhere) {
                $searchQuery .= ' WHERE ' . implode(' AND ', $searchWhere);
            }
            $searchCount = $this->db->query_fetch($searchQuery, $arrBind, false)['cnt'];

            $totalQuery = 'SELECT count(DISTINCT imageName) as cnt FROM ' . DB_TMP_GOODS_IMAGE;
            $totalCount = $this->db->query_fetch($totalQuery, null, false)['cnt'];

            $possibleQuery = "select  count(distinct imageName) as cnt FROM  es_tmpGoodsImage where status='ready' ";
            $possibleCount = $this->db->query_fetch($possibleQuery, null, false)['cnt'];
            $result['possibleCount'] = $possibleCount;
        }
        else {
            if ($req['isApplyGoods'] == 'y') {
                $searchWhere[] = "a.status = 'ready' ";
            }
            else if ($req['isApplyGoods'] == 'n') {
                $searchWhere[] = "a.status != 'ready' ";
            }

            $query = 'SELECT a.*, g.goodsNm,g.imageStorage , gi.imageName as oriImageName FROM ' . DB_TMP_GOODS_IMAGE . '   as a LEFT OUTER JOIN ' .  DB_GOODS . " as g ON g.goodsNo = a.goodsNo
            LEFT OUTER JOIN ".DB_GOODS_IMAGE." as gi ON gi.imageName = TRIM(a.imageName) AND gi.goodsNo = a.goodsNo AND a.imageKind = gi.imageKind WHERE 1";
            if ($searchWhere) {
                $query .= ' AND  ' . implode(' AND ', $searchWhere);
            }
            $query .= '    LIMIT ' . $offset . ',' . $limit;
            $data = $this->db->query_fetch($query, $arrBind);


            $totalQuery = 'SELECT count(*) as cnt FROM ' . DB_TMP_GOODS_IMAGE;
            $totalCount = $this->db->query_fetch($totalQuery, null, false)['cnt'];

            $searchQuery = 'SELECT count(*) as cnt FROM ' . DB_TMP_GOODS_IMAGE . " as a ";
            if ($searchWhere) {
                $searchQuery .= ' WHERE ' . implode(' AND ', $searchWhere);
            }
            $searchCount = $this->db->query_fetch($searchQuery, $arrBind, false)['cnt'];
        }

        if ($isPaging === false) {
            return $data;
        }

        $listNo = $searchCount - $offset;
        $page = new Page($page, $searchCount, $searchCount, $limit, 10);
        $page->setUrl(Request::getQueryString());;
        //$pageHtml = $page->getPage();;

        foreach ($data as &$row) {
            $row['no'] = $listNo;
            $row['tmpPath'] = Storage::disk(Storage::PATH_CODE_GOODS, 'local')->getHttpPath('tmp/' . $row['imageName']);
            $listNo--;
        }
        $result['list'] = $data;
        $result['page'] = $page;
        $result['totalCnt'] = $totalCount;
        $result['searchCnt'] = $searchCount;
        return $result;
    }


    /**
     * 상품이미지 일괄업로드 시 임시폴더에 있는 파일을 db화
     *
     * @param $arrData
     * @return mixed
     */
    public function saveTmpGoodsImage($arrData)
    {
        $arrData['imageName'] = str_replace("\\","",$arrData['imageName']);// 상품이미지 일괄처리시 오류로 인한 이미지명에 속한 역슬래시 제거하는 부분
        $arrBind = $this->db->get_binding(DBTableField::tableTmpGoodsImage(), $arrData, 'insert', array_keys($arrData));
        $this->db->set_insert_db(DB_TMP_GOODS_IMAGE, $arrBind['param'], $arrBind['bind'], 'y');
        return $this->db->affected_rows();
    }

    /**
     * 상품이미지 일괄업로드 시 임시폴더 Db 수정
     *
     * @param $arrData
     * @return mixed
     */
    public function updateTmpGoodsImage($arrData)
    {
        $arrBind = null;
        $this->db->bind_param_push($arrBind, 's', $arrData['status']);
        $this->db->bind_param_push($arrBind, 'i', $arrData['sno']);
        $this->db->set_update_db(DB_TMP_GOODS_IMAGE,  ' status = ?  '  , ' sno = ?' , $arrBind);
        return $this->db->affected_rows();
    }

    /**
     * 상품이미지 일괄업로드 시 임시폴더에 있는 파일을 삭제
     *
     * @param $imageName
     * @return mixed
     */
    public function deleteTmpGoodsImage(array $imageName)
    {
        foreach ($imageName as $fileName) {
            $fileName = stripslashes($fileName);
            Storage::disk(Storage::PATH_CODE_GOODS, 'local')->delete('tmp' . DS . $fileName);
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $fileName);
            $this->db->set_delete_db(DB_TMP_GOODS_IMAGE, "imageName = ? ", $arrBind);
        }
    }

    /**
     * 대표색상 사용 여부 확인
     *
     * @param $color
     * @return mixed
     */
    public function getGoodsColorCount($color)
    {
        $arrBind = [];
        $strSQL = "SELECT count(goodsNo) as  cnt FROM " . DB_GOODS . " WHERE goodsColor LIKE concat('%',?,'%') ";
        $this->db->bind_param_push($arrBind, 's', $color);
        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
        return $tmp['cnt'];
    }

    /**
     * 상품 상세 설명 수정
     *
     * @param $sDescription
     * @param $goodsNo
     */
    public function setGoodsDescription($sDescription, $goodsNo)
    {
        $arrBind = null;
        $this->db->bind_param_push($arrBind, 's', $sDescription);
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $this->db->setModDtUse(false);
        $this->db->set_update_db(DB_GOODS, 'goodsDescription = ?', 'goodsNo = ?', $arrBind);
    }

    /**
     * 네이버 사용 상품 총 합계
     *
     * @param $arrData
     * @return mixed
     */
    public function getNaverStats() {

        $where[] = 'goodsDisplayFl = \'y\'';
        $where[] = 'delFl = \'n\'';
        $where[] = 'applyFl = \'y\'';
        $where[] = 'NOT(stockFl = \'y\' AND totalStock = 0)';
        $where[] = 'NOT(soldOutFl = \'y\')';
        $where[] = '(UNIX_TIMESTAMP(goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(goodsOpenDt) = 0 OR UNIX_TIMESTAMP(goodsOpenDt) < UNIX_TIMESTAMP())';

        $totalCountSQL =  ' SELECT COUNT(goodsNo) AS totalCnt FROM ' . DB_GOODS . ' as g WHERE '.implode(' AND ', $where);
        $count['total'] = $this->db->query_fetch($totalCountSQL,null,false)['totalCnt'];

        $where[] = 'naverFl = \'y\'';
        $totalCountSQL =  ' SELECT COUNT(goodsNo) AS totalCnt FROM ' . DB_GOODS . ' as g WHERE '.implode(' AND ', $where);
        $count['naver'] = $this->db->query_fetch($totalCountSQL,null,false)['totalCnt'];

        return $count;
    }

    /**
     * 네이버 사용 상품 총 합계
     *
     * @param $arrData
     * @return mixed
     */
    public function getGoodsNaverStats($cateCd) {

        $where[] = 'goodsDisplayFl = \'y\'';
        $where[] = 'delFl = \'n\'';
        $where[] = 'applyFl = \'y\'';
        $where[] = 'NOT(stockFl = \'y\' AND totalStock = 0)';
        $where[] = 'NOT(soldOutFl = \'y\')';
        $where[] = '(UNIX_TIMESTAMP(goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(goodsOpenDt) = 0 OR UNIX_TIMESTAMP(goodsOpenDt) < UNIX_TIMESTAMP())';
        if(is_array($cateCd) && gd_isset($cateCd)) {
            $where[] = "(cateCd = '".implode("' OR cateCd = '", $cateCd)."')";
        } else {
            $where[] = "cateCd = ''";
        }

        $totalCountSQL =  ' SELECT COUNT(goodsNo) as cnt , cateCd  FROM ' . DB_GOODS . ' WHERE '.implode(' AND ', $where)." group by cateCd";
        $tmpData = $this->db->query_fetch($totalCountSQL,null);
        $getData['total'] =array_combine(array_column($tmpData, 'cateCd'), array_column($tmpData, 'cnt'));
        $where[] = 'naverFl = \'y\'';

        $totalCountSQL =  ' SELECT COUNT(goodsNo) as cnt , cateCd  FROM ' . DB_GOODS . ' WHERE '.implode(' AND ', $where)." group by cateCd";
        $tmpData = $this->db->query_fetch($totalCountSQL,null);
        $getData['naver'] =array_combine(array_column($tmpData, 'cateCd'), array_column($tmpData, 'cnt'));

        return $getData;

    }

    /**
     * 다음 쇼핑하우 사용 상품 총 합계
     *
     * @return mixed
     * @throws Exception
     */
    public function getDaumStats()
    {
        $where[] = 'goodsDisplayFl = "y"';
        $where[] = 'delFl = "n"';
        $where[] = 'applyFl = "y"';
        $where[] = 'NOT(stockFl = "y" AND totalStock = 0)';
        $where[] = 'NOT(soldOutFl = "y")';
        $where[] = '(UNIX_TIMESTAMP(goodsOpenDt) IS NULL OR UNIX_TIMESTAMP(goodsOpenDt) = 0 OR UNIX_TIMESTAMP(goodsOpenDt) < UNIX_TIMESTAMP())';

        $totalCountSQL =  'SELECT COUNT(goodsNo) AS totalCnt FROM ' . DB_GOODS . ' as g WHERE ' . implode(' AND ', $where);
        $count['total'] = $this->db->query_fetch($totalCountSQL,null,false)['totalCnt'];

        $where[] = 'daumFl = "y"';
        $totalCountSQL =  'SELECT COUNT(goodsNo) AS totalCnt FROM ' . DB_GOODS . ' as g WHERE ' . implode(' AND ', $where);

        $count['daum'] = $this->db->query_fetch($totalCountSQL,null,false)['totalCnt'];

        return $count;
    }

    /**
     * 다음 쇼핑하우 사용 상품 총 합계
     *
     * @param $cateCd
     * @return mixed
     * @throws Exception
     */
    public function getGoodsDaumStats($cateCd = null)
    {
        $where[] = 'goodsDisplayFl = "y"';
        $where[] = 'delFl = "n"';
        $where[] = 'applyFl = "y"';
        $where[] = 'NOT(stockFl = "y" AND totalStock = 0)';
        $where[] = 'NOT(soldOutFl = "y")';
        $where[] = '(UNIX_TIMESTAMP(goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(goodsOpenDt) = 0 OR UNIX_TIMESTAMP(goodsOpenDt) < UNIX_TIMESTAMP())';
        if (is_array($cateCd) && gd_isset($cateCd)) {
            $where[] = "(cateCd = '" . implode("' OR cateCd = '", $cateCd) . "')";
        } else {
            $where[] = "cateCd = ''";
        }

        $totalCountSQL = 'SELECT COUNT(goodsNo) as cnt , cateCd  FROM ' . DB_GOODS . ' WHERE ' . implode(' AND ', $where) . " group by cateCd";
        $tmpData = $this->db->query_fetch($totalCountSQL,null);
        $getData['total'] = array_combine(array_column($tmpData, 'cateCd'), array_column($tmpData, 'cnt'));

        $where[] = 'daumFl = "y"';
        $totalCountSQL = 'SELECT COUNT(goodsNo) as cnt , cateCd  FROM ' . DB_GOODS . ' WHERE ' . implode(' AND ', $where) . " group by cateCd";
        $tmpData = $this->db->query_fetch($totalCountSQL,null);

        $getData['daum'] = array_combine(array_column($tmpData, 'cateCd'), array_column($tmpData, 'cnt'));

        return $getData;
    }

    /**
     * 상품 재입고 알림 리스트
     *
     * @return array 상품 리스트 정보
     */
    public function getGoodsRestockList()
    {
        $getValue = Request::get()->toArray();

        $this->setSearchGoodsRestock($getValue);

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $sort = 'goodsNmSort desc';
        $sort .= ', ' . gd_isset($getValue['sort'], 'gr.goodsNm asc');
        $sort .= ', gr.sno desc';

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $join[] = ' LEFT JOIN ' . DB_GOODS . ' AS g ON gr.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS_OPTION . ' AS go ON (gr.goodsNo = go.goodsNo AND gr.optionSno = go.sno )';
        $join[] = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' AS gi ON gr.goodsNo = gi.goodsNo AND gi.imageKind = "List" ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' AS s ON g.scmNo = s.scmNo ';

        //상품 재입고 노출필드
        $strField[] = "IF(g.goodsNo > 0, 1, null) as goodsNmSort, gr.sno, gr.diffKey, gr.goodsNo, gr.goodsNm, gr.optionSno, gr.optionName, gr.optionValue, COUNT(gr.sno) AS requestCount, COUNT(IF(smsSendFl='y', gr.sno, null)) AS smsSendY, COUNT(IF(smsSendFl='n', gr.sno, null)) AS smsSendN";
        //상품 노출필드
        $strField[] = "g.goodsNo AS ori_goodsNo, g.totalStock, g.goodsNm AS ori_goodsNm, g.optionName AS ori_optionName, g.imagePath, g.imageStorage, g.delFl, g.optionFl";
        //상품 옵션 노출필드
        $strField[] = "go.optionValue1, go.optionValue2, go.optionValue3, go.optionValue4, go.optionValue5, go.stockCnt";
        //상품이미지 노출필드
        $strField[] = "gi.imageName";
        //공급사 노출필드
        $strField[] = "s.companyNm";

        $this->db->strField = implode(", ", $strField);
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strGroup = 'gr.goodsNo, gr.optionSno';
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_RESTOCK . ' AS gr ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        /* 검색 count 쿼리 */
        if(count($this->arrWhere) > 0){
            $totalCountWhere = '  WHERE '.implode(' AND ', $this->arrWhere) . ' GROUP BY gr.diffKey ORDER BY null';
        }
        else {
            $totalCountWhere = ' GROUP BY gr.diffKey ORDER BY null';
        }
        $totalCountSQL =  ' SELECT COUNT(gr.diffKey) AS totalCnt FROM ' . DB_GOODS_RESTOCK . ' AS gr '.implode('', $join).$totalCountWhere;
        $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind);
        $dataCount = count($dataCount);

        unset($this->arrBind);

        $page->recode['total'] = $dataCount; //검색 레코드 수

        $totalAmountSQL = "SELECT COUNT(diffKey) AS totalCnt FROM " . DB_GOODS_RESTOCK . " GROUP BY diffKey";
        $dataAmount = $this->db->query_fetch($totalAmountSQL);
        $dataAmount = count($dataAmount);
        $page->recode['amount'] = $dataAmount;
        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['selected'] = $this->selected;
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 상품 재입고 알림 신청 내역
     *
     * @return array 상품 재입고 알림 신청 내역 정보
     */
    public function getGoodsRestockView()
    {
        $getValue = Request::get()->toArray();

        $strField[] = "gr.diffKey, gr.goodsNm, gr.optionName, gr.optionValue, gr.optionSno";
        $strField[] = "g.goodsNo AS ori_goodsNo, g.goodsNm AS ori_goodsNm, g.optionName AS ori_optionName, g.totalStock, g.goodsDisplayFl, g.goodsDisplayMobileFl, g.goodsSellFl, g.goodsSellMobileFl, g.soldOutFl, g.stockFl, g.delFl";
        $strField[] = "go.optionValue1, go.optionValue2, go.optionValue3, go.optionValue4, go.optionValue5, go.stockCnt";

        $query = " WHERE gr.diffKey = '".$getValue['diffKey']."' LIMIT 1";
        $strSQL = "
            SELECT ".implode(", ", $strField)." FROM
                ".DB_GOODS_RESTOCK." AS gr
                    LEFT JOIN
                ".DB_GOODS." AS g ON gr.goodsNo = g.goodsNo
                    LEFT JOIN
                ".DB_GOODS_OPTION." AS go ON (gr.goodsNo = go.goodsNo AND gr.optionSno = go.sno )
        ".$query;
        $data = $this->db->query_fetch($strSQL);

        //옵션
        $data[0]['option'] = $this->getGoodsRestockOptionDisplay($data[0]);

        $returnData = $data[0];
        $returnData['goodsDisplayFl'] = ($returnData['goodsDisplayFl'] === 'y') ? '노출함' : '노출안함';
        $returnData['goodsDisplayMobileFl'] = ($returnData['goodsDisplayMobileFl'] === 'y') ? '노출함' : '노출안함';
        $returnData['goodsSellFl'] = ($returnData['goodsSellFl'] === 'y') ? '판매함' : '판매안함';
        $returnData['goodsSellMobileFl'] = ($returnData['goodsSellMobileFl'] === 'y') ? '판매함' : '판매안함';
        if($returnData['soldOutFl'] === 'y' || ($returnData['stockFl'] === 'y' && $returnData['totalStock'] <= 0)){
            $returnData['soldOutResult'] = "품절";
        }
        else {
            $returnData['soldOutResult'] = "정상";
        }

        if((int)$returnData['optionSno'] > 0){
            $returnData['totalStock'] = $returnData['stockCnt'];
        }

        return $returnData;
    }

    /**
     * 상품 재입고 알림 내역 리스트
     *
     * @return array 품 재입고 알림 내역 리스트
     */
    public function getGoodsRestockViewList($searchData = array())
    {
        if(count($searchData) > 0){
            $getValue = $searchData;
            $this->arrWhere = array();
        }
        else {
            $getValue = Request::get()->toArray();
        }

        $this->setSearchGoodsRestockViewList($getValue);

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $sort = gd_isset($getValue['sort'], 'gr.regdt DESC');

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        //상품 재입고 노출필드
        $strField[] = "gr.sno, gr.optionName, gr.optionValue, gr.smsSendFl, gr.cellPhone, gr.regdt, gr.name, gr.goodsNm, gr.goodsNo, gr.memNo";

        $this->db->strField = implode(", ", $strField);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        if(count($searchData) < 1) {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_RESTOCK . ' AS gr ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        /* 검색 count 쿼리 */
        unset($query['limit']);	// 상품 재입고 알림 신청 내역 페이지 오류로 검색count쿼리시 limit없도록 처리 20171024
        $totalCountSQL =  ' SELECT COUNT(gr.sno) AS totalCnt FROM ' . DB_GOODS_RESTOCK . ' AS gr ' . implode(' ', $query);
        $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind);

        unset($this->arrBind);

        if(trim($getValue['diffKey']) !== ''){
            $page->recode['total'] = $dataCount[0]['totalCnt']; //검색 레코드 수
            $totalAmountSQL =  "SELECT COUNT(sno) AS totalCnt FROM " . DB_GOODS_RESTOCK . " WHERE diffKey = '".$getValue['diffKey']."'";
            $totalAmount = $this->db->query_fetch($totalAmountSQL);
            $page->recode['amount'] = $totalAmount[0]['totalCnt'];
            $page->setPage();
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['selected'] = $this->selected;
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 관리자 상품 재입고 알림 신청 관리 리스트를 위한 검색 정보
     */
    public function setSearchGoodsRestock($getValue = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');
        $fieldTypeGoodsRestock = DBTableField::getFieldTypes('tableGoodsRestockBasic');

        // 통합 검색
        /* @formatter:off */
        $this->search['combineSearch'] = [
            'all' => __('=통합검색='),
            'goodsNm' => __('상품명'),
            'goodsNo' => __('상품코드'),
            'goodsCd' => __('자체상품코드'),
            'optionName' => __('옵션명'),
            'goodsSearchWord' => __('검색키워드'),
            'goodsModelNo' => __('모델번호')
        ];
        /* @formatter:on */

        //검색설정
        $this->search['sortList'] = array(
            'gr.goodsNm desc' => __('상품명 ↓'),
            'gr.goodsNm asc' => __('상품명 ↑'),
            'g.totalStock desc' => __('재고량 ↓'),
            'g.totalStock asc' => __('재고량 ↑'),
            'requestCount desc' => __('신청자 ↓'),
            'requestCount asc' => __('신청자 ↑'),
            'smsSendY desc' => __('발송건수 ↓'),
            'smsSendY asc' => __('발송건수 ↑'),
            'smsSendN desc' => __('미발송건수 ↓'),
            'smsSendN asc' => __('미발송건수 ↑'),
        );

        // --- 검색 설정
        $this->search['sort'] = gd_isset($getValue['sort'], 'gr.goodsNm asc');
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['stock'] = gd_isset($getValue['stock']);
        $this->search['scmFl'] = gd_isset($getValue['scmFl'], Session::get('manager.isProvider') ? 'y' : 'all');
        if($this->search['scmFl'] =='y' && !isset($getValue['scmNo'])  && !Session::get('manager.isProvider')  )  $this->search['scmFl'] = "all";
        $this->search['scmNo'] = gd_isset($getValue['scmNo'], (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm']);

        $this->selected['sort'][$this->search['sort']] = "selected='selected'";
        $this->checked['scmFl'][$this->search['scmFl']] = "checked='checked'";

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('goodsNm', 'goodsNo', 'goodsCd', 'optionName', 'goodsSearchWord', 'goodsModelNo');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    if($keyNm === 'goodsNm' || $keyNm === 'optionName'){
                        $arrWhereAll[] = '(gr.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                        $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock[$keyNm], $this->search['keyword']);
                    }
                    else {
                        $arrWhereAll[] = '(g.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                        $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$keyNm], $this->search['keyword']);
                    }
                }

                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            }
            else if($this->search['key'] == 'goodsNm' || $this->search['key'] == 'optionName'){
                $this->arrWhere[] = 'gr.'.$this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock[$this->search['key']], $this->search['keyword']);
            }
            else {
                $this->arrWhere[] = 'g.'.$this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$this->search['key']], $this->search['keyword']);
            }
        }

        // 재고검색
        if ($this->search['stock'][0] || $this->search['stock'][1]) {
            if($this->search['stock'][0]) {
                $this->arrWhere[] = 'g.totalStock >= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['totalStock'], $this->search['stock'][0]);
            }

            if($this->search['stock'][1]) {
                $this->arrWhere[] = 'g.totalStock <= ? ';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['totalStock'], $this->search['stock'][1]);
            }
        }
        //공급사 검색
        if ($this->search['scmFl'] != 'all') {
            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'g.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'g.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['scmNo'], $this->search['scmNo']);

                $this->search['scmNo'] = array($this->search['scmNo']);
                $this->search['scmNoNm'] = array($this->search['scmNoNm']);
            }
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 상품 재입고 알림 신청 내역 리스트를 위한 검색 정보
     */
    public function setSearchGoodsRestockViewList($getValue = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        $fieldTypeGoodsRestock = DBTableField::getFieldTypes('tableGoodsRestockBasic');

        //검색설정
        $this->search['sortList'] = array(
            'gr.regdt desc' => __('신청일 ↓'),
            'gr.regdt asc' => __('신청일 ↑'),
            'gr.name desc' => __('신청자 ↓'),
            'gr.name asc' => __('신청자 ↑'),
        );

        // --- 검색 설정
        $this->search['sort'] = gd_isset($getValue['sort'], 'gr.regdt desc');
        $this->search['sno'] = gd_isset($getValue['sno']);
        $this->search['stock'] = gd_isset($getValue['stock']);
        $this->search['diffKey'] = gd_isset($getValue['diffKey']);
        $this->search['smsSendFl'] = gd_isset($getValue['smsSendFl']);
        $this->search['memberFl'] = gd_isset($getValue['memberFl']);

        $this->selected['sort'][$this->search['sort']] = "selected='selected'";
        $this->checked['smsSendFl'][$this->search['smsSendFl']] = 'checked="checked"';
        $this->checked['memberFl'][$this->search['memberFl']] = 'checked="checked"';

        if ($this->search['sno']) {
            $this->arrWhere[] = 'gr.sno = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock['sno'], $this->search['sno']);
        }
        if ($this->search['diffKey']) {
            $this->arrWhere[] = 'gr.diffKey = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock['diffKey'], $this->search['diffKey']);
        }
        //SMS 발송 여부
        if ($this->search['smsSendFl']) {
            $this->arrWhere[] = 'gr.smsSendFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock['smsSendFl'], $this->search['smsSendFl']);
        }
        //회원 여부
        if($this->search['memberFl'] === 'y'){
            $this->arrWhere[] = 'gr.memNo > ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock['memNo'], 0);
        }
        else if($this->search['memberFl'] === 'n'){
            $this->arrWhere[] = 'gr.memNo < ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoodsRestock['memNo'], 1);
        }
        else {}

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 상품재입고 알림 - 옵션 정보 출력
     *
     * @param array $dataArray
     * @return string $option
     */
    public function getGoodsRestockOptionDisplay($dataArray)
    {
        $optionArray = $optionNameArray = $optionValueArray = array();
        $option = '';
        $optionNameArray = explode(STR_DIVISION, $dataArray['optionName']);
        $optionValueArray = explode(STR_DIVISION, $dataArray['optionValue']);
        $optionArray = array_map(function($nameVal, $valueVal){
            if(trim($nameVal) !== ''){
                return $nameVal . '/' . $valueVal;
            }
            else {
                return '';
            }
        }, $optionNameArray, $optionValueArray);
        $option = implode("<br />", $optionArray);

        return $option;
    }

    /**
     * 상품재입고 알림 - 기존 상품정보와 신청정보의 상풍명, 옵션명, 옵션값이 다를 경우 색으로 다름을 표시
     *
     * @param array $dataArray
     * @return array
     */
    public function getGoodsRestockStatus($dataArray)
    {
        if(!$dataArray['ori_goodsNo']){
            return array('deleteComplete', '#FFEAEA');
        }
        if($dataArray['delFl'] === 'y'){
            return array('delete', '#FFEAEA');
        }

        $optionOriginalValueArray = $this->setGoodsRestockOriginalOptionValue($dataArray);
        $optionOriginalValue = implode(STR_DIVISION, $optionOriginalValueArray);
        if(
            $dataArray['ori_goodsNm'] !== $dataArray['goodsNm'] ||
            $dataArray['ori_optionName'] !== $dataArray['optionName'] ||
            $optionOriginalValue !== $dataArray['optionValue']
        ){
            return array('change', '#FFFFE4');
        }

        return array();
    }

    /**
     * 상품재입고 알림 - 옵션값 재정렬 후 반환
     *
     * @param array $dataArray
     * @return array $optionValueArray
     */
    public function setGoodsRestockOriginalOptionValue($dataArray)
    {
        $optionValueArray = array_values(array_filter(array(
            $dataArray['optionValue1'],
            $dataArray['optionValue2'],
            $dataArray['optionValue3'],
            $dataArray['optionValue4'],
            $dataArray['optionValue5'],
        )));

        return $optionValueArray;
    }

    /**
     * 상품재입고 알림 - 신청내역 삭제
     *
     * @param array $postData
     * @return void
     */
    public function deleteGoodsRestock($postData)
    {
        if(count($postData['diffKey']) > 0){
            foreach($postData['diffKey'] as $key => $value){
                $this->db->set_delete_db(DB_GOODS_RESTOCK, "diffKey = '".$value."'");
            }
        }
    }

    /**
     * 상품재입고 알림 - SMS 발송여부에 따른 업데이트
     *
     * @param array $restockUpdateData
     * @return void
     */
    public function updateGoodsRestockSmsSend($restockUpdateData)
    {
        if(count($restockUpdateData) > 0) {
            $snoArray = array_column($restockUpdateData, 'sno');
            $snoArray = @array_chunk($snoArray, 100);
            foreach ($snoArray as $key => $valueArray) {
                $this->db->set_update_db(DB_GOODS_RESTOCK, "smsSendFl='y'", "sno IN ('" . implode("','", $valueArray) . "')");
            }
        }
    }

    public function setGoodsSale($param)
    {
        $goodsNo = explode(INT_DIVISION, $param['goodsNo']);
        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionModify') == 'c') {
            $param['applyFl'] = 'a';
            $param['applyDt'] = date('Y-m-d H:i:s');
        } else {
            $param['applyFl'] = 'y';
        }

        //상품수정일 변경유무 추가
        $modDtUse = $param['modDtUse'];

        unset($param['goodsNo'], $param['mode'], $param['goodsDisplay'], $param['goodsDisplayMobile'], $param['goodsSell'], $param['goodsSellMobile'], $param['modDtUse']);
        $updateData = $param;
        unset($updateData['applyFl'], $updateData['applyDt']);

        $arrBind = $this->db->get_binding(DBTableField::tableGoods(), $param, 'update', array_keys($param));
        foreach ($goodsNo as $value) {
            $goodsData = $this->getGoodsInfo($value, @implode(',', array_keys($param)));
            $this->setGoodsLog('goods', $value, $goodsData, $updateData);

            //상품수정일 변경유무 추가
            if ($modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }

            $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = \'' . $value . '\'', $arrBind['bind']);
            if($this->goodsDivisionFl) {
                //상품수정일 변경유무 추가
                if ($modDtUse == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS_SEARCH, $arrBind['param'], 'goodsNo = \'' . $value . '\'', $arrBind['bind']);
            }
        }

        return $param['applyFl'];
    }


    /**
     * 상품 검색 일괄 수정
     *
     * @param array $arrGoodsNo 상품 검색 테이블 일괄 수정
     * @return
     */
    public function updateGoodsSearch($arrGoodsNo, $modDtUse = null) {

        if(empty($arrGoodsNo)) return true;

        $updateField = [];
        foreach(DBTableField::tableGoodsSearch() as $k => $v) {
            if($v['batch'] =='y') $updateField[] = 'gs.'.$v['val'].'='.'g.'.$v['val'];
        }

        //상품수정일 변경유무 추가
        if ($modDtUse == 'n') {
            $this->db->setModDtUse(false);
        }

        $strSQL = 'UPDATE ' . DB_GOODS_SEARCH . ' gs INNER JOIN '.DB_GOODS.' g  ON g.goodsNo = gs.goodsNo SET '.implode(',',$updateField).' WHERE g.goodsNo IN (' . implode(',', $arrGoodsNo) . ')';
        $this->db->query($strSQL);

        return true;
    }

    /**
     * 검색조건에 따른 전체 검색수 리턴
     *
     * @param array $getValue 검색 조건
     * @return integer 상품카운트
     */
    public function getAdminListGoodsSearchCount($getValue)
    {
        gd_isset($this->goodsTable,DB_GOODS);

        $this->setSearchGoods($getValue);
        if($getValue['goodsNo'] && is_array($getValue['goodsNo'])) {
            $this->arrWhere[] = 'goodsNo IN (' . implode(',', $getValue['goodsNo']) . ')';
        }

        $strSQL = ' SELECT COUNT(g.goodsNo) AS cnt FROM ' . $this->goodsTable . ' as g  WHERE ' . implode(' AND ', $this->arrWhere);
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $totalCount = $res['cnt']; // 전체


        return $totalCount;
    }

    /**
     * doOpenApiGoodsNoInsert
     * openAPI 에서 사용하는 상품 번호를 Goods 테이블에 저장
     * doGoodsNoInsert() 와 다른 부분이 상품을 삭제된 상태로 delFL 을 y 로 저장
     *
     * @return string 저장된 상품 번호
     */
    protected function doOpenApiGoodsNoInsert()
    {
        $newGoodsNo = $this->getNewGoodsno();
        $arrData['goodsNo'] = $newGoodsNo;
        $arrData['delFl'] = 'y';
        $arrBind = $this->db->get_binding(DBTableField::tableGoods(), $arrData, 'insert');
        $this->db->set_insert_db(DB_GOODS, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
        if($this->goodsDivisionFl) {
            //검색테이블 추가
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsSearch(), $arrData, 'insert');
            $this->db->set_insert_db(DB_GOODS_SEARCH, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);
        }

        return $newGoodsNo;
    }

    /**
     * setUseOpenAPIGoodsNo
     * openAPI 에서 사용하는 신규 상품 고유번호 등록
     *
     * @return array $returnData
     */
    public function setUseOpenAPIGoodsNo()
    {
        $goodsNo = $this->doOpenApiGoodsNoInsert();
        $returnData['newGoodsNo'] = $goodsNo;

        return $returnData;
    }

    /**
     * 수기주문에서의 상품추가 팝업 상품검색시 조건 추가
     *
     * @param array $param 검색 조건
     * @return integer 상품카운트
     */
    /*
    public function setSearchGoodsOrderWrite($param=array())
    {
        // 회원, 비회원에 따라 성인인증 상품 노출범위 조정
        if(empty($param['memNo']) === false && (int)$param['memNo'] > 0){
            //회원의 경우 성인인증이 되지 않았거나 성인인증 기간이 만료된 경우 성인인증상품을 보여주지 않는다.
            if((gd_use_ipin() || gd_use_auth_cellphone()) && $param['memberData']['adultFl'] == 'n' || ($param['memberData']['adultFl'] == 'y' && (strtotime($param['memberData']['adultConfirmDt']) < strtotime("-1 year", time())))){
                $this->arrWhere[] = 'g.onlyAdultFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', 'n');
            }

            //결제수단 설정이 인 상품 이거나 상품결제수단이 개별설정이면서 무통장 사용을 사용중인 상품만보여준다.
            $this->arrWhere[] = "((g.payLimitFl = ? AND (g.payLimit LIKE '%gb%' or g.payLimit LIKE '%gm%' or g.payLimit LIKE '%gd%')) OR g.payLimitFl = ?)";
            $this->db->bind_param_push($this->arrBind, 's', 'y');
            $this->db->bind_param_push($this->arrBind, 's', 'n');

            //상품구매제한 체크
           // $this->arrWhere[] = "g.goodsPermission = ? AND SUBSTRING_INDEX(g.goodsPermission, '||', 1) = ?";
           // $this->db->bind_param_push($this->arrBind, 's', 'group');
           // $this->db->bind_param_push($this->arrBind, 's', $param['memberData']['groupSno']);
        }
        else {
            //비회원의 경우 성인인증 상품이 아닌것만 보여준다.
            if(gd_use_ipin() || gd_use_auth_cellphone()){
                $this->arrWhere[] = 'g.onlyAdultFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', 'n');
            }

            //결제수단 설정이 통합설정인 상품 이거나 상품결제수단이 개별설정이면서 무통장 사용을 사용중인 상품만보여준다.
            $this->arrWhere[] = "((g.payLimitFl = ? AND g.payLimit LIKE '%gb%') OR g.payLimitFl = ?)";
            $this->db->bind_param_push($this->arrBind, 's', 'y');
            $this->db->bind_param_push($this->arrBind, 's', 'n');

            //상품구매제한 체크
            $this->arrWhere[] = "g.goodsPermission = ?";
            $this->db->bind_param_push($this->arrBind, 's', 'all');
        }
    }
    */

    /**
     * saveInfoPopupDisplay
     * 상품리스트 메인페이지 상품진열 변경/추가(팝업)
     *
     */
    public function saveInfoPopupDisplay($arrData)
    {
        // 상품진열의 모든 데이터값 가져옴(메인)
        $arrBind = [];
        $strWhere = 'kind = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', 'main');
        $strSQL = "SELECT * FROM ".DB_DISPLAY_THEME. " WHERE ".$strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
        unset($arrBind);

        // 처리방법이 변경일때
        if($arrData['mode'] == 'popup_display_change') {
            $chkGoodsNo = explode(',', $arrData['goodsNo']);    // 상품리스트에서 선택된 상품값

            // 상품진열 모든 데이터 루프
            foreach ($getData as $key => $value) {

                // 상품진열이 선택 되었다면
                if (in_array($value['sno'], $arrData['sno'])) {

                    // 선택된 상품진열 루프
                    foreach ($arrData['sno'] as $snoK => $snoV) {
                        $arrBind = [];
                        $strWhere = "sno = ?";
                        $this->db->bind_param_push($arrBind['bind'], 'i', $snoV);
                        $strSQL = "SELECT sno, themeCd, sortAutoFl, goodsNo, fixGoodsNo, exceptGoodsNo FROM " . DB_DISPLAY_THEME . " WHERE " . $strWhere;
                        $data = $this->db->query_fetch($strSQL, $arrBind['bind'], false);   // 선택된 메인상품진열의 sno(일련번호), sortAutoFl(진열방법선택), exceptGoodsNo(예외상품)
                        unset($arrBind);

                        $exceptGoodsNo = explode(INT_DIVISION, $data['exceptGoodsNo']); // 선택된 상품진열의 예외상품값

                        $arrBind= [];
                        $strWhere = "themeCd = ?";
                        $this->db->bind_param_push($arrBind['bind'], 's', $data['themeCd']);
                        $strSQL = "SELECT themeCd, displayType, detailSet FROM ".DB_DISPLAY_THEME_CONFIG." WHERE ".$strWhere;
                        $themeCnfData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                        unset($arrBind);
                        $goodsNoArr = explode(INT_DIVISION, $data['goodsNo']);
                        // 탭진열형일때
                        if ($themeCnfData['displayType'] == '07' && ($data['themeCd'] == $themeCnfData['themeCd'])) {
                            foreach ($chkGoodsNo as $key => $val) {
                                if(!in_array($val, $goodsNoArr)) {
                                    $chkArrGoodsNo = explode(STR_DIVISION, $data['goodsNo']); // 탭구분기호로 나눔
                                    $detailSet = stripslashes($themeCnfData['detailSet']);  // 슬래시 제거
                                    $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                                    for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                                        $arrVal = $chkArrGoodsNo[$i];
                                        $tabThemeGoodsNo = explode(INT_DIVISION, $arrVal);

                                        if (!empty($tabThemeGoodsNo[0])) {
                                            $tabGoodsNo = array_unique(array_merge($chkGoodsNo, $tabThemeGoodsNo));
                                        } else {
                                            if (!empty($tabThemeGoodsNo)) {
                                                $tabGoodsNo = $chkGoodsNo;
                                            }
                                        }
                                        $impTabGoodsNo[] = implode(INT_DIVISION, $tabGoodsNo);
                                    }

                                    // 탭진열형 상태로 묶은 상품데이터
                                    $newTabGoodsNo = implode(STR_DIVISION, $impTabGoodsNo);
                                    $arrBind['param'][] = 'goodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $newTabGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $data['sno'] . '"', $arrBind['bind']);
                                    unset($impTabGoodsNo);
                                    unset($arrBind);
                                }
                            }
                        } else {
                            // 선택된 상품 루프
                            foreach ($chkGoodsNo as $key => $val) {
                                // 자동진열일때
                                if ($data['sortAutoFl'] == 'y') {
                                    if(!empty($data['exceptGoodsNo'])) {
                                        $newExceptGoodsNo = $data['exceptGoodsNo'] . INT_DIVISION . $val;
                                        $strExceptGoodsNo = explode(INT_DIVISION, $newExceptGoodsNo);
                                        $newArrExceptGoodsNo = array_unique(array_diff($strExceptGoodsNo, $chkGoodsNo));
                                        $newStrExceptGoodsNo = implode(INT_DIVISION, $newArrExceptGoodsNo);
                                        $arrBind['param'][] = 'exceptGoodsNo = ?';
                                        $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                                        $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $data['sno'] . '"', $arrBind['bind']);
                                        unset($arrBind);
                                        unset($exceptGoodsNo);
                                    }
                                    // 수동진열일때
                                } else {
                                    $fixGoodsNo = explode(INT_DIVISION, $data['fixGoodsNo']);
                                    $goodsNo =  explode(INT_DIVISION, $data['goodsNo']);
                                    if(!empty($data['goodsNo'])){
                                        if(!in_array($val, $goodsNo)) {
                                            if (!empty($data['fixGoodsNo'])) {
                                                $resetGoodsNo = array_values(array_diff($goodsNo, $fixGoodsNo));
                                                $newFixGoodsNo = implode(INT_DIVISION, $fixGoodsNo);
                                                $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                                $newGoodsNo = implode(INT_DIVISION, $resetGoodsNo);
                                                $goodsNo = $newFixGoodsNo . INT_DIVISION . $chkStrGoodsNo . INT_DIVISION . $newGoodsNo;
                                                $arrGoodsNo = explode(INT_DIVISION, $goodsNo);
                                                $newArrStrGoodsNo = array_unique($arrGoodsNo);
                                                $newStrGoodsNo = trim(implode(INT_DIVISION, $newArrStrGoodsNo));

                                                $arrBind['param'][] = 'goodsNo = ?';
                                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $data['sno'] . '"', $arrBind['bind']);
                                                unset($arrBind);
                                            } else {
                                                $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                                $goodsNo = $chkStrGoodsNo . INT_DIVISION . $data['goodsNo'];
                                                $arrGoodsNo = explode(INT_DIVISION, $goodsNo);
                                                $newArrStrGoodsNo = array_unique($arrGoodsNo);
                                                $newStrGoodsNo = trim(implode(INT_DIVISION, $newArrStrGoodsNo));
                                                $arrBind['param'][] = 'goodsNo = ?';
                                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $data['sno'] . '"', $arrBind['bind']);
                                                unset($arrBind);
                                            }
                                        }
                                    }else {
                                        $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                        $arrGoodsNo = explode(INT_DIVISION, $chkStrGoodsNo);
                                        $newArrStrGoodsNo = array_unique($arrGoodsNo);
                                        $newStrGoodsNo = implode(INT_DIVISION, $newArrStrGoodsNo);

                                        $arrBind['param'][] = 'goodsNo = ?';
                                        $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                        $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $data['sno'] . '"', $arrBind['bind']);
                                        unset($arrBind);
                                    }
                                }
                            }
                        }
                    }

                    // 상품진열이 미선택 되었다면
                } else {
                    // 선택된 상품 루프
                    foreach ($chkGoodsNo as $key => $val) {
                        $arrBind = [];
                        $strWhere = "themeCd = ?";
                        $this->db->bind_param_push($arrBind['bind'], 's', $value['themeCd']);
                        $strSQL = "SELECT themeCd, displayType, detailSet FROM ".DB_DISPLAY_THEME_CONFIG." WHERE ".$strWhere;
                        $themeCnfData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                        unset($arrBind);

                        // 탭진열형일때
                        if ($themeCnfData['displayType'] == '07' && ($themeCnfData['themeCd'] == $value['themeCd'])) {
                            $nChkArrGoodsNo = explode(STR_DIVISION, $value['goodsNo']); // 탭구분기호로 나눔
                            $detailSet = stripslashes($themeCnfData['detailSet']);  // 슬래시 제거
                            $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt
                            for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                                $arrChkGoodsNo = $nChkArrGoodsNo[$i];
                                $nChkTabThemeGoodsNo = explode(INT_DIVISION, $arrChkGoodsNo);
                                if (!empty($nChkTabThemeGoodsNo[0])) {
                                    $tabArrGoodsNo = array_unique(array_diff($nChkTabThemeGoodsNo, $chkGoodsNo));
                                }
                                $strTabGoodsNo[] = implode(INT_DIVISION, $tabArrGoodsNo);
                            }

                            // 탭진열형 상태로 묶은 상품데이터
                            $newTabGoodsNo = implode(STR_DIVISION, $strTabGoodsNo);
                            $arrBind['param'][] = 'goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $newTabGoodsNo);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $value['sno'] . '"', $arrBind['bind']);
                            unset($arrBind);
                            unset($strTabGoodsNo);
                        } else {
                            // 자동진열일때
                            if ($value['sortAutoFl'] == 'y') {
                                $resetExceptGoodsNo = implode(INT_DIVISION, $chkGoodsNo);

                                if (!empty($value['exceptGoodsNo'])) {
                                    $strExceptGoodsNo = $value['exceptGoodsNo'] . INT_DIVISION . $resetExceptGoodsNo;
                                    $overlapExceptGoodsNo = explode(INT_DIVISION, $strExceptGoodsNo);
                                    $strArrGoodsNo = array_unique($overlapExceptGoodsNo);
                                    $newStrExceptGoodsNo = implode(INT_DIVISION,$strArrGoodsNo);
                                    $arrBind['param'][] = 'exceptGoodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $value['sno'] . '"', $arrBind['bind']);
                                    unset($arrBind);
                                } else {
                                    $arrBind['param'][] = 'exceptGoodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $resetExceptGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $value['sno'] . '"', $arrBind['bind']);
                                    unset($arrBind);
                                }
                                // 수동진열일때
                            } else {
                                $fixGoodsNo = explode(INT_DIVISION, $value['fixGoodsNo']);
                                $goodsNo =  explode(INT_DIVISION, $value['goodsNo']);
                                if (!empty($value['fixGoodsNo'])) {
                                    $resetGoodsNo = array_unique(array_diff($goodsNo, $chkGoodsNo));
                                    $newStrGoodsNo = trim(implode(INT_DIVISION, $resetGoodsNo));
                                    $resetFixGoodsNo = array_unique(array_diff($fixGoodsNo, $chkGoodsNo)); // 고정값
                                    $newFixGoodsNo = trim(implode(INT_DIVISION, $resetFixGoodsNo));

                                    $arrBind['param'][] = 'goodsNo = ?';
                                    $arrBind['param'][] = 'fixGoodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                    $this->db->bind_param_push($arrBind['bind'], 's', $newFixGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $value['sno'] . '"', $arrBind['bind']);
                                    unset($arrBind);
                                } else {
                                    $goodsNo = explode(INT_DIVISION, $value['goodsNo']); // 선택되지 않은 메인상품진열의 상품코드값
                                    $newExceptGoodsNo = array_unique(array_diff($goodsNo, $chkGoodsNo));
                                    $strGoodsNo = trim(implode(INT_DIVISION, $newExceptGoodsNo));
                                    $arrBind['param'][] = 'goodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $strGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $value['sno'] . '"', $arrBind['bind']);
                                    unset($arrBind);
                                }
                            }
                        }
                    }
                }
            }
        }

        // 처리방법이 추가일때
        if($arrData['mode'] == 'popup_display_add') {
            $arrNewGoodsNo = [];
            foreach ($arrData['sno'] as $arrKey => $arrValue) {
                $chkGoodsNo = explode(',', $arrData['goodsNo']);    // 상품리스트에서 선택된 상품값

                $arrBind = [];
                $strWhere = "sno = ?";
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrValue);
                $strSQL = "SELECT goodsNo, themeCd, sortAutoFl, exceptGoodsNo FROM " . DB_DISPLAY_THEME . " WHERE " . $strWhere;
                $data = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                unset($arrBind);

                foreach ($chkGoodsNo as $k => $v) {

                    $arrBind= [];
                    $strWhere = "themeCd = ?";
                    $this->db->bind_param_push($arrBind['bind'], 's', $data['themeCd']);
                    $strSQL = "SELECT themeCd, displayType, detailSet FROM ".DB_DISPLAY_THEME_CONFIG." WHERE ".$strWhere;
                    $themeCnfData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                    unset($arrBind);

                    $goodsNoArr = explode(INT_DIVISION, $data['goodsNo']);
                    // 탭진열형일때
                    if ($themeCnfData['displayType'] == '07' && ($data['themeCd'] == $themeCnfData['themeCd'])) {
                        if(!in_array($v, $goodsNoArr)) {
                            $arrGoodsNo = explode(STR_DIVISION, $data['goodsNo']); // 탭구분기호로 나눔
                            $detailSet = stripslashes($themeCnfData['detailSet']);  // 슬래시 제거
                            $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                            for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                                $arrVal = $arrGoodsNo[$i];
                                $tabThemeGoodsNo = explode(INT_DIVISION, $arrVal);

                                if (!empty($tabThemeGoodsNo[0])) {
                                    $tabGoodsNo = array_unique(array_merge($chkGoodsNo, $tabThemeGoodsNo));
                                } else {
                                    $tabGoodsNo = $chkGoodsNo;
                                }
                                $impTabGoodsNo[] = implode(INT_DIVISION, $tabGoodsNo);
                            }

                            // 탭진열형 상태로 묶은 상품데이터
                            $newTabGoodsNo = implode(STR_DIVISION, $impTabGoodsNo);
                            $arrBind['param'][] = 'goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $newTabGoodsNo);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $arrValue . '"', $arrBind['bind']);
                            unset($impTabGoodsNo);
                            unset($arrBind);
                        }
                    }else {
                        // 자동진열일때
                        if ($data['sortAutoFl'] == 'y') {
                            if(!empty($data['exceptGoodsNo'])) {
                                $newExceptGoodsNo = $data['exceptGoodsNo'] . INT_DIVISION . $v;
                                $strExceptGoodsNo = explode(INT_DIVISION, $newExceptGoodsNo);
                                $newArrExceptGoodsNo = array_unique(array_diff($strExceptGoodsNo, $chkGoodsNo));
                                $newStrExceptGoodsNo = implode(INT_DIVISION, $newArrExceptGoodsNo);
                                $arrBind['param'][] = 'exceptGoodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $arrValue . '"', $arrBind['bind']);
                                unset($arrBind);
                            }else{
                                $newArrExceptGoodsNo = array_unique($chkGoodsNo);
                                $newStrExceptGoodsNo = implode(INT_DIVISION, $newArrExceptGoodsNo);
                                $arrBind['param'][] = 'exceptGoodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $arrValue . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                            // 수동진열일때
                        } else {
                            if(!empty($data['goodsNo'])){
                                if(!in_array($v, $goodsNoArr)) {
                                    $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                    $goodsNo = $chkStrGoodsNo . INT_DIVISION . $data['goodsNo'];
                                    $arrGoodsNo = explode(INT_DIVISION, $goodsNo);
                                    $newArrStrGoodsNo = array_unique($arrGoodsNo);
                                    $newStrGoodsNo = implode(INT_DIVISION, $newArrStrGoodsNo);

                                    $arrBind['param'][] = 'goodsNo = ?';
                                    $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                    $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $arrValue . '"', $arrBind['bind']);
                                    unset($arrBind);
                                }
                            }else {
                                $chkStrGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                                $arrGoodsNo = explode(INT_DIVISION, $chkStrGoodsNo);
                                $newArrStrGoodsNo = array_unique($arrGoodsNo);
                                $newStrGoodsNo = implode(INT_DIVISION, $newArrStrGoodsNo);

                                $arrBind['param'][] = 'goodsNo = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $arrValue . '"', $arrBind['bind']);
                                unset($arrBind);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * modifyGoodsNoPopupDisplay
     * 상품리스트 메인페이지 상품진열 삭제(팝업)
     *
     */
    public function modifyGoodsNoPopupDisplay($sno, $arrData)
    {
        $arrBind = [];
        // 부모창에서 선택상품값
        $chkGoodsNo = explode(',', $arrData['goodsNo']);
        $strWhere = "sno = ?";
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        $strSQL = " SELECT goodsNo, themeCd, sortAutoFl, exceptGoodsNo FROM ".DB_DISPLAY_THEME. " WHERE " .$strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
        unset($arrBind);

        foreach ($chkGoodsNo as $key => $val) {

            $arrBind= [];
            $strWhere = "themeCd = ?";
            $this->db->bind_param_push($arrBind['bind'], 's', $getData['themeCd']);
            $strSQL = "SELECT themeCd, displayType, detailSet FROM ".DB_DISPLAY_THEME_CONFIG." WHERE ".$strWhere;
            $themeCnfData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
            unset($arrBind);

            // 탭진열형일때
            if($themeCnfData['displayType'] == '07' && ($getData['themeCd'] == $themeCnfData['themeCd'])) {
                $arrGoodsNo = explode(STR_DIVISION, $getData['goodsNo']); // 탭구분기호로 나눔
                $detailSet = stripslashes($themeCnfData['detailSet']);  // 슬래시 제거
                $tabSetCnt = unserialize($detailSet);   // 변환키신 후 탭개수 cnt

                for ($i = 0; $i < $tabSetCnt[0]; $i++) {
                    $arrVal = $arrGoodsNo[$i];
                    $tabThemeGoodsNo = explode(INT_DIVISION, $arrVal);

                    // 상품코드에 추가되어있으면
                    if (!empty($tabThemeGoodsNo[0])) {
                        $tabGoodsNo = array_unique(array_diff($tabThemeGoodsNo, $chkGoodsNo));
                    } else {
                        if(!empty($getData['exceptGoodsNo'])) {
                            $tabGoodsNo = $chkGoodsNo;
                        }
                    }
                    $impTabGoodsNo[] = implode(INT_DIVISION, $tabGoodsNo);
                }
                // 탭진열형 상태로 묶은 상품데이터
                $newTabGoodsNo = implode(STR_DIVISION, $impTabGoodsNo);
                $arrBind['param'][] = 'goodsNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $newTabGoodsNo);
                $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = "' . $sno . '"', $arrBind['bind']);
                unset($impTabGoodsNo);
                unset($arrBind);
            }else {
                // 진열방식이 자동진열일때
                if ($getData['sortAutoFl'] == 'y') {
                    // 선택된 상품이 다중선택일때
                    if(count($chkGoodsNo) >= 2){
                        $exceptGoodsNo = implode(INT_DIVISION, $chkGoodsNo);
                        $newExceptGoodsNo = $getData['exceptGoodsNo'].INT_DIVISION.$exceptGoodsNo;
                        $arrExceptGoodsNo = explode(INT_DIVISION, $newExceptGoodsNo);
                        $overlapExceptGoodsNo = array_unique($arrExceptGoodsNo);
                        $strExceptGoodsNo = trim(implode(INT_DIVISION, $overlapExceptGoodsNo));

                        $arrBind['param'][] = 'exceptGoodsNo = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $strExceptGoodsNo);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
                        $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }else{
                        if (!empty($getData['exceptGoodsNo'])) {
                            $strExceptGoodsNo = $getData['exceptGoodsNo'] . INT_DIVISION . $val;
                            $arrExceptGoodsNo = explode(INT_DIVISION, $strExceptGoodsNo);
                            $overlapExceptGoodsNo = array_unique($arrExceptGoodsNo);
                            $newStrExceptGoodsNo = trim(implode(INT_DIVISION, $overlapExceptGoodsNo));

                            $arrBind['param'][] = 'exceptGoodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $newStrExceptGoodsNo);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                            unset($arrBind);

                        } else {
                            $strExceptGoodsNo = $val;
                            $arrBind['param'][] = 'exceptGoodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $strExceptGoodsNo);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                            unset($arrBind);
                        }
                    }
                } else {
                    // 선택된 상품이 다중선택일때
                    if (count($chkGoodsNo) >= 2) {
                        $goodsNo = implode(INT_DIVISION, $chkGoodsNo);
                        $newGoodsNo = $goodsNo . INT_DIVISION . $getData['goodsNo'];
                        $arrGoodsNo = explode(INT_DIVISION, $newGoodsNo);
                        $overlapExceptGoodsNo = array_unique(array_diff($arrGoodsNo, $chkGoodsNo));
                        $strExceptGoodsNo = trim(implode(INT_DIVISION, $overlapExceptGoodsNo));

                        $arrBind['param'][] = 'goodsNo = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $strExceptGoodsNo);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
                        $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    } else {
                        if (!empty($getData['goodsNo'])) {
                            $strGoodsNo = $val . INT_DIVISION . $getData['goodsNo'];
                            $newGoodsNo = explode(INT_DIVISION, $strGoodsNo);
                            $overlapGoodsNo = array_unique(array_diff($newGoodsNo, $chkGoodsNo));
                            $newStrGoodsNo = trim(implode(INT_DIVISION, $overlapGoodsNo));
                            $arrBind['param'][] = 'goodsNo = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $newStrGoodsNo);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
                            $this->db->set_update_db(DB_DISPLAY_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                            unset($arrBind);
                        }
                    }
                }
            }
        }
    }

    /**
     * saveInfoPopupCategory
     * 상품리스트 분류관리 변경/추가(팝업)
     *
     */
    public function saveInfoPopupCategory($arrData)
    {
        $arrData['goodsNo'] = explode(',', $arrData['goodsNo']);

        if($arrData['mode'] == 'popup_category_change') {
            foreach ($arrData['goodsNo'] as $key => $val) {
                $strSQL = "SELECT glc.cateCd, IF(MAX(glc.goodsSort) > 0, (MAX(glc.goodsSort) + 1), 1) AS sort,MIN(glc.goodsSort) - 1 as reSort, MAX(glc.fixSort) + 1 as fixSort, glc.cateCd,cg.sortAutoFl,cg.sortType FROM ".DB_GOODS_LINK_CATEGORY." AS glc INNER JOIN ".DB_CATEGORY_GOODS." AS cg ON cg.cateCd = glc.cateCd WHERE glc.cateCd IN  ('" . implode('\',\'', $arrData['link']['cateCd']) . "') GROUP BY glc.cateCd ";
                $result = $this->db->query($strSQL);

                // 카테고리 변경시 기존 카테고리 데이터 삭제
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', $arrData['goodsNo'][$key]);
                $this->db->set_delete_db(DB_GOODS_LINK_CATEGORY, 'goodsNo = ?', $arrBind);

                foreach($arrData['link']['cateCd'] as $k => $v) {
                    while ($sortData = $this->db->fetch($result)) {
                        // 상단고정
                        if($arrData['goodsSortTop'] == 'y') {
                            $arrData['link']['fixSort'][] = $sortData['fixSort'];
                        }else{
                            $arrData['link']['fixSort'][] = 0;
                        }
                        // 자동진열일때 상품정렬번호 초기화
                        if($sortData['sortAutoFl'] == 'y'){
                            $arrData['link']['goodsSort'][] = 0;
                        }else{
                            $arrData['link']['goodsSort'][] = $sortData['sort'];
                        }
                    }

                    // 기존 카테고리 데이터 지운후 선택된 카테고리 데이터로 변경
                    $arrBind = [];
                    $arrBind['param'][] = 'goodsNo, cateCd, cateLinkFl, goodsSort, fixSort';
                    $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo'][$key]);
                    $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateCd'][$k]);
                    $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateLinkFl'][$k]);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['link']['goodsSort'][$k]);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['link']['fixSort'][$k]);
                    $this->db->set_insert_db(DB_GOODS_LINK_CATEGORY, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                }
                // 대표 카테고리 수정
                $arrBind['param'][] = 'cateCd =?';
                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['cateCd']);
                //상품수정일 변경유무 추가
                if ($arrData['modDtUse'] == 'n') {
                    $this->db->setModDtUse(false);
                }
                $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = "' . $arrData['goodsNo'][$key] . '"', $arrBind['bind']);
                unset($arrBind);
            }
        }else{
            foreach ($arrData['goodsNo'] as $key => $val) {
                foreach($arrData['link']['cateCd'] as $k => $v) {
                    unset($arrBind);
                    // 중복체크 하기위한 select
                    $strWhere = "goodsNo = ? and cateCd = ? ";
                    $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo'][$key]);
                    $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateCd'][$k]);
                    $strSQL = 'SELECT count(sno) as cnt FROM ' . DB_GOODS_LINK_CATEGORY . ' WHERE ' . $strWhere;
                    $cnt = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

                    // 상단 고정 처리값 select
                    $strSQL = "SELECT glc.cateCd, IF(MAX(glc.goodsSort) > 0, (MAX(glc.goodsSort) + 1), 1) AS sort,MIN(glc.goodsSort) - 1 as reSort, MAX(glc.fixSort) + 1 as fixSort, glc.cateCd,cg.sortAutoFl,cg.sortType FROM ".DB_GOODS_LINK_CATEGORY." AS glc INNER JOIN ".DB_CATEGORY_GOODS." AS cg ON cg.cateCd = glc.cateCd WHERE glc.cateCd IN  ('" . implode('\',\'', $arrData['link']['cateCd']) . "') GROUP BY glc.cateCd ORDER BY FIELD (glc.cateCd, '" . implode('\',\'', $arrData['link']['cateCd']) . "')";
                    $result = $this->db->query($strSQL);

                    // 처리방법 추가시 중복 체크
                    if($cnt['cnt'] == 0){
                        unset($arrBind);
                        $strWhere = "goodsNo = ?";
                        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo'][$key]);
                        $strSQL = ' SELECT goodsSort, fixSort FROM ' .DB_GOODS_LINK_CATEGORY. ' WHERE '.$strWhere;
                        $data = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                        unset($arrBind);

                        while ($sortData = $this->db->fetch($result)) {
                            // 상단고정
                            if ($arrData['goodsSortTop'] == 'y' && !empty($arrData['cateGoods'])) {
                                $arrData['link']['fixSort'][] = $sortData['fixSort'];
                            }

                            // 자동진열일때 상품정렬번호 초기화
                            if ($sortData['sortAutoFl'] == 'y'){
                                $arrData['link']['goodsSort'][] = 0;
                            }
                        }

                        $arrBind = [];
                        $arrBind['param'][] = 'goodsNo, cateCd, cateLinkFl, goodsSort, fixSort';
                        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo'][$key]);
                        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateCd'][$k]);
                        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateLinkFl'][$k]);

                        if($arrData['goodsSortTop'] == 'y' || $sortData['sortAutoFl'] == 'y') {
                            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['link']['goodsSort'][$k]);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['link']['fixSort'][$k]);
                        }else{
                            $this->db->bind_param_push($arrBind['bind'], 'i', $data[$k]['goodsSort']);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $data[$k]['fixSort']);
                        }
                        $this->db->set_insert_db(DB_GOODS_LINK_CATEGORY, $arrBind['param'], $arrBind['bind'], 'y');
                        unset($arrBind);
                    }else{
                        // 이미 카테고리가 있지만 상단고정으로 변경할때
                        unset($arrBind);
                        $strWhere = "goodsNo = ? and cateCd = ?";
                        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo'][$key]);
                        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateCd'][$k]);
                        $strSQL = ' SELECT fixSort FROM ' .DB_GOODS_LINK_CATEGORY. ' WHERE '.$strWhere;
                        $data = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

                        unset($arrBind);
                        // 상품에 연결된 상태 여부 y로 업데이트
                        $arrBind = [];
                        $arrBind['param'][] = 'cateLinkFl = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', 'y');

                        // 상단고정진열이고 상단고정이 적용되지 않은 카테고리일때
                        if($arrData['goodsSortTop'] == 'y' && $data['fixSort'] == 0){;
                            while ($sortData = $this->db->fetch($result)) {
                                $arrData['link']['fixSort'][] = $sortData['fixSort'];
                            }

                            $arrBind['param'][] = 'fixSort = ?';
                            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['link']['fixSort'][$k]);
                        }
                        $this->db->set_update_db(DB_GOODS_LINK_CATEGORY, $arrBind['param'], 'goodsNo = "' . $arrData['goodsNo'][$key] . '" AND cateCd = "' . $arrData['link']['cateCd'][$k] . '" ', $arrBind['bind']);
                        unset($arrBind);
                    }
                }
            }
        }
    }

    /**
     * modifyPopupCategory
     * 상품리스트 분류관리 삭제(팝업)
     *
     */
    public function modifyPopupCategory($arrData)
    {
        $arrData['goodsNo'] = explode(',', $arrData['goodsNo']);

        foreach ($arrData['goodsNo'] as $key => $val) {
            foreach ($arrData['link']['cateCd'] as $k => $v) {
                $strWhere = 'goodsNo = ? AND  cateCd = ?';
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['goodsNo'][$key]);
                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['link']['cateCd'][$k]);;
                $this->db->set_delete_db(DB_GOODS_LINK_CATEGORY, $strWhere, $arrBind['bind']);
                unset($arrBind);
            }

        }

    }

    /**
     * saveGoodsModdt
     * 상품리스트 상품 수정일변경
     *
     */
    public function saveGoodsModdt($arrData)
    {
        $arrgoodsNo = explode(',', $arrData['goodsNo']);
        foreach ($arrgoodsNo as $key => $val) {
            $arrBind['param'][] = 'modDt = now()';
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrgoodsNo[$key]);
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            $this->db->set_update_db(DB_GOODS_SEARCH, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            unset($arrBind);
        }
    }

    /**
     * 그리드 설정으로 필드 추가 및 상품리스트 진열 데이터 반환 - 그리드3
     *
     * @param string $goodsAdminGridMode
     * @param array $addFieldGridData 추가 필드 항목
     * @param array $goodsGridConfigDisplayList 추가 디스플레이 항목
     *
     * @return string $setData;
     */
    public function setGoodsGridDataAddField($goodsAdminGridMode, $addFieldGridData, $goodsGridConfigDisplayList)
    {
        if($addFieldGridData) {
            $addFieldGridImplode = "," . implode(',', $addFieldGridData);
        }
        $addFieldGrid = ",g.commission,g.cateCd,g.orderGoodsCnt,g.hitCnt" . $addFieldGridImplode; // 기존 조회 필드 외 그리드 추가필드 일렬화
        if($goodsAdminGridMode == 'goods_list_delete') { // 삭제상품일 경우 추가
            $addFieldGrid = $addFieldGrid . ",g.delDt";
        }
        // 노출항목 추가 값 색상 여부 확인 후 필드 추가
        if(array_key_exists('color', $goodsGridConfigDisplayList) == true) {
            if($goodsGridConfigDisplayList['color'] == 'y') {
                $addFieldGrid = $addFieldGrid . ",g.goodsColor"; // 색상 필드 추가
            }
        }
        if($goodsGridConfigDisplayList['best'] == 'y') {
            $goodsDisplayAddDbData['best'] = $this->getPopulateThemeData(); // 인기상품 진열 DB 데이터 가져오기(전체)
        }
        if($goodsGridConfigDisplayList['main'] == 'y') {
            $goodsDisplayAddDbData['main'] = $this->getGoodsListDisplayThemeMultiData(null); // 메인 진열 DB 데이터 가져오기(전체)
        }
        // 그리드 선택항목에 결제수단 필드가 있을 경우 config 로드
        if(array_key_exists('payLimit', $addFieldGridData) == true ) {
            // 결제수단 config 로드
            $this->paySettleKind = gd_policy('order.settleKind');
        }
        // 절사 config 로드 - 가격 변환 노출용
        $configTrunc = gd_policy('basic.trunc');
        $this->goodsUnit['trunc'] = $configTrunc['goods'];

        $returnData['addFieldGrid'] = $addFieldGrid; // 추가필드 리턴
        $returnData['goodsDisplayAddDbData'] = $goodsDisplayAddDbData; // 추가진열 DB 데이터 리턴
        unset($goodsDisplayAddDbData);
        return $returnData;
    }

    /**
     * 그리드 항목 추가에 따른 필드 데이터 가공 처리(관리자 리스트 디자인 출력 시 사용) - 그리드5
     *
     * @param array $val 상품 array
     * @return array goods 추가 데이터
     */
    public function setGoodsGridDataAddFieldConvert($val, $goodsGridConfigDisplayList, $goodsDisplayAddDbData)
    {
        if($val) {
            // 수수료율 소수점 처리
            $val['commission'] = gd_number_figure($val['commission'], '0.1', 'floor') . "%";
            // 해외상품명
            if($val['goodsNmUs']) {
                $goodsNmUs = $this->getGoodsNmGlobal($val['goodsNo'], 2);
                $val['goodsNmUs'] = ($goodsNmUs) ? $goodsNmUs : $val['goodsNmUs'];
            }
            if($val['goodsNmCn']) {
                $goodsNmCn = $this->getGoodsNmGlobal($val['goodsNo'], 3);
                $val['goodsNmCn'] = ($goodsNmCn) ? $goodsNmCn : $val['goodsNmCn'];
            }
            if($val['goodsNmJs']) {
                $goodsNmJs = $this->getGoodsNmGlobal($val['goodsNo'], 4);
                $val['goodsNmJs'] = ($goodsNmJs) ? $goodsNmJs : $val['goodsNmJs'];
            }
            // 과세 면세 처리
            if($val['taxPercent'] > 0) {
                $val['taxPercent'] = gd_number_figure($val['taxPercent'], '0.1', 'floor') . "%";
            }
            // 옵션 정보
            if ($val['optionFl'] == 'y' && empty($val['optionName']) == false) {
                $val['optionName'] = explode(STR_DIVISION, $val['optionName']); // 옵션 이름
                $getOptionData = $this->getGoodsOption($val['goodsNo'])['optVal']; // 옵션 값
                // 옵션 값 , 로 합치기
                foreach ($getOptionData as $sKey => $sValue) {
                    if($sValue) {
                        $optionValueReturn[] = implode(',', $sValue);
                    }
                }
                $val['optionInfo'] = ArrayUtils::removeEmpty($optionValueReturn); // 빈 배열 제거
                unset($getOptionData, $optionValueReturn);
            }
            // 텍스트 옵션 정보
            if ($val['optionTextFl'] == 'y') {
                $optionTextInfoArray = $this->getGoodsOptionText($val['goodsNo']); // 옵션 값
                foreach ($optionTextInfoArray as $optionTextKey => $optionTextVal) {
                    $val['optionTextInfo'][] = $optionTextVal['optionName'] . "/" . gd_money_format($optionTextVal['addPrice']) . gd_currency_string();
                }
                unset($optionTextInfoArray);
            }
            // 리스트 공급가
            if ($val['commission'] > 0) {
                $val['supplyPrice'] = $val['goodsPrice'] - ($val['goodsPrice'] * ($val['commission'] / 100));
            } else {
                $val['supplyPrice'] = '0';
            }
            // 구매율
            if ($val['orderRate'] == 0 || $val['orderRate'] > 0) {
                $val['orderRate'] = round((($val['orderGoodsCnt'] / $val['hitCnt']) * 100), 2);
                $val['orderRate'] = gd_number_figure($val['orderRate'],  '0.1', 'floor') . "%";
            }
            // 결제수단
            if($val['payLimitFl'] =='y') {
                $val['payLimitIcon'] = $this->getGoodsListPayLimitIcon($val['payLimitFl'], $val['payLimit']); // 결제수단 아이콘 생성
            } else {
                $val['payLimitIcon'] = "통합설정";
            }
            // 후기 수 (플러스리뷰)
            if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) {
                $val['reviewCnt'] = $val['reviewCnt'] + $val['plusReviewCnt'];
            }
            // 대표색상
            if($val['goodsColor']) {
                $val['goodsColor'] = explode(STR_DIVISION, $val['goodsColor']);
            }
            // 분류 항목 추가
            if(count($goodsGridConfigDisplayList) > 0) {
                $gridAddDisplayArray = ['best', 'main', 'cate']; // 그리드 추가진열 레이어 노출 항목
                $addDisplayByPass = false; // 그리드 추가 진열 레이어 노출 여부
                foreach($gridAddDisplayArray as $displayPassVal) {
                    if(array_key_exists($displayPassVal, $goodsGridConfigDisplayList) === true) {
                        $addDisplayByPass = true; // 그리드 추가 진열 레이어 노출 사용
                        break;
                    }
                }
                if($addDisplayByPass == true) {
                    $cate = \App::load('\\Component\\Category\\Category');
                    $tmpCategoryList = $cate->getCateCd($val['goodsNo'], 'category', 'y');
                    $val['goodsCategoryLink'] = $tmpCategoryList; // 카테고리 링크 코드
                    $val['goodsGridDisplayData'] = $categoryList = [];
                    if ($goodsGridConfigDisplayList['main'] == 'y') {
                        $val['goodsGridDisplayData']['main'] = $this->goodsListDisplayEachPrint($goodsDisplayAddDbData, $val)['main'];
                    }
                    if ($goodsGridConfigDisplayList['best'] == 'y') {
                        $val['goodsGridDisplayData']['best'] = $this->goodsListDisplayEachPrint($goodsDisplayAddDbData, $val)['best'];
                    }
                    if ($goodsGridConfigDisplayList['cate'] == 'y') {
                        //카테고리 정보
                        if ($tmpCategoryList) {
                            foreach ($tmpCategoryList as $k => $v) {
                                $categoryList[$v] = gd_htmlspecialchars_decode($this->getGoodsListCategoryTree($v));
                            }
                        }
                        $val['goodsGridDisplayData']['cate'] = $categoryList;
                    }
                    $val['goodsGridDisplayData'] = ArrayUtils::removeEmpty($val['goodsGridDisplayData']); // 빈 배열 제거
                    unset($gridAddDisplayArray, $tmpCategoryList, $categoryList);
                }
            }
            return $val;
        }
    }

    /**
     * 그리드항목 - 인기상품진열 추출
     *
     * @return array data
     */
    public function getPopulateThemeData()
    {
        // 조회할 필드
        $selectField = [
            'sno',
            'populateName',
            '`range`',
            'goodsNo',
            'categoryCd',
            'brandCd',
            'except_goodsNo',
            'except_categoryCd',
            'except_brandCd',
        ];
        $strSQL = "SELECT " . implode(',', $selectField) . " FROM  " . DB_POPULATE_THEME;
        $data = $this->db->query_fetch($strSQL);
        return $data;
    }

    /**
     * 그리드항목 - 상품리스트 추가진열 설정에 따라 관리자페이지 보이기
     * 인기상품,메인분류,카테고리
     * 상품번호 진열,제외의 경우 정규식 / 그외 진열,제외 항목 in_array 비교
     *
     * @param int $goodsDisplayAddData 추가진열 데이터 값
     * @param array $val 상품정보
     *
     * @return trues
     */
    public function goodsListDisplayEachPrint($goodsDisplayAddData, $val)
    {
        if($goodsDisplayAddData) {
            $displayReDefineArr = [];
            foreach($goodsDisplayAddData as $dataKey => $dataVal) { // 인자 배열 main, best
                if ($dataKey == 'main') { // 메인상품진열
                    $confirmField = ['exceptCateCd', 'exceptBrandCd', 'exceptScmNo', 'exceptGoodsNo']; // 진열제외항목
                    $confirmCompareGoodsField = [ 'cateCd', 'brandCd', 'scmNo', 'goodsNo']; // 상품비교 필드
                    foreach ($dataVal as $displayKey => $displayVal) { // 진열 별 foreach
                        if ($displayVal['sort']) { // 자동진열
                            foreach ($confirmField as $confirmMainKey => $confirmMainVal) {
                                // 예외 데이터
                                $mainValueExplode = explode(INT_DIVISION, $displayVal[$confirmMainVal]);
                                if ($confirmMainKey == 0) { // 카테고리일 경우
                                    foreach ($val['goodsCategoryLink'] as $linkKey => $linkVal) {
                                        if (in_array($linkVal, $mainValueExplode) == false) {
                                            $displayReDefineArr['main'][$displayKey] = $displayVal;
                                        }
                                    }
                                } else { // 카테고리 외 필드
                                    if($val[$confirmCompareGoodsField[$confirmMainKey]]) {
                                        if (in_array($val[$confirmCompareGoodsField[$confirmMainKey]], $mainValueExplode) == false) {
                                            $displayReDefineArr['main'][$displayKey] = $displayVal;
                                        } else {
                                            if($displayReDefineArr['main'][$displayKey]) {
                                                unset($displayReDefineArr['main'][$displayKey]);
                                            }
                                        }
                                    }
                                }
                            }
                        } else { // 수동진열일 경우 상품만 체크
                            if ($displayVal['goodsNo']) { // 수동 상품설정
                                $patternGoodsNo = '/' . $val['goodsNo'] . '/';
                                if (preg_match($patternGoodsNo, $displayVal['goodsNo']) == true) {
                                    $displayReDefineArr['main'][$displayKey] = $displayVal;
                                }
                            }
                        }
                    }
                } else if ($dataKey == 'best') { // 인기상품진열
                    // 상품 비교 필드 - goodsNo가 우선순위 높아 맨 마지막 배열 위치
                    $confirmCompareGoodsField = ['goodsCategoryLink', 'brandCd', 'goodsNo'];
                    $confirmField = ['categoryCd', 'brandCd', 'goodsNo']; // 진열 필드
                    $confirmExceptField = ['except_categoryCd', 'except_brandCd', 'except_goodsNo']; // 제외 필드
                    $confirmAll = array_merge($confirmField, $confirmExceptField);
                    foreach ($dataVal as $displayKey => $displayVal) { // 진열 별 foreach
                        foreach ($displayVal as $bestKey => $bestVal) {
                            $bestByPass = true;
                            if(in_array($bestKey, $confirmAll) == $bestByPass) {
                                foreach ($confirmField as $confirmBestKey => $confirmBestVal) {
                                    // 상품번호 정규식 데이터
                                    $patternLink = '/' . $val[$confirmCompareGoodsField[$confirmBestKey]] . '/';
                                    if ($confirmBestKey == 0) { // 카테고리 필드
                                        $bestValueExplode = explode(INT_DIVISION, $displayVal[$confirmBestVal]);
                                        $bestValueExceptExplode = explode(INT_DIVISION, $displayVal[$confirmExceptField[$confirmBestKey]]);
                                        foreach ($val['goodsCategoryLink'] as $linkKey => $linkVal) {
                                            if($displayVal['range'] == 'all') {
                                                $displayReDefineArr['best'][$displayKey] = $displayVal;
                                            } else {
                                                if (in_array($linkVal, $bestValueExplode) == $bestByPass) { // 추가
                                                    // 카테고리 체크 후 상품번호 확인
                                                    if(preg_match($patternLink, $displayVal['goodsNo']) == $bestByPass) { // 진열상품 체크
                                                        $displayReDefineArr['best'][$displayKey] = $displayVal;
                                                    }
                                                }
                                            }
                                            if (in_array($linkVal, $bestValueExceptExplode) == $bestByPass) { // 카테고리제외
                                                if ($displayReDefineArr['best'][$displayKey]) {
                                                    unset($displayReDefineArr['best'][$displayKey]);
                                                }
                                                if(preg_match($patternLink, $displayVal['except_goodsNo']) == $bestByPass) { // 제외상품 체크
                                                    unset($displayReDefineArr['best'][$displayKey]);
                                                }
                                            }
                                        }
                                    } else { // 카테고리 외 필드 브랜드, 상품
                                        if($displayVal['range'] == 'all') {
                                            $displayReDefineArr['best'][$displayKey] = $displayVal;
                                        } else {
                                            if(preg_match($patternLink, $displayVal[$confirmBestVal]) == $bestByPass) { // 진열데이터 체크
                                                if(preg_match($patternLink, $displayVal['goodsNo']) == $bestByPass) { // 진열상품 체크
                                                    $displayReDefineArr['best'][$displayKey] = $displayVal;
                                                }
                                            }
                                        }
                                        if (preg_match($patternLink, $displayVal[$confirmExceptField[$confirmBestKey]]) == $bestByPass) { // 제외데이터 체크
                                            if ($displayReDefineArr['best'][$displayKey]) {
                                                if(preg_match($patternLink, $displayVal['except_goodsNo']) == $bestByPass) { // 제외상품 체크
                                                    unset($displayReDefineArr['best'][$displayKey]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unset($confirmCompareGoodsField, $confirmField, $confirmExceptField, $confirmAll);
                }
            }
        }
        return $displayReDefineArr;
    }

    /**
     * 그리드항목 - 상품리스트 삭제버튼 시 추가진열 노드 삭제
     * 인기상품,메인분류,카테고리
     *
     * @param array $data 상품번호 / 진열데이터(cate,main,best)
     *
     * @return true
     */
    public function goodsListDisplayEachDelete($data)
    {
        $goodsNo = $data['goodsNo'];
        if($data['cateCode']) { // 카테고리 분류 삭제
            $arrBind = [];
            $representCateCd = $data['representCateCd']; // 대표카테고리코드
            // 카테고리 링크 삭제
            $strWhere = 'goodsNo = ? AND cateCd IN (' . $data['cateCode'] . ')';
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $this->db->set_delete_db(DB_GOODS_LINK_CATEGORY, $strWhere, $arrBind);
            // 대표카테고리와 삭제된 카테고리가 같을 때
            $delCateCd = explode(',', $data['cateCode']);
            foreach($delCateCd as $delCateKey => $delCateVal) {
                if ($representCateCd == $delCateVal) {
                    // 기존 카테고리 정보
                    $getLink = $this->getGoodsLinkCategory($goodsNo)[0]; // 카테고리 링크 로드
                    $changeRepresentCateCd = ($getLink['cateCd']) ? $getLink['cateCd'] : NULL; // 최상위 카테고리 지정
                    $this->db->bind_param_push($arrBind['bind'], 's', $changeRepresentCateCd);
                    $this->db->bind_param_push($arrBind['bind'], 's', $goodsNo);
                    $this->db->setModDtUse(true);
                    $this->db->set_update_db(DB_GOODS, 'cateCd = ?', 'goodsNo = ?', $arrBind['bind']);
                    unset($getLink);
                }
            }
        }
        if($data['mainCode']) { // 메인진열 삭제
            $delMainSno = explode(',', $data['mainCode']);
            $convertGoodsNo['goodsNo'] = $goodsNo; // 메인 진열 삭제 메소드 인자가 array로 받음
            foreach($delMainSno as $delMainKey => $delMainVal) {
                $this->modifyGoodsNoPopupDisplay($delMainVal, $convertGoodsNo); // 메인진열삭제
            }
            unset($delMainSno);
        }
        if($data['bestCode']) { // 인기상품진열 삭제
            $bestCodeArray['sno'] = explode(',', $data['bestCode']); // sno 배열화
            $bestCodeArray['batchChange'] = 'n'; // 처리모드
            $bestCodeArray['goodsNo'] = $goodsNo;
            $populate = \App::load('\\Component\\Goods\\Populate');
            $populate->populateGoods($bestCodeArray);
        }
        return true;
    }

    /**
     * 그리드항목 - 결제 아이콘 정보 가공하기
     * @param string $payLimitFl 결제수단 제한 사용여부
     * @param string $payLimit 결제수단 제한값
     *
     * @return array 결제 아이콘
     */
    public function getGoodsListPayLimitIcon($payLimitFl, $payLimit)
    {
        // 결제수단 제한 사용
        if ($payLimitFl == 'y') {
            $payLimitArray = explode(STR_DIVISION, $payLimit);
            if(in_array('pg', $payLimitArray)) {
                // PG의 경우 결제수단 추가
                $payLimitPgArray =  array('pc','pv','pb','ph','ec','eb','ev');
            }
            // PG를 제외한 결제수단
            foreach ($payLimitArray as $payLimitKey => $payLimitVal) {
                if ($payLimitVal && $payLimitVal != 'pg') {
                    // 사용설정 체크
                    if (is_file(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $payLimitVal . '.gif'))) {
                        $val['payLimitIcon'][] = gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $payLimitVal . '.gif')->www(), false);
                    }

                }
            }
            // PG 를 포함한 결제수단
            foreach ($payLimitPgArray as $payLimitPgKey => $payLimitPgVal) {
                if ($payLimitPgVal) {
                    // 사용설정 체크
                    if($this->paySettleKind[$payLimitPgVal]['useFl'] == 'y') {
                        if (is_file(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $payLimitPgVal . '.gif'))) {
                            $val['payLimitIcon'][] = gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $payLimitPgVal . '.gif')->www(), false);
                        }
                    }
                }
            }
            $val['payLimitIcon'] = implode(' ', $val['payLimitIcon']);
            unset($payLimitArray);
        }
        return $val['payLimitIcon'];
    }

    /**
     * 그리드항목 - 카테고리 분류 가공 - 카테고리 tree형태 및 국가 flag 추가
     * @param array $goodsNo 상품번호
     *
     * @return array 카테고리Tree
     */
    public function getGoodsListCategoryTree($cateCd, $removeDepth = 0, $arrow = ' &gt; ', $linkFl = false,$viewFl = true)
    {
        $cate = \App::load('\\Component\\Category\\Category');
        $useMallList = array_combine(array_column($this->gGlobal['useMallList'], 'sno'), $this->gGlobal['useMallList']);

        $thisCateDepth = strlen($cateCd) / DEFAULT_LENGTH_CATE;

        $_tmp = [];
        for ($i = 1; $i < $thisCateDepth; $i++) {
            $_tmp[] = " left('" . $cateCd . "'," . (DEFAULT_LENGTH_CATE * $i) . ") ";
        }
        $_tmp[] = "'" . $cateCd . "'";
        $inStr = "cateCd in (" . implode(',', $_tmp) . ")";
        unset($_tmp);

        if (empty($inStr)) {
            return false;
        }

        if($viewFl && Session::has('manager.managerId') === null) {
            if(Request::isMobile())  $cateDisplayMode = "cateDisplayMobileFl";
            else $cateDisplayMode = "cateDisplayFl";
            $inStr .= ' AND '.$cateDisplayMode.' = \'y\'';
        }

        $data = $cate->getCategoryData(null, null, 'cateCd, cateNm, mallDisplay', $inStr, 'cateCd ASC');

        if ($this->cateType == 'brand') {
            $cateType = 'brandCd';
        } else {
            $cateType = 'cateCd';
        }

        foreach ($data as $key => $val) {
            if ($key >= $removeDepth) {
                if ($linkFl === true) {
                    $_tmp[] = '<a href="../goods/goods_list.php?' . $cateType . '=' . $val['cateCd'] . '">' . strip_tags($val['cateNm']) . '</a>';
                } else {
                    $_tmp[] = strip_tags($val['cateNm']);
                }
            }
            if($this->gGlobal['isUse']) {
                if ($val['mallDisplay']) {
                    $dataFlag = [];
                    foreach (explode(",", $val['mallDisplay']) as $k => $v) {
                        if ($useMallList[$v]) $dataFlag[$useMallList[$v]['domainFl']] = $useMallList[$v]['mallName'];
                    }
                    $_tmpCateFlag = gd_htmlspecialchars_stripslashes($dataFlag);
                }
            }
        }

        if (isset($_tmp)) {
            $returnCate['cateNm'] = implode($arrow, $_tmp);
            $returnCate['cateFlag'] = $_tmpCateFlag;
            return $returnCate;
        } else {
            return false;
        }
    }

    /**
     * 그리드항목 - 상품리스트 관리자 메모 처리
     *
     * @param int $goodsNo 상품번호
     * @param string $memo 메모
     * @param string $mode 조회/갱신
     *
     * @return array data
     */
    public function getGoodsListAdminMemo($goodsNo, $memo, $mode='select')
    {
        $arrBind = [];
        if($mode == 'select') {
            $this->db->bind_param_push($arrBind['bind'], 'i', $goodsNo);
            $strSQL = 'SELECT goodsNo, goodsNm, memo FROM ' . DB_GOODS . ' WHERE goodsNo = ? ';
            $data = $this->db->query_fetch($strSQL, $arrBind['bind']);
            return $data;
        } else if($mode =='update') {
            if($memo) {
                $goodsData['memo'] = $memo;
                $compareField = array_keys($goodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableGoods(), $goodsData, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $goodsNo);
                $this->db->setModDtUse(true);
                $return = $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
                unset($arrBind, $goodsData);
                return $return;
            }
        }
    }

    /**
     * 상품상단검색 조건 - 메인분류 1,2차 선택에 따른 goods 검색 조건 설정
     *
     * @param string $data 데이터 검색 조건 값 분류 1차, 분류2차
     * @param int sno sno 값 전달 - search
     *
     * @return array goods 조건 데이터
     */
    public function searchGoodsListDisplayThemeData($data, $sno=0)
    {
        if($data) {
            if(!$sno) { // 1차분류만 선택 시
                $data['value'] = $data;
                $displayThemeData = $this->getGoodsListDisplayThemeMultiData($data['value'])[0];
            }
            if($sno) { // 2차분류도 선택될 경우
                $displayThemeData = $this->getGoodsListDisplayThemeMultiData(null, $this->search['displayTheme'][1])[0];
            }
        }

        // 상품진열 갯수가 있을 경우
        if ($displayThemeData['goodsNo']) {
            $goodsNoDivisionCheckTabArray = ArrayUtils::removeEmpty(explode(STR_DIVISION, $displayThemeData['goodsNo'])); // 탭 상품번호 갯수 체크
            $displayThemeData['goodsNo'] = implode(INT_DIVISION, $goodsNoDivisionCheckTabArray);
            $goodsNoDivisionCheckArray = ArrayUtils::removeEmpty(explode(INT_DIVISION, $displayThemeData['goodsNo'])); // 상품번호 갯수 체크
            if(count($goodsNoDivisionCheckArray) > 1 || count($goodsNoDivisionCheckTabArray) > 1) {
                $displayThemeGoodsNo = str_replace(INT_DIVISION, ",", $displayThemeData['goodsNo']);
            } else {
                $displayThemeGoodsNo = $displayThemeData['goodsNo'];
            }
            $strWhere[] = 'g.goodsNo IN (' . $displayThemeGoodsNo . ')';
            unset($goodsNoDivisionCheckArray);
        } else if($displayThemeData['sort']) { // 자동진열
            // 제외상품, 제외카테고리, 제외브랜드, 제외공급사 존재할 경우
            $fieldSimpleArray = ['exceptGoodsNo', 'exceptCateCd', 'exceptBrandCd', 'exceptScmNo']; // 제외 항목 필드 데이터
            $fieldSimpleCompareArray = ['goodsNo', 'cateCd', 'brandCd', 'scmNo']; // 상품 필드 데이터
            foreach($fieldSimpleArray as $simpleKey => $simpleVal) {
                if ($displayThemeData[$simpleVal]) {
                    $dataDivisionCheckArray = ArrayUtils::removeEmpty(explode(INT_DIVISION, $displayThemeData[$simpleVal])); // 데이터 갯수 체크
                    if(count($dataDivisionCheckArray) > 1) {
                        $displayThemeStrReData = str_replace(INT_DIVISION, ",", $displayThemeData[$simpleVal]);
                    } else {
                        $displayThemeStrReData = $displayThemeData[$simpleVal];
                    }
                    $joinAs = 'g.'; // 상품테이블 얼리어스
                    $cateLinkFl = '';
                    if($simpleVal == 'exceptCateCd') {
                        $joinAs = '( gl.'; // 상품링크테이블 얼리어스
                        $cateLinkFl = " AND gl.cateLinkFl = 'y' )"; // 링크테이블 일 경우 추가
                    }
                    $strWhere[] = $joinAs . $fieldSimpleCompareArray[$simpleKey] . ' NOT IN (' . $displayThemeStrReData . ') ' . $cateLinkFl;
                    unset($dataDivisionCheckArray);
                }
            }
        }
        unset($displayThemeData, $fieldSimpleArray, $fieldSimpleCompareArray);
        return $strWhere;
    }

    /**
     * 그리드항목 - 메인분류 박스 DATA 추출 (검색 ajax 및 그리드 사용)
     *
     * @param string $postValue 데이터 출력 값 pc/mobile
     * @param int sno sno 값 전달 - search
     *
     * @return array 데이터
     */
    public function getGoodsListDisplayThemeMultiData($postValue, $sno=0)
    {
        $displayFl = ['y','n'];
        $arrBind = [];
        if($postValue['value'] == 'pc') {
            // pcFl 값이 없는 경우가 있음
            $selectWhere = " AND ( dt.pcFl = ? OR dt.pcFl ='' ) AND dt.mobileFl = ? ";
            $this->db->bind_param_push($arrBind, 's', $displayFl[0]);
            $this->db->bind_param_push($arrBind, 's', $displayFl[1]);
        } else if($postValue['value'] == 'mobile') {
            $selectWhere = " AND dt.mobileFl = ? ";
            $this->db->bind_param_push($arrBind, 's', $displayFl[0]);
        }

        // 일련번호가 있는 경우
        if($sno) {
            $selectWhere = " AND dt.sno = ? ";
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }

        // 조회할 필드
        $selectField = [
            'dt.sno',
            'dt.themeNm',
            'dt.pcFl',
            'dt.mobileFl',
            'dt.displayFl',
            'dt.sort',
            'dt.exceptGoodsNo',
            'dt.exceptCateCd',
            'dt.exceptBrandCd',
            'dt.exceptScmNo',
            'dt.goodsNo'
        ];
        //--- 메인상품진열 정보
        $sqlQuery = "SELECT " . implode(',', $selectField) .  " FROM " . DB_DISPLAY_THEME . " as dt WHERE kind = 'main' " . $selectWhere . "  ORDER BY dt.regDt desc";

        $getData = $this->db->query_fetch($sqlQuery, $arrBind);

        // 관리자 노출 기획에 맞춰 데이터 재가공
        $returnData = [];

        foreach($getData as $key =>$value) {
            // pcFl 값이 없는 경우가 있음
            if(empty($value['pcFl']) === true) {
                $value['pcFl'] = 'y';
            }
            // PC 가공
            if($value['pcFl'] == 'y' && $value['mobileFl'] =='n') {
                if($value['themeNm']) {
                    $returnData[$key]['themeNm'] = "PC | " . $value['themeNm'];
                }
            } else if($value['mobileFl'] =='y') { // 모바일 가공
                if($value['themeNm']) {
                    $returnData[$key]['themeNm'] = "모바일 | " . $value['themeNm'];
                }
            }
            // 일련번호
            if($value['sno']) {
                $returnData[$key]['themeCode'] = $value['sno'];
            }
            $fieldSimpleArray = ['goodsNo', 'sort', 'exceptGoodsNo', 'exceptCateCd', 'exceptBrandCd', 'exceptScmNo'];
            foreach( $fieldSimpleArray as $simpleKey => $simpleVal) {
                if($value[$simpleVal]) {
                    $returnData[$key][$simpleVal] = $value[$simpleVal];
                }
            }
        }
        unset($getData, $fieldSimpleArray);
        return $returnData;
    }

    /**
     * 메인분류 멀티 셀렉트 박스 값 전달 및 초기 값 셋팅
     *
     * @param string  $selectID    select box 아이디 (기본 null)
     * @param string  $selectValue selected 된 카테고리 코드
     * @param string  $strStyle    select box style (기본 null)
     * @param boolean $userMode    사용자 화면 출력 (기본 false)
     *
     * @return string 다중 메인분류 select box
     */
    public function getGoodsListDisplayThemeMultiBox($selectID = null, $selectValue = null, $strStyle = null, $userMode = false, $isMobile = false)
    {
        if(is_array($selectValue)) {
            $selectValue['value'] = $selectValue[0];
        } else {
            $selectValue = null;
        }
        // 분류 1차 타이틀
        $getData[] = [['themeNm' =>'PC쇼핑몰', 'themeCode' =>'pc'], ['themeNm' =>'모바일쇼핑몰', 'themeCode' =>'mobile']];
        if(ArrayUtils::removeEmpty($selectValue)) { // 1차 분류 선택 시
            $getData[] = $this->getGoodsListDisplayThemeMultiData($selectValue);
        }
        $tmpName = 'displayTheme';
        // 분류 2차 호출 ajax
        $tempUrl = '/share/display_theme_search_ajax.php';
        // selectValue 값이 배열일 경우 마지막 값으로 설정
        return $this->setGoodsListDisplayThemeMultiBox($tmpName, $getData, gd_isset($selectValue), '2', $tempUrl, '=' . $tmpTitle . '=', $strStyle,$isMobile);
    }

    /**
     * 멀티 셀렉트 박스 생성
     *
     * @param string  $inputID   select box ID
     * @param array   $arrData   기본 적으로 출력할 select box 의 배열 값
     * @param array   $arrValue  각 select box 의 selected 값
     * @param integer $selectCnt select box 총 갯수
     * @param string  $ajaxUrl   다음 select box 값을 가지고오기 위한 jquery post URL
     * @param string  $strTitle  select box 첫번째 option의 타이틀 명
     * @param string  $addStyle  select box 의 스타일 (style, multiple, size, onchange 등등의) (default = null)
     *
     * @return string select box
     */
    protected function setGoodsListDisplayThemeMultiBox($inputID, $arrData, $arrValue = null, $selectCnt, $ajaxUrl, $strTitle = '---', $addStyle = null,$isMobile= false)
    {
        $tmp = '';
        $tmpValue = [];
        for ($i = 0; $i < $selectCnt; $i++) {
            $inputNo = $i + 1;
            if($isMobile) {
                $tmp.='<div class="inp_sel" style="margin-top:10px">'.chr(10);
            }
            if(gd_is_skin_division()) {
                $tmp.='<div class="select_box">'.chr(10);
                $selectClass = "chosen-select";
            } else {
                $selectClass = "form-control multiple-select";
            }
            // select 박스 타이틀 재선언
            if($inputNo == 1) {
                $strTitle = "=전체=";
                $strTitleScript = "=메인페이지 분류 선택=";
            }
            if($inputNo == 2) {
                $strTitle = "=메인페이지 분류 선택=";
            }
            $tmp .= '<select id="' . $inputID . $inputNo . '" name="'.$inputID.'[]" ' . $addStyle . ' class="'.$selectClass.'">' . chr(10);
            $tmp .= '<option value="">' . $strTitle . '</option>' . chr(10);
            if (gd_isset($arrData[$i])) {
                foreach ($arrData[$i] as $key => $val) {
                    $disabledStr = ""; //$disabledStr = "disabled='disabled'";
                    $tmp .= '<option value="' . $val['themeCode'] . '" '.$disabledStr.' ">' . StringUtils::htmlSpecialChars($val['themeNm']) . '</option>' . chr(10);
                    unset($mallSno);
                    unset($mallName);
                }
            }
            $tmp .= '</select>' . chr(10);
            if(gd_is_skin_division()) {
                $tmp.='</div>'.chr(10);
            }
            if($isMobile) {
                $tmp.='</div>'.chr(10);
            }
            $tmpBox[] = '$(\'#' . $inputID . $inputNo . '\').multi_select_box(\'#' . $inputID . '\',' . $selectCnt . ',\'' . $ajaxUrl . '\',\'' . $strTitleScript . '\');';
            if (gd_isset($arrValue[$i])) {
                $tmpValue[] = "$('#" . $inputID . $inputNo . " option[value=\'" . $arrValue[$i] . "\']').attr('selected','selected');";
            }
        }

        $tmp .= '<script type="text/javascript">' . chr(10);
        $tmp .= '$(function() {' . chr(10);
        $tmp .= '	' . implode(chr(10) . '	', $tmpBox) . chr(10);
        $tmp .= '});' . chr(10);
        $tmp .= implode(chr(10), $tmpValue) . chr(10);
        $tmp .= '</script>' . chr(10);
        return $tmp;
    }

    /**
     * 관리자 메인상품진열 데이터
     */
    public function getAdminMainDisplayTheme()
    {
        $arrBind = [];
        $strWhere = 'kind = ?';
        $strOrder = "regDt DESC ";
        $this->db->bind_param_push($arrBind['bind'], 's', main);
        $strSQL = "SELECT sno, themeNm, mobileFl, themeCd, sortAutoFl, goodsNo, exceptGoodsNo  FROM " .DB_DISPLAY_THEME. " WHERE " .$strWhere." ORDER BY ".$strOrder;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind']);

        return $getData;
    }

    /**
     * 관리자 테마설정 데이터
     */
    public function getAdminThemeConfig($arrData)
    {
        foreach($arrData as $key => $val) {
            $arrBind = [];
            $strWhere = 'themeCd = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $val['themeCd']);
            $strSQL = "SELECT themeCd, displayType, detailSet FROM " . DB_DISPLAY_THEME_CONFIG . " WHERE " . $strWhere;
            $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
            $data[] = $getData;
        }

        return $data;
    }

    /**
     * 상품 아이콘 정보 저장
     *
     * @param array $arrIconCd 저장할 아이콘 정보
     * @param string $iconKind 아이콘 타입
     * @param stirng $strGoodsNo 상품번호
     * @param string $strGoodsIconStartYmd 상품 아이콘 설정기간 - 시작
     * @param string $strGoodsIconStartEnd 상품 아이콘 설정기간 - 종료
     * @param integer $benefitSno 상품혜택 일렬번호
     */
    public function setGoodsIcon($arrIconCd = null, $iconKind, $strGoodsNo, $strGoodsIconStartYmd, $strGoodsIconStartEnd, $benefitSno = false)
    {
        //해당 상품 아이콘 정보 삭제
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $strGoodsNo);
        $this->db->bind_param_push($arrBind, 's', $iconKind);
        $this->db->set_delete_db(DB_GOODS_ICON, 'goodsNo = ? AND iconKind = ?', $arrBind);
        unset($arrBind);

        //아이콘 정보 저장
        foreach ($arrIconCd as $k => $v) {
            $inData['goodsNo'] = $strGoodsNo;
            $inData['goodsIconCd'] = $v;
            $inData['iconKind'] = $iconKind;
            $inData['goodsIconStartYmd'] = $strGoodsIconStartYmd;
            $inData['goodsIconEndYmd'] = $strGoodsIconStartEnd;
            $inData['benefitSno'] = ($benefitSno) ?  : 0;

            $arrBind = $this->db->get_binding(DBTableField::tableGoodsIcon(), $inData, 'insert');
            $return = $this->db->set_insert_db(DB_GOODS_ICON, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);
            unset($inData);
        }

        return $return;
    }

    /**
     * 상품 데이터 조회
     *
     * @param array $getValue
     * @param integer $minGooodsNo
     * @param integer $maxGoodsNo
     *
     * @return array
     * @throws
     */
    public function getGoodsListGenerator($getValue, $minGooodsNo = null, $maxGoodsNo = null)
    {
        gd_isset($this->goodsTable,DB_GOODS);

        if ($getValue['mode'] == 'crema') {
            // 당일 데이터 제외
            $this->arrWhere[] = "DATE_FORMAT(g.regDt, '%Y-%m-%d') != ?";
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d'));
            // 삭제 상품 제외
            $this->arrWhere[] = "g.delFl = ?";
            $this->db->bind_param_push($this->arrBind, 's', 'n');
            // 미승인 상품 제외
            $this->arrWhere[] = "g.applyFl = ?";
            $this->db->bind_param_push($this->arrBind, 's', 'y');
        }

        if (is_null($minGooodsNo) === false && is_null($maxGoodsNo) === false) {
            // between 조회
            $this->arrWhere[] = '(g.goodsNo BETWEEN ? AND ?)';
            $this->db->bind_param_push($this->arrBind, 'i', $minGooodsNo);
            $this->db->bind_param_push($this->arrBind, 'i', $maxGoodsNo);
        }

        $this->db->strField = "g.goodsNo";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $fromQuery = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($fromQuery) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $fromQuery) ;
        $preSQL =  $this->db->prepare($strSQL);
        $this->db->bind_param($preSQL, $this->arrBind);
        $fromSql = $this->db->getBindingQueryString($strSQL, $this->arrBind);
        $tmpStrField = implode(',', $getValue['strField']);
        $query = 'SELECT ' . $tmpStrField .' FROM (' . $fromSql . ') gs INNER JOIN ' . DB_GOODS . ' as g ON g.goodsNo = gs.goodsNo order by gs.goodsNo desc';
        $result = $this->db->query_fetch_generator($query);
        unset($this->arrBind, $this->arrWhere);

        return $result;
    }

    /**
     * 상품 등록 시 옵션 임시 저장
     *
     * @param $data 등록 할 옵션 값
     *
     * @return boolean
     * @throws
     */
    public function goodsOptionTempRegister($data)
    {
        gd_isset($this->goodsTable, DB_GOODS);

        //임시 세션값이 없다면 생성
        if (empty($data['sess'])) {
            do {
                //임의의 32자리 값 생성
                $sessionString = '';
                for ($i = 0; $i < 32; $i++) {
                    $tmpChar = '';
                    switch (rand(0, 2)) {
                        case 0:
                            $tmpChar = chr(rand(48, 57));
                            break;
                        case 1:
                            $tmpChar = chr(rand(65, 90));
                            break;
                        case 2:
                            $tmpChar = chr(rand(97, 122));
                            break;
                    }
                    $sessionString .= $tmpChar;

                }
                //중복값 검색
                $arrBind = [];
                $strWhere = 'session = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $sessionString);
                $strSQL = "SELECT COUNT(`session`) cnt FROM " . DB_GOODS_OPTION_TEMP . " WHERE " . $strWhere;
                $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
                unset($arrBind);
                $dataOption = $getData;
            } while ($dataOption['cnt'] != '0');
        }else{
            $sessionString = $data['sess'];
        }

        $arrBind = [];
        //이전 값은 삭제 할 것
        $strSQL = 'DELETE FROM '.DB_GOODS_OPTION_TEMP.' WHERE session = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $sessionString);
        $this->db->bind_query($strSQL, $arrBind['bind']);
        unset ($arrBind);

        $stocked = false;
        $stock = 0;
        foreach ($data['optionY']['optionValueText'] as $k => $v) {
            $optionData['session'] = $sessionString;
            $optionData['optionValue1'] = $data['optionY']['optionName'][0];
            $optionData['optionValue2'] = $data['optionY']['optionName'][1];
            $optionData['optionValue3'] = $data['optionY']['optionName'][2];
            $optionData['optionValue4'] = $data['optionY']['optionName'][3];
            $optionData['optionValue5'] = $data['optionY']['optionName'][4];
            $optionData['optionDisplayFl'] = $data['optionY']['optionDisplayFl'];
            $optionData['optionImagePreviewFl'] = $data['optionImagePreviewFl'];
            $optionData['optionImageDisplayFl'] = $data['optionImageDisplayFl'];
            $optionData['optionValueText'] = $v;
            $optionData['optionCostPrice'] = $data['optionY']['optionCostPrice'][$k];
            $optionData['optionPrice'] = $data['optionY']['optionPrice'][$k];
            $optionData['stockCnt'] = $data['optionY']['stockCnt'][$k];
            if($data['optionY']['stockCnt'][$k] > 0 && !empty($data['optionY']['stockCnt'][$k])){
                $stocked = true;
                if($data['optionY']['optionSellFl'][$k] == 'y'){
                    $stock += $data['optionY']['stockCnt'][$k];
                }
            }
            $optionData['sellStopFl'] = $data['optionY']['optionStopFl'][$k];
            $optionData['sellStopStock'] = $data['optionY']['optionStopCnt'][$k];
            $optionData['confirmRequestFl'] = $data['optionY']['optionRequestFl'][$k];
            $optionData['confirmRequestStock'] = $data['optionY']['optionRequestCnt'][$k];
            $optionData['optionViewFl'] = $data['optionY']['optionViewFl'][$k];
            $optionData['optionStockFl'] = $data['optionY']['optionSellFl'][$k];
            if($optionData['optionStockFl'] != 'y' && $optionData['optionStockFl'] != 'n'){
                $optionData['optionStockFl'] = 't';
                $optionData['optionStockCode'] = $data['optionY']['optionSellFl'][$k];
            }
            $optionData['optionDeliveryFl'] = $data['optionY']['optionDeliveryFl'][$k];
            if($optionData['optionDeliveryFl'] != 'normal'){
                $optionData['optionDeliveryFl'] = 't';
                $optionData['optionDeliveryCode'] = $data['optionY']['optionDeliveryFl'][$k];
            }
            $optionData['optionCode'] = $data['optionY']['optionCode'][$k];
            $optionData['optionMemo'] = $data['optionY']['optionMemo'][$k];
            $optionData['regDt'] = date('Y-m-d H:i:s');

            $arrBind = $this->db->get_binding(DBTableField::tableGoodsOptionTemp(), $optionData, 'insert');
            $this->db->set_insert_db(DB_GOODS_OPTION_TEMP, $arrBind['param'], $arrBind['bind'], 'y');

            unset($arrBind);
            unset($optionData);
        }

        $optionImage = array();
        $optionImageChanged = array();
        $optionImgPath = App::getUserBasePath() . '/data/goods/option_temp';
        //옵션 이미지 업로드 하기
        $arrFileData = Request::files()->get('optionYIcon');
        if ($arrFileData['name']['goodsImage']) {
            foreach ($arrFileData['name']['goodsImage'] as $fKey => $fVal) {
                foreach ($fVal as $vKey => $vVal) {
                    if (gd_file_uploadable($arrFileData, 'image', 'goodsImage', $fKey, $vKey) === true) {
                        if(file_exists($optionImgPath) === false){
                            //디렉토리 생성
                            mkdir($optionImgPath);
                        }
                        $fileExt = explode('.', $arrFileData['name']['goodsImage'][$fKey][$vKey]);
                        $fileExt = $fileExt[count($fileExt)-1];
                        move_uploaded_file($arrFileData['tmp_name']['goodsImage'][$fKey][$vKey], $optionImgPath.DIRECTORY_SEPARATOR.$sessionString.'_'.$vKey.'.'.$fileExt);
                    }
                    if ($data['optionImageAddUrl'] == 'y' && !empty($data['optionYIcon']['goodsImageText'][0][$vKey])) {
                        $optionImage[$vKey] = $data['optionYIcon']['goodsImageText'][0][$vKey];
                        $optionImageChanged[$vKey] = $data['optionYIcon']['goodsImageTextChanged'][0][$vKey];
                    } else if (gd_file_uploadable($arrFileData, 'image', 'goodsImage', $fKey, $vKey) === true) {
                        $optionImage[$vKey] = $sessionString.'_'.$vKey.'.'.$fileExt;
                        $optionImageChanged[$vKey] = 'y';
                    } else {
                        $optionImage[$vKey] = '';
                        $optionImageChanged[$vKey] = $data['optionYIcon']['goodsImageTextChanged'][0][$vKey];
                    }
                }
            }
        }

        //임시 파일 테이블에 등록
        foreach($optionImage as $key => $value){
            //이전 값은 삭제 할 것
            $strSQL = 'DELETE FROM '.DB_GOODS_OPTION_ICON_TEMP.' WHERE session = ? AND optionNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $sessionString);
            $this->db->bind_param_push($arrBind['bind'], 's', $key);
            $this->db->bind_query($strSQL, $arrBind['bind']);
            unset ($arrBind);

            //임시로 등록된 파일 가져오기
            $optionImgData['session'] = $sessionString;
            $optionImgData['optionNo'] = $key;
            $optionImgData['optionValue'] = $data['optionY']['optionValue'][0][$key];
            $optionImgData['goodsImage'] = $optionImage[$key];
            $optionImgData['isUpdated'] = $optionImageChanged[$key];

            $arrBind = $this->db->get_binding(DBTableField::tableGoodsOptionIconTemp(), $optionImgData, 'insert');
            $this->db->set_insert_db(DB_GOODS_OPTION_ICON_TEMP, $arrBind['param'], $arrBind['bind'], 'y');
            unset ($arrBind);
            unset ($optionImgData);
        }

        $return['stocked'] = $stocked;
        $return['stock'] = $stock;
        $return['sessionString'] = $sessionString;
        return $return;
    }
}
