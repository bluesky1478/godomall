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
 * @link http://www.godo.co.kr
 */
namespace Bundle\Component\Promotion;

use App;
use Request;
use Exception;
use Session;
use FileHandler;
use Component\AbstractComponent;
use Component\Database\DBTableField;
use Component\Member\Util\MemberUtil;
use Component\Storage\Storage;
use Component\Mileage\Mileage;
use Framework\Database\DBTool;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\StaticProxy\Proxy\UserFilePath;


/**
 * Class Poll
 * @package Bundle\Component\Promotion
 * @author  Bagyj
 */
class Poll extends \Component\AbstractComponent
{
    protected $db;
    protected $usePlusShop = true;
    public $cateCd;
    public $defaultBannerImg = 'poll-banner.png';
    public $defaultBannerImgMobile = 'poll-banner-mobile.png';
    public $viewPosition;
    public $device;
    public $bannerImagePath;

    public $shortLen = '50';
    public $descriptLen = '500';
    public $questionCount = '20';
    public $answerCount = '10';

    private $deviceFl = [
        'pc' => 'PC쇼핑몰',
        'mobile' => '모바일쇼핑몰',
        'all' => 'PC+모바일',
    ];
    private $groupFl = [
        'all' => '회원+비회원',
        'member' => '회원',
        'select' => '특정회원',
    ];
    private $statusFl = [
        'S' => '대기',
        'Y' => '진행중',
        'N' => '일시중지',
        'E' => '종료',
    ];
    private $reverseStatusFl = [
        'Y' => '일시중지',
        'N' => '재시작',
    ];
    private $reverseStatusKey = [
        'Y' => 'N',
        'N' => 'Y',
    ];

