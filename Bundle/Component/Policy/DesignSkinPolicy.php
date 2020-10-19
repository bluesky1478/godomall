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

namespace Bundle\Component\Policy;

use App;
use Bundle\Component\Board\Board;
use Bundle\Component\Board\BoardTheme;
use Component\Board\BoardAdmin;
use Component\Mall\Mall;
use Component\Validator\Validator;
use Exception;
use Framework\Utility\StringUtils;
use Globals;

/**
 * Class DesignSkinPolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class DesignSkinPolicy extends \Component\Policy\Policy
{
    const KEY = 'design.skin';
    /** @var  \Framework\Database\DBTool $db */
    protected $db;
    /** @var  \Bundle\Component\Validator\Validator $validator */
    protected $validator;
    protected $boardSkinType = 'frontLive';
    protected $boardLiveSkin;
    protected $boardMobileFl = 'n';
    protected $boardThemeField = 'themeSno';
    protected $requestPolicy;
    protected $saveDataFields = [
        'frontLive',
        'frontWork',
        'mobileLive',
        'mobileWork',
    ];


    public function __construct(array $config = [])
    {
        $this->db = is_object($config['db']) ? $config['db'] : App::load('DB');
        $this->validator = is_object($config['validator']) ? $config['validator'] : new Validator();
        if (is_object($config['storage'])) {
            parent::__construct($config['storage']);
        } else {
            parent::__construct();
        }
    }

    /**
     * 상점에 설정된 스킨을 반환하는 함수
     *
     * @param $sno
     *
     * @return mixed
     */
    public function getSkin($sno)
    {
        $policy = $this->getValue(self::KEY);

        return $policy[$sno];
    }

    /**
     * 쇼핑몰 스킨 변경 함수
     *
     * @param array $skinInfo
     *
     * @return bool
     * @throws Exception
     */
    public function saveSkin(array $skinInfo)
    {
        \Logger::debug(__METHOD__, $skinInfo);
        $this->requestPolicy = $skinInfo;
        StringUtils::strIsSet($this->requestPolicy['sno'], DEFAULT_MALL_NUMBER);

        $currentPolicy = $this->getValue(self::KEY, $this->requestPolicy['sno']);
        if ($this->hasGlobalMallDefaultSkin() === false) {
            $currentPolicy = $this->getValue(self::KEY, $this->requestPolicy['sno']);
        }
        $this->initBoardSkinByChangeSkinLive();

        $validatePolicy = $this->validateSkin();
        if($this->requestPolicy['skinUseAllFl'] == 'y') { // 사용스킨, 작업스킨 동시에 변경하는 경우
            if($this->requestPolicy['frontLive'] != null ) {
                $validatePolicy['frontWork'] = $validatePolicy['frontLive'];
            } else {
                $validatePolicy['mobileWork'] = $validatePolicy['mobileLive'];
            }
        }
        foreach ($validatePolicy as $index => $item) {
            $currentPolicy[$index] = $item;
        }

        return $this->setValue(self::KEY, $currentPolicy, $this->requestPolicy['sno']);
    }

    /**
     * 스킨 검증
     *
     * @return array
     * @throws Exception
     */
    public function validateSkin()
    {
        if (Validator::number($this->requestPolicy['sno'], null, null, true) === false) {
            throw new Exception(__('잘못된 상점번호입니다.'));
        }
        foreach ($this->saveDataFields as $item) {
            if (isset($this->requestPolicy[$item])) {
                $validatePolicy[$item] = $this->requestPolicy[$item];
                $this->validator->add($item, 'designSkinName');
            }
        }
        if ($this->validator->act($validatePolicy, true) === false) {
            throw new \Exception(implode('\n', $this->validator->errors));
        }

        return $validatePolicy;
    }

    /**
     * 기본상점 스킨설정이 존재하는지 여부 체크
     *
     * @return bool 기본상점 스킨설정이 있으면 true, 없으면  false
     */
    public function hasGlobalMallDefaultSkin()
    {
        $policy = $this->getValue(self::KEY);

        return key_exists(DEFAULT_MALL_NUMBER, $policy);
    }

    /**
     * 사용 스킨이 변경될 경우 게시판 스킨도 같이 변경하는 함수
     *
     * @throws Exception
     */
    protected function initBoardSkinByChangeSkinLive()
    {
        try {
            if($this->requestPolicy['skinUse'] == 'Work') {
                return;
            }

            if(\Globals::get('gGlobal.isUse')){
                foreach(\Globals::get('gGlobal.useMallList') as $val){
                    if($val['sno'] == $this->requestPolicy['sno']){
                        $domainPostfix = $val['domainFl'] == 'kr' ? '' : ucfirst($val['domainFl']);
                        break;
                    }
                }
            }



            if ($this->hasMobileLive()) {
                $this->boardSkinType = 'mobileLive';
                $this->boardMobileFl = 'y';
                $this->boardThemeField = 'mobileTheme'.$domainPostfix.'Sno';
            }
            else {
                $this->boardThemeField = 'theme'.$domainPostfix.'Sno';
            }

            $this->initBoardTheme($this->requestPolicy[$this->boardSkinType],$this->boardMobileFl);

            $themeInfo = [
                'liveSkin'   => $this->requestPolicy[$this->boardSkinType],
                'bdMobileFl' => $this->boardMobileFl,
            ];

            $boardChangeThemeSno = $this->selectBoardThemeSno($themeInfo);
            if (count($boardChangeThemeSno) === 0) {
                throw new Exception(__('스킨을 변경할 게시판을 찾지 못하였습니다.'), 200);
            }


            $this->updateBoardThemeSno($boardChangeThemeSno, $this->boardThemeField);
        } catch (Exception $e) {
            if ($e->getCode() === 200) {
                \Logger::info(__METHOD__ . ', ' . $e->getMessage());
            } else {
                throw new Exception(__('게시판 스킨 변경 중 오류가 발생하였습니다.'), 500, $e);
            }
        }
    }

    /**
     * 모바일사용스킨 정보가 있는지 체크
     *
     * @return bool
     */
    protected function hasMobileLive()
    {
        return isset($this->requestPolicy['mobileLive']);
    }

    /**
     * 현재 글로벌 변수에 있는 라이브 스킨과 비교
     *
     * @return bool true 변경 안됨, false 변경 됨
     */
    protected function diffSkinLiveByGlobals()
    {
        $globalName = $this->hasMobileLive() ? 'gSkin.frontSkinLive' : 'gSkin.mobileSkinLive';

        return Globals::get($globalName) == $this->requestPolicy[$this->boardSkinType];
    }

    /**
     * 게시판 스킨 리스트 조회
     *
     * @param array $info
     *
     * @return array
     */
    public function selectBoardThemeSno(array $info)
    {
        $boardAdminService = new BoardAdmin();
        $boardList = $boardAdminService->getBoardList(null, false, null, false, null);
        foreach ($boardList['data'] as $board) {
            $selectQuery = 'SELECT sno FROM ' . DB_BOARD_THEME . ' WHERE liveSkin=\'' . $info['liveSkin'] . '\'';
            $selectQuery .= ' AND bdMobileFl=\'' . $info['bdMobileFl'] . '\' AND bdKind=\'' . $board['bdKind'] . '\'';
            $selectQuery .= ' AND bdBasicFl=\'y\'';
            $resultSet = $this->db->query_fetch($selectQuery, null, false);
            $themeSno = $resultSet['sno'] ?? 0;
            $boardChangeThemeSno[$board['bdId']] = $themeSno;
        }
        return $boardChangeThemeSno;
    }

    public function initBoardTheme($skin,$isMobile = 'n'){
        $selectQuery = "SELECT *  FROM " . DB_BOARD_THEME . " WHERE liveSkin='" . $skin . "' ";
        $selectQuery .= " AND bdMobileFl='" . $isMobile . "'   AND bdBasicFl='y'";
        $result = $this->db->query_fetch($selectQuery);
        foreach($result as $row){
            $bdKind[] = $row['bdKind'];
        }
        if($bdKind){
            $notExistsKind = array_diff(array_flip(Board::KIND_LIST), $bdKind);
        }
        else {
            $notExistsKind =array_flip(Board::KIND_LIST);
        }
//        debug($notExistsKind,true);
        if($notExistsKind){
            foreach($notExistsKind as $kind){
                $query="INSERT INTO es_boardTheme (themeId, themeNm, liveSkin, bdBasicFl, bdKind, bdAlign, bdWidth, bdWidthUnit, bdListLineSpacing,bdMobileFl,regDt) VALUES ('".$kind."', '".BoardTheme::getKindText($kind)."', '".$skin."', 'y', '".$kind."', 'center', 100, '%', 10,'".$isMobile."',now())";

                $this->db->query($query);
            }
        }
    }

    /**
     * 게시판 스킨 변경
     *
     * @param array  $changeThemeSno
     * @param string $field
     *
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function updateBoardThemeSno(array $changeThemeSno, $field = 'themeSno')
    {
        foreach ($changeThemeSno as $bdId => $themeSno) {
            $updateQuery = 'UPDATE ' . DB_BOARD . ' SET ' . $field . ' = ' . $themeSno . ' WHERE bdId=\'' . $bdId . '\'';
            $this->db->query($updateQuery);
        }
    }

    /**
     * @param mixed $requestPolicy
     */
    public function setRequestPolicy($requestPolicy)
    {
        $this->requestPolicy = $requestPolicy;
    }
}
