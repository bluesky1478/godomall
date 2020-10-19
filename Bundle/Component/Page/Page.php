<?php
/**
 * 페이징 class
 *
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Page;

use Exception;
use Logger;
use Request;

class Page
{
    public $page = [];
    public $recode = [];
    public $block = [];
    public $icon = [];
    public $design = [];
    public $idx = 0;
    public $printHtml = '';
    protected $queryString = '';
    protected $ampersand = '&amp;';
    protected $limit = [];
    protected $useCache = false; //캐시사용 여부

    /**
     * 생성자
     *
     * @param integer $page   현재 페이지
     * @param integer $total  검색 총 레코드수
     * @param integer $amount 총 레코드수
     * @param integer $list   페이지당 리스트 수
     * @param integer $block  페이지 블록 갯수
     * @param string  $url    이동할 페이지 URL
     */
    public function __construct($page = 1, $total = 0, $amount = 0, $list = 10, $block = 10, $url = null)
    {
        $this->setCurrentPage($page);
        $this->setList($list);
        $this->setTotal($total);
        $this->setAmount($amount);
        $this->setBlockCount($block);

        //--- 페이지 세팅
        $this->setPage($url);

        //--- 아이콘 세팅
        $this->setIcon(
            [
                '<img src=' . PATH_ADMIN_GD_SHARE . 'img/icon_arrow_page_ll.png class=img-page-arrow>',
                '<img src=' . PATH_ADMIN_GD_SHARE . 'img/icon_arrow_page_rr.png class=img-page-arrow>',
                '<img src=' . PATH_ADMIN_GD_SHARE . 'img/icon_arrow_page_l.png class=img-page-arrow>',
                '<img src=' . PATH_ADMIN_GD_SHARE . 'img/icon_arrow_page_r.png class=img-page-arrow>',
                '&nbsp;',
                '&nbsp;',
                '&nbsp;',
                '&nbsp;',
            ]
        );
    }

    /**
     * 페이지캐시를 사용하려면 true로 설정
     *
     * @param $useCache
     *
     * @return $this
     */
    public function setCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }

    /**
     * 아이콘 세팅
     *
     * @param bool $iconArr
     */
    public function setIcon($iconArr = false)
    {
        if (is_array($iconArr)) {
            $this->icon['start'] = gd_isset($iconArr[0]);
            $this->icon['end'] = gd_isset($iconArr[1]);
            $this->icon['prev'] = gd_isset($iconArr[2]);
            $this->icon['next'] = gd_isset($iconArr[3]);
            $this->icon['left'] = gd_isset($iconArr[4]);
            $this->icon['right'] = gd_isset($iconArr[5]);
            $this->icon['nowLeft'] = gd_isset($iconArr[6]);
            $this->icon['nowRight'] = gd_isset($iconArr[7]);
        }
    }

    /**
     * Query string 세팅
     *
     * @param bool|string $getString 페이지의 queryString 값
     */
    public function setUrl($getString = false)
    {
        if ($getString) {
            if (is_array($getString)) {
                $getStringNew = [];
                foreach ($getString as $k => $v) {
                    if (substr($v, 0, 8) != 'pagelink') {
                        $getStringNew[] = $v;
                    }
                }
                $this->queryString = $this->ampersand . implode($this->ampersand, $getStringNew);
            } else {
                $getString = preg_replace('/(&amp;|&)/', $this->ampersand, $getString);
                $tmpArrString = explode($this->ampersand, $getString);
                $getStringNew = [];
                foreach ($tmpArrString as $k => $v) {
                    if (substr($v, 0, 8) != 'pagelink') {
                        $getStringNew[] = $v;
                    }
                }

                //				$this->queryString	= $this->ampersand.$getString;
                //$this->queryString = $this->ampersand . preg_replace('/(&amp;|&)/', $this->ampersand, $getStringNew);
                $this->queryString = $this->ampersand . implode($this->ampersand, $getStringNew);
            }
            //			$this->queryString		= preg_replace('/'.$this->ampersand.'page=[0-9]+/','',$this->queryString);
            $this->queryString = preg_replace('/' . $this->ampersand . 'page=[^&]*/', '', $this->queryString);
        }
    }

    /**
     * 페이지 출력
     *
     * @param null $script null:href link, #:data-page, script:function
     *
     * @return string $script 페이지 출력을 위한 HTML
     */
    public function getPage($script = null)
    {
        unset($this->printHtml);
        if ($script === null) {
            return $this->getPageWithLink();
        } else if ($script == '#') {
            return $this->getPageWithPageData();
        } else {
            return $this->getPageWithJavascript($script);
        }
    }

    /**
     * 페이지 세팅
     *
     * @param string     $url 이동할 페이지 URL
     * @param array $setRecodeCache   캐시로 저장할 키 값
     *
     */
    public function setPage($url = null,array $setRecodeCache  = [])
    {
        $this->setRecodeCache($setRecodeCache);
        //--- 페이지 계산
        $this->page['url'] = ($url) ? $url : basename(Request::getPhpSelf());
        // $this->page['url'] = ($url) ? $url : Request::server()->get('QUERY_STRING');
        try {
            $this->page['total'] = ($this->page['list'] ? ceil($this->recode['total'] / $this->page['list']) : 0);
        } catch (Exception $e) {
            Logger::error(__METHOD__ . ', Division by zero=>total/list', $this->page);
            $this->page['total'] = 0;
        }
        $this->block['next'] = false;
        $this->block['prev'] = false;
        $this->block['total'] = ceil($this->page['total'] / $this->block['cnt']);
        $this->block['now'] = ceil($this->page['now'] / $this->block['cnt']);
        $this->recode['start'] = ($this->page['now'] - 1) * $this->page['list'];
        $this->recode['limit'] = $this->page['list'];

        $this->page['start'] = ($this->block['now'] - 1) * $this->block['cnt'] + 1;
        $this->page['end'] = $this->page['start'] + $this->block['cnt'] - 1;
        $this->page['prevNo'] = $this->page['start'] - $this->block['cnt'];
        $this->page['nextNo'] = $this->page['end'] + 1;

        //--- 페이지 체크
        if (($this->recode['start'] + $this->recode['limit']) > $this->recode['total']) {
            $this->recode['limit'] = $this->recode['total'] - $this->recode['start'];
        }
        if ($this->page['end'] > $this->page['total']) {
            $this->page['end'] = $this->page['total'];
        }
        if ($this->block['now'] < $this->block['total']) {
            $this->block['next'] = true;
        }
        if ($this->block['now'] > 1) {
            $this->block['prev'] = true;
        }
        if ($this->block['prev'] === false) {
            $this->page['prevNo'] = 0;
        }
        if ($this->block['next'] === false) {
            $this->page['nextNo'] = 0;
        }

        $this->limit[] = $this->recode['start'];
        $this->limit[] = $this->recode['limit'];

        //--- 직접 페이지 페이지 작업을 하기위한 세팅
        $this->design['prevPage'] = $this->page['prevNo'];
        $this->design['nowPage'] = $this->page['now'];
        $this->design['startPage'] = $this->page['start'];
        $this->design['endPage'] = $this->page['end'];
        $this->design['nextPage'] = $this->page['nextNo'];
        $this->design['lastPage'] = $this->page['total'];

        //--- 리스팅 인덱스
        $this->idx = $this->recode['total'] - ($this->page['list'] * ($this->page['now'] - 1));
    }

    /**
     * 페이지 출력 및 링크 액션 설정
     * @return string $script 페이지 출력을 위한 HTML
     */
    public function getPageWithLink()
    {
        //신규스킨마크업변경에 따른 수정
        if(gd_is_skin_division()) {
            $wrapaStartMarkup = '<div class="pagination"><ul>';
            $firstCss = 'btn_page btn_page_first';
            $prevCss='btn_page btn_page_prev';
            $nextCss='btn_page btn_page_next';
            $lastCss='btn_page btn_page_last';
            $activeClass='on';
            $wrapaEndMarkup = '</ul></div>';
        } else {
            $wrapaStartMarkup = '<nav><ul class="pagination pagination-sm">';
            $firstCss = 'front-page front-page-first';
            $prevCss='front-page front-page-prev';
            $nextCss='front-page front-page-next';
            $lastCss='front-page front-page-last';
            $activeClass='active';
            $wrapaEndMarkup = '</ul></nav>';
        }

        $tmp = $this->page['now'] - 1;
        if ($tmp < 1) {
            $tmp = 1;
        }
        $this->page['prevPage'] = '?page=' . $tmp . $this->queryString;

        $tmp = $this->page['now'] + 1;
        if ($this->page['total'] < $tmp) {
            $tmp = $this->page['total'];
        }
        $this->page['nextPage'] = '?page=' . $tmp . $this->queryString;

        $this->printHtml .= $wrapaStartMarkup;

        if ($this->block['prev'] === true) {
            $tmp1 = 'page=1' . $this->queryString;
            $tmp2 = 'page=' . $this->page['prevNo'] . $this->queryString;
            $this->printHtml .= '<li class="'.$firstCss.'"><a aria-label="First" href="./' . $this->page['url'] . '?' . $tmp1 . '">'.$this->icon['start'].'맨앞</a></li>';
            $this->printHtml .= '<li class="'.$prevCss.'"><a aria-label="Previous" href="./' . $this->page['url'] . '?' . $tmp2 . '">'.$this->icon['prev'].'이전</a></li>';
        }

        for ($i = $this->page['start']; $i <= $this->page['end']; $i++) {
            if ($i == $this->page['now']) {
                $this->printHtml .= '<li class="'.$activeClass.'"><span>' . $i . '</span></li>';
            } else {
                $tmp = 'page=' . $i . $this->queryString;
                $this->printHtml .= '<li><a href="./' . $this->page['url'] . '?' . $tmp . '">' . $i . '</a></li>';
            }
        }

        if ($this->block['next'] === true) {
            $tmp1 = 'page=' . $this->page['nextNo'] . $this->queryString;
            $tmp2 = 'page=' . $this->page['total'] . $this->queryString;
            $this->printHtml .= '<li class="'.$nextCss.'"><a aria-label="Next" href="./' . $this->page['url'] . '?' . $tmp1 . '">' . $this->icon['next'] . '다음</a></li>';
            $this->printHtml .= '<li class="'.$lastCss.'"><a aria-label="Last" href="./' . $this->page['url'] . '?' . $tmp2 . '">' . $this->icon['end'] . '맨뒤</a></li>';
        }

        $this->printHtml .= $wrapaEndMarkup;

        return $this->printHtml;
    }

    /**
     * 페이지 출력 및 페이지 번호 data 속성 설정
     * @return string $script 페이지 출력을 위한 HTML
     */
    public function getPageWithPageData()
    {
        //신규스킨마크업변경에 따른 수정
        if(gd_is_skin_division()) {
            $wrapaStartMarkup = '<div class="pagination"><ul>';
            $firstCss = 'btn_page btn_page_first';
            $prevCss='btn_page btn_page_prev';
            $nextCss='btn_page btn_page_next';
            $lastCss='btn_page btn_page_last';
            $activeClass='on';
            $wrapaEndMarkup = '</ul></div>';
        } else {
            $wrapaStartMarkup = '<nav><ul class="pagination pagination-sm">';
            $firstCss = 'front-page front-page-first';
            $prevCss='front-page front-page-prev';
            $nextCss='front-page front-page-next';
            $lastCss='front-page front-page-last';
            $activeClass='active';
            $wrapaEndMarkup = '</ul></nav>';
        }

        $tmp = $this->page['now'] - 1;
        if ($tmp < 1) {
            $tmp = 1;
        }
        $this->page['prevPage'] = '?page=' . $tmp . $this->queryString;

        $tmp = $this->page['now'] + 1;
        if ($this->page['total'] < $tmp) {
            $tmp = $this->page['total'];
        }
        $this->page['nextPage'] = '?page=' . $tmp . $this->queryString;

        $this->printHtml .= $wrapaStartMarkup;

        if ($this->block['prev'] === true) {
            $this->printHtml .= '<li class="'.$firstCss.'"><a aria-label="First" data-page="1" href="#" >' . $this->icon['start'] . '맨앞</a></li>';
            $this->printHtml .= '<li class="'.$prevCss.'"><a aria-label="Previous" data-page="' . $this->page['prevNo'] . '" href="#" >' . $this->icon['prev'] . '이전</a></li>';
        }

        for ($i = $this->page['start']; $i <= $this->page['end']; $i++) {
            if ($i == $this->page['now']) {
                $this->printHtml .= '<li class="'.$activeClass.'"><span>' . $i . '</span></li>';
            } else {
                $this->printHtml .= '<li><a data-page="' . $i . '" href="#" >' . $i . '</a></li>';
            }
        }

        if ($this->block['next'] === true) {
            $this->printHtml .= '<li class="'.$nextCss.'"><a aria-label="Next" href="#" data-page="' . $this->page['nextNo'] . '">' . $this->icon['next'] . '다음</a></li>';
            $this->printHtml .= '<li class="'.$lastCss.'"><a aria-label="Last" href="#" data-page="' . $this->page['total'] . '">' . $this->icon['end'] . '맨뒤</a></li>';
        }

        $this->printHtml .= $wrapaEndMarkup;

        return $this->printHtml;
    }

    /**
     * 페이지 출력 및 자바스크립트 액션 설정
     *
     * @param $script
     *
     * @return string $script 페이지 출력을 위한 HTML
     */
    public function getPageWithJavascript($script)
    {
        //신규스킨마크업변경에 따른 수정
        if(gd_is_skin_division()) {
            $wrapaStartMarkup = '<div class="pagination"><ul>';
            $firstCss = 'btn_page btn_page_first';
            $prevCss='btn_page btn_page_prev';
            $nextCss='btn_page btn_page_next';
            $lastCss='btn_page btn_page_last';
            $activeClass='on';
            $wrapaEndMarkup = '</ul></div>';
        } else {
            $wrapaStartMarkup = '<nav><ul class="pagination pagination-sm">';
            $firstCss = 'front-page front-page-first';
            $prevCss='front-page front-page-prev';
            $nextCss='front-page front-page-next';
            $lastCss='front-page front-page-last';
            $activeClass='active';
            $wrapaEndMarkup = '</ul></nav>';
        }

        $tmp = $this->page['now'] - 1;
        if ($tmp < 1) {
            $tmp = 1;
        }
        $this->page['prevPage'] = '?page=' . $tmp . $this->queryString;

        $tmp = $this->page['now'] + 1;
        if ($this->page['total'] < $tmp) {
            $tmp = $this->page['total'];
        }
        $this->page['nextPage'] = '?page=' . $tmp . $this->queryString;

        $this->printHtml .= $wrapaStartMarkup;

        if ($this->block['prev'] === true) {
            $tmp1 = 'page=1' . $this->queryString;
            $tmp2 = 'page=' . $this->page['prevNo'] . $this->queryString;
            $this->printHtml .= '<li class="'.$firstCss.'"><a href="javascript:' . str_replace('PAGELINK', $tmp1, $script) . '">' . $this->icon['start'] . '맨앞</a></li>';
            $this->printHtml .= '<li class="'.$prevCss.'"><a href="javascript:' . str_replace('PAGELINK', $tmp2, $script) . '">' . $this->icon['prev'] . '이전</a></li>';
        }

        for ($i = $this->page['start']; $i <= $this->page['end']; $i++) {
            if ($i == $this->page['now']) {
                $this->printHtml .= '<li class="'.$activeClass.'"><span>' . $i . '</span></li>';
            } else {
                $tmp = 'page=' . $i . $this->queryString;
                $this->printHtml .= '<li><a href="javascript:' . str_replace('PAGELINK', $tmp, $script) . '">' . $i . '</a></li>';
            }
        }

        if ($this->block['next'] === true) {
            $tmp1 = 'page=' . $this->page['nextNo'] . $this->queryString;
            $tmp2 = 'page=' . $this->page['total'] . $this->queryString;
            $this->printHtml .= '<li class="'.$nextCss.'"><a href="javascript:' . str_replace('PAGELINK', $tmp1, $script) . '">' . $this->icon['next'] . '다음</a></li>';
            $this->printHtml .= '<li class="'.$lastCss.'"><a href="javascript:' . str_replace('PAGELINK', $tmp2, $script) . '">' . $this->icon['end'] . '맨뒤</a></li>';
        }

        $this->printHtml .= $wrapaEndMarkup;

        return $this->printHtml;
    }


    /**
     * 페이지 출력 복사본
     *
     * @param null $script
     *
     * @return string $script 페이지 출력을 위한 HTML
     */
    public function copyGetPage($script = null)
    {
        $tmp = $this->page['now'] - 1;
        if ($tmp < 1) {
            $tmp = 1;
        }
        $this->page['prevPage'] = '?page=' . $tmp . $this->queryString;

        $tmp = $this->page['now'] + 1;
        if ($this->page['total'] < $tmp) {
            $tmp = $this->page['total'];
        }
        $this->page['nextPage'] = '?page=' . $tmp . $this->queryString;

        if ($this->block['prev'] === true) {
            $tmp1 = 'page=1' . $this->queryString;
            $tmp2 = 'page=' . $this->page['prevNo'] . $this->queryString;
            if ($script == null) {
                $this->printHtml .= ' <a href="./' . $this->page['url'] . '?' . $tmp1 . '"><span class="page_navi_start"><span>' . $this->icon['start'] . '</span></span></a> ';
                $this->printHtml .= ' <a href="./' . $this->page['url'] . '?' . $tmp2 . '"><span class="page_navi_prev"><span>' . $this->icon['prev'] . '</span></span></a> ';
            } else {
                $this->printHtml .= ' <span onclick="' . str_replace('PAGELINK', $tmp1, $script) . '" class="page_navi_start hand"><span>' . $this->icon['start'] . '</span></span> ';
                $this->printHtml .= ' <span onclick="' . str_replace('PAGELINK', $tmp2, $script) . '" class="page_navi_prev hand"><span>' . $this->icon['prev'] . '</span></span> ';
            }
        }

        for ($i = $this->page['start']; $i <= $this->page['end']; $i++) {
            if ($i == $this->page['now']) {
                $this->printHtml .= ' <span class="page_navi_now">' . $this->icon['nowLeft'] . $i . $this->icon['nowRight'] . '</span> ';
            } else {
                $tmp = 'page=' . $i . $this->queryString;
                if ($script == null) {
                    $this->printHtml .= ' <a href="./' . $this->page['url'] . '?' . $tmp . '"><span class="page_navi">' . $this->icon['left'] . $i . $this->icon['right'] . '</span></a> ';
                } else {
                    $this->printHtml .= ' <span onclick="' . str_replace('PAGELINK', $tmp, $script) . '" class="page_navi hand">' . $this->icon['left'] . $i . $this->icon['right'] . '</span>';
                }
            }
        }

        if ($this->block['next'] === true) {
            $tmp1 = 'page=' . $this->page['nextNo'] . $this->queryString;
            $tmp2 = 'page=' . $this->page['total'] . $this->queryString;
            if ($script == null) {
                $this->printHtml .= ' <a href="./' . $this->page['url'] . '?' . $tmp1 . '"><span class="page_navi_next"><span>' . $this->icon['next'] . '</span></span></a> ';
                $this->printHtml .= ' <a href="./' . $this->page['url'] . '?' . $tmp2 . '"><span class="page_navi_end"><span>' . $this->icon['end'] . '</span></span></a> ';
            } else {
                $this->printHtml .= ' <span onclick="' . str_replace('PAGELINK', $tmp1, $script) . '" class="page_navi_next hand"><span>' . $this->icon['next'] . '</span></span> ';
                $this->printHtml .= ' <span onclick="' . str_replace('PAGELINK', $tmp2, $script) . '" class="page_navi_end hand"><span>' . $this->icon['end'] . '</span></span> ';
            }
        }

        return $this->printHtml;
    }

    /**
     * 현재 페이지 설정하기
     *
     * @param $pageNo
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setCurrentPage($pageNo)
    {
        if ($pageNo < 1) {
            $pageNo = 1;
        }
        $this->page['now'] = $pageNo;
    }

    /**
     * 현재 페이지 가져오기
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getCurrentPage()
    {
        $this->page['now'];
    }

    /**
     * 한 페이지당 보여줄 갯수 설정하기
     *
     * @param $list
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setList($list)
    {
        $this->page['list'] = $list;
    }

    /**
     * getList
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getList()
    {
        return $this->page['list'];
    }

    /**
     * 쿼리의 조건에 부합하는 갯수 설정하기
     *
     * @param $total
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setTotal($total)
    {
        $this->recode['total'] = $total;
    }

    /**
     * 쿼리의 조건에 부합하는 갯수 가져오기
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getTotal()
    {
        return $this->recode['total'];
    }

    /**
     * 쿼리 조건여부와 상관없이 전체 갯수 설정하기
     *
     * @param $amount
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setAmount($amount)
    {
        $this->recode['amount'] = $amount;
    }

    /**
     * 쿼리 조건여부와 상관없이 전체 갯수 가져오기
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getAmount()
    {
        return $this->recode['amount'];
    }

    /**
     * 보여줄 페이지 버튼의 갯수 설정하기
     *
     * @param $count
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setBlockCount($count)
    {
        $this->block['cnt'] = $count;
    }

    /**
     * 보여줄 페이지 버튼의 갯수 가져오기
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getBlockCount()
    {
        return $this->block['cnt'];
    }

    protected function overrideTest()
    {
        return __METHOD__;
    }

    /**
     * recode 에 있는 데이터 카운트(total,amount...)를 페이징 queryString에 추가시켜 캐시저장소로 이용한다.
     * 캐시안탔을때의 정보를 페이징 쿼리스트링에 추가시킨다.
     * @param array $setRecodeCache   캐시에 저장할 데이터key (amount 와 total은 디폴트로 추가된상태)
     */
    protected function setRecodeCache(array $setRecodeCache = [])
    {
        if($this->useCache === false){
            return;
        }
        array_push($setRecodeCache,'total');
        array_push($setRecodeCache,'amount');
        $addUrl = '';
        $queryString = $this->queryString ? $this->queryString : \Request::getQueryString();
        foreach ($setRecodeCache as $val) {
            $queryKey = '__'.$val;
            if($this->hasRecodeCache($val) === true) {
                $queryValue = Request::get()->get($queryKey) ? Request::get()->get($queryKey) : $this->recode[$val];
            }
            else {
                $queryValue = $this->recode[$val];
            }

            $this->recode[$val] = $queryValue;    //레코드 세팅
            if(strpos($this->queryString,$queryKey.'='.$queryValue) === false){
                $addUrl.= '&'.$queryKey.'='.$queryValue;
            }
        }
        $this->setUrl($queryString . $addUrl);
    }

    public function hasRecodeCache($key)
    {
        if($this->useCache === false) {
            return false;
        }

        if(!$this->page['total']) {
            $this->page['total'] = $this->page['list'] ? ceil($this->getRecodeCache('total') / $this->page['list']) : 0;
        }
        if(((\Request::get()->get('searchFl') == 'y' || \Request::get()->get('indicate') == 'search') && \Request::get()->get('page') > 1) === false || $this->page['total'] == $this->page['now']){
            return false;
        }

        return \Request::get()->has('__'.$key);
    }

    public function getRecodeCache($key)
    {
        return \Request::get()->get('__'.$key);
    }
}
