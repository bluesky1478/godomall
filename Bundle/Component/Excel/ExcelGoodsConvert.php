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

namespace Bundle\Component\Excel;


use Component\Database\DBTableField;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * Class ExcelGoodsConvert
 * @package Bundle\Component\Excel
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 * @method doGoodsnoCheckWrapper($goodsNo = null)
 * @method doGoodsNoInsertWrapper()
 * @method setGoodsDataWrapper($goodsData, $goodsNo, $goodsInsert, $tableField, $tableName, $strOrderBy = null)
 * @method setGoodsLinkCategoryWrapper($linkData, $goodsNo, $goodsInsert)
 * @method setGoodsLinkBrandWrapper($brandData, $goodsNo, $goodsInsert)
 */
class ExcelGoodsConvert extends \Component\Excel\ExcelDataConvert
{
    /** @var array */
    protected $dbNames = [];
    /** @var array */
    protected $dbKeys = [];
    /** @var array */
    protected $fields = [];
    /** @var bool $hasGoodsNoField 엑셀에 상품 번호 필드가 있는경우 */
    protected $hasGoodsNoField = false;
    /** @var bool $hasGoodsNmField 엑셀에 상품명 필드가 있는경우 */
    protected $hasGoodsNmField = false;
    protected $hasGoodsCommissionAuth = true;
    protected $hasGoodsSalesDateAuth = true;
    protected $hasGoodsNmAuth = true;
    protected $hasGoodsPriceAuth = true;
    protected $hasGoodsStockAuth = true;
    protected $hasScmPermissionInsertAndIsProvider = false;
    protected $hasScmPermissionModifyAndIsProvider = false;
    /** @var \PhpOffice\PhpSpreadsheet\Worksheet $sheet */
    protected $sheet;
    /** @var string $scmNo */
    protected $scmNo;
    /** @var array $configDisplayAutoCategory */
    protected $configDisplayAutoCategory;
    /** @var \Bundle\Component\Goods\GoodsBenefit $goodsBenefit */
    protected $goodsBenefit;
    /** @var  \Framework\File\UserFilePathResolver $userFilPath */
    protected $userFilPath;
    /** @var \Bundle\Component\Scm\ScmAdmin $scmAdmin */
    protected $scmAdmin;
    /** @var \Bundle\Component\Policy\SeoTag $seoTag */
    protected $seoTag;
    /** @var \Bundle\Component\Goods\GoodsAdmin $goodsAdmin */
    protected $goodsAdmin;
    /** @var array $scmCommissions */
    protected $scmCommissions = [];
    protected $updateGoodsNo = [];
    protected $tableGoods;
    /** @var \Bundle\Component\Marketing\FacebookAd $facebookAd */
    protected $facebookAd;
    protected $modDtUse;
    /** @var string $modDtUse 상품수정일 변경 유무 */

    /** @var array $kcmarkInfo kc인증정보 */
    protected $kcmarkInfo = [];

    /**
     * 상품 엑셀 업로드
     *
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Throwable
     * @param string $modDtUse 상품수정일 변경 유무
     */
    public function setExcelGoodsUp($modDtUse = null)
    {
        $this->userFilPath = \App::getInstance('user.path');
        $this->scmAdmin = \App::load('Component\\Scm\\ScmAdmin');
        $this->seoTag = \App::load('Component\\Policy\\SeoTag');
        $this->goodsBenefit = \App::load('Component\\Goods\\GoodsBenefit');
        $this->facebookAd = \App::load('Component\\Marketing\\FacebookAd');
        $this->goodsAdmin = \App::load('\\Component\\Goods\\GoodsAdmin');
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $filesValue = $request->files()->toArray();
        if ($request->post()->get('scmNo', '') == '') {
            $request->post()->del('scmNo');
        }
        $this->scmNo = $request->post()->get('scmNo', (string) $session->get('manager.scmNo'));
        $this->hasScmPermissionInsertAndIsProvider = ($session->get('manager.isProvider') && $session->get('manager.scmPermissionInsert', '') == 'c');
        $this->hasScmPermissionModifyAndIsProvider = ($session->get('manager.isProvider') && $session->get('manager.scmPermissionModify', '') == 'c');
        if ($this->hasError()) {
            $this->createBodyByReadError();
            $this->printExcel();

            return false;
        }
        $xls = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $chk = $xls->canRead($filesValue['excel']['tmp_name']);
        if ($chk === false) {
            $this->createBodyByReadError();
            $this->printExcel();

            return false;
        }
        $xls->setReadDataOnly(true);
        $this->sheet = $xls->setReadEmptyCells(false)->load($filesValue['excel']['tmp_name'])->getActiveSheet();
        if ($this->sheet->getHighestRow() < 4) {
            $this->createDataError();
            $this->printExcel();

            return false;
        }
        // 설정된 엑셀 코드 재설정
        $excelField = $this->excelGoods();
        foreach ($excelField as $key => $val) {
            $this->fields[$val['excelKey']] = $val['dbKey'];
            $this->dbNames[$val['dbKey']] = $val['dbName'];
        }
        unset($excelField);

        if ($this->validate()) {
            // 상품수정일 변경유무 추가
            if ($modDtUse) {
                $this->modDtUse = $modDtUse;
            }
            $this->setTableKey();
        } else {
            $message = '오류가 발생하였습니다.';
            if (!$this->hasGoodsCommissionAuth) {
                $message = '수수료 권한이 없습니다. commission 셀을 삭제 후 업로드 해 주세요.';
            } else if (!$this->hasGoodsSalesDateAuth) {
                $message = '판매기간 권한이 없습니다. 상품판매기간 시작일, 종료일 셀을 삭제 후 업로드 해 주세요.';
            }
            $this->createBodyByMessage($message);
            $this->printExcel();

            return false;
        }

        echo $this->excelHeader;

        echo '<table border="1">' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td>' . __('번호') . '</td><td>' . __('상품번호') . '</td><td>' . __('등록/수정') . '</td><td>' . __('이미지저장소') . '</td>' . chr(10);
        echo '</tr>' . chr(10);

        $this->processExcel();
        echo $this->excelFooter;

        return true;
    }

