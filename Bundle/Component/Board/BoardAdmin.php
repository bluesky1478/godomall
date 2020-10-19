<?php
/**
 * 게시판관리 Class
 *
 * @author sunny, sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Board;

use App;
use Component\Goods\Goods;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Manager;
use Component\Storage\Storage;
use Cache;
use Component\Database\DBTableField;
use Component\File\DataFileFactory;
use Component\Validator\Validator;
use Framework\Cache\CacheableProxyFactory;
use Framework\Utility\ArrayUtils;
use Request;


class BoardAdmin
{
    const CACHE_EXPIRE = 60;
    const CACHE_USE = false;

    const ECT_INVALID_ARG = 'BoardAdmin.ECT_INVALID_ARG';
    const ECT_OVERLAP_BDID = 'BoardAdmin.ECT_OVERLAP_BDID';
    const ECT_OVERLAP_TYPE = 'BoardAdmin.ECT_OVERLAP_TYPE';
    const ECT_EXCEED_MAXSIZE = 'BoardAdmin.ECT_EXCEED_MAXSIZE';

    const TEXT_OVERLAP_TYPE = '같은 용도의 게시판이 존재합니다';
    const TEXT_EXCEED_MAXSIZE = '최대 업로드 사이즈는 %s입니다';

    private $db;
    private $fieldTypes; // board field type
    public $page;
    public $mobileConfig;

    const DEFAULT_ATTACH_IMAGE_MAX_SIZE = 700;  //첨부파일 이미지 디폴트 최대 사이즈

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = App::load('DB');
        }
        $this->mobileConfig = gd_policy('mobile.config'); // 모바일 샵 사용 여부 가져오기 위함.
        $this->fieldTypes = DBTableField::getFieldTypes('tableBoard');
    }

    public static function getPageUrl($bdId, $isMobile = false)
    {
        $rootUrl = $isMobile ? URI_MOBILE : URI_HOME;
        return $rootUrl . 'board' . DS . 'list.php?bdId=' . $bdId;
    }

    public function getMoveBoardList($cfg)
    {
        $query = "SELECT * FROM " . DB_BOARD . " WHERE bdKind = '" . $cfg['bdKind'] . "'";
        $data = $this->db->query_fetch($query, null);
        return $data;
    }

    public function getArticleCount($bdId, $someTimePast = null, array $replyStatus = null)
    {
        $addWhere[] = "isDelete='n'";
        if ($someTimePast != null || $someTimePast === 0) {
            $addWhere[] = " b.regDt > '" . date('Y-m-d', strtotime("-" . $someTimePast . " days")) . "'";
        }
        if (Manager::isProvider()) {
            $funcSelectBdGoodsFl = function () use ($bdId) {
                $db = \App::getInstance('DB');
                $db->bind_param_push($bindParam, 's', $bdId);
                $resultSet = $db->query_fetch('SELECT bdGoodsFl FROM ' . DB_BOARD . ' WHERE bdId=?', $bindParam, false);
                return $resultSet['bdGoodsFl'];
            };
            if ($funcSelectBdGoodsFl() == 'y') {
                $search['scmNo'] = \Session::get('manager.scmNo');
            }
        }

        if ($replyStatus) {
            $queryReplyStatus = implode(',', $replyStatus);
            $addWhere[] = " b.replyStatus in (" . $queryReplyStatus . ")";
        }

        return BoardBuildQuery::init($bdId)->selectCount($search, $addWhere);
    }

    public function getNewArticleCount($bdId, $newFl)
    {
        $newCntQuery = "SELECT count(sno)  FROM " . DB_BD_ . $bdId . " WHERE isDelete='n' AND regdt >= DATE_ADD(NOW(), INTERVAL -" . $newFl . " HOUR) ;";
        return $this->db->fetch($newCntQuery, 'row')[0];
    }

    // 특정게시판의 특정시간대의 게시물 카운트
    public function getArticleCountByBetween($bdId, $sDate, $eDate)
    {
        $newCntQuery = "SELECT count(sno)  FROM " . DB_BD_ . $bdId . " WHERE isDelete='n' AND regdt BETWEEN '" . $sDate . "' AND '" . $eDate . "';";
        return $this->db->fetch($newCntQuery, 'row')[0];
    }

    /**
     * getBoardList
     *
     * @param null $req
     * @param bool $isPaging
     * @param string $sort
     * @param bool $hasStatics
     * @param int $listCount null or 0 이면 전체노출
     * @return null
     */
    public function getBoardList($req = null, $isPaging = true, $sort = 'desc', $hasStatics = true, $listCount = 20)
    {
        if (!$sort) {
            $sort = 'desc';
        }

        $getData = $arrBind = $search = $arrWhere = $checked = $selected = null;
        gd_isset($req['page'], 1);
        $offset = ($req['page'] - 1) * $listCount;
        $data = $this->selectList($req, $offset, $listCount, $sort);
        $searchCnt = $this->selectCount($req);
        if ($isPaging) {
            $totalCnt = $this->selectCount();
            gd_isset($req['page'], 1);
            $this->page = App::load('\\Component\\Page\\Page', $req['page'], $searchCnt, $totalCnt, $listCount);
            $this->page->setPage();
            $getData['pagination'] = $this->page->getPage();
            $getData['cnt']['total'] = $totalCnt;
            $getData['cnt']['search'] = $searchCnt;
        }

        $listNo = $searchCnt - $offset;

        if ($data) {
            foreach ($data as &$board) {
                $board['listNo'] = $listNo;

                if (\Globals::get('gGlobal.isUse')) {
                    $boardTheme = new BoardTheme();
                    foreach (\Globals::get('gGlobal.useMallList') as $val) {
                        $domainPostfix = $val['domainFl'] == 'kr' ? '' : ucfirst($val['domainFl']);

//                        dump($domainPostfix.$board['theme'.$domainPostfix.'Sno']);
                        //    $board['bdSkinList'][$val['domainFl']] = $boardTheme->getData($board['theme'.$domainPostfix.'Sno']);    //TODO:11111
                        //    $board['bdMobileSkinList'][$val['domainFl']] = $boardTheme->getData($board['mobileTheme'.$domainPostfix.'Sno']);    //TODO:11111
                    }

                }
                $listNo--;
            }
        }

        if ($hasStatics) {
            /*
            if (self::CACHE_USE) {
                $cacheBoard = CacheableProxyFactory::create($this, self::CACHE_EXPIRE, Request::server()->get('REQUEST_URI'), 'admin/board/board_list');
            } else {
                Cache::delete('admin/board/board_list');
                $cacheBoard = $this;
            }*/
            $cacheBoard = $this;

            if (ArrayUtils::isEmpty($data) === false) {
                foreach ($data as &$val) {
                    $val['bdNewListCnt'] = $cacheBoard->getNewArticleCount($val['bdId'], $val['bdNewFl']);
                    $val['bdListCnt'] = $cacheBoard->getArticleCount($val['bdId']);
                    $val['pageUrl'] = $this->getPageUrl($val['bdId'], false);
                    $val['pageMobileUrl'] = $this->getPageUrl($val['bdId'], true);

                    $val['bdQuestionCnt'] = '-';
                    if ($val['bdKind'] == Board::KIND_QA) {
                        $questionCntQuery = "SELECT count(sno)  FROM " . DB_BD_ . $val['bdId'] . " WHERE replyStatus in ('1','2')";
                        $val['bdQuestionCnt'] = $this->db->fetch($questionCntQuery, 'row')[0];
                    }
                    $_boardKindList = Board::KIND_LIST;
                    $val['bdKindStr'] = $_boardKindList[$val['bdKind']];
                }
            }
        }

        $checked['boardKind']['all'] = $req['boardKind']['all'] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_DEFAULT] = $req['boardKind'][Board::KIND_DEFAULT] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_GALLERY] = $req['boardKind'][Board::KIND_GALLERY] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_QA] = $req['boardKind'][Board::KIND_QA] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_EVENT] = $req['boardKind'][Board::KIND_EVENT] == 'y' ? 'checked' : '';
        $search['key'] = $req['key'];
        $search['keyword'] = $req['keyword'];

        //--- 각 데이터 배열화
        $getData['data'] = $data;
        $getData['search'] = $search;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    private function getQueryWhere($req)
    {
        // 키워드 검색
        $search['key'] = gd_isset($req['key']);
        $search['keyword'] = gd_isset($req['keyword']);
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] == 'all') {
                $arrWhere[] = "concat(b.bdId,b.bdNm) LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            } else {
                $arrWhere[] = "b." . $search['key'] . " LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, $this->fieldTypes[$search['key']], $search['keyword']);
            }
        }

        if ($req['bdBasicFl']) {
            $arrWhere[] = "b.bdBasicFl = ?";
            $this->db->bind_param_push($arrBind, 's', $req['bdBasicFl']);
        }

        if ($req['boardKind']) {
            if ($req['boardKind']['all'] != 'y') {
                foreach ($req['boardKind'] as $key => $val) {
                    if ($val == 'y') {
                        $_bdKind[] = '?';
                        $this->db->bind_param_push($arrBind, 's', $key);
                    }
                }
                $arrWhere[] = "b.bdKind in (" . implode(",", $_bdKind) . ")";
            }
        }

        // 관리자앱에서 필요해서 PC모바일 사용여부 where조건 추가
        if ($req['bdUseFl']) {
            $arrWhere[] = "(b.bdUsePcFl = ? OR b.bdUseMobileFl = ?)";
            $this->db->bind_param_push($arrBind, 's', $req['bdUseFl']);
            $this->db->bind_param_push($arrBind, 's', $req['bdUseFl']);
        }

        return [$arrWhere, $arrBind];
    }

    public function selectList($req = null, $offset = 0, $limit = null, $sort = 'desc')
    {
        if ($req) {
            list($arrWhere, $arrBind) = $this->getQueryWhere($req);
        }
//        $this->db->strField = " b.*,bt.sno AS themeSno ,bt.themeNm , btt.sno AS mobileThemeSno, btt.themeNm as mobileThemeNm";
        $this->db->strField = " b.* ";
        if (is_array($arrWhere) === true) {
            $this->db->strWhere = implode(" AND ", $arrWhere);
        }
        if ($limit) {
            $this->db->strLimit = "{$offset},{$limit}";
        }
        if ($sort == 'desc') {
            $this->db->strOrder = "b.sno desc";
        } else {
            $this->db->strOrder = "b.sno asc";
        }
        $query = $this->db->query_complete();

        /* $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD . ' AS b LEFT OUTER JOIN ' . DB_BOARD_THEME . ' AS bt ON b.themeSno=bt.sno  AND bt.bdMobileFl=\'n\' AND liveSkin=\'' . \Globals::get('gSkin.frontSkinLive') . '\' LEFT OUTER JOIN  ' . DB_BOARD_THEME . ' AS btt ON  b.mobileThemeSno = btt.sno AND btt.bdMobileFl=\'y\'  AND btt.liveSkin=\'' . \Globals::get('gSkin.mobileSkinLive') . '\'  ' . implode(' ', $query);
         $data = $this->db->query_fetch($strSQL, $arrBind);*/

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD . ' as b ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        $boardTheme = new BoardTheme();
        if (\Globals::get('gGlobal.isUse')) {
            foreach($data as &$board){
                foreach (\Globals::get('gGlobal.useMallList') as $val) {
                    $domainPostfix = $val['domainFl'] == 'kr' ? '' :  ucfirst($val['domainFl']);
                    $frontThemeInfo = $boardTheme->getData($board['theme'.$domainPostfix.'Sno']);
                    $mobileThemeInfo = $boardTheme->getData($board['mobileTheme'.$domainPostfix.'Sno']);
                    $board['theme'.$domainPostfix.'Nm'] = $frontThemeInfo[0]['themeNm'];
                    $board['mobileTheme'.$domainPostfix.'Nm'] = $mobileThemeInfo[0]['themeNm'];
                }
            }
        } else {
            foreach($data as &$board) {
                $frontThemeInfo = $boardTheme->getData($board['themeSno']);
                $mobileThemeInfo = $boardTheme->getData($board['mobileThemeSno']);
                $board['themeNm'] = $frontThemeInfo[0]['themeNm'];
                $board['mobileThemeNm'] = $mobileThemeInfo[0]['themeNm'];
            }
        }

        return $data;
    }

    public function selectCount($req = null)
    {
        if ($req) {
            list($arrWhere, $arrBind) = $this->getQueryWhere($req);
        }
        $this->db->strField = " count(b.sno) as cnt";
        if (is_array($arrWhere) === true) $this->db->strWhere = implode(" AND ", $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD . ' as b' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        return $data['cnt'];
    }

    private function getDefaultConfig()
    {
        $getData = null;

        $checked = 'checked="checked"';
        $selected = 'selected';

        //--- 각 데이터 배열화
        $getData['data']['bdNewFl'] = '24';
        $getData['data']['bdHotFl'] = '100';
        $defaultUploadMaxFileSize = ini_get('upload_max_filesize');
        if ($defaultUploadMaxFileSize > Board::UPLOAD_DEFAULT_MAX_SIZE) {
            $defaultUploadMaxFileSize = Board::UPLOAD_DEFAULT_MAX_SIZE;
        }
        $getData['data']['bdUploadMaxSize'] = str_replace('M', '', $defaultUploadMaxFileSize);
        $getData['data']['bdHitPerCnt'] = 1;
        $getData['data']['bdStartNum'] = 1;

        // radiobutton
        $getData['checked']['bdKind']['default'] = $checked; //pc쇼핑몰 사용여부
        $getData['checked']['bdUsePcFl']['y'] = $checked; //pc쇼핑몰 사용여부
        $getData['checked']['bdUseMobileFl']['y'] = $checked; //모바일쇼핑몰 사용여부
        $getData['checked']['bdUserDsp']['nick'] = $checked; //작성자 표시방법
        $getData['checked']['bdAdminDsp']['nick'] = $checked; //관리자 표시방법
        $getData['checked']['bdListInView']['y'] = $checked; //게시글 내용화면 리스트 화면노출
        $getData['checked']['bdSecretFl'][0] = $checked; //비밀글 설정
        $getData['checked']['bdSecretTitleFl'][0] = $checked; //비밀글 제목설정
        $getData['checked']['bdSecretReplyFl'][0] = $checked; //비밀댓글 설정
        $getData['checked']['bdSecretReplyTitleFl'][0] = $checked; //비밀댓글 제목설정
        $getData['checked']['bdLinkFl']['n'] = $checked; //링크사용여부
        $getData['checked']['bdMemoFl']['y'] = $checked; //코멘트사용여부
        $getData['checked']['bdAuthMemo']['member'] = $checked; //코맨트 권한
        $getData['checked']['bdEmailFl']['n'] = $checked; //이메일 작성
        $getData['checked']['bdMobileFl']['n'] = $checked; //휴대폰 작성
        $getData['checked']['bdGoodsPtFl']['n'] = $checked; //별점 설정
        $getData['checked']['bdRecommendFl']['n'] = $checked; //추천  설정
        $getData['checked']['bdSnsFl']['n'] = $checked;  //sns사용
        $getData['checked']['bdGoodsFl']['n'] = $checked; //상품연동
        $getData['checked']['bdSubSubjectFl']['n'] = $checked; //부가제목
        $getData['checked']['bdSupplyDsp']['nick'] = $checked; //공급사 표시방법
        $getData['checked']['bdUploadFl']['y'] = $checked; //파일업로드 여부
        $getData['checked']['bdEditorFl']['y'] = $checked; //에디터 사용여부
        $getData['checked']['bdListImageFl']['n'] = $checked; //리스트이미지 노출여부
        $getData['checked']['bdReviewAuthWrite']['all'] = $checked; //쓰기권한 추가기능
        $getData['checked']['bdReviewDuplicateLimit']['free'] = $checked; //쓰기권한 추가기능
        $getData['selected']['bdReviewOrderStatus']['s1'] = $selected; //작성가능 시점
        $getData['selected']['bdReviewPeriod']['15'] = $selected; //작성가능 기간
        $getData['checked']['bdPcHitFl']['y'] = $checked; // PC 쇼핑몰 조회수 표시 여부

        $mobileConfig = $this->mobileConfig;

        if ($mobileConfig['mobileShopFl'] == 'y') {
            $getData['checked']['bdMobileHitFl']['y'] = $checked; // Mobile 쇼핑몰 조회수 표시 여부
            $getData['disabled']['bdMobileShopFl']['y'] = ' '; // 모바일 쇼핑몰 조회수 disabled
        } else {
            $getData['disabled']['bdMobileShopFl']['y'] = ' disabled="disabled" '; // 모바일 쇼핑몰 조회수 disabled
        }

        $arrayAuthData = ['리스트' => 'List', '읽기' => 'Read', '쓰기' => 'Write', '답글' => 'Reply', '댓글' => 'Memo'];
        foreach ($arrayAuthData as $key => $val) {
            if ($val == 'Write') {
                $getData['checked']['bdAuth' . $val]['all'] = $checked;
            } else {
                $getData['checked']['bdAuth' . $val]['all'] = $checked;
            }
        }

        $getData['checked']['bdReplyFl']['y'] = $checked; //에디터 사용여부

        //select
        $getData['selected']['bdUserLimitDsp'][0] = 'selected';

        // multicheck
        $getData['checked']['bdSpamMemoFl'][1] = $checked; //코멘트 스팸방지(외부유입차단)
        $getData['checked']['bdSpamMemoFl'][2] = $checked; //코멘트 스팸방지(자동등록방지문자)
        $getData['checked']['bdSpamBoardFl'][1] = $checked; //게시글 스팸방지(외부유입차단)
        $getData['checked']['bdSpamBoardFl'][2] = $checked; //게시글 스팸방지(자동등록방지문자)

        // disabled
        $getData['disabled']['bdIpFilterFl'] = ' disabled="disabled" '; //IP 끝자리 암호화표기

        //대표이미지 설정
        $getData['checked']['bdListImageTarget']['upload'] = $checked;

        //제목글 제한
        $getData['data']['bdSubjectLength'] = 30;
        //리스트출력 개수
        $getData['data']['bdListCount'] = 15;
        $getData['data']['bdListColsCount'] = 4;
        $getData['data']['bdListRowsCount'] = 2;

        //리스트 이미지 사이즈
        $getData['data']['bdListImageSizeWidth'] = 178;
        $getData['data']['bdListImageSizeHeight'] = 227;
        //이벤트 이미지 사이즈
        $getData['data']['bdEventListImageSizeWidth'] = 485;
        $getData['data']['bdEventListImageSizeHeight'] = 251;
        //상품상세 페이지 내 페이지별 게시물 수
        $getData['data']['bdGoodsPageCountPc'] = 10;
        $getData['data']['bdGoodsPageCountMobile'] = 5;


        $getData['checked']['bdEndEventType']['read'] = 'checked';  //종료된이벤트

        $getData['checked']['bdMileageFl']['n'] = $checked; //마일리지 사용유무
        $getData['checked']['bdMileageDeleteFl']['n'] = $checked; //게시글 삭제 시 마일리지 차감
        $getData['checked']['bdMileageLackAction']['nodelete'] = $checked; //차감 마일리지 부족 시 처리방법

        $getData['data']['bdCategoryCount'] = 1;

        $getData['data']['bdAllowDomainCount'] = 2;
        $getData['data']['bdAttachImageMaxSize'] = self::DEFAULT_ATTACH_IMAGE_MAX_SIZE;
        $getData['checked']['bdAttachImageDisplayFl']['y'] = $checked;
        $getData['checked']['bdAttachImagePosition']['top'] = $checked;
        $getData['checked']['bdPasswordMemoFl']['y'] = $checked;

        $boardTheme = new BoardTheme();
        if (\Globals::get('gGlobal.isUse')) {
            foreach (\Globals::get('gGlobal.useMallList') as $val) {
                $frontThemeList[$val['domainFl']] = $boardTheme->getThemeListByKind($val['skin']['frontLive'], 'default', 'n');
                $mobileThemeList[$val['domainFl']] = $boardTheme->getThemeListByKind($val['skin']['mobileLive'], 'default', 'y');
            }
        } else {
            $frontSkin = $liveSkin = \Globals::get('gSkin.frontSkinLive');
            $mobileSkin = $liveSkin = \Globals::get('gSkin.mobileSkinLive');
            $frontThemeList = $boardTheme->getThemeListByKind($frontSkin, 'default', 'n');
            $mobileThemeList = $boardTheme->getThemeListByKind($mobileSkin, 'default', 'y');
        }
        $getData['selected']['frontThemeList'] = $frontThemeList;
        $getData['selected']['mobileThemeList'] = $mobileThemeList;

        $getData['data']['bdNoticeCount'] = 3;  //공지사항 노출 개수
        $getData['checked']['bdListInNotice']['y'] = $checked;    //리스트 내 노출
        $getData['checked']['bdOnlyMainNotice']['y'] = $checked;  //첫페이지만 노출
        $getData['checked']['bdIncludeReplayInSearchFl']['n'] = $checked;

        $getData['checked']['bdIncludeReplayInSearchType']['front']['y'] = $checked;
        $getData['checked']['bdIncludeReplayInSearchType']['admin']['y'] = $checked;

        $getData['checked']['bdListNoticeImageDisplayPc']['y'] = $checked; //공지글 이미지 노출 여부 PC
        $getData['checked']['bdListNoticeImageDisplayMobile']['y'] = $checked; //공지글 이미지 노출 여부 MOBILE

        //SEO태그 관련
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        $getData['data']['seoTag']['target'] = 'board';
        $getData['data']['seoTag']['replaceCode'] = $seoTag->seoConfig['replaceCode']['board'];
        $getData['data']['seoTag']['config'] = $seoTag->seoConfig['tag'];
        $getData['checked']['seoTagFl']['n'] = $checked; //작성자 표시방법

        //게시글(답변) 삭제 관련
        $getData['checked']['bdReplyDelFl']['applicable'] = $checked;

        return $getData;
    }

    /**
     * 게시판정보
     * @param string $sno 일련번호
     * @return array 데이터
     */
    public function getBoardView($sno = null)
    {
        try {
            if (!$sno) {
                return $this->getDefaultConfig();
            }

            if (Validator::number($sno, null, null, true) === false) {
                throw new \Exception('잘못된 접근입니다.');
            }
            $getData = $data = $checked = $selected = $disabled = null;
            $arrBind = [];
            $this->db->strField = "*";
            $this->db->strWhere = "sno=?";
            $this->db->bind_param_push($arrBind, 'i', $sno);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            $getData['data'] = gd_htmlspecialchars_stripslashes($data);
            if ($getData['data']['bdKind'] == 'gallery' || $getData['data']['bdKind'] == 'event') {
                $getData['data']['bdListImageFl'] = 'y';
            }
            unset($data);

            if (!$getData['data']) {
                throw new \Exception(sprintf(__('%1$s 인자가 잘못되었습니다.'), '일련번호'));
            }

            $boardTheme = new BoardTheme();
            if (\Globals::get('gGlobal.isUse')) {
                foreach (\Globals::get('gGlobal.useMallList') as $val) {
                    $frontThemeList[$val['domainFl']] = $boardTheme->getThemeListByKind($val['skin']['frontLive'], $getData['data']['bdKind'], 'n');
                    $mobileThemeList[$val['domainFl']] = $boardTheme->getThemeListByKind($val['skin']['mobileLive'], $getData['data']['bdKind'], 'y');
                }
            } else {
                $frontSkin = $liveSkin = \Globals::get('gSkin.frontSkinLive');
                $mobileSkin = $liveSkin = \Globals::get('gSkin.mobileSkinLive');
                $frontThemeList = $boardTheme->getThemeListByKind($frontSkin, $getData['data']['bdKind'], 'n');
                $mobileThemeList = $boardTheme->getThemeListByKind($mobileSkin, $getData['data']['bdKind'], 'y');
            }

            if($getData['data']['bdId'] == Board::BASIC_GOODS_REIVEW_ID) {
                if($getData['data']['bdReviewAuthWrite'] == 'all'){
                    $getData['data']['bdGoodsType'] ='goods';
                    $disabled['bdGoodsType']['order'] = 'disabled';
                }
                else {
                    $getData['data']['bdGoodsType'] ='order';
                    $disabled['bdGoodsType']['goods'] ='disabled';
                }
            }
            if(empty($getData['data']['bdReviewExceptGoodsNo']) === false){
                $goods = new Goods();
                $goodsData = $goods->getGoodsDataDisplay($getData['data']['bdReviewExceptGoodsNo']);
                if(is_array($goodsData)){
                    $getData['data']['bdReviewExceptGoodsData'] = $goodsData;
                    $checked['bdReviewExceptGoodsData'] = 'checked';
                }
            }

            //SEO태그 개별 설정
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');
            $getData['data']['seoTag']['target'] = 'board';
            $getData['data']['seoTag']['config'] = $seoTag->seoConfig['tag'];
            $getData['data']['seoTag']['replaceCode'] = $seoTag->seoConfig['replaceCode']['board'];
            if(empty($getData['data']['seoTagSno']) === false) {
                $getData['data']['seoTag']['data'] = $seoTag->getSeoTagData($getData['data']['seoTagSno']);
            }
            $checked['seoTagFl'][$getData['data']['seoTagFl']] = ' checked="checked" '; //작성자 표시방법

            $selected['frontThemeList'] = $frontThemeList;
            $selected['mobileThemeList'] = $mobileThemeList;
            $getData['data']['pageUrl'] = $this->getPageUrl($getData['data']['bdId'], false);
            $getData['data']['pageMobileUrl'] = $this->getPageUrl($getData['data']['bdId'], true);

            // radiobutton
            $checked['bdUsePcFl'][$getData['data']['bdUsePcFl']] = ' checked="checked" '; //작성자 표시방법
            $checked['bdUseMobileFl'][$getData['data']['bdUseMobileFl']] = ' checked="checked" '; //작성자 표시방법
            $checked['bdUserDsp'][$getData['data']['bdUserDsp']] = ' checked="checked" '; //작성자 표시방법
            $checked['bdAdminDsp'][$getData['data']['bdAdminDsp']] = ' checked="checked" '; //관리자 표시방법
            $checked['bdListInView'][$getData['data']['bdListInView']] = ' checked="checked" '; //View 타입
            $checked['bdSecretFl'][$getData['data']['bdSecretFl']] = ' checked="checked" '; //비밀글 설정
            $checked['bdSecretTitleFl'][$getData['data']['bdSecretTitleFl']] = ' checked="checked" '; //비밀글 제목설정
            $checked['bdSecretReplyFl'][$getData['data']['bdSecretReplyFl']] = ' checked="checked" '; //비밀댓글 설정
            if(is_null($getData['data']['bdSecretReplyTitle']) === true || empty($getData['data']['bdSecretReplyTitle']) === true) { $getData['data']['bdSecretReplyTitleFl'] = '0'; }
            else { $getData['data']['bdSecretReplyTitleFl'] = '1'; }
            $checked['bdSecretReplyTitleFl'][$getData['data']['bdSecretReplyTitleFl']] = ' checked="checked" '; //비밀댓글 제목설정

            $checked['bdLinkFl'][$getData['data']['bdLinkFl']] = ' checked="checked" '; //링크 사용 유무
            $checked['bdMemoFl'][$getData['data']['bdMemoFl']] = ' checked="checked" '; //코멘트 사용 유무
            $checked['bdMobileFl'][$getData['data']['bdMobileFl']] = ' checked="checked" '; //휴대폰 작성
            $checked['bdEmailFl'][$getData['data']['bdEmailFl']] = ' checked="checked" '; //이메일 작성
            $checked['bdGoodsPtFl'][$getData['data']['bdGoodsPtFl']] = 'checked="checked"'; //별점
            $checked['bdRecommendFl'][$getData['data']['bdRecommendFl']] = 'checked="checked"'; //추천
            $checked['bdSnsFl'][$getData['data']['bdSnsFl']] = 'checked="checked"'; //sns

            // checkbox
            $checked['bdCategoryFl'] = ($getData['data']['bdCategoryFl'] == 'y') ? ' checked="checked" ' : ''; //말머리 사용 유무
            $checked['bdIpFl'] = ($getData['data']['bdIpFl'] == 'y') ? ' checked="checked" ' : ''; //아이피 출력 유무
            $checked['bdIpFilterFl'] = ($getData['data']['bdIpFilterFl'] == 'y') ? ' checked="checked" ' : ''; //아이피 별표 유무
            $checked['bdAnswerStatusFl'] = ($getData['data']['bdAnswerStatusFl'] == 'y') ? ' checked="checked" ' : ''; //답변관리 기능 사용 유무

            $checked['bdPcHitFl']['y'] = ($getData['data']['bdPcHitFl']['y'] == 'y') ? ' checked="checked" ' : ''; //PC 쇼핑몰 조회수 노출 유무
            $mobileConfig = $this->mobileConfig;
            if ($mobileConfig['mobileShopFl'] == 'y') {
                $checked['bdMobileHitFl']['y'] = ($getData['data']['bdMobileHitFl']['y'] == 'y') ? ' checked="checked" ' : ''; //모바일 쇼핑몰 조회수 노출 유무
                $disabled['bdMobileShopFl']['y'] = ' '; // 모바일 쇼핑몰 조회수 disabled
            } else {
                $disabled['bdMobileShopFl']['y'] = ' disabled="disabled" '; // 모바일 쇼핑몰 조회수 disabled
            }

            // multicheck
            $checked['bdSpamMemoFl'][1] = ($getData['data']['bdSpamMemoFl'] & 1) ? ' checked="checked" ' : ''; //코멘트 스팸방지(외부유입차단)
            $checked['bdSpamMemoFl'][2] = ($getData['data']['bdSpamMemoFl'] & 2) ? ' checked="checked" ' : ''; //코멘트 스팸방지(자동등록방지문자)
            $checked['bdSpamBoardFl'][1] = ($getData['data']['bdSpamBoardFl'] & 1) ? ' checked="checked" ' : ''; //게시글 스팸방지(외부유입차단)
            $checked['bdSpamBoardFl'][2] = ($getData['data']['bdSpamBoardFl'] & 2) ? ' checked="checked" ' : ''; //게시글 스팸방지(자동등록방지문자)
            $checked['bdEditorFl'][$getData['data']['bdEditorFl']] = 'checked="checked"';    //에디터사용여부
            $checked['bdKind'][$getData['data']['bdKind']] = 'checked="checked"';

            // select
            $selected['bdUserLimitDsp'][$getData['data']['bdUserLimitDsp']] = ' selected="selected" '; //작성자 노출제한

            // disabled
            $disabled['bdIpFilterFl'] = ($getData['data']['bdIpFl'] != 'y') ? ' disabled="disabled" ' : '';
            $checked['bdListImageFl'][$getData['data']['bdListImageFl']] = 'checked';   //대표이미지 사용여부 설정
            $checked['bdListImageTarget'][$getData['data']['bdListImageTarget']] = 'checked';   //대표이미지 설정

            $getData['data']['bdListImageSizeWidth'] = 0;
            $getData['data']['bdListImageSizeHeight'] = 0;
            if ($getData['data']['bdListImageSize']) {
                list($getData['data']['bdListImageSizeWidth'], $getData['data']['bdListImageSizeHeight']) = explode(INT_DIVISION, $getData['data']['bdListImageSize']);  //리스트 이미지 사이즈
            }
            if ($getData['data']['bdEndEventMsg']) { //이벤트 종료 시 액션 / 값 유무로 경고창액션
                $checked['bdEndEventType']['msg'] = 'checked';
            } else {
                $checked['bdEndEventType']['read'] = 'checked';
            }

            $checked['bdGoodsFl'][$getData['data']['bdGoodsFl']] = 'checked';   //상품연동여부
            $checked['bdGoodsType'][$getData['data']['bdGoodsType']] = 'checked';   //상품연동타입
            if ($getData['data']['bdGoodsType'] == 'orderDuplication') {
                $checked['bdGoodsType']['order'] = 'checked';   //상품연동타입
            }

            $getData['data']['onlyGoodsBdId'] = [Board::BASIC_GOODS_REIVEW_ID, Board::BASIC_GOODS_QA_ID];//상품연동 고정ID
            $checked['bdSubSubjectFl'][$getData['data']['bdSubSubjectFl']] = 'checked'; //부가제목여부
            $checked['bdSupplyDsp'][$getData['data']['bdSupplyDsp']] = 'checked';   //공급사 표시방법
            $checked['bdUploadFl'][$getData['data']['bdUploadFl']] = 'checked';

            $checked['bdMileageFl'][$getData['data']['bdMileageFl']] = 'checked';   //마일리지 사용유무
            $checked['bdMileageDeleteFl'][$getData['data']['bdMileageDeleteFl']] = 'checked';   //게시글삭제시 마일리지차감
            $checked['bdMileageLackAction'][$getData['data']['bdMileageLackAction']] = 'checked';   //차감마일리지부족 시 처리방법
            $checked['bdListNoticeImageDisplayPc'][$getData['data']['bdListNoticeImageDisplayPc']] = ' checked="checked"'; //공지글 이미지 노출 여부 PC
            $checked['bdListNoticeImageDisplayMobile'][$getData['data']['bdListNoticeImageDisplayMobile']] = ' checked="checked"'; //공지글 이미지 노출 여부 mobile

            $arrayAuthData = ['리스트' => 'List', '읽기' => 'Read', '쓰기' => 'Write', '답글' => 'Reply', '댓글' => 'Memo'];
            foreach ($arrayAuthData as $key => $val) {
                $checked['bdAuth' . $val][$getData['data']['bdAuth' . $val]] = 'checked';
                if ($getData['data']['bdAuth' . $val . 'Group']) {
                    $_arrData = explode(INT_DIVISION, $getData['data']['bdAuth' . $val . 'Group']);
                    $getData['data']['bdAuth' . $val . 'Group'] = GroupUtil::getGroupName("sno IN ('" . implode("','", $_arrData) . "')");
                }
            }
            $checked['bdReplyFl'][$getData['data']['bdReplyFl']] = 'checked';
            $arrBdAllowTags = explode(STR_DIVISION, $getData['data']['bdAllowTags']);
            $checked['bdAllowTags']['iframe'] = in_array('iframe', $arrBdAllowTags) ? 'checked' : '';
            $checked['bdAllowTags']['embed'] = in_array('embed', $arrBdAllowTags) ? 'checked' : '';

            $arrBdAllowDomain = explode(STR_DIVISION, $getData['data']['bdAllowDomain']);
            if (count($arrBdAllowDomain) < 2) {
                $getData['data']['bdAllowDomainCount'] = 2;
            } else {
                $getData['data']['bdAllowDomainCount'] = count($arrBdAllowDomain);
            }

            $arrBdCategory = explode(STR_DIVISION, $getData['data']['bdCategory']);
            if (count($arrBdCategory) < 1) {
                $getData['data']['bdCategoryCount'] = 1;
            } else {
                $getData['data']['bdCategoryCount'] = count($arrBdCategory);
            }

            $getData['data']['arrBdAllowDomain'] = $arrBdAllowDomain;
            $checked['bdAttachImageDisplayFl'][$getData['data']['bdAttachImageDisplayFl']] = 'checked';
            $checked['bdAttachImagePosition'][$getData['data']['bdAttachImagePosition']] = 'checked';
            $checked['bdIncludeReplayInSearchFl'][$getData['data']['bdIncludeReplayInSearchFl']] = 'checked';

            $bdIncludeReplayInSearchType = $getData['data']['bdIncludeReplayInSearchType'] ?? 3;
            $getData['data']['bdIncludeReplayInSearchType'] = null;

            $getData['data']['bdIncludeReplayInSearchType']['front'] = $bdIncludeReplayInSearchType & 1 ? 'y' : 'n';
            $getData['data']['bdIncludeReplayInSearchType']['admin'] = $bdIncludeReplayInSearchType & 2 ? 'y' : 'n';
            if ($getData['data']['bdIncludeReplayInSearchType']['front'] == 'y'){
                $checked['bdIncludeReplayInSearchType']['front']['y'] = 'checked';
            }
            if ($getData['data']['bdIncludeReplayInSearchType']['admin'] == 'y'){
                $checked['bdIncludeReplayInSearchType']['admin']['y'] = 'checked';
            }
            list($bdNoticeCount,$bdListInNotice,$bdOnlyMainNotice) = explode(STR_DIVISION,$getData['data']['bdNoticeDisplay']);
            $checked['bdListInNotice'][$bdListInNotice] = 'checked';
            $checked['bdOnlyMainNotice'][$bdOnlyMainNotice] = 'checked';
            $getData['data']['bdNoticeCount'] = $bdNoticeCount;

            gd_isset($getData['data']['bdReviewAuthWrite'],'all');
            gd_isset($getData['data']['bdReviewOrderStatus'],'s1');
            gd_isset($getData['data']['bdReviewDuplicateLimit'],'free');

            if($getData['data']['bdReviewPeriod']>0){
                $checked['bdReviewPeriodFl'] = 'checked';
            }

            $getData['data']['bdReviewPeriod'] = $getData['data']['bdReviewPeriod'] ? $getData['data']['bdReviewPeriod'] : 15;

            $checked['bdReviewAuthWrite'][$getData['data']['bdReviewAuthWrite']] = 'checked'; //쓰기권한 추가기능
            $selected['bdReviewOrderStatus'][$getData['data']['bdReviewOrderStatus']] = 'selected'; //작성가능 시점
            $checked['bdReviewDuplicateLimit'][$getData['data']['bdReviewDuplicateLimit']] = 'checked'; //쓰기권한 추가기능
            $selected['bdReviewPeriod'][$getData['data']['bdReviewPeriod']] = 'selected'; //작성가능 기간
            $checked['bdHitIPCheck'][$getData['data']['bdHitIPCheck']] = 'checked'; //조회수 중복체크
            $checked['bdReplyDelFl'][$getData['data']['bdReplyDelFl']] = ' checked="checked" '; //게시글 삭제 설정
            $checked['bdReplyMileageFl'][$getData['data']['bdReplyMileageFl']] = 'checked'; //답변글 작성 시에도 마일리지 지급

            $checked['bdPasswordMemoFl'][$getData['data']['bdPasswordMemoFl']] = 'checked'; //비밀댓글 암호보안 여부

            $getData['checked'] = $checked;
            $getData['selected'] = $selected;
            $getData['disabled'] = $disabled;

            return $getData;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 게시판등록
     * @param array $arrData
     * @throws \Exception
     * @internal param $file
     */
    public function insertBoardData($arrData)
    {
        // 아이디 중복여부 체크
        if ($this->overlapBdId($arrData['bdId'])) {
            throw new \Exception(sprintf(__('%1$s 는 이미 등록된 아이디입니다'), $arrData['bdId']));
        }

        switch ($arrData['bdKind']) {
            case 'event' :
                $arrData['bdEventFl'] = 'y';
                $arrData['bdGoodsPtFl'] = 'n';
                break;
            case 'qa' :
                $arrData['bdReplyStatusFl'] = 'y';
                break;
        }

        gd_isset($arrData['bdNewFl'], '0');
        gd_isset($arrData['bdHotFl'], '0');

        //공지사항글 이미지 노출 여부
        gd_isset($arrData['bdListNoticeImageDisplayPc'], 'n');
        gd_isset($arrData['bdListNoticeImageDisplayMobile'], 'n');

        if ($arrData['bdGoodsType'] == 'order' && $arrData['bdGoodsTypeOrderDuplication'] == 'y') {
            $arrData['bdGoodsType'] = 'orderDuplication';
        }
        if ($arrData['bdGoodsFl'] == 'n') {
            $arrData['bdGoodsType'] = 'n';
        }

        $arrData['bdAllowTags'] = implode(STR_DIVISION, gd_array_remove_empty($arrData['bdAllowTags']));
        if (count($arrData['bdAllowDomain']) > 20) {
            throw new \Exception('허용도메인은 최대 20개까지 등록가능합니다.');
        }
        $arrData['bdAllowDomain'] = implode(STR_DIVISION, gd_array_remove_empty($arrData['bdAllowDomain']));

        // 말머리 입력 방식 수정
        $arrBdCategory = $arrData['bdCategory'];
        $filterBdCategory = array_filter(array_map('trim',$arrBdCategory));
        $arrData['bdCategory'] = implode(STR_DIVISION,$filterBdCategory);

        // 말머리 양식
        $arrBdCategoryTemplateSno = $arrData['bdCategoryTemplateSno'];
        $filterBdCategoryTemplateSno = array_filter(array_map('trim',$arrBdCategoryTemplateSno), 'is_numeric');
        $arrData['bdCategoryTemplateSno'] = implode(STR_DIVISION,$filterBdCategoryTemplateSno);

        // 조회수 노출 여부
        $arrData['bdPcHitFl'] = $arrData['bdPcHitFl'] ?? 'n';
        $arrData['bdMobileHitFl'] = $arrData['bdMobileHitFl'] ?? 'n';

        $arrData['bdSpamMemoFl'] = $this->sumMultiCheckValue(gd_isset($arrData['bdSpamMemoFl']));
        $arrData['bdSpamBoardFl'] = $this->sumMultiCheckValue(gd_isset($arrData['bdSpamBoardFl']));

        $_filterBdHeader = preg_replace('/[&nbsp;|<p>|<\/p>]/i', '', $arrData['bdHeader']);
        $_filterBdFooter = preg_replace('/[&nbsp;|<p>|<\/p>]/i', '', $arrData['bdFooter']);

        $arrData['bdHeader'] = $_filterBdHeader ? $arrData['bdHeader'] : '';
        $arrData['bdFooter'] = $_filterBdFooter ? $arrData['bdFooter'] : '';

        // 기본 저장소 local로 지정
        gd_isset($arrData['bdUploadStorage'], 'local');
        if ($arrData['bdUploadStorage'] == 'local') {
            $uploadPath = 'upload/' . $arrData['bdId'];
            $uploadThumPath = 'upload/' . $arrData['bdId'] . '/t';
            $arrData['bdUploadPath'] = gd_isset($uploadPath);
            $arrData['bdUploadThumbPath'] = gd_isset($uploadThumPath);
        }

        $arrData['bdUploadPath'] = (gd_isset($arrData['bdUploadPath']) && substr($arrData['bdUploadPath'], strlen($arrData['bdUploadPath']) - 1, 1) != '/') ? $arrData['bdUploadPath'] . '/' : $arrData['bdUploadPath'];
        $arrData['bdUploadThumbPath'] = (gd_isset($arrData['bdUploadThumbPath']) && substr($arrData['bdUploadThumbPath'], strlen($arrData['bdUploadThumbPath']) - 1, 1) != '/') ? $arrData['bdUploadThumbPath'] . '/' : $arrData['bdUploadThumbPath'];

        // 업로드 최대크기 확인
        if ($arrData['bdUploadMaxSize'] > str_replace('M', '', ini_get('upload_max_filesize'))) {
            throw new \Exception(sprintf(__('최대 업로드 사이즈는 %s 입니다'), ini_get('upload_max_filesize')));
        }

        if ($arrData['bdEndEventType'] == 'read') {
            $arrData['bdEndEventMsg'] = '';
        }

        if ($arrData['bdListImageSize']['width'] && $arrData['bdListImageSize']['height']) {
            $arrData['bdListImageSize'] = implode(INT_DIVISION, $arrData['bdListImageSize']);
        }

        //공지사항 노출설정
        $arrData['bdListInNotice'] = $arrData['bdListInNotice'] ?? 'n';
        $arrData['bdOnlyMainNotice'] = $arrData['bdOnlyMainNotice'] ?? 'n';
        $arrData['bdNoticeDisplay'] = $arrData['bdNoticeCount'].STR_DIVISION.$arrData['bdListInNotice'].STR_DIVISION.$arrData['bdOnlyMainNotice'];

        //비밀댓글 제목
        if ( $arrData['bdSecretReplyTitleFl'] == '0' ) { $arrData['bdSecretReplyTitle'] = null; }
        unset($arrData['bdSecretReplyTitleFl']);

        $this->validate('regist', $arrData);
        $arrData['bdBasicFl'] = 'n';
        $arrayAuthData = [__('리스트') => 'List', __('읽기') => 'Read', __('쓰기') => 'Write', __('답글') => 'Reply', __('댓글') => 'Memo'];
        foreach ($arrayAuthData as $key => $val) {
            if ($arrData['bdAuth' . $val] == 'group') {
                if (!$arrData['bdAuth' . $val . 'Group'] || is_array($arrData['bdAuth' . $val . 'Group']) == false) {
                    throw new \Exception(sprintf(__('%1$s 권한의 특정회원등급이 선택되지 않았습니다.'), $key));
                }
                $arrData['bdAuth' . $val . 'Group'] = implode(INT_DIVISION, $arrData['bdAuth' . $val . 'Group']);
            }
        }

        $arrData['bdIncludeReplayInSearchType'] = array_sum($arrData['bdIncludeReplayInSearchType']);

        if (gd_isset($arrData['bdUploadStorage']) && gd_isset($arrData['bdUploadPath']) && gd_isset($arrData['bdUploadThumbPath'])) {
            Storage::disk(Storage::PATH_CODE_BOARD, $arrData['bdUploadStorage'])->createDir($arrData['bdUploadPath']);
            Storage::disk(Storage::PATH_CODE_BOARD, $arrData['bdUploadStorage'])->createDir($arrData['bdUploadThumbPath']);
        }

        //SEO개별태그관련
        $seoTagData = $arrData['seoTag'];
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        if($arrData['seoTagFl'] =='n') {
            if($arrData['seoTagSno'] ) {
                $seoTag->deleteSeoTag(['sno'=>$arrData['seoTagSno']]);
            }
            $arrData['seoTagSno'] = "";
        } else {
            $seoTagData['sno'] = $arrData['seoTagSno'];
            $seoTagData['pageCode'] = $arrData['bdId'];
            $arrData['seoTagSno'] = $seoTag->saveSeoTagEach('board',$seoTagData);
        }

        gd_isset($arrData['bdHitIPCheck'], 'n');

        gd_isset($arrData['bdReplyMileageFl'], 'n'); // 답변글 작성 시에도 마일리지 지급
        // 저장

        $this->db->begin_tran();
        try {
            $arrBind = $this->db->get_binding(DBTableField::tableBoard(), $arrData, 'insert');
            $this->db->set_insert_db(DB_BOARD, $arrBind['param'], $arrBind['bind'], 'y');
            $strSQL = " CREATE TABLE " . DB_BD_ . $arrData['bdId'] . " (
                        sno				INT(10)			AUTO_INCREMENT PRIMARY KEY	COMMENT '시퀀스 번호' ,
                        groupNo			INT(11)			NOT NULL					COMMENT '그룹핑번호',
                        groupThread		VARCHAR(255)	 BINARY NOT NULL				COMMENT '답변',
                        channel		VARCHAR(50)	      NULL	DEFAULT NULL			COMMENT '채널',
                        memNo			INT(10)			NOT NULL	DEFAULT 0        	COMMENT '작성자번호',
                        writerNm		VARCHAR(20)					DEFAULT NULL	COMMENT '작성자명',
                        apiExtraData		VARCHAR(100)					DEFAULT NULL	COMMENT 'api연동데이터',
                        writerId		VARCHAR(50)					DEFAULT NULL	COMMENT '작성자아이디',
                        writerNick		VARCHAR(50)					DEFAULT NULL	COMMENT '작성자닉네임',
                        writerEmail		VARCHAR(50)					DEFAULT NULL	COMMENT '이메일',
                        writerHp		VARCHAR(100)				DEFAULT NULL	COMMENT '작성자홈페이지',
                        writerPw		VARCHAR(32)					DEFAULT NULL	COMMENT '비밀번호',
                        writerIp		VARCHAR(15)		NOT NULL					COMMENT '아이피',
                        subject			VARCHAR(100)		NOT NULL		COMMENT '글제목',
                        subSubject			VARCHAR(100)				DEFAULT NULL	COMMENT '부제목',
                        contents		MEDIUMTEXT						DEFAULT NULL	COMMENT '글내용',
                        urlLink			VARCHAR(255)				DEFAULT NULL	COMMENT 'url',
                        uploadFileNm	VARCHAR(255)				DEFAULT NULL	COMMENT '원본이미지파일명',
                        saveFileNm		VARCHAR(255)				DEFAULT NULL	COMMENT '저장이미지파일명',
                        parentSno		INT(11)						DEFAULT NULL	COMMENT '원글의일련번호',
                        isNotice		ENUM('y', 'n')	NOT NULL	DEFAULT 'n'		COMMENT '공지여부',
                        isSecret		ENUM('y', 'n')	NOT NULL	DEFAULT 'n'		COMMENT '비밀글여부',
                        hit				INT(10)				NOT NULL		DEFAULT 0		COMMENT '조회수',
                        memoCnt			SMALLINT(5)		NOT NULL	DEFAULT 0		COMMENT '코멘트수',
                        category		VARCHAR(50)		NOT NULL	DEFAULT ''		COMMENT '카테고리',
                        writerMobile	VARCHAR(20)					DEFAULT NULL	COMMENT '작성자휴대폰',
                        goodsNo			INT(10)			    NOT NULL	DEFAULT 0	COMMENT '상품번호',
                        goodsPt			tinyint		NOT NULL        DEFAULT 0 	COMMENT '상품평점',
                        orderNo			VARCHAR(16)					DEFAULT NULL	COMMENT '주문번호',
                        mileage			INT(10)						DEFAULT NULL	COMMENT '마일리지',
                        mileageReason	VARCHAR(100)				DEFAULT NULL	COMMENT '마일리지지급이유',
                        recommend		    int(10)	    NOT NULL			DEFAULT 0	COMMENT '추천',
                        replyStatus		    ENUM('0','1','2','3')		NOT NULL		DEFAULT '0'	COMMENT '답변상태값',
                        isDelete		ENUM('y', 'n')		NOT NULL		DEFAULT 'n'		COMMENT '삭제',
                        eventStart		 DATETIME				DEFAULT NULL	    COMMENT '이벤트시작일',
                        eventEnd		DATETIME				DEFAULT NULL		COMMENT '이벤트마감일',
                        answerSubject		 VARCHAR(255)				DEFAULT NULL	    COMMENT '답변제목',
                        answerContents		MEDIUMTEXT				DEFAULT NULL		COMMENT '답변내용',
                        answerManagerNo		 INT(10)				DEFAULT NULL	    COMMENT '답변관리자번호',
                        answerModDt		 DATETIME			DEFAULT NULL	    COMMENT '답변수정날짜',
                        bdUploadStorage		 VARCHAR(255)				DEFAULT NULL	    COMMENT '저장소',
                        bdUploadPath		 VARCHAR(255)				DEFAULT NULL	    COMMENT '저장경로',
                        bdUploadThumbPath		 VARCHAR(255)				DEFAULT NULL	    COMMENT '섬네일저장경로',
                        isMobile		 ENUM('y', 'n')			NOT NULL	DEFAULT 'n'	    COMMENT '모바일여부',
                        regDt			DATETIME									COMMENT '등록일',
                        modDt			DATETIME									COMMENT '수정일',
                        KEY BD_IX01(groupNo,groupThread),
                        KEY BD_IX02(goodsNo),
                        KEY BD_IX03(isDelete,groupThread),
                        KEY BD_IX04(isNotice),
                        KEY BD_IX05(isDelete,groupNo,groupThread,memNo,replyStatus,subject)
                    )\n
                    COLLATE='utf8mb4_general_ci'\n
                    ENGINE=InnoDB";
            $this->db->query($strSQL);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        unset($arrData);

    }

    /**
     * 게시판수정
     * @param array $arrData
     * @param $file
     * @throws \Exception
     */
    public function modifyBoardData($arrData)
    {
        gd_isset($arrData['bdNewFl'], '0');
        gd_isset($arrData['bdHotFl'], '0');

        if($arrData['presentExceptFl'] == 'goods'){
            $arrData['bdReviewExceptGoodsNo'] = $arrData['exceptGoods'] ? implode(INT_DIVISION,$arrData['exceptGoods']) : '';
        }
        else {
            $arrData['bdReviewExceptGoodsNo'] = '';
        }

        if($arrData['bdReviewPeriodFl'] != 'y') {
            $arrData['bdReviewPeriod'] = 0;
        }

        if ($arrData['bdGoodsType'] == 'order' && $arrData['bdGoodsTypeOrderDuplication'] == 'y') {
            $arrData['bdGoodsType'] = 'orderDuplication';
        }
        if ($arrData['bdGoodsFl'] == 'n') {
            $arrData['bdGoodsType'] = 'n';
        }
        //상품후기 , 상품문의
        if ($arrData['bdId'] == Board::BASIC_GOODS_REIVEW_ID || $arrData['bdId'] == Board::BASIC_GOODS_REIVEW_ID) {
            $arrData['bdGoodsFl'] = 'y';
        }

        //공지사항글 이미지 노출 여부
        gd_isset($arrData['bdListNoticeImageDisplayPc'], 'n');
        gd_isset($arrData['bdListNoticeImageDisplayMobile'], 'n');

        $arrData['bdAllowTags'] = implode(STR_DIVISION, gd_array_remove_empty($arrData['bdAllowTags']));
        if (count($arrData['bdAllowDomain']) > 20) {
            throw new \Exception(sprintf(__('허용도메인은 최대 %s 개까지 등록가능합니다.'), 20));
        }
        $arrData['bdAllowDomain'] = implode(STR_DIVISION, gd_array_remove_empty($arrData['bdAllowDomain']));

        // 말머리 입력 방식 수정
        $arrBdCategory = $arrData['bdCategory'];
        $filterBdCategory = array_filter(array_map('trim',$arrBdCategory));
        $arrData['bdCategory'] = implode(STR_DIVISION,$filterBdCategory);

        // 말머리 양식
        $arrBdCategoryTemplateSno = $arrData['bdCategoryTemplateSno'];
        $filterBdCategoryTemplateSno = array_filter(array_map('trim',$arrBdCategoryTemplateSno), 'is_numeric');
        $arrData['bdCategoryTemplateSno'] = implode(STR_DIVISION,$filterBdCategoryTemplateSno);

        $arrData['bdSpamMemoFl'] = $this->sumMultiCheckValue(gd_isset($arrData['bdSpamMemoFl']));
        $arrData['bdSpamBoardFl'] = $this->sumMultiCheckValue(gd_isset($arrData['bdSpamBoardFl']));
        $arrData['bdPasswordMemoFl'] = gd_isset($arrData['bdPasswordMemoFl'], 'n');

        // 조회수 노출 여부
        $arrData['bdPcHitFl'] = $arrData['bdPcHitFl'] ?? 'n';
        $arrData['bdMobileHitFl'] = $arrData['bdMobileHitFl'] ?? 'n';

        // 기본 저장소 local로 지정 (에디터의 업로드 파일 저장)
        gd_isset($arrData['bdUploadStorage'], 'local');
        if ($arrData['bdUploadStorage'] == 'local' || $arrData['bdUploadStorage'] == 'url') {
            $uploadPath = 'upload/' . $arrData['bdId'];
            $uploadThumPath = 'upload/' . $arrData['bdId'] . '/t';

            $arrData['bdUploadPath'] = gd_isset($arrData['bdUploadPath'], $uploadPath);
            $arrData['bdUploadThumbPath'] = gd_isset($arrData['bdUploadThumbPath'], $uploadThumPath);
        }

        $arrData['bdUploadPath'] = (gd_isset($arrData['bdUploadPath']) && substr($arrData['bdUploadPath'], strlen($arrData['bdUploadPath']) - 1, 1) != '/') ? $arrData['bdUploadPath'] . '/' : $arrData['bdUploadPath'];
        $arrData['bdUploadThumbPath'] = (gd_isset($arrData['bdUploadThumbPath']) && substr($arrData['bdUploadThumbPath'], strlen($arrData['bdUploadThumbPath']) - 1, 1) != '/') ? $arrData['bdUploadThumbPath'] . '/' : $arrData['bdUploadThumbPath'];
        // 업로드 최대크기 확인
        if ($arrData['bdUploadMaxSize'] > str_replace('M', '', ini_get('upload_max_filesize'))) {
            throw new \Exception(sprintf(__('업로드 최대 용량은 %s 까지 설정할 수 있습니다. 서버환경를 확인하시기 바랍니다.'), ini_get('upload_max_filesize')));
        }

        if ($arrData['bdListImageSize']['width'] && $arrData['bdListImageSize']['height']) {
            $arrData['bdListImageSize'] = implode(INT_DIVISION, $arrData['bdListImageSize']);
        }

        $_filterBdHeader = preg_replace('/[&nbsp;|<p>|<\/p>]/i', '', $arrData['bdHeader']);
        $_filterBdFooter = preg_replace('/[&nbsp;|<p>|<\/p>]/i', '', $arrData['bdFooter']);

        $arrData['bdHeader'] = $_filterBdHeader ? $arrData['bdHeader'] : '';
        $arrData['bdFooter'] = $_filterBdFooter ? $arrData['bdFooter'] : '';

        $arrayAuthData = [__('리스트') => 'List', __('읽기') => 'Read', __('쓰기') => 'Write', __('답글') => 'Reply', __('댓글') => 'Memo'];
        foreach ($arrayAuthData as $key => $val) {
            if ($arrData['bdAuth' . $val] == 'group') {
                if (!$arrData['bdAuth' . $val . 'Group'] || is_array($arrData['bdAuth' . $val . 'Group']) == false) {
                    throw new \Exception(sprintf(__('%s 권한의 특정회원등급이 선택되지 않았습니다.'), $key));
                }
                $arrData['bdAuth' . $val . 'Group'] = implode(INT_DIVISION, $arrData['bdAuth' . $val . 'Group']);
            } else {
                $arrData['bdAuth' . $val . 'Group'] = '';
            }
        }

        if ($arrData['bdKind'] == 'event') {
            $arrData['bdEventFl'] = 'y';
            $arrData['bdGoodsPtFl'] = 'n';
        } else {
            $arrData['bdEventFl'] = 'n';
        }

        if ($arrData['bdKind'] == 'qa') {
            $arrData['bdReplyStatusFl'] = 'y';
            $arrData['bdGoodsPtFl'] = 'n';
        } else {
            $arrData['bdReplyStatusFl'] = 'n';
        }

        //공지사항설정
        $arrData['bdNoticeCount'] = $arrData['bdNoticeCount'] ?? 0;
        $arrData['bdListInNotice'] = $arrData['bdListInNotice'] ?? 'n';
        $arrData['bdOnlyMainNotice'] = $arrData['bdOnlyMainNotice'] ?? 'n';
        $arrData['bdNoticeDisplay'] = $arrData['bdNoticeCount'].STR_DIVISION.$arrData['bdListInNotice'].STR_DIVISION.$arrData['bdOnlyMainNotice'];
        $arrData['bdIncludeReplayInSearchType'] = array_sum($arrData['bdIncludeReplayInSearchType']);
        $arrData['bdIncludeReplayInSearchType'] = $arrData['bdIncludeReplayInSearchType']?? 0;

        //SEO개별태그관련
        $seoTagData = $arrData['seoTag'];
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        if (empty($arrData['seoTagFl']) === false) {
            $seoTagData['sno'] = $arrData['seoTagSno'];
            $seoTagData['pageCode'] = $arrData['bdId'];
            $arrData['seoTagSno'] = $seoTag->saveSeoTagEach('board',$seoTagData);
        }

        //비밀댓글 제목
        if ( $arrData['bdSecretReplyTitleFl'] == '0' ) { $arrData['bdSecretReplyTitle'] = null; }
        unset($arrData['bdSecretReplyTitleFl']);

        // Validation
        $this->validate('modify', $arrData);

        $dbTableField = DBTableField::tableBoard();
        $exceptfield = ['bdId', 'bdBasicFl', 'bdReplyStatusFl'];
        if (!$arrData['bdKind']) {
            $exceptfield[] = 'bdKind';
        }
        foreach ($dbTableField as $key => $val) {
            if (in_array($val['val'], $exceptfield)) {
                unset($dbTableField[$key]);
            }
        }

        gd_isset($arrData['bdHitIPCheck'], 'n');
        gd_isset($arrData['bdReplyMileageFl'], 'n');

        $arrBind = $this->db->get_binding($dbTableField, $arrData, 'update', null, array('bdId'));
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
        $this->db->set_update_db(DB_BOARD, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
        unset($arrBind);
        unset($arrData);
    }

    /**
     * 게시판삭제
     * @param int $sno 일련번호
     * @throws \Exception
     */
    public function deleteBoardData($sno, $seotagSno = null)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%1$s 인자가 잘못되었습니다.'), '일련번호'));
        }

        $strSQL = 'SELECT bdId, bdUploadStorage, bdUploadPath, bdUploadThumbPath,bdBasicFl FROM ' . DB_BOARD . ' WHERE sno=?';
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        $res = gd_htmlspecialchars_stripslashes($res);

        if ($res) {
            if ($res['bdBasicFl'] == 'y') {
                throw new \Exception(__('기본으로 제공되는 게시판은 삭제 하실 수 없습니다.'));
            }
            if (gd_isset($res['bdUploadStorage']) && gd_isset($res['bdUploadPath'])) {
                if ($res['bdUploadThumbPath']) {
                    Storage::disk(Storage::PATH_CODE_BOARD, $res['bdUploadStorage'])->deleteDir($res['bdUploadThumbPath']);
                }
                if ($res['bdUploadPath']) {
                    Storage::disk(Storage::PATH_CODE_BOARD, $res['bdUploadStorage'])->deleteDir($res['bdUploadPath']);
                }
            }
        } else {
            throw new \Exception(sprintf(__('인자가 잘못되었습니다.(게시판고유번호 : %s )'), $sno));
        }
        $this->db->set_delete_db(DB_BOARD, 'sno = ?', $arrBind);
        unset($arrBind);

        $this->db->bind_param_push($arrBind, $this->fieldTypes['bdId'], $res['bdId']);
        $this->db->set_delete_db(DB_BOARD_MEMO, 'bdId = ?', $arrBind);
        unset($arrBind);
        $this->db->query("DROP TABLE " . DB_BD_ . $res['bdId']);

        // seo태그 삭제
        if (empty($seotagSno) === false) {
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');
            $seoTag->deleteSeoTag(['sno' => $seotagSno]);
        }
    }

    private function validate($mode, &$arrData)
    {
        // Validation
        $validator = new Validator();
        switch ($mode) {
            case 'regist' : {
                $validator->add('bdId', '', true); // 아이디
                break;
            }
            case 'modify' : {
                $validator->add('sno', 'number', true); // 아이디
                break;
            }
        }
        $validator->add('bdNm', '', true); // 이름
        $validator->add('themeSno', 'i'); // 스킨 고유번호
        $validator->add('mobileThemeSno', 'i'); // 모바일스킨 고유번호

        $validator->add('themeUsSno', 'i'); // 스킨 고유번호
        $validator->add('mobileThemeUsSno', 'i'); // 모바일스킨 고유번호
        $validator->add('themeCnSno', 'i'); // 스킨 고유번호
        $validator->add('mobileThemeCnSno', 'i'); // 모바일스킨 고유번호
        $validator->add('themeJpSno', 'i'); // 스킨 고유번호
        $validator->add('mobileThemeJpSno', 'i'); // 모바일스킨 고유번호

        $validator->add('bdKind', ''); // 유형
        $validator->add('bdNewFl', 'number'); // 새글 지속 시간
        $validator->add('bdHotFl', 'number'); // 인기글 조회수
        $validator->add('bdIpFl', 'yn'); // 아이피출력유무
        $validator->add('bdIpFilterFl', 'yn'); // 아이피별표유무
        $validator->add('bdListInView', 'yn'); // 상세보기타입
        $validator->add('bdUploadStorage', ''); // 업로드파일저장소
        $validator->add('bdUploadPath', ''); // 업로드파일경로
        $validator->add('bdUploadThumbPath', ''); // 업로드파일경로(썸네일이미지)
        $validator->add('bdUploadMaxSize', 'number'); // 업로드최대파일사이즈
        $validator->add('bdHeader', ''); // 헤더
        $validator->add('bdFooter', ''); // 푸터
        $validator->add('bdCategoryFl', 'yn'); // 말머리사용유무
        $validator->add('bdCategoryTitle', ''); // 말머리타이틀
        $validator->add('bdCategory', ''); // 말머리
        $validator->add('bdCategoryTemplateSno', ''); // 말머리 게시판 양식
        $validator->add('bdPcHitFl', 'yn'); // PC 쇼핑몰 조회수 노출 여부
        $validator->add('bdMobileHitFl', 'yn'); // Mobile 쇼핑몰 조회수 노출 여부
        $validator->add('bdUserDsp', ''); // 작성자표시
        $validator->add('bdUserLimitDsp', ''); // 작성자표시
        $validator->add('bdAdminDsp', ''); // 관리자표시
        $validator->add('bdSpamMemoFl', 'number'); // 코멘트 스팸방지
        $validator->add('bdSpamBoardFl', 'number'); // 게시글 스팸방지
        $validator->add('bdSecretFl', ''); // 비밀글 설정
        $validator->add('bdSecretTitleFl', ''); // 비밀글 설정
        $validator->add('bdSecretTitleTxt', ''); // 비밀글 설정
        $validator->add('bdSecretReplyFl', ''); // 비밀댓글 설정
        $validator->add('bdSecretReplyTitle', ''); // 비밀댓글 제목설정
        $validator->add('bdSubjectClrFl', 'yn'); // 제목글자색 사용
        $validator->add('bdSubjectSizeFl', 'yn'); // 제목글자크기 사용
        $validator->add('bdSubjectBoldFl', 'yn'); // 제목글자굵기 사용
        $validator->add('bdMemoFl', 'yn'); // 코멘트사용유무
        $validator->add('bdLinkFl', 'yn'); // 링크사용유무
        $validator->add('bdMobileFl', 'yn'); // 이메일작성
        $validator->add('bdEmailFl', 'yn'); // 이메일작성
        $validator->add('bdHitPerCnt', 'number'); // 조회당 증가수
        $validator->add('bdStartNum', 'number'); // 게시물 시작번호
        $validator->add('bdSubjectLength', 'number'); // 제목글 제한
        $validator->add('bdListCount', 'number'); // 페이지당 Pc게시물 수
        $validator->add('bdListColsCount', 'number'); // 페이지당 모바일게시물 수
        $validator->add('bdListRowsCount', 'number'); // 페이지당 모바일게시물 수
        $validator->add('bdListImageFl', 'yn'); // 대표이미지사용여부
        $validator->add('bdListImageTarget', ''); // 대표이미지 설정
        $validator->add('bdListImageSize', ''); // 이미지크기
        $validator->add('bdEndEventMsg', ''); // 종료된 이벤트
        $validator->add('bdGoodsPtFl', 'yn'); // 평점사용여부
        $validator->add('bdSnsFl', 'yn'); // sns연동
        $validator->add('bdRecommendFl', 'yn'); // 추천기능여부
        $validator->add('bdGoodsFl', 'yn'); // 상품연동
        $validator->add('bdSubSubjectFl', ''); // 부가제목
        $validator->add('bdSupplyDsp', ''); // 공급사 표시방법
        $validator->add('bdUploadFl', ''); // 업로드 여부
        $validator->add('bdMileageFl', 'yn'); // 마일리지 사용유무
        $validator->add('bdMileageAmount', 'number'); // 마일리지 지급
        $validator->add('bdMileageDeleteFl', 'yn'); // 게시글 삭제 시 마일리지 차감
        $validator->add('bdMileageLackAction', ''); //차감 마일리지 부족시 처리방법
        $validator->add('bdEditorFl', 'yn'); //에디터 사용여부
        $validator->add('bdBasicFl', 'yn'); // 기본제공게시판 여부
        $validator->add('bdEventFl', 'yn'); // 기본제공게시판 여부
        $validator->add('bdReplyStatusFl', 'yn'); // 기본제공게시판 여부
        $validator->add('bdUsePcFl', 'yn'); // pc쇼핑몰 사용여부
        $validator->add('bdUseMobileFl', 'yn'); // 모바일쇼핑몰 사용여부
        $validator->add('bdAuthList', 's'); // 리스트 권한
        $validator->add('bdAuthRead', 's'); // 읽기 권한
        $validator->add('bdAuthWrite', 's'); // 쓰기권한
        $validator->add('bdReplyFl', 's'); // 답글사용여부
        $validator->add('bdAuthReply', 's'); // 답글권한
        $validator->add('bdAuthMemo', 's'); // 댓글권한
        $validator->add('bdAuthListGroup', 's'); // 리스트 특정회원그룹
        $validator->add('bdAuthReadGroup', 's'); // 읽기 특정회원그룹
        $validator->add('bdAuthWriteGroup', 's'); // 쓰기 특정회원그룹
        $validator->add('bdAuthReplyGroup', 's'); // 답글 특정회원그룹
        $validator->add('bdAuthMemoGroup', 's'); // 답글 특정회원그룹
        $validator->add('bdAllowTags', 's'); // 허용 태그
        $validator->add('bdAllowDomain', 's'); // 허용 도매인
        $validator->add('bdGoodsType', 's'); // 상폄연동타입
        $validator->add('bdAttachImageDisplayFl', 's'); // 첨부파일 이미지 노출여부
        $validator->add('bdAttachImageMaxSize', 'i'); // 첨부파일 이미지 max 사이즈
        $validator->add('bdAttachImagePosition', 's'); // 첨부파일 이미지 노출위치
        $validator->add('bdTemplateSno', 'i'); // 게시글 양식
        $validator->add('bdNoticeDisplay', 's'); // 공지사항 노출설정
        $validator->add('bdNoticeCount', 'i'); // 노출 개수
        $validator->add('bdListInNotice', 'yn'); // 리스트 내 노출
        $validator->add('bdOnlyMainNotice', 'yn'); // 첫페이지만 노출
        $validator->add('bdGoodsPageCountPc', 'number'); // 상품상세 페이지내 페이지별 게시물 수 - PC
        $validator->add('bdGoodsPageCountMobile', 'number'); // 상품상세 페이지내 페이지별 게시물 수 - MOBILE
        $validator->add('bdIncludeReplayInSearchFl', 'yn'); // 검색 시 답변글 노출여부
        $validator->add('bdIncludeReplayInSearchType', 'i'); // 검색 시 답변글 노출여부
        $validator->add('bdListNoticeImageDisplayPc', 'yn'); // 공지글 이미지 노출 여부
        $validator->add('bdListNoticeImageDisplayMobile', 'yn'); // 공지글 이미지 노출 여부

        $validator->add('bdReviewAuthWrite', 's'); // 쓰기권한 추가기준
        $validator->add('bdReviewOrderStatus', 's'); // 작성가능 시점
        $validator->add('bdReviewPeriod', 'i'); // 후기작성기간
        $validator->add('bdReviewDuplicateLimit', 's'); // 중복작성 제한
        $validator->add('bdReviewExceptGoodsNo', 's'); // 후기작성 예외상품

        $validator->add('seoTagFl', 's'); // 후기작성 예외상품
        $validator->add('seoTag', ''); // 후기작성 예외상품
        $validator->add('seoTagSno', 'i'); // 후기작성 예외상품
        $validator->add('bdAnswerStatusFl', ''); // 답변관리 기능 사용여부
        $validator->add('bdHitIPCheck', ''); // 조회수 IP 중복제한
        $validator->add('bdReplyDelFl', 's'); // 게시글 삭제 설정
        $validator->add('bdReplyMileageFl', ''); // 답변글 작성 시에도 마일리지 지급

        $validator->add('bdPasswordMemoFl', 'n'); // 비밀댓글 암호보안 여부

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }
    }

    /**
     * 아이디중복확인
     * @param $bdId 게시판아이디
     * @return bool
     * @throws \Exception
     */
    public function overlapBdId($bdId)
    {
        // Validation
        if (Validator::required($bdId) === false) {
            throw new \Exception('형식 애러');
        }

        $strSQL = 'SELECT bdId FROM ' . DB_BOARD . ' WHERE bdId=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['bdId'], $bdId);
        $this->db->query_fetch($strSQL, $arrBind);

        if ($this->db->num_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * multicheckbox value 조정
     * @param $param Array 파라미터
     * @return value
     */
    private function sumMultiCheckValue($param)
    {
        if (gd_isset($param) && is_array($param)) {
            $value = 0;
            foreach ($param as $val) {
                $value += $val;
            }
            unset($param);
            return $value;
        }
        return 0;
    }

    /**
     * Captcha 색상 가져오기
     * @param $sno 게시판번호
     * @return value
     */
    public function getCaptchaColor($sno)
    {
        try {
            if (Validator::number($sno, null, null, true) === false) {
                throw new \Exception(sprintf(__('%1$s 인자가 잘못되었습니다.'), '일련번호'));
            }

            $this->db->strField = "bdCaptchaBgClr, bdCaptchaClr";
            $this->db->strWhere = "sno=?";
            $this->db->bind_param_push($arrBind, 'i', $sno);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            return gd_htmlspecialchars_stripslashes($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Captcha 색상 수정하기
     * @param $arrData Array 파라미터
     * @return value
     * @throws \Exception
     */
    public function modifyCaptchaColor($arrData)
    {
        try {
            // Validation
            $validator = new Validator();
            $validator->add('sno', 'number', true); // Captcha 배경 색상
            $validator->add('bdCaptchaBgClr', ''); // Captcha 배경 색상
            $validator->add('bdCaptchaClr', ''); // Captcha 글자 색상

            if ($validator->act($arrData, true) === false) {
                throw new \Exception(implode("\n", $validator->errors));
            }

            // 저장
            $arrBind = $this->db->get_binding(DBTableField::tableBoard(), $arrData, 'update', Array('bdCaptchaBgClr', 'bdCaptchaClr'));
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            $this->db->set_update_db(DB_BOARD, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 파일이름생성(확장자는 동일하게 사용하고 파일이름만 변경)
     * @param string $filename 소스파일이름
     * @return string 변경된 파일이름
     */
    private function setIconFileName($srcFile, $newFile)
    {
        $tmpPaths = explode('.', $srcFile);
        if (count($tmpPaths) == 1) {
            return $newFile;
        } else {
            return $newFile . '.' . $tmpPaths[count($tmpPaths) - 1];
        }
    }

    /**
     * 기본으로 제공되는 게시판 정보 조회
     *
     * @param string $column
     *
     * @param null $where
     *
     * @return mixed
     */
    public static function getBasicBoard($column = '*', $where = null)
    {
        $db = App::load('DB');
        $strSQL = 'SELECT ' . $column . ' FROM ' . DB_BOARD . ' WHERE bdBasicFl=\'y\'';
        if ($where != null) {
            $strSQL .= $where;
        }
        $result = $db->query_fetch($strSQL, null, false);
        return $result;
    }

    public function migrationReview($data = null){
        if(!$data){
            $data = BoardUtil::getData(Board::BASIC_GOODS_REIVEW_ID);
        }
        else {
            if($data['bdId'] !== Board::BASIC_GOODS_REIVEW_ID){
                return;
            }
        }

        if($data['bdReviewAuthWrite']) {
            return;
        }
        $upData = [];
        if($data['bdAuthWrite'] == 'buyer') {   //쓰기권한이 `구매자`면
            $upData['bdAuthWrite'] = 'member';  //쓰기권한 `회원`
            $upData['bdReviewAuthWrite'] = 'buyer'; //쓰기권한 추가기준 : 구매내역이 존재하는경우에만 작성가능
            $upData['bdReviewOrderStatus'] = 's1'; //작성가능시점 구매확정
            $upData['bdReviewPeriod'] = '0'; //작성가능기간
            $upData['bdReviewDuplicateLimit'] = 'free'; //중복작성제한
        }
        else {
            $upData['bdReviewAuthWrite'] = 'all'; //쓰기권한 추가기준 : 구매 여부와 상관없이 후기 작성 가능
        }

        $arrBind = $this->db->get_binding(DBTableField::tableBoard(), $upData, 'update', array_keys($upData));
        $this->db->bind_param_push($arrBind['bind'], 's', Board::BASIC_GOODS_REIVEW_ID);
        $affectedRows = $this->db->set_update_db(DB_BOARD, $arrBind['param'], 'bdId = ?', $arrBind['bind']);
        return $affectedRows;
    }

    public function migrationReviewTest($isBuyer = true){
        //쓰기권한이 구매자인경우
        $upData['bdReviewAuthWrite'] = '';
        $upData['bdReviewOrderStatus'] = '';
        $upData['bdReviewPeriod'] = 0;
        $upData['bdReviewDuplicateLimit'] = '';
        $upData['bdReviewExceptGoodsNo'] = '';

        $arrBind = $this->db->get_binding(DBTableField::tableBoard(), $upData, 'update', array_keys($upData));
        $this->db->bind_param_push($arrBind['bind'], 's', Board::BASIC_GOODS_REIVEW_ID);
        $affectedRows = $this->db->set_update_db(DB_BOARD, $arrBind['param'], 'bdId = ?', $arrBind['bind']);
        $upData = $arrBind = null;
        if($isBuyer){
            $upData['bdAuthWrite'] = 'buyer';
            $arrBind = $this->db->get_binding(DBTableField::tableBoard(), $upData, 'update', array_keys($upData));
            $this->db->bind_param_push($arrBind['bind'], 's', Board::BASIC_GOODS_REIVEW_ID);
            $affectedRows = $this->db->set_update_db(DB_BOARD, $arrBind['param'], 'bdId = ?', $arrBind['bind']);
        }
        else {
            $upData['bdAuthWrite'] = 'member';
            $arrBind = $this->db->get_binding(DBTableField::tableBoard(), $upData, 'update', array_keys($upData));
            $this->db->bind_param_push($arrBind['bind'], 's', Board::BASIC_GOODS_REIVEW_ID);
            $affectedRows = $this->db->set_update_db(DB_BOARD, $arrBind['param'], 'bdId = ?', $arrBind['bind']);
        }

        $result = $this->migrationReview();


        return $result;
    }

    public function userBoardChk($fl)
    {
        return $fl;
    }

    /**
     * 상품 후기 게시판 데이터 조회
     *
     * @param array $getValue
     * @param integer $minReviewNo
     * @param integer $maxReviewNo
     *
     * @return array
     * @throws
     */
    public function getReviewListGenerator($getValue, $minReviewNo = null, $maxReviewNo = null)
    {
        $tableName = DB_BD_ . Board::BASIC_GOODS_REIVEW_ID . ' gr ';

        if ($getValue['mode'] == 'crema') {
            // 삭제 리뷰 제외
            $arrWhere[] = 'gr.isDelete = ?';
            $this->db->bind_param_push($arrBind, 's', 'n');
            // 상품번호 0 제외
            $arrWhere[] = 'gr.goodsNo != ?';
            $this->db->bind_param_push($arrBind, 'i', 0);
        }

        if (is_null($minReviewNo) === false && is_null($maxReviewNo) === false) {
            // between 조회
            $arrWhere[] = '(gr.sno BETWEEN ? AND ?)';
            $this->db->bind_param_push($arrBind, 'i', $minReviewNo);
            $this->db->bind_param_push($arrBind, 'i', $maxReviewNo);
        }

        // regDt 조건
        if (empty($getValue['regDt']) === false) {
            $arrWhere[] = 'gr.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', $getValue['regDt'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $getValue['regDt'] . ' 23:59:59');
        }

        // sno 조건
        if (empty($getValue['sno']) === false) {
            if (is_array($getValue['sno'])) {
                $tmpAddWhere = [];
                foreach ($getValue['sno'] as $val) {
                    $tmpAddWhere[] = '?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                $arrWhere[] = 'gr.sno IN (' . implode(' , ', $tmpAddWhere) . ')';
                unset($tmpAddWhere);
            } else {
                $arrWhere[] = 'gr.sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $getValue['sno']);
            }
        }

        $this->db->strField = implode(',', $getValue['strField']);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'gr.sno desc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . implode(' ', $query);
        if ($getValue['generatorFl'] === false) {
            $result = $this->db->query_fetch($strSQL, $arrBind);
        } else {
            $result = $this->db->query_fetch_generator($strSQL, $arrBind);
        }

        return $result;
    }

    /**
     * 댓글가져오기
     *
     * @param int $sno 글번호
     *
     * @return array
     */
    public function getDefaultMemo($sno)
    {
        $buildQuery = BoardBuildQuery::init(Board::BASIC_GOODS_REIVEW_ID);
        $resMemo = $buildQuery->selectMemoList($sno);
        return $resMemo;
    }

    /**
     * 회원이 작성한 모든 게시물 개수 가져오기
     *
     * @param $memSno 회원번호
     * @return array
     */
    public function getBoardCntByMemberSno($memSno)
    {
        $db = App::load('DB');
        $resultCount = $arrBind = [];
        $strSQL = 'SELECT bdId FROM ' . DB_BOARD ;
        $boardList = $db->query_fetch($strSQL, null, false);

        $this->db->strField = "COUNT(*) as cnt";
        $this->db->strWhere = 'memNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $memSno);
        $query = $this->db->query_complete();
        foreach($boardList as $key) {
            $getBoardCntSQL = 'SELECT '. $query['field'] .' FROM es_bd_'. $key['bdId'] . $query['where'];
            $result = $db->query_fetch($getBoardCntSQL, $arrBind, false);
            $resultCount[$key['bdId']] = $result['cnt'];
        }
       return $resultCount;
    }

    /**
     * 날짜 기준 메모 리스트 가져오기(크리마 연동)
     *
     * @param $getValue array
     * @return object|null
     */
    public function getMemoList($getValue)
    {
        $arrBind = [];

        // 크리마 연동일 경우 리뷰 게시판의 댓글만 가져오기
        if($getValue['mode'] == 'crema') {
            $arrWhere[] = 'bm.bdId = ? ';
            $this->db->bind_param_push($arrBind, 's', Board::BASIC_GOODS_REIVEW_ID);
        }

        // 삭제 리뷰 제외
        $arrWhere[] = 'bg.isDelete = ?';
        $this->db->bind_param_push($arrBind, 's', 'n');

        // 비회원 댓글 등록 불가
        $arrWhere[] = 'bm.writerId <> \'\'';

        // regDt 조건
        if (empty($getValue['regDt']) === false) {
            $arrWhere[] = 'bm.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', $getValue['regDt'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $getValue['regDt'] . ' 23:59:59');
        }

        $this->db->strField = implode(',', $getValue['strField']);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strJoin = 'LEFT JOIN es_bd_goodsreview AS bg ON bg.sno = bm.bdSno';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD_MEMO . ' as bm ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind);

        return $result;
    }

}
