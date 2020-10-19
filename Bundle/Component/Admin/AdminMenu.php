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

namespace Bundle\Component\Admin;

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Utility\GodoUtils;
use App;
use Globals;
use Request;

/**
 * 관리자 메뉴 class
 *
 * 관리자 좌측 메뉴 및 상단 위치 설정 관련 class
 * @author su
 */
class AdminMenu
{
    // 디비 접속
    protected $db;

    /**
     * @var array arrBind
     */
    protected $arrBind = [];
    protected $arrWhere = [];

    /**
     * @var string 솔루션 상품군
     */
    protected $ecKind = 'standard';

    /**
     * @var const PLUSSHOP_MENU_KEY 플러스샵 배열 키
     */
    const PLUSSHOP_MENU_KEY = 'plusShop';

    /**
     * @var array
     */
    public $cd = [];

    /**
     * @var array
     */
    public $location = [];

    /**
     * @var array
     */
    public $lno = [];

    /**
     * @var array
     */
    public $leftMenus = [];

    /**
     * @var array
     */
    public $menuSelected = [];

    /*
     * @var int
     */
    public $menuCnt = 0;

    /**
     * @var string 페이코서치 사용여부
     */
    public $paycosearchUseFl;

    /**
     * @var array 접근 가능 메뉴고유번호
     */
    public $accessMenu = []; // 3차 메뉴

    /**
     * @var array 쓰기 가능 메뉴고유번호
     */
    public $writeEnabledMenu = []; // 읽기 전용 메뉴

    /**
     * AdminMenu constructor.
     */
    public function __construct()
    {
        if (!\is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->ecKind = Globals::get('gLicense.ecKind');

        // 페이코서치 사용여부
        $paycoSearch = \App::load('\\Component\\Nhn\\Paycosearch');
        $this->paycosearchUseFl = $paycoSearch->neSearchConfigIsset;
    }

    /**
     * callMenu
     *
     * @param        $topMenu
     * @param string $midMenu
     * @param string $thisMenu
     * @param string $adminMenuType
     *
     * @return bool
     * @throws AlertBackException
     */
    public function callMenu($topMenu, $midMenu = '', $thisMenu = '', $adminMenuType = 'd')
    {
        $isThirdMenuPlusShop = false;
        // Cd, code
        $this->cd = \func_get_args();

        // left list data
        $this->leftMenus = $this->getAdminMenuList($adminMenuType, $topMenu, null, true, true);
        // Location
        foreach ($this->leftMenus as $listKey => $listVal) {
            if ($topMenu && $listVal['fCode'] === $topMenu) {
                $this->location[0] = $listVal['fName'];
                $this->lno[0] = $listVal['fNo'];
                // 현재 선택된 메뉴
                $this->menuSelected['top'][$this->lno[0]] = 'active';
                if ($midMenu && $listVal['sCode'] === $midMenu) {
                    $this->location[1] = $listVal['sName'];
                    $this->lno[1] = $listVal['sNo'];
                    // 현재 선택된 메뉴
                    $this->menuSelected['mid'][$this->lno[1]] = 'active';
                    if ($thisMenu && $listVal['tCode'] === $thisMenu) {
                        $isThirdMenuPlusShop = ($listVal['tSetting'] === 'p');
                        if ($listVal['isPlusShop'] === 'n') {
                            break;
                        }
                        $this->location[2] = $listVal['tName'];
                        $this->lno[2] = $listVal['tNo'];
                        // 현재 선택된 메뉴
                        if ($listVal['tDisplay'] === 'n') {
                            $this->menuSelected['this'][$listVal['tDisplayNo']] = 'active';
                        } else {
                            $this->menuSelected['this'][$this->lno[2]] = 'active';
                        }
                    }
                }
            }
        }

        foreach ($this->leftMenus as $key => $val) {
            if (array_key_exists('isPlusShop', $val) && $val['isPlusShop'] === 'n') {
                unset($this->leftMenus[$key]);
            }
        }
        // 요청 메뉴가 없는 경우는
        // 1. 메뉴가 등록되어 있지 않음
        // 2. 플러스샵 설치안됨/사용안함 시 메뉴가 없음
        if (empty($this->lno[0]) === false && empty($this->lno[1]) === false && empty($this->lno[2]) === false) {
            // 페이지가 메뉴에 등록되어 있음

            // 페이코 서치 사용안함, 페이지 직접 접근 - 해당 메뉴 열람 권한 삭제
            $hideMenuPaycoSearch = array('godo00578','godo00577', 'godo00751');
            if(in_array($this->lno[2], $hideMenuPaycoSearch) && $this->paycosearchUseFl == 'N') {
                if(Request::request()->get('popupMode') === 'yes') {
                    throw new AlertCloseException(__('권한이 없는 메뉴입니다.'));
                } else {
                    throw new AlertBackException(__('권한이 없는 메뉴입니다.'));
                }
            }

        } else {
            if ($isThirdMenuPlusShop) {
                if (\Request::get()->get('inflow') == 'godo') {
                    throw new AlertRedirectException(__('해당 서비스는 플러스샵에서 앱 설치 후 이용 가능합니다.'), null, null, 'http://plus.godo.co.kr');
                } else {
                    throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.'));
                }
            } else {
                if(Request::request()->get('popupMode') === 'yes') {
                    throw new AlertCloseException(__('권한이 없는 메뉴입니다.'));
                } else {
                    throw new AlertBackException(__('권한이 없는 메뉴입니다.'));
                }
            }
        }

        // 메뉴가 있는 경우에만 처리
        if (empty($this->leftMenus) === true) {
            return false;
        }
    }

    /**
     * getTopMenu
     *
     * @param string $adminMenuType 관리자 타입에 따른 메뉴 - 본사(d)/공급사(s)
     *
     * @return array|string
     */
    public function getTopMenu($adminMenuType = 'd')
    {
        $arrWhere['adminMenuType'] = $adminMenuType;
        $arrWhere['adminMenuEcKind'] = $this->ecKind;
        $this->setAdminMenuWhere($arrWhere);
        // 메뉴 Depth 구분
        $this->arrWhere[] = 'am.adminMenuDepth = ?';
        $this->db->bind_param_push($this->arrBind, 'i', 1);

        //버전별 메뉴 숨김
        $srcVersion = GodoUtils::getSrcVersion();
        $this->arrWhere[] = 'INSTR(am.adminMenuHideVersion, ?) < 1 ';
        $this->arrWhere[] = 'INSTR(am.adminMenuHideVersion, ?) < 1 ';
        $this->db->bind_param_push($this->arrBind, 's', $srcVersion);
        $this->db->bind_param_push($this->arrBind, 's', $srcVersion);

        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . ' as am ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        foreach ($getData as $topKey => $topVal) {
            if ($topVal['adminMenuSettingType'] === 'p') {
                if (GodoUtils::isPlusShop($topVal['adminMenuPlusCode']) === false) {
                    unset($getData[$topKey]);
                } else {
                    // 관리자 상단에 출력되는 전체 메뉴갯수 count - plusshop메뉴일 경우
                    if ($topVal['adminMenuDisplayType'] === 'y') {
                        $this->menuCnt++;
                    }
                }
            } else {
                // 관리자 상단에 출력되는 전체 메뉴갯수 count - 일반메뉴일 경우
                if ($topVal['adminMenuDisplayType'] === 'y') {
                    $this->menuCnt++;
                }
            }
        }

        $getTopData['data'] = $getData;
        if ($adminMenuType == 's') {
            $getTopData['link'] = URI_PROVIDER;
        } else {
            $getTopData['link'] = URI_ADMIN;
        }

        return gd_htmlspecialchars_stripslashes($getTopData);
    }

