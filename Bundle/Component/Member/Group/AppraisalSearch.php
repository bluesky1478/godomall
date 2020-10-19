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


use Component\Board\Board;
use Framework\Object\SimpleStorage;

/**
 * 회원등급 평가대상 조회 클래스
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class AppraisalSearch extends \Component\AbstractComponent
{
    /**
     * @var string $appraisalDateTime 평가일시
     */
    protected $appraisalDateTime;
    /** @var int $orderPriceUnit 주문금액 단위 */
    protected $orderPriceUnit = 10000;
    /** @var int $appraisalOffset 평가 대상 조회 시작 */
    protected $appraisalOffset = 0;
    /** @var int $appraisalLimit 평가 대상 조회 범위 */
    protected $appraisalLimit = 10000;

    /** @var SimpleStorage $appraisalGroupConfig 평가정책 */
    protected $appraisalGroupConfig;
    /** @var SimpleStorage $group 등급정책 */
    protected $group;
    /** @var array $result 조회 결과 */
    protected $result = [];
    /** @var array $where 쿼리 조건 */
    protected $where = [];
    /** @var array $column 쿼리 컬럼 */
    protected $column = [];
    /** @var string $query 쿼리 */
    protected $query;
    /** @var string $boardQuery 게시글 관련 쿼리 */
    private $boardQuery;
    /** @var string $orderQuery 주문 관련 쿼리 */
    private $orderQuery;
    /** @var string $loginQuery 로그인 관련 쿼리 */
    private $loginQuery;
    /** @var string $periodQuery 검색기간 */
    private $periodQuery;
    /** @var string $periodDateFormat 검색기간 포맷 */
    private $periodDateFormat = 'Y-m-d';

    /**
     * 조회
     *
     * @param SimpleStorage $group
     *
     * @return array
     */
    public function search(SimpleStorage $group)
    {
        $logger = \App::getInstance('logger');
        $logger->info('Start appraisal member search');
        $this->group = $group;

        unset($this->query, $this->column, $this->where);
        $this->periodQuery();
        $this->boardQuery();
        $this->orderQuery();
        $this->loginQuery();
        $this->buildQuery();
        $logger->info(sprintf('Build query complete. %s', $this->query));
        $results = $this->db->query_fetch($this->query);
        $logger->debug('Appraisal search result keys', array_keys($results[0]));
        $return = [];
        foreach ($results as $index => $result) {
            if (key_exists($result['memNo'], $return)) {
                $default = $return[$result['memNo']];
                $default['orderCountMobile'] += $result['orderCountMobile'];
                $default['orderPriceMobile'] += $result['orderPriceMobile'];
                $result = $default;
            }
            $return[$result['memNo']] = $result;
        }

        return $return;
    }

    /**
     * 로그인 정보 조회 쿼리 반환
     *
     * @return string
     */
    public function loginQuery()
    {
        $query[] = 'SELECT memNo, COUNT(sno) AS cnt';
        $query[] = ', COUNT(IF(loginCntMobile > 0, 1, NULL)) AS mcnt';
        $query[] = 'FROM ' . DB_MEMBER_LOGINLOG;
        if ($this->usePeriod()) {
            $query[] = 'WHERE regDt ' . $this->periodQuery;
        }
        $query[] = 'GROUP BY memNo';
        $this->loginQuery = '(' . implode(' ', $query) . ')';

        return $this->loginQuery;
    }

    /**
     * 게시글 정보 조회 쿼리 반환
     *
     * @return string
     */
    public function boardQuery()
    {
        $query[] = 'SELECT memNo, COUNT(IFNULL(sno, NULL)) AS cnt';
        $query[] = ', COUNT(IF(isMobile=\'y\', sno, NULL)) AS mcnt';
        $query[] = 'FROM ' . DB_BD_ . Board::BASIC_GOODS_REIVEW_ID;
        $query[] = 'WHERE memNo > 0';
        if ($this->usePeriod()) {
            $query[] = 'AND regDt ' . $this->periodQuery;
        }
        $query[] = 'GROUP BY memNo';
        $this->boardQuery = '(' . implode(' ', $query) . ')';

        return $this->boardQuery;
    }

    /**
     * 실적계산 사용여부 반환
     *
     * @return bool
     */
    public function usePeriod()
    {
        return $this->appraisalGroupConfig->get('calcPeriodFl', 'n') == 'y';
    }

    /**
     * 주문 정보 조회쿼리 반환
     *
     * @return string
     */
    public function orderQuery()
    {
        $taxPrefix = 'tax';
        $goods = [
            'og.' . $taxPrefix . 'SupplyGoodsPrice',
            'og.' . $taxPrefix . 'VatGoodsPrice',
            'og.' . $taxPrefix . 'FreeGoodsPrice',
        ];
        $this->_addIfNull($goods);
        $goods = implode('+', $goods);
        $addGoods = [
            'oag.' . $taxPrefix . 'SupplyAddGoodsPrice',
            'oag.' . $taxPrefix . 'VatAddGoodsPrice',
            'oag.' . $taxPrefix . 'FreeAddGoodsPrice',
        ];
        $this->_addIfNull($addGoods);
        $addGoods = implode('+', $addGoods);
        $delivery = [
            'od.' . $taxPrefix . 'SupplyDeliveryCharge',
            'od.' . $taxPrefix . 'VatDeliveryCharge',
            'od.' . $taxPrefix . 'FreeDeliveryCharge',
        ];
        $this->_addIfNull($delivery);
        $delivery = implode('+', $delivery);

        $subQuery = [];
        $subQuery[] = 'SELECT o.memNo, o.orderTypeFl, o.orderNo, og.orderDeliverySno, og.goodsType';
        $subQuery[] = ', SUM((' . $goods . ')+(' . $addGoods . ')) AS price';
        $subQuery[] = ', SUM(IF(o.orderTypeFl=\'mobile\', (' . $goods . ')+(' . $addGoods . '), 0)) AS mprice';
        $subQuery[] = ', COUNT(og.sno) AS cnt';
        $subQuery[] = ', COUNT(IF(o.orderTypeFl=\'mobile\', og.sno, NULL)) AS mcnt';
        $subQuery[] = 'FROM ' . DB_ORDER . ' AS o';
        $subQuery[] = 'LEFT JOIN ' . DB_ORDER_GOODS . ' AS og ON o.orderNo = og.orderNo';
        $subQuery[] = 'LEFT JOIN ' . DB_ORDER_ADD_GOODS . ' AS oag ON o.orderNo = oag.orderNo';
        $subQuery[] = 'WHERE og.orderStatus=\'s1\' AND o.memNo > 0';
        if ($this->usePeriod()) {
            $subQuery[] = 'AND og.finishDt ' . $this->periodQuery;
        }
        $subQuery[] = 'GROUP BY o.orderNo, og.orderDeliverySno';

        $query[] = 'SELECT sub1.memNo, SUM(sub1.price + (IF(sub1.goodsType=\'goods\', ' . $delivery . ', 0))) AS price';
        $query[] = ', SUM(sub1.mprice + (IF(sub1.orderTypeFl=\'mobile\', IF(sub1.goodsType=\'goods\', ' . $delivery . ', 0), 0))) AS mprice';
        $query[] = ', SUM(sub1.cnt) AS cnt, SUM(sub1.mcnt) AS mcnt';
        $query[] = 'FROM (' . implode(' ', $subQuery) . ') AS sub1';
        $query[] = 'LEFT JOIN ' . DB_ORDER_DELIVERY . ' AS od ON sub1.orderDeliverySno = od.sno';
        $query[] = 'GROUP BY sub1.memNo';
        $this->orderQuery = '(' . implode(' ', $query) . ')';

        return $this->orderQuery;
    }

    /**
     * 실적계산기간 쿼리 반환
     *
     * @return string
     */
    public function periodQuery()
    {
        $endPeriod = $this->_getEndPeriod();
        $startPeriod = $this->_getStartPeriod($endPeriod);
        $startDate = date($this->periodDateFormat, $startPeriod);
        $endDate = date($this->periodDateFormat, $endPeriod);
        $this->periodQuery = 'BETWEEN DATE_FORMAT(\'' . $startDate . '\',\'%Y-%m-%d 00:00:00\')';
        $this->periodQuery .= ' AND DATE_FORMAT(\'' . $endDate . '\',\'%Y-%m-%d 23:59:59\')';

        return $this->periodQuery;
    }

    /**
     * 특정 등급 제외한 회원 조회
     *
     * @param               $groupSno
     * @param SimpleStorage $group
     *
     * @return array|object
     */
    public function searchByGroupSno($groupSno, SimpleStorage $group)
    {
        $this->group = $group;

        unset($this->query, $this->column, $this->where);
        $this->periodQuery();
        $this->boardQuery();
        $this->orderQuery();
        $this->loginQuery();
        $this->buildQuery('m.groupSno!=' . $groupSno);
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Build query complete. %s', $this->query));

        return $this->db->query_fetch($this->query);
    }

    /**
     * 기본등급 대상 회원 조회
     *
     * @param string $appraisalDateTime 등급평가시 회원정보에 입력된 변경일시
     *
     * @return array
     */
    public function searchDefaultGroup($appraisalDateTime)
    {
        $this->db->strField = 'm.memNo, m.memNm, m.email, m.cellPhone, m.groupSno, m.adminMemo, m.groupModDt';
        // 다른등급 대상 회원 조회(_pushWhere())와 조건 동일하게 설정
        $this->db->strWhere = 'm.sleepFl=\'n\' AND m.appFl=\'y\'';
        $this->db->strLimit = ($this->appraisalOffset * $this->appraisalLimit) . ', ' . $this->appraisalLimit;
        $this->db->strGroup = 'm.memNo';

        $query = $this->db->query_complete();

        $this->query = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ';
        $this->query .= implode(' ', $query);
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Build query complete. %s', $this->query));

        $results = $this->db->query_fetch($this->query);
        $return = [];
        foreach ($results as $result) {
            // 기본등급 조건 처리
            if ($result['groupSno'] != '1' && $result['groupModDt'] != $appraisalDateTime) {
                $return[$result['memNo']] = $result;
            }
        }
        return $return;
    }

    public function setAppraisalGroupConfig($appraisalGroupConfig)
    {
        $this->appraisalGroupConfig = $appraisalGroupConfig;
    }

    public function hasAppraisalMember(array $members)
    {
        return isset($members) && is_array($members) && count($members) > 0;
    }

    public function getAppraisalOffset()
    {
        return $this->appraisalOffset;
    }

    public function setAppraisalOffset($appraisalOffset)
    {
        $this->appraisalOffset = $appraisalOffset;
    }

    public function getAppraisalLimit()
    {
        return $this->appraisalLimit;
    }

    public function setAppraisalLimit($appraisalLimit)
    {
        $this->appraisalLimit = $appraisalLimit;
    }

    /**
     * @param $appraisalDateTime
     */
    public function setAppraisalDateTime($appraisalDateTime)
    {
        $this->appraisalDateTime = $appraisalDateTime;
    }

    /**
     * 쿼리 조합
     *
     * @param string $where
     */
    protected function buildQuery($where = '')
    {
        $this->_pushColumn();
        $this->_pushWhere();
        if (!empty($where)) {
            $this->where[] = $where;
        }
        $this->db->strField = implode(',', $this->column);
        $this->db->strWhere = implode(' AND ', $this->where);
        $this->db->strJoin = 'LEFT JOIN ' . $this->boardQuery . ' AS b ON m.memNo = b.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . $this->orderQuery . ' AS o ON m.memNo = o.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . $this->loginQuery . ' AS l ON m.memNo = l.memNo';
        $this->db->strLimit = ($this->appraisalOffset * $this->appraisalLimit) . ', ' . $this->appraisalLimit;
        $this->db->strGroup = 'm.memNo';

        $query = $this->db->query_complete();

        $this->query = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ';
        $this->query .= implode(' ', $query);
    }

    /**
     * 실적계산기간 종료일
     *
     * @return int
     */
    private function _getEndPeriod()
    {
        switch ($this->appraisalGroupConfig->get('calcPeriodBegin')) {
            case '-1w':
                $strDate = strtotime('-1 week');
                break;
            case '-2w':
                $strDate = strtotime('-2 week');
                break;
            case '-1m':
                $strDate = strtotime('-1 month');
                break;
            default:
                $strDate = strtotime('-1 day');
                break;
        }

        return $strDate;
    }

    /**
     * 실적계산기간 시작일
     *
     * @param $startDate
     *
     * @return int
     */
    private function _getStartPeriod($startDate)
    {
        $calcPeriodMonth = $this->appraisalGroupConfig->get('calcPeriodMonth');
        $calcPeriodMonth = gd_isset($calcPeriodMonth, 1);
        $this->appraisalGroupConfig->set('calcPeriodMonth', $calcPeriodMonth);
        $time = '-' . $calcPeriodMonth . ' month';

        return strtotime($time, $startDate);
    }

    /**
     * 조회할 컬럼 설정
     *
     */
    private function _pushColumn()
    {
        $this->column[] = 'm.memNo, m.memNm, m.email, m.cellPhone, m.groupSno, m.adminMemo, m.groupModDt';
        $this->column[] = 'IFNULL(l.cnt, 0) AS loginCount';
        $this->column[] = 'IFNULL(b.cnt, 0) AS reviewCount';
        $this->column[] = 'IFNULL(o.cnt, 0) AS orderCount';
        $this->column[] = 'IFNULL(o.price, 0) AS orderPrice';
        $this->column[] = 'IFNULL(l.mcnt, 0) AS loginCountMobile';
        $this->column[] = 'IFNULL(b.mcnt, 0) AS reviewCountMobile';
        $this->column[] = 'IFNULL(o.mcnt, 0) AS orderCountMobile';
        $this->column[] = 'IFNULL(o.mprice, 0) AS orderPriceMobile';
    }

    /**
     * 조건절 설정
     *
     */
    private function _pushWhere()
    {
        $this->where[] = 'm.sleepFl=\'n\' AND m.appFl=\'y\'';
    }

    /**
     * 컬럼에 if 추가
     *
     * @param array $columns
     */
    private function _addIfNull(array &$columns)
    {
        foreach ($columns as &$column) {
            $column = 'IFNULL(' . $column . ', 0)';
        }
    }
}
