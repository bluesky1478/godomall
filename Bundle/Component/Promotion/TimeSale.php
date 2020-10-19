<?php
/**
 * 상품노출형태 관리
 * @author atomyang
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Promotion;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Globals;
use LogHandler;
use Request;


class TimeSale
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
    }


    /**
     * getInfoThemeConfig
     *
     * @param null $sno
     * @param null $timeSaleField
     * @param null $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getInfoTimeSale($sno = null, $timeSaleField = null, $arrBind = null, $dataArray = false)
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === false) return false;

        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere    = " ts.sno  = ? AND ".$this->db->strWhere;
            } else {
                $this->db->strWhere    = " ts.sno  = ?";
            }
            $this->db->bind_param_push($arrBind, 's', $sno);
        }
        if ($timeSaleField) {
            if ($this->db->strField) {
                $this->db->strField    = $timeSaleField.', '.$this->db->strField;
            } else {
                $this->db->strField    = $timeSaleField;
            }
        }
        $query    = $this->db->query_complete();
        $strSQL = 'SELECT '.array_shift($query).' FROM '.DB_TIME_SALE.' ts '.implode(' ', $query);
        $getData    = $this->db->query_fetch($strSQL, $arrBind);


        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * 상품 번호 기준 해당 타임세일 프로모션 여부 확인
     * getGoodsTimeSale
     *
     * @param null $sno
     * @param null $timeSaleField
     * @param null $arrBind
     * @param bool|false $dataArray
     *
     *@return string
     */
    public function getGoodsTimeSale($goodsNo)
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === false) return false;

        $arrBind = [];

        if (\Request::isMobile()) {
            $where[]  = 'mobileDisplayFl =?';
        } else {
            $where[]  = 'pcDisplayFl =?';
        }
        $this->db->bind_param_push($arrBind, 's','y');
        $where[] = "FIND_IN_SET(?,replace(ts.goodsNo,'".INT_DIVISION."',','))";
        $this->db->bind_param_push($arrBind, 's', $goodsNo);

        $where[]  = 'startDt < ? AND  endDt > ? ';
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));

        $this->db->strField    = "ts.*";
        $this->db->strWhere = implode(' AND ', gd_isset($where));

        $query  = $this->db->query_complete();
        $strSQL = 'SELECT '.array_shift($query).' FROM '.DB_TIME_SALE.' as ts '.implode(' ', $query);

        $getData    = $this->db->query_fetch($strSQL, $arrBind);

        unset($arrBind);

        if($getData) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        } else {
            return false;
        }
    }

    /**
     * 상품 번호 기준 해당 타임세일 프로모션 여부 확인
     * getListTimeSale
     *
     * @param null $sno
     * @param null $timeSaleField
     * @param null $arrBind
     * @param bool|false $dataArray
     *
     *@return string
     */
    public function getListTimeSale()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === false) return false;

        $arrBind = [];

        $where[]  = 'startDt < ? AND  endDt > ? ';
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));

        if (\Request::isMobile()) {
            $where[]  = 'mobileDisplayFl =?';
        } else {
            $where[]  = 'pcDisplayFl =?';
        }
        $this->db->bind_param_push($arrBind, 's','y');

        $this->db->strField    = "ts.timeSaleTitle,ts.sno";
        $this->db->strWhere = implode(' AND ', gd_isset($where));

        $query  = $this->db->query_complete();
        $strSQL = 'SELECT '.array_shift($query).' FROM '.DB_TIME_SALE.' as ts '.implode(' ', $query);

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        unset($arrBind);

        return gd_htmlspecialchars_stripslashes($getData);


    }
}