    /**
     * validateFields
     *
     * @return bool
     */
    protected function validate()
    {
        $cells = $this->sheet->getRowIterator(2)->current()->getCellIterator();
        $session = \App::getInstance('session');
        $cells->rewind();
        $isFunctionAuthStateCheck = $session->get('manager.functionAuthState') == 'check';
        if ($isFunctionAuthStateCheck) {
            $this->hasGoodsNmAuth = $session->get('manager.functionAuth.goodsNm', 'n') == 'y';
            $this->hasGoodsPriceAuth = $session->get('manager.functionAuth.goodsPrice', 'n') == 'y';
        }
        $this->hasGoodsStockAuth = $session->get('manager.functionAuth.goodsStockOverall', 'n') == 'y';
        while ($cells->valid()) {
            $cell = $cells->current();
            $value = $cell->getValue();
            if ($this->fields[$value] == 'goodsNo') {
                $this->hasGoodsNoField = true;
            }
            if ($this->fields[$value] == 'goodsNm') {
                $this->hasGoodsNmField = true;
            }
            if ($this->fields[$value] == 'commission' && $isFunctionAuthStateCheck) {
                $this->hasGoodsCommissionAuth = $session->get('manager.functionAuth.goodsCommission', 'n') == 'y';
            }
            if (($this->fields[$value] == 'salesStartYmd' || $this->fields[$value] == 'salesEndYmd') && $isFunctionAuthStateCheck) {
                $this->hasGoodsSalesDateAuth = $session->get('manager.functionAuth.goodsSalesDate', 'n') == 'y';
            }
            $cells->next();
        }

        return $this->hasGoodsSalesDateAuth && $this->hasGoodsCommissionAuth;
    }

    /**
     * initDbKeys
     */
    protected function setTableKey()
    {
        $cells = $this->sheet->getRowIterator(2)->current()->getCellIterator();
        $cells->rewind();
        $idx = 1;
        while ($cells->valid()) {
            $cell = $cells->current();
            $value = $cell->getValue();
            $this->dbKeys[$idx] = $this->fields[$value];
            $idx++;
            $cells->next();
        }
    }

    protected function processExcel()
    {
        $this->tableGoods = DBTableField::tableGoods();
        $rows = $this->sheet->getRowIterator(4);
        while ($rows->valid()) {
            $row = $rows->current();
            $db = \App::getInstance('DB');
            $db->begin_tran();
            try {
                $this->processCells($row);
            } catch (\Throwable $e) {
                $db->rollback();
                throw $e;
            }
            $db->commit();
            $rows->next();
        }
        echo '</table>' . chr(10);

        $this->printExcel();
        unset($this->excelBody, $this->excelHeader, $this->excelFooter);
        if ($this->goodsDivisionFl && count($this->updateGoodsNo) > 0) {
            //엑셀 상품 검색 일괄 수정
            $this->updateGoodsSearch($this->updateGoodsNo, $this->modDtUse);
        }

        return true;
    }

