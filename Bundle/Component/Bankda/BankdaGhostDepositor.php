<?php

namespace Bundle\Component\Bankda;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertCloseException;
use Component\Storage\Storage;
use Request;
use App;
use UserFilePath;

/**
 * 미확인 입금자 리스트 관리
 *
 * @author  cjb3333
 * @copyright ⓒ 2016, NHN godo: Corp.

 */

class BankdaGhostDepositor
{

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 미확인 입금자 디비 저장
     * @param array $arrData 미확인 입금자 배열
     */
    public function registerGhostDepositor($arrData)   {

        // 코드 저장
        $insertData['depositDate'] = isset($arrData['depositDate']) ? $arrData['depositDate'] : '';
        $insertData['ghostDepositor'] = isset($arrData['ghostDepositor']) ? $arrData['ghostDepositor'] : '';
        $insertData['bankName'] = isset($arrData['bankName']) ? $arrData['bankName'] : '';
        $insertData['depositPrice'] = isset($arrData['depositPrice']) ? $arrData['depositPrice'] : '';
        $arrBind = $this->db->get_binding(DBTableField::tableGhostDepositor(), $insertData, 'insert');
        $this->db->set_insert_db(DB_GHOST_DEPOSITOR, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 미확인 입금자 디비 삭제
     *
     * @param integer $sno 고유키
     */
    public function deleteGhostDepositor($sno)
    {

        // 상태 변경
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno); // 추가 bind 데이터
        $this->db->set_delete_db(DB_GHOST_DEPOSITOR, 'sno = ?', $arrBind);
    }

    /**
     * 미확인 입금자 리스트 관리 설정값 디비 저장
     *
     * @param array $cfgGhostDepositor 배열
     */
    public function configGhostDepositor($cfgGhostDepositor)
    {

        $setGhostDepositor = array();
        $setGhostDepositor['use'] = isset($cfgGhostDepositor['use']) ? $cfgGhostDepositor['use'] : '';
        $setGhostDepositor['expire'] = isset($cfgGhostDepositor['expire']) ? $cfgGhostDepositor['expire'] : '';
        $setGhostDepositor['hideBank'] = isset($cfgGhostDepositor['hideBank']) ? $cfgGhostDepositor['hideBank'] : '';
        $setGhostDepositor['hideMoney'] = isset($cfgGhostDepositor['hideMoney']) ? $cfgGhostDepositor['hideMoney'] : '';
        $setGhostDepositor['bankdaUse'] = isset($cfgGhostDepositor['bankdaUse']) ? $cfgGhostDepositor['bankdaUse'] : '';
        $setGhostDepositor['bankdaLimit'] = isset($cfgGhostDepositor['bankdaLimit']) ? $cfgGhostDepositor['bankdaLimit'] : '';
        $setGhostDepositor['bannerSkin'] = isset($cfgGhostDepositor['bannerSkin']) ? $cfgGhostDepositor['bannerSkin'] : '';
        $setGhostDepositor['designSkin'] = isset($cfgGhostDepositor['designSkin']) ? $cfgGhostDepositor['designSkin'] : '';
        $setGhostDepositor['bannerSkinType'] = isset($cfgGhostDepositor['bannerSkinType']) ? $cfgGhostDepositor['bannerSkinType'] : '';    // FILE
        $setGhostDepositor['designSkinType'] = isset($cfgGhostDepositor['designSkinType']) ? $cfgGhostDepositor['designSkinType'] : '';    // FILE
        $setGhostDepositor['mobileDesignSkin'] = isset($cfgGhostDepositor['mobileDesignSkin']) ? $cfgGhostDepositor['mobileDesignSkin'] : '';
        $setGhostDepositor['mobileBannerSkin'] = isset($cfgGhostDepositor['mobileBannerSkin']) ? $cfgGhostDepositor['mobileBannerSkin'] : '';
        $setGhostDepositor['mobileBannerFile'] = isset($cfgGhostDepositor['mobileBannerFile']) ? $cfgGhostDepositor['mobileBannerFile'] : '';
        $setGhostDepositor['mobileBannerSkinType'] = isset($cfgGhostDepositor['mobileBannerSkinType']) ? $cfgGhostDepositor['mobileBannerSkinType'] : '';    // FILE
        $setGhostDepositor['mobileDesignSkinType'] = isset($cfgGhostDepositor['mobileDesignSkinType']) ? $cfgGhostDepositor['mobileDesignSkinType'] : '';    // FILE

        // PC 배너 저장경로
        $ghostDepositorBannerPath = 'ghostdepositorbanner_'.date("YmdHis");
        // PC 배너 삭제
        if (isset($cfgGhostDepositor['bannerFileDelete']) && empty($cfgGhostDepositor['bannerFileTmp']) === false) {
            Storage::disk(Storage::PATH_CODE_GHOST_DEPOSITOR_BANNER, 'local')->delete($cfgGhostDepositor['bannerFileTmp']);
            $cfgGhostDepositor['bannerFileTmp'] = '';
            $setGhostDepositor['bannerFile'] = '';
        }

        // PC 배너 업로드
        if (gd_file_uploadable(Request::files()->get('bannerFile'), 'image') === true) {
            // 이미지 저장
            $tmpImageFile = Request::files()->get('bannerFile.tmp_name');
            list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
            Storage::disk(Storage::PATH_CODE_GHOST_DEPOSITOR_BANNER, 'local')->upload($tmpImageFile, $ghostDepositorBannerPath);
            $setGhostDepositor['bannerFile'] = $ghostDepositorBannerPath;
        } else {
            if (empty($cfgGhostDepositor['bannerFileTmp']) === false) {
                $setGhostDepositor['bannerFile'] = $cfgGhostDepositor['bannerFileTmp'];
            }
        }

        // PC 커스텀 스킨
        /** @var \Bundle\Component\File\SafeFile $safe */
        if ($cfgGhostDepositor['designHtml'] && $cfgGhostDepositor['designSkinType'] == 'direct') {
            $skinUrl = UserFilePath::data('ghost_depositor', 'tpl', 'custom.html');
            $safe = \App::load('\\Component\\File\\SafeFile');

            $skinDir = UserFilePath::data('ghost_depositor', 'tpl');
            if (!file_exists($skinDir)) {
                @mkdir($skinDir, 0757, true);
                @chmod($skinDir, 0757);
            }

            //@chmod($dir, 0757);
            $safe->open($skinUrl);
            $safe->write($cfgGhostDepositor['designHtml']);
            $safe->close();
            @chmod($skinUrl, 0707);
        }

        // mobile 배너 저장경로
        $mobileGhostDepositorBannerPath = 'mobileGhostdepositorbanner_'.date("YmdHis");
        // 모바일 배너 삭제
        if (isset($cfgGhostDepositor['mobileBannerFileDelete']) && empty($cfgGhostDepositor['mobileBannerFileTmp']) === false) {
            Storage::disk(Storage::PATH_CODE_GHOST_DEPOSITOR_BANNER, 'local')->delete($cfgGhostDepositor['mobileBannerFileTmp']);
            $cfgGhostDepositor['mobileBannerFileTmp'] = '';
            $setGhostDepositor['mobileBannerFile'] = '';
        }

        // 모바일 배너 업로드
        if (gd_file_uploadable(Request::files()->get('mobileBannerFile'), 'image') === true) {
            // 이미지 저장
            $tmpImageFile = Request::files()->get('mobileBannerFile.tmp_name');
            list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
            Storage::disk(Storage::PATH_CODE_GHOST_DEPOSITOR_BANNER, 'local')->upload($tmpImageFile, $mobileGhostDepositorBannerPath);
            $setGhostDepositor['mobileBannerFile'] = $mobileGhostDepositorBannerPath;
        } else {
            if (empty($cfgGhostDepositor['mobileBannerFileTmp']) === false) {
                $setGhostDepositor['mobileBannerFile'] = $cfgGhostDepositor['mobileBannerFileTmp'];
            }
        }

        // 모바일 커스텀 스킨
        /** @var \Bundle\Component\File\SafeFile $safe */
        if ($cfgGhostDepositor['mobileDesignHtml']) {
            $skinUrl = UserFilePath::data('ghost_depositor', 'tpl', 'mobileCustom.html');
            $safe = \App::load('\\Component\\File\\SafeFile');

            $skinDir = UserFilePath::data('ghost_depositor', 'tpl');
            if (!file_exists($skinDir)) {
                @mkdir($skinDir, 0757, true);
                @chmod($skinDir, 0757);
            }

            //@chmod($dir, 0757);
            $safe->open($skinUrl);
            $safe->write($cfgGhostDepositor['mobileDesignHtml']);
            $safe->close();
            @chmod($skinUrl, 0707);
        }

        gd_set_policy('order.ghostDepositor', $setGhostDepositor, false);

        return true;

    }

    /**
     * 미확인 입금자 리스트 로딩
     *
     * @param array $search 검색파라미터
     */
    public function loadGhostDepositor($search = null)
    {
        $this->arrWhere[] = " 1 ";
        $fieldType = DBTableField::getFieldTypes('tableGhostDepositor');

        $search['keyword'] = urldecode($search['keyword']);
        // 키워드 검색
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] == 'all') {
                $tmpWhere = ['bankName', 'ghostDepositor', 'depositPrice'];
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldType[$keyNm], $search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $this->arrWhere[] = $search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType[$search['key']], $search['keyword']);
            }
        }
        $this->db->bindParameterByDateRange('depositDate', $search, $this->arrBind, $this->arrWhere, 'tableGhostDepositor');

        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "depositDate asc";


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GHOST_DEPOSITOR . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['depositPrice'] = number_format($data[$i]['depositPrice']);
        }

        // 출력
        $rs['result'] = true;
        $rs['body'] = $data;
        $rs['page'] = array(
            'total'=>count($data)
        );
        //debug($rs);exit;
        return $rs;

    }

    /**
     * 미확인 입금자 팝업 리스트 (미확인 로컬 디비 리스트)
     * @param array $cfgGhostDepositor 미확인입금자 설정배열
     * @param array $arrData 검색파라미터
     */
    public function ghostDepositorDbList($cfgGhostDepositor,$arrData)
    {
        $this->arrWhere[] = " 1 ";

        $this->arrWhere[] = 'depositDate >= ?';
        $this->db->bind_param_push($this->arrBind, 's', date('Ymd',strtotime('-'.$cfgGhostDepositor['expire'].' day')));

        $fieldType = DBTableField::getFieldTypes('tableGhostDepositor');

        //검색
        if ($arrData['ghostDepositor']) {
            $this->arrWhere[] = 'ghostDepositor LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, 's', $arrData['ghostDepositor']);
        }

        if ($arrData['depositDate']) {
            $this->arrWhere[] = 'depositDate = ?';
            $this->db->bind_param_push($this->arrBind, 's', $arrData['depositDate']);
        }

        $countArrbind = $this->arrBind;

        // --- 페이지 설정
        $nowPage = gd_isset($arrData['page']);
        $pageNum = gd_isset($arrData['pageNum']);
        if ($pageNum == '') {
            $pageNum = '5';
        }

        $page = App::load('Component\\Page\\Page', $nowPage, 0, 0, $pageNum);

        $start = $page->recode['start'];
        $limit = $page->page['list'];

        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "depositDate desc";


        if (is_null($start) === false && is_null($limit) === false) {
            $this->db->strLimit = '?,?';
            $this->db->bind_param_push($this->arrBind, 'i', $start);
            $this->db->bind_param_push($this->arrBind, 'i', $limit);
        }

        $arrQuery = $this->db->query_complete();


        $strSQL = 'SELECT  ' . array_shift($arrQuery) . ' FROM ' . DB_GHOST_DEPOSITOR . ' ' . implode(' ', $arrQuery);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['depositPrice'] = number_format($data[$i]['depositPrice']);
        }

        unset($arrQuery['order']);
        unset($arrQuery['limit']);
        //검색개수
        $strSQL = 'SELECT count(*) as cnt  FROM ' . DB_GHOST_DEPOSITOR . ' ' . implode(' ', $arrQuery);
        $total = $this->db->query_fetch($strSQL, $countArrbind, false)['cnt'];

        $page->recode['total'] = $total; // 검색 레코드 수
        $page->recode['amount'] = $total; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        for($i=0;$i<count($data);$i++) $data[$i]['no'] = $page->idx--;

        return $data;
    }

    /**
     * 미확인 입금자 팝업 리스트 (뱅크다 중계서버 디비 리스트)
     * @param array $cfgGhostDepositor 미확인입금자 설정배열
     * @param array $arrData 검색파라미터
     */
    public function bankdaDbList($cfgGhostDepositor,$arrData)
    {
        $bankda = App::load('\\Component\\Bankda\\Bankda');
        $bankdaSetInfo = $bankda -> getBankdaSetInfo();

        // Create Query
        $arrData['ghostDepositor'] = iconv('utf-8', 'euc-kr', gd_isset($arrData['ghostDepositor']));
        $pageNum = gd_isset($arrData['pageNum']);
        if ($pageNum == '') {
            $pageNum = '5';
        }

        $query = '';
        $query .= '&belowprice='.$cfgGhostDepositor['bankdaLimit']; // 이하가격
        $query .= '&term='.$cfgGhostDepositor['expire']; // 노출기간(3,7,14,30,60)
        $query .= '&page='.$arrData['page']; // 페이지번호
        $query .= '&page_num='.$pageNum; // 페이지리스팅수


        if ($arrData['depositDate'] != '')
            $query .= '&date='.str_replace("-","",$arrData['depositDate']); // 입금일자

        if ($arrData['ghostDepositor'] != '')
            $query .= '&name='.urlencode($arrData['ghostDepositor']); // 입금자


        /***************************************************************************************************
         *  hashdata 생성
         *    - 데이터 무결성을 검증하는 데이터로 요청시 필수 항목.
         *    - MID 를 조합한후 md5 방식으로 생성한 해쉬값.
         ***************************************************************************************************/
        $MID		= $bankdaSetInfo['MID'];	# 상점아이디

        $hashdata	= md5($MID);						# hashdata 생성

        $json = $bankda->readurl("http://bankmatch.godo.co.kr/sock_listing_unconfirm.php?MID={$MID}&{$query}&hashdata={$hashdata}");
        $json = iconv('euc-kr', 'utf-8', $json);
        unset($query, $hashdata, $MID);

        if ( !preg_match("/^false[ |]*/i",$json) ) { // 성공

            // inline json_decode... 훗;
            $eval_json = array();

            $_quoted = false;
            $_out = '$eval_json=';

            for ($i=0,$max=strlen($json); $i<$max; $i++) {

                if (! $_quoted) {
                    if ($json[$i] == '{')		$_out .= ' array(';
                    else if ($json[$i] == '}')	$_out .= ')';
                    else if ($json[$i] == ':')	$_out .= '=>';
                    else if ($json[$i] == '[')	$_out .= ' array(';
                    else if ($json[$i] == ']')	$_out .= ')';
                    else $_out .= $json[$i];
                }
                else $_out .= $json[$i];

                if ($json[$i] == '"') $_quoted = ! $_quoted;
            }

            @eval($_out.';');

            if (!empty($eval_json)) {

                $_page = & $eval_json['page'];

                /*
                recode	검색 레코드 수
                total	총 페이지 수
                now	현 페이지 번호
                */
                $phpSelf = gd_php_self();
                $_page['current'] = $arrData['page'];
                if ($_page['total'] && $_page['current']>$_page['total']) $_page['current'] = $_page['total'];
                $_page['start']		= (ceil($_page['current']/10)-1)*10;

                $param = '?date='.$arrData['date'].'&name='.urlencode($arrData['name']);

                if($_page['current']>10){
                    $paging .= '

                            <li class="disabled">
                                <a aria-label="Previous" href="'.$phpSelf.$param.'&page='.$_page['start'].'" ><span aria-hidden="true">«</span></a>
                            </li>
                            ';
                }

                $i=0;
                while($i+$_page['start']<$_page['total']&&$i<10){
                    $i++;
                    $page[move] = $i+$_page['start'];
                    $paging .= ($_page['current']==$page[move]) ? " <li class=\"active\"><a href='#'>$page[move]</a></li> " : " <li><a href=\"{$phpSelf}{$param}&page=$page[move]\" >$page[move]</a></li> ";
                }

                if($_page['total']>$page[move]){
                    $page[next] = $page[move]+1;
                    $paging .= "
                                <li>
                                <a aria-label=\"Next\" href=\"{$phpSelf}{$param}&page=$page[next]\"><span aria-hidden=\"true\">»</span> </a>
                                </li>
					        ";
                }

                // 리스트
                $_row = array();
                $no = sizeof($eval_json['lists']);
                foreach( $eval_json['lists'] as $row ) {

                    $_row['depositPrice'] = $row['price'];
                    $_row['bankName'] = $row['bkname'];
                    $_row['ghostDepositor'] = $row['name'];
                    $_row['depositDate'] = $row['date'];
                    $_row['no'] = $row['no'];

                    $loop[] = $_row;

                }

            }

            return array("loop"=>$loop,"paging"=>$paging,"total"=>$_page['total']);

        }else{

            $out = preg_replace("/^false[ |]*-[ |]*/i", "", $json);
            throw new AlertCloseException('사용할 수 없습니다.');
            exit;

        }

    }

    /**
     * 입금은행, 입금액 숨김처리
     * @param array $cfgGhostDepositor 미확인입금자 설정배열
     * @param array $loop 미확인 입금자 데이터 배열
     */
    public function setHideDataProc($cfgGhostDepositor,$loop = [])
    {
        if(count($loop)<1) return $loop;

        foreach ($loop as $k => $row) {

            if ($cfgGhostDepositor['hideBank']) {
                if (($_pos = strpos($row['bankName'], '은행')) !== false) $row['bankName'] = substr_replace($row['bankName'], sprintf("%'*" . $_pos . "s", '*'), 0, $_pos);
                elseif (($_pos = strpos($row['bankName'], '협')) !== false) $row['bankName'] = substr_replace($row['bankName'], sprintf("%'*" . $_pos . "s", '*'), 0, $_pos);
                elseif (($_pos = strpos($row['bankName'], '금고')) !== false) $row['bankName'] = substr_replace($row['bankName'], sprintf("%'*" . $_pos . "s", '*'), 0, $_pos);
                elseif (($_pos = strpos($row['bankName'], '뱅크')) !== false) $row['bankName'] = substr_replace($row['bankName'], sprintf("%'*" . $_pos . "s", '*'), 0, $_pos);
                else  $row['bankName'] = sprintf("%'*" . (strlen($row['bankName'])) . "s", '*');
            }

            // 입금은행이 없는 경우 빈값 처리 (입금은행이 없을 때 * 로 출력됨)
            if($row['bankName'] == '*') $row['bankName'] = '';

            $row['depositPrice'] = number_format(str_replace(",", "", $row['depositPrice']));

            if ($cfgGhostDepositor['hideMoney']) {

                for ($i = 0; $i < strlen($row['depositPrice']); $i++) {
                    if ($i == 0) {
                        $temp = $row['depositPrice'][$i];
                    } else {
                        if ($row['depositPrice'][$i] == ',') {
                            $temp .= ',';
                        } else {
                            $temp .= '*';
                        }
                    }
                }

                $row['depositPrice'] = $temp;
            }

            $loop[$k] = $row;
        }
        return $loop;
    }
    /**
     * 미확인 입금자 설정값
     * @return array $cfgGhostDepositor 미확인 입금자 설정값 배열
     */
    public function getGhostDepositorPolicy()
    {
        $cfgGhostDepositor = gd_policy('order.ghostDepositor');

        if (empty($cfgGhostDepositor)) {

            $cfgGhostDepositor['use'] = 0;
            $cfgGhostDepositor['expire'] = 3;
            $cfgGhostDepositor['hideBank'] = 0;
            $cfgGhostDepositor['hideMoney'] = 0;
            $cfgGhostDepositor['bankdaUse'] = 0;
            $cfgGhostDepositor['bankdaLimit'] = '';
            $cfgGhostDepositor['designSkin'] = 1;
            $cfgGhostDepositor['designHtml'] = '';
            $cfgGhostDepositor['bannerSkin'] = 2;
            $cfgGhostDepositor['bannerFile'] = '';
            $cfgGhostDepositor['bannerSkinType'] = 'select';
            $cfgGhostDepositor['designSkinType'] = 'select';

        }

        if(empty($cfgGhostDepositor['mobileDesignSkin'])) {
            $cfgGhostDepositor['mobileDesignSkin'] = 1;
            $cfgGhostDepositor['mobileDesignHtml'] = '';
            $cfgGhostDepositor['mobileBannerSkin'] = 1;
            $cfgGhostDepositor['mobileBannerFile'] = '';
            $cfgGhostDepositor['mobileBannerSkinType'] = 'select';
            $cfgGhostDepositor['mobileDesignSkinType'] = 'select';
        }

        if (is_file( UserFilePath::data('ghost_depositor','tpl','custom.html' ))) {
            $cfgGhostDepositor['designHtml'] = file_get_contents(UserFilePath::data('ghost_depositor','tpl','custom.html'));
        }
        if (is_file( UserFilePath::data('ghost_depositor','tpl','mobileCustom.html' ))) {
            $cfgGhostDepositor['mobileDesignHtml'] = file_get_contents(UserFilePath::data('ghost_depositor','tpl','mobileCustom.html'));
        }

        return $cfgGhostDepositor;
    }

    public function downloadGhostDepositor($arrData){

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=bankda_ghost_depositor_".date("YmdHi").".xls");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<table border=1>';
        // __('번호')
        // __('입금일자')
        // __('고객명')
        // __('은행')
        // __('입금액')
        echo '
			<tr>
				<td>번호</td>
				<td>입금일자</td>
				<td>고객명</td>
				<td>은행</td>
				<td>입금액</td>
			</tr>
			';

        $i=0;
        foreach($arrData as $k => $row) {
            echo '
				<tr>
					<td>'.++$i.'</td>
					<td>'.$row['depositDate'].'</td>
					<td>'.$row['ghostDepositor'].'</td>
					<td>'.$row['bankName'].'</td>
					<td>'.$row['depositPrice'].'</td>
				</tr>
				';
        }
        echo '</table>';
        exit;

    }

}

