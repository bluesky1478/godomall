<?php
/**
 * 사은품 class
 *
 * 사은품 관련 관리자 Class
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright Copyright (c), Godosoft
 */
namespace Bundle\Component\Gift;

use Component\Member\Group\Util as GroupUtil;
use Component\Storage\Storage;
use Component\Database\DBTableField;
use Component\File\StorageHandler;
use Component\Gift\Gift;
use Component\Validator\Validator;
use Exception;
use LogHandler;
use Request;
use Session;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\LayerNotReloadException;

class GiftAdmin extends \Component\Gift\Gift
{
    const ECT_INVALID_ARG = 'GiftAdmin.ECT_INVALID_ARG';

    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';

    const TEXT_NOT_EXIST = '%s이(가) 존재하지 않습니다.';

    private $giftNo;

    private $imagePath;

    private $arrBind = [];
    // 리스트 검색관련
    private $arrWhere = [];
    // 리스트 검색관련
    private $checked = [];
    // 리스트 검색관련
    private $search = [];
    // 리스트 검색관련

    protected $storage;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->storage = Storage::disk(Storage::PATH_CODE_GIFT);
    }

    /**
     * 사은품의 등록 및 수정에 관련된 정보
     *
     * @param  string $giftNo 사은품 번호
     *
     * @return array  해당 사은품 데이타
     */
    public function getDataGift($giftNo = null)
    {
        // --- 등록인 경우
        if (is_null($giftNo)) {
            // 기본 정보
            $data['mode'] = 'register';
            $data['giftNo'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableGift', $data);

            // --- 수정인 경우
        } else {
            // 기본 정보
            $data = $this->getGiftInfo($giftNo); // 사은품 기본 정보
            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            $data['mode'] = 'modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableGift', $data);
        }

        // --- 기본값 설정
        gd_isset($data['stockFl'], 'n');

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) {
            $data['scmFl'] = "n";
        } else {
            $data['scmFl'] = "y";
        }

        $checked = [];
        $checked['scmFl'][$data['scmFl']] = $checked['stockFl'][$data['stockFl']] = 'checked="checked"';


        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 새로운 사은품 번호 출력
     *
     * @return string 새로운 사은품 번호
     */
    public function getNewGiftno()
    {
        $data = $this->getGiftInfo(null, 'if(max(giftNo) > 0, (max(giftNo) + 1), ' . DEFAULT_CODE_GIFTNO . ') as newGiftNo');

        return $data['newGiftNo'];
    }

    /**
     * 사은품 번호를 Gift 테이블에 저장
     *
     * @return array 저장된 사은품 번호
     */
    private function doGiftnoInsert()
    {
        $newGiftNo = $this->getNewGiftno();
        $this->db->set_insert_db(
            DB_GIFT, 'giftNo', [
            'i',
            $newGiftNo,
        ], 'y'
        );

        return $newGiftNo;
    }

    /**
     * 사은품 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoGift($arrData)
    {
        // 사은품명 체크
        if (\Component\Validator\Validator::required(gd_isset($arrData['giftNm'])) === false) {
            throw new \Exception(__('사은품명은 필수 항목입니다.'), 500);
        }

        // giftNo 처리
        if ($arrData['mode'] == 'register') {
            $arrData['giftNo'] = $this->doGiftnoInsert();
        } else {
            // giftNo 체크
            if (\Component\Validator\Validator::required(gd_isset($arrData['giftNo'])) === false) {
                throw new \Exception(__('고유키값은 필수 항목입니다.'), 500);
            }
        }
        $this->giftNo = $arrData['giftNo'];

        // 이미지 저장 경로 설정
        if (empty($arrData['imagePath'])) {
            $this->imagePath = $arrData['imagePath'] = DIR_GIFT_IMAGE . $arrData['giftNo'] . '/';
        } else {
            $this->imagePath = $arrData['imagePath'];
        }


        $files = Request::files()->toArray();
        // --- 사은품 이미지 정보 저장
        if ($arrData['imageStorage'] != 'url') {
            if (gd_file_uploadable($files['imageNm'], 'image') === true) {
                $arrData['imageNm'] = $this->imageUploadGift($files['imageNm'], $arrData['imageStorage'],$arrData['giftNo']);
            } else {
                $arrData['imageNm'] = $arrData['imageNmTemp'];
            }
        }
        if (empty($arrData['imageNm'])) {
            $arrData['imagePath'] = null;
        }

        // 브랜드 삭제 체크시 브랜드 초기화
        if ($arrData['brandCdDel'] == 'y') $arrData['brandCd'] = '';

        // 사은품 정보 저장
        $arrBind = $this->db->get_binding(DBTableField::tableGift(), $arrData, 'update');
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['giftNo']);
        $this->db->set_update_db(DB_GIFT, $arrBind['param'], 'giftNo = ?', $arrBind['bind']);
        unset($arrBind);

        if ($arrData['mode'] == 'modify') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('gift', null, 'modify', $arrData['giftNo'], $arrData['giftNm']);
        }
    }

    /**
     * 사은품 이미지 저장
     *
     * @param array $arrFileData 저장할 _FILES[image]
     * @param string $strImageStorage 저장소
     * @param string $giftNo 사은품 번호
     * @return string
     */
    public function imageUploadGift($arrFileData, $strImageStorage, $giftNo)
    {
        if ($arrFileData && $strImageStorage != 'url') {
            if (gd_file_uploadable($arrFileData, 'image')) {

                $imageExt = strrchr($arrFileData['name'], '.');
                $newImageName =  $giftNo.'_'.rand(1,100) .  $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기

                $targetImageFile = $this->imagePath . $newImageName;
                $thumbnailImageFile[] = $this->imagePath . PREFIX_GIFT_THUMBNAIL_SMALL . $newImageName;
                $thumbnailImageFile[] = $this->imagePath . PREFIX_GIFT_THUMBNAIL_LARGE . $newImageName;
                $tmpImageFile = $arrFileData['tmp_name'];

                //                $this->storageHandler->upload($tmpImageFile, $strImageStorage, $targetImageFile);
                //                $this->storageHandler->uploadThumbImage($tmpImageFile, $strImageStorage, $thumbnailImageFile[0],'50');
                //                $this->storageHandler->uploadThumbImage($tmpImageFile, $strImageStorage, $thumbnailImageFile[1],'100');
                Storage::disk(Storage::PATH_CODE_GIFT, $strImageStorage)->upload($tmpImageFile, $targetImageFile);
                Storage::disk(Storage::PATH_CODE_GIFT, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile[0], ['width' => 50]);
                Storage::disk(Storage::PATH_CODE_GIFT, $strImageStorage)->upload($tmpImageFile, $thumbnailImageFile[1], ['width' => 100]);

                // 계정용량 갱신 - 사은품
                gd_set_du('gift');

                return $newImageName;
            }
        }
    }

    /**
     * 사은품 복사
     *
     * @param array $giftNo 사은품 번호
     */
    public function setCopyGift($giftNo)
    {
        // 새로운 사은품 번호
        $newGiftNo = $this->getNewGiftno();

        // 이미지 저장소 및 이미지 경로 정보
        $strWhere = 'giftNo = ?';
        $this->db->bind_param_push($this->arrBind, 's', $giftNo);
        $this->db->strWhere = $strWhere;
        $data = $this->getGiftInfo(null, 'giftNm, imageStorage, imagePath', $this->arrBind);
        $newImagePath = DIR_GIFT_IMAGE . $newGiftNo . '/';

        if ($data['imageStorage'] != 'url') {
            // --- 이미지 복사
            //            $this->storageHandler->copy($data['imageStorage'], $data['imagePath'], $newImagePath);
            Storage::copy(Storage::PATH_CODE_GIFT, $data['imageStorage'], $data['imagePath'], $data['imageStorage'], $newImagePath);

            // 계정용량 갱신 - 사은품
            gd_set_du('gift');
        }

        $arrField = DBTableField::setTableField(
            'tableGift', null, [
            'giftNo',
            'imagePath',
        ]
        );
        $strSQL = 'INSERT INTO ' . DB_GIFT . ' (giftNo, ' . implode(', ', $arrField) . ', imagePath, regDt)
			SELECT \'' . $newGiftNo . '\', ' . implode(', ', $arrField) . ', if((isnull(imageNm) OR imageNm = \'\'), null, \'' . $newImagePath . '\') as newImagePath, now()
			FROM ' . DB_GIFT . ' WHERE giftNo = ' . $giftNo;
        $this->db->query($strSQL);
        unset($arrField);
        unset($this->arrBind);


        // 전체 로그를 저장합니다.
        $addLogData = $giftNo . ' -> ' . $newGiftNo . ' 사은품 복사' . chr(10);
        LogHandler::wholeLog('gift', null, 'copy', $newGiftNo, $data['giftNm'], $addLogData);
    }

    /**
     * 사은품 삭제
     *
     * @param array $giftNo 사은품 번호
     */
    public function setDeleteGift($giftNo)
    {
        // 이미지 저장소 및 이미지 경로 정보
        $strWhere = 'giftNo = ?';
        $this->db->bind_param_push($this->arrBind, 's', $giftNo);
        $this->db->strWhere = $strWhere;
        $data = $this->getGiftInfo(null, 'giftNm, imageStorage, imagePath', $this->arrBind);
        unset($this->arrBind);

        if ($data['imageStorage'] != 'url' && $data['imagePath']) {
            //$this->storageHandler->delete($data['imageStorage'] , $data['imagePath']);
            Storage::disk(Storage::PATH_CODE_GIFT, $data['imageStorage'])->deleteDir($data['imagePath']);

            // 계정용량 갱신 - 사은품
            gd_set_du('gift');
        }

        // 사은품 정보 삭제
        $this->db->bind_param_push($arrBind, 'i', $giftNo);
        $this->db->set_delete_db(DB_GIFT, 'giftNo = ?', $arrBind);
        unset($arrBind);

        // 전체 로그를 저장합니다.
        LogHandler::wholeLog('gift', null, 'delete', $giftNo, $data['giftNm']);
    }

    /**
     * 사은품 증정의 등록 및 수정에 관련된 정보
     *
     * @param  string $giftNo 사은품 번호
     *
     * @return array  해당 사은품 데이타
     */
    public function getDataGiftPresent($presentSno = null)
    {
        // --- 등록인 경우
        if (is_null($presentSno)) {
            // 기본 정보
            $data['mode'] = 'present_register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableGiftPresent', $data);

            // --- 수정인 경우
        } else {
            // 기본 정보
            $data = $this->getGiftPresentData($presentSno); // 사은품 기본 정보

            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            $data['mode'] = 'present_modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableGiftPresent', $data);

            if ($data['presentPermissionGroup']) {
                $data['presentPermissionGroup'] = explode(INT_DIVISION, $data['presentPermissionGroup']);
                $memberGroupName = GroupUtil::getGroupName("sno IN ('" . implode("','", $data['presentPermissionGroup']) . "')");
                $data['presentPermissionGroup'] = $memberGroupName;
            }
        }

        // --- 기본값 설정
        gd_isset($data['presentPeriodFl'], 'n');
        gd_isset($data['presentFl'], 'a');
        gd_isset($data['conditionFl'], 'a');
        // --- 기본값 설정
        gd_isset($data['stockFl'], 'n');
        gd_isset($data['scmNoNm'], '');
        gd_isset($data['addGoodsFl'], 'n');

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) {
            $data['scmFl'] = "n";
        } else {
            $data['scmFl'] = "y";
        }

        $checked = [];
        $checked['presentPermission'][$data['presentPermission']] = $checked['scmFl'][$data['scmFl']] = $checked['presentPeriodFl'][$data['presentPeriodFl']] = $checked['presentFl'][$data['presentFl']] = $checked['conditionFl'][$data['conditionFl']] = $checked['addGoodsFl'][$data['addGoodsFl']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 사은품 증정 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveInfoGiftPresent($arrData)
    {
        // 사은품 증정 제목 체크
        if (Validator::required(gd_isset($arrData['presentTitle'])) === false) {
            throw new \Exception(__('사은품 증정 제목은 필수 항목입니다.'), 500);
        }


        // --- 기본값 설정
        gd_isset($arrData['presentPeriodFl'], 'n');
        gd_isset($arrData['presentFl'], 'a');
        gd_isset($arrData['conditionFl'], 'a');
        gd_isset($arrData['addGoodsFl'], 'n');
        // 증정 조건이 무조건지급 | 금액별 지급일 경우 추가상품 수량포함은 무조건 n으로 저장
        if($arrData['conditionFl'] == 'a' || $arrData['conditionFl'] == 'p') {
            $arrData['addGoodsFl'] = 'n';
        }
        if ($arrData['presentPeriodFl'] == 'n') {
            $arrData['periodStartYmd'] = null;
            $arrData['periodEndYmd'] = null;
        }

        //구매 가능 권한 설정
        if ($arrData['presentPermission'] === 'group' && is_array($arrData['memberGroupNo'])) {
            $arrData['presentPermissionGroup'] = implode(INT_DIVISION, $arrData['memberGroupNo']);
        } else $arrData['presentPermissionGroup'] = '';


        // 구매 상품 범위에 따른 체크 및 설정
        $kindCd = [
            'g' => 'presentGoods',
            'c' => 'presentCategory',
            'b' => 'presentBrand',
            'e' => 'presentEvent',
        ];
        if ($arrData['presentFl'] != 'a') {
            $arrData['presentKindCd'] = implode(INT_DIVISION, gd_isset($arrData['presentKindCd'], $arrData[$kindCd[$arrData['presentFl']]]));
            if (is_null($arrData['presentKindCd'])) {
                switch ($arrData['presentFl']) {
                    case 'g':
                        $presentFlText   = __("상품");
                        break;
                    case 'c':
                        $presentFlText   = __("카테고리");
                        break;
                    case 'b':
                        $presentFlText   = __("브랜드");
                        break;
                    case 'e':
                        $presentFlText   = __("이벤트");
                        break;
                }
                throw new LayerNotReloadException(sprintf(__("지급 %s을(를) 선택해주세요."), $presentFlText));
            }


        }
        foreach ($kindCd as $val) {
            unset($arrData[$val]);
        }

        // 예외 조건 설정
        if (gd_isset($arrData['exceptGoods'])) {
            $arrData['exceptGoodsNo'] = implode(INT_DIVISION, $arrData['exceptGoods']);
        }
        if(in_array('goods',$arrData['presentExceptFl']) && empty($arrData['exceptGoodsNo'])  ) {
            throw new LayerNotReloadException(__("사은품 지급 예외조건의 상품을 선택해주세요."));
        }

        if (gd_isset($arrData['exceptCategory'])) {
            $arrData['exceptCateCd'] = implode(INT_DIVISION, $arrData['exceptCategory']);
        }

        if(in_array('category',$arrData['presentExceptFl']) && empty($arrData['exceptCateCd'])) {
            throw new LayerNotReloadException(__("사은품 지급 예외조건의 카테고리를 선택해주세요."));
        }

        if (gd_isset($arrData['exceptBrand'])) {
            $arrData['exceptBrandCd'] = implode(INT_DIVISION, $arrData['exceptBrand']);
        }
        if(in_array('brand',$arrData['presentExceptFl']) && empty($arrData['exceptBrandCd']) ) {
            throw new LayerNotReloadException(__("사은품 지급 예외조건의 브랜드를 선택해주세요."));
        }

        if (gd_isset($arrData['exceptEvent'])) {
            $arrData['exceptEventCd'] = implode(INT_DIVISION, $arrData['exceptEvent']);
        }
        if(in_array('event',$arrData['exceptEventCd']) && empty($arrData['exceptEvent'])) {
            throw new LayerNotReloadException(__("사은품 지급 예외조건의 이벤트를 선택해주세요."));
        }

        if(!in_array('goods',$arrData['presentExceptFl'])) {
         unset($arrData['exceptGoodsNo']);
        }

        if(!in_array('category',$arrData['presentExceptFl'])) {
            unset($arrData['exceptCateCd']);
        }

        if(!in_array('brand',$arrData['presentExceptFl'])) {
            unset($arrData['exceptBrandCd']);
        }

        // 사은품 증정 정보 저장
        if ($arrData['mode'] == 'present_modify') {
            $arrBind = $this->db->get_binding(DBTableField::tableGiftPresent(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            $this->db->set_update_db(DB_GIFT_PRESENT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableGiftPresent(), $arrData, 'insert');
            $this->db->set_insert_db(DB_GIFT_PRESENT, $arrBind['param'], $arrBind['bind'], 'y');
            $arrData['sno'] = $this->db->insert_id();
        }
        unset($arrBind);

        // 사은품 설정
        $getGift = [];
        if ($arrData['mode'] == 'present_modify') {
            $getGift = $this->getGiftPresentInfo($arrData['sno']); // 사은품 설정 정보
        }
        $i = 0;
        $gift = [];
        $arrField = DBTableField::setTableField('tableGiftPresentInfo');
        foreach ($arrData['gift']['giftSno'] as $key => $val) {
            foreach ($arrField as $fKey => $fVal) {
                if ($fKey == 0) {
                    $gift['sno'][$i] = gd_isset($val);
                    $gift[$fVal][$i] = $arrData['sno'];
                } elseif ($fKey == 1) {
                    $gift[$fVal][$i] = $i;
                } else {
                    $gift[$fVal][$i] = gd_isset($arrData['gift'][$fVal][$key]);
                }
                if (is_array($gift[$fVal][$i])) {
                    $gift[$fVal][$i] = implode(INT_DIVISION, $gift[$fVal][$i]);
                }
            }
            $i++;
        }
        // 사은품 설정 정보
        $compareGift = $this->db->get_compare_array_data($getGift, gd_isset($gift));

        // 공통 키값
        $arrDataKey = ['presentSno' => $arrData['sno']];

        // 사은품 설정 정보 저장
        $this->db->set_compare_process(DB_GIFT_PRESENT_INFO, gd_isset($gift), $arrDataKey, $compareGift);

        if ($arrData['mode'] == 'present_modify') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('gift', 'present', 'modify', $arrData['sno'], $arrData['presentTitle']);
        }
    }

    /**
     * 사은품 증정 복사
     *
     * @param array $dataSno 사은품 증정 sno
     */
    public function setCopyGiftPresent($dataSno)
    {
        // 사은품 증정 정보 복사
        $arrField = DBTableField::setTableField('tableGiftPresent');
        $strSQL = 'INSERT INTO ' . DB_GIFT_PRESENT . ' (' . implode(', ', $arrField) . ', regDt) SELECT ' . implode(', ', $arrField) . ', now() FROM ' . DB_GIFT_PRESENT . ' WHERE sno = ' . $dataSno;
        $this->db->query($strSQL);
        $presentSno = $this->db->insert_id(); // 사은품 증정 정보 sno

        // 증정 사은품 정보 복사
        $arrField = DBTableField::setTableField('tableGiftPresentInfo', null, 'presentSno');
        $strSQL = 'INSERT INTO ' . DB_GIFT_PRESENT_INFO . ' (presentSno, ' . implode(', ', $arrField) . ', regDt) SELECT \'' . $presentSno . '\', ' . implode(', ', $arrField) . ', now() FROM ' . DB_GIFT_PRESENT_INFO . ' WHERE presentSno = ' . $dataSno;
        $this->db->query($strSQL);

        // 사은품 증정 정보
        $data = $this->getGiftPresent($dataSno);

        // 전체 로그를 저장합니다.
        $addLogData = $dataSno . ' -> ' . $presentSno . ' 사은품 증정 복사' . chr(10);
        LogHandler::wholeLog('gift', 'present', 'copy', $presentSno, $data['presentTitle'], $addLogData);
    }

    /**
     * 사은품 증정 삭제
     *
     * @param array $dataSno 사은품 증정 sno
     */
    public function setDeleteGiftPresent($dataSno)
    {
        // 사은품 증정 정보
        $data = $this->getGiftPresent($dataSno);

        // 사은품 증정 정보 삭제
        $this->db->bind_param_push($arrBind, 'i', $dataSno);
        $this->db->set_delete_db(DB_GIFT_PRESENT, 'sno = ?', $arrBind);
        unset($arrBind);

        // 증정 사은품 정보 삭제
        $this->db->bind_param_push($arrBind, 'i', $dataSno);
        $this->db->set_delete_db(DB_GIFT_PRESENT_INFO, 'presentSno = ?', $arrBind);
        unset($arrBind);

        // 전체 로그를 저장합니다.
        LogHandler::wholeLog('gift', 'present', 'delete', $dataSno, $data['presentTitle']);
    }

    /**
     * 관리자 사은품 리스트를 위한 검색 정보
     */
    public function setSearchGift($searchData, $searchPeriod = 7)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableGift');

        /* @formatter:off */
        $this->search['sortList'] = [
            'regDt desc'      => __('등록일 ↓'),
            'regDt asc'     => __('등록일 ↑'),
            'giftNm asc'     => __('사은품명 ↓'),
            'giftNm desc'    => __('사은품명 ↑'),
            'companyNm asc'  => __('공급사 ↓'),
            'companyNm desc' => __('공급사 ↑'),
            'brandNm asc'    => __('브랜드 ↓'),
            'brandNm desc'   => __('브랜드 ↑'),
            'makerNm asc'    => __('제조사 ↓'),
            'makerNm desc'   => __('제조사 ↑'),
        ];
        /* @formatter:on */


        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['brandNm'] = gd_isset($searchData['brandNm']);
        $this->search['makerNm'] = gd_isset($searchData['makerNm']);
        $this->search['stockFl'] = gd_isset($searchData['stockFl'], 'all');
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], Session::get('manager.isProvider') ? 'n' : 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo'], (string) Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['brandCd'] = gd_isset($searchData['brandCd']);
        $this->search['brandCdNm'] = gd_isset($searchData['brandCdNm']);


        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }


        $this->checked['scmFl'][$searchData['scmFl']] = $this->checked['stockFl'][$this->search['stockFl']] = 'checked="checked"';
        $this->selected['sort'][$this->search['sort']] = "selected='selected'";


        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = [
                    'giftNm',
                    'giftNo',
                    'giftCd',
                ];
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(g.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldType[$keyNm], $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $this->arrWhere[] = 'g.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType[$this->search['key']], $this->search['keyword']);
            }
        }

        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 'g.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        //공급사 선택
        if ($this->search['scmFl'] != 'all') {

            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'g.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'g.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);

                $this->search['scmNo'] = [$this->search['scmNo']];
                $this->search['scmNoNm'] = [$this->search['scmNoNm']];

            }

        }


        if ($this->search['brandCd'] && $this->search['brandCdNm']) {
            $this->arrWhere[] = 'g.brandCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['brandCd'], $this->search['brandCd']);
        } else $this->search['brandCd'] = '';

        // 제조사 검색
        if ($this->search['makerNm']) {
            $this->arrWhere[] = 'g.makerNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['makerNm'], $this->search['makerNm']);
        }
        // 사은품 출력 여부 검색

        if ($this->search['stockFl'] != 'all') {
            if ($this->search['stockFl'] == 'x') {
                $stockFl = 'y';
                $this->arrWhere[] = 'g.stockFl = ? AND g.stockCnt = 0';
            } elseif ($this->search['stockFl'] == 'y') {
                $stockFl = $this->search['stockFl'];
                $this->arrWhere[] = 'g.stockFl = ? AND g.stockCnt > 0';
            } else {
                $stockFl = $this->search['stockFl'];
                $this->arrWhere[] = 'g.stockFl = ?';
            }
            $this->db->bind_param_push($this->arrBind, $fieldType['stockFl'], $stockFl);
        }
        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 사은품 리스트를 위한 검색 정보
     */
    public function setSearchGiftPresent($searchData)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableGiftPresent');


        /* @formatter:off */
        $this->search['sortList'] = [
            'regDt desc'         => __('등록일 ↓'),
            'regDt asc'        => __('등록일 ↑'),
            'presentTitle asc'  => __('사은품 증정 제목 ↓'),
            'presentTitle desc' => __('사은품 증정 제목 ↑'),
            'companyNm asc'     => __('공급사 ↓'),
            'companyNm desc'    => __('공급사 ↑'),
        ];
        /* @formatter:on */

        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['presentTitle'] = gd_isset($searchData['presentTitle']);
        $this->search['stateFl'] = gd_isset($searchData['stateFl']);
        $this->search['presentPeriodFl'] = gd_isset($searchData['presentPeriodFl']);
        $this->search['periodStartYmd'] = gd_isset($searchData['periodStartYmd']);
        $this->search['periodEndYmd'] = gd_isset($searchData['periodEndYmd']);
        $this->search['presentFl'] = gd_isset($searchData['presentFl']);
        $this->search['conditionFl'] = gd_isset($searchData['conditionFl']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], Session::get('manager.isProvider') ? 'n' : 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo'], (string) Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }


        $this->checked['scmFl'][$searchData['scmFl']] = $this->checked['presentFl'][$this->search['presentFl']] = $this->checked['stateFl'][$this->search['stateFl']] = $this->checked['presentPeriodFl'][$this->search['presentPeriodFl']] = $this->checked['conditionFl'][$this->search['conditionFl']] = 'checked="checked"';

        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";


        // 사은품 증정 제목 검색
        if ($this->search['presentTitle']) {
            $this->arrWhere[] = 'gp.presentTitle LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['presentTitle'], $this->search['presentTitle']);
        }
        // 진행상태 검색
        if ($this->search['stateFl']) {
            // 대기
            if ($this->search['stateFl'] == 's') {
                $this->arrWhere[] = 'gp.presentPeriodFl = \'y\' AND gp.periodStartYmd > \'' . date('Y-m-d') . '\'';
            } elseif ($this->search['stateFl'] == 'i') {
                $this->arrWhere[] = '(gp.presentPeriodFl = \'n\' OR (gp.periodStartYmd <= \'' . date('Y-m-d') . '\' AND gp.periodEndYmd >= \'' . date('Y-m-d') . '\'))';
            } elseif ($this->search['stateFl'] == 'e') {
                $this->arrWhere[] = 'gp.presentPeriodFl = \'y\' AND gp.periodEndYmd < \'' . date('Y-m-d') . '\'';
            }
        }
        // 구매기간 검색
        if ($this->search['presentPeriodFl']) {
            $this->arrWhere[] = 'gp.presentPeriodFl LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['presentPeriodFl'], $this->search['presentPeriodFl']);
            if ($this->search['presentPeriodFl'] == 'y') {
                $this->arrWhere[] = 'gp.periodStartYmd >= ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['periodStartYmd'], $this->search['periodStartYmd']);
                $this->arrWhere[] = 'gp.periodEndYmd <= ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['periodEndYmd'], $this->search['periodEndYmd']);
            }
        }
        // 구매 상품 범위 검색
        if ($this->search['presentFl']) {
            $this->arrWhere[] = 'gp.presentFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['presentFl'], $this->search['presentFl']);
        }
        // 사은품 증정 조건 검색
        if ($this->search['conditionFl']) {
            $this->arrWhere[] = 'gp.conditionFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['conditionFl'], $this->search['conditionFl']);
        }


        //공급사 선택
        if ($this->search['scmFl'] != 'all') {

            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'gp.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'gp.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);

                $this->search['scmNo'] = [$this->search['scmNo']];
                $this->search['scmNoNm'] = [$this->search['scmNoNm']];

            }

        }

        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 'gp.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }


        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 사은품 리스트
     *
     * @return array 사은품 리스트 정보
     */
    public function getAdminListGift($mode = null, $pageNum = 5)
    {
        $getValue = Request::get()->toArray();
        // --- 검색 설정
        $this->setSearchGift($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], $pageNum);
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        if (Session::get('manager.isProvider')) $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GIFT . ' WHERE scmNo = \'' . Session::get('manager.scmNo') . '\'';
        else $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GIFT;
        $res = $this->db->query_fetch($strSQL, null, false);
        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $join[] = ' INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' as b ON b.cateCd = g.brandCd ';
        $this->db->strJoin = implode('', $join);
        $this->db->strField = "g.*,s.companyNm as scmNm,b.cateNm as brandNm";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GIFT .' as g ' . $this->db->strJoin;
        if($this->db->strWhere) {
            $strSQL .= ' WHERE ' . $this->db->strWhere;
        }
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GIFT . ' g ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * 관리자 사은품 리스트 엑셀
     *
     * @return array 사은품 리스트 정보
     */
    public function getAdminListGiftExcel($getValue)
    {
        // --- 검색 설정
        $this->setSearchGift($getValue);

        if($getValue['giftNo'] && is_array($getValue['giftNo'])) {
            $this->arrWhere[] = 'giftNo IN (' . implode(',', $getValue['giftNo']) . ')';
        }

        $sort = 'regDt desc';

        // 현 페이지 결과
        $join[] = ' INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' as b ON b.cateCd = g.brandCd ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $this->db->strField = "g.*,s.companyNm as scmNm,b.cateNm as brandNm";
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GIFT . ' g ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * 관리자 사은품 리스트
     *
     * @return array 사은품 리스트 정보
     */
    public function getAdminListGiftPresent()
    {

        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchGiftPresent($getValue);

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

        if (Session::get('manager.isProvider')) $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GIFT_PRESENT . ' WHERE scmNo = \'' . Session::get('manager.scmNo') . '\'';
        else $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GIFT_PRESENT;
        $res = $this->db->query_fetch($strSQL, null, false);
        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        // 현 페이지 결과
        $join[] = ' INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = gp.scmNo ';
        $this->db->strJoin = implode('', $join);
        $this->db->strField = "gp.*,s.companyNm as scmNm";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GIFT_PRESENT .' as gp ' . $this->db->strJoin;
        if($this->db->strWhere) {
            $strSQL .= ' WHERE ' . $this->db->strWhere;
        }
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GIFT_PRESENT . ' gp ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }


    /**
     * 관리자 사은품 리스트
     *
     * @return array 사은품 리스트 정보
     */
    public function getAdminListGiftPresentExcel($getValue)
    {

        // --- 검색 설정
        $this->setSearchGiftPresent($getValue);

        // --- 정렬 설정
        $sort = 'regDt desc';

        if($getValue['sno'] && is_array($getValue['sno'])) {
            $this->arrWhere[] = 'sno IN (' . implode(',', $getValue['sno']) . ')';
        }

        // 현 페이지 결과
        $join[] = ' INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = gp.scmNo ';
        $this->db->strJoin = implode('', $join);
        $this->db->strField = "gp.*,s.companyNm as scmNm";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GIFT_PRESENT . ' gp ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }
    /*
     * 사은품 증정 조건 관련
     */
    public function setGiftPresentTerms($mode,$sno) {

        switch ($mode) {
            case 'goods':

                $goods   = \App::load('\\Component\\Goods\\GoodsAdmin');
                $data =  $goods->getGoodsDataDisplay($sno);

                break;
            case 'category':

                $cate  = \App::load('\\Component\\Category\\CategoryAdmin');

                $tmp['code']    = explode(INT_DIVISION, $sno);

                foreach ($tmp['code'] as $val) {
                    $tmp['name'][]    = gd_htmlspecialchars_decode($cate->getCategoryPosition($val));
                }

                $data = $tmp['name'];

                break;
            case 'brand':

                $brand    = \App::load('\\Component\\Category\\BrandAdmin');
                $tmp['code']    = explode(INT_DIVISION, $sno);
                foreach ($tmp['code'] as $val) {
                    $tmp['name'][]    = gd_htmlspecialchars_decode($brand->getCategoryPosition($val));
                }
                $data = $tmp['name'];

                break;
            case 'gift':

                // --- 사은품 정보
                $gift = \App::load('\\Component\\Gift\\GiftAdmin');

                $data = $gift->getGiftPresentInfo($sno);
                $gift->viewGiftData($data);

                break;
            case 'scm':

                // --- 공급사 정보
                $scm = \App::load('\\Component\\Scm\\ScmAdmin');

                $data = [];
                $getData = $scm->getScmSelectList($sno);
                foreach ($getData as $value) {
                    $data[] = $value['companyNm'];
                }
                unset($getData);

                break;
        }

        return $data;
    }
}