    /**
     * getAdminMenuList
     *
     * @param string $adminMenuType
     * @param string $topMenu
     * @param string $plusShopType
     * @param bool $isStrip 데이터 반환 시 gd_htmlspecialchars_stripslashes 실행 여부
     * @param bool $showAllMenu T : 권한이 없는 메뉴도 노출
     * @return array|string
     */
    public function getAdminMenuList($adminMenuType = 'd', $topMenu = null, $plusShopType = null, $isStrip = true, $showAllMenu = false)
    {
        $excludeTopMenu = ['share',];
        if (in_array($topMenu, $excludeTopMenu)) {
            return [];
        }
        $arrWhere['adminMenuType'] = $adminMenuType;

        // 메뉴 상품군 구분
        if ($this->ecKind == 'standard') {
            $arrWhere['adminMenuEcKind'] = 's';
        } else {
            $arrWhere['adminMenuEcKind'] = 'p';
        }

        // 본사, 공급사 구분
        $this->arrWhere[] = 'f.adminMenuType = ? AND s.adminMenuType = ? AND t.adminMenuType = ?';
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuType']);
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuType']);
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuType']);
        // 메뉴 상품군 구분
        $this->arrWhere[] = 'f.adminMenuEcKind IN (?, ?) AND s.adminMenuEcKind IN (?, ?) AND t.adminMenuEcKind IN (?, ?)';
        $this->db->bind_param_push($this->arrBind, 's', 'a');
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuEcKind']);
        $this->db->bind_param_push($this->arrBind, 's', 'a');
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuEcKind']);
        $this->db->bind_param_push($this->arrBind, 's', 'a');
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuEcKind']);
        // 1차 메뉴 정의 구분
        if (empty($topMenu) !== true) {
            $this->arrWhere[] = 'f.adminMenuCode = ?';
            $this->db->bind_param_push($this->arrBind, 's', $topMenu);
        }
        // 1차 메뉴 뎁스 구분
        $this->arrWhere[] = 'f.adminMenuDepth = ?';
        $this->db->bind_param_push($this->arrBind, 'i', 1);

        // 무조건 제외해야 할 메뉴 처리 (system 제외)
        if (!GodoUtils::isGodoIp() || App::isProduction()) {
            $this->arrWhere[] = 'f.adminMenuNo != \'godo00611\'';
        }

        //버전별 메뉴 숨김
        $srcVersion = GodoUtils::getSrcVersion();
        $this->arrWhere[] = 'INSTR(f.adminMenuHideVersion, ?) < 1 ';
        $this->arrWhere[] = 'INSTR(t.adminMenuHideVersion, ?) < 1 ';
        $this->db->bind_param_push($this->arrBind, 's', $srcVersion);
        $this->db->bind_param_push($this->arrBind, 's', $srcVersion);

        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefitUse = $goodsBenefit->getConfig();
        $hideMenu = array('godo00723','godo00724');
        //상품혜택을 사용하지 않으면 해당 메뉴 숨김처리
        if($goodsBenefitUse == 'n'){
            foreach($hideMenu as $menuNo) {
                $this->arrWhere[] = 'f.adminMenuNo != \'' . $menuNo . '\'';
                $this->arrWhere[] = 't.adminMenuNo != \'' . $menuNo . '\'';
            }
        }

        //페이코 서치 사용하지 않으면 해당 메뉴 숨김처리 && 솔루션신규설치업체 해당메뉴 숨김처리
        $hideMenuPaycoSearch = array('godo00578','godo00577', 'godo00751');
        if($this->paycosearchUseFl == 'N'){
            foreach($hideMenuPaycoSearch as $menuNo) {
                $this->arrWhere[] = 'f.adminMenuNo != \'' . $menuNo . '\'';
                $this->arrWhere[] = 't.adminMenuNo != \'' . $menuNo . '\'';
            }
        }

        $externalOrder = \App::load('\\Component\\Order\\ExternalOrder');
        $externalOrderAdminMenuUseFl = $externalOrder->getUseAdminMenu();
        if($externalOrderAdminMenuUseFl === false){
            //외부채널 주문등록 메뉴를 사용하고 있지 않다면 접근권한에서 노출시키지 않는다.
            $this->arrWhere[] = 's.adminMenuNo != ?';
            $this->arrWhere[] = 't.adminMenuNo != ?';
            $this->db->bind_param_push($this->arrBind, 's', 'godo00763');
            $this->db->bind_param_push($this->arrBind, 's', 'godo00764');
        }

        //바코드 기능 제거로 메뉴 숨김 처리
        $hideBarcodeMenuNo = 'godo00765';
        $this->arrWhere[] = 'f.adminMenuNo != \'' . $hideBarcodeMenuNo . '\'';
        $this->arrWhere[] = 't.adminMenuNo != \'' . $hideBarcodeMenuNo . '\'';

        $field[] = 't.adminMenuDepth as depth, f.adminMenuNo as fNo, f.adminMenuCode as fCode, f.adminMenuName as fName, f.adminMenuDisplayType as fDisplay, f.adminMenuSettingType as fSetting, f.adminMenuEcKind as fEc, f.adminMenuSort as fSort, f.adminMenuPlusCode as fPlusCode';
        $field[] = 's.adminMenuNo as sNo, s.adminMenuCode as sCode, s.adminMenuName as sName, s.adminMenuDisplayType as sDisplay, s.adminMenuSettingType as sSetting, s.adminMenuEcKind as sEc, s.adminMenuSort as sSort, s.adminMenuPlusCode as sPlusCode';
        $field[] = 't.adminMenuNo as tNo, t.adminMenuCode as tCode, t.adminMenuName as tName, t.adminMenuDisplayType as tDisplay, t.adminMenuDisplayNo as tDisplayNo, t.adminMenuSettingType as tSetting, t.adminMenuEcKind as tEc, t.adminMenuSort as tSort, t.adminMenuUrl as tUrl, t.adminMenuPlusCode as tPlusCode';
        $this->db->strField = implode(', ', $field);
        $join[] = 'LEFT JOIN ' . DB_ADMIN_MENU . ' as s ON s.adminMenuDepth = 2 AND f.adminMenuNo = s.adminMenuParentNo';
        $join[] = 'LEFT JOIN ' . DB_ADMIN_MENU . ' as t ON t.adminMenuDepth = 3 AND s.adminMenuNo = t.adminMenuParentNo';
        $this->db->strJoin = implode(' ', $join);
        $this->db->strOrder = 'fSort asc, sSort asc , tSort asc';
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . ' as f ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);
        // 플러스샵 구분
        $nowFNo = '00000';
        $nowSNo = '00000';
        $addArrCount = 0;
        foreach ($getData as $menuKey => $menuVal) {
            if ($nowFNo != $menuVal['fNo']) {
                $addFirstArr[] = [
                    'depth'     => 1,
                    'fNo'       => $menuVal['fNo'],
                    'fCode'     => $menuVal['fCode'],
                    'fPlusCode' => $menuVal['fPlusCode'],
                    'fName'     => $menuVal['fName'],
                    'fDisplay'  => $menuVal['fDisplay'],
                    'fSetting'  => $menuVal['fSetting'],
                    'fEc'       => $menuVal['fEc'],
                ];
                array_splice($getData, ($menuKey + $addArrCount), 0, $addFirstArr);
                $addArrCount++;
                unset($addFirstArr);
            }
            if ($nowSNo != $menuVal['sNo']) {
                $addSecondArr[] = [
                    'depth'     => 2,
                    'fNo'       => $menuVal['fNo'],
                    'fCode'     => $menuVal['fCode'],
                    'fPlusCode' => $menuVal['fPlusCode'],
                    'fName'     => $menuVal['fName'],
                    'fDisplay'  => $menuVal['fDisplay'],
                    'fSetting'  => $menuVal['fSetting'],
                    'fEc'       => $menuVal['fEc'],
                    'sNo'       => $menuVal['sNo'],
                    'sCode'     => $menuVal['sCode'],
                    'sPlusCode' => $menuVal['sPlusCode'],
                    'sName'     => $menuVal['sName'],
                    'sDisplay'  => $menuVal['sDisplay'],
                    'sSetting'  => $menuVal['sSetting'],
                    'sEc'       => $menuVal['sEc'],
                ];
                array_splice($getData, ($menuKey + $addArrCount), 0, $addSecondArr);
                $addArrCount++;
                unset($addSecondArr);
            }
            $nowFNo = $menuVal['fNo'];
            $nowSNo = $menuVal['sNo'];
        }

        $checkDisplay = [];
        foreach ($getData as $key => $val) {
            // 플러스샵 세팅 초기화
            if ($plusShopType === null) {
                if ($val['fSetting'] == 'p') {
                    if (GodoUtils::isPlusShop($val['fPlusCode']) === false) {
                        if($showAllMenu === false) {
                            unset($getData[$key]);
                        }
                        $getData[$key]['isPlusShop'] = 'n';
                        $getData[$key]['sDisplay'] = 'n';
                        $getData[$key]['fDisplay'] = 'n';
                    }
                }
                if ($val['sSetting'] == 'p') {
                    if (GodoUtils::isPlusShop($val['sPlusCode']) === false) {
                        if($showAllMenu === false) {
                            unset($getData[$key]);
                        }
                        $getData[$key]['isPlusShop'] = 'n';
                        $getData[$key]['sDisplay'] = 'n';
                        $getData[$key]['fDisplay'] = 'n';
                    }
                }
                if (array_key_exists('tSetting', $val) && $val['tSetting'] === 'p') {
                    if (GodoUtils::isPlusShop($val['tPlusCode']) === false) {
                        if($showAllMenu === false) {
                            unset($getData[$key]);
                        }
                        $getData[$key]['isPlusShop'] = 'n';
                        $getData[$key]['sDisplay'] = 'n';
                        $getData[$key]['fDisplay'] = 'n';
                    }
                }
            }
            // 개발소스관리 체크
            if (GodoUtils::isVersionControl() === false) {
                if ($val['fCode'] === 'development' && $val['sCode'] === 'version') {
                    unset($getData[$key]);
                }
            }

            if($val['depth'] == 3 ) {
                if($getData[$key]['fDisplay'] == 'y') {
                    $checkDisplay[]= $val['sNo'] ;
                }
            }
        }

        //2차메뉴중 하위메뉴가 전부 히든처리가 돼있으면 히든처리
        $checkDisplay = array_unique($checkDisplay);
        if($checkDisplay) {
            foreach ($getData as &$val) {
                if ($val['depth'] == 2 && (in_array($val['sNo'], $checkDisplay) === false)) {
                    $val['fDisplay'] = $val['sDisplay'] = 'n';
                }
            }
        }
        return $isStrip ? gd_htmlspecialchars_stripslashes($getData) : $getData;
    }

