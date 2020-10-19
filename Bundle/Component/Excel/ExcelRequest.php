<?php
/**
 * 상품노출형태 관리
 * @author    atomyang
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Excel;

use Component\Database\DBTableField;
use Component\Member\MemberDAO;
use Encryptor;
use Framework\StaticProxy\Proxy\FileHandler;
use Framework\Utility\ComponentUtils;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\NumberUtils;
use LogHandler;
use Request;
use Session;
use UserFilePath;
use Exception;
use Globals;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Manager;
use Component\Validator\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelRequest
{


    const EXCEL_PAGE_NUM = '100';

    protected $db;
    protected $gGlobal;

    /**
     * @var array arrBind
     */
    private $arrBind = [];

    /**
     * @var array 조건
     */
    private $arrWhere = [];

    /**
     * @var array 체크
     */
    private $checked = [];

    /**
     * @var array 검색
     */
    private $search = [];

    private $excelHeader;

    private $excelFooter;

    private $excelPageNum;

    public $fileConfig;

    public $mileageGiveInfo = [];

    public $depositInfo = [];

    public function __construct()
    {
        ob_end_flush();
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->excelHeader = '<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">' . chr(10);
        $this->excelHeader .= '<head>' . chr(10);
        $this->excelHeader .= '<title>Excel Down</title>' . chr(10);
        $this->excelHeader .= '<meta http-equiv="Content-Type" content="text/html; charset=' . SET_CHARSET . '" />' . chr(10);
        $this->excelHeader .= '<style>' . chr(10);
        $this->excelHeader .= 'br{mso-data-placement:same-cell;}' . chr(10);
        //        $this->excelHeader .= 'td{mso-number-format:"\@";} ' . chr(10);
        $this->excelHeader .= '.xl31{mso-number-format:"0_\)\;\\\(0\\\)";}' . chr(10);
        $this->excelHeader .= '.xl24{mso-number-format:"\@";} ' . chr(10);
        $this->excelHeader .= '.title{font-weight:bold; background-color:#F6F6F6; text-align:center;} ' . chr(10);
        $this->excelHeader .= '</style>' . chr(10);
        $this->excelHeader .= '</head>' . chr(10);
        $this->excelHeader .= '<body>' . chr(10);

        $this->excelFooter = '</body>' . chr(10);
        $this->excelFooter .= '</html>' . chr(10);

        // 마일리지 지급 정보
        $this->mileageGiveInfo = gd_mileage_give_info();

        //예치금정보
        $this->depositInfo = gd_policy('member.depositConfig');
        $this->gGlobal = Globals::get('gGlobal');
    }

    public function reserveExcelFile()
    {

        $sort = 'regDt desc';

        $this->arrWhere = [];

        $this->arrWhere[] = 'state = ?';
        $this->db->bind_param_push($this->arrBind, 's', 'n');


        // 현 페이지 결과
        $this->db->strField = " er.*";

        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_REQUEST . ' as er' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        foreach ($data as $key => $val) {
            $this->makeExcelFile($val);
        }

    }


    /**
     * saveInfoThemeConfig
     *
     * @param $arrData
     *
     * @throws Except
     */
    public function saveInfoExcelRequest($arrData)
    {
        // replyStatus 상태 문자열로 변경을 위함
        $arrReplyStatus = explode('&', $arrData['whereDetail']);
        foreach($arrReplyStatus as $key => $val){
            $newArrReplyStatus = explode('=', $val);
            if($newArrReplyStatus[0] == 'replyStatus'){
                if($newArrReplyStatus[1] == 0){
                    $newArrReplyStatus[1] = '-';
                }else if($newArrReplyStatus[1] == 1){
                    $newArrReplyStatus[1] = '접수';
                }else if($newArrReplyStatus[1] == 2){
                    $newArrReplyStatus[1] = '답변대기';
                }else if($newArrReplyStatus[1] == 3){
                    $newArrReplyStatus[1] = '답변완료';
                }
            }
            $reData[] = implode('=', $newArrReplyStatus);
        }

        // replyStatus 상태 문자열로 변경후 whereDetail 재정의
        $arrData['whereDetail'] = implode('&', $reData);

        $arrData['excelPageNum'] = (int) $arrData['excelPageNum'];
        gd_isset($arrData['goodsNameTagFl'], 'n');
        parse_str($arrData['whereDetail'], $arrData['whereDetail']);
        if ($arrData['whereFl'] == 'select') {
            $arrData['whereDetail']['searchWord'] = null;
        }
        $arrData['whereDetail']['goodsNameTagFl'] = $arrData['goodsNameTagFl'];

        $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
        $formData = $excelForm->getInfoExcelForm($arrData['formSno']);

        if ($formData['menu'] === 'orderDraft') {
            $countPersonal = $excelForm->countPersonalField($formData['excelField']);
            if ($countPersonal >= 3 && $arrData['passwordFl'] !== 'y') {
                echo "<script> parent.hide_process(); </script>";
                echo '<script> parent.setPersonalField(\''.$countPersonal.'\');</script>';
                echo "<script> parent.dialog_alert('비밀번호를 입력해주세요.'); </script>";
                exit;
            }
        }

        if ($arrData['whereFl'] == 'total') {
            if ($formData['menu'] == 'board') {
                $tmp = $arrData['whereDetail'];
                unset($arrData['whereDetail']);
                $arrData['whereDetail']['bdId'] = $tmp['bdId'];

            } else if ($formData['menu'] == 'promotion' && $formData['location'] == 'coupon_offline_list') {
                $tmp = $arrData['whereDetail'];
                unset($arrData['whereDetail']);
                $arrData['whereDetail']['couponNo'] = $tmp['couponNo'];
            } elseif ($formData['menu'] == 'plusreview') {
                $arrData['whereDetail']['reviewType'] = $arrData['whereFl'];
            } else if ($formData['menu'] == 'promotion' && $formData['location'] == 'coupon_manage') {
                $arrData['couponNo'] = $arrData['whereDetail']['couponNo'];
            } else if ($formData['menu'] == 'promotion' && $formData['location'] == 'coupon_offline_manage') {
                $arrData['couponNo'] = $arrData['whereDetail']['couponNo'];
            }else {
                unset($arrData['whereDetail']);
            }
        }

        $arrData['whereDetail']['optionCountFl'] = gd_isset($arrData['optionCountFl'], '');
        $arrData['whereCondition'] = serialize($arrData['whereDetail']);
        $arrData['managerNo'] = Session::get('manager.sno');
        $arrData['scmNo'] = Session::get('manager.scmNo');


        if ($formData['menu'] == 'goods' && $formData['location'] == 'goods_list') {
            if ($formData['location'] == 'gift_list' || $formData['location'] == 'gift_present_list' || $formData['location'] == 'goods_list_delete' || $formData['location'] == 'goods_must_info_list' || $formData['location'] == 'add_goods_list' || $formData['location'] == 'common_content_list') {
                // 사은품관리/사은품 지급조건 관리/삭제상품 관리/상품 필수정보 관리/추가상품 관리 내역은 상품정보 엑셀다운로드와 상관없이 다운되게 처리
            } else {
                if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.goodsExcelDown') != 'y') {
                    throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
                }
            }
        }
        if ($formData['menu'] == 'order') {
            if ($formData['location'] == 'order_list_user_exchange' || $formData['location'] == 'order_list_user_return' || $formData['location'] == 'order_list_user_refund') {
                // 고객 교환/반품/환불 신청 내역은 주문정보 엑셀다운로드와 상관없이 다운되게 처리
            } else {
                if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderExcelDown') != 'y') {
                    throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
                }
            }
        }
        if ($formData['menu'] == 'orderDraft') {
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderExcelDown') != 'y') {
                throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
            }
        }
        if ($formData['menu'] == 'member') {
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.memberExcelDown') != 'y') {
                throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
            }
        }

        $this->excelPageNum = gd_isset($arrData['excelPageNum']);

        if(!is_dir(UserFilePath::data('excel')->getPathName())){
            mkdir(UserFilePath::data('excel')->getPathName(),0707);
        }

        if ($this->makeExcelFile($arrData)) {
            $arrBind = $this->db->get_binding(DBTableField::tableExcelRequest(), $arrData, 'insert');
            $this->db->set_insert_db(DB_EXCEL_REQUEST, $arrBind['param'], $arrBind['bind'], 'y');
            $arrData['requestSno'] = $this->db->insert_id();
            unset($arrBind);

            return true;
        } else {
            return false;
        }

        //특정 갯수 이하인 경우 바로 생성
        /*
        if($arrData[ $arrData['whereFl'].'Count'] <= self::EXCEL_PAGE_NUM ) {
            $this->makeExcelFile($arrData);
            return $this->fileConfig;
        } else {
            return true;
        }
        */

    }

    public function makeExcelFile(&$arrData)
    {
        $excleForm = \App::load('\\Component\\Excel\\ExcelForm');
        $formData = $excleForm->getInfoExcelForm($arrData['formSno']);
        $excelFieldName = $excleForm->setExcelForm($formData['menu'], $formData['location']);

        if ($arrData['menu'] === 'orderDraft') {
            $funcName = "getOrderList";
        } else {
            $funcName = "get" . ucfirst($formData['menu']) . str_replace("_", "", ucwords($formData['location'], "_"));
        }

        $this->fileConfig['menu'] = $formData['menu'];
        $this->fileConfig['location'] = $formData['location'];

        if(!is_dir(UserFilePath::data('excel',$formData['menu'])->getPathName())){
            mkdir(UserFilePath::data('excel',$formData['menu'])->getPathName(),0707);
        }

        if ($arrData['passwordFl'] == 'y') {
            // 특정문자 치환
            $replace = ['$', '"', '`'];
            foreach ($replace as $key => $val) {
                $arrData['password'] = str_replace($val, '\\' . $val, $arrData['password']);
            }
            $this->fileConfig['password'] = gd_isset($arrData['password']);
        } else {
            $this->fileConfig['password'] = "";
        }
        if ($formData['menu'] == 'order' || $arrData['menu'] === 'orderDraft') {
            if ($formData['location'] == 'tax_invoice_request' || $formData['location'] == 'tax_invoice_list') {
                $whereCondition = unserialize($arrData['whereCondition']);
                if ($arrData['excelPageNum'] > 0) $whereCondition['pageNum'] = $arrData['excelPageNum'];
                $getData = $this->$funcName($whereCondition, $formData['excelField'], $excelFieldName);
                unset($whereCondition);
            } else {
                $defaultField = $excleForm->setExcelForm($formData['menu'], $formData['location']);
                $getData = $this->getOrderList(unserialize($arrData['whereCondition']), $formData['excelField'], $defaultField, $excelFieldName);
            }
        } else if ($formData['menu'] == 'member') {
            $getData = $this->getMemberMemberList(unserialize($arrData['whereCondition']), $formData['excelField'], $excelFieldName);
        } else if($formData['menu'] == 'promotion' && $formData['location'] == 'coupon_manage'){
            $getData = $this->getPromotionCouponManage($arrData, $formData['excelField'], $excelFieldName);
        } else if($formData['menu'] == 'promotion' && $formData['location'] == 'coupon_offline_manage'){
            $getData = $this->getPromotionCouponOfflineManage($arrData, $formData['excelField'], $excelFieldName);
        } else {
            $getData = $this->$funcName(unserialize($arrData['whereCondition']), $formData['excelField'], $excelFieldName);
        }


        if ($getData) {
            if(\is_array($getData)) {
                foreach ($getData as $k => $v) {
                    $this->genarateFile($v);
                }
            } else {
                if (gd_isset($this->fileConfig['password'])) {
                    $fileList = $this->fileConfig['fileName'];
                    unset($this->fileConfig['fileName']);
                    foreach($fileList as $k => $v) {
                        $tmpFilePath = UserFilePath::data('excel', $this->fileConfig['menu'],  $v)->getRealPath();
                        $fileName=pathinfo($tmpFilePath)['filename'];

                        $zipFilePath = UserFilePath::data('excel', $this->fileConfig['menu'], $fileName . ".zip")->getRealPath();
                        exec('cd ' . pathinfo($tmpFilePath)['dirname'] . ' && zip -P ' . $this->fileConfig['password'] . ' -r "' . $zipFilePath . '" "' . $fileName . '.xls"');
                        $this->fileConfig['fileName'][] = $fileName . ".zip";
                        FileHandler::delete($tmpFilePath);
                    }
                }

            }

            $arrData['filePath'] = "excel" . DS . $formData['menu'];
            $arrData['fileName'] = implode(STR_DIVISION, $this->fileConfig['fileName']);

            $arrData['state'] = "y";
            $arrData['expiryDate'] = date("Y-m-d", strtotime("+7 day"));

            return $arrData;
        } else {
            return false;
        }
    }

    /*
     * 상품상세 공통정보 엑셀파일 생성
     */
    public function getGoodsCommonContentList($whereCondition, $excelField, $excelFieldName)
    {
        $commonContent = \App::load('\\Component\\Goods\\CommonContent');
        $excelField = explode(STR_DIVISION, $excelField);

        $data = $commonContent->getDataExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);
        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] ."</td>";
                    }

                    if ($excelValue == 'sno') {
                        $tmpData[] = "<td>" . ($key + 1) . "</td>";
                    } else {
                        $tmpData[] = "<td>" . $val[$excelValue] ."</td>";
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";

            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
     * 사은품 관리 엑셀파일 생성
     */
    public function getGoodsGiftList($whereCondition, $excelField, $excelFieldName)
    {

        $giftAdmin = \App::load('\\Component\\Gift\\GiftAdmin');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 검색 설정
        $data = $giftAdmin->getAdminListGiftExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    if ($excelValue == 'stock') {

                        if ($val['stockFl'] == 'n') {
                            $strStockFl = __('제한없음');
                        } else {
                            if ($val['stockCnt'] > 0) {
                                $strStockFl = number_format($val['stockCnt']);
                            } else {
                                $strStockFl = __('품절');
                            }
                        }

                        $tmpData[] = "<td>" . $strStockFl . "</td>";
                    } else {
                        $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";

            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
     * 사은품 증정관리 엑셀파일 생성
     */
    public function getGoodsGiftPresentList($whereCondition, $excelField, $excelFieldName)
    {

        $giftAdmin = \App::load('\\Component\\Gift\\GiftAdmin');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 검색 설정
        $data = $giftAdmin->getAdminListGiftPresentExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";

        $arrPresentText = [
            'a' => __('전체 상품'),
            'g' => __('특정 상품'),
            'c' => __('특정 카테고리'),
            'b' => __('특정 브랜드'),
            'e' => __('특정 이벤트'),
        ];
        $arrPresent = ['g' => 'goods', 'c' => 'category', 'b' => 'brand'];
        $arrExceptText = [
            'exceptGoodsNo' => '상품',
            'exceptCateCd'  => '카테고리',
            'exceptBrandCd' => '브랜드',
            'exceptEventCd' => '이벤트',
        ];
        $arrConditionText = ['a' => __('무조건'), 'p' => __('금액별'), 'c' => __('수량별'), 'l' => __('구매수량별')];

        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'presentState':

                            if ($val['presentPeriodFl'] == 'y') {
                                if ($val['periodStartYmd'] > date('Y-m-d')) {
                                    $statusStr = __('대기');
                                } else if ($val['periodEndYmd'] >= date('Y-m-d')) {
                                    $statusStr = __('진행중');
                                } else {
                                    $statusStr = __('종료');
                                }
                            } else {
                                $statusStr = __('제한없음');
                            }

                            $tmpData[] = "<td>" . $statusStr . "</td>";

                            break;
                        case 'periodYmd':
                            if ($val['presentPeriodFl'] == 'y') {
                                $periodYmd = $val['periodStartYmd'] . ' ~ ' . $val['periodEndYmd'];
                            } else {
                                $periodYmd = "";
                            }

                            $tmpData[] = "<td>" . $periodYmd . "</td>";

                            break;
                            break;
                        case 'presentFl':
                            $presentFl = $arrPresentText[$val['presentFl']];
                            $tmpData[] = "<td>" . $presentFl . "</td>";
                            break;
                        case 'presentKindCd':
                            if ($val['presentFl'] == 'a') {
                                $presentKindCd = [];
                            } else {
                                $_tmpTermsData = $giftAdmin->setGiftPresentTerms($val['presentFl'], $val['sno']);

                                if ($val['presentFl'] == 'g' && $_tmpTermsData) {
                                    $_tmpGoodsNm = [];
                                    foreach ($_tmpTermsData as $k => $v) {
                                        $_tmpGoodsNm[] = $v['goodsNm'];
                                    }
                                    $presentKindCd = $_tmpGoodsNm;

                                } else {
                                    $presentKindCd = $_tmpTermsData;
                                }
                            }

                            if ($presentKindCd) {
                                $tmpData[] = "<td>" . implode("<br>", $presentKindCd) . "</td>";
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            unset($presentKindCd);
                            unset($_tmpTermsData);

                            break;

                        case 'conditionFl':
                            $conditionFl = $arrConditionText[$val['conditionFl']];
                            $tmpData[] = "<td>" . $conditionFl . "</td>";
                            break;
                        case 'conditionInfo':
                            $_tmpTermsData = $giftAdmin->setGiftPresentTerms('gift', $val['sno']);
                            $_tmpTermsName = [];


                            foreach ($_tmpTermsData as $k1 => $v1) {
                                $textValue = '';
                                $textValue[] = number_format($v1['conditionStart']) . ' ~ ' . number_format($v1['conditionEnd']);
                                if (is_array($v1['multiGiftNo'])) {
                                    $tmpValue = [];
                                    foreach ($v1['multiGiftNo'] as $mKey => $mVal) {
                                        $tmpValue[] = $mVal['giftNm'];
                                    }
                                    $textValue[] = implode('', $tmpValue);
                                }

                                if ($v1['selectCnt'] == 0) {
                                    $textValue[] = sprintf('%s : %s', __('선택수량'), __('전체지급'));
                                } else {
                                    $textValue[] = sprintf('%s : ' . $v1['selectCnt'] . '%s', __('선택수량'), __('개씩 지급'));
                                }

                                $textValue[] = sprintf('%s : ' . $v1['giveCnt'] . '%s', __('지급수량'), __('개씩 지급'));

                                $_tmpTermsName[] = implode("/", $textValue);
                            }

                            $conditionInfo = implode("<br>", $_tmpTermsName);

                            $tmpData[] = "<td>" . $conditionInfo . "</td>";

                            unset($textValue);
                            unset($_tmpTermsName);
                            unset($_tmpTermsData);

                            break;
                        case 'exceptFl':

                            // 예외 조건

                            $exceptFl['exceptGoodsNo'] = $val['exceptGoodsNo'];
                            $exceptFl['exceptCateCd'] = $val['exceptCateCd'];
                            $exceptFl['exceptBrandCd'] = $val['exceptBrandCd'];
                            $_tmpData = [];
                            foreach ($exceptFl as $eKey => $eVal) {
                                if (!empty($eVal)) {
                                    $_tmpData[] = $arrExceptText[$eKey];
                                }
                            }

                            if ($_tmpData) $tmpData[] = "<td>" . implode("<br>", $_tmpData) . "</td>";
                            else $tmpData[] = "<td></td>";

                            unset($exceptFl);
                            unset($_tmpData);

                            break;
                        case 'exceptInfo':
                            if ($val['exceptGoodsNo']) {
                                $_tmpTermsData = $giftAdmin->setGiftPresentTerms('goods', $val['exceptGoodsNo']);

                                $_tmpGoodsNm = [];
                                foreach ($_tmpTermsData as $k => $v) {
                                    $_tmpGoodsNm[] = $v['goodsNm'];
                                }
                                if ($_tmpGoodsNm) {
                                    $exceptInfo[] = __("예외상품");
                                    $exceptInfo[] = implode("<br>", $_tmpGoodsNm);
                                };
                            }

                            if ($val['exceptCateCd']) {
                                $_tmpTermsData = $giftAdmin->setGiftPresentTerms('category', $val['exceptCateCd']);
                                if ($_tmpTermsData) {
                                    $exceptInfo[] = __("예외카테고리");
                                    $exceptInfo[] = implode("<br>", $_tmpTermsData);
                                };

                            }

                            if ($val['exceptBrandCd']) {
                                $_tmpTermsData = $giftAdmin->setGiftPresentTerms('brand', $val['exceptBrandCd']);
                                if ($_tmpTermsData) {
                                    $exceptInfo[] = __("예외브랜드");
                                    $exceptInfo[] = implode("<br>", $_tmpTermsData);
                                };
                            }
                            if ($exceptInfo) $tmpData[] = "<td>" . implode("<br>", $exceptInfo) . "</td>";
                            else $tmpData[] = "<td></td>";

                            unset($exceptInfo);
                            unset($_tmpTermsData);

                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";

            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }


    /*
     * 상품 관리 엑셀파일 생성
     */
    public function getGoodsGoodsList($whereCondition, $excelField, $excelFieldName)
    {
        $goodsAdmin = \App::load('\\Component\\Goods\\GoodsAdmin');

        $excelField = explode(STR_DIVISION, $excelField);
        $isGlobal = false;
        foreach ($excelField as $k => $v) {
            if (strpos($v, 'global_') !== false) {
                $isGlobal = true;
            }
        }

        $goodsData = $goodsAdmin->getAdminListGoodsExcel($whereCondition,0,$this->excelPageNum);

        //튜닝한 업체를 위해 데이터 맞춤
        if(empty($goodsData['totalCount']) === true && empty($goodsData['goodsList']) === true) {
            $isGenerator = false;
            $totalNum = count($goodsData);
            if ($this->excelPageNum) $goodsList= array_chunk($goodsData, $this->excelPageNum, true);
            else $goodsList = array_chunk($goodsData, $totalNum, true);
        } else {
            $isGenerator = true;
            $totalNum = $goodsData['totalCount'];
            $goodsList = $goodsData['goodsList'];
        }
        unset($goodsData);

        if ($this->excelPageNum >= $totalNum) $pageNum = 0;
        else $pageNum = ceil($totalNum / $this->excelPageNum) - 1;


        $arrTag = [
            'goodsNm',
            'goodsNmMain',
            'goodsNmList',
            'goodsNmDetail',
            'shortDescription',
            'goodsDescription',
            'goodsDescriptionMobile',
        ];

        $goodsStateList = $goodsAdmin->getGoodsStateList();
        $goodsImportType = $goodsAdmin->getGoodsImportType();
        $goodsSellType = $goodsAdmin->getGoodsSellType();
        $goodsAgeType = $goodsAdmin->getGoodsAgeType();
        $goodsGenderType = $goodsAdmin->getGoodsGenderType();
        $goodsColorList = $goodsAdmin->getGoodsColorList(true);
        $purchaseArr = $scmArr = $brandArr = [];

        $seoTag = \App::load('\\Component\\Policy\\SeoTag');

        for ($i = 0; $i <= $pageNum; $i++) {

            $fileName = $this->fileConfig['location'] . array_sum(explode(' ', microtime()));
            $tmpFilePath = UserFilePath::data('excel', $this->fileConfig['menu'],  $fileName . ".xls")->getRealPath();

            $fh = fopen($tmpFilePath, 'a+');
            fwrite($fh, $this->excelHeader."<table border='1'>");

            if($isGenerator) {
                if($i == '0') {
                    $data = $goodsList;
                }  else {
                    $data = $goodsAdmin->getAdminListGoodsExcel($whereCondition,$i,$this->excelPageNum)['goodsList'];
                }
            } else {
                $data = $goodsList[$i];
            }

            foreach($data as $key => $val) {
                $progress = round((100 / ($totalNum- 1)) * ($key+($i*$this->excelPageNum)));
                if ($key % round($this->excelPageNum*0.5) == 0  || $progress == '100') {
                    echo "<script> parent.progressExcel('" .gd_isset($progress,0) . "'); </script>";
                }

                if ($isGlobal) {
                    $globalData = $goodsAdmin->getDataGoodsGlobal($val['goodsNo']);
                    if ($globalData) {
                        $globalField = array_keys($globalData[0]);
                        unset($globalField[0]); //mallSno삭제
                        foreach ($globalData as $globalKey => $globalValue) {
                            foreach ($globalField as $globalKey1 => $globalValue1) {
                                $val['global_' . $globalValue['mallSno'] . '_' . $globalValue1] = $globalValue[$globalValue1];
                            }
                        }
                    }
                }

                if($val['seoTagFl'] =='y' && $val['seoTagSno']) {
                    $val['seoTag']['data'] = $seoTag->getSeoTagData($val['seoTagSno'], null, false, ['path' => 'goods/goods_view.php', pageCode => $val['goodsNo']]);
                }

                $kcmarkInfoArr = [];
                if (empty($val['kcmarkInfo']) === false) {
                    $kcmarkInfoArr = json_decode($val['kcmarkInfo'], true);
                    if ($kcmarkInfoArr['kcmarkFl'] == 'n') {
                        $kcmarkInfoArr['kcmarkNo'] = $kcmarkInfoArr['kcmarkDivFl'] = '';
                    }
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name']  . "</td>";
                    }
                    if (in_array($excelValue, $arrTag)) {
                        $val[$excelValue] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($val[$excelValue]));
                    }

                    if ($excelValue == 'kcmarkInfo') {
                        unset($excelField[$excelKey]);
                    }

                    switch ($excelValue) {
                        case 'goodsCd':
                            $tmpData[] = "<td class='xl24'> ". $val[$excelValue] ." </td>";
                            break;
                        case 'goodsNm':
                        case 'goodsNmMain':
                        case 'goodsNmList':
                        case 'goodsNmDetail':
                            if ($whereCondition['goodsNameTagFl'] == 'y') {
                                $tmpData[] = "<td>" . StringUtils::removeTag($val[$excelValue]) . "</td>";
                            } else {
                                $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            }
                            break;
                        case 'daumFl':
                            if ($val['daumFl'] == 'n') {
                                $tmpData[] = "<td>" . __('노출안함') . "</td>";
                            } else {
                                $tmpData[] = "<td>" . __('노출함') . "</td>";
                            }
                            break;
                        case 'naverFl':

                            if ($val['naverFl'] == 'n') $tmpData[] = "<td>" . __('노출안함') . "</td>";
                            else  $tmpData[] = "<td>" . __('노출함') . "</td>";

                            break;
                        case 'seoTagFl':

                            if ($val['seoTagFl'] == 'n') $tmpData[] = "<td>" . __('사용안함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용함') . "</td>";

                            break;
                        case 'seoTagTitle':
                        case 'seoTagAuthor':
                        case 'seoTagDescription':
                        case 'seoTagKeyword':
                            $seoTagField = strtolower(str_replace("seoTag","",$excelValue));
                            $tmpData[] = "<td>" . $val['seoTag']['data'][$seoTagField] . "</td>";

                            break;
                        case 'goodsDisplayFl':

                            if ($val['goodsDisplayFl'] == 'n') $tmpData[] = "<td>" . __('노출안함') . "</td>";
                            else  $tmpData[] = "<td>" . __('노출함') . "</td>";

                            break;
                        case 'goodsDisplayMobileFl':

                            if ($val['goodsDisplayMobileFl'] == 'n') $tmpData[] = "<td>" . __('노출안함') . "</td>";
                            else  $tmpData[] = "<td>" . __('노출함') . "</td>";

                            break;
                        case 'goodsDescriptionMobile':

                            if ($val['goodsDescriptionSameFl'] == 'y') $tmpData[] = "<td>" . $val['goodsDescription'] . "</td>";
                            else   $tmpData[] = "<td>" . $val['goodsDescriptionSameFl'] . "</td>";

                            break;
                        case 'goodsSellFl':

                            if ($val['goodsSellFl'] == 'n') $tmpData[] = "<td>" . __('판매안함') . "</td>";
                            else  $tmpData[] = "<td>" . __('판매함') . "</td>";

                            break;
                        case 'payLimitFl':

                            if ($val['payLimitFl'] == 'y') $tmpData[] = "<td>" . __('개별설정') . "</td>";
                            else  $tmpData[] = "<td>" . __('통합설정') . "</td>";

                            break;
                        case 'payLimit':

                            $paylimit = [];
                            if(strpos($val['payLimit'], "gb") !== false) $paylimit[] = "무통장 사용";
                            if(strpos($val['payLimit'], "pg") !== false) $paylimit[] = "PG결제 사용";
                            if(strpos($val['payLimit'], "gm") !== false) $paylimit[] = "마일리지 사용";
                            if(strpos($val['payLimit'], "gd") !== false) $paylimit[] = "예치금 사용";

                            $tmpData[] = "<td>" .implode('<br>',$paylimit) . "</td>";
                            unset($paylimit);

                            break;
                        case 'goodsColor':
                            if($val['goodsColor']) {
                                $goodsColor = explode(STR_DIVISION,$val['goodsColor']);
                                foreach($goodsColor as $cKey => $cVal) {
                                    if(!in_array($cVal,$goodsColorList)) {
                                        unset($goodsColor[$cKey]);
                                    }
                                }
                                $tmpData[] = "<td>" .implode(STR_DIVISION,$goodsColor) . "</td>";
                                unset($goodsColor);
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'effectiveYmd':

                            if ($val['effectiveStartYmd'] != '0000-00-00 00:00:00' && $val['effectiveEndYmd'] != '0000-00-00 00:00:00') $tmpData[] = "<td>" . $val['effectiveStartYmd'] . '~' . $val['effectiveEndYmd'] . "</td>";
                            else  $tmpData[] = "<td></td>";

                            break;
                        case 'goodsSellMobileFl':

                            if ($val['goodsSellMobileFl'] == 'n') $tmpData[] = "<td>" . __('판매안함') . "</td>";
                            else  $tmpData[] = "<td>" . __('판매함') . "</td>";

                            break;
                        case 'soldOutFl':

                            if ($val['soldOutFl'] == 'n') $tmpData[] = "<td>" . __('정상') . "</td>";
                            else  $tmpData[] = "<td>" . __('품절') . "</td>";

                            break;
                        case 'category' :

                            $_tmpCategoryData = [];
                            $categoryList = $goodsAdmin->getGoodsLinkCategory($val['goodsNo']);
                            $cate = \App::load('\\Component\\Category\\CategoryAdmin');

                            if ($categoryList) {
                                foreach ($categoryList as $k1 => $v1) {
                                    if($v1['cateLinkFl'] =='y') {
                                        $_tmpCategoryData[] = $v1['cateCd']." : ".$cate->getCategoryPosition($v1['cateCd']);
                                    }
                                }

                                $tmpData[] = "<td>" . implode("<br>", $_tmpCategoryData) . "</td>";
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            unset($categoryList);
                            break;
                        case 'brandNm' :
                            if(empty($brandArr[$val['brandCd']]) === true && $val['brandCd']) {
                                $brandQuery = "SELECT cateNm FROM " . DB_CATEGORY_BRAND . " WHERE cateCd = '" . $val['brandCd'] . "'";
                                $brandData = $this->db->query_fetch($brandQuery, null, false);
                                $brandArr[$val['brandCd']] = $brandData['cateNm'];
                                unset($brandData,$brandQuery);
                            }
                            $tmpData[] = "<td>" .$brandArr[$val['brandCd']] . "</td>";
                            break;
                        case 'scmNm' :
                            if(empty($scmArr[$val['scmNo']]) === true && $val['scmNo']) {
                                $scmQuery = "SELECT companyNm FROM " . DB_SCM_MANAGE . " WHERE scmNo = '" . $val['scmNo'] . "'";
                                $scmData = $this->db->query_fetch($scmQuery, null, false);
                                $scmArr[$val['scmNo']] = $scmData['companyNm'];
                                unset($scmData,$scmQuery);
                            }
                            $tmpData[] = "<td>" .$scmArr[$val['scmNo']] . "</td>";
                            break;
                        case 'purchaseNm' :
                            if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && empty($purchaseArr[$val['purchaseNo']]) === true && $val['purchaseNo']) {
                                $purchaseQuery = "SELECT purchaseNm FROM " . DB_PURCHASE . " WHERE purchaseNo = '" . $val['purchaseNo'] . "'";
                                $purchaseData = $this->db->query_fetch($purchaseQuery, null, false);
                                $purchaseArr[$val['purchaseNo']] = $purchaseData['purchaseNm'];
                                unset($purchaseData,$purchaseQuery);
                            }
                            $tmpData[] = "<td>" .$purchaseArr[$val['purchaseNo']] . "</td>";
                            break;
                        case 'stockFl':

                            if ($val['stockFl'] == 'n') $tmpData[] = "<td>" . __('제한없음') . "</td>";
                            else  $tmpData[] = "<td>" . __('재고') . "</td>";

                            break;
                        case 'salesYmd':

                            if ($val['salesStartYmd'] && $val['salesEndYmd'] && $val['salesStartYmd'] != '0000-00-00 00:00:00' && $val['salesEndYmd'] != '0000-00-00 00:00:00') $tmpData[] = "<td>" . $val['salesStartYmd'] . "~" . $val['salesEndYmd'] . "</td>";
                            else  $tmpData[] = "<td></td>";

                            break;
                        case 'goodsPermission':

                            if ($val['goodsPermission'] == 'all') {
                                $tmpData[] = "<td>" . __('전체(회원+비회원)') . "</td>";
                            } else {

                                $goodsPermissionStr = "";
                                if ($val['goodsPermission'] == 'member') {
                                    $goodsPermissionStr =  __('회원전용(비회원제외)') ;
                                } else if ($val['goodsPermission'] == 'group') {
                                    $val['goodsPermissionGroup'] = explode(INT_DIVISION, $val['goodsPermissionGroup']);
                                    $memberGroupName = GroupUtil::getGroupName("sno IN ('" . implode("','", $val['goodsPermissionGroup']) . "')");
                                    $goodsPermissionStr =  __('특정회원') . "<br>(".implode(STR_DIVISION,$memberGroupName).")";
                                }

                                if($val['goodsPermissionPriceStringFl'] =='y') {
                                    $goodsPermissionStr .="<br/>".$val['goodsPermissionPriceString'];
                                }

                                $tmpData[] = "<td>" . $goodsPermissionStr."</td>";
                            }

                            break;
                        case 'goodsAccess':

                            if ($val['goodsAccess'] == 'all') {
                                $tmpData[] = "<td>" . __('전체(회원+비회원)') . "</td>";
                            } else {
                                $goodsAccessStr = "";
                                if ($val['goodsAccess'] == 'member') {
                                    $goodsAccessStr =  __('회원전용(비회원제외)') ;
                                } else if ($val['goodsAccess'] == 'group') {
                                    $val['goodsAccessGroup'] = explode(INT_DIVISION, $val['goodsAccessGroup']);
                                    $memberGroupName = GroupUtil::getGroupName("sno IN ('" . implode("','", $val['goodsAccessGroup']) . "')");
                                    $goodsAccessStr =  __('특정회원') . "<br>(".implode(STR_DIVISION,$memberGroupName).")";
                                }

                                if($val['goodsAccessDisplayFl'] =='y') {
                                    $goodsAccessStr .="<br/>접근불가 고객 상품 노출함";
                                }

                                $tmpData[] = "<td>" . $goodsAccessStr."</td>";
                            }
                            break;
                        case 'mileageFl':

                            $tmpData[] = "<td>";
                            if($val['mileageFl'] == 'c'){   // 통합설정
                                $arrMileageGroupInfo = explode(INT_DIVISION, $val['mileageGroupInfo']);
                                foreach($arrMileageGroupInfo as $item){
                                    if(empty($item) === false) {
                                        $mileageQuery1 = "SELECT groupNm FROM " . DB_MEMBER_GROUP . " WHERE sno = '" . $item . "'";
                                        $mileageData1 = $this->db->query_fetch($mileageQuery1, null, false);
                                        $mileageGroupNm1 = $mileageData1['groupNm'];
                                        $tmpData[] .= __('통합') . " : " . $mileageGroupNm1 . "<br>";
                                    }else{
                                        $tmpData[] .= __('통합') . " : " . __('전체') . "<br>";
                                    }
                                }
                            }else{  // 개별설정
                                $arrMileageGroupMemberInfo = json_decode($val['mileageGroupMemberInfo'], true);
                                if($val['mileageGroup'] == 'group') {
                                    foreach ($arrMileageGroupMemberInfo['groupSno'] as $mileageKey => $mileageVal) {
                                        if (empty($mileageVal) === false) {
                                            if ($arrMileageGroupMemberInfo['mileageGoodsUnit'][$mileageKey] == 'percent') {
                                                $mileageUnit = '%';
                                            } else {
                                                //$mileageUnit = $mileageBasicConfig['unit'];
                                                $mileageUnit = '';
                                            }
                                            $mileageQuery2 = "SELECT groupNm FROM " . DB_MEMBER_GROUP . " WHERE sno = '" . $mileageVal . "'";
                                            $mileageData2 = $this->db->query_fetch($mileageQuery2, null, false);
                                            $mileageGroupNm2 = $mileageData2['groupNm'];
                                            $tmpData[] .= __('개별') . " : " . $mileageGroupNm2 . " : " . $arrMileageGroupMemberInfo['mileageGoods'][$mileageKey] . $mileageUnit . "<br>";
                                        }
                                    }
                                }else if($val['mileageGroup'] == 'all'){
                                    if($val['mileageGoodsUnit'] == 'percent'){
                                        $mileageUnit = '%';
                                    }else{
                                        //$mileageUnit = $mileageBasicConfig['unit'];
                                        $mileageUnit = '';
                                    }
                                    $mileageQuery3 = "SELECT mileageGoods FROM " . DB_GOODS . " WHERE goodsNo = '" . $val['goodsNo'] . "'";
                                    $mileageData3 = $this->db->query_fetch($mileageQuery3, null, false);
                                    $mileageGroupNm3 = $mileageData3['mileageGoods'];
                                    $tmpData[] .= __('개별') . " : " . __('전체') . " : " . $mileageGroupNm3 . $mileageUnit . "<br>";
                                }
                            }

                            $tmpData[] .= "</td>";

                            break;
                        case 'imageList':

                            $imageList = $goodsAdmin->getGoodsImage($val['goodsNo']);
                            $_tmpImageList = [];
                            if ($imageList) {
                                foreach ($imageList as $k1 => $v1) {
                                    $_tmpImageList[] = $v1['imageName'];
                                }
                                $tmpData[] = "<td>" . implode("<br>", $_tmpImageList) . "</td>";
                                unset($_tmpImageList);
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            unset($imageList);
                            break;
                        case 'goodsIconCd':
                        case 'goodsIconCdPeriod':
                        case 'goodsIconStartYmd':
                        case 'goodsIconEndYmd':
                            $iconList = $goodsAdmin->getGoodsDetailIcon($val['goodsNo']);
                            $_tmpIconList = [];
                            if ($iconList) {
                                $iconKind = ($excelValue == 'goodsIconCd') ? 'un' : 'pe';
                                $goodsIconStartYmd = '';
                                $goodsIconEndYmd = '';
                                foreach ($iconList as $k1 => $v1) {
                                    if ($v1['iconKind'] == $iconKind) {
                                        $_tmpIconList[] = $v1['goodsIconCd'];
                                    }

                                    //기간제한 아이콘 설정기간
                                    if ($v1['iconKind'] == 'pe') {
                                        if ($excelValue == 'goodsIconStartYmd') {
                                            $goodsIconStartYmd = $v1['goodsIconStartYmd'];
                                        }

                                        if ($excelValue == 'goodsIconEndYmd') {
                                            $goodsIconEndYmd = $v1['goodsIconEndYmd'];
                                        }
                                    }
                                }

                                if ($excelValue == 'goodsIconCd' || $excelValue == 'goodsIconCdPeriod') {
                                    $tmpData[] = "<td>" . implode("||", $_tmpIconList) . "</td>";
                                    unset($_tmpIconList);
                                } else if ($excelValue == 'goodsIconStartYmd') {
                                    $tmpData[] = "<td>" . $goodsIconStartYmd."</td>";
                                } else if ($excelValue == 'goodsIconEndYmd') {
                                    $tmpData[] = "<td>" . $goodsIconEndYmd."</td>";
                                }
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            unset($iconList);
                            break;
                        case 'taxFreeFl':

                            if ($val['taxFreeFl'] == 't') $tmpData[] = "<td>" . __('과세') . "</td>";
                            else  $tmpData[] = "<td>" . __('면세') . "</td>";

                            break;
                        case 'optionFl':

                            if ($val['optionFl'] == 'y') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'optionDisplayFl':

                            if ($val['optionFl'] == 's') $tmpData[] = "<td>" . __('일체형') . "</td>";
                            else  $tmpData[] = "<td>" . __('분리형') . "</td>";

                            break;
                        case 'option':

                            if ($val['optionFl'] == 'y') {

                                $optionData = $goodsAdmin->getGoodsOption($val['goodsNo'], $val); // 옵션 & 가격 정보

                                if ($optionData) {
                                    $_tmpOption = [];
                                    foreach ($optionData as $k1 => $v1) {
                                        if (empty($v1['optionValue1'])) continue;
                                        if ($v1['optionValue1']) $_tmpOptionInfo[] = $v1['optionValue1'];
                                        if ($v1['optionValue1']) $_tmpOptionInfo[] = $v1['optionValue2'];
                                        if ($v1['optionValue2']) $_tmpOptionInfo[] = $v1['optionValue3'];
                                        if ($v1['optionValue3']) $_tmpOptionInfo[] = $v1['optionValue4'];
                                        if ($v1['optionValue4']) $_tmpOptionInfo[] = $v1['optionValue5'];

                                        $_tmpOptionInfo[] = gd_currency_display($v1['optionCostPrice']);
                                        $_tmpOptionInfo[] = gd_currency_display($v1['optionPrice']);
                                        $_tmpOptionInfo[] = $v1['stockCnt'];
                                        $_tmpOptionInfo[] = $v1['optionCode'];


                                        if ($v1['optionViewFl'] == 'y') $_tmpOptionInfo[] = __("노출함");
                                        else  $_tmpOptionInfo[] = __("노출안함");

                                        if ($v1['optionSellFl'] == 'y') $_tmpOptionInfo[] = __("판매함");
                                        else  $_tmpOptionInfo[] = __("판매안함");

                                        $_tmpOptionInfo[] = $v1['optionMemo'];

                                        $_tmpOption[] = implode("/", $_tmpOptionInfo);
                                        unset($_tmpOptionInfo);
                                    }

                                    $tmpData[] = "<td>" . implode("<br>", $_tmpOption) . "</td>";
                                    unset($_tmpOption);

                                } else {
                                    $tmpData[] = "<td></td>";
                                }

                                unset($optionData);
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            break;

                        case 'addGoodsFl' :

                            if ($val['addGoodsFl'] == 'y') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'addGoods':

                            if ($val['addGoodsFl'] == 'y') {

                                $addGoods = json_decode(gd_htmlspecialchars_stripslashes($val['addGoods']), true);

                                if ($addGoods) {

                                    foreach ($addGoods as $k1 => $v1) {
                                        $_tmpAddGoodsInfo[] = $v1['title'];
                                        if ($v1['mustFl'] == 'y') $_tmpAddGoodsInfo[] = __("필수");
                                        $_tmpAddGoodsInfo[] = implode(",", $v1['addGoods']);
                                        $_tmpAddGoods[] = implode("/", $_tmpAddGoodsInfo);
                                        unset($_tmpAddGoodsInfo);
                                    }

                                    $tmpData[] = "<td>" . implode("<br>", $_tmpAddGoods) . "</td>";
                                    unset($_tmpAddGoods);

                                } else {
                                    $tmpData[] = "<td></td>";
                                }

                                unset($addGoods);

                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'hscode':

                            if ($val['hscode']) {

                                $hscode = json_decode(gd_htmlspecialchars_stripslashes($val['hscode']), true);

                                if ($hscode) {
                                    foreach ($hscode as $k1 => $v1) {
                                        $_tmpHsCode[] = $k1 . " : " . $v1;
                                    }

                                    $tmpData[] = "<td>" . implode("<br>", $_tmpHsCode) . "</td>";
                                    unset($_tmpHsCode);

                                } else {
                                    $tmpData[] = "<td></td>";
                                }

                                unset($hscode);

                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'optionTextFl':

                            if ($val['optionTextFl'] == 'y') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'optionText':

                            if ($val['optionTextFl'] == 'y') {
                                $optionText = $goodsAdmin->getGoodsOptionText($val['goodsNo']); // 텍스트 옵션 정보
                                if ($optionText) {
                                    foreach ($optionText as $k1 => $v1) {
                                        $_tmpOptionTextInfo[] = $v1['optionName'];
                                        if ($v1['mustFl'] == 'y') $_tmpOptionTextInfo[] = __("필수");
                                        $_tmpOptionTextInfo[] = gd_currency_display($v1['addPrice']);
                                        $_tmpOptionTextInfo[] = $v1['inputLimit'];
                                        $_tmpOptionText[] = implode("/", $_tmpOptionTextInfo);
                                        unset($_tmpOptionTextInfo);
                                    }
                                    $tmpData[] = "<td>" . implode("<br>", $_tmpOptionText) . "</td>";
                                    unset($_tmpOptionText);
                                } else {
                                    $tmpData[] = "<td></td>";
                                }
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'relationFl' :

                            if ($val['relationFl'] != 'n') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'relationCnt' :

                            if ($val['relationFl'] != 'n') {
                                if ($val['relationGoodsNo']) {
                                    $tmpData[] = "<td>" . count(explode(INT_DIVISION, $val['relationGoodsNo'])) . "</td>";
                                } else {
                                    $tmpData[] = "<td>0</td>";
                                }
                            } else {
                                $tmpData[] = "<td></td>";
                            }


                            break;
                        case 'relationGoodsNo' :
                            if ($val['relationFl'] != 'n') {
                                if ($val['relationGoodsNo']) {
                                    $tmpData[] = "<td>" . $val['relationGoodsNo'] . "</td>";
                                } else {
                                    $tmpData[] = "<td></td>";
                                }
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'goodsAddInfo' :

                            $addInfo = $goodsAdmin->getGoodsAddInfo($val['goodsNo']); // 추가항목 정보
                            if ($addInfo) {
                                foreach ($addInfo as $k1 => $v1) {
                                    $_tmpAddInfo[] = $v1['infoTitle'] . ":" . $v1['infoValue'];
                                }
                                $tmpData[] = "<td>" . implode("<br>", $_tmpAddInfo) . "</td>";
                                unset($_tmpAddInfo);
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'goodsMustInfo' :

                            $goodsMustInfo = json_decode(gd_htmlspecialchars_stripslashes($val['goodsMustInfo']), true);
                            if ($goodsMustInfo) {
                                foreach ($goodsMustInfo as $k1 => $addMustInfo) {
                                    foreach ($addMustInfo as $k2 => $v2) {
                                        $_tmpAddMustInfo[] = $v2['infoTitle'] . ":" . $v2['infoValue'];
                                    }
                                }
                                $tmpData[] = "<td>" . implode("<br>", $_tmpAddMustInfo) . "</td>";
                                unset($_tmpAddMustInfo);
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'detailInfoDelivery' :
                        case 'detailInfoAS' :
                        case 'detailInfoRefund' :
                        case 'detailInfoExchange' :

                            switch ($val[$excelValue.'Fl']) {
                                case 'no': {
                                    $val[$excelValue] = "";
                                    break;
                                }
                                case 'direct' : {
                                    $val[$excelValue] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($val[$excelValue.'DirectInput']));
                                    break;
                                }
                                case 'selection' : {
                                    $val[$excelValue] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($val[$excelValue]));
                                    break;
                                }
                            }

                            $tmpData[] = "<td>" .$val[$excelValue] . "</td>";

                            break;
                        case 'restockFl' :

                            if ($val['restockFl'] == 'y') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'qrCodeFl' :

                            if ($val['qrCodeFl'] == 'y') $tmpData[] = "<td>" . __('노출함') . "</td>";
                            else  $tmpData[] = "<td>" . __('노출안함') . "</td>";

                            break;
                        case 'onlyAdultFl' :

                            if ($val['onlyAdultFl'] == 'y')  {
                                $onlyAdultStr = "사용함";
                                if($val['onlyAdultDisplayFl'] =='y') {
                                    $onlyAdultStr .="<br/>미인증 고객 상품 노출함";
                                }
                                if($val['onlyAdultImageFl'] =='y') {
                                    $onlyAdultStr .="<br/>미인증 고객 상품 이미지 노출함";
                                }
                                $tmpData[] = "<td>" . $onlyAdultStr . "</td>";
                            } else  {
                                $tmpData[] = "<td>" . __('사용안함') . "</td>";
                            }

                            break;
                        case 'imgDetailViewFl' :

                            if ($val['imgDetailViewFl'] == 'y') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'externalVideoFl' :

                            if ($val['externalVideoFl'] == 'y') $tmpData[] = "<td>" . __('사용함') . "</td>";
                            else  $tmpData[] = "<td>" . __('사용안함') . "</td>";

                            break;
                        case 'goodsState' :
                            $tmpData[] = "<td>" . $goodsStateList[$val['goodsState']] . "</td>";
                            break;
                        case 'naverImportFlag' :
                            $tmpData[] = "<td>" . $goodsImportType[$val['naverImportFlag']] . "</td>";
                            break;
                        case 'naverProductFlag' :
                            $tmpData[] = "<td>" . $goodsSellType[$val['naverProductFlag']] . "</td>";
                            break;
                        case 'naverAgeGroup' :
                            $tmpData[] = "<td>" . $goodsAgeType[$val['naverAgeGroup']] . "</td>";
                            break;
                        case 'naverGender' :
                            $tmpData[] = "<td>" . $goodsGenderType[$val['naverGender']] . "</td>";
                            break;
                        case 'orderRate' : // 구매율
                            if($val['orderGoodsCnt'] > 0 && $val['hitCnt'] > 0 ) {
                                $orderRate = round(($val['orderGoodsCnt'] / $val['hitCnt']) * 100, 2) . "%";
                            }
                            else {
                                $orderRate = "0%";
                            }
                            $tmpData[] = "<td>" . $orderRate . "</td>";
                            break;
                        case 'reviewCnt' : // 후기수
                            if(gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) {
                                $reviewCnt =  ($val['reviewCnt'] + $val['plusReviewCnt']);
                            }
                            else {
                                $reviewCnt = $val['reviewCnt'];
                            }
                            $tmpData[] = "<td>" . $reviewCnt . "</td>";
                            break;
                        case 'kcmarkFl' :
                            if ($kcmarkInfoArr[$excelValue] == 'y') {
                                $tmpData[] = '<td>사용함</td>';
                            } else {
                                $tmpData[] = '<td>사용안함</td>';
                            }
                            break;
                        case 'kcmarkNo' :
                            $tmpData[] = '<td>' . $kcmarkInfoArr[$excelValue] . '</td>';
                            break;
                        case 'kcmarkDivFl' :
                            $tmpData[] = '<td>' . $kcmarkInfoArr[$excelValue] . '</td>';
                            break;
                        case 'kcmarkDt' :
                            if($kcmarkInfoArr['kcmarkDivFl'] == 'kcCd04' || $kcmarkInfoArr['kcmarkDivFl'] == 'kcCd05' || $kcmarkInfoArr['kcmarkDivFl'] == 'kcCd06'){
                                $tmpData[] = '<td>' . $kcmarkInfoArr[$excelValue] . '</td>';
                            }else{
                                $kcmarkInfoArr['kcmarkDt'] = '';
                                $tmpData[] = '<td>' . $kcmarkInfoArr[$excelValue] . '</td>';
                            }

                            break;
                        case 'sellStopStock':
                            $optionData = $goodsAdmin->getGoodsOption($val['goodsNo'], $val); // 옵션 & 가격 정보
                            foreach($optionData as $key_inner => $value_inner){
                                if($value_inner['sellStopFl'] == 'y') $tmpString[] = $value_inner['sellStopStock'];
                                else $tmpString[] = __('사용하지 않음');
                            }
                            $tmpData[]  = '<td>'.implode("<br />", $tmpString).'</td>';

                            unset($tmpString);
                            break;
                        case 'confirmRequestStock':
                            $optionData = $goodsAdmin->getGoodsOption($val['goodsNo'], $val); // 옵션 & 가격 정보
                            foreach($optionData as $key_inner => $value_inner){
                                if($value_inner['confirmRequestFl'] == 'y') $tmpString[] = $value_inner['confirmRequestStock'];
                                else $tmpString[] = __('사용하지 않음');
                            }
                            $tmpData[]  = '<td>'.implode("<br />", $tmpString).'</td>';

                            unset($tmpString);
                            break;
                        case 'optionDelivery':
                            $optionData = $goodsAdmin->getGoodsOption($val['goodsNo'], $val); // 옵션 & 가격 정보
                            foreach($optionData as $key_inner => $value_inner){
                                if($value_inner['optionDeliveryFl'] == 'normal') $tmpString[] = '정상';
                                else if($value_inner['optionDeliveryFl'] == 't'){
                                    $request = \App::getInstance('request');
                                    $mallSno = $request->get()->get('mallSno', 1);
                                    $code = \App::load('\\Component\\Code\\Code',$mallSno);
                                    $deliveryReason = $code->getGroupItems('05003');
                                    $deliveryReasonNew['normal'] = $deliveryReason['05003001']; //정상은 코드 변경
                                    unset($deliveryReason['05003001']);
                                    $deliveryReason = array_merge($deliveryReasonNew, $deliveryReason);

                                    $tmpString[] = $deliveryReason[$value_inner['optionDeliveryCode']];
                                }
                            }
                            $tmpData[]  = '<td>'.implode("<br />", $tmpString).'</td>';

                            unset($tmpString);
                            break;

                        case 'optionSellFl':
                            $optionData = $goodsAdmin->getGoodsOption($val['goodsNo'], $val); // 옵션 & 가격 정보
                            foreach($optionData as $key_inner => $value_inner){
                                if($value_inner['optionSellFl'] == 'y') $tmpString[] = '정상';
                                else if($value_inner['optionSellFl'] == 'n') $tmpString[] = '품절';
                                else if($value_inner['optionSellFl'] == 't'){
                                    $request = \App::getInstance('request');
                                    $mallSno = $request->get()->get('mallSno', 1);
                                    $code = \App::load('\\Component\\Code\\Code',$mallSno);
                                    $deliverySell = $code->getGroupItems('05002');
                                    $deliverySellNew['y'] = $deliverySell['05002001']; //정상은 코드 변경
                                    $deliverySellNew['n'] = $deliverySell['05002002']; //품절은 코드 변경
                                    unset($deliverySell['05002001']);
                                    unset($deliverySell['05002002']);
                                    $deliverySell = array_merge($deliverySellNew, $deliverySell);

                                    $tmpString[] = $deliverySell[$value_inner['optionSellCode']];
                                }
                            }
                            $tmpData[]  = '<td>'.implode("<br />", $tmpString).'</td>';

                            unset($tmpString);
                            break;
                        case 'kcmarkFl' :
                            if ($kcmarkInfoArr[$excelValue] == 'y') {
                                $tmpData[] = '<td>사용함</td>';
                            } else {
                                $tmpData[] = '<td>사용안함</td>';
                            }
                            break;
                        case 'kcmarkNo' :
                            $tmpData[] = '<td>' . $kcmarkInfoArr[$excelValue] . '</td>';
                            break;
                        case 'kcmarkDivFl' :
                            $tmpData[] = '<td>' . $kcmarkInfoArr[$excelValue] . '</td>';
                            break;
                        default  :
                            if ($excelFieldName[$excelValue]['type'] == 'mileage' && $val[$excelValue] != '') {

                                $tmpData[] = "<td>" . gd_number_figure($val[$excelValue], $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']) . $this->mileageGiveInfo['basic']['unit'] . "</td>";
                            } else if ($excelFieldName[$excelValue]['type'] == 'deposit' && $val[$excelValue] != '') {
                                $tmpData[] = "<td>" . number_format($val[$excelValue]) . $this->depositInfo['unit'] . "</td>";
                            } else {
                                if ($val[$excelValue] == '0000-00-00' || $val[$excelValue] == '0000-00-00 00:00:00') {
                                    $tmpData[] = "<td></td>";
                                } else {
                                    $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                                }
                            }


                            break;

                    }


                }
                $tmpData[] = "</tr>";

                if($key == '0') {
                    fwrite($fh, implode(chr(13) . chr(10),$setHedData));
                    unset($setHedData);
                }
                fwrite($fh, implode(chr(13) . chr(10),$tmpData));
                unset($tmpData);
            }

            fwrite($fh, "</table>");
            fwrite($fh,  $this->excelFooter);
            fclose($fh);

            $this->fileConfig['fileName'][] = $fileName . ".xls";
        }

        return true;
    }

    /*
     * 삭제 상품 엑셀파일
     */
    public function getGoodsGoodsListDelete($whereCondition, $excelField, $excelFieldName)
    {

        $whereCondition['delFl'] = "y";

        return $this->getGoodsGoodsList($whereCondition, $excelField, $excelFieldName);

    }

    /*
     * 상품 필수 정보
     */
    public function getGoodsGoodsMustInfoList($whereCondition, $excelField, $excelFieldName)
    {

        $mustInfo = \App::load('\\Component\\Goods\\GoodsMustInfo');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 검색 설정
        $data = $mustInfo->getAdminListMustInfoExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'mustInfo':
                            $_tmpArray = json_decode($val['info'], true);

                            if (is_array($_tmpArray)) {

                                foreach ($_tmpArray['infoTitle'] as $k1 => $mustInfoData) {
                                    foreach ($mustInfoData as $k2 => $v2) {
                                        $_tmpMustInfoData[] = $v2 . ":" . $_tmpArray['infoValue'][$k1][$k2];
                                    }

                                    $_tmpMustInfo[] = implode(" / ", $_tmpMustInfoData);
                                    unset($_tmpMustInfoData);
                                }
                                $tmpData[] = "<td>" . implode("<br>", $_tmpMustInfo) . "</td>";
                                unset($_tmpMustInfo);
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
       * 추가상품정보
       */
    public function getGoodsAddGoodsList($whereCondition, $excelField, $excelFieldName)
    {

        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        $excelField = explode(STR_DIVISION, $excelField);

        $isGlobal = false;
        foreach ($excelField as $k => $v) {
            if (strpos($v, 'global_') !== false) {
                $isGlobal = true;
            }
        }

        // --- 검색 설정
        $data = $addGoods->getAdminListAddGoodsExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                if ($isGlobal) {
                    $globalData = $addGoods->getDataAddGoodsGlobal($val['addGoodsNo']);
                    if ($globalData) {
                        $globalField = array_keys($globalData[0]);
                        unset($globalField[0]); //mallSno삭제
                        foreach ($globalData as $globalKey => $globalValue) {
                            foreach ($globalField as $globalKey1 => $globalValue1) {
                                $val['global_' . $globalValue['mallSno'] . '_' . $globalValue1] = $globalValue[$globalValue1];
                            }
                        }
                    }
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'viewFl':

                            if ($val['viewFl'] == 'y') $tmpData[] = "<td>" . __('노출함') . "</td>";
                            else  $tmpData[] = "<td>" . __('노출안함') . "</td>";

                            break;
                        case 'soldOutFl':

                            if ($val['soldOutFl'] == 'y') $tmpData[] = "<td>" . __('품절') . "</td>";
                            else  $tmpData[] = "<td>" . __('정상') . "</td>";

                            break;
                        case 'stockUseFl':

                            if ($val['stockUseFl'] == 'n') {
                                $strStockFl = __('제한없음');
                            } else {
                                if ($val['stockCnt'] > 0) {
                                    $strStockFl = number_format($val['stockCnt']);
                                } else {
                                    $strStockFl = __('품절');
                                }
                            }

                            $tmpData[] = "<td>" . $strStockFl . "</td>";

                            break;
                        case 'taxFreeFl':

                            if ($val['taxFreeFl'] == 't') $tmpData[] = "<td>" . __('과세') . "</td>";
                            else  $tmpData[] = "<td>" . __('면세') . "</td>";

                            break;
                        default  :
                            if ($excelFieldName[$excelValue]['type'] == 'mileage' && $val[$excelValue] != '') {
                                $tmpData[] = "<td>" . gd_number_figure($val[$excelValue], $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']) . $this->mileageGiveInfo['basic']['unit'] . "</td>";
                            } else if ($excelFieldName[$excelValue]['type'] == 'deposit' && $val[$excelValue] != '') {
                                $tmpData[] = "<td>" . number_format($val[$excelValue]) . $this->depositInfo['unit'] . "</td>";
                            } else {
                                $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            }

                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
            unset($globalData);
        }

        return $setData;
    }


    /*
   * 오프라인쿠폰 인증번호 관리
   */
    public function getPromotionCouponOfflineList($whereCondition, $excelField, $excelFieldName)
    {

        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 검색 설정
        $data = $couponAdmin->getCouponOfflineAuthCodeAdminListExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'couponOfflineCodeUser':
                            $tmpData[] = "<td class=\"xl24\">" . $val[$excelValue] . "</td>";
                            break;
                        case 'couponOfflineCodeSaveType':
                            if ($val['couponOfflineCodeSaveType'] == 'y') {
                                $couponOfflineCodeSaveType = __('발급');
                            } else {
                                $couponOfflineCodeSaveType = __('미발급');
                            }
                            $tmpData[] = "<td>" . $couponOfflineCodeSaveType . "</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /**
     * getMemberMemberList
     *
     * @param $whereCondition
     * @param $excelField
     * @param $excelFieldName
     *
     * @return array
     */
    public function getMemberMemberList($whereCondition, $excelField, $excelFieldName)
    {
        $excelField = explode(STR_DIVISION, $excelField);

        if (!$whereCondition) {
            $whereCondition = [''];
        }

        $memberDAO = \App::load(MemberDAO::class);

        $data = $memberDAO->selectExcelMemberList($whereCondition);

        $totalNum = \count($data);

        if ($totalNum > $this->excelPageNum) {
            $data = array_chunk($data, $this->excelPageNum, true);
        } else {
            $data = array_chunk($data, $totalNum, true);
        }

        $setData = $setHedData = [];
        $setHedData[] = '<tr>';
        foreach ($data as $k => $v) {
            $tmpData = [];
            foreach ($v as $key => $val) {
                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpDataRow = [];
                $tmpDataRow[] = '<tr>';
                foreach ($excelField as $excelKey => $excelValue) {
                    if (!$excelFieldName[$excelValue]) {
                        continue;
                    }

                    if ((int) $key === 0) {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . '</td>';
                    }

                    switch ($excelValue) {
                        case 'mallSno':
                            $tmpDataRow[] = '<td>' . $this->gGlobal['mallList'][$val['mallSno']]['mallName'] . '</td>';
                            break;
                        case 'appFl':
                            if ($val['appFl'] === 'y') {
                                $tmpDataRow[] = '<td>' . __('승인') . '</td>';
                            } else {
                                $tmpDataRow[] = '<td>' . __('미승인') . '</td>';
                            }
                            break;
                        case 'sexFl':
                            if ($val['sexFl'] === 'm') {
                                $tmpDataRow[] = '<td>' . __('남자') . '</td>';
                            } elseif ($val['sexFl'] === 'w') {
                                $tmpDataRow[] = '<td>' . __('여자') . '</td>';
                            } else {
                                $tmpDataRow[] = '<td></td>';
                            }
                            break;
                        case 'recommId':
                        case 'memId':
                        case 'nickNm':
                            $tmpDataRow[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . '</td>';
                            break;
                        case 'address':
                            $tmpDataRow[] = '<td>' . $val['zonecode'] . ' ' . $val['address'] . ' ' . $val['addressSub'] . '</td>';
                            break;
                        case 'comAddress':
                            $tmpDataRow[] = '<td>' . $val['comZonecode'] . ' ' . $val['comAddress'] . ' ' . $val['comAddressSub'] . '</td>';
                            break;
                        default  :
                            $isEmpty = $val[$excelValue] === '';
                            if (!$isEmpty && $excelFieldName[$excelValue]['type'] === 'mileage') {
                                $tmpDataRow[] = '<td>' . NumberUtils::getNumberFigure($val[$excelValue], $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']) . $this->mileageGiveInfo['basic']['unit'] . '</td>';
                            } elseif (!$isEmpty && $excelFieldName[$excelValue]['type'] === 'deposit') {
                                $tmpDataRow[] = '<td>' . number_format($val[$excelValue]) . $this->depositInfo['unit'] . '</td>';
                            } else {
                                $tmpDataRow[] = '<td>' . $val[$excelValue] . '</td>';
                            }
                            break;
                    }
                }
                $tmpDataRow[] = '</tr>';
                $tmpData[] = implode('', $tmpDataRow);
            }

            $setHedData[] = '</tr>';
            $setData[] = array_merge([implode('', $setHedData)], $tmpData);
        }

        return $setData;
    }


    /*
    * 게시판 게시글 관리
    */
    public function getBoardBoard($whereCondition, $excelField, $excelFieldName)
    {

        $articleList = \App::load('\\Component\\Board\\ArticleListAdmin', $whereCondition);

        $excelField = explode(STR_DIVISION, $excelField);
        // --- 검색 설정

        $data = $articleList->getExcelList($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
     * 게시판 댓글 관리
     */
    public function getBoardMemo($whereCondition, $excelField, $excelFieldName)
    {

        $memoList = \App::load('\\Component\\Memo\\MemoAdmin', $whereCondition);

        $excelField = explode(STR_DIVISION, $excelField);
        // --- 검색 설정

        $data = $memoList->getExcelList($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /**
     * 플러스리뷰 게시글 관리
     *
     * @param $whereCondition
     * @param $excelField
     * @param $excelFieldName
     * @return array
     */
    public function getPlusreviewPlusreviewBoard($whereCondition, $excelField, $excelFieldName)
    {
        $plusReviewArticle = \App::load('Component\\PlusShop\\PlusReview\\PlusReviewArticleAdmin');
        $excelField = explode(STR_DIVISION, $excelField);
        $whereCondition['excelField'] = $excelField;
        $data = $plusReviewArticle->getListForExcel($whereCondition);
        $totalNum = count($data);
        if ($this->excelPageNum) {
            $data = array_chunk($data, $this->excelPageNum, true);
        } else {
            $data = array_chunk($data, $totalNum, true);
        }
        $setData = [];
        $setHeadData[] = "<tr>";
        foreach ($data as $k => $v) {
            $tmpData = [];
            foreach ($v as $key => $val) {
                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }
                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {
                    if (!$excelFieldName[$excelValue]) {
                        continue;
                    }
                    if ($key == '0') {
                        $setHeadData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }
                    switch ($excelValue) {
                        case 'orderNo':
                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'addFormData':
                            $tmpAddFormData = ArrayUtils::multi_implode(' / ', $val[$excelValue]);
                            if (is_null($tmpAddFormData) || $tmpAddFormData === 'null') {
                                $tmpAddFormData = '';
                            }
                            $tmpData[] = '<td>' . $tmpAddFormData . '</td>';
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }
                }
                $tmpData[] = "</tr>";
            }
            $setHeadData[] = "</tr>";
            $setData[] = array_merge($setHeadData, $tmpData);
        }

        return $setData;
    }

    /**
     * 플러스리뷰 댓글 관리
     *
     * @param $whereCondition
     * @param $excelField
     * @param $excelFieldName
     * @return array
     */
    public function getPlusreviewPlusreviewMemo($whereCondition, $excelField, $excelFieldName)
    {
        $plusReviewArticle = \App::load('Component\\PlusShop\\PlusReview\\PlusReviewArticleAdmin');
        $excelField = explode(STR_DIVISION, $excelField);
        $whereCondition['excelField'] = $excelField;
        $data = $plusReviewArticle->getMemoListForExcel($whereCondition);
        $totalNum = count($data);
        if ($this->excelPageNum) {
            $data = array_chunk($data, $this->excelPageNum, true);
        } else {
            $data = array_chunk($data, $totalNum, true);
        }
        $setData = [];
        $setHeadData[] = "<tr>";
        foreach ($data as $k => $v) {
            $tmpData = [];
            foreach ($v as $key => $val) {
                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }
                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {
                    if (!$excelFieldName[$excelValue]) {
                        continue;
                    }
                    if ($key == '0') {
                        $setHeadData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }
                    switch ($excelValue) {
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }
                }
                $tmpData[] = "</tr>";
            }
            $setHeadData[] = "</tr>";
            $setData[] = array_merge($setHeadData, $tmpData);
        }

        return $setData;
    }

    /*
   * 공급사 엑셀 다운로드
   */
    public function getScmScmList($whereCondition, $excelField, $excelFieldName)
    {

        $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);

        $excelField = explode(STR_DIVISION, $excelField);
        // --- 검색 설정

        $data = $scmAdmin->getScmAdminListExcel($whereCondition);

        $count['staff'] = 0;
        $count['account'] = 0;
        if(in_array('staff',$excelField)) {
            $count['staff'] = 1;
            $department = gd_code('02001'); // 부서
        }
        if(in_array('account',$excelField)){
            $count['account'] = 1;
            $account = gd_code('04002');
        }

        if($count['staff'] > 0 || $count['account'] > 0) {
            foreach ($data as $k => $v) {
                if($count['staff'] > 0 && empty($v['staff']) == false) {
                    $_staff = json_decode($v['staff']);
                    if(count($_staff) > $count['staff']) $count['staff'] = count($_staff);
                }
                if($count['account'] > 0 && empty($v['account']) == false) {
                    $_account = json_decode($v['account']);
                    if(count($_account) > $count['account']) $count['account'] = count($_account);
                }
            }
        }

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        switch ($excelValue) {
                            case 'staff':
                            case 'account':
                                $colspan = ($excelValue == 'staff') ? 5 : 4;
                                if($count[$excelValue] > 1) {
                                    for($i = 1; $i <= $count[$excelValue]; $i++) {
                                        $setHedData[] = "<td class='title' colspan='" . $colspan . "'>" . $excelFieldName[$excelValue]['name'] . $i . "</td>";
                                    }
                                } else {
                                    $setHedData[] = "<td class='title' colspan='" . $colspan . "'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                                }
                                break;
                            default:
                                $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                                break;
                        }
                    }

                    switch ($excelValue) {
                        case 'staff':
                            if(empty($val[$excelValue]) == false) {
                                $_tmpData = json_decode($val[$excelValue]);
                            }
                            for($i = 0; $i < $count['staff']; $i++) {
                                $tmpData[] = "<td>" . $department[$_tmpData[$i]->staffType] . "</td>";
                                $tmpData[] = "<td>" . $_tmpData[$i]->staffName . "</td>";
                                $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $_tmpData[$i]->staffTel . "</td>";
                                $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $_tmpData[$i]->staffPhone . "</td>";
                                $tmpData[] = "<td>" . $_tmpData[$i]->staffEmail . "</td>";
                            }
                            unset($_tmpData);
                            break;
                        case 'account':
                            if(empty($val[$excelValue]) == false) {
                                $_tmpData = json_decode($val[$excelValue]);
                            }
                            for($i = 0; $i < $count['account']; $i++) {
                                $tmpData[] = "<td>" . $account[$_tmpData[$i]->accountType] . "</td>";
                                $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $_tmpData[$i]->accountNum . "</td>";
                                $tmpData[] = "<td>" . $_tmpData[$i]->accountName . "</td>";
                                $tmpData[] = "<td>" . $_tmpData[$i]->accountMemo . "</td>";
                            }
                            unset($_tmpData);
                            break;
                        case 'scmPermissionInsert':
                        case 'scmPermissionModify':
                        case 'scmPermissionDelete':
                            if ($val[$excelValue] == 'c') $tmpData[] = "<td>" . __('관리자승인') . "</td>";
                            else  $tmpData[] = "<td>" . __('자동승인') . "</td>";
                            break;
                        case 'scmKind':
                            if ($val['scmKind'] == 'p') $tmpData[] = "<td>" . __('공급사') . "</td>";
                            else  $tmpData[] = "<td>" . __('본사') . "</td>";
                            break;
                        case 'scmType':
                            if ($val['scmType'] == 'y') $tmpData[] = "<td>" . __('운영') . "</td>";
                            else  $tmpData[] = "<td>" . __('탈퇴') . "</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
    * 통합정산관리 엑셀 다운로드
    */
    public function getScmScmAdjustTotal($whereCondition, $excelField, $excelFieldName)
    {

        $scmAdjust = \App::load('\\Component\\Scm\\ScmAdjust');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 리스트 설정
        $data = $scmAdjust->getScmAdjustTotal($whereCondition)['data']['list'];

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        $index = 0;
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $val['adjustGoods'] = ($val['10']['order']['adjust'] + $val['30']['order']['adjust']);
                $val['adjustDelivery'] = ($val['10']['delivery']['adjust'] + $val['30']['delivery']['adjust']);
                $val['adjustTotal'] = $val['adjustGoods'] + $val['adjustDelivery'];
                $val['commissionGoods'] = ($val['10']['order']['commission'] + $val['30']['order']['commission']);
                $val['commissionDelivery'] = ($val['10']['delivery']['commission'] + $val['30']['delivery']['commission']);
                $val['commissionTotal'] = $val['commissionGoods'] + $val['commissionDelivery'];
                $val['refundGoods'] = ($val['10']['orderAfter']['total'] + $val['30']['orderAfter']['total']);
                $val['refundDelivery'] = ($val['10']['deliveryAfter']['total'] + $val['30']['deliveryAfter']['total']);
                $val['refundTotal'] = $val['refundGoods'] + $val['refundDelivery'];
                $val['priceTotal'] = $val['adjustTotal'] + $val['commissionTotal'] + $val['refundTotal'];
                // 정산요청 금액
                $val['step1'] = $val['1']['order']['ea'] + $val['1']['delivery']['ea'] + $val['1']['orderAfter']['ea'] + $val['1']['deliveryAfter']['ea'];
                // 정산확정 금액
                $val['step10'] = $val['10']['order']['ea'] + $val['10']['delivery']['ea'] + $val['10']['orderAfter']['ea'] + $val['10']['deliveryAfter']['ea'];
                // 지급완료 금액
                $val['step30'] = $val['30']['order']['ea'] + $val['30']['delivery']['ea'] + $val['30']['orderAfter']['ea'] + $val['30']['deliveryAfter']['ea'];


                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($index == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'orderNo':
                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'sno':
                            $tmpData[] = "<td>" . ($index + 1) . "</td>";
                            break;
                        case 'searchDate':
                            if ($whereCondition['treatDate'][0] && $whereCondition['treatDate'][0]) {
                                $tmpData[] = "<td>" . $whereCondition['treatDate'][0] . " ~ " . $whereCondition['treatDate'][1] . "</td>";
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            break;
                        case 'companyNm':
                            $tmpData[] = "<td>" . $val['scm']['scmName'] . "</td>";
                            break;
                        case 'scmCode':
                            $tmpData[] = "<td>" . $val['scm']['scmCode'] . "</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
                $index++;
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }


    /*
    * 정산관리 엑셀 다운로드
    */
    public function getScmScmAdjustList($whereCondition, $excelField, $excelFieldName)
    {

        $adjust = \App::load('\\Component\\Scm\\ScmAdjust');

        $excelField = explode(STR_DIVISION, $excelField);

        $data = $adjust->getScmAdjustListExcel($whereCondition);

        $totalNum = count($data);
        if ($this->excelPageNum) {
            $convertData = array_chunk($data['convertData'], $this->excelPageNum, true);
            $data = array_chunk($data['data'], $this->excelPageNum, true);
        } else {
            $convertData = array_chunk($data['convertData'], count($data['convertData']), true);
            $data = array_chunk($data['data'], $totalNum, true);
        }

        $setData = [];
        $setHedData[] = "<tr>";
        $index = 0;
        foreach ($data as $adjustSno => $adjustInfo) {
            $tmpData = [];
            foreach ($adjustInfo as $k => $v) {
                if ($v['scmAdjustKind'] === 'm') {
                    $tmpData[] = "<tr>";
                    foreach ($excelField as $excelKey => $excelValue) {
                        if (!$excelFieldName[$excelValue]) continue;
                        if ($index == '0') {
                            $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                        }
                        switch ($excelValue) {
                            case 'orderNo':
                                $tmpData[] = "<td style='mso-number-format:\"\@\";'>수기정산으로 주문번호 없음</td>";
                                break;
                            case 'sno':
                                $tmpData[] = "<td>" . ($index + 1) . "</td>";
                                break;
                            case 'managerNm':
                            case 'managerId':
                            case 'regDt':
                            case 'scmAdjustDt':
                            case 'invoiceNo':
                            case 'invoiceCompanySno':
                            case 'receiverName':
                            case 'orderGoodsSno':
                                $tmpData[] = "<td>" . $v[$excelValue] . "</td>";
                                break;
                            case 'companyNm':
                                $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scm']['name'] . "</td>";
                                break;
                            case 'scmCode':
                                $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scm']['code'] . "</td>";
                                break;
                            case 'scmAdjustType':
                                $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustType'] . "</td>";
                                break;
                            case 'scmAdjustKind':
                                $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustKind'] . "</td>";
                                break;
                            case 'scmAdjustState':
                                $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustState'] . "</td>";
                                break;
                            case 'scmAdjustCode':
                                $tmpData[] = "<td>" . $adjustInfo[$k]['scmAdjustCode'] . "</td>";
                                break;
                            case 'price':
                                $tmpData[] = "<td>" . $v['scmAdjustTotalPrice'] . "</td>";
                                break;
                            case 'commission':
                                if ($v['scmAdjustCommission'] > 0) {
                                    $tmpData[] = "<td>" . $v['scmAdjustCommission'] . "%</td>";
                                } else {
                                    $tmpData[] = "<td></td>";
                                }
                                break;
                            case 'adjustCommission':
                                $tmpData[] = "<td>" . $v['scmAdjustCommissionPrice'] . "</td>";
                                break;
                            case 'adjustPrice':
                                $tmpData[] = "<td>" . $v['scmAdjustPrice'] . "</td>";
                                break;
                            default  :
                                $tmpData[] = "<td></td>";
                                break;
                        }

                    }
                    $tmpData[] = "</tr>";

                    if (count($tmpData)) $index++;
                } else {
                    foreach ($v['info']['data'] as $key => $val) {

                        if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                            echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                        }

                        if ($v['scmAdjustType'] == 'o' || $v['scmAdjustType'] == 'oa') {
                            $val['price'] = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']);
                            $val['adjustCommission'] = $val['goodsAdjustCommission'];
                            $val['adjustPrice'] = $val['goodsAdjustPrice'];
                        } else {
                            $val['price'] = $val['deliveryCharge'];
                            $val['adjustCommission'] = $val['deliveryAdjustCommission'];
                            $val['adjustPrice'] = $val['deliveryAdjustPrice'];
                        }

                        $tmpData[] = "<tr>";

                        foreach ($excelField as $excelKey => $excelValue) {

                            if (!$excelFieldName[$excelValue]) continue;

                            if ($index == '0') {
                                $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                            }

                            switch ($excelValue) {
                                case 'orderNo':
                                    $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                                    break;
                                case 'sno':
                                    $tmpData[] = "<td>" . ($index + 1) . "</td>";
                                    break;
                                case 'managerNm':
                                case 'managerId':
                                case 'regDt':
                                case 'scmAdjustDt':
                                    $tmpData[] = "<td>" . $v[$excelValue] . "</td>";
                                    break;
                                case 'companyNm':
                                    $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scm']['name'] . "</td>";
                                    break;
                                case 'scmCode':
                                    $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scm']['code'] . "</td>";
                                    break;
                                case 'scmAdjustType':
                                    $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustType'] . "</td>";
                                    break;
                                case 'scmAdjustKind':
                                    $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustKind'] . "</td>";
                                    break;
                                case 'commission':
                                    $tmpData[] = "<td>" . $val[$excelValue] . "%</td>";
                                    break;
                                case 'scmAdjustState':
                                    $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustState'] . "</td>";
                                    break;
                                case 'scmAdjustCode':
                                    $tmpData[] = "<td>" . $adjustInfo[$k]['scmAdjustCode'] . "</td>";
                                    break;
                                case 'goodsPrice':
                                    $tmpData[] = "<td>" . ($val['goodsCnt'] * $val['goodsPrice']) . "</td>";
                                    break;
                                case 'optionPrice':
                                    $tmpData[] = "<td>" . ($val['goodsCnt'] * $val['optionPrice']) . "</td>";
                                    break;
                                case 'optionTextPrice':
                                    $tmpData[] = "<td>" . ($val['goodsCnt'] * $val['optionTextPrice']) . "</td>";
                                    break;
                                case 'optionInfo':
                                    // 옵션정보
                                    $tmpOptionInfo = [];
                                    if ($val['optionInfo']) {
                                        $tmpOption = gd_htmlspecialchars_stripslashes($val['optionInfo']);
                                        foreach ($tmpOption as $optionKey => $optionValue) {
                                            $tmpOptionInfo[] = $optionValue[0] . " : " . $optionValue[1];
                                        }
                                        unset($tmpOption);
                                    }
                                    if ($tmpOptionInfo) {
                                        $tmpData[] = "<td>" . implode("<br/>", $tmpOptionInfo) . "</td>";
                                    } else {
                                        $tmpData[] = "<td></td>";
                                    }
                                    unset($tmpOptionInfo);
                                    break;
                                case 'optionTextInfo':
                                    // 텍스트옵션정보
                                    $tmpOptionTextInfo = [];
                                    if ($val['optionTextInfo']) {
                                        $tmpOption = $val['optionTextInfo'];
                                        foreach ($tmpOption as $optionKey => $optionValue) {
                                            $tmpOptionTextInfo[] = gd_htmlspecialchars_stripslashes($optionValue[0]) . " : " . gd_htmlspecialchars_stripslashes($optionValue[1]);
                                        }
                                        unset($tmpOption);
                                    }
                                    if ($tmpOptionTextInfo) {
                                        $tmpData[] = "<td>" . implode("<br/>", $tmpOptionTextInfo) . "</td>";
                                    } else {
                                        $tmpData[] = "<td></td>";
                                    }
                                    unset($tmpOptionTextInfo);
                                    break;
                                default  :
                                    $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                                    break;
                            }

                        }
                        $tmpData[] = "</tr>";

                        if (count($tmpData)) $index++;

                        if ($val['addGoods']) {

                            foreach ($val['addGoods'] as $addGoodsKey => $addGoodsValue) {
                                $tmpData[] = "<tr>";

                                $addGoodsValue['price'] = $addGoodsValue['goodsCnt'] * ($addGoodsValue['goodsPrice']);
                                $addGoodsValue['adjustCommission'] = $addGoodsValue['addGoodsAdjustCommission'];
                                $addGoodsValue['adjustPrice'] = $addGoodsValue['addGoodsAdjustPrice'];

                                foreach ($excelField as $excelKey => $excelValue) {

                                    switch ($excelValue) {
                                        case 'orderNo':
                                        case 'memId':
                                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                                            break;
                                            break;
                                        case 'sno':
                                            $tmpData[] = "<td>" . ($index + 1) . "</td>";
                                            break;
                                        case 'orderStatusStr':
                                        case 'orderName':
                                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                                            break;
                                        case 'managerNm':
                                        case 'managerId':
                                        case 'regDt':
                                        case 'scmAdjustDt':
                                            $tmpData[] = "<td>" . $v[$excelValue] . "</td>";
                                            break;
                                        case 'companyNm':
                                            $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scm']['name'] . "</td>";
                                            break;
                                        case 'scmAdjustType':
                                            $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustType'] . "</td>";
                                            break;
                                        case 'scmAdjustKind':
                                            $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustKind'] . "</td>";
                                            break;
                                        case 'commission':
                                            $tmpData[] = "<td>" . $addGoodsValue[$excelValue] . "%</td>";
                                            break;
                                        case 'scmAdjustState':
                                            $tmpData[] = "<td>" . $convertData[$adjustSno][$k]['scmAdjustState'] . "</td>";
                                            break;
                                        case 'scmAdjustCode':
                                            $tmpData[] = "<td>" . $adjustInfo[$k]['scmAdjustCode'] . "</td>";
                                            break;
                                        default  :
                                            $tmpData[] = "<td>" . $addGoodsValue[$excelValue] . "</td>";
                                            break;
                                    }

                                }

                                $tmpData[] = "</tr>";
                                if (count($tmpData)) $index++;
                            }
                        }
                    }
                }
            }

            if (count($tmpData)) {
                $setHedData[] = "</tr>";
                $setData[] = array_merge($setHedData, $tmpData);

            }
        }

        return $setData;
    }


    /*
    * 주문 상품 정산요청 엑셀 다운로드
    */
    public function getScmScmAdjustOrder($whereCondition, $excelField, $excelFieldName)
    {

        $adjust = \App::load('\\Component\\Scm\\ScmAdjust');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 리스트 설정
        $whereCondition['statusMode'] = 's';

        $data = $adjust->getScmAdjustOrderList($whereCondition, $whereCondition['searchPeriod'], false)['data'];

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'orderNo':
                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'totalPrice':
                            $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                            $tmpData[] = "<td>" . $goodsPrice . "</td>";
                            break;
                        case 'commission':
                            $tmpData[] = "<td>" . $val[$excelValue] . "%</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
    * 배송비 정산요청 엑셀 다운로드
    */
    public function getScmScmAdjustDelivery($whereCondition, $excelField, $excelFieldName)
    {

        $adjust = \App::load('\\Component\\Scm\\ScmAdjust');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 리스트 설정
        $whereCondition['statusMode'] = 's';

        $data = $adjust->getScmAdjustDeliveryList($whereCondition, $whereCondition['searchPeriod'], false)['data'];

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'orderNo':
                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'totalPrice':
                            $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                            $tmpData[] = "<td>" . $goodsPrice . "</td>";
                            break;
                        case 'commission':
                            $tmpData[] = "<td>" . $val[$excelValue] . "%</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }


    /*
    * 정산 후 주문 상품 환불 정산  엑셀 다운로드
    */
    public function getScmScmAdjustAfterOrder($whereCondition, $excelField, $excelFieldName)
    {

        $adjust = \App::load('\\Component\\Scm\\ScmAdjust');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 리스트 설정
        $whereCondition['statusMode'] = 'r3';

        $data = $adjust->getScmAdjustAfterOrderList($whereCondition, $whereCondition['searchPeriod'], false)['data'];

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'orderNo':
                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'totalPrice':
                            $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                            $tmpData[] = "<td>" . $goodsPrice . "</td>";
                            break;
                        case 'commission':
                            $tmpData[] = "<td>" . $val[$excelValue] . "%</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
    * 정산 후 배송비 환불 정산  엑셀 다운로드
    */
    public function getScmScmAdjustAfterDelivery($whereCondition, $excelField, $excelFieldName)
    {

        $adjust = \App::load('\\Component\\Scm\\ScmAdjust');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 리스트 설정
        $whereCondition['statusMode'] = 'r3';

        $data = $adjust->getScmAdjustAfterDeliveryList($whereCondition, $whereCondition['searchPeriod'], false)['data'];

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'orderNo':
                            $tmpData[] = "<td style='mso-number-format:\"\@\";'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'totalPrice':
                            $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                            $tmpData[] = "<td>" . $goodsPrice . "</td>";
                            break;
                        case 'commission':
                            $tmpData[] = "<td>" . $val[$excelValue] . "%</td>";
                            break;
                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }


    /*
    * 주문목록
    */
    public function getOrderList($whereCondition, $excelField, $defaultField, $excelFieldName)
    {
        $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');

        $excelField = explode(STR_DIVISION, $excelField);

        $orderType = "order";
        $isGlobal = false;

        foreach ($excelField as $k => $v) {
            if ($defaultField[$v]['orderFl'] != 'y') {
                $orderType = "goods";
            }
            if (strpos($v, 'global_') !== false) {
                $isGlobal = true;
                $globalFiled[] = str_replace("global_","",$v);
            }

            //임의의 추가항목이 있을 경우.
            $defaultAddField = '{addFieldNm}_';
            if (strstr($v, $defaultAddField)) {
                $tmpAddFieldName = str_replace($defaultAddField, '', $v);
                $excelFieldName[$v]['name'] =  $tmpAddFieldName;
            }
        }

        //if(gd_isset($whereCondition['periodFl']) === null) $whereCondition['periodFl'] = "-1";
        gd_isset($whereCondition['periodFl'], 7);

        $userHandleMode = ['order_list_user_exchange', 'order_list_user_return', 'order_list_user_refund'];
        if (in_array($this->fileConfig['location'], $userHandleMode)) {
            $whereCondition['userHandleMode'] = $whereCondition['statusMode'];
            unset($whereCondition['statusMode']);
            $isUserHandle = true;
        } else {
            $isUserHandle = false;
        }

        // --- 검색 설정
        $orderData = $orderAdmin->getOrderListForAdminExcel($whereCondition, $whereCondition['periodFl'], $isUserHandle, $orderType,$excelField,0,$this->excelPageNum);

        //튜닝한 업체를 위해 데이터 맞춤
        if(empty($orderData['totalCount']) === true && empty($orderData['orderList']) === true) {
            $isGenerator = false;
            $totalNum = count($orderData);
            if ($this->excelPageNum) $orderList= array_chunk($orderData, $this->excelPageNum, true);
            else $orderList = array_chunk($orderData, $totalNum, true);
        } else {
            $isGenerator = true;
            $totalNum = count($orderData['totalCount']);
            $orderList = $orderData['orderList'];
            if(gd_is_provider() && $orderType =='goods') {
                $totalScmInfo = $orderData['totalScmInfo'];
            }
        }
        unset($goodsData);

        //값이 입력되지 않은 것으로 간주하고 전체 다운로드.
        if ($this->excelPageNum == 0) {
            $pageNum = 0;
        } else if ($this->excelPageNum >= $totalNum) {
            $pageNum = 0;
        } else {
            $pageNum = ceil($totalNum / $this->excelPageNum) - 1;
        }

        $setHedData[] = "<tr>";


        $arrTag = [
            'orderGoodsNm',
            'goodsNm',
            'orderGoodsNmStandard',
            'goodsNmStandard',
        ];

        for ($i = 0; $i <= $pageNum; $i++) {

            $fileName = $this->fileConfig['location'] . array_sum(explode(' ', microtime()));
            $tmpFilePath = UserFilePath::data('excel', $this->fileConfig['menu'],  $fileName . ".xls")->getRealPath();

            $fh = fopen($tmpFilePath, 'a+');
            fwrite($fh, $this->excelHeader."<table border='1'>");

            if($isGenerator) {
                if($i == '0') {
                    $data = $orderList;
                }  else {
                    $data = $orderAdmin->getOrderListForAdminExcel($whereCondition, $whereCondition['periodFl'], $isUserHandle, $orderType,$excelField,$i,$this->excelPageNum)['orderList'];
                }
            } else {
                $data = $orderList[$i];
            }

            foreach($data as $key => $val) {
                $progress = round((100 / ($totalNum- 1)) * ($key+($i*$this->excelPageNum)));
                if ($progress%20  == 0  || $progress == '100') {
                    echo "<script> parent.progressExcel('" .gd_isset($progress,0) . "'); </script>";
                }

                if($val['orderGoodsNmStandard']) {
                    list($val['orderGoodsNm'],$val['orderGoodsNmStandard']) =[$val['orderGoodsNmStandard'],$val['orderGoodsNm']];
                }

                if($val['goodsNmStandard']) {
                    list($val['goodsNm'],$val['goodsNmStandard']) =[$val['goodsNmStandard'],$val['goodsNm']];
                }

                if($whereCondition['statusMode'] === 'o'){
                    // 입금대기리스트에서 '주문상품명' 을 입금대기 상태의 주문상품명만 구성
                    $noPay = (int)$val['noPay'] - 1;
                    if(trim($val['goodsNm']) !== '') {
                        if ($noPay > 0) {
                            $val['orderGoodsNm'] = $val['goodsNm'] . ' 외 ' . $noPay . ' 건';
                        } else {
                            $val['orderGoodsNm'] = $val['goodsNm'];
                        }
                    }
                    if(trim($val['goodsNmStandard']) !== '') {
                        if($noPay > 0){
                            $val['orderGoodsNmStandard'] = $val['goodsNmStandard'] . ' ' . __('외') . ' ' . $noPay . ' ' . __('건');
                        }
                        else {
                            $val['orderGoodsNmStandard'] = $val['goodsNmStandard'];
                        }
                    }
                }

                if($isGlobal && $val['mallSno'] != $this->gGlobal['defaultMallSno']) {
                    $val['currencyPolicy'] = json_decode($val['currencyPolicy'], true);
                    $val['exchangeRatePolicy'] = json_decode($val['exchangeRatePolicy'], true);
                    $val['currencyIsoCode'] = $val['currencyPolicy']['isoCode'];
                    $val['exchangeRate'] = $val['exchangeRatePolicy']['exchangeRate' . $val['currencyPolicy']['isoCode']];

                    foreach($globalFiled as $globalKey =>$globalValue) {
                        $val["global_".$globalValue] = NumberUtils::globalOrderCurrencyDisplay($val[$globalValue], $val['exchangeRate'], $val['currencyPolicy']);
                    }
                }

                if($val['refundBankName']) {
                    $_tmpRefundAccount[] = $val['refundBankName'];
                    $_tmpRefundAccountNumber[] = $val['refundBankName'];
                }
                if ($val['refundAccountNumber'] && gd_str_length($val['refundAccountNumber']) > 50) {
                    $val['refundAccountNumber'] = \Encryptor::decrypt($val['refundAccountNumber']);
                }
                if ($val['userRefundAccountNumber'] && gd_str_length($val['userRefundAccountNumber']) > 50) {
                    $val['userRefundAccountNumber'] = \Encryptor::decrypt($val['userRefundAccountNumber']);
                }
                if($val['refundAccountNumber']) {
                    $_tmpRefundAccount[] = $val['refundAccountNumber'];
                }
                if($val['userRefundAccountNumber']) {
                    $_tmpRefundAccountNumber[] = $val['userRefundAccountNumber'];
                }

                if($val['refundDepositor']) {
                    $_tmpRefundAccount[] = $val['refundDepositor'];
                    $_tmpRefundAccountNumber[] = $val['refundDepositor'];
                }

                if(empty($_tmpRefundAccount) == false) {
                    $val['refundAccountNumber'] = implode(STR_DIVISION, $_tmpRefundAccount);
                    unset($_tmpRefundAccount);
                }
                if(empty($_tmpRefundAccountNumber) == false) {
                    $val['userRefundAccountNumber'] = implode(STR_DIVISION, $_tmpRefundAccountNumber);
                    unset($_tmpRefundAccountNumber);
                }

                // 상품 무게 및 용량
                if($val['goodsWeight'] || $val['goodsVolume']) {
                    $val['g_goodsWeight'] = ($val['goodsWeight'] === '0.00') ? '0' : number_format((float) $val['goodsWeight'] / $val['goodsCnt'], 2, '.', '');
                    $val['g_goodsVolume'] = ($val['goodsVolume'] === '0.00') ? '0' : number_format((float) $val['goodsVolume'] / $val['goodsCnt'], 2, '.', '');
                    $val['goodsWeightVolume'] = $val['g_goodsWeight'].gd_isset(Globals::get('gWeight.unit'),'kg').'|'.$val['g_goodsVolume'].gd_isset(Globals::get('gVolume.unit'),'㎖');

                    // 총 무게 및 용량
                    $val['goodsWeight'] = ($val['goodsWeight'] === '0.00') ? '0' : $val['goodsWeight'];
                    $val['goodsVolume'] = ($val['goodsVolume'] === '0.00') ? '0' : $val['goodsVolume'];
                    $val['goodsTotalWeightVolume'] = $val['goodsWeight'].gd_isset(Globals::get('gWeight.unit'),'kg').'|'.$val['goodsVolume'].gd_isset(Globals::get('gVolume.unit'),'㎖');
                }

                if(gd_is_provider()) {
                    if($orderType =='goods') {
                        $val['scmOrderCnt'] = $totalScmInfo[$val['orderNo']]['scmOrderCnt'];
                        $val['scmGoodsCnt'] = $totalScmInfo[$val['orderNo']]['scmGoodsCnt'];
                        $val['scmGoodsNm'] = $totalScmInfo[$val['orderNo']]['scmGoodsNm'];
                        $val['totalGoodsPrice'] = $totalScmInfo[$val['orderNo']]['totalGoodsPrice'];
                        $tmpScmDeliveryCharge = explode(STR_DIVISION,$totalScmInfo[$val['orderNo']]['scmDeliveryCharge']);
                        $tmpScmDeliverySno =  explode(STR_DIVISION,$totalScmInfo[$val['orderNo']]['scmDeliverySno']);
                        $val['scmDeliveryCharge'] = array_sum(array_combine($tmpScmDeliverySno,$tmpScmDeliveryCharge));
                    } else {
                        $tmpScmDeliveryCharge = explode(STR_DIVISION,$val['scmDeliveryCharge']);
                        $tmpScmDeliverySno =  explode(STR_DIVISION,$val['scmDeliverySno']);
                        $val['scmDeliveryCharge'] = array_sum(array_combine($tmpScmDeliverySno,$tmpScmDeliveryCharge));
                    }
                    $val['scmGoodsNm'] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes(StringUtils::stripOnlyTags($val['scmGoodsNm'])));
                    unset($tmpScmDeliveryCharge);
                    unset($tmpScmDeliverySno);
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if (in_array($excelValue, $arrTag)) {
                        $val[$excelValue] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes(StringUtils::stripOnlyTags($val[$excelValue])));
                    }

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    $tmpOptionInfo = [];
                    $tmpOptionCode = [];
                    if ($val['optionInfo']) {
                        $tmpOption = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']), true);
                        foreach ($tmpOption as $optionKey => $optionValue) {
                            if ($optionValue[2]) $tmpOptionCode[] = $optionValue[2];
                            $tmpOptionInfo[] = $optionValue[0] . " : " . $optionValue[1];
                        }
                        unset($tmpOption);
                    }

                    switch ($excelValue) {
                        case 'orderGoodsCnt':
                            if(gd_is_provider()) {
                                $tmpData[]="<td>".$val['scmOrderCnt'] . "</td>";
                            } else {
                                $tmpData[]="<td>".$val[$excelValue] . "</td>";
                            }
                            break;
                        case 'orderGoodsNm':
                            if(gd_is_provider()) {
                                if($val['scmOrderCnt']==1) {
                                    $tmpData[]="<td>".$val['scmGoodsNm'] ."</td>";
                                } else {
                                    $tmpData[]="<td>".$val['scmGoodsNm'] ." 외 ". ($val['scmOrderCnt']-1) . " 건</td>";
                                }
                            } else {
                                $tmpData[]="<td>".$val[$excelValue] . "</td>";
                            }

                            break;
                        case 'totalSettlePrice':
                            if(gd_is_provider()) {
                                $tmpData[] = "<td>". ($val['totalOrderGoodsPrice']+$val['scmDeliveryCharge']) . "</td>";
                            } else {
                                if($val['orderChannelFl'] === 'naverpay'){
                                    $checkoutData = json_decode($val['checkoutData'], true);
                                    $tmpData[] = "<td>" . $checkoutData['orderData']['GeneralPaymentAmount'] . " </td>";
                                }
                                else {
                                    $tmpData[] = "<td>" . $val['totalRealSettlePrice'] . " </td>";
                                }
                            }
                            break;
                        case 'totalDeliveryCharge':
                            if(gd_is_provider()) {
                                $tmpData[] = "<td>" .  $val['scmDeliveryCharge'] . "</td>";
                            } else {
                                $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            }
                            break;
                        case 'goodsDeliveryCollectFl':
                            if($val['goodsDeliveryCollectFl'] =='pre') {
                                $tmpData[] = "<td>선불</td>";
                            } else {
                                $tmpData[] = "<td>착불</td>";
                            }
                            break;
                        case 'mallSno':
                            $tmpData[] = "<td>" . $this->gGlobal['mallList'][$val['mallSno']]['mallName'] . "</td>";
                            break;
                        case 'hscode':

                            if ($val['hscode']) {

                                $hscode = json_decode(gd_htmlspecialchars_stripslashes($val['hscode']), true);

                                if ($hscode) {
                                    foreach ($hscode as $k1 => $v1) {
                                        $_tmpHsCode[] = $k1 . " : " . $v1;
                                    }

                                    $tmpData[] = "<td>" . implode("<br>", $_tmpHsCode) . "</td>";
                                    unset($_tmpHsCode);

                                } else {
                                    $tmpData[] = "<td></td>";
                                }

                                unset($hscode);

                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'addField':
                            unset($excelAddField);
                            $addFieldData = json_decode($val[$excelValue], true);
                            $excelAddField[] = "<td><table border='1'><tr>";
                            foreach ($addFieldData as $addFieldKey => $addFieldVal) {
                                if ($addFieldVal['process'] == 'goods') {
                                    foreach ($addFieldVal['data'] as $addDataKey => $addDataVal) {
                                        $goodsVal = $addDataVal;
                                        if ($addFieldVal['type'] == 'text' && $addFieldVal['encryptor'] == 'y') {
                                            $goodsVal = Encryptor::decrypt($goodsVal);
                                        }
                                        $excelAddField[] = "<td>" . $addFieldVal['name'] . " (" . $addFieldVal['goodsNm'][$addDataKey] . ") : " . $goodsVal . "</td>";
                                    }
                                } else {
                                    $excelAddField[] = "<td>" . $addFieldVal['name'] . " : ";
                                    $goodsVal = $addFieldVal['data'];
                                    if ($addFieldVal['type'] == 'text' && $addFieldVal['encryptor'] == 'y') {
                                        $goodsVal = Encryptor::decrypt($goodsVal);
                                    }
                                    $excelAddField[] = $goodsVal;
                                    $excelAddField[] = "</td>";
                                }
                            }
                            if ($val['orderChannelFl'] == 'naverpay') {
                                $checkoutData = json_decode($val['checkoutData'], true);
                                if (empty($checkoutData['orderGoodsData']['IndividualCustomUniqueCode']) === false) {
                                    $excelAddField[] = "<td> 개인통관 고유번호(네이버) : " . $checkoutData['orderGoodsData']['IndividualCustomUniqueCode'] . "</td>";
                                }
                            }
                            if ($val['orderChannelFl'] == 'payco') {
                                $paycoDataField = empty($val['fintechData']) === false ? 'fintechData' : 'checkoutData';
                                if (empty($val[$paycoDataField]) === false) {
                                    $paycoData = json_decode($val[$paycoDataField], true);
                                    if ($paycoData['individualCustomUniqNo']) {
                                        $excelAddField[] = "<td> 개인통관 고유번호(페이코) : " . $paycoData['individualCustomUniqNo'] . "</td>";
                                    }
                                }
                            }
                            $excelAddField[] = "</tr></table></td>";
                            $tmpData[] = implode('', $excelAddField);
                            break;
                        case 'goodsType':
                            if ($val['goodsType'] == 'addGoods') {
                                $tmpData[] = "<td>추가</td>";
                            } else {
                                $tmpData[] = "<td>일반</td>";
                            }

                            break;
                        case 'goodsNm':
                            if ($val['goodsType'] == 'addGoods') {
                                $tmpData[] = "<td><span style='color:red'>[" . __('추가') . "]</span>" . $val[$excelValue] . "</td>";
                            } else {
                                $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            }

                            break;
                        case 'optionInfo':
                            if ($tmpOptionInfo) {
                                $tmpData[] = "<td>" . implode("<br/>", $tmpOptionInfo) . "</td>";
                            } else {
                                $tmpData[] = "<td></td>";
                            }
                            break;
                        case 'optionCode':
                            if ($tmpOptionCode) {
                                $tmpData[] = "<td class='xl24'>" . implode("<br/>", $tmpOptionCode) . "</td>";
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'optionTextInfo':
                            $tmpOptionTextInfo = [];
                            if ($val[$excelValue]) {
                                $tmpOption = json_decode($val[$excelValue], true);
                                foreach ($tmpOption as $optionKey => $optionValue) {
                                    //$tmpOptionTextInfo[] = $optionValue[0] . " : " . $optionValue[1] . " / " . __('옵션가') . " : " . $optionValue[2];
                                    $tmpOptionTextInfo[] = gd_htmlspecialchars_stripslashes($optionValue[0]) . " : " . gd_htmlspecialchars_stripslashes($optionValue[1]);
                                }
                            }
                            if ($tmpOptionTextInfo) {
                                $tmpData[] = "<td>" . implode("<br/>", $tmpOptionTextInfo) . "</td>";
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            unset($tmpOptionTextInfo);
                            unset($tmpOption);
                            break;
                        case 'orderStatus':
                            $tmpData[] = "<td>" . $orderAdmin->getOrderStatusAdmin($val[$excelValue]) . "</td>";
                            break;
                        case 'settleKind':
                            $tmpData[] = "<td>" . $orderAdmin->printSettleKind($val[$excelValue]) . "</td>";
                            break;
                        // 숫자 처리 - 주문 번호 및 우편번호(5자리) 숫자에 대한 처리
                        case 'orderNo':
                        case 'apiOrderNo':
                        case 'apiOrderGoodsNo':
                        case 'receiverZonecode':
                        case 'invoiceNo':
                        case 'pgTid':
                        case 'pgAppNo':
                        case 'pgResultCode':
                        case 'orderPhone':
                        case 'orderCellPhone':
                        case 'receiverPhone':
                            $tmpData[] = "<td class=\"xl24\">" . $val[$excelValue] . "</td>";
                            break;

                        case 'totalGift':
                        case 'ogi.presentSno':
                        case 'ogi.giftNo':
                            $gift = $orderAdmin->getOrderGift($val['orderNo'], $val['scmNo'], 40);
                            $presentTitle = [];
                            $giftInfo = [];
                            $totalGift = [];
                            if ($gift) {
                                foreach ($gift as $gk => $gv) {
                                    $presentTitle[] = $gv['presentTitle'];
                                    $giftInfo[] = $gv['giftNm'] . INT_DIVISION . $gv['giveCnt'] . "개";
                                    $totalGift[] = $gv['presentTitle'] . INT_DIVISION . $gv['giftNm'] . INT_DIVISION . $gv['giveCnt'] . "개";
                                }

                                if ($excelValue == 'ogi.presentSno') {
                                    $tmpData[] = "<td>" . implode("<br>", $presentTitle) . "</td>";
                                } else if ($excelValue == 'ogi.giftNo') {
                                    $tmpData[] = "<td>" . implode("<br>", $giftInfo) . "</td>";
                                } else if ($excelValue == 'totalGift') {
                                    $tmpData[] = "<td>" . implode("<br>", $totalGift) . "</td>";
                                }
                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;

                        case 'memNo':
                            $tmpData[] = "<td class=\"xl24\">" . $val['memId'] . "</td>";
                            break;
                        case 'receiverAddressTotal':
                            $receiverAddress = $val['receiverAddress'] . " " . $val['receiverAddressSub'];
                            if ($val['deliveryMethodFl'] == 'visit' && empty(trim($val['visitAddress'])) === false) $receiverAddress = $val['visitAddress'];
                            if($val['mallSno'] != $this->gGlobal['defaultMallSno']) {
                                $countriesCode = $orderAdmin->getCountriesList();
                                $countriesCode = array_combine (array_column($countriesCode, 'code'), array_column($countriesCode, 'countryNameKor'));
                                $tmpData[] = "<td>" .$countriesCode[$val['receiverCountryCode']]." ". $val['receiverCity'] . " ". $val['receiverState'] . " ". $receiverAddress . "</td>";
                            } else {
                                $tmpData[] = "<td>" . $receiverAddress . "</td>";
                            }
                            break;
                        case 'receiverAddress':
                            $receiverAddress = $val['receiverAddress'];
                            if ($val['deliveryMethodFl'] == 'visit' && empty(trim($val['visitAddress'])) === false) $receiverAddress = $val['visitAddress'];
                            $tmpData[] = "<td class='xl24'>" . $receiverAddress . "</td>";
                            break;
                        case 'receiverAddressSub':
                            $receiverAddressSub = $val['receiverAddressSub'];
                            if ($val['deliveryMethodFl'] == 'visit') $receiverAddressSub = '';
                            $tmpData[] = "<td class='xl24'>" . $receiverAddressSub . "</td>";
                            break;
                        case 'receiverName':
                            $receiverName = $val['receiverName'];
                            if ($val['deliveryMethodFl'] == 'visit' && empty(trim($val['visitName'])) === false) $receiverName = $val['visitName'];
                            $tmpData[] = "<td class='xl24'>" . $receiverName . "</td>";
                            break;
                        case 'receiverCellPhone':
                            $receiverCellPhone = $val['receiverCellPhone'];
                            if ($val['deliveryMethodFl'] == 'visit' && empty(trim($val['visitPhone'])) === false) $receiverCellPhone = $val['visitPhone'];
                            $tmpData[] = "<td class='xl24'>" . $receiverCellPhone . "</td>";
                            break;
                        case 'addGoodsNo':

                            $addGoods = $orderAdmin->getOrderAddGoods(
                                $val['orderNo'],
                                $val['orderCd'],
                                [
                                    'sno',
                                    'addGoodsNo',
                                    'goodsNm',
                                    'goodsCnt',
                                    'goodsPrice',
                                    'optionNm',
                                    'goodsImage',
                                    'addMemberDcPrice',
                                    'addMemberOverlapDcPrice',
                                    'addCouponGoodsDcPrice',
                                    'addGoodsMileage',
                                    'addMemberMileage',
                                    'addCouponGoodsMileage',
                                    'divisionAddUseDeposit',
                                    'divisionAddUseMileage',
                                    'divisionAddCouponOrderDcPrice',
                                ]
                            );

                            $addGoodsInfo = [];
                            if ($addGoods) {
                                foreach ($addGoods as $av => $ag) {
                                    $addGoodsInfo[] = $ag['goodsNm'];
                                }
                                $tmpData[] = "<td>" . implode("<br>", $addGoodsInfo) . "</td>";

                            } else {
                                $tmpData[] = "<td></td>";
                            }

                            break;
                        case 'orderMemo' :
                            if($val['orderChannelFl'] == 'naverpay'){
                                $checkoutData= json_decode($val['checkoutData'], true);
                                $tmpData[] = "<td>" . $checkoutData['orderGoodsData']['ShippingMemo'] . "</td>";
                            }
                            else {
                                $orderMemo = $val['orderMemo'];
                                if ($val['deliveryMethodFl'] == 'y') $orderMemo = $val['visitMemo'];
                                $tmpData[] = "<td>" . $orderMemo . "</td>";
                            }
                            break;

                        case 'packetCodeFl' :
                            $tmpData[] = (trim($val['packetCode'])) ? "<td>y</td>" : "<td>n</td>";
                            break;

                        //복수배송지 배송지
                        case 'multiShippingOrder' :
                            $multiShippingName = ((int)$val['orderInfoCd'] === 1) ? '메인 배송지' : '추가' . ((int)$val['orderInfoCd'] - 1) . ' 배송지';
                            $tmpData[] = "<td>" . $multiShippingName . "</td>";
                            break;

                        //복수배송지 배송지별배송비
                        case 'multiShippingPrice' :
                            $tmpData[] = "<td>" . $val['deliveryCharge'] . "</td>";
                            break;

                        // 안심번호 (사용하지 않을경우 휴대폰번호 노출)
                        case 'receiverSafeNumber':
                            if ($val['receiverUseSafeNumberFl'] == 'y' && empty($val['receiverSafeNumber']) == false && empty($val['receiverSafeNumberDt']) == false && DateTimeUtils::intervalDay($val['receiverSafeNumberDt'], date('Y-m-d H:i:s')) <= 30) {
                                $tmpData[] = "<td class='xl24'>" . $val['receiverSafeNumber'] . "</td>";
                            } else {
                                $tmpData[] = "<td class='xl24'>" . $val['receiverCellPhone'] . "</td>";
                            }

                            break;
                        case 'userHandleInfo':
                            $userHandleInfo = '';
                            if ($whereCondition['userHandleViewFl'] != 'y') {
                                $userHandleInfo = $orderAdmin->getUserHandleInfo($val['orderNo'], $val['orderGoodsSno'])[0];
                            }
                            $tmpData[] = "<td>" . $userHandleInfo . "</td>";

                            break;
                        case 'goodsCnt' :
                            $tmpOrderCnt = $val[$excelValue];
                            if ($whereCondition['optionCountFl'] === 'per') { $tmpOrderCnt = 1; }
                            $tmpData[] = "<td>" . $tmpOrderCnt . "</td>";
                            break;
                        case 'orderChannelFl':
                            $channel = $val[$excelValue];
                            if ($val['trackingKey']) $channel .= '<br />페이코쇼핑';
                            $tmpData[] = "<td>" . $channel . "</td>";
                            break;
                        case 'orderTypeFl':
                            // 주문유형
                            if ($val['orderTypeFl'] == 'pc') {
                                $tmpData[] = "<td>PC쇼핑몰</td>";
                            } else if ($val['orderTypeFl'] == 'mobile') {
                                if (empty($val['appOs']) === true && empty($val['pushCode']) === true) {
                                    $tmpData[] = "<td>모바일쇼핑몰 - WEB</td>";
                                } else {
                                    $tmpData[] = "<td>모바일쇼핑몰 - APP</td>";
                                }
                            } else {
                                $tmpData[] = "<td>수기주문</td>";
                            }

                            break;
                        case 'totalGoodsPriceByGoods':
                            $tmpData[] = "<td>".(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'])."</td>";
                            break;
                        case 'goodsPriceWithOption':
                            $tmpData[] = "<td>".($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])."</td>";
                            break;
                        case 'useCouponNm': // 사용된 쿠폰명
                            if($val['couponGoodsDcPrice'] <= 0) {
                                $tmpData[] = '<td></td>';
                                break;
                            }
                            if($val['goodsType'] == 'addGoods') {
                                $tmpData[] = '<td>'.$tmpCouponNm.'</td>';
                                break;
                            }
                            $tmpGoodsCouponNmText = $tmpOrderCouponNmText = $tmpDeliveryCouponNmText = [];
                            $orderCouponData = $orderAdmin->getOrderCoupon($val['orderNo']);
                            foreach($orderCouponData as $couponVal) {
                                $couponBenefitType = ($couponVal['couponBenefitType'] == 'fix') ? gd_currency_default() : '%';
                                // 상품 쿠폰
                                if ($val['couponGoodsDcPrice'] > 0) {
                                    if ($couponVal['couponKindType'] == 'sale' && $couponVal['couponUseType'] == 'product') {
                                        $orderGoodsStatusMode = substr($val['orderStatus'], 0, 1); // 주문상품상태값
                                        $orderCancelFl = false; // 주문 교환/환불/취소/반품 구분
                                        if($orderGoodsStatusMode == 'c' || $orderGoodsStatusMode == 'e' || $orderGoodsStatusMode == 'r' || $orderGoodsStatusMode == 'b') {
                                            $orderCancelFl = true;
                                        }
                                        if ($val['goodsNo'] == $couponVal['goodsNo'] && ($val['orderCd'] == $couponVal['orderCd']) || $orderCancelFl) {
                                            $tmpGoodsCouponNmText[] = $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 할인";
                                        }
                                    }
                                }
                                // 주문적용 쿠폰
                                if($val['totalCouponOrderDcPrice'] > 0 || $val['divisionCouponOrderDcPrice'] > 0) {
                                    if ($couponVal['couponKindType'] == 'sale' && $couponVal['couponUseType'] == 'order') {
                                        if ($val['divisionCouponOrderDcPrice'] > 0) { // 주문적용 쿠폰인 경우
                                            $tmpOrderCouponNmText[] = $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 할인";
                                        }
                                    }
                                }
                                // 배송비 쿠폰
                                if($val['totalCouponDeliveryDcPrice'] > 0) { // 배송비적용 쿠폰
                                    if ($couponVal['couponKindType'] == 'delivery' || $couponVal['couponUseType'] == 'delivery') {
                                        $tmpDeliveryCouponNmText[] = $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 할인";
                                    }
                                }
                            }
                            $tmpCouponNmText = array_merge($tmpGoodsCouponNmText, array_merge($tmpOrderCouponNmText, $tmpDeliveryCouponNmText));
                            $tmpCouponNm = implode('<br/>', $tmpCouponNmText);
                            $tmpData[] = '<td>'.$tmpCouponNm.'</td>';
                            break;
                        case 'goodsDiscountInfo':
                            if($val['goodsDcPrice'] > 0) {
                                $goodsDiscountInfo = json_decode($val[$excelValue], true);
                                $arrDiscountGroup = array('member' => '| 회원전용 |', 'group' => '| 특정회원등급 |');
                                $arrNewGoodsReg = array('regDt' => '등록일', 'modDt' => '수정일');
                                $arrNewGoodsDate = array('day' => '일', 'hour' => '시간');
                                $benefitNm = '개별설정 ';
                                if(empty($goodsDiscountInfo['benefitNm']) == false && $goodsDiscountInfo != null) {
                                    $benefitNm = $goodsDiscountInfo['benefitNm'];
                                }
                                $divisionText = ''; // 기간할인 구분자 초기화
                                if(!$arrDiscountGroup[$goodsDiscountInfo['goodsDiscountGroup']]) $divisionText = " | " ;  // 배열에 값이 없을 경우 기간할인 구분자 삽입
                                if($goodsDiscountInfo['goodsDiscountUnit'] == 'price') $goodsDiscountPricePrint = ' | ' . gd_currency_symbol() . gd_money_format($goodsDiscountInfo['goodsDiscount']) . gd_currency_string();
                                else $goodsDiscountPricePrint = ' | ' . $goodsDiscountInfo['goodsDiscount'] . '%';
                                if($goodsDiscountInfo['benefitUseType'] == 'nonLimit') { // 제한 없음
                                    $benefitPeriod = '';
                                } else if($goodsDiscountInfo['benefitUseType'] == 'newGoodsDiscount') { // 등록일 기준
                                    $benefitPeriod = $divisionText . ' 상품' . $arrNewGoodsReg[$goodsDiscountInfo['newGoodsRegFl']] . '부터 ' . $goodsDiscountInfo['newGoodsDate'] . $arrNewGoodsDate[$goodsDiscountInfo['newGoodsDateFl']] . '까지';
                                } else { // 기간 제한
                                    $benefitPeriod = $divisionText . " " . gd_date_format("Y-m-d H:i", $goodsDiscountInfo['periodDiscountStart']) . ' ~ ' . gd_date_format("Y-m-d H:i", $goodsDiscountInfo['periodDiscountEnd']);
                                }
                                if (empty($goodsDiscountInfo) === false || $goodsDiscountInfo != null) {
                                    $tmpGoodsDiscountInfoText[] = $benefitNm . $benefitPeriod . $goodsDiscountPricePrint . "할인";
                                } else {
                                    $tmpGoodsDiscountInfoText[] = '상품 할인 | ' . gd_money_format($val['goodsDcPrice']) . gd_currency_default() . "할인";
                                }
                            }
                            $tmpGoodsDiscountInfo = implode('<br/>', $tmpGoodsDiscountInfoText);
                            $tmpData[] = '<td>'.$tmpGoodsDiscountInfo.'</td>';
                            unset($tmpGoodsDiscountInfoText);
                            break;
                        case 'memberPolicy':
                            $orderMemberPolicy = json_decode($val[$excelValue], true);
                            $arrMemberPolicyFixedOrderType = array('option' => '옵션별', 'goods' => '상품별', 'order' => '주문별', 'brand' => '브랜드별');
                            if ($orderMemberPolicy['fixedOrderTypeDc'] == 'brand') {
                                //회원등급 > 브랜드별 추가할인 상품 브랜드 정보
                                if (in_array($val['brandCd'], $orderMemberPolicy['dcBrandInfo']['cateCd'])) {
                                    $goodsBrandInfo[$val['goodsNo']][$val['brandCd']] = $val['brandCd'];
                                } else {
                                    if ($val['brandCd']) {
                                        $goodsBrandInfo[$val['goodsNo']]['allBrand'] = $val['brandCd'];
                                    } else {
                                        $goodsBrandInfo[$val['goodsNo']]['noBrand'] = $val['brandCd'];
                                    }
                                }
                                // 무통장결제 중복 할인 설정 체크에 따른 할인율
                                foreach ($goodsBrandInfo[$val['goodsNo']] as $gKey => $gVal) {
                                    foreach ($orderMemberPolicy['dcBrandInfo']['cateCd'] AS $mKey => $mVal) {
                                        if ($gKey == $mVal) {
                                            $orderMemberPolicy['dcPercent'] = ($orderMemberPolicy['dcBrandInfo']['goodsDiscount'][$mKey]);
                                        }
                                    }
                                }
                            }
                            if($val['orgMemberDcPrice'] > 0) {
                                if(empty($orderMemberPolicy) == false) {
                                    $tmpMemberPolicyText[] = $orderMemberPolicy['groupNm'] . " : " . $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeDc']] . " 구매금액 " . gd_currency_display($orderMemberPolicy['dcLine']) . " 이상 " .
                                        $orderMemberPolicy['dcPercent'] . "% 추가 할인";
                                } else {
                                    $tmpMemberPolicyText[] = "회원 추가 할인 : " . gd_money_format($val['orgMemberDcPrice']) . gd_currency_default() . "할인";
                                }
                            }
                            if($val['orgMemberOverlapDcPrice'] > 0) {
                                if(empty($orderMemberPolicy) == false) {
                                    $tmpMemberPolicyText[] = $orderMemberPolicy['groupNm'] . " : " . $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeOverlapDc']] . " 구매금액 " . gd_currency_display($orderMemberPolicy['overlapDcLine']) . " 이상 " .
                                        $orderMemberPolicy['overlapDcPercent'] . "% 중복 할인";
                                } else {
                                    $tmpMemberPolicyText[] = "회원 중복 할인 : " . gd_money_format($val['orgMemberOverlapDcPrice']) . gd_currency_default() . "할인";
                                }
                            }
                            $tmpMemberPolicy = implode('<br/>', $tmpMemberPolicyText);
                            $tmpData[] = '<td>'.$tmpMemberPolicy.'</td>';
                            unset($tmpMemberPolicyText);
                            break;
                        default  :
                            if ($excelFieldName[$excelValue]['type'] == 'price' && $val[$excelValue] != '') {
                                $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            } else if ($excelFieldName[$excelValue]['type'] == 'mileage' && $val[$excelValue] != '') {
                                $tmpData[] = "<td>" . number_format($val[$excelValue]) . $this->mileageGiveInfo['basic']['unit'] . "</td>";
                            } else if ($excelFieldName[$excelValue]['type'] == 'deposit' && $val[$excelValue] != '') {
                                $tmpData[] = "<td>" . number_format($val[$excelValue]) . $this->depositInfo['unit'] . "</td>";
                            } else {
                                $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            }
                            break;
                    }

                    unset($tmpOptionInfo);
                    unset($tmpOptionCode);

                }
                $tmpData[] = "</tr>";

                if ($key == '0') {
                    fwrite($fh, implode(chr(10), $setHedData));
                    unset($setHedData);
                }

                if ($whereCondition['optionCountFl'] === 'per') {
                    $tmpGoodsCnt = ($val['goodsCnt'] == '0' || empty($val['goodsCnt']) === true) ? 1 : $val['goodsCnt'];
                    for ($tmpDataCnt = 1; $tmpDataCnt <= $tmpGoodsCnt; $tmpDataCnt++) {
                        fwrite($fh, implode(chr(10), $tmpData));
                    }
                } else {
                    fwrite($fh, implode(chr(10), $tmpData));
                }
                unset($tmpData);
            }
//exit;
            fwrite($fh, "</table>");
            fwrite($fh,  $this->excelFooter);
            fclose($fh);

            $this->fileConfig['fileName'][] = $fileName . ".xls";
        }




        return true;
    }

    /*
    * 발행 요청 리스트
    */
    public function getOrderTaxInvoiceRequest($whereCondition, $excelField, $excelFieldName)
    {
        $tax = \App::load('\\Component\\Order\\Tax');
        $excelField = explode(STR_DIVISION, $excelField);
        $taxInvoiceStr = ['t' => __('과세'), 'f' => __('면세')];
        $rowspanField = ['totalPrice', 'price', 'vat', 'reTotalPrice', 'tax'];
        if (empty(array_intersect($rowspanField, $excelField)) === true) {
            $rowspanFix = 1;
        }

        if (empty($whereCondition['orderNo']) === false) {
            foreach ($whereCondition as $key => $value) {
                if (in_array($key, ['orderNo', 'pageNum'])) continue;
                unset($whereCondition[$key]);
            }
        }
        $whereCondition['statusFl'] = 'r';
        $data = $tax->getDataExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";

        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {
                $rowspan = $rowspanFix ?? count($val['taxInvoiceInfo']);
                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'sno':
                            $tmpData[] = "<td rowspan='" . $rowspan . "' class='xl24'>" . ($key + 1) . "</td>";
                            break;
                        case 'regDt':
                        case 'issueDt':
                            $tmpData[] = "<td rowspan='" . $rowspan . "' class='xl24'>" . gd_date_format('Y-m-d', $val[$excelValue]) . "</td>";
                            break;
                        case 'orderNo':
                            $tmpData[] = "<td rowspan='" . $rowspan . "' class='xl24'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'totalPrice':
                        case 'price':
                        case 'vat':
                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][0][$excelValue]) . "</td>";
                            break;
                        case 'reTotalPrice':
                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][0]['totalPrice']) . "</td>";
                            break;
                        case 'tax':
                            $tmpData[] = "<td class='xl24'>" . $taxInvoiceStr[$val['taxInvoiceInfo'][0][$excelValue]] . "</td>";
                            break;
                        case 'taxAddress':
                            $tmpData[] = "<td rowspan='" . $rowspan . "' class='xl24'>" . $val[$excelValue] . ' ' . $val['taxAddressSub'] . "</td>";
                            break;
                        default:
                            $tmpData[] = "<td rowspan='" . $rowspan . "' class='xl24'>" . $val[$excelValue] . "</td>";
                            break;
                    }
                }
                if ($rowspan > 1) {
                    foreach ($excelField as $excelKey => $excelValue) {
                        if ($excelKey === 0) $tmpData[] = "</tr><tr>";
                        switch ($excelValue) {
                            case 'totalPrice':
                            case 'tax':
                            case 'price':
                            case 'vat':
                            case 'reTotalPrice':
                                foreach ($val['taxInvoiceInfo'] as $tKey => $tVal) {
                                    if ($tKey == 0) continue;
                                    switch ($excelValue) {
                                        case 'reTotalPrice':
                                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][$tKey]['totalPrice']) . "</td>";
                                            break;
                                        case 'tax':
                                            $tmpData[] = "<td class='xl24'>" . $taxInvoiceStr[$val['taxInvoiceInfo'][$tKey][$excelValue]] . "</td>";
                                            break;
                                        default:
                                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][$tKey][$excelValue]) . "</td>";
                                            break;
                                    }
                                }
                                break;
                        }
                    }
                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";

            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /*
    * 발행 내역 리스트
    */
    public function getOrderTaxInvoiceList($whereCondition, $excelField, $excelFieldName)
    {
        $tax = \App::load('\\Component\\Order\\Tax');
        $order = \App::load('\\Component\\Order\\OrderAdmin');

        $excelField = explode(STR_DIVISION, $excelField);
        $taxFreeStr = ['t' => __('과세'), 'f' => __('면세')];
        $printStr = ['y' => __('발행완료(인쇄후)'), 'n' => __('미발행(인쇄전)')];
        $godoBillStr = ['y' => __('전송완료'), 'n' => __('전송실패')];
        $rowspanField = ['totalPrice', 'price', 'vat', 'reTotalPrice', 'tax', 'printFl'];
        if (empty(array_intersect($rowspanField, $excelField)) === true) {
            $rowspanFix = 1;
        }

        if (empty($whereCondition['orderNo']) === false) {
            foreach ($whereCondition as $key => $value) {
                if (in_array($key, ['orderNo', 'pageNum'])) continue;
                unset($whereCondition[$key]);
            }
        }
        $whereCondition['statusFl'] = 'y';
        $data = $tax->getDataExcel($whereCondition);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";

        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {
                $rowspan = $rowspanFix ?? count($val['taxInvoiceInfo']);
                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'sno':
                            $tmpData[] = "<td rowspan='" . $rowspan . "' class='xl24'>" . ($key + 1) . "</td>";
                            break;
                        case 'regDt':
                        case 'issueDt':
                            $tmpData[] = "<td class='xl24'>" . gd_date_format('Y-m-d', $val[$excelValue]) . "</td>";
                            break;
                        case 'orderNo':
                        case 'adminMemo':
                            $tmpData[] = "<td class='xl24'>" . $val[$excelValue] . "</td>";
                            break;
                        case 'issueFl':
                            $tmpData[] = "<td class='xl24'>" . ($val[$excelValue] =='g' ? '일반' : '전자') . ($val['taxInvoiceInfo'][0]['tax'] =='t' ? '세금' : '' ) . "계산서</td>";
                            break;
                        case 'printFl':
                            $printFl = '';
                            if ($val['issueFl'] == 'g') {
                                if (empty($val['taxIssueInfo'][$val['taxInvoiceInfo'][0]['tax']]['issueStatusFl']) == false) {
                                    $printFl .= $printStr[$val['taxIssueInfo'][$val['taxInvoiceInfo'][0]['tax']]['printFl']];
                                }
                                if ($val['taxIssueInfo'][$val['taxInvoiceInfo'][0]['tax']]['printFl'] == 'y') {
                                    $printFl .= gd_date_format('Y-m-d', $val['taxIssueInfo'][$val['taxInvoiceInfo'][0]['tax']]['printDt']);
                                }
                            } else {
                                $printFl .= $godoBillStr[$val['taxIssueInfo'][$val['taxInvoiceInfo'][0]['tax']]['issueStatusFl']];
                            }

                            $tmpData[] = "<td class='xl24'>" . $printFl . "</td>";
                            break;
                        case 'totalPrice':
                        case 'price':
                        case 'vat':
                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][0][$excelValue]) . "</td>";
                            break;
                        case 'reTotalPrice':
                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][0]['totalPrice']) . "</td>";
                            break;
                        case 'tax':
                            $tmpData[] = "<td class='xl24'>" . $taxFreeStr[$val['taxInvoiceInfo'][0][$excelValue]] . "</td>";
                            break;
                        case 'taxAddress':
                            $tmpData[] = "<td class='xl24'>" . $val[$excelValue] . ' ' . $val['taxAddressSub'] . "</td>";
                            break;
                        default:
                            $tmpData[] = "<td class='xl24'>" . $val[$excelValue] . "</td>";
                            break;
                    }
                }
                if ($rowspan > 1) {
                    foreach ($excelField as $excelKey => $excelValue) {
                        if ($excelKey === 0) $tmpData[] = "</tr><tr>";
                        switch ($excelValue) {
                            case 'sno':
                                break;
                            case 'taxAddress':
                                $tmpData[] = "<td class='xl24'>" . $val[$excelValue] . ' ' . $val['taxAddressSub'] . "</td>";
                                break;
                                break;
                            case 'regDt':
                            case 'issueDt':
                                $tmpData[] = "<td class='xl24'>" . gd_date_format('Y-m-d', $val[$excelValue]) . "</td>";
                                break;
                            case 'adminMemo':
                                $tmpData[] = "<td class='xl24'>" . $val['adminMemo'] . "</td>";
                                break;
                            case 'totalPrice':
                            case 'tax':
                            case 'price':
                            case 'vat':
                            case 'reTotalPrice':
                            case 'printFl':
                            case 'issueFl':
                                foreach ($val['taxInvoiceInfo'] as $tKey => $tVal) {
                                    if ($tKey == 0) continue;
                                    switch ($excelValue) {
                                        case 'issueFl':
                                            $tmpData[] = "<td class='xl24'>" . ($val[$excelValue] =='g' ? '일반' : '전자') . ($val['taxInvoiceInfo'][$tKey]['tax'] =='t' ? '세금' : '' ) . "계산서</td>";
                                            break;
                                        case 'reTotalPrice':
                                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][$tKey]['totalPrice']) . "</td>";
                                            break;
                                        case 'tax':
                                            $tmpData[] = "<td class='xl24'>" . $taxFreeStr[$val['taxInvoiceInfo'][$tKey][$excelValue]] . "</td>";
                                            break;
                                        case 'printFl':
                                            $printFl = '';
                                            if ($val['issueFl'] == 'g') {
                                                $printFl .= $printStr[$val['taxIssueInfo'][$tVal['tax']]['printFl']];
                                                if ($val['taxIssueInfo'][$tVal['tax']]['printFl'] == 'y') {
                                                    $printFl .= gd_date_format('Y-m-d', $val['taxIssueInfo'][$tVal['tax']]['printDt']);
                                                }
                                            } else {
                                                $printFl .= $godoBillStr[$val['taxIssueInfo'][$tVal['tax']]['issueStatusFl']];
                                            }

                                            $tmpData[] = "<td class='xl24'>" . $printFl . "</td>";
                                            break;
                                        default:
                                            $tmpData[] = "<td class='xl24'>" . gd_currency_display($val['taxInvoiceInfo'][$tKey][$excelValue]) . "</td>";
                                            break;
                                    }
                                }
                                break;
                            default:
                                $tmpData[] = "<td class='xl24'>" . $val[$excelValue] . "</td>";
                                break;
                        }
                    }
                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";

            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }


    /**
     * 엑셀 다운로드 요청으로 조회된 데이터를 파일로 생성하는 함수
     *
     * @param $saveData
     *
     * @return bool
     */
    public function genarateFile($saveData)
    {
        try {
            $inputData = $this->excelHeader;
            $inputData .= "<table border='1'>";
            $inputData .= implode(\chr(13) . \chr(10), $saveData);
            $inputData .= '</table>';
            $inputData .= $this->excelFooter;
            $this->fileConfig['data'] = $inputData;
            if (StringUtils::strIsSet($this->fileConfig['password'])) {
                $fileName = $this->fileConfig['location'] . array_sum(explode(' ', microtime()));
                $tmpFilePath = UserFilePath::data('excel', $this->fileConfig['menu'], '_tmp', $fileName . ".xls")->getRealPath();
                FileHandler::write($tmpFilePath, $inputData, 0707);
                $zipFilePath = UserFilePath::data('excel', $this->fileConfig['menu'], $fileName . ".zip")->getRealPath();
                exec('cd ' . pathinfo($tmpFilePath)['dirname'] . ' && zip -P ' . $this->fileConfig['password'] . ' -r "' . $zipFilePath . '" "' . $fileName . '.xls"');
                $this->fileConfig['fileName'][] = $fileName . '.zip';
                FileHandler::delete($tmpFilePath);
            } else {
                $fileName = $this->fileConfig['location'] . array_sum(explode(' ', microtime()));
                $xlsFilePath = UserFilePath::data('excel', $this->fileConfig['menu'], $fileName . '.xls')->getRealPath();
                FileHandler::write($xlsFilePath, $inputData, 0707);
                $this->fileConfig['fileName'][] = $fileName . '.xls';
            }
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }


    /**
     * getExcelListForAdmin
     *
     * @return mixed
     */
    public function getExcelRequestListForAdmin()
    {
        $getValue = Request::get()->toArray();

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        $this->arrWhere = [];
        $this->arrWhere[] = "er.scmNo = ?";
        $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.scmNo'));

        $this->arrWhere[] = 'menu = ?';
        $this->db->bind_param_push($this->arrBind, 's', $getValue['menu']);

        if ($getValue['location']) {
            $this->arrWhere[] = 'location = ?';
            $this->db->bind_param_push($this->arrBind, 's', $getValue['location']);
        }

        $join[] = ' LEFT JOIN ' . DB_EXCEL_FORM . ' ef ON ef.sno = er.formSno ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON er.managerNo = m.sno ';

        // 현 페이지 결과
        $this->db->strField = "er.*,ef.title,m.managerNm,m.managerId";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_REQUEST . ' as er' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;

        return $getData;
    }

    /**
     * 엑셀
     *
     */
    public function getInfoExcelRequest($sno = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (empty($arrBind) === true) {
            $arrBind = [];
        }

        // 상품 코드 정보가 있는경우
        if ($sno) {
            $arrWhere = [];
            if ($this->db->strWhere) $arrWhere[] = $this->db->strWhere;

            // 상품 코드가 배열인 경우
            if (is_array($sno) === true) {
                $arrWhere[] = "er.sno IN ('" . implode("','", $sno) . "')";
                // 상품 코드가 하나인경우
            } else {
                $arrWhere[] = 'er.sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $sno);
            }

            $this->db->strWhere = implode(' AND ', $arrWhere);
        }

        $this->db->strField = "er.*, ef.menu, ef.location";

        $this->db->strJoin = ' LEFT JOIN ' . DB_EXCEL_FORM . ' ef ON ef.sno = er.formSno ';

        // 쿼리문 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_REQUEST . ' as er' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);


        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 유효기간 지난 파일 삭제 (등록일로부터 7일임)
     * 스케쥴러 등록 완료
     *
     */
    public function removeExcelRequest()
    {
        $this->arrWhere = [];
        $this->arrWhere[] = "er.expiryDate < NOW()";

        // 현 페이지 결과
        $this->db->strField = "er.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_REQUEST . ' as er' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if ($data) {
            foreach ($data as $key => $val) {
                $fileList = explode(STR_DIVISION, $val['fileName']);
                if ($fileList) {
                    foreach ($fileList as $k => $v) {

                        $filePath = UserFilePath::data('', $val['filePath'], $v)->getRealPath();
                        if (FileHandler::delete($filePath)) {
                            $arrBind = [];
                            $this->db->bind_param_push($arrBind, 'i', $val['sno']);
                            $this->db->set_delete_db(DB_EXCEL_REQUEST, 'sno = ?', $arrBind);
                            unset($arrBind);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * 방문자 IP 분석
     * @param $data
     * @param string $dataType
     * @param $searchData
     * @param $excelField
     * @param $excelFieldName
     * @return mixed
     */
    public function getVisitIpExcel($data, $dataType = 'list', $searchData = null, $excelField, $excelFieldName)
    {
        $useCellChar = ['A', 'B', 'C'];
        if ($dataType == 'list') {
            array_push($useCellChar, 'D');
        }
        $lastCellChar = $useCellChar[count($useCellChar)-1];

        $objPHPExcel = new Spreadsheet();

        $this->excelPageNum = 60000;
        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $tmpData = $setData = [];

        if($dataType == 'detail') {
            $setData[0][] = 'IP';
            $setData[0][] = $searchData['searchIP'];
            $setData[1][] = '운영체제';
            $setData[1][] = $searchData['searchOS'];
            $setData[2][] = '브라우저';
            $setData[2][] = $searchData['searchBrowser'];
        }
        $tmpHeader = [];
        foreach ($data as $k => $v) {
            foreach ($v as $key => $val) {
                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key % $this->excelPageNum == 0) {
                        $tmpHeader[$k][] = $excelFieldName[$excelValue]['name'];
                    }
                    switch ($excelValue) {
                        case 'sno':
                            $tmpData[$k][$key][] = $key + 1;
                            break;
                        case 'visitIP':
                        case 'visitOS':
                        case 'visitBrowser':
                        case 'visitPageView':
                        case 'regDt':
                            $tmpData[$k][$key][] = $val[$excelValue];
                            break;
                        case 'visitReferer':
                            $tmpData[$k][$key][] = $val[$excelValue];
                            break;
                    }
                }
            }
            $tmpData[$k] = array_merge($tmpHeader, $tmpData[$k]);
            unset($tmpHeader);
        }

        foreach ($tmpData as $key => $value) {
            $objPHPExcel->createSheet();
            $sheet = $objPHPExcel->setActiveSheetIndex($key);
            $sheet->setTitle('sheet' . ($key + 1));
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
            foreach ($useCellChar as $char) {
                $setDimension = $objPHPExcel->getActiveSheet()->getColumnDimension($char);
                if ($char == 'C') {
                    $setDimension->setAutoSize(true);
                } else {
                    $setDimension->setWidth(18);
                }
            }

            foreach ($setData as $k => $v) {
                $cellNum = $k + 1;
                $this->setStyleExcelTitle($objPHPExcel, 'A', $cellNum, $useCellChar);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $cellNum, $v[0]);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $cellNum, $v[1]);
                $objPHPExcel->getActiveSheet()->mergeCells('B' . $cellNum . ':' . $lastCellChar . $cellNum);
            }

            foreach ($value as $k => $v) {
                if ($dataType == 'list') {
                    $cellNum = $k + 1;
                } else {
                    $cellNum++;
                }
                if ($k % ($this->excelPageNum + 1) == 0) {
                    foreach ($useCellChar as $char) {
                        $this->setStyleExcelTitle($objPHPExcel, $char, $cellNum, $useCellChar);
                    }
                }
                foreach ($useCellChar as $num => $char) {
                    $objPHPExcel->getActiveSheet()->setCellValue($char . $cellNum, $v[$num]);
                }
                foreach ($useCellChar as $key => $val) {
                    if (empty($useCellChar[$key+1]) === false) {
                        $this->setStyleExcelBorder($objPHPExcel, $val, $useCellChar[$key+1], $cellNum);
                    }
                }
            }
        }

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0*/

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xls');
        ob_start ();
        $objWriter->save('php://output');
        $inputData = ob_get_contents();
        ob_end_flush();

        $fileName = $this->fileConfig['location'] . array_sum(explode(' ', microtime()));
        $xlsFilePath = UserFilePath::data('excel', $this->fileConfig['menu'], $fileName . ".xls")->getRealPath();
        FileHandler::write($xlsFilePath, $inputData, 0707);
        $this->fileConfig['fileName'][] = $fileName . ".xls";

        $arrData['filePath'] = "excel";
        $arrData['fileName'] = implode(STR_DIVISION, $this->fileConfig['fileName']);

        echo '<script>parent.completeExcel(\'' . $arrData['fileName'] . '\')</script>';
        return $arrData;
    }

    public function setStyleExcelTitle($obj, $startChar = 'A', $cellNum = 1, $useCellChar)
    {
        $obj->getActiveSheet()->getStyle($startChar . $cellNum)->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]]);
        $obj->getActiveSheet()->getStyle($startChar . $cellNum)->getFill()->applyFromArray(['type' => Fill::FILL_SOLID, 'startcolor' => ['rgb' => 'f3f3f3']]);
        foreach ($useCellChar as $key => $val) {
            if (empty($useCellChar[$key+1]) === false) {
                $this->setStyleExcelBorder($obj, $val, $useCellChar[$key+1], $cellNum);
            }
        }
    }

    public function setStyleExcelBorder($obj, $startChar, $endChar, $cellNum)
    {
        $obj->getActiveSheet()->getStyle($startChar . $cellNum . ':' . $endChar . $cellNum)->applyFromArray(['borders' => ['outline' => ['style' => Border::BORDER_THIN, 'color' => ['argb' => 'FF0000']]]]);
    }

    /**
     * excelPageNum 리턴 (튜닝상점을 위한 excelPageNum 리턴값)
     *
     * @param void
     *
     * @return integer $this->excelPageNum
     */
    public function getExcelPageNum()
    {
        return $this->excelPageNum;
    }

    /**
     * 프로모션 > 쿠폰 관리 > 쿠폰발급내역관리 엑셀다운로드
     * @param $whereCondition
     * @param $excelField
     * @param $excelFieldName
     * @return array
     */
    public function getPromotionCouponManage($arrData, $excelField, $excelFieldName)
    {
        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        $groupDao = \App::load('Component\Member\Group\GroupDAO');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 검색 설정
        //$data = $couponAdmin->getCouponAdminListExcel($whereCondition);
        $data = $couponAdmin->getCouponAdminListExcel($arrData);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'memId':
                            $tmpData[] = "<td class=\"xl24\">" . $val['memId'] . "</td>";
                            break;

                        case 'memNm':
                            $tmpData[] = "<td class=\"xl24\">" . $val['memNm'] . "</td>";
                            break;

                        case 'groupNm':
                            $groupList[$val['groupSno']] = $groupDao->selectGroup($val['groupSno']);
                            $tmpData[] = "<td class=\"xl24\">" . $groupList[$val['groupSno']]['groupNm'] . "</td>";
                            break;

                        case 'memberCouponEndDate':
                            $tmpData[] = "<td>" . $val['memberCouponEndDate'] . "</td>";
                            break;

                        case 'memberCouponUseDate':
                            if($val['memberCouponState'] == 'y') {
                                $usedDt = '';
                            } else {
                                $usedDt = gd_date_format('Y-m-d H:i:s', $val['memberCouponUseDate']);
                            }
                            $tmpData[] = "<td>" . $usedDt . "</td>";
                            break;

                        case 'couponSaveAdminId':
                            $tmpData[] = "<td class=\"xl24\">" . $val['couponSaveAdminId'] . "</td>";
                            break;

                        case 'memberCouponState':
                            if($val['memberCouponState'] == 'y') {
                                $memberCouponUseText = '미사용';
                            } else {
                                $memberCouponUseText = '사용';
                            }
                            $tmpData[] = "<td class=\"xl24\">" . $memberCouponUseText . "</td>";
                            break;

                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }

    /**
     * 프로모션 > 쿠폰 관리 > 페이퍼쿠폰인증내역관리 엑셀다운로드
     * @param $arrData
     * @param $excelField
     * @param $excelFieldName
     * @return array
     */
    public function getPromotionCouponOfflineManage($arrData, $excelField, $excelFieldName)
    {
        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        $groupDao = \App::load('Component\Member\Group\GroupDAO');

        $excelField = explode(STR_DIVISION, $excelField);

        // --- 검색 설정
        //$data = $couponAdmin->getCouponOfflineAdminListExcel($whereCondition);
        $data = $couponAdmin->getCouponOfflineAdminListExcel($arrData);

        $totalNum = count($data);

        if ($this->excelPageNum) $data = array_chunk($data, $this->excelPageNum, true);
        else $data = array_chunk($data, $totalNum, true);

        $setData = [];
        $setHedData[] = "<tr>";
        foreach ($data as $k => $v) {

            $tmpData = [];
            foreach ($v as $key => $val) {

                if (($key % 20 == 0 && $key != '0') || $totalNum - 1 == $key) {
                    echo "<script> parent.progressExcel('" . round((100 / ($totalNum - 1)) * $key) . "'); </script>";
                }

                $tmpData[] = "<tr>";
                foreach ($excelField as $excelKey => $excelValue) {

                    if (!$excelFieldName[$excelValue]) continue;

                    if ($key == '0') {
                        $setHedData[] = "<td class='title'>" . $excelFieldName[$excelValue]['name'] . "</td>";
                    }

                    switch ($excelValue) {
                        case 'couponOfflineCodeUser':
                            $tmpData[] = "<td class=\"xl24\">" . $val['couponOfflineCodeUser'] . "</td>";
                            break;

                        case 'memId':
                            $tmpData[] = "<td class=\"xl24\">" . $val['memId'] . "</td>";
                            break;

                        case 'memNm':
                            $tmpData[] = "<td class=\"xl24\">" . $val['memNm'] . "</td>";
                            break;

                        case 'groupNm':
                            $groupList[$val['groupSno']] = $groupDao->selectGroup($val['groupSno']);
                            $tmpData[] = "<td class=\"xl24\">" . $groupList[$val['groupSno']]['groupNm'] . "</td>";
                            break;

                        case 'memberCouponEndDate':
                            $tmpData[] = "<td>" . $val['memberCouponEndDate'] . "</td>";
                            break;

                        case 'memberCouponUseDate':
                            if($val['memberCouponState'] == 'y') {
                                $usedDt = '';
                            } else {
                                $usedDt = gd_date_format('Y-m-d H:i:s', $val['memberCouponUseDate']);
                            }
                            $tmpData[] = "<td>" . $usedDt . "</td>";
                            break;

                        case 'memberCouponState':
                            if($val['memberCouponState'] == 'y') {
                                $memberCouponUseText = '미사용';
                            } else {
                                $memberCouponUseText = '사용';
                            }
                            $tmpData[] = "<td class=\"xl24\">" . $memberCouponUseText . "</td>";
                            break;

                        default  :
                            $tmpData[] = "<td>" . $val[$excelValue] . "</td>";
                            break;
                    }

                }
                $tmpData[] = "</tr>";
            }

            $setHedData[] = "</tr>";
            $setData[] = array_merge($setHedData, $tmpData);
        }

        return $setData;
    }
}