    /**
     * processRow
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Row $row
     *
     * @return bool
     * @throws \Throwable
     */
    protected function processCells($row)
    {
        $cells = $row->getCellIterator();
        $goodsNoCompare = $goodsNmEach = $goodsNoEach = true;
        $failMsg = null;
        $goodsData = $linkData = $infoData = $optionData = $iconData = $addNameData = $addValueData = $textData = $imageData = $seoTagData = $fbData = $globalData = $brandData = $goodsIconDataArr = [];
        $cells->rewind();
        $goodsData['scmNo'] = $this->scmNo;
        $idx = 1;
        echo '<tr>' . chr(10);
        echo '<td>' . ($row->getRowIndex() - 3) . '</td>' . chr(10);
        while ($cells->valid() && $failMsg === null) {
            $cell = $cells->current();
            $value = trim($cell->getValue());
            $dbKey = $this->dbKeys[$idx];
            $dbName = $this->dbNames[$dbKey];
            $cells->next();
            $idx++;

            switch ($dbName) {
                case 'goods':
                    $goodsData[$dbKey] = StringUtils::strIsSet($value);
                    $this->processGoods($goodsData, $dbKey);
                    break;
                case 'goodsGlobal':
                    $globalDataArr = explode(chr(10), StringUtils::strIsSet($value))[0]; // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
                    $tmpKey = explode("_", $dbKey);
                    $globalData[$tmpKey[1]][$tmpKey[2]] = $globalDataArr;
                    $globalData[$tmpKey[1]]['mallSno'] = $tmpKey[1];
                    unset($tmpKey, $globalDataArr);
                    break;
                case 'link':
                    $this->processLink($goodsData, $dbKey, $linkData, $value);
                    break;
                case 'brand':
                    $brandData = StringUtils::strIsSet($value);
                    if (strlen($brandData) == 0 || (strlen($brandData) % DEFAULT_LENGTH_BRAND) != 0 || (strlen($brandData) > DEFAULT_LENGTH_BRAND * DEFAULT_DEPTH_BRAND)) {
                        $brandData = '';
                    }
                    $goodsData[$dbKey] = $brandData;
                    break;
                case 'info':
                    $infoDataArr = explode(chr(10), StringUtils::strIsSet($value)); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
                    if (!empty($infoDataArr[0])) {
                        foreach ($infoDataArr as $key => $val) {
                            $tmpArr = explode(STR_DIVISION, $val);
                            $infoData[$key]['infoTitle'] = $tmpArr[0];
                            $infoData[$key]['infoValue'] = $tmpArr[1];
                        }
                        unset($tmpArr);
                    }
                    unset($infoDataArr);
                    break;
                case 'icon':
                    $iconDataArr = explode(chr(10), StringUtils::strIsSet($value)); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
                    if (!empty($iconDataArr[0])) {
                        foreach ($iconDataArr as $key => $val) {
                            $tmpArr = explode(STR_DIVISION, $val);
                            $iconData[$key]['optionNo'] = 0;
                            $iconData[$key]['optionValue'] = $tmpArr[0];
                            $iconData[$key]['goodsImage'] = $tmpArr[1];
                        }
                        unset($tmpArr);
                    }
                    unset($iconDataArr);
                    break;
                case 'option':
                    $optionDataArr = explode(chr(10), StringUtils::strIsSet($value)); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
                    if ($dbKey == 'stockCnt') {
                        $goodsData['totalStock'] = 0;
                    }
                    foreach ($optionDataArr as $key => $val) {
                        if ($dbKey == 'optionValue') {
                            $tmpArr = explode(STR_DIVISION, $val);
                            $optionData[$key]['optionNo'] = $key + 1;
                            for ($m = 1; $m <= DEFAULT_LIMIT_OPTION; $m++) {
                                $n = $m - 1;
                                $optionData[$key][$dbKey . $m] = StringUtils::strIsSet($tmpArr[$n]);
                            }
                        } else {
                            if (isset($optionData[$key]['optionNo']) === true) {
                                // optionValue 가 없는데 가격이나 정가 등등만 있는경우는 배열에 담지 않음
                                $optionData[$key][$dbKey] = $val;
                            }
                        }
                        if ($dbKey == 'stockCnt') {
                            $goodsData['totalStock'] = $goodsData['totalStock'] + $val; // 전체 재고량
                        }
                    }
                    unset($tmpArr, $optionDataArr);
                    break;
                case 'add':
                    $addDataArr = explode(chr(10), StringUtils::strIsSet($value)); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
                    if (!empty($addDataArr[0])) {
                        $optionNameChk = '';
                        $optionCd = $optionKey = 0;
                        foreach ($addDataArr as $key => $val) {
                            $tmpArr = explode(STR_DIVISION, $val);
                            if ($optionNameChk != $tmpArr[0]) {
                                $optionNameChk = $tmpArr[0];
                                $optionCd++;
                                $addNameData[$optionKey]['optionName'] = $tmpArr[0];
                                $addNameData[$optionKey]['mustFl'] = $tmpArr[1];
                                $addNameData[$optionKey]['optionCd'] = $optionCd;
                                $optionKey++;
                            }
                            $addValueData[$key]['optionValue'] = $tmpArr[2];
                            $addValueData[$key]['addPrice'] = $tmpArr[3];
                            $addValueData[$key]['optionCd'] = $optionCd;
                        }
                        unset($tmpArr);
                    }
                    unset($addDataArr);
                    break;
                case 'text':
                    $textDataArr = explode(chr(10), StringUtils::strIsSet($value)); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
                    if (!empty($textDataArr[0])) {
                        foreach ($textDataArr as $key => $val) {
                            $tmpArr = explode(STR_DIVISION, $val);
                            $textData[$key]['optionName'] = $tmpArr[0];
                            $textData[$key]['mustFl'] = $tmpArr[1];
                            $textData[$key]['addPrice'] = $tmpArr[2];
                            $textData[$key]['inputLimit'] = $tmpArr[3];
                        }
                        unset($tmpArr);
                    }
                    unset($textDataArr);
                    break;
                case 'image':
                    $this->processImage($imageData, $value);
                    break;
                case 'seoTag':
                    $seoTagData[strtolower(str_replace('seoTag', '', $dbKey))] = $value;
                    break;

                case 'goodsIcon':
                    if ($dbKey == 'goodsIconCdPeriod') {
                        $goodsIconDataArr[$dbKey] = ArrayUtils::removeEmpty(explode("||", StringUtils::strIsSet($value)));
                    } else if ($dbKey == 'goodsIconStartYmd') {
                        $goodsIconDataArr[$dbKey] = StringUtils::strIsSet($value);
                    } else if ($dbKey == 'goodsIconEndYmd') {
                        $goodsIconDataArr[$dbKey] = StringUtils::strIsSet($value);
                    } else {
                        $goodsIconDataArr[$dbKey] = ArrayUtils::removeEmpty(explode("||", StringUtils::strIsSet($value)));
                    }
                    break;
                case 'goodsWeight':
                case 'goodsVolume':
                    $goodsData[$dbName] = gd_number_figure($value,  '0.001', 'floor');
                    break;
            }

            if ($goodsData['optionFl'] == 'n') {
                //옵션이 n이면 옵션정보 관련 모두 초기화 / 옵션 수정사항 반영안댐
                $goodsData['optionName'] = '';
            }
            switch ($dbKey) {
                case 'deliveryAddArea':
                    $goodsData[$dbKey] = str_replace(chr(10), MARK_DIVISION, $goodsData[$dbKey]);
                    break;
                case 'deliverySno':
                    StringUtils::strIsSet($value, 1);
                    $goodsData[$dbKey] = $value;
                    break;
                case 'fbVn':
                case 'fbImageName':
                    $fbData[$dbKey] = $value;
                    break;
                case 'goodsNo':
                    // goodsNo 없으면 insert, 새로운 goodsNo 입력시 fail
                    if ((empty($value) === true)) {
                        $goodsNoEach = false; // 해당 데이터에 상품 번호가 없는 경우 (insert)
                    } else if ($this->doGoodsnoCheckWrapper($value) === false) {
                        $goodsNoCompare = false; //새로운 상품코드
                        break;
                    }
                    break;
                case 'goodsNm':
                    if (empty($value) === true) {
                        $goodsNmEach = false;
                    }
                    break;
                case 'optionSellFl':
                    foreach($optionData as $key => $value){
                        if($value['optionSellFl'] != 'y' && $value['optionSellFl'] != 'n' && $value['optionSellFl'] != ''){
                            $optionData[$key]['optionSellFl'] = 't';
                            $optionData[$key]['optionSellCode'] = $value['optionSellFl'];
                        }
                    }
                    break;
                case 'optionDeliveryFl':
                    foreach($optionData as $key => $value){
                        if($value['optionDeliveryFl'] != 'y' && $value['optionDeliveryFl'] != ''){
                            $optionData[$key]['optionDeliveryFl'] = 't';
                            $optionData[$key]['optionDeliveryCode'] = $value['optionDeliveryCode'];
                        }else if($value['optionDeliveryFl'] == 'y'){
                            $optionData[$key]['optionDeliveryFl'] = 'normal';
                        }
                    }
                    break;
            }
            // 상품 등록인 경우에만 상품명 없을시 실패
            if (($goodsNoEach === false || $this->hasGoodsNoField === false) && ($goodsNmEach === false || $this->hasGoodsNmField === false)) {
                $failMsg = '상품명 값은 필수입니다.';
            }
            // 새로운 상품코드 입력시 실패
            if ($goodsNoCompare === false) {
                $failMsg = '일치하는 상품번호가 없습니다.';
            }
        }
        // 오류 있을시 실패메시지 저장
        if ($failMsg) {
            if ($goodsData['goodsNo']) {
                echo '<td>' . $goodsData['goodsNo'] . '</td>' . chr(10);
                echo '<td>update (실패) ' . $failMsg . '</td>' . chr(10);
            } else {
                echo '<td></td>' . chr(10);
                echo '<td>insert (실패) ' . $failMsg . '</td>' . chr(10);
            }
            echo '<td></td>' . chr(10);
            echo '</tr>' . chr(10);

            return false;
        }

        // 이시점에서 구매수량기준이 ID기준인지 체크해 설정값을 강제로 바꿔준다.
        if ($goodsData['fixedOrderCnt'] == 'id') {
            // 처리
            if ($goodsData['goodsPermission'] == 'all') {
                $goodsData['goodsPermission'] = 'member';
            }
        }

        // insert 인 경우, 이미지 저장 경로 변경
        $goodsInsert = false;
        if ($goodsNoEach === false || $this->hasGoodsNoField === false) {
            $goodsData['goodsNo'] = $this->doGoodsNoInsertWrapper();
            StringUtils::strIsSet($goodsData['imageStorage'], 'local');
            StringUtils::strIsSet($goodsData['deliverySno'], 1);
            $goodsInsert = true;
            $goodsData['excelFl'] = 'R';
            echo '<td>' . $goodsData['goodsNo'] . '</td>' . chr(10);
            echo '<td>insert (등록)</td>' . chr(10);
            if (empty($optionData) === true) {
                // 옵션데이터가 아예 없는 경우 기본 필드 하나 생성
                $optionData[0]['optionNo'] = 1;
            }
        } else {
            $goodsData['excelFl'] = 'M';
            echo '<td>' . $goodsData['goodsNo'] . '</td>' . chr(10);
            echo '<td>update (수정)</td>' . chr(10);

            /**
             * 업데이트 이전 데이터 가져오기 (로그 저장 시 사용함) - 2018.10.10 parkjs
             **/
            $goodsAdminClass    = \App::load('\\Component\\Goods\\GoodsAdmin');
            $tmpStrWhere        = $this->db->strWhere;
            unset($this->db->strWhere);
            $exceptTableField   = ['modDt', 'regDt', 'applyDt', 'applyFl', 'applyMsg', 'delDt', 'applyType', 'hitCnt', 'orderCnt', 'orderGoodsCnt', 'reviewCnt', 'cartCnt', 'wishCnt'];
            $useTableField      = DBTableField::setTableField('tableGoods', null, $exceptTableField, 'g');
            $prevGoodsData      = $goodsAdminClass->getGoodsInfo($goodsData['goodsNo'], implode(',', $useTableField)); //필수
            $prevCategoryData   = $goodsAdminClass->getGoodsLinkCategory($goodsData['goodsNo']);
            $prevAddInfoData    = $goodsAdminClass->getGoodsAddInfo($goodsData['goodsNo']);
            $prevImageData      = $goodsAdminClass->getGoodsImage($goodsData['goodsNo']);
            $prevOptionTextData = $goodsAdminClass->getGoodsOptionText($goodsData['goodsNo']);
            $prevOptionData     = $goodsAdminClass->getGoodsOption($goodsData['goodsNo']);
            unset($prevOptionData['optVal']);

            if(isset($goodsData['goodsIconCdPeriod']) || isset($goodsData['goodsIconCd'])) {
                $originGoodsIconData        = $goodsAdminClass->getGoodsDetailIcon($goodsData['goodsNo']);
                $originGoodsIconTableData   = [];
                foreach($originGoodsIconData as $originIconKey => $originIconValue) {
                    $originGoodsIconTableData['goodsIconCd'][]      = $originIconValue['goodsIconCd'];
                    $originGoodsIconTableData['goodsIconStartYmd']  = $originIconValue['goodsIconStartYmd'];
                    $originGoodsIconTableData['goodsIconEndYmd']    = $originIconValue['goodsIconEndYmd'];
                }
                $prevGoodsData['goodsIconCd']        = $originGoodsIconTableData['goodsIconCd'];
                $prevGoodsData['goodsIconStartYmd']  = $originGoodsIconTableData['goodsIconStartYmd'];
                $prevGoodsData['goodsIconEndYmd']    = $originGoodsIconTableData['goodsIconEndYmd'];
            }
            $this->db->strWhere = $tmpStrWhere;
            unset($tmpStrWhere);
        }

        //서로등록 이면서 선택상품 사용함이면
        if (/*$goodsData['relationSameFl'] == 's' && */
            $goodsData['relationFl'] == 'm') {
            $strSQL = ' SELECT goodsNo, relationGoodsNo, relationGoodsEach FROM ' . DB_GOODS . ' WHERE relationGoodsNo LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($arrBind, 's', $goodsData['goodsNo']);
            $res = $this->db->query_fetch($strSQL, $arrBind, false);
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

                //상품수정일 변경유무 추가
                if ($this->modDtUse == 'n') {
                    $this->db->setModDtUse(false);
                }

                //업데이트 처리
                $this->db->set_update_db(DB_GOODS, "relationGoodsNo = '" . implode(INT_DIVISION, $tmpRelatedGoodsList['goodsNo']) . "', relationGoodsEach = '" . implode(STR_DIVISION, $tmpRelatedGoodsList['each']) . "'", "goodsNo = '{$tmpRelatedGoodsList['original']}'");
                unset($tmpRelatedGoodsList);
            }
        }