    /**
     * 생성자
     */
    public function __construct()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_POLL) === false) {
            throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.'));
        }
        if ($db === null) {
            $db = App::load('DB');
        }
        $this->db = $db;
        $this->cateCd = Request::get()->get('cateCd');

        $controller = App::getController();
        $bannerDeviceType = $controller->getRootDirecotory() == 'admin' ? 'front' : $controller->getRootDirecotory();

        $this->bannerImagePath = UserFilePath::data('poll')->www();

        switch ($controller->getPageName()) {
            case "index":
            case "main/index":
                $this->viewPosition = 'main';
                break;
            default:
                $this->viewPosition = 'category';
                break;
        }

        if ($bannerDeviceType === 'front') {
            $this->device = 'pc';
        } else {
            $this->device = 'mobile';
        }
    }

    public function getObject($obj)
    {
        return $this->$obj;
    }

    public function viewPollBanner($code = null)
    {
        try{
            $getData = $this->getPollData($code, null, ['pollCode', 'pollTitle', 'pollStatus', 'pollMemberLimitFl', 'pollMemberLimitCnt', 'pollBannerLimitFl', 'pollBannerFl', 'pollBannerImg', 'pollBannerImgMobile', 'pollViewPosition', 'pollViewCategory'], true);

            foreach ($getData as $k => $v) {
                if ($v['pollBannerFl'] == 'none') {
                    unset($getData[$k]);
                    continue;
                }

                // 참여 인원 제한 배너 미노출
                if ($v['pollMemberLimitFl'] == 'max' && $v['pollMemberLimitCnt'] <= $this->getPollCnt($v['pollCode']) && $v['pollBannerLimitFl'] == 'Y') {
                    unset($getData[$k]);
                    continue;
                }

                $ViewPosition = explode(',' , $v['pollViewPosition']);
                if (empty($code) === true && in_array($this->viewPosition, $ViewPosition) === false) {
                    unset($getData[$k]);
                    continue;
                }

                if (!$code && $this->viewPosition == 'category') {
                    if ($v['pollViewCategory'] != 'all') {
                        $viewCategory = explode(STR_DIVISION , $v['pollViewCategory']);
                        if (!in_array($this->cateCd, $viewCategory)) {
                            unset($getData[$k]);
                            continue;
                        }
                    }
                }

                if ($v['pollBannerFl'] == 'def') {
                    if ($this->device == 'pc') {
                        $getData[$k]['pollBannerImg'] = $this->bannerImagePath . '/' . $this->defaultBannerImg;
                    } else {
                        $getData[$k]['pollBannerImg'] = $this->bannerImagePath . '/' . $this->defaultBannerImgMobile;
                    }
                } else {
                    if ($this->device == 'pc') {
                        $getData[$k]['pollBannerImg'] = $this->bannerImagePath . '/' . $v['pollBannerImg'];
                    } else {
                        $getData[$k]['pollBannerImg'] = $this->bannerImagePath . '/' . $v['pollBannerImgMobile'];
                    }
                }
            }
        } catch (AlertBackException $e) {
            return;
        }

        return $getData;
    }

    public function getPollData($code = null, $sno = null, $arrInclude = null, $returnArray = false)
    {
        $arrField = DBTableField::setTableField('tablePoll',$arrInclude);
        $arrBind = $arrWhere = [];

        if (isset($code)) {
            $arrWhere[] = '`pollCode` = ?';
            $this->db->bind_param_push($arrBind, 's', $code);
        }
        if (isset($sno)) {
            $arrWhere[] = '`sno` = ?';
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        if ($arrInclude !== null) {
            /*$arrWhere[] = '`pollViewPosition` LIKE CONCAT("%",?,"%")';
            $this->db->bind_param_push($arrBind, 's', $this->viewPosition);*/
            $arrWhere[] = '`pollStatusFl` = ?';
            $this->db->bind_param_push($arrBind, 's', 'Y');
            /*$arrWhere[] = '`pollBannerFl` != ?';
            $this->db->bind_param_push($arrBind, 's', 'none');*/
            $arrWhere[] = '(`pollDeviceFl` = ? OR `pollDeviceFl` = ?)';
            $this->db->bind_param_push($arrBind, 's', 'all');
            $this->db->bind_param_push($arrBind, 's', $this->device);
            $arrWhere[] = '(((? BETWEEN `pollStartDt` AND `pollEndDt`) AND `pollEndDtFl` = ?) OR (`pollStartDt` <= ? AND `pollEndDtFl` = ?))';
            $this->db->bind_param_push($arrBind, 's', gd_date_format('Y-m-d H:i', 'now'));
            $this->db->bind_param_push($arrBind, 's', 'N');
            $this->db->bind_param_push($arrBind, 's', gd_date_format('Y-m-d H:i', 'now'));
            $this->db->bind_param_push($arrBind, 's', 'Y');
        }

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'pollStartDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_POLL . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, $returnArray);

        if (isset($sno)) {
            $data['pollItem'] = gd_htmlspecialchars(json_decode($data['pollItem'], true));
            $data['pollItem'] = json_encode($data['pollItem']);
        }

        unset($arrBind);
        unset($arrWhere);

        if (empty($data) === true) {
            throw new AlertBackException(__('설문조사 경로가 유효하지 않습니다.'));
        }

        return $data;
    }

    public function pollViewCheck($params)
    {
        if ($params['pollStatusFl'] != 'Y') {
            throw new AlertBackException(__('설문조사 진행 기간이 아닙니다.'));
        }
        if ($params['pollGroupFl'] != 'all') {
            //비회원 체크
            if (!MemberUtil::isLogin()) {
                $script = 'if (confirm("' . __('로그인하셔야 본 서비스를 이용하실 수 있습니다. 로그인하시겠습니까?') . '")) {location.href="../member/login.php";} else {history.back();}';
                return $script;
            }
        }
        if (MemberUtil::isLogin()) {
            //참여여부 체크
            $cnt = $this->getPollJoinYn($params['pollCode']);
            if ($cnt > 0) {
                throw new AlertBackException(__('이미 참여하신 설문입니다. 다음에 다시 참여해주세요.'));
            }
            //회원등급 체크
            if ($params['pollGroupFl'] == 'select') {
                $groupSno = explode(INT_DIVISION, $params['pollGroupSno']);
                if (in_array(Session::get('member.groupSno'), $groupSno) === false) {
                    throw new AlertBackException(__('설문조사 참여 대상이 아닙니다. 다음에 참여해주세요.'));
                }
            }
        }
        //설문기간 체크
        if ($params['pollStartDt'] > gd_date_format('Y-m-d H:i', 'now')) {
            throw new AlertBackException(__('설문조사 진행 기간이 아닙니다.'));
        }
        if ($params['pollStatusFl'] == 'N' || ($params['pollEndDtFl'] == 'N' && $params['pollEndDt'] < gd_date_format('Y-m-d H:i', 'now'))) {
            throw new AlertBackException(__('설문조사 기간이 종료되었습니다.'));
        }
        // 참여 인원 제한
        if ($params['pollMemberLimitFl'] == 'max' && $params['pollMemberLimitCnt'] <= $this->getPollCnt($params['pollCode'])) {
            throw new AlertBackException(__('해당 설문조사가 마감되었습니다. 다음에 다시 참여해주세요.'));
        }
    }

    public function save($params, $pollData)
    {
        // 참여 인원 제한
        if ($pollData['pollMemberLimitFl'] == 'max' && $pollData['pollMemberLimitCnt'] <= $this->getPollCnt($params['code'])) {
            throw new AlertBackException(__('해당 설문조사가 마감되었습니다. 다음에 다시 참여해주세요.'));
        }

        $memNo = '';
        $arrData = [];
        if (\Session::has('member.memNo')) {
            $memNo = \Session::get('member.memNo');
        }
        $result = json_encode($params['result'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $resultEtc = json_encode($params['resultEtc'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $arrData['pollCode'] = $params['code'];
        $arrData['memNo'] = $memNo;
        $arrData['pollResult'] = $result;
        $arrData['pollResultEtc'] = $resultEtc;

        if ($pollData['pollMileage'] > 0 && \Session::has('member.memNo')) {
            $mileage = new Mileage();
            $mileage->setIsTran(false);
            $code = $mileage::REASON_CODE_GROUP . $mileage::REASON_CODE_ETC;
            $mileage->setMemberMileage($memNo, $pollData['pollMileage'], $code, 'm', $params['code'], '', '설문조사 참여 (' . $pollData['pollTitle'] . ')');
            $arrData['mileage'] = $pollData['pollMileage'];
        }

        $arrBind = $this->db->get_binding(DBTableField::tablePollResult(), $arrData, 'insert');
        $this->db->set_insert_db(DB_POLL_RESULT, $arrBind['param'], $arrBind['bind'], 'y');
        $pollResultNo = $this->db->insert_id();

        return $pollResultNo;
    }

    public function regist($params)
    {
        if (empty($params['pollTitle']) === true) {
            throw new Exception(__('설문 제목을 입력해주세요'));
        }
        if (empty($params['pollStartDt']) === true) {
            throw new Exception(__('설문 기간을 입력해주세요'));
        } else {
            if (empty($params['pollEndDtFl']) === true && empty($params['pollEndDt']) === true) {
                throw new Exception(__('설문 기간을 입력해주세요'));
            }
            if (empty($params['pollEndDt']) === false) {
                if ($params['pollStartDt'] < gd_date_format('Y-m-d H:i', 'today')) {
                    throw new Exception(__('진행기간을 다시 확인해주세요'));
                }
                if ($params['pollStartDt'] > $params['pollEndDt']) {
                    throw new Exception(__('진행기간을 다시 확인해주세요'));
                }
            }
        }
        if (empty($params['pollMemberLimitFl']) === true) {
            throw new Exception(__('참여 인원 제한을 선택해주세요'));
        } else if ($params['pollMemberLimitFl'] == 'max' && $params['pollMemberLimitCnt'] < 1) {
            throw new Exception(__('참여 인원 제한을 1 이상 입력해주세요'));
        }

        $item = [];
        if (empty($params['sno']) === true) {
            $pollCode = microtime(true);
            $arrData['pollCode'] = $code = \Encryptor::checksum($pollCode);
        } else {
            $code = $params['pollCode'];
            if ($params['pollMemberLimitFl'] == 'max') {
                $pollResultCnt = $this->getPollCnt($params['pollCode']);
                if ($params['pollMemberLimitCnt'] < $pollResultCnt) {
                    throw new Exception(sprintf(__('해당 설문조사는 이미 %s명의 인원이 참여하였습니다.<br/>인원 제한 설정을 %s명 이상으로 설정해주세요.'), $pollResultCnt, $pollResultCnt), 500);
                }
            }
        }

        if (empty($params['pollEndDtFl']) === false) {
            $arrData['pollEndDtFl'] = $params['pollEndDtFl'];
        }
        if (empty($params['pollStatusFl']) === false) {
            $arrData['pollStatusFl'] = $params['pollStatusFl'];
        }
        if ($params['pollMemberLimitFl'] === 'unlimited') {
            $params['pollMemberLimitCnt'] = 0;
            $params['pollBannerLimitFl'] = 'N';
        }
        if ($params['pollBannerImgDel'] === 'Y') {
            $arrData['pollBannerImg'] = '';
        }
        if ($params['pollBannerImgDel'] === 'Y') {
            $arrData['pollBannerImgMobile'] = '';
        }
        if ($params['pollBannerFl'] == 'upl') {
            if (FileHandler::isDirectory(UserFilePath::data('poll')) === false) {
                $result = FileHandler::makeDirectory(UserFilePath::data('poll'), 0707);
                if ($result !== true) {
                    throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                }
            }
            if (Request::files()->get('pollBannerImg')) {
                $uploadFile = Request::files()->get('pollBannerImg');
                if (gd_file_uploadable($uploadFile, 'image') === true) {
                    $ext = explode('.', $uploadFile['name']);
                    $length = count($ext) - 1;
                    $filename = $code . '.' . $ext[$length];
                    Storage::disk(Storage::PATH_CODE_POLL)->upload($uploadFile['tmp_name'], $filename);
                    $arrData['pollBannerImg'] = $filename;
                }
            }
            if (Request::files()->get('pollBannerImgMobile')) {
                $uploadFile = Request::files()->get('pollBannerImgMobile');
                if (gd_file_uploadable($uploadFile, 'image') === true) {
                    $ext = explode('.', $uploadFile['name']);
                    $length = count($ext) - 1;
                    $filename = $code . '-mobile.' . $ext[$length];
                    Storage::disk(Storage::PATH_CODE_POLL)->upload($uploadFile['tmp_name'], $filename);
                    $arrData['pollBannerImgMobile'] = $filename;
                }
            }
        }
        $arrData['pollTitle'] = $params['pollTitle'];
        $arrData['pollStartDt'] = $params['pollStartDt'];
        $arrData['pollEndDt'] = $params['pollEndDt'];
        $arrData['pollEndDtFl'] = $params['pollEndDtFl'] ?? 'N';
        $arrData['pollDeviceFl'] = $params['pollDeviceFl'];
        $arrData['pollGroupFl'] = $params['pollGroupFl'];
        $arrData['pollGroupSno'] = @implode(INT_DIVISION, $params['groupSno']);
        $arrData['pollMemberLimitFl'] = gd_isset($params['pollMemberLimitFl'], 'unlimited');
        $arrData['pollMemberLimitCnt'] = gd_isset($params['pollMemberLimitCnt'], 0);
        $arrData['pollBannerLimitFl'] = gd_isset($params['pollBannerLimitFl'], 'N');
        $arrData['pollBannerFl'] = $params['pollBannerFl'];

        $arrData['pollViewPosition'] = @implode(',', $params['pollViewPosition']);
        $arrData['pollViewCategory'] = $params['pollViewCategory'] == 'all' ? $params['pollViewCategory'] : @implode(STR_DIVISION, $params['category']);
        $arrData['pollResultViewFl'] = $params['pollResultViewFl'];
        $arrData['pollMileage'] = $params['pollMileage'];
        $arrData['pollHtmlContentFl'] = $params['pollHtmlContentFl'];
        $arrData['pollHtmlContentSameFl'] = $params['pollHtmlContentSameFl'];
        $arrData['pollHtmlContent'] = $params['pollHtmlContent'];
        $arrData['pollHtmlContentMobile'] = $params['pollHtmlContentMobile'];
        $arrData['managerSno'] = Session::get('manager.sno');

        $item['itemAnswerType'] = $params['itemAnswerType'];
        $item['itemRequired'] = $params['itemRequired'];
        $item['itemResponseType'] = $params['itemResponseType'];
        $item['itemTitle'] = $params['itemTitle'];
        if (is_array($params['itemAnswerEtc']) === true) {
            foreach ($params['itemAnswerEtc'] as $k => $v) {
                $params['itemAnswer'][$k][] = $v;
            }
        }
        $item['itemAnswer'] = $params['itemAnswer'];

        $arrData['pollItem'] = json_encode($item);

        if (empty($params['sno']) === true) {
            $arrBind = $this->db->get_binding(DBTableField::tablePoll(), $arrData, 'insert');
            $this->db->set_insert_db(DB_POLL, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tablePoll(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $params['sno']);
            $this->db->set_update_db(DB_POLL, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
    }

    public function getPollJoinYn($code)
    {
        $memNo = \Session::get('member.memNo');
        $cnt = $this->db->getCount(DB_POLL_RESULT, '*', 'WHERE memNo = ' . $memNo . ' AND pollCode = "' . $this->db->escape($code) . '"');

        return $cnt;
    }

    public function getPollCnt($code, $memberCheck = null)
    {
        $whereis = 'WHERE pollCode = "' . $this->db->escape($code) . '"';
        if ($memberCheck === true) {
            $whereis .= ' AND memNo > 0';
        } elseif ($memberCheck === false) {
            $whereis .= ' AND memNo = 0';
        }
        $cnt = $this->db->getCount(DB_POLL_RESULT, '*', $whereis);

        return $cnt;
    }

    public function getPollMileage($code)
    {
        $arrBind = $arrWhere = [];

        $arrWhere[] = '`pollCode` = ?';
        $this->db->bind_param_push($arrBind, 's', $code);

        $this->db->strField = 'SUM(mileage) as mileage';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_POLL_RESULT . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return $data['mileage'];
    }

    public function getpollResult($code, $getData, $sort = 'pr.regDt desc')
    {
        if (empty($code) === true) {
            throw new AlertBackException(__('설문조사 경로가 유효하지 않습니다.'));
        }

        $arrField = DBTableField::setTableField('tablePollResult', null, ['pollCode'], 'pr');
        $arrBind = $arrWhere = [];

        $arrWhere[] = 'pr.pollCode = ?';
        $this->db->bind_param_push($arrBind, 's', $code);

        $this->db->strField = implode(', ', $arrField) . ', pr.regDt, m.memNm, m.memId, m.groupSno';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strJoin  = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON m.memNo=pr.memNo';
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_POLL_RESULT . ' AS pr' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return $data;
    }

    public function lists(array  $arrData, $offset = 0, $limit = 20)
    {
        $getValue = Request::get()->toArray();

        $arrField = DBTableField::setTableField('tablePoll', null, null, 'p');
        $arrBind = $arrWhere = [];
        $today = gd_date_format('Y-m-d', 'today');

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        $page->recode['amount'] = $this->db->getCount(DB_POLL); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        if (empty($arrData['keyword']) === false) {
            switch ($arrData['key']) {
                case 'all':
                    $arrWhere[] = 'p.pollTitle LIKE CONCAT("%",?,"%") OR m.managerNm LIKE CONCAT("%",?,"%")';
                    $this->db->bind_param_push($arrBind, 's', $arrData['keyword']);
                    $this->db->bind_param_push($arrBind, 's', $arrData['keyword']);
                    break;
                case 'pollTitle':
                    $arrWhere[] = 'p.' . $arrData['key'] . ' LIKE CONCAT("%",?,"%")';
                    $this->db->bind_param_push($arrBind, 's', $arrData['keyword']);
                    break;
                case 'managerNm':
                    $arrWhere[] = 'm.' . $arrData['key'] . ' LIKE CONCAT("%",?,"%")';
                    $this->db->bind_param_push($arrBind, 's', $arrData['keyword']);
                    break;
            }
        }

        // 초기 검색일 설정
        gd_isset($arrData['date'], 'pollDt');
        gd_isset($arrData['regDt'][0], date('Y-m-d', strtotime('-6 day')));
        gd_isset($arrData['regDt'][1], date('Y-m-d'));

        if (empty($arrData['regDt'][0]) === false) {
            if ($arrData['date'] == 'pollDt') $arrWhere[] = 'p.pollStartDt >=?';
            else $arrWhere[] = 'p.regDt >=?';

            $this->db->bind_param_push($arrBind, 's', $arrData['regDt'][0]);
        }
        if (empty($arrData['regDt'][1]) === false) {
            if ($arrData['date'] == 'pollDt') $arrWhere[] = 'p.pollEndDt <=?';
            else $arrWhere[] = 'p.regDt <=?';

            $this->db->bind_param_push($arrBind, 's', $arrData['regDt'][1]);
        }
        if (empty($arrData['statusFl']) === false) {
            switch ($arrData['statusFl']) {
                case 'S':
                    $arrWhere[] = 'p.pollStartDt > ?';
                    $this->db->bind_param_push($arrBind, 's', $today);
                    break;
                case 'Y':
                    $arrWhere[] = '((? BETWEEN p.pollStartDt AND pollEndDt) AND p.pollEndDtFl = ?) OR (p.pollStartDt <= ? AND p.pollEndDtFl = ?)';
                    $this->db->bind_param_push($arrBind, 's', $today);
                    $this->db->bind_param_push($arrBind, 's', 'N');
                    $this->db->bind_param_push($arrBind, 's', $today);
                    $this->db->bind_param_push($arrBind, 's', 'Y');
                    break;
                case 'E':
                    $arrWhere[] = 'p.pollEndDt < ?';
                    $arrWhere[] = 'p.pollEndDtFl = ?';
                    $this->db->bind_param_push($arrBind, 's', $today);
                    $this->db->bind_param_push($arrBind, 's', 'N');
                    break;
            }
        }

        $this->db->strField = implode(', ', $arrField) . ', m.managerId, m.managerNm';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strJoin  = ' LEFT JOIN ' . DB_MANAGER . ' AS m ON m.sno=p.managerSno';
        $this->db->strOrder = 'sno desc';
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_POLL . ' AS p' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        unset($query['left'], $query['group'], $query['order'], $query['limit']);
        $page->recode['total'] = $this->db->query_count($query, DB_POLL . ' AS p', $arrBind);
        $page->setPage();

        return $data;
    }

    public function pollChangeStatus($sno, $status)
    {
        $arrData['pollStatusFl'] = $status;
        $arrBind = $this->db->get_binding(DBTableField::tablePoll(), $arrData, 'update', array_keys($arrData));
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        $this->db->set_update_db(DB_POLL, $arrBind['param'], 'sno = ?', $arrBind['bind']);
    }

    public function getGroupCnt($code, $group = array())
    {
        foreach ($group as $v) {
            $data[$v] = $this->db->getCount(DB_POLL_RESULT . ' AS pr LEFT JOIN ' . DB_MEMBER . ' AS m ON pr.memNo = m.memNo', '*', 'WHERE pr.pollCode = \'' . $code . '\' AND m.groupSno = \'' . $v . '\'');
        }

        return $data;
    }

    public function delete($params)
    {
        foreach ($params['chk'] as $value) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $value);
            $this->db->set_delete_db(DB_POLL, 'pollCode = ?', $arrBind);
            $this->db->set_delete_db(DB_POLL_RESULT, 'pollCode = ?', $arrBind);
        }
        unset($arrBind);
    }

    public function getFieldData($code, $fieldName)
    {

    }
}
