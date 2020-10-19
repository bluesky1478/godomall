<?php
/**
 * DBUrl
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\Marketing;

use Bundle\Component\PlusShop\PlusReview\PlusReviewConfig;
use Component\Database\DBTableField;
use Framework\Debug\Exception\Except;
use Framework\Utility\GodoUtils;
use Framework\Utility\HttpUtils;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\SkinUtils;
use Globals;
use Request;
use UserFilePath;

class DBUrl
{

    const ECT_NOTEXISTS_PATH = '%s.ECT_NOTEXISTS_PATH';

    const ECT_SAVE_FAILED = '%s.ECT_SAVE_FAILED';

    const ECT_ACCESS_DENIED = '%s.ECT_ACCESS_DENIED';

    const ECT_EMPTY_COLLECT = '%s.ECT_EMPTY_COLLECT';

    const TEXT_NOTEXISTS_PATH = '%s 파일의 저장 폴더가 존재하지 않습니다.';

    const TEXT_SAVE_FAILED = '%s 업체의 설정 저장이 실패하였습니다.';

    const TEXT_ACCESS_DENIED = '접근 권한이 없습니다.';

    const TEXT_EMPTY_COLLECT = '착불 배송비를 입력하세요.';

    private $db;

    private $dcPrice = 0;

    private $deliveryBasic;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->db = \App::load('DB');
    }

    /**
     * 설정저장하기
     *
     * @param $req 설정값.
     * @param null $files
     * @throws Except
     */
    public function setConfig(&$req, $files = null)
    {
        try {
            // FBEv2 업데이트 버전으로 네이버페이 구매전환 성과 측정 설정 저장 시 변경
            if(empty($req['accessToken']) === false){
                $req['company'] = 'facebookExtensionV2';
                $req['mode'] = 'config';
            }

            $this->db->strField = "COUNT(*) AS cnt, IFNULL(value, '') as value";
            $this->db->strJoin = DB_MARKETING;
            $this->db->strWhere = 'company=? AND mode=?';
            $this->db->bind_param_push($arrBind, 's', $req['company']);
            $this->db->bind_param_push($arrBind, 's', $req['mode']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $res = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));
            unset($arrBind);

            $arrData['company'] = $req['company'];
            $arrData['mode'] = $req['mode'];


            if($req['company'] =='naver') {
                gd_isset($req['cpaAgreement'], 'n');
                gd_isset($req['dcGoods'], 'n');
                gd_isset($req['dcCoupon'], 'n');
                gd_isset($req['dcTimeSale'], 'n');

                if($req['cpaAgreement'] =='y') {
                    $req['cpaAgreementDt'] = date('Y-m-d H:i:s');
                }

                gd_isset($req['naverEventCommon'], 'n');
                gd_isset($req['naverEventGoods'], 'n');
                gd_isset($req['naverGrade'], 'n');
                gd_isset($req['onlyMemberReviewUsed'], 'n');
            }

            if($req['company'] == 'facebook') {
                gd_isset($req['goodsViewScriptFl'], 'n');
                gd_isset($req['cartScriptFl'], 'n');
                gd_isset($req['orderEndScriptFl'], 'n');
            }

            if ($res['value']) {
                $tmpValue = json_decode($res['value'], true);
            }

            foreach ($req as $key => $val) {
                $tmpValue[$key] = $val;
            }


            if (ArrayUtils::isEmpty($tmpValue['dcExCate']) === false) {
                for ($i = 0; $i < count($tmpValue['dcExCate']); $i++) {
                    if (empty($tmpValue['dcExCate'][$i])) array_splice($tmpValue['dcExCate'], $i, 1);
                }
            }
            if (ArrayUtils::isEmpty($tmpValue['dcExGoods']) === false) {
                for ($i = 0; $i < count($tmpValue['dcExGoods']); $i++) {
                    if (empty($tmpValue['dcExGoods'][$i])) array_splice($tmpValue['dcExGoods'], $i, 1);
                }
            }
            if (ArrayUtils::isEmpty($tmpValue['dcExCate'])) unset($tmpValue['dcExCate']);
            if (ArrayUtils::isEmpty($tmpValue['dcExGoods'])) unset($tmpValue['dcExGoods']);

            unset($tmpValue['type']);
            unset($tmpValue['company']);
            unset($tmpValue['mode']);
            unset($tmpValue['subCompany']);

            if ($files != null) {
                foreach ($files as $key => $file) {
                    if (is_uploaded_file($file['tmp_name'])) {
                        move_uploaded_file($file['tmp_name'], UserFilePath::frontSkin(Globals::get('gSkin.frontSkinName'), 'img', 'marketing', $file['name']));
                        $tmpValue[$key] = $file['name'];
                    }
                }
                unset($files);
            }

            // 도메인 인증코드 검증
            if($req['company'] == 'facebookExtension' && empty($req['value']['domainAuthCode']) === false) {
                $authCode = $tmpValue['value']['domainAuthCode'];
                $getDefaultHost = Request::getDefaultHost();

                if (strpos($authCode, '<meta name="facebook-domain-verification"') !== false) {
                    preg_match('@content="([^"]+)"@', $authCode, $match);
                    $tmpValue['value']['domainAuthCode'] = array_pop($match);
                } else if (strpos($authCode , 'facebook-domain-verification') !== false) {
                    $tmpValue['value']['domainAuthCode'] = str_replace('facebook-domain-verification=', '', $authCode);
                } else if (strpos($authCode, $getDefaultHost) !== false) {
                    $arrAuthCode = explode('/', $authCode);
                    $tmpAuthCode = explode('.', $arrAuthCode[count($arrAuthCode) - 1]);
                    $tmpValue['value']['domainAuthCode'] = $tmpAuthCode[0];
                } else if (strpos($authCode, '.html') !== false) {
                    $tmpValue['value']['domainAuthCode'] = str_replace('.html', '', $authCode);
                }
            }

            $arrData['value'] = json_encode($tmpValue,JSON_UNESCAPED_UNICODE);

            if ($res['cnt'] == 0) {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMarketing(), $arrData, 'insert', array_keys($arrData));
                $this->db->set_insert_db(DB_MARKETING, $arrBind['param'], $arrBind['bind'], 'y');
            }
            else {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMarketing(), $arrData, 'update', array_keys($arrData));
                $this->db->bind_param_push($arrBind['bind'], 's', $req['company']);
                $this->db->bind_param_push($arrBind['bind'], 's', $req['mode']);
                $this->db->set_update_db(DB_MARKETING, $arrBind['param'], 'company=? AND mode=?', $arrBind['bind'], false);
            }

            // htaccess 저장하기
            if(($req['company'] == 'facebook' && empty($req['domainAuthCode']) === false) || ($req['company'] == 'facebookExtension' && empty($req['value']['domainAuthCode']) === false)) {
                $defaultFileName = 'data/facebook/domain_auth.html';
                $authCode = $req['value']['domainAuthCode'];
                $getDefaultHost = Request::getDefaultHost();

                // 도메인 인증코드 검증
                if (strpos($authCode , '<meta name="facebook-domain-verification"') !== false) {
                    preg_match( '@content="([^"]+)"@' , $authCode, $match );
                    $authCode = array_pop($match);
                } else if (strpos($authCode , 'facebook-domain-verification') !== false) {
                    $authCode = str_replace('facebook-domain-verification=', '', $authCode);
                } else if (strpos($authCode, $getDefaultHost) !== false) {
                    $arrAuthCode = explode('/', $authCode);
                    $tmpAuthCode = explode('.', $arrAuthCode[count($arrAuthCode) - 1]);
                    $authCode = $tmpAuthCode[0];
                } else if (strpos($authCode, '.html') !== false) {
                    $authCode = str_replace('.html', '', $authCode);
                }

                \FileHandler::write(USERPATH . $defaultFileName, $authCode . ".html", 0707);
                $content = \FileHandler::read(USERPATH . '.htaccess');
                $data = explode("\n", $content);

                $rewriteFl = false;
                $addLine = $defaultLine = '';
                foreach ($data as $key => $value) {
                    if (strtolower($value) == 'rewriteengine on') $addLine = $key;
                    if (strstr($value, $defaultFileName)) {echo $key;
                        $rewriteFl = true;
                        $defaultLine = $key;
                    }
                }

                if ($rewriteFl === true) {
                    if (empty($data[$defaultLine + 1]) === true) unset($data[$defaultLine + 1]);
                    unset($data[$defaultLine], $data[$defaultLine - 1]);
                    $data = array_values($data);
                }

                $addHtaccess = [
                    "",
                    "RewriteCond %{REQUEST_URI} =/" . $authCode . ".html",
                    "RewriteRule ^" . $authCode . ".html /" . $defaultFileName,
                ];
                array_splice($data, $addLine + 1, 0, $addHtaccess);
                $htaccess = @implode("\n", $data);

                \FileHandler::write(USERPATH . '.htaccess', $htaccess, 0707);
            }
        }
        catch (Except $e) {
            throw new Except(sprintf(self::ECT_SAVE_FAILED, 'DBUrl'), sprintf(__('%s 업체의 설정 저장이 실패하였습니다.'), gd_isset($req['company'])));
        }
    }

    /**
     * 설정가져오기
     *
     * @param string $company   업체명
     * @param string $mode      저장정보
     *
     * @return mixed|null
     */
    public function getConfig($company, $mode)
    {
        try {
            $this->db->strField = '*';
            $this->db->strJoin = DB_MARKETING;
            $this->db->strWhere = 'company=? AND mode=?';
            $this->db->bind_param_push($arrBind, 's', $company);
            $this->db->bind_param_push($arrBind, 's', $mode);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $res = gd_htmlspecialchars_stripslashes($this->db->slave()->query_fetch($strSQL, $arrBind, false));

            if (!isset($res['value'])) return null;
            $result = json_decode($res['value'], true);

            if(\Request::isCli() === false){
                if($company == 'naver' && $mode == 'config') {
                    if(GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW) === false){
                        $result['naverReviewChannel'] = 'board';
                    }
                    else {
                        $plusReviewConfig = new PlusReviewConfig();
                        if ($plusReviewConfig->getConfig('useFl') != 'y') {
                            $result['naverReviewChannel'] = 'board';
                        }
                    }
                }
            }
            return $result;
        }
        catch (Except $e) {
            return null;
        }
    }


    /**
     * 상품 리뷰 가져오기.
     *
     * @param $goodsNo 상품코드
     */
    private function getReviewCnt($goodsNo)
    {
        try {
            $this->db->strField = 'COUNT(*) AS cnt';
            $this->db->strJoin = DB_BD_ . 'goodsreview';
            $this->db->strWhere = 'goodsNo=?';
            $this->db->bind_param_push($arrBind, 's', INT_DIVISION . $goodsNo . INT_DIVISION);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $res = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));

            return gd_isset($res['cnt'], 0);
        }
        catch (Except $e) {
            return 0;
        }
    }

    /**
     * 상품 정보 쿼리.
     *
     * @param $mode 가져올 상품 정보 분류(전체(all), 요약 또는 신규(summary or new))
     * @param $type 상품정보(goods) / 상품수량(count)
     * @param $start 시작번호 (Limit ?, ?)
     * @param $limit 가져올 상품수 (Limit ?, ?)
     * @param $updateDt 요약상품 기준일
     */
    public function getGoodsQuery($mode, $type = 'goods', $start = 0, $limit = 100, $updateDt = 'curdate()', $addWhere = '')
    {
        switch ($type) {
            case 'goods':
                {
                    $query['field'] = "g.goodsNo, g.goodsNm, g.imageStorage, g.imagePath, gi.imageName, g.makerNm, g.originNm, g.cateCd, g.goodsModelNo, g.shortDescription, g.goodsCd, g.goodsPrice, g.mileageGoods, cb.cateNm AS brandNm, g.regDt, IFNULL(g.modDt, g.regDt) AS modDt, g.soldOutFl, g.taxFreeFl, g.taxPercent, g.goodsDisplayFl, g.goodsSellFl, g.scmNo, g.goodsWeight, g.optionName, g.optionTextFl, g.goodsDescription";
                    break;
                }
            case 'count':
                {
                    $query['field'] = "COUNT(*) AS cnt ";
                    break;
                }
        }

        $query['join'] = DB_GOODS . " AS g " . " LEFT JOIN " . DB_GOODS_OPTION . " AS go ON g.goodsNo = go.goodsNo AND optionNo = 1 " . " LEFT JOIN " . DB_CATEGORY_BRAND . " AS cb ON g.brandCd = cb.cateCd " . " LEFT JOIN " . DB_GOODS_IMAGE . " AS gi ON g.goodsNo = gi.goodsNo AND gi.imageKind='magnify' AND gi.imageNo=0 ";

        $query['where'] = " goodsDisplayFl!='n' AND goodsSellFl!='n' AND g.cateCd!='' AND g.cateCd IS NOT NULL ";
        if ($mode == 'new') {
            $query['where'] .= " AND (g.regDt >= '" . $updateDt . "' OR g.modDt >= '" . $updateDt . "')";
        }
        if ($addWhere != '') $query['where'] .= $addWhere;
        $query['order'] = "g.goodsNo ASC";

        if ($type == 'goods') {
            $query['limit'] = $start . ',' . $limit;
        }
        return $query;
    }

    /**
     * 상품 수량 가져오기.
     *
     * @param $addWhere 추가할 조건
     * @param $mode 가져올 상품 정보 분류(전체(all), 요약 또는 신규(summary or new))
     */
    private function getGoodsCount($mode, $updateDt = 'curdate()', $addWhere = '')
    {
        try {
            $query = $this->getGoodsQuery($mode, 'count', 0, 0, $updateDt, $addWhere);
            $this->db->strField = gd_isset($query['field']);
            $this->db->strJoin = gd_isset($query['join']);
            $this->db->strWhere = gd_isset($query['where']);
            $this->db->strOrder = gd_isset($query['order']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $goodsDatas = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, null, false));

            return $goodsDatas['cnt'];
        }
        catch (Except $e) {
            return null;
        }
    }

    /**
     * 상품 정보 가져오기.
     *
     * @param $mode 가져올 상품 정보 분류(전체(all), 요약 또는 신규(summary or new))
     * @param $start 시작번호 (Limit ?, ?)
     * @param $limit 가져올 상품수 (Limit ?, ?)
     * @param $updateDt 요약상품 기준일
     */
    public function getGoodsList($mode, $start = 0, $limit = 100, $updateDt = 'curdate()', $addWhere = '')
    {
        try {
            $query = $this->getGoodsQuery($mode, 'goods', $start, $limit, $updateDt, $addWhere);
            $this->db->strField = gd_isset($query['field']);
            $this->db->strJoin = gd_isset($query['join']);
            $this->db->strWhere = gd_isset($query['where']);
            $this->db->strOrder = gd_isset($query['order']);
            $this->db->strLimit = gd_isset($query['limit']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $goodsDatas = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, null));

            return $goodsDatas;
        }
        catch (Except $e) {
            return null;
        }
    }

    /**
     * 상품 전체카테고리 가져오기.
     *
     * @return array
     */
    public function getCategory()
    {
        try {
            $this->db->strField = '*';
            $this->db->strJoin = DB_CATEGORY_GOODS;
            $this->db->strWhere = "divisionFl='n'";

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $res = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, null));

            foreach ($res as $val) {
                $goodsCate[$val['cateCd']] = $val['cateNm'];
            }
            unset($res);

            return $goodsCate;
        }
        catch (Except $e) {
            return null;
        }
    }

    /**
     * 상품 카테고리 가져오기.
     *
     * @param &$arrCate 전체 카테고리 배열
     * @param $cateCd 카테고리 코드
     * @return array
     */
    public function getGoodsCategory(&$arrCate, $cateCd)
    {
        try {
            $arrCateCd = str_split($cateCd, DEFAULT_LENGTH_CATE);
            $tmpCateCd = '';
            for ($j = 0; $j < count($arrCateCd); $j++) {
                $tmpCateCd .= $arrCateCd[$j];
                $rtnCateCd[] = $tmpCateCd;
                $rtnCateNm[] = gd_isset($arrCate[$tmpCateCd]);
            }
            unset($arrCateCd, $tmpCateCd);
            return array('cateCd' => $rtnCateCd, 'cateNm' => $rtnCateNm);
        }
        catch (Except $e) {
            return null;
        }
    }

    /**
     * 회원 할인가 계산.
     *
     * @param $goodsPrice 상품가격
     * @param $memberDc 회원 할인 정보
     */
    private function getMemberCalculate($goodsPrice, $memberDc)
    {
        gd_isset($memberDc['dcType'], 'price');
        gd_isset($memberDc['dcPercent'], 0);
        gd_isset($memberDc['dcPrice'], 0);
        gd_isset($memberDc['dcCut'], 0);
        gd_isset($memberDc['dcCutType'], 'down');

        // 할인 방법에 따른 할인가 계산
        if ($memberDc['dcType'] == 'price') {
            $member_dc_sale = $memberDc['dcPrice'];
        }
        else {
            $tmpPrice = $goodsPrice * $memberDc['dcPercent'] / 100;
            if ($memberDc['dcCut'] == 0) {
                $member_dc_sale = $tmpPrice;
            }
            else {
                switch ($memberDc['dcCutType']) {
                    case 'up':
                        {
                            $member_dc_sale = ceil($tmpPrice / ($memberDc['dcCut'] * 10)) * ($memberDc['dcCut'] * 10);
                            break;
                        }
                    case 'half':
                        {
                            $member_dc_sale = round($tmpPrice / ($memberDc['dcCut'] * 10)) * ($memberDc['dcCut'] * 10);
                            break;
                        }
                    case 'down':
                        {
                            $member_dc_sale = floor($tmpPrice / ($memberDc['dcCut'] * 10)) * ($memberDc['dcCut'] * 10);
                            break;
                        }
                }
            }
            unset($tmpPrice);
        }

        return $member_dc_sale;
    }

    /**
     * 회원 할인가 가져오기.
     *
     * @param $goodsNo 상품코드
     * @param $cateCd 카테고리코드
     * @param $goodsPrice 상품가격
     */
    public function getMemberDc($goodsNo, $cateCd, $goodsPrice)
    {
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $memberDc = $memberGroup->getGroupForSale($goodsNo, $cateCd);
        $memberDcPrice = $this->getMemberCalculate($goodsPrice, $memberDc);

        unset($memberGroup);
        unset($memberDc);

        return $memberDcPrice;
    }

    /**
     * 모든 배송 정책 가져오기.
     * @todo [중요] 기본 배송 정책은 더 이상 존재하지 않음 basicKey 변경 혹은 삭제 필요
     */
    public function setDeliveryList()
    {
        try {
            $arrField = DBTableField::setTableField('tableScmDeliveryBasic', null, null, 'sdb');
            $strSQL = "SELECT " . implode(', ', $arrField) . " FROM " . DB_SCM_DELIVERY_BASIC . " sdb WHERE basicKey = 1";
            $this->deliveryBasic = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL));
        }
        catch (Except $e) {
            throw new Except(sprintf(self::ECT_DELIVERY_LIST, "DBUrl"), self::TEXT_DELIVERY_LIST);
        }
    }

    /**
     * 배송료 계산
     *
     * @param $goodsData 상품정보
     */
    public function getDeliveryCharge(&$goodsData)
    {
        switch ($goodsData['deliveryFl']) {
            case 'conf':
                {
                    // 기본 배송설정에 의한 설정
                    // 배송정책 기본 정보
                    $deliveryPrice = 0;
                    $deliveryData = null;

                    for ($i = 0; $i < count($this->deliveryBasic); $i++) {
                        if ($this->deliveryBasic[$i]['scmNo'] == $goodsData['scmNo']) {
                            $deliveryData = $this->deliveryBasic[$i];
                            break;
                        }
                    }

                    if ($deliveryData == null) break;

                    // 후불 정책이면 -1
                    if ($deliveryData['collectFl'] == 'y') return -1;

                    // 배송 금액 체크
                    if ($deliveryData['fixFl'] == 'price' || $deliveryData['fixFl'] == 'weight') {
                        if (empty($goodsData['goods' . ucfirst($deliveryData['fixFl'])])) { // 상품이 잘못들어간경우 제외(상품가격이 없는 경우임)
                            break;
                        }
                        $strWhere = ' AND ' . $goodsData['goods' . ucfirst($deliveryData['fixFl'])] . ' BETWEEN sdc.unitStart AND sdc.unitEnd ';
                    }
                    else {
                        $strWhere = '';
                    }

                    $strSQL = "SELECT sdc.price	FROM " . DB_SCM_DELIVERY_CHARGE . " AS sdc WHERE sdc.scmNo = '" . $goodsData['scmNo'] . "' AND sdc.basicKey = '" . $deliveryData['basicKey'] . "' " . $strWhere;
                    $resultPrice = $this->db->query_fetch($strSQL);
                    foreach ($resultPrice as $val) {
                        $deliveryPrice = $val['price'];
                    }
                    break;
                }
            case 'goods':
                {
                    $tmp = explode(INT_DIVISION, gd_isset($goodsData['deliveryGoods']));
                    // $goodsData['deliveryGoods'] = gd_isset($tmp[1]);
                    $deliveryPrice = gd_isset($tmp[1], 0);
                    unset($tmp);
                    break;
                }
            case 'collect':
                {
                    return -1;
                }
            default:
                {
                    return 0;
                }
        }
        return $deliveryPrice;
    }

    /**
     * 해당 상품 포함된 이벤트 가져오기
     *
     * @param $goodsNo 상품번호
     * @return array
     */
    private function getEvent($goodsNo)
    {
        $date = date("Ymd");
        $query = 'SELECT sno, subject FROM ' . DB_EVENT . ' WHERE goodsNo LIKE concat(?,?,?) AND startDt <= ? AND endDt >= ? LIMIT 1';
        $this->db->bind_param_push($arrBind, 's', INT_DIVISION);
        $this->db->bind_param_push($arrBind, 's', $goodsNo);
        $this->db->bind_param_push($arrBind, 's', INT_DIVISION);
        $this->db->bind_param_push($arrBind, 's', $date);
        $this->db->bind_param_push($arrBind, 's', $date);
        $res = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind);
        return $res;
    }

    /**
     * 해당 상품 이미지 가져오기
     *
     * @param &$dataFile DataFile object
     * @param $imageStorage 이미지스토리지
     * @param $imagePath 이미지경로
     * @param $imageName 이미지명
     * @return string
     */
    public function getImageUrl(&$dataFile, $imageStorage, $imagePath, $imageName)
    {
        if ($imageStorage == 'url') {
            $imageUrl = $imageName;
        }
        else {
            if ($dataFile->getImageStorage($imageStorage) == null) {
                $dataFile->setImageStorage($imageStorage, $imageStorage, 'goods');
            }
            $imgPath = $dataFile->getUrl($imageStorage, $imagePath . $imageName);
            $imageUrl = $imgPath['fullPath'];
            unset($imgPath);
        }

        return $imageUrl;
    }

    /**
     * 아이피 검사
     *
     * @param string $iplist_url 접근 가능 ip list URL
     * @param string $referer 레퍼러
     * @return bool
     */
    public function checkAcceptIp($iplist_url, $referer = '')
    {
        $out = HttpUtils::remoteGet($iplist_url);
        $arr = explode(chr(10), $out);
        $ret = false;
        if (ArrayUtils::isEmpty($arr) === false) {
            foreach ($arr as $v) {
                $v = trim($v);
                if ($v && preg_match('/' . $v . '/', Request::getRemoteAddress())) $ret = true;
            }
        }

        if (preg_match('/61.36.175./', Request::getRemoteAddress())) $ret = true; // 테스트 아이피
        if ($referer && preg_match($referer, $_SERVER['HTTP_REFERER'])) $ret = true;
        return $ret;
    }

    /**
     * 네이버 상품 정보 가져오기
     *
     * @param $mode ep구분(all : 전체, summary : 요약)
     */
    public function genarateNaver($mode, $path)
    {
        if (empty($path)) return;

        $coupon = \App::load('\\Component\\Coupon\\Coupon');

        $configData = $this->getConfig('naver', 'config');

        $mileageGoods = gd_policy('mileage.goods');
        $goodsTax = gd_policy('goods.tax');


        $goodsCate = $this->getCategory();

        $goodsCount = $this->getGoodsCount($mode);

        //$this->setDeliveryList(); // 전체 배송정책 배열에 저장.



        if ($path != '') $this->initFile($path);

        for ($i = 0; $i < ceil($goodsCount / 100); $i++) {
            $goodsDatas = $this->getGoodsList($mode, $i * 100, 100);

            foreach ($goodsDatas as &$data) {
                // 배송비 설정
                //$data['deliveryGoods'] = $this->getDeliveryCharge($data);

                // 상품 이미지
                $data['imageUrl'] = SkinUtils::imageViewStorageConfig($data['imageName'], $data['imagePath'], $data['imageStorage'], null, 'goods',false)[0];


                // 상품 카테고리
                $category = $this->getGoodsCategory($goodsCate, $data['cateCd']);

                $memberDcPrice = $this->getMemberDc($data['goodsNo'], $data['cateCd'], $data['goodsPrice']);
                $couponPrice = $coupon->getCouponInfoGoodsList(gd_isset($data['goodsNo']), gd_isset($data['cateCd']), gd_isset($data['goodsPrice'])) + $memberDcPrice;

                $result = '';
                $result .= '<<<begin>>>' . chr(13) . chr(10);
                $result .= '<<<mapid>>>' . $data['goodsNo'] . chr(13) . chr(10); // [필수] 쇼핑몰 상품ID
                $result .= '<<<pname>>>' . str_replace(array('{_maker}', '{_brand}'), array($data['makerNm'], $data['brandNm']), $configData['goodshead'] . ' ' . $data['goodsNm']) . chr(13) . chr(10); // [필수] 상품명
                $result .= '<<<price>>>' . ($data['goodsPrice'] - $couponPrice) . chr(13) . chr(10); // [필수] 판매가격
                if ($mode == 'all') {
                    $result .= '<<<pgurl>>>' . URI_HOME . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=naver' . chr(13) . chr(10); // [필수] 상품의 상세페이지 주소
                    $result .= '<<<igurl>>>' . $data['imageUrl'] . chr(13) . chr(10); // [필수] 이미지 URL
                    $result .= '<<<caid1>>>' . gd_isset($category['cateCd'][0]) . chr(13) . chr(10); // [필수] 대분류 카테고리 코드
                    if (gd_isset($category['cateCd'][1])) $result .= '<<<caid2>>>' . gd_isset($category['cateCd'][1]) . chr(13) . chr(10); // [선택] 중분류 카테고리 코드
                    if (gd_isset($category['cateCd'][2])) $result .= '<<<caid3>>>' . gd_isset($category['cateCd'][2]) . chr(13) . chr(10); // [선택] 소분류 카테고리 코드
                    if (gd_isset($category['cateCd'][3])) $result .= '<<<caid4>>>' . gd_isset($category['cateCd'][3]) . chr(13) . chr(10); // [선택] 세분류 카테고리 코드
                    $result .= '<<<cate1>>>' . gd_isset($category['cateNm'][0]) . chr(13) . chr(10); // [필수] 대카테고리명
                    if (gd_isset($category['cateNm'][1])) $result .= '<<<cate2>>>' . gd_isset($category['cateNm'][1]) . chr(13) . chr(10); // [선택] 중카테고리명
                    if (gd_isset($category['cateNm'][2])) $result .= '<<<cate3>>>' . gd_isset($category['cateNm'][2]) . chr(13) . chr(10); // [선택] 소카테고리명
                    if (gd_isset($category['cateNm'][3])) $result .= '<<<cate4>>>' . gd_isset($category['cateNm'][3]) . chr(13) . chr(10); // [선택] 세카테고리명
                    $result .= '<<<model>>>' . gd_isset($data['goodsModelNo']) . chr(13) . chr(10); // [선택] 모델명
                    $result .= '<<<brand>>>' . gd_isset($data['brandNm']) . chr(13) . chr(10); // [선택] 브랜드
                    $result .= '<<<maker>>>' . gd_isset($data['makerNm']) . chr(13) . chr(10); // [선택] 메이커
                    $result .= '<<<origi>>>' . gd_isset($data['originNm']) . chr(13) . chr(10); // [선택] 원산지
                    $result .= '<<<pdate>>>' . DateTimeUtils::dateFormat('Y-m-d', gd_isset($data['regDt'])) . chr(13) . chr(10); // [선택] 상품등록일자
                    $result .= '<<<deliv>>>' . gd_isset($data['deliveryGoods']) . chr(13) . chr(10); // [선택] 배송비
                    $result .= '<<<coupo>>>' . $couponPrice . __('원') . chr(13) . chr(10); // [선택] 무이자
                    $result .= '<<<pcard>>>' . $configData['pcard'] . chr(13) . chr(10); // [선택] 무이자
                    $result .= '<<<point>>>' . gd_isset($data['mileage']) . chr(13) . chr(10); // [선택] 마일리지
                }
                $result .= '<<<ftend>>>';
                $saveData[] = $result;
                unset($result);
                unset($data);
            }
            unset($goodsDatas);

            if (ArrayUtils::isEmpty($saveData) === false) {
                $inputData = implode(chr(13) . chr(10), $saveData);
                unset($saveData);

                if ($i != ceil($goodsCount / 100) - 1) $inputData .= chr(13) . chr(10);
                if ($path != '') {
                    $this->genarateFile($path, $inputData);
                }
                else {
                    echo $inputData;
                }
                unset($inputData);
            }
        }
    }

    /**
     * 다음 쇼핑하우 상품 정보 가져오기
     */
    public function printDaumcpc()
    {
        try {
            $mode = 'all';
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            $dataFile = \App::load('\\Component\\File\\DataFile');

            $configData = $this->getConfig('daumcpc', 'config');

            $mileageGoods = gd_policy('mileage.goods');
            $goodsTax = gd_policy('goods.tax');

            $goodsCate = $this->getCategory();
            $goodsCount = $this->getGoodsCount($mode);

            $this->setDeliveryList(); // 전체 배송정책 배열에 저장.

            for ($i = 0; $i < ceil($goodsCount / 100); $i++) {
                $goodsDatas = $this->getGoodsList($mode, $i * 100, 100);

                foreach ($goodsDatas as &$data) {
                    // 배송비 설정
                    $data['deliveryGoods'] = $this->getDeliveryCharge($data);
                    switch ($data['deliveryGoods']) {
                        case 0:
                            {
                                // 무료
                                $deliv1 = '0';
                                $deliv2 = '';
                                break;
                            }
                        case -1:
                            {
                                // 착불
                                $deliv1 = '1';
                                $deliv2 = '착불';
                                break;
                            }
                        default:
                            {
                                // 배송료
                                $deliv1 = '1';
                                $deliv2 = $data['deliveryGoods'];
                                break;
                            }
                    }

                    // 상품 이미지
                    $data['imageUrl'] = $this->getImageUrl($dataFile, $data['imageStorage'], $data['imagePath'], $data['imageName']);

                    // 카테고리
                    $category = $this->getGoodsCategory($goodsCate, $data['cateCd']);

                    $memberDcPrice = $this->getMemberDc($data['goodsNo'], $data['cateCd'], $data['goodsPrice']);
                    $couponPrice = $coupon->getCouponInfoGoodsList(gd_isset($data['goodsNo']), gd_isset($data['cateCd']), gd_isset($data['goodsPrice'])) + $memberDcPrice;
                    $reviewCnt = $this->getReviewCnt($data['goodsNo']);

                    $result = '';
                    $result .= '<<<begin>>>' . chr(13) . chr(10);
                    $result .= '<<<pid>>>' . $data['goodsNo'] . chr(13) . chr(10); // [필수] 쇼핑몰 상품ID
                    $result .= '<<<price>>>' . ($data['goodsPrice'] - $couponPrice) . chr(13) . chr(10); // [필수] 판매가격
                    $result .= '<<<pname>>>' . str_replace(array('{_maker}', '{_brand}'), array($data['makerNm'], $data['brandNm']), $configData['goodshead'] . ' ' . $data['goodsNm']) . chr(13) . chr(10); // [필수] 상품명

                    $result .= '<<<pgurl>>>' . URI_HOME . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=daumCpc' . chr(13) . chr(10); // [필수] 상품의 상세페이지 주소
                    $result .= '<<<igurl>>>' . $data['imageUrl'] . chr(13) . chr(10); // [필수] 이미지 URL
                    $result .= '<<<cate1>>>' . gd_isset($category['cateCd'][0]) . chr(13) . chr(10); // [필수] 대분류 카테고리 코드
                    if (gd_isset($category['cateCd'][1])) $result .= '<<<cate2>>>' . gd_isset($category['cateCd'][1]) . chr(13) . chr(10); // [선택] 중분류 카테고리 코드
                    if (gd_isset($category['cateCd'][2])) $result .= '<<<cate3>>>' . gd_isset($category['cateCd'][2]) . chr(13) . chr(10); // [선택] 소분류 카테고리 코드
                    if (gd_isset($category['cateCd'][3])) $result .= '<<<cate4>>>' . gd_isset($category['cateCd'][3]) . chr(13) . chr(10); // [선택] 세분류 카테고리 코드
                    $result .= '<<<catename1>>>' . gd_isset($category['cateNm'][0]) . chr(13) . chr(10); // [필수] 대카테고리명
                    if (gd_isset($category['cateNm'][1])) $result .= '<<<catename2>>>' . gd_isset($category['cateNm'][1]) . chr(13) . chr(10); // [선택] 중카테고리명
                    if (gd_isset($category['cateNm'][2])) $result .= '<<<catename3>>>' . gd_isset($category['cateNm'][2]) . chr(13) . chr(10); // [선택] 소카테고리명
                    if (gd_isset($category['cateNm'][3])) $result .= '<<<catename4>>>' . gd_isset($category['cateNm'][3]) . chr(13) . chr(10); // [선택] 세카테고리명
                    $result .= '<<<model>>>' . gd_isset($data['goodsModelNo']) . chr(13) . chr(10); // [선택] 모델명
                    $result .= '<<<brand>>>' . gd_isset($data['brandNm']) . chr(13) . chr(10); // [선택] 브랜드
                    $result .= '<<<maker>>>' . gd_isset($data['makerNm']) . chr(13) . chr(10); // [선택] 메이커
                    $result .= '<<<pdate>>>' . DateTimeUtils::dateFormat('Ymd', gd_isset($data['regDt'])) . chr(13) . chr(10); // [선택] 상품등록일자
                    $result .= '<<<coupon>>>' . $couponPrice . __('원') . chr(13) . chr(10); // [선택] 무이자
                    $result .= '<<<pcard>>>' . $configData['pcard'] . chr(13) . chr(10); // [선택] 무이자
                    $result .= '<<<point>>>' . gd_isset($data['mileage']) . chr(13) . chr(10); // [선택] 마일리지
                    $result .= '<<<deliv>>>' . $deliv1 . chr(13) . chr(10); // [선택] 배송비
                    $result .= '<<<deliv2>>>' . $deliv2 . chr(13) . chr(10); // [선택] 배송비
                    if ($reviewCnt) $result .= '<<<review>>>' . $reviewCnt . chr(13) . chr(10);
                    $gMall = ComponentUtils::getMallInfo();
                    if (gd_isset($gMall['mallNm'])) $result .= '<<<sellername>>>' . $gMall['mallNm'] . chr(13) . chr(10);
                    $result .= '<<<end>>>';
                    $saveData[] = $result;
                    unset($result);
                    unset($data);
                }
                unset($goodsDatas);

                if (ArrayUtils::isEmpty($saveData) === false) {
                    $inputData = implode(chr(13) . chr(10), $saveData);
                    unset($saveData);

                    if ($i != ceil($goodsCount / 100) - 1) $inputData .= chr(13) . chr(10);
                    echo @iconv('UTF-8', 'EUC-KR//IGNORE', $inputData);
                    unset($inputData);
                }
            }
        }
        catch (\Exception $e) {
            return "";
        }
    }

    /**
     * 상품 정보 파일에 준비.
     *
     * @param $path 경로
     */
    private function initFile($path)
    {
        $paths = explode('/', $path);
        array_splice($paths, count($paths) - 1, 1);
        $tmpPath = implode('/', $paths);
        if (is_dir($tmpPath) === false) {
            $dir_path = '';
            for ($i = 0; $i < count($paths); $i++) {
                $dir_path .= $paths[$i];
                if (is_dir($dir_path) === false) {
                    @mkdir($dir_path);
                    @chmod($dir_path, 0707);
                }
                $dir_path .= '/';
            }
            unset($dir_path);

            if (is_dir($tmpPath) === false) {
                throw new Except(sprintf(self::ECT_NOTEXISTS_PATH, 'DBUrl'), sprintf(__('%s 파일의 저장 폴더가 존재하지 않습니다.'), $tmpPath));
            }
        }
        unset($paths);

        $result = '<?php header("Cache-Control: no-cache, must-revalidate"); header("Content-Type: text/plain; charset=euc-kr"); ?>';
        file_put_contents($path, @iconv('UTF-8', 'EUC-KR//IGNORE', $result));
        @chmod($path, 0707);
    }

    /**
     * 상품 정보 파일에 저장.
     *
     * @param $path 경로
     * @param $result 저장내용
     */
    private function genarateFile($path, $result)
    {
        $tmpRes = str_split($result, 1024);
        for ($i = 0; $i < count($tmpRes); $i++) {
            file_put_contents($path, @iconv('UTF-8', 'EUC-KR//IGNORE', $tmpRes[$i]), FILE_APPEND | LOCK_EX);
        }
        @chmod($path, 0707);
    }

    /**
     * delConfig
     * 설정 삭제
     *
     * @param $data
     */
    public function delConfig($data) {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $data['company']);
        $this->db->bind_param_push($arrBind, 's', $data['mode']);
        $this->db->set_delete_db(DB_MARKETING, 'company = ? AND mode = ?', $arrBind);
    }
}