    /**
     * getAdminMenuFirstInfo
     *
     * @param $adminMenuNo
     *
     * @return array|string
     */
    public function getAdminMenuInfo($adminMenuNo)
    {
        // 메뉴 Depth 구분
        $this->arrWhere[] = 'am.adminMenuNo = ?';
        $this->db->bind_param_push($this->arrBind, 's', $adminMenuNo);
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . ' as am ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if (count($getData) == 1) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * getAdminMenuUrl
     *
     * @param $url
     *
     * @return array|string
     */
    public function getAdminMenuUrl($url)
    {
        // 메뉴 Depth 구분
        $this->arrWhere[] = 'am.adminMenuUrl = ?';
        $this->db->bind_param_push($this->arrBind, 's', $url);
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . ' as am ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if (count($getData) == 1) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * getAdminMenuTreeJsonList
     *
     * @param      $adminMenuArr
     * @param bool $isThirdDisplay
     *
     * @return string
     */
    public function getAdminMenuTreeJsonList($adminMenuArr, $isThirdDisplay = true)
    {
        $dataTreeArrList = [];

        foreach ($adminMenuArr as $menuKey => $menuVal) {
            if ($isThirdDisplay === false && $menuVal['tDisplay'] == 'n') {
                continue;
            }

            // 시스템 노출안되도록 수정
            if (!GodoUtils::isGodoIp() || App::isProduction()) {
                if ($menuVal['fNo'] == 'godo00611') {
                    continue;
                }
            }


            if ($menuVal['depth'] == 1) {
                $dataTreeArrList[$menuVal['fName']] = [
                    'number' => $menuVal['fNo'],
                ];
            } else if ($menuVal['depth'] == 2) {
                $dataTreeArrList[$menuVal['fName']][$menuVal['sName']] = [
                    'number' => $menuVal['sNo'],
                ];
            } else if ($menuVal['depth'] == 3) {
                $dataTreeArrList[$menuVal['fName']][$menuVal['sName']][$menuVal['tName']] = [
                    'number' => $menuVal['tNo'],
                ];
            }
        }

        $result = json_encode($this->convertTreeForJson($dataTreeArrList));

        return $result;
    }

    /**
     * getAdminMenuTreeList
     *
     * @param $adminMenuArr
     *
     * @return array
     */
    public function getAdminMenuTreeList($adminMenuArr)
    {
        foreach ($adminMenuArr as $menuKey => $menuVal) {
            if ($menuVal['depth'] == 1) {
                if (isset($dataTreeArrList['top'][$menuVal['fNo']]) === false) {
                    $dataTreeArrList['top'][$menuVal['fNo']] = [
                        'name' => $menuVal['fName'],
                        'code' => $menuVal['fCode'],
                        'display' => $menuVal['fDisplay'],
                        'setting' => $menuVal['fSetting'],
                        'ec' => $menuVal['fEc'],
                    ];
                }
            } else if ($menuVal['depth'] == 2) {
                if (isset($dataTreeArrList['top'][$menuVal['fNo']]['mid'][$menuVal['sNo']]) === false) {
                    $dataTreeArrList['top'][$menuVal['fNo']]['mid'][$menuVal['sNo']] = [
                        'name' => $menuVal['sName'],
                        'code' => $menuVal['sCode'],
                        'display' => $menuVal['sDisplay'],
                        'setting' => $menuVal['sSetting'],
                        'ec' => $menuVal['sEc'],
                    ];
                }
            } else if ($menuVal['depth'] == 3) {
                if (isset($dataTreeArrList['top'][$menuVal['fNo']]['mid'][$menuVal['sNo']]['last'][$menuVal['tNo']]) === false) {
                    $dataTreeArrList['top'][$menuVal['fNo']]['mid'][$menuVal['sNo']]['last'][$menuVal['tNo']] = [
                        'name' => $menuVal['tName'],
                        'code' => $menuVal['tCode'],
                        'display' => $menuVal['tDisplay'],
                        'setting' => $menuVal['tSetting'],
                        'ec' => $menuVal['tEc'],
                        'url' => $menuVal['tUrl'],
                    ];
                }
            }
            unset($menuKey, $menuVal);
        }
        unset($adminMenuArr);

        return $dataTreeArrList;
    }

    /**
     * 메뉴 Top Code 리턴
     * @param $menuTreeList
     * @param $menuMidCode
     * @return bool | string
     */
    public function getTopCode($menuTreeList, $menuMidCode)
    {
        if ($menuMidCode == '') return false;
        if (\is_array($menuTreeList) === false || count($menuTreeList) == 0) return false;
        if (\is_array($menuTreeList['top']) === false) return false;
        foreach ($menuTreeList['top'] as $key_1 => $val_1) { // 1차
            if (array_key_exists($menuMidCode, $val_1['mid']) === true) {
                return $key_1;
                break;
            }
        }
        return false;
    }

    /**
     * getAdminMenuPermissionSelected
     *
     * 설정된 권한정보(permissionFl,permissionMenu, functionAuth, writeEnabledMenu)로 selected 정의
     *
     * @param array $existingPermission
     * @param array $menuTreeList
     *
     * @return array
     */
    public function getAdminMenuPermissionSelected($existingPermission, $menuTreeList)
    {
        $dataPermissionMenu = $existingPermission['permissionMenu'];
        $dataWriteEnabledMenu = $existingPermission['writeEnabledMenu'];

        if ($existingPermission['permissionFl'] == 's') { // 전체권한(s)
            $selected = [];
        } else { // 권한선택(l)
            // 전체 권한 아니고 일부 권한이고 일부 권한 설정이 있을 경우
            // individual : 2~3차 메뉴 개별설정
            // empty      : 권한없음
            // readonly   : 읽기
            // writable   : 읽기+쓰기
            $selected = ['permission_1' => [], 'permission_2' => [], 'permission_3' => [], ];

            // 쓰기 권한 메뉴
            if (\is_array($dataWriteEnabledMenu) && count($dataWriteEnabledMenu) > 0) {
                foreach ($dataWriteEnabledMenu as $key_2 => $val_2) { // 2차
                    foreach ($val_2 as $key_3 => $val_3) { // 3차
                        $selected['permission_3'][$key_2][$val_3]['writable'] = 'selected="selected"';
                        unset($key_3, $val_3);
                    }
                    unset($key_2, $val_2);
                }
            }

            // 접근 권한 메뉴
            if (\is_array($dataPermissionMenu) && count($dataPermissionMenu) > 0) {
                // 3차 메뉴 정의
                foreach ($dataPermissionMenu['permission_3'] as $key_2 => $val_2) { // 2차
                    foreach ($val_2 as $key_3 => $val_3) { // 3차
                        if (isset($selected['permission_3'][$key_2][$val_3]['writable']) === false) {
                            $selected['permission_3'][$key_2][$val_3]['readonly'] = 'selected="selected"';
                        }
                        unset($key_3, $val_3);
                    }

                    // 상위 메뉴(permission_1) 존재여부 체크 및 대입
                    $parentTopCode = $this->getTopCode($menuTreeList, $key_2);
                    if (\is_array($dataPermissionMenu['permission_1']) === false) {
                        $dataPermissionMenu['permission_1'] = [$parentTopCode];
                    } else if (in_array($parentTopCode, $dataPermissionMenu['permission_1']) === false) {
                        array_push($dataPermissionMenu['permission_1'], $parentTopCode);
                    }

                    // 상위 메뉴(permission_2) 존재여부 체크 및 대입
                    if (\is_array($dataPermissionMenu['permission_2']) === false) {
                        $dataPermissionMenu['permission_2'] = [$parentTopCode => [$key_2]];
                    } else if (\is_array($dataPermissionMenu['permission_2'][$parentTopCode]) === false) {
                        $dataPermissionMenu['permission_2'][$parentTopCode] = [$key_2];
                    } else if (in_array($key_2, $dataPermissionMenu['permission_2'][$parentTopCode]) === false) {
                        array_push($dataPermissionMenu['permission_2'][$parentTopCode], $key_2);
                    }

                    unset($key_2, $val_2, $parentTopCode);
                }

                // 2차 메뉴 정의
                foreach ($dataPermissionMenu['permission_2'] as $key_1 => $val_1) { // 1차
                    foreach ($val_1 as $key_2 => $val_2) { // 2차
                        if (isset($menuTreeList['top'][$key_1]['mid'][$val_2]) === false) {
                            if ($key_1 == 'godo00778' && $val_2 == 'godo00780'); // 본사 - [모바일앱 서비스] 는 continue 제외 (목적:1차 메뉴만 출력하고 저장시 3차 메뉴로 저장)
                            else if ($key_1 == 'godo00801' && $val_2 == 'godo00802'); // 본사 - [샵링커 서비스] 는 continue 제외 (목적:1차 메뉴만 출력하고 저장시 3차 메뉴로 저장)
                            else continue;
                        }
                        $type = 'readonly';

                        if (count($selected['permission_3'][$val_2]) < count($menuTreeList['top'][$key_1]['mid'][$val_2]['last'])) { // 메뉴 개수 대비 읽기&쓰기 개수가 적은 경우
                            $type = 'individual';
                        }
                        else { // 메뉴 개수와 읽기&쓰기 개수가 같은 경우
                            $tmp = array_values($selected['permission_3'][$val_2]);
                            $writableCnt = count(array_column($tmp, 'writable'));
                            $readonlyCnt = count(array_column($tmp, 'readonly'));

                            if ($writableCnt > 0 && $readonlyCnt > 0) { // 개별설정 (읽기, 쓰기 혼합)
                                $type = 'individual';
                            } else if ($writableCnt > 0) { // 읽기+쓰기
                                $type = 'writable';
                            } else if ($readonlyCnt > 0) { // 읽기
                                $type = 'readonly';
                            }
                            unset($tmp, $writableCnt, $readonlyCnt);
                        }

                        $selected['permission_2'][$key_1][$val_2][$type] = 'selected="selected"';
                        unset($key_2, $val_2, $type);
                    }
                    unset($key_1, $val_1);
                }

                // 1차 메뉴 정의
                foreach ($dataPermissionMenu['permission_1'] as $key_1 => $val_1) { // 1차
                    $type = 'readonly';

                    if (count($selected['permission_2'][$val_1]) < count($menuTreeList['top'][$val_1]['mid'])) { // 메뉴 개수 대비 읽기&쓰기 개수가 적은 경우
                        $type = 'individual';
                    }
                    else { // 메뉴 개수와 읽기&쓰기 개수가 같은 경우
                        $tmp = array_values($selected['permission_2'][$val_1]);
                        $individualCnt = count(array_column($tmp, 'individual'));
                        $writableCnt = count(array_column($tmp, 'writable'));
                        $readonlyCnt = count(array_column($tmp, 'readonly'));

                        if ($individualCnt > 0) { // 개별설정 (읽기, 쓰기 혼합)
                            $type = 'individual';
                        } else if ($writableCnt > 0 && $readonlyCnt > 0) { // 개별설정 (읽기, 쓰기 혼합)
                            $type = 'individual';
                        } else if ($writableCnt > 0) { // 읽기+쓰기
                            $type = 'writable';
                        } else if ($readonlyCnt > 0) { // 읽기
                            $type = 'readonly';
                        }
                        unset($tmp, $individualCnt, $writableCnt, $readonlyCnt);
                    }

                    $selected['permission_1'][$val_1][$type] = 'selected="selected"';
                    unset($key_1, $val_1, $type);
                }
            }
        }

        return $selected;
    }

    /**
     * 공급사 부운영자 메뉴권한 설정범위 정의
     *
     * @param array $scmSuperData
     * @param array $menuTreeList
     * @param array $selected
     */
    public function getAdminMenuScmPermissionDisabled($scmSuperData, &$menuTreeList, &$selected)
    {
        if ($scmSuperData['permissionFl'] == 's') { // 공급사 대표운영자 권한범위 = 전체권한(s)
            // empty statement
        } else { // 공급사 대표운영자 권한범위 = 권한선택(l)
            $selectedCount = count($selected);
            $scmSuperData['permissionMenu'] = json_decode($scmSuperData['permissionMenu'], true);
            $scmSuperData['writeEnabledMenu'] = json_decode($scmSuperData['writeEnabledMenu'], true);
            $scmSuperSelected = $this->getAdminMenuPermissionSelected($scmSuperData, $menuTreeList);

            foreach ($menuTreeList['top'] as $menuTreeKey => $menuTreeVal) {
                $selectedType = null;
                if ($selectedCount) { //공급사 부운영자 권한범위 = 권한선택(l)
                    list($selectedType) = array_keys($selected['permission_1'][$menuTreeKey]);
                }

                list($default) = array_keys($scmSuperSelected['permission_1'][$menuTreeKey]);
                $menuTreeList['top'][$menuTreeKey]['default'] = ($default ? $default : '');
                switch ($default) {
                    case 'individual':
                        $menuTreeList['top'][$menuTreeKey]['disabled'] = [
                            '' => '',
                            'readonly' => 'data-disabled-lock="lock" disabled="disabled"',
                            'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                        ];
                        if ($selectedType !== null) {
                            unset($selected['permission_1'][$menuTreeKey]);
                            $selected['permission_1'][$menuTreeKey]['individual'] = 'selected="selected"';
                        }
                        break;
                    case 'readonly':
                        $menuTreeList['top'][$menuTreeKey]['disabled'] = [
                            '' => '',
                            'readonly' => '',
                            'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                        ];
                        if ($selectedType !== null && $selectedType == 'writable') {
                            unset($selected['permission_1'][$menuTreeKey]);
                            $selected['permission_1'][$menuTreeKey]['readonly'] = 'selected="selected"';
                        }
                        break;
                    case 'writable':
                        break;
                    default:
                        $menuTreeList['top'][$menuTreeKey]['disabled'] = [
                            '' => '',
                            'readonly' => 'data-disabled-lock="lock" disabled="disabled"',
                            'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                        ];
                        if ($selectedType !== null) {
                            unset($selected['permission_1'][$menuTreeKey]);
                            $selected['permission_1'][$menuTreeKey][''] = 'selected="selected"';
                        }
                }

                foreach ($menuTreeVal['mid'] as $subTreeKey => $subTreeVal) {
                    $selectedType = null;
                    if ($selectedCount) { //공급사 부운영자 권한범위 = 권한선택(l)
                        list($selectedType) = array_keys($selected['permission_2'][$menuTreeKey][$subTreeKey]);
                    }
                    list($default) = array_keys($scmSuperSelected['permission_2'][$menuTreeKey][$subTreeKey]);
                    $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['default'] = ($default ? $default : '');
                    switch ($default) {
                        case 'individual':
                            $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['disabled'] = [
                                '' => '',
                                'readonly' => 'data-disabled-lock="lock" disabled="disabled"',
                                'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                            ];
                            if ($selectedType !== null) {
                                unset($selected['permission_2'][$menuTreeKey][$subTreeKey]);
                                $selected['permission_2'][$menuTreeKey][$subTreeKey]['individual'] = 'selected="selected"';
                            }
                            break;
                        case 'readonly':
                            $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['disabled'] = [
                                '' => '',
                                'readonly' => '',
                                'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                            ];
                            if ($selectedType !== null && $selectedType == 'writable') {
                                unset($selected['permission_2'][$menuTreeKey][$subTreeKey]);
                                $selected['permission_2'][$menuTreeKey][$subTreeKey]['readonly'] = 'selected="selected"';
                            }
                            break;
                        case 'writable':
                            break;
                        default:
                            $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['disabled'] = [
                                '' => '',
                                'readonly' => 'data-disabled-lock="lock" disabled="disabled"',
                                'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                            ];
                            if ($selectedType !== null) {
                                unset($selected['permission_2'][$menuTreeKey][$subTreeKey]);
                                $selected['permission_2'][$menuTreeKey][$subTreeKey][''] = 'selected="selected"';
                            }
                    }

                    foreach ($subTreeVal['last'] as $lastTreeKey => $lastTreeVal) {
                        $selectedType = null;
                        if ($selectedCount) { //공급사 부운영자 권한범위 = 권한선택(l)
                            list($selectedType) = array_keys($selected['permission_3'][$subTreeKey][$lastTreeKey]);
                        }
                        list($default) = array_keys($scmSuperSelected['permission_3'][$subTreeKey][$lastTreeKey]);
                        $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['last'][$lastTreeKey]['default'] = ($default ? $default : '');
                        switch ($default) {
                            case 'readonly':
                                $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['last'][$lastTreeKey]['disabled'] = [
                                    '' => '',
                                    'readonly' => '',
                                    'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                                ];
                                if ($selectedType !== null && $selectedType == 'writable') {
                                    unset($selected['permission_3'][$subTreeKey][$lastTreeKey]);
                                    $selected['permission_3'][$subTreeKey][$lastTreeKey]['readonly'] = 'selected="selected"';
                                }
                                break;
                            case 'writable':
                                break;
                            default:
                                $menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]['last'][$lastTreeKey]['disabled'] = [
                                    '' => '',
                                    'readonly' => 'data-disabled-lock="lock" disabled="disabled"',
                                    'writable' => 'data-disabled-lock="lock" disabled="disabled"'
                                ];
                                if ($selectedType !== null) {
                                    unset($selected['permission_3'][$subTreeKey][$lastTreeKey]);
                                    $selected['permission_3'][$subTreeKey][$lastTreeKey][''] = 'selected="selected"';
                                }
                        }
                        unset($lastTreeKey, $lastTreeVal, $default, $selectedType);
                    }
                    unset($subTreeKey, $subTreeVal, $default, $selectedType);
                }
                unset($menuTreeKey, $menuTreeVal, $default, $selectedType);
            }
        }
    }

    /**
     * 메뉴 리스트 필터
     *
     * @param array $menuTreeList
     * @return mixed
     */
    public function getMenuTreeListFilter($menuTreeList)
    {
        // 1차메뉴 제외
        unset($menuTreeList['top']['godo00519']); // 본사 - [메뉴 관리] 는 제외
        unset($menuTreeList['top']['godo00611']); // 본사 - [시스템] 는 제외

        // 2차메뉴 제외
        unset($menuTreeList['top']['godo00778']['mid']['godo00780']); // 본사 - [모바일앱> 모바일앱 서비스] 는 제외 (목적:1차 메뉴만 출력하고 저장시 3차 메뉴로 저장)
        unset($menuTreeList['top']['godo00801']['mid']['godo00802']); // 본사 - [마켓연동> 샵링커 서비스] 는 제외 (목적:1차 메뉴만 출력하고 저장시 3차 메뉴로 저장)

        // 3차메뉴 제외
        unset($menuTreeList['top']['godo00467']['mid']['godo00468']['last']['godo00469']); // 본사 - [관리자 기본> 관리자 메인> 관리자 메인] 는 제외
        unset($menuTreeList['top']['godo00470']['mid']['godo00471']['last']['godo00472']); // 공급사 - [관리자 기본> 관리자 메인> 관리자 메인] 는 제외
        unset($menuTreeList['top']['godo00001']['mid']['godo00040']['last']['godo00042']); // 본사 - [기본설정> 결제 정책> 무통장 입금 은행 관리] 제외
        unset($menuTreeList['top']['godo00292']['mid']['godo00293']['last']['godo00294']); // 본사 - [통계> 방문자분석> 방문자 설정] 제외
        unset($menuTreeList['top']['godo00099']['mid']['godo00100']['last']['godo00696']); // 본사 - [주문/배송> 주문 관리> 주문상세상품정보] 제외 (권한 설정이 필요하지 않은 메뉴가 노출되고 있어 삭제처리)
        unset($menuTreeList['top']['godo00099']['mid']['godo00121']['last']['godo00767']); // 본사 - [주문/배송> 택배연동 서비스> CJ대한통운 연동 안내] 제외
        unset($menuTreeList['top']['godo00229']['mid']['godo00498']['last']['godo00499']); // 본사 - [부가서비스> 모바일> 스마트앱 서비스] 제외
        unset($menuTreeList['top']['godo00229']['mid']['godo00478']['last']['godo00481']); // 본사 - [부가서비스> 회원관리> 장바구니 상품 문자알림] 제외
        unset($menuTreeList['top']['godo00229']['mid']['godo00484']['last']['godo00686']); // 본사 - [부가서비스> 운영편의> 에이스카운터+] 제외
        unset($menuTreeList['top']['godo00229']['mid']['godo00484']['last']['godo00488']); // 본사 - [부가서비스> 운영편의> 우체국택배연동 서비스] 제외
        unset($menuTreeList['top']['godo00229']['mid']['godo00484']['last']['godo00491']); // 본사 - [부가서비스> 운영편의> 도매꾹] 제외
        unset($menuTreeList['top']['godo00229']['mid']['godo00484']['last']['godo00492']); // 본사 - [부가서비스> 운영편의> 조음도매] 제외
        unset($menuTreeList['top']['godo00234']['mid']['godo00262']['last']['godo00264']); // 본사 - [마케팅> 리타게팅 광고> 크리테오 설정/관리] 제외 (미사용)
        unset($menuTreeList['top']['godo00292']['mid']['godo00315']['last']['godo00318']); // 본사 - [통계> 상품분석> 장바구니분석] 제외 (미사용)
        unset($menuTreeList['top']['godo00384']['mid']['godo00385']['last']['godo00390']); // 공급사 - [상품> 상품 관리> 상품 아이콘 관리] 제외 (LNB 미노출 메뉴)
        unset($menuTreeList['top']['godo00384']['mid']['godo00385']['last']['godo00391']); // 공급사 - [상품> 상품 관리> 상품 아이콘 등록] 제외 (LNB 미노출 메뉴)
        unset($menuTreeList['top']['godo00384']['mid']['godo00385']['last']['godo00546']); // 공급사 - [상품> 상품 관리> 상품 아이콘 수정] 제외 (LNB 미노출 메뉴)
        unset($menuTreeList['top']['godo00416']['mid']['godo00426']['last']['godo00430']); // 공급사 - [주문/배송> 교환/반품/환불 관리> 환불 상세정보] 제외 (미제공)
        unset($menuTreeList['top']['godo00431']['mid']['godo00432']['last']['godo00569']); // 공급사 - [게시글> 게시글 관리> 상품문의 수정] 제외 (미제공)

        // 3차메뉴 없는 2차메뉴 제외
        // (향후 2차메뉴 없는 1차메뉴도 제외해야 한다면 모바일앱(godo00778), 마켓연동(godo00801)은 1차 메뉴만 출력해야하므로 제외에서 열외해야 함.)
        foreach ($menuTreeList['top'] as $menuTreeKey => $menuTreeVal) {
            foreach ($menuTreeVal['mid'] as $subTreeKey => $subTreeVal) {
                if (is_array($subTreeVal['last']) === false || count($subTreeVal['last']) < 1) {
                    unset($menuTreeList['top'][$menuTreeKey]['mid'][$subTreeKey]);
                }
                unset($subTreeKey, $subTreeVal);
            }
            unset($menuTreeKey, $menuTreeVal);
        }

        return $menuTreeList;
    }

    /**
     * 기능 리스트
     *
     * @param string $adminMenuType 본사(d)/공급사(s)
     * @return array
     */
    public function getMenuFunction($adminMenuType)
    {
        $functionAuth = [
            'godo00467' => [
                'mainStatisticsSales'     => '주요현황 - 매출',
                'mainStatisticsOrder'     => '주요현황 - 주문',
                'mainStatisticsVisit'     => '주요현황 - 방문자',
                'mainStatisticsMember'    => '주요현황 - 신규회원',
            ],
            'godo00051' => [
                'goodsDelete'             => '상품삭제',
                'goodsExcelDown'          => '엑셀다운로드',
                'goodsCommission'         => '판매수수료 등록/수정',
                'goodsNm'                 => '상품명 수정',
                'goodsSalesDate'          => '판매기간 등록/수정',
                'goodsPrice'              => '판매가 수정',
                'goodsStockModify'        => '상품 재고 수정',
                'goodsStockExceptView'    => '상품 상세 재고 수정 제외',
                'goodsSortTop'            => '상단 고정진열 적용',
                'addGoodsCommission'      => '추가상품 판매수수료 등록/수정',
                'addGoodsNm'              => '추가상품 상품명 수정',
            ],
            'godo00099' => [
                'orderState'              => '주문상태 변경',
                'orderExcelDown'          => '엑셀다운로드',
                'orderBank'               => '입금은행 변경',
                'bankdaManual'            => '입금내역 주문서 수동매칭',
                'orderReceiptProcess'     => '현금영수증 처리(발급/거절/취소/삭제)',
            ],
            'godo00138' => [
                'memberHack'              => '회원탈퇴',
                'memberExcelDown'         => '엑셀다운로드',
            ],
            'godo00176' => [
                'boardDelete'             => '게시글 삭제',
            ],
            'godo00271' => [
                'scmCommissionRegister'   => '수수료 일정 등록',
                'scmCommissionModify'     => '수수료 일정 수정',
            ],
            'godo00292' => [
                'orderSalesStatisticsProcess' => '통계 수집 방식 설정',
            ],
            'godo00458' => [
                'workPermissionFl'        => '개발권한',
                'debugPermissionFl'       => '디버그권한',
            ],
        ];

        // 뱅크다 미사용 경우 필터
        $bankdaConfig = gd_policy('order.bankda');
        if($bankdaConfig['useFl'] != 'y')  {
            unset($functionAuth['godo00099']['bankdaManual']);
        }

        // 공급사 미사용 경우 필터
        if(gd_use_provider() !== true) {
            unset($functionAuth['godo00271']);
        }

        // 고도몰5pro 미사용 경우 필터
        $globals = \App::getInstance('globals');
        $license = $globals->get('gLicense');
        if ($license['ecCode'] !== 'rental_mx_pro') {
            unset($functionAuth['godo00458']);
        }

        // 공급사 경우 일부 필터
        if ($adminMenuType === 's') {
            $functionAuth = [
                'godo00470' => $functionAuth['godo00467'], // 관리자 기본
                'godo00384' => $functionAuth['godo00051'], // 상품
                'godo00416' => $functionAuth['godo00099'], // 주문/배송
                'godo00431' => $functionAuth['godo00176'], // 게시판
                'godo00445' => $functionAuth['godo00292'], // 통계
            ];
            unset($functionAuth['godo00470']['mainStatisticsVisit']);
            unset($functionAuth['godo00470']['mainStatisticsMember']);
            unset($functionAuth['godo00416']['orderBank']);
            unset($functionAuth['godo00416']['bankdaManual']);
            unset($functionAuth['godo00416']['orderReceiptProcess']);
            unset($functionAuth['godo00445']['orderSalesStatisticsProcess']);
        }

       return $functionAuth;
    }

    /**
     * setAdminMenuWhere
     *
     * @param $arrWhere
     */
    protected function setAdminMenuWhere($arrWhere)
    {
        // 본사, 공급사 구분
        $this->arrWhere[] = 'am.adminMenuType = ?';
        $this->db->bind_param_push($this->arrBind, 's', $arrWhere['adminMenuType']);
        // 메뉴 상품군 구분
        if ($arrWhere['adminMenuEcKind'] == 'standard') {
            $adminMenuEcKind = 's';
        } else {
            $adminMenuEcKind = 'p';
        }
        $this->arrWhere[] = 'am.adminMenuEcKind IN (?, ?)';
        $this->db->bind_param_push($this->arrBind, 's', 'a');
        $this->db->bind_param_push($this->arrBind, 's', $adminMenuEcKind);
    }

    /**
     * 솔루션 메뉴 제한 이미지 설정(메뉴가 보이나 프리미엄 전용 이라는 아이콘이 노출되고 클릭시 프리미엄 서비스용이라는 알림)
     * 고도5는 지금 정책으로는 메뉴 숨김처리 하기로 함
     *
     * @author artherot
     *
     * @param string $levelLimit 체크 레벨
     *
     * @return string 이미지나 빈값
     */
    protected function _smartAuthorityMenu($levelLimit)
    {
        // 제한 메뉴가 아닌 경우
        if (empty($levelLimit) === true) {
            return;
        }
        // 제한 메뉴인 경우
        if (in_array(Globals::get('gLicense.ecKind'), $levelLimit) === true) {
            return SkinUtils::makeImageTag(PATH_ADMIN_GD_SHARE . 'img/btn_premium.gif', null, __('프리미엄서비스'), 'middle hand', 'onclick="premium_ui(\'close\');"');
        }

        return;
    }


    /**
     * 솔루션 제한 페이지 설정
     *
     * @author artherot
     *
     * @param string $levelLimit 체크 레벨
     *
     * @return boolean true(제한 페이지), false(미제한 페이지)
     */
    protected function _smartAuthorityPage($levelLimit)
    {
        // 제한 페이지가 아닌 경우
        if (empty($levelLimit) === true) {
            return false;
        }
        // 제한 페이지인 경우
        if (in_array(Globals::get('gLicense.ecKind'), $levelLimit) === true) {
            return true;
        }

        return false;
    }

    /**
     * convertTreeForJson
     *
     * 디렉토리 배열을 키/값을 가진 JSON 데이터화 가능한 배열로 변경
     * NHN Entertainment의 tui.component.tree 처리 방식에 맞게 변환 처리
     *
     * @param $dataTreeArrList
     *
     * @return array
     */
    public function convertTreeForJson($dataTreeArrList)
    {
        $result = [];

        foreach ($dataTreeArrList as $key => $val) {
            if (is_array($val)) {
                array_push(
                    $result, [
                        'text'     => $key,
                        'number'   => $val['number'],
                        'children' => self::convertTreeForJson($val),
                    ]
                );
            } else {
                if ($key != 'number') {
                    array_push(
                        $result, [
                            'text'   => $key,
                            'number' => $val['number'],
                        ]
                    );
                }
            }
        }

        return $result;
    }

    /**
     * getAdminMenuMaxSort
     *
     * @param string $parentNo
     *
     * @return array|string
     */
    public function getAdminMenuMaxSort($parentNo = '')
    {
        if ($parentNo) {
            // 메뉴 Depth 구분
            $this->arrWhere[] = 'am.adminMenuParentNo = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $parentNo);
        }

        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT MAX(am.adminMenuSort) FROM ' . DB_ADMIN_MENU . ' as am ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if (count($getData) == 1) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }
    }

    // 관리자 메뉴 등록
    // @todo 개발중
    public function setAdminMenu($postData)
    {
        $arrBind = [];
        // 최대 정렬 번호 가져오기
        if (substr($postData['mode'], 0, 6) == 'insert') {
            $maxSort = $this->getAdminMenuMaxSort($postData['adminMenuParentNo']);
            if ($postData['adminMenuDepth'] == 1) {
                $depthLength = 1;
            } else if ($postData['adminMenuDepth'] == 2) {
                $depthLength = 10;
            } else if ($postData['adminMenuDepth'] == 3) {
                $depthLength = 100;
            }
            $thisSort = (floor($maxSort / $depthLength) * $depthLength) + $depthLength;
        }
        $arrBind['adminMenuType'] = $postData['adminMenuType'];
        $arrBind['adminMenuProductCode'] = $postData['adminMenuProductCode'];
        $arrBind['adminMenuPlusCode'] = $postData['adminMenuPlusCode'];
        $arrBind['adminMenuCode'] = $postData['adminMenuCode'];
        $arrBind['adminMenuDepth'] = $postData['adminMenuDepth'];
        $arrBind['adminMenuParentNo'] = $postData['adminMenuParentNo'];
        $arrBind['adminMenuSort'] = $thisSort;
        $arrBind['adminMenuName'] = $postData['adminMenuName'];
        $arrBind['adminMenuUrl'] = $postData['adminMenuUrl'];
        $arrBind['adminMenuDisplayType'] = $postData['adminMenuDisplayType'];
        $arrBind['adminMenuSettingType'] = $postData['adminMenuSettingType'];
        $arrBind['adminMenuEcKind'] = $postData['adminMenuEcKind'];
    }

    /**
     * 공급사 디렉토리 접근 여부
     *
     * @static
     * @return bool
     */
    static public function isProviderDirectory()
    {
        return preg_match("/\/provider.*/", Request::getFullDirectoryUri());
    }

    /**
     * 공급사 디렉토리 접근 여부
     *
     * @static
     * @return bool
     */
    static public function isAdminShareDirectory()
    {
        return preg_match("/\/share.*/", Request::getFullDirectoryUri());
    }

    /**
     * 메뉴키 추출 (메뉴얼 전용)
     *
     * @param string $adminMenuType 본사(d)/공급사(s)
     * @param string $topMenu       최상위 메뉴 (함수 명)
     * @param string $pageUrl       페이지 주소
     *
     * @return bool|string 메뉴키
     */
    public function getMenuKey($adminMenuType, $topMenu, $pageUrl)
    {
        $getData = $this->getAdminMenuList($adminMenuType, $topMenu);

        $getKey = '';
        foreach ($getData as $key => $val) {
            $tUrl = parse_url($val['tUrl']);
            if (empty($tUrl['query']) == false) {
                $tUrl1 = implode('?', $tUrl);
                if (trim($pageUrl) == trim($tUrl1)) {
                    $getKey = $key;
                    break;
                }
            } else {
                if (trim($pageUrl) == trim($tUrl['path'])) {
                    $getKey = $key;
                    break;
                }
            }
        }
        $getData = $getData[$getKey];

        return $getData['sCode'];
    }

    /**
     * getAdminMenuAccessAuth
     *
     * 관리자 메뉴 접근 권한 체크
     * 관리자 메뉴 접근 권한 체크를 하며, 권한이 없을 경우 layoutContent 에 접근권한 없음 알림
     * postHandler 에서 처리되므로 index()의 callMenu 실행 후 처리됨
     *
     * @param array $pageLno        접속페이지의 1,2,3차의 메뉴고유번호
     * @param array $pageLocation   접속페이지의 1,2,3차의 메뉴명
     * @param array $pageAccessMenu 접속한 운영자의 접근권한
     *
     * @return bool
     * @throws AlertRedirectException
     */
    public function getAdminMenuAccessAuth($pageLno, $pageLocation, $pageAccessMenu)
    {
        // 운영자 권한 설정
        $accessible['check'] = true;

        if ($pageAccessMenu != 'all') {
            $sMenuCode = $pageLno[1];
            $tMenuCode = $pageLno[2];
            $accessible['title'] = $pageLocation[2];
            if (empty($sMenuCode) === false && array_key_exists($sMenuCode, $pageAccessMenu) !== true) {
                $accessible['check'] = false;
            }
            if (empty($tMenuCode) === false && in_array($tMenuCode, $pageAccessMenu[$sMenuCode]) === false) {
                $accessible['check'] = false;
            }
        }

        return $accessible;
    }

    /**
     * getAdminMenuWritableAuth
     *
     * 관리자 메뉴 쓰기 권한 체크
     * 관리자 메뉴 쓰기 권한 체크를 하며, 권한이 없을 경우 접근권한 없음 알림
     * postHandler 에서 처리되므로 index()의 callMenu 실행 후 처리됨
     *
     * @param array $pageLno        접속페이지의 1,2,3차의 메뉴고유번호
     * @param array $pageLocation   접속페이지의 1,2,3차의 메뉴명
     * @param array $pageAccessMenu 접속한 운영자의 접근권한
     * @param array $writeEnabledMenu   접속한 운영자의 쓰기권한
     *
     * @return array
     */
    public function getAdminMenuWritableAuth($pageLno, $pageLocation, $pageAccessMenu, $writeEnabledMenu)
    {
        $writable['check'] = true;
        $session = \App::getInstance('session');

        if (empty($pageAccessMenu) === false && $pageAccessMenu != 'all') {
            $sMenuCode = $pageLno[1];
            $tMenuCode = $pageLno[2];
            $writable['title'] = $pageLocation[2];

            if ($session->get('manager.isSuper') == 'cs') { // CS계정
                if (empty($sMenuCode) === false && array_key_exists($sMenuCode, $pageAccessMenu) !== true) {
                    $writable['check'] = false;
                }
                if (empty($tMenuCode) === false && in_array($tMenuCode, $pageAccessMenu[$sMenuCode]) === false) {
                    $writable['check'] = false;
                }
            } else { // 본사&공급사 계정
                if (empty($sMenuCode) === false && array_key_exists($sMenuCode, $writeEnabledMenu) !== true) {
                    $writable['check'] = false;
                }
                if (empty($tMenuCode) === false && in_array($tMenuCode, $writeEnabledMenu[$sMenuCode]) === false) {
                    $writable['check'] = false;
                }
            }
        }

        return $writable;
    }

    /**
     * setAccessMenu
     *
     * @param $managerSno
     */
    public function setAccessMenu($managerSno)
    {
        if ($managerSno > 0) {
            $arrBind = [];
            $this->db->strField = 'permissionFl, permissionMenu';
            $this->db->strWhere = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $managerSno);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            // 운영자 메뉴 권한 설정
            if ($data['permissionFl'] == 'l') { // 운영권한 - 권한선택
                $permission = json_decode($data['permissionMenu'], true);
                $this->accessMenu = $permission['permission_3'];
            } else if ($data['permissionFl'] == 's') { // 운영권한 - 전체권한
                $this->accessMenu = 'all';
            }
        }
    }

    /**
     * setAccessWriteEnabledMenu
     *
     * @param $managerSno
     */
    public function setAccessWriteEnabledMenu($managerSno)
    {
        if ($managerSno > 0) {
            $arrBind = [];
            $this->db->strField = 'permissionFl, permissionMenu, writeEnabledMenu';
            $this->db->strWhere = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $managerSno);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            // 운영자 메뉴 권한 설정
            if ($data['permissionFl'] == 'l') { // 운영권한 - 권한선택
                $permission = json_decode($data['permissionMenu'], true);
                $this->accessMenu = $permission['permission_3'];
                $this->writeEnabledMenu = json_decode($data['writeEnabledMenu'], true);
            } else if ($data['permissionFl'] == 's') { // 운영권한 - 전체권한
                $this->accessMenu = $this->writeEnabledMenu = 'all';
            }

            // 공급사 부운영자는 공급사 대표운영자에게 부여한 메뉴권한 내에서만 실행 가능
            $session = \App::getInstance('session');
            if (gd_is_provider() && $session->get('manager.isSuper') == 'n') {
                $arrBind = [];
                $this->db->strField = 'permissionFl, permissionMenu, writeEnabledMenu';
                $this->db->strWhere = 'scmNo = ? and isDelete = "n" AND isSuper = "y"';
                $this->db->bind_param_push($arrBind, 'i', $session->get('manager.scmNo'));
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
                $superData = $this->db->query_fetch($strSQL, $arrBind, false);

                if ($data['permissionFl'] == 's') { // 공급사 부운영자 운영권한이 '전체권한(s)' 이면 공급사 대표운영자 메뉴권한으로 적용
                    if ($superData['permissionFl'] == 'l') { // 공급사 대표운영자 운영권한 - 권한선택
                        $permission = json_decode($superData['permissionMenu'], true);
                        $this->accessMenu = $permission['permission_3'];
                        $this->writeEnabledMenu = json_decode($superData['writeEnabledMenu'], true);
                    } else if ($superData['permissionFl'] == 's') { // 공급사 대표운영자 운영권한 - 전체권한
                        $this->accessMenu = $this->writeEnabledMenu = 'all';
                    }
                } else { // 공급사 부운영자 운영권한이 '권한선택(l)' 이면 공급사 대표운영자 메뉴권한 범위 내로 제한
                    if ($superData['permissionFl'] == 'l') { // 공급사 대표운영자 운영권한 - 권한선택
                        $superPermission = json_decode($superData['permissionMenu'], true);
                        $superWriteEnabled = json_decode($superData['writeEnabledMenu'], true);
                        // 읽기 권한 필터
                        foreach ($this->accessMenu as $key => $val) {
                            if (isset($superPermission['permission_3'][$key]) === false) {
                                unset($this->accessMenu[$key]);
                                continue;
                            }
                            $this->accessMenu[$key] = array_intersect($val, $superPermission['permission_3'][$key]);
                        }
                        // 쓰기 권한 필터
                        foreach ($this->writeEnabledMenu as $key => $val) {
                            if (isset($superWriteEnabled[$key]) === false) {
                                unset($this->writeEnabledMenu[$key]);
                                continue;
                            }
                            $this->writeEnabledMenu[$key] = array_intersect($val, $superWriteEnabled[$key]);
                        }
                    } else if ($superData['permissionFl'] == 's') { // 공급사 대표운영자 운영권한 - 전체권한
                        // empty statement
                    }
                }
            }
        }
    }

    /**
     * 메뉴 읽기+쓰기 상태 리턴
     *
     * @param $topMenu
     * @param $subMenu
     * @param $lastMenu
     * @param null $adminMenuType
     * @return string
     */
    public function getAccessMenuStatus($topMenu, $subMenu, $lastMenu, $adminMenuType = null)
    {
        $status = ''; // 권한없음
        $session = \App::getInstance('session');

        if ($session->get('manager.isSuper') == 'cs') { // CS계정
            $enabledMenu = $this->accessMenu;
        } else { // 본사&공급사 계정
            $enabledMenu = $this->writeEnabledMenu;
        }

        if ($enabledMenu !== 'all') { // 운영권한 - 권한선택
            // 관리자 메뉴 정보 조회
            // 본사, 공급사 구분
            $adminMenuType = ($adminMenuType === true ? 's' : 'd');
            $this->arrWhere[] = 'f.adminMenuType = ? AND s.adminMenuType = ? AND t.adminMenuType = ?';
            $this->db->bind_param_push($this->arrBind, 's', $adminMenuType);
            $this->db->bind_param_push($this->arrBind, 's', $adminMenuType);
            $this->db->bind_param_push($this->arrBind, 's', $adminMenuType);

            // 1차, 2차, 3차 메뉴 구분
            $this->arrWhere[] = 'f.adminMenuCode = ? AND s.adminMenuCode = ? AND t.adminMenuCode = ?';
            $this->db->bind_param_push($this->arrBind, 's', $topMenu);
            $this->db->bind_param_push($this->arrBind, 's', $subMenu);
            $this->db->bind_param_push($this->arrBind, 's', $lastMenu);

            $this->db->strField = 't.adminMenuNo, t.adminMenuParentNo, t.adminMenuCode';
            $join[] = 'LEFT JOIN ' . DB_ADMIN_MENU . ' as s ON s.adminMenuDepth = 2 AND f.adminMenuNo = s.adminMenuParentNo';
            $join[] = 'LEFT JOIN ' . DB_ADMIN_MENU . ' as t ON t.adminMenuDepth = 3 AND s.adminMenuNo = t.adminMenuParentNo';
            $this->db->strJoin = implode(' ', $join);
            $this->db->strWhere = implode(' AND ', $this->arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . ' as f ' . implode(' ', $query);
            list($menuData) = $this->db->query_fetch($strSQL, $this->arrBind);
            unset($this->arrBind);
            unset($this->arrWhere);

            if ($menuData['adminMenuNo'] != '' && $menuData['adminMenuParentNo'] != '') {
                if (in_array($menuData['adminMenuNo'], $enabledMenu[$menuData['adminMenuParentNo']]) === true) { // 읽기 + 쓰기
                    $status = 'writable';
                } else if (in_array($menuData['adminMenuNo'], $this->accessMenu[$menuData['adminMenuParentNo']]) === true) { // 읽기
                    $status = 'readonly';
                }
            }
        } else if ($enabledMenu === 'all') { // 운영권한 - 전체권한
            $status = 'writable';
        }

        return $status;
    }

    /**
     * getMainStatisticsAccessMenu
     *
     * @param null $adminMenuType
     * @return mixed
     */
    public function getMainStatisticsAccessMenu($adminMenuType = null)
    {
        $returnMainAccessMenu['sales'] = false;
        $returnMainAccessMenu['order'] = false;
        $returnMainAccessMenu['visit'] = false;
        $returnMainAccessMenu['member'] = false;

        // 전체 권한이면 메인통계 전부 확인 가능으로 리턴
        if ($this->accessMenu == 'all') {
            $returnMainAccessMenu['sales'] = true;
            $returnMainAccessMenu['order'] = true;
            if (!$adminMenuType) {
                $returnMainAccessMenu['visit'] = true;
                $returnMainAccessMenu['member'] = true;
            }
            return $returnMainAccessMenu;
        }

        // 부분 권한이면 통계 권한 확인 후 가능여부 리턴
        if ($adminMenuType) { // 공급사 통계 메뉴 권한 코드
            $accessCode['sales'] = 'godo00446';
            $accessCode['order'] = 'godo00451';
        } else { // 본사 통계 메뉴 권한 코드
            $accessCode['sales'] = 'godo00321';
            $accessCode['order'] = 'godo00340';
            $accessCode['visit'] = 'godo00293';
            $accessCode['member'] = 'godo00304';
        }

        // 관리자 메인 통계 권한 확인 - 매출 탭
        if(count($this->accessMenu[$accessCode['sales']]) > 0) {
            $returnMainAccessMenu['sales'] = true;
        }
        // 관리자 메인 통계 권한 확인 - 주문 탭
        if(count($this->accessMenu[$accessCode['order']]) > 0) {
            $returnMainAccessMenu['order'] = true;
        }
        // 관리자 메인 통계 권한 확인 - 방문자 탭
        if(count($this->accessMenu[$accessCode['visit']]) > 0) {
            $returnMainAccessMenu['visit'] = true;
        }
        // 관리자 메인 통계 권한 확인 - 회원 탭
        if(count($this->accessMenu[$accessCode['member']]) > 0) {
            $returnMainAccessMenu['member'] = true;
        }

        return $returnMainAccessMenu;
    }

    /**
     * getMainStatisticsFunctionAuthMenu
     *
     * @param null $adminMenuType
     * @return mixed
     */
    public function getMainStatisticsFunctionAuthMenu($adminMenuType = null)
    {
        $session = \App::getInstance('session');
        $returnMainAccessMenu['sales'] = true;
        $returnMainAccessMenu['order'] = true;
        $returnMainAccessMenu['visit'] = true;
        $returnMainAccessMenu['member'] = true;
        if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.mainStatisticsSales') != 'y') {
            $returnMainAccessMenu['sales'] = false;
        }
        if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.mainStatisticsOrder') != 'y') {
            $returnMainAccessMenu['order'] = false;
        }
        if ($adminMenuType === true || ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.mainStatisticsVisit') != 'y')) {
            $returnMainAccessMenu['visit'] = false;
        }
        if ($adminMenuType === true || ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.mainStatisticsMember') != 'y')) {
            $returnMainAccessMenu['member'] = false;
        }

        return $returnMainAccessMenu;
    }

    /**
     * getAdminMenuByCode
     * 코드값 기준으로 메뉴 정보 가져오기
     * @param       $code           [코드 값]
     * @param array $arrWhere       [추가 조건문]   ex) ['adminMenuNo'=>'godo0000']
     */
    public function getAdminMenuByCode($code, array $arrWhere = [], $fields = '')
    {
        if ($code === 'barcode') { //미노출 (바코드 기능 제거되어 사용 안함)
            $getData[0] = [
                'adminMenuNo' => 0,
                'adminMenuType' => '',
                'adminMenuUrl' => '',
                'adminMenuDisplayType' => 'n'
            ];
        } else {
            $arrBind = $tmpWhere = [];
            //추가된 조건문이 있을 경우
            if (empty($arrWhere) === false && count($arrWhere) > 0) {
                foreach ($arrWhere as $fieldName => $fieldValue) {
                    $tmpWhere[] = 'am.' . $fieldName . ' = ?';
                    $this->db->bind_param_push($arrBind, 's', $fieldValue);
                }
            }
            //필요한 필드 정의
            if ($fields === '') {
                $fields = ' am.adminMenuNo, am.adminMenuType, am.adminMenuUrl, am.adminMenuDisplayType ';
            }
            $tmpWhere[] = 'am.adminMenuCode = ?';
            $this->db->bind_param_push($arrBind, 's', $code);
            $strWhere = implode(' AND ', $tmpWhere);
            $strSQL = 'SELECT ' . $fields . ' FROM ' . DB_ADMIN_MENU . ' as am WHERE ' . $strWhere;
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($tmpWhere);
            unset($arrBind);
            unset($strWhere);
        }
        return gd_htmlspecialchars_stripslashes($getData);
    }
}