        $tmp = explode(INT_DIVISION, $goodsData['relationGoodsNo']);
        foreach ($tmp as $k => $v) {
            $strSQL = ' SELECT relationGoodsNo, relationGoodsEach FROM ' . DB_GOODS . ' WHERE goodsNo = ?';
            $this->db->bind_param_push($arrBind, 's', $v);
            $res = $this->db->query_fetch($strSQL, $arrBind, false);
            unset($arrBind);

            $tmpRelatedGoodsList['goodsNo'] = array_filter(explode(INT_DIVISION, $res['relationGoodsNo']));
            $res['relationGoodsEach'] = str_pad('', count($tmpRelatedGoodsList['goodsNo']) * 3, 'y' . STR_DIVISION);
            $tmpRelatedGoodsList['each'] = array_filter(explode(STR_DIVISION, $res['relationGoodsEach']));
            if (!in_array($goodsData['goodsNo'], $tmpRelatedGoodsList['goodsNo'])) {
                //사용함인데 상품이 등록되어 있지 않을 경우
                $tmpRelatedGoodsList['goodsNo'][] = $goodsData['goodsNo'];
                $tmpRelatedGoodsList['each'][] = 'y';
            }

            //상품수정일 변경유무 추가
            if ($this->modDtUse == 'n') {
                $this->db->setModDtUse(false);
            }

            //업데이트 처리
            $this->db->set_update_db(DB_GOODS, "relationGoodsNo = '" . implode(INT_DIVISION, $tmpRelatedGoodsList['goodsNo']) . "', relationGoodsEach = '" . implode(STR_DIVISION, $tmpRelatedGoodsList['each']) . "'", "goodsNo = '{$v}' AND relationFl='m'");
            unset($tmpRelatedGoodsList);
        }

