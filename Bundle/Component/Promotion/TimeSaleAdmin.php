<?php
/**
 * 상품노출형태 관리
 * @author atomyang
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Promotion;

use Component\Member\Manager;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Globals;
use LogHandler;
use Request;
use Session;


class TimeSaleAdmin extends \Component\Promotion\TimeSale
{
    const ECT_INVALID_ARG = 'Config.ECT_INVALID_ARG';
    const TEXT_INVALID_NOTARRAY_ARG = '%s이 배열이 아닙니다.';
    const TEXT_INVALID_EMPTY_ARG = '%s이 비어있습니다.';
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_USELESS_VALUE = '%s은(는) 사용할 수 없습니다.';


    protected $db;

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

    /**
     * @var array 상세 선택 기본 값
     */


    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        parent::__construct();
    }

    /**
     * saveInfoThemeConfig
     *
     * @param $arrData
     * @throws Except
     */
    public function saveInfoTimeSale($arrData)
    {
        // 이벤트명
        if (Validator::required(gd_isset($arrData['timeSaleTitle'])) === false) {
            throw new \Exception(__('이벤트명은 필수 항목입니다.'), 500);
        }

        if($arrData['promotionDate']) {
            $arrData['startDt'] = $arrData['promotionDate'][0];
            $arrData['endDt'] = $arrData['promotionDate'][1];
        }

        if(empty($arrData['goodsPriceViewFl']) === true) {
            $arrData['goodsPriceViewFl'] = "n";
        }

        if(empty($arrData['orderCntDateFl']) === true) {
            $arrData['orderCntDateFl'] = "n";
        }

        //타임세일 남은기간 PC/MOBILE 노출 여부
        if (empty($arrData['leftTimeDisplayType']) === false && is_array($arrData['leftTimeDisplayType']) === true) {
            $arrData['leftTimeDisplayType'] = implode(',', $arrData['leftTimeDisplayType']);
        } else {
            $arrData['leftTimeDisplayType'] = '';
        }

        switch ($arrData['displayFl']) {
            case 'all':
                $arrData['pcDisplayFl'] = "y";
                $arrData['mobileDisplayFl'] = "y";
                break;
            case 'p':
                $arrData['pcDisplayFl'] = "y";
                $arrData['mobileDisplayFl'] = "n";
                break;
            case 'm':
                $arrData['pcDisplayFl'] = "n";
                $arrData['mobileDisplayFl'] = "y";
                break;
        }

        $arrData['goodsNo'] = implode(INT_DIVISION, $arrData['goodsNoData']);
        $arrData['managerNo']  =  Session::get('manager.sno');

        // 테마명 정보 저장
        if ($arrData['mode'] == 'modify') {
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableTimeSale', array_keys($arrData)), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_TIME_SALE, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableTimeSale(), $arrData, 'insert');
            $this->db->set_insert_db(DB_TIME_SALE, $arrBind['param'], $arrBind['bind'], 'y');
        }

        unset($arrBind);

        $this->setUseCntThemeConfig();

        return $arrData['sno'];
    }

    public function setUseCntThemeConfig()
    {
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $goods->setRefreshThemeConfig('event');
    }



    /**
     * getAdminListDisplayThemeConfig
     *
     * @return mixed
     */
    public function getAdminListTimeSale()
    {
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchTimeSale($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        if($getValue['delFl']) $page->recode['amount'] = $this->db->getCount(DB_TIME_SALE, 'sno', ' WHERE delFl = "'.$getValue['delFl'].'"'); // 전체 레코드 수
        else $page->recode['amount'] = $this->db->getCount(DB_TIME_SALE); // 전체 레코드 수

        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON ts.managerNo = m.sno ';

        // 현 페이지 결과
        $this->db->strField = "ts.*,m.managerNm,m.managerId,m.isDelete";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_TIME_SALE . ' as ts ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);
        // 검색 레코드 수
        $page->recode['total'] = $this->db->query_count($query, DB_TIME_SALE . ' as ts ', $this->arrBind);
        $page->setPage();

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
     * @param int $searchPeriod
     */
    public function setSearchTimeSale($searchData, $searchPeriod = '-1')
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableTimeSale');

        //검색설정
        $this->search['sortList'] = array(
            'regDt desc' => '등록일 ↑',
            'regDt asc' => '등록일 ↓',
            'timeSaleTitle desc' => '타임세일명 ↑',
            'timeSaleTitle asc' => '타임세일명 ↓',
            'startDt desc' => '시작일 ↑',
            'startDt asc' => '시작일 ↓'
        );

        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '6');
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['displayFl'] = gd_isset($searchData['displayFl'], '');
        $this->search['stateFl'] = gd_isset($searchData['stateFl'], 'all');
        $this->search['delFl'] = gd_isset($searchData['delFl']);

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-' . $this->search['searchPeriod'] . ' day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['displayFl'][$searchData['displayFl']]  = $this->checked['stateFl'][$searchData['stateFl']] = "checked='checked'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";


        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = "ts.".$this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('timeSaleTitle', 'managerNm');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {

                $this->arrWhere[] =  $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            }
        }

        // 노출
        if(gd_isset($this->search['displayFl'])) {

            if($this->search['displayFl'] =='p')   {
                $this->arrWhere[] = 'mobileDisplayFl = ?';
                $this->arrWhere[] = 'pcDisplayFl = ?';

                $this->db->bind_param_push($this->arrBind, 's', 'n');
                $this->db->bind_param_push($this->arrBind, 's', 'y');
            } else if($this->search['displayFl'] =='m') {
                $this->arrWhere[] = 'pcDisplayFl = ?';
                $this->arrWhere[] = 'mobileDisplayFl = ?';

                $this->db->bind_param_push($this->arrBind, 's', 'n');
                $this->db->bind_param_push($this->arrBind, 's', 'y');
            } else {
                $this->arrWhere[] = 'pcDisplayFl = ?';
                $this->arrWhere[] = 'mobileDisplayFl = ?';

                $this->db->bind_param_push($this->arrBind, 's', 'y');
                $this->db->bind_param_push($this->arrBind, 's', 'y');
            }

        }

        if($this->search['delFl']) {
            $this->arrWhere[] = 'delFl  = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['delFl']);
        }

        if ($this->search['stateFl'] !='all') {
            switch ($this->search['stateFl']) {
                case 'n':
                    $this->arrWhere[] = 'startDt < ? and endDt > ? ';
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    break;
                case 'e':
                    $this->arrWhere[] = 'endDt < ? ';
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    break;
                case 'd':
                    $this->arrWhere[] = 'startDt > ? ';
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    break;
            }
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }

    /**
     * setDeleteTimeSale
     *
     * @param $themeCd
     */
    public function setDeleteTimeSale($arrData)
    {
        foreach($arrData['sno'] as $k => $v ) {
            if($arrData['stateFl'][$k] =='d') {
                $strWhere = "sno ='".$k."'";
                $this->db->set_delete_db(DB_TIME_SALE, $strWhere);
            } else {
                $strWhere = "sno ='".$k."'";
                $this->db->set_update_db(DB_TIME_SALE, array("delFl = 'y'"), $strWhere);
            }
        }

        $this->setUseCntThemeConfig();

    }


    /**
     * setCloseTimeSale
     *
     * @param $themeCd
     */
    public function setCloseTimeSale($sno)
    {
        $strWhere = "sno IN ('" . implode("','", $sno) . "')";
        $this->db->set_update_db(DB_TIME_SALE, array("endDt = now()"), $strWhere);
    }

    /**
     * getDataThemeCongif
     *
     * @param null $themeCd
     * @return mixed
     */
    public function getDataTimeSale($sno)
    {
        // --- 등록인 경우
        if (!$sno) {
            // 기본 정보
            $data['mode'] = 'register';
            // 기본값 설정
            DBTableField::setDefaultData('tableTimeSale', $data);
            $data['updateFl'] = "y";

            // --- 수정인 경우
        } else {
            // 테마 정보
            $data = $this->getInfoTimeSale($sno);
            $data['mode'] = 'modify';

            if($data['goodsNo']) {
                $goodsAdmin = \App::load('\\Component\\Goods\\GoodsAdmin');
                $data['goodsNo'] = $goodsAdmin->getGoodsDataDisplay($data['goodsNo']);
            }
            if($data['fixGoodsNo']) $data['fixGoodsNo'] = explode(INT_DIVISION, $data['fixGoodsNo']);

            // 기본값 설정
            DBTableField::setDefaultData('tableTimeSale', $data);
        }

        if($data['mobileDisplayFl'] =='y' && $data['pcDisplayFl'] =='y')  $data['displayFl'] = 'all';
        else if($data['mobileDisplayFl'] =='y')  $data['displayFl'] = 'm';
        else if($data['pcDisplayFl'] =='y')  $data['displayFl'] = 'p';

        $checked = array();
        $checked['goodsPriceViewFl'][gd_isset($data['goodsPriceViewFl'])]  = $checked['orderCntDateFl'][gd_isset($data['orderCntDateFl'])]  = $checked['orderCntDisplayFl'][gd_isset($data['orderCntDisplayFl'])] = $checked['stockFl'][gd_isset($data['stockFl'])] = $checked['memberDcFl'][gd_isset($data['memberDcFl'])] = $checked['mileageFl'][gd_isset($data['mileageFl'])] = $checked['couponFl'][gd_isset($data['couponFl'])] = $checked['displayFl'][gd_isset($data['displayFl'])]  = $checked['moreBottomFl'][gd_isset($data['moreBottomFl'])]  = 'checked="checked"';

        $checked['leftTimeDisplayType']['pc'] = strpos($data['leftTimeDisplayType'], 'PC') !== false ? 'checked="checked"' : '';
        $checked['leftTimeDisplayType']['m'] = strpos($data['leftTimeDisplayType'], 'MOBILE') !== false ? 'checked="checked"' : '';

        $selected = array();
        $selected['pcThemeCd'][gd_isset($data['pcThemeCd'])]  = $selected['mobileThemeCd'][gd_isset($data['mobileThemeCd'])]  = 'selected="selected"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

}