        $goodsData['applyFl'] = 'y';
        if (($goodsInsert == false && $this->hasScmPermissionModifyAndIsProvider) || ($goodsInsert === true && $this->hasScmPermissionInsertAndIsProvider)) {
            $goodsData['applyFl'] = 'a';
            $goodsData['applyDt'] = date('Y-m-d H:i:s');
            $goodsData['applyType'] = "U";
            if ($goodsInsert) {
                $goodsData['applyType'] = "R";
            }
        }

        unset($goodsData['imagePath']); //이미지 경로는 수정안댐
        if ($goodsData['imageStorage'] == 'url') {
            echo '<td>종류 : ' . $goodsData['imageStorage'] . '</td>' . chr(10);
        } else if ($goodsData['imageStorage'] == 'local') {
            if ($goodsInsert === true) {
                $goodsData['imagePath'] = DIR_GOODS_IMAGE . $goodsData['goodsNo'] . '/';
                $imagePath = $this->userFilPath->data('goods' . DS . $goodsData['imagePath']);
                if (!file_exists($imagePath)) {
                    @mkdir($imagePath, 0707, true);
                    @chmod($imagePath, 0707);
                }
                echo '<td>종류 : ' . $goodsData['imageStorage'] . ' , 경로 : ' . $this->userFilPath->data('goods' . DS . $goodsData['imagePath'])->www() . '</td>' . chr(10);
            } else {
                echo '<td>종류 : ' . $goodsData['imageStorage'] . '</td>' . chr(10);
            }
        } else {
            echo '<td>종류 : ' . $goodsData['imageStorage'] . ' , 경로 : ' . DIR_GOODS_IMAGE_FTP . $goodsData['imagePath'] . '</td>' . chr(10);
        }
        echo '</tr>' . chr(10);
        // 상품 정보 저장
        if ($goodsInsert) {
            $goodsData['goodsBenefitSetFl'] = 'n'; //상품엑셀 업로드시에는 개별설정으로 등록
            $this->goodsBenefit->delGoodsLink($goodsData['goodsNo']); //기존 상품혜택링크 삭제
            if (!$goodsData['commission']) {
                if (!isset($this->scmCommissions[$goodsData['scmNo']])) {
                    $this->scmCommissions[$goodsData['scmNo']] = $this->scmAdmin->getScm($goodsData['scmNo'])['scmCommission'];
                }
                $goodsData['commission'] = $this->scmCommissions[$goodsData['scmNo']];
            }
            $arrBind = $this->db->get_binding($this->tableGoods, $goodsData, 'update');
        } else {
            $arrExclude = [];
            // 운영자 기능권한 처리
            if (!$this->hasGoodsNmAuth) {
                $arrExclude[] = 'goodsNmFl';
                $arrExclude[] = 'goodsNm';
                $arrExclude[] = 'goodsNmMain';
                $arrExclude[] = 'goodsNmList';
                $arrExclude[] = 'goodsNmDetail';
                $arrExclude[] = 'goodsNmPartner';
                $arrExclude[] = 'goodsBenefitSetFl';
            }
            if (!$this->hasGoodsPriceAuth) {
                $arrExclude[] = 'goodsPrice';
            }
            if (!$this->hasGoodsStockAuth) {
                $arrExclude[] = 'totalStock';
            }
            // 상품명 없는 경우 기존 상품명 그대로 사용
            if ($goodsNmEach === false && in_array('goodsNm', $arrExclude) == false) {
                $arrExclude[] = 'goodsNm';
            }
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableGoods', array_keys($goodsData)), $goodsData, 'update', null, $arrExclude);

            // 상품 재고 수정 권한이 없으면 옵션 재고량 업데이트 제외
            if (!$this->hasGoodsStockAuth && count($optionData) > 0) {
                foreach ($optionData as $key => $value) {
                    unset($optionData[$key]['stockCnt']);
                }
            }
        }
        $this->db->bind_param_push($arrBind['bind'], 'i', $goodsData['goodsNo']);

        //상품수정일 변경유무 추가
        if ($this->modDtUse == 'n') {
            $this->db->setModDtUse(false);
        }

        $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind'], false, false);
        unset($arrBind, $arrExclude);

        //수정일 업데이트
        if ($this->modDtUse == 'y') {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $goodsData['goodsNo']);
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, 'modDt = now()', 'goodsNo = ?', $arrBind);
            unset($arrBind);
        }

        // 글로벌저장
        if (count($globalData) > 0) {
            $this->setGoodsDataWrapper(array_values($globalData), $goodsData['goodsNo'], $goodsInsert, 'tableGoodsGlobal', DB_GOODS_GLOBAL);
        }
        // 카테고리 링크 정보 저장
        if (count($linkData) > 0) {
            $linkData = $this->setGoodsLinkCategoryWrapper($linkData, $goodsData['goodsNo'], $goodsInsert);
            $this->setGoodsDataWrapper($linkData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsLinkCategory', DB_GOODS_LINK_CATEGORY, 'cateCd ASC');
        }
        // 브랜드 링크 정보 저장
        if (count($brandData) > 0) {
            $brandData = $this->setGoodsLinkBrandWrapper($brandData, $goodsData['goodsNo'], $goodsInsert);
            $this->setGoodsDataWrapper($brandData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsLinkBrand', DB_GOODS_LINK_BRAND, 'cateCd ASC');
        }
        // 상품 추가 정보 저장
        if (count($infoData) > 0) {
            $this->setGoodsDataWrapper($infoData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsAddInfo', DB_GOODS_ADD_INFO, 'sno ASC');
        }
        // 추가 노출 정보 저장
        if (count($iconData) > 0) {
            $this->setGoodsDataWrapper($iconData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsOptionIcon', DB_GOODS_OPTION_ICON, 'optionNo ASC, sno ASC');
        }
        // 옵션 저장
        if (count($optionData) > 0) {
            $this->setGoodsDataWrapper($optionData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsOption', DB_GOODS_OPTION, 'optionNo ASC');
        }
        // 텍스트 옵션 저장
        if (count($textData) > 0) {
            $this->setGoodsDataWrapper($textData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsOptionText', DB_GOODS_OPTION_TEXT, 'sno ASC');
        }
        //페이스북 제품 피드 저장
        if (count($fbData) > 0) {
            $this->facebookAd->setFacebookGoodsFeedData($goodsData['goodsNo'], $fbData['fbVn'], $fbData['fbImageName']);
        }

        if ($goodsData['seoTagFl'] == 'y') {
            if ($goodsInsert === false) {
                $seoTagData['sno'] = $this->seoTag->getSeoNo('goods', $goodsData); //update일 경우 기존 sno 가져옴.
            }
            $goodsSeoData['goodsNo'] = $seoTagData['pageCode'] = $goodsData['goodsNo'];
            $goodsSeoData['seoTagSno'] = $arrData['seoTagSno'] = $this->seoTag->saveSeoTagEach('goods', $seoTagData);

            //update seoTagSno(goods)
            $arrBind = $this->db->get_binding($this->tableGoods, $goodsSeoData, 'update', array_keys($goodsSeoData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $goodsData['goodsNo']);
            $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
        }
        unset($seoTagData, $goodsSeoData);

        // 이미지 저장
        if (count($imageData) > 0) {
            if ($goodsInsert === false) {
                foreach ($imageData as $k => $v) {
                    $strSQL = 'SELECT imageSize,imageHeightSize,imageRealSize FROM ' . DB_GOODS_IMAGE . ' WHERE goodsNo="' . $goodsData['goodsNo'] . '" AND imageKind = "' . $v['imageKind'] . '" AND imageNo = "' . $v['imageNo'] . '"';
                    $imageSno = $this->db->query_fetch($strSQL, null, false);
                    //엑셀 이미지 데이터 누락 없이 입력하기 위한 설정.
                    $imageSnoArray = [
                        'imageSize'       => 0,
                        'imageHeightSize' => 0,
                        'imageRealSize'   => '',
                    ];
                    if (!$imageSno) {
                        $imageData[$k] = array_merge($imageSnoArray, $imageData[$k]);
                    }
                    $imageData[$k] = array_merge($imageSno, $imageData[$k]);
                }
            }
            $this->setGoodsDataWrapper($imageData, $goodsData['goodsNo'], $goodsInsert, 'tableGoodsImage', DB_GOODS_IMAGE, 'imageKind , imageNo');
        }
        //네이버,다음ep 업데이트 관련 요약정보 미사용으로 제거
        $this->updateGoodsNo[] = $goodsData['goodsNo'];

        //상품 아이콘 저장
        if ($goodsIconDataArr) {
            foreach ($goodsIconDataArr AS $icon_key => $icon_val) {
                if ($icon_key == 'goodsIconCdPeriod') {
                    $this->goodsAdmin->setGoodsIcon($icon_val, 'pe', $goodsData['goodsNo'], $goodsIconDataArr['goodsIconStartYmd'], $goodsIconDataArr['goodsIconEndYmd'], 0);
                }
                if ($icon_key == 'goodsIconCd') {
                    $this->goodsAdmin->setGoodsIcon($icon_val, 'un', $goodsData['goodsNo'], '0000-00-00', '0000-00-00', 0);
                }
            }
        }

        /**
         * 엑셀 수정 업로드 시, 로그 저장 - 2018.10.10 parkjs
         * 변동사항 체크하지 않고 바로 저장함.
         **/
        if ($goodsInsert === false) { //수정일 경우
            //mode == 'excel'일 경우, 상품/추가정보/카테고리/이미지 값 저장.
            $prevLogData = [
                'goods'     => $prevGoodsData,
                'addInfo'   => $prevAddInfoData,
                'category'  => $prevCategoryData,
                'image'     => $prevImageData
            ];
            $updateLogData = [
                'goods'     => empty($goodsData) ? '' : $goodsData,
                'addInfo'   => empty($infoData) ? '' : $infoData,
                'category'  => empty($linkData) ? '' : $linkData,
                'image'     => empty($imageData) ? '' : $imageData
            ];
            self::setExcelGoodsLog($goodsData['goodsNo'], $prevLogData, $updateLogData, 'excel');

            //mode == 'excelOption'일 경우, 선택/텍스트 옵션 값 저장.
            $prevLogOptionData = [
                'option'       => $prevOptionData,
                'optionText'   => $prevOptionTextData,
            ];
            $updateLogOptionData = [
                'option'        => empty($optionData) ? '' : $optionData,
                'optionText'    => empty($textData) ? '' : $textData,
            ];
            self::setExcelGoodsLog($goodsData['goodsNo'], $prevLogOptionData, $updateLogOptionData, 'excelOption');
        }

        return true;
    }

    /**
     * 상품 엑셀 수정(업로드) 시 로그 저장 - 2018.10.10 parkjs
     * @param $goodsNo      상품 번호
     * @param $prevData     이전 데이터
     * @param $uploadData   업데이트 데이터
     * @param $mode         로그 종류
     */
    private function setExcelGoodsLog($goodsNo, $prevData, $updateData, $mode = 'excel')
    {
        $session = \App::getInstance('session');
        $logData['mode']        = $mode;
        $logData['goodsNo']     = $goodsNo;
        $logData['managerId']   = (string) $session->get('manager.managerId');
        $logData['managerNo']   = $session->get('manager.sno');
        $logData['prevData']    = json_encode($prevData, JSON_UNESCAPED_UNICODE);
        $logData['updateData']  = json_encode($updateData, JSON_UNESCAPED_UNICODE);

        //공급사이면서 자동승인이 아닌경우 상품 승인신청 처리
        if ($session->get('manager.isProvider') && $session->get('manager.scmPermissionModify') == 'c') {
            $logData['applyFl'] = 'a';
        } else {
            $logData['applyFl'] = 'y';
        }

        $logBind = $this->db->get_binding(DBTableField::tableLogGoods(), $logData, 'insert');
        $this->db->set_insert_db(DB_LOG_GOODS, $logBind['param'], $logBind['bind'], 'y');
        return true;
    }

    /**
     * processGoods
     *
     * @param $goodsData
     * @param $dbKey
     */
    protected function processGoods(&$goodsData, $dbKey)
    {
        $goodsData['goodsNmFl'] = 'd';
        // 확장 상품명 체크
        if (empty($goodsData['goodsNmMain']) === false || empty($goodsData['goodsNmList']) === false || empty($goodsData['goodsNmDetail']) === false || empty($goodsData['goodsNmPartner']) === false) {
            $goodsData['goodsNmFl'] = 'e';
        }
        switch ($dbKey) {
            case 'kcmarkFl':
            case 'kcmarkDivFl':
            case 'kcmarkNo':
            case 'kcmarkDt':
                if($goodsData['kcmarkDivFl'] == "kcCd04" || $goodsData['kcmarkDivFl'] == "kcCd05" || $goodsData['kcmarkDivFl'] == "kcCd06"){
                    $this->kcmarkInfo[$dbKey] = $goodsData[$dbKey];
                }else {
                    $goodsData['kcmarkDt'] = '';
                    $this->kcmarkInfo[$dbKey] = $goodsData[$dbKey];
                }
                $goodsData['kcmarkInfo'] = json_encode($this->kcmarkInfo);
                break;
            case 'goodsMustInfo':
                // 상품필수정보
                $this->convertGoodsMustInfo($goodsData, $dbKey);
                break;
            case 'relationGoodsDate':
                // 필수 상품 날짜 정보
                $this->convertRelationGoodsDate($goodsData, $dbKey);
                break;
            case 'addGoods':
                $this->convertAddGoods($goodsData, $dbKey);
                break;
            case 'detailInfoDelivery':
            case 'detailInfoAS':
            case 'detailInfoRefund':
            case 'detailInfoExchange':
                $info = $goodsData[$dbKey];
                if (empty($info) === false) {
                    $goodsData[$dbKey . 'DirectInput'] = ($goodsData[$dbKey . 'Fl'] == 'direct') ? $info : '';
                    $goodsData[$dbKey] = ($goodsData[$dbKey . 'Fl'] == 'selection') ? $info : '';
                }
                break;
            case 'hscode':
                $this->convertHsCode($goodsData, $dbKey);
                break;
            case 'salesUnit':
                //묶음주문단위 최소수량 설정
                if (empty($goodsData[$dbKey]) === true || (int) $goodsData[$dbKey] < 1) {
                    $goodsData[$dbKey] = 1;
                }
                break;
            case 'goodsNm':
                // 상품명 NFC로 변경
                $goodsData[$dbKey] = StringUtils::convertStringToNFC($goodsData[$dbKey]);
                break;
            case 'goodsNmMain':
            case 'goodsNmList':
            case 'goodsNmDetail':
            case 'goodsNmPartner':
                // 상품명 NFC로 변경
                if ($goodsData['goodsNmFl'] == 'e' && empty($goodsData[$dbKey]) === false) {
                    $goodsData[$dbKey] = StringUtils::convertStringToNFC($goodsData[$dbKey]);
                }
                break;
            case 'mileageGroupInfo':
            case 'mileageGoods':
            case 'mileageGoodsUnit':
                // 상품 등록 시, 마일리지 설정 지급방법 선택이 개별설정이면서 대상이 특정 회원등급인 경우
                if($goodsData['mileageFl'] == 'g' && $goodsData['mileageGroup'] == 'group'){
                    // mileageGroupMemberInfo 컬럼의 groupSno, mileageGoods, mileageGoodsUnit 값 세팅
                    $arrGroupSno = explode(INT_DIVISION, $goodsData['mileageGroupInfo']);
                    $arrMileageGoods = explode(",", str_replace(array("\r\n","\r","\n"), ",", $goodsData['mileageGoods']));
                    $arrMileageUnit = explode(",", str_replace(array("\r\n","\r","\n"), ",", $goodsData['mileageGoodsUnit']));

                    $arr['groupSno'] = $arrGroupSno;
                    $arr['mileageGoods'] = $arrMileageGoods;
                    $arr['mileageGoodsUnit'] = $arrMileageUnit;
                    $goodsData['mileageGroupMemberInfo'] = str_replace('\'', '', json_encode($arr, JSON_UNESCAPED_UNICODE));
                }
                break;
        }
    }

    /**
     * 상품필수정보를 배열화 시킴
     *
     * @param array $goodsData
     * @param       $dbKey
     */
    protected function convertGoodsMustInfo(&$goodsData, $dbKey)
    {
        if (empty($goodsData[$dbKey]) === false) {
            $infoDataArr = explode(chr(10), StringUtils::strIsSet($goodsData[$dbKey]));
            foreach ($infoDataArr as $iKey => $iVal) {
                $tmpDataArr = explode(STR_DIVISION, $iVal);
                foreach ($tmpDataArr as $tKey => $tVal) {
                    if ($tKey % 2 == 0 || $tKey == 0) {
                        $addMustInfo['infoTitle'][$iKey][] = $tVal;
                    }
                    if ($tKey % 2 == 1) {
                        $addMustInfo['infoValue'][$iKey][] = $tVal;
                    }
                }
            }// 배열 정보를 xml화 하기 위한 준비
            $tmpGoodsMustInfo = [];
            $ii = 0;
            if (isset($addMustInfo)) {
                foreach ($addMustInfo['infoTitle'] as $mKey => $mVal) {
                    foreach ($mVal as $iKey => $iVal) {
                        $tmpGoodsMustInfo['line' . $ii]['step' . $iKey]['infoTitle'] = $iVal;
                        $tmpGoodsMustInfo['line' . $ii]['step' . $iKey]['infoValue'] = $addMustInfo['infoValue'][$mKey][$iKey];
                    }
                    $ii++;
                }
            }// xml 처리
            $goodsData[$dbKey] = json_encode(StringUtils::htmlSpecialChars($tmpGoodsMustInfo), JSON_UNESCAPED_UNICODE);
            unset($infoDataArr, $addMustInfo, $tmpDataArr, $tmpGoodsMustInfo);
        }
    }

    /**
     * 필수 상품 날짜 정보 배열화
     *
     * @param $goodsData
     * @param $dbKey
     */
    protected function convertRelationGoodsDate(&$goodsData, $dbKey)
    {
        if (empty($goodsData[$dbKey]) === false) {
            // 엑셀 정보를 배열화 시킴
            $infoDataArr = explode(chr(10), StringUtils::strIsSet($goodsData[$dbKey]));
            foreach ($infoDataArr as $iKey => $iVal) {
                if ($iVal) {
                    $tmpDataArr = explode(STR_DIVISION, $iVal);
                    $tmpKey = $tmpDataArr[0];
                    $tmpRelationGoodsDate[$tmpKey]['startYmd'] = $tmpDataArr[1];
                    $tmpRelationGoodsDate[$tmpKey]['endYmd'] = $tmpDataArr[2];
                    unset($tmpDataArr[0]);
                }
            }
            if (isset($tmpRelationGoodsDate)) {
                // xml 처리
                $goodsData[$dbKey] = json_encode(StringUtils::htmlSpecialChars($tmpRelationGoodsDate), JSON_UNESCAPED_UNICODE);
            }
            unset($infoDataArr, $relationGoodsDate, $tmpDataArr, $tmpGoodsMustInfo);
        }
    }

    /**
     * 추가상품정보 배열화
     *
     * @param $goodsData
     * @param $dbKey
     */
    protected function convertAddGoods(&$goodsData, $dbKey)
    {
        if (empty($goodsData[$dbKey]) === false) {
            // 엑셀 정보를 배열화 시킴
            $infoDataArr = explode(chr(10), StringUtils::strIsSet($goodsData[$dbKey]));
            foreach ($infoDataArr as $iKey => $iVal) {
                $tmp = explode(STR_DIVISION, $iVal);
                if ($tmp[0]) {
                    $addGoods[$iKey]['title'] = $tmp[0];
                    $addGoods[$iKey]['mustFl'] = $tmp[1];
                    $addGoods[$iKey]['addGoods'] = explode(INT_DIVISION, $tmp[2]);;
                }
            }
            if (isset($addGoods)) {
                $goodsData[$dbKey] = json_encode(StringUtils::htmlSpecialChars($addGoods), JSON_UNESCAPED_UNICODE);
            }
            unset($addGoods, $tmp, $infoDataArr);
        }
    }

    /**
     * 해외상품관련 코드 배열화
     *
     * @param $goodsData
     * @param $dbKey
     */
    protected function convertHsCode(&$goodsData, $dbKey)
    {
        if (empty($goodsData[$dbKey]) === false) {
            $infoDataArr = explode(chr(10), StringUtils::strIsSet($goodsData[$dbKey]));
            foreach ($infoDataArr as $iKey => $iVal) {
                $tmp = explode(STR_DIVISION, $iVal);
                if ($tmp[0]) {
                    $hscode[$tmp[0]] = $tmp[1];
                }
            }
            if (isset($hscode)) {
                $goodsData[$dbKey] = json_encode(StringUtils::htmlSpecialChars($hscode), JSON_UNESCAPED_UNICODE);
            }
            unset($hscode, $tmp, $infoDataArr);
        }
    }

    /**
     * processLink
     *
     * @param $goodsData
     * @param $dbKey
     * @param $linkData
     * @param $value
     */
    protected function processLink(&$goodsData, $dbKey, &$linkData, &$value)
    {
        $linkDataArr = array_filter(array_unique(explode(chr(10), StringUtils::strIsSet($value))));
        StringUtils::trimValue($linkDataArr); // 배열을 trim 처리
        if (strlen($linkDataArr[0]) != 0 && (strlen($linkDataArr[0]) % DEFAULT_LENGTH_CATE) == 0 && (strlen($linkDataArr[0]) <= DEFAULT_LENGTH_CATE * DEFAULT_DEPTH_CATE)) {
            // 상위카테고리 자동등록 설정 사용함
            if ($this->configDisplayAutoCategory()['autoUse'] == 'y') {
                $parentCateCdArr = [];
                $addParentKey = count($linkDataArr);
                foreach ($linkDataArr as $key => $val) {
                    $tmpArr = explode(STR_DIVISION, $val);
                    if ($key == 0) {
                        $goodsData[$dbKey] = $linkDataArr[$key];
                    }
                    // 상위 카테고리 존재 여부 추출
                    $categoryLength = strlen($tmpArr[0]) / 3;
                    if ($categoryLength > 1) {
                        // 엑셀 입력된 하위 카테고리 등록
                        $linkData[$key] = $tmpArr[0];
                        for ($cateNum = 1; $cateNum < $categoryLength; $cateNum++) {
                            // 상위 카테고리 코드
                            $parentCateCd = substr($tmpArr[0], 0, $cateNum * 3);
                            if (in_array($parentCateCd, $parentCateCdArr) === false) {
                                $parentCateCdArr[] = $parentCateCd;
                                // 기존 엑셀 입력 값에 상위 카테고리가 존재하지 않을 경우 추가
                                if (in_array($parentCateCd, $linkDataArr) == false) {
                                    $linkData[$key + $addParentKey] = $parentCateCd;
                                    // 증가 갯수 마지막 -1까지만
                                    if ($cateNum != $categoryLength - 1) {
                                        $addParentKey++;
                                    }
                                }
                            }
                        }
                    } else {
                        $linkData[$key] = $tmpArr[0];
                    }
                }
                ksort($linkData); // key 기준 정렬
                unset($parentCateCdArr);
            } else {
                foreach ($linkDataArr as $key => $val) {
                    $tmpArr = explode(STR_DIVISION, $val);
                    if ($key == 0) {
                        $goodsData[$dbKey] = $linkDataArr[$key];
                    }
                    $linkData[$key] = $tmpArr[0];
                }
            }
        }
        unset($linkDataArr);
    }

    /**
     * configDisplayAutoCategory
     *
     * @return array
     */
    protected function configDisplayAutoCategory()
    {
        if (count($this->configDisplayAutoCategory) < 1) {
            $this->configDisplayAutoCategory = ComponentUtils::getPolicy('display.auto_category');
        }

        return $this->configDisplayAutoCategory;
    }

    /**
     * processImage
     *
     * @param $imageData
     * @param $value
     */
    protected function processImage(&$imageData, &$value)
    {
        $imageDataArr = explode(chr(10), StringUtils::strIsSet($value)); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
        if (!empty($imageDataArr[0])) {
            $tmpData = $tmpUrlData = [];
            foreach ($imageDataArr as $key => $val) {
                $tmpArr = explode(STR_DIVISION, $val);
                foreach ($tmpArr as $tKey => $tVal) {
                    if ($tKey == 0) {
                        continue;
                    }
                    if ((strpos(strtolower($tVal), 'http://') !== false || strpos(strtolower($tVal), 'https://') !== false)) {
                        $tmpUrlData[$tmpArr[0]][] = $tVal;
                    } else {
                        $tmpData[$tmpArr[0]][] = $tVal;
                    }
                }
            }
            unset($tmpArr);
            $tmpData = array_merge_recursive($tmpData, $tmpUrlData);
            if ($tmpData) {
                $tmpKey = 0;
                foreach ($tmpData as $imageKind => $image) {
                    foreach ($image as $key => $val) {
                        if ($imageKind != 'detail' && $imageKind != 'magnify' && $key > 0) { //확대,상세 이미지 제외하고는 0번째만 올림
                            continue;
                        }
                        $imageData[$imageKind . $key]['imageKind'] = $imageKind;
                        $imageData[$imageKind . $key]['imageNo'] = $key;
                        $imageData[$imageKind . $key]['imageName'] = $val;
                        $tmpKey++;
                    }
                }
                ksort($imageData);
                $imageData = array_values($imageData);
            }
        }
        unset($imageDataArr);
    }
}
