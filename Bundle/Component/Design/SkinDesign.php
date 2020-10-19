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

namespace Bundle\Component\Design;

use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Component\Storage\Storage;
use Component\File\SafeFile;
use Component\Mall\Mall;
use Component\Page\Page;
use App;
use Globals;
use Request;
use Message;
use UserFilePath;
use FileHandler;
use DirectoryIterator;

/**
 * 디자인 페이지 관리 클래스
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SkinDesign extends \Component\Design\SkinBase
{
    // 디자인 히스토리 화일의 개수
    private $designHistoryFile = 10;

    // div__div (구분자)
    private $treeIdDivision = '__';

    /**
     * 폴더 정보
     * @param  string $dirPath 디자인페이지의 폴더
     * @return array  폴더 정보
    */
    private function _getDesignDir($dirPath)
    {
        // 스킨 정보
        if (empty($this->skinPath) === true) {
            $this->setSkin(Globals::get('gSkin.' . $this->skinType . 'SkinWork'));
        }

        // 디자인 폴더 경로
        $tmp = explode('/', $dirPath);
        $tmpCnt = count($tmp);

        // 디자인 폴더 기본 설정 정보
        include $this->skinConfigDir.'default_skin_tree.php';
        if ($tmpCnt === 1) {
            if (isset($defaultSkinTree[$dirPath]) === true) {
                $dirInfo = ['type' => 'dir', 'name' => $dirPath, 'path' => $tmp[0], 'text' => $defaultSkinTree[$dirPath]['_text_'], 'form_type' => $defaultSkinTree[$dirPath]['_type_']];
            } else {
                $dirInfo = ['type' => 'dir', 'name' => $dirPath, 'path' => $tmp[0], 'text' => $dirPath, 'form_type' => 'file'];
            }
        } else {
            $tmpArr = [];
            $tmpDir = [];
            for ($i = 0; $i <= ($tmpCnt-2); $i++) {
                $dirName = $tmp[$i];
                if ($i === 0) {
                    $tmpArr = gd_isset($defaultSkinTree[$dirName]);
                } else {
                    $tmpArr = gd_isset($tmpArr[$dirName]);
                }
                $tmpDir[] = $dirName;
            }
            $dirInfo = ['type' => 'dir', 'name' => $dirName, 'path' => implode('/', $tmpDir), 'text' => $tmpArr['_text_'], 'form_type' => $tmpArr['_type_']];
        }

        return $dirInfo;
    }

    /**
     * 디자인 페이지 정보
     * @param  string $designPage 디자인 페이지
     * @return array 폴더 정보
     * @throws \Exception
     */
    private function _getDesignPage($designPage)
    {
        // 스킨 정보
        if (empty($this->skinPath) === true) {
            $skin = Globals::get('gSkin.' . $this->skinType . 'SkinWork') ?? gd_policy('design.skin', 1)[$this->skinType.'Work'];
            $this->setSkin($skin);
        }

        // 디자인 페이지의 실제 경로 및 디자인 페이지 체크
        if ($designPage === 'default') {
            $designPagePath = '';
        } else {
            // 디자인 페이지의 실제 경로
            $designPagePath = $this->skinPath->add($designPage);

            // 디자인 페이지 체크
            if (is_file($designPagePath) === false && $designPage !== 'default') {
                throw new \Exception(sprintf(__('%s 파일이 없습니다.'), '"' . $designPage . '"'));
            }
        }

        // 디자인 페이지의 정보
        $getInfo = ['type' => 'file', 'name' => basename($designPagePath), 'size' => filesize($designPagePath), 'date' => filemtime($designPagePath)];

        // 디자인 페이지 설정 정보
        $pageInfo = [];
        $skin = Globals::get('gSkin.' . $this->skinType . 'SkinWork') ?? gd_policy('design.skin', 1)[$this->skinType.'Work'];
        $skinConfig = $this->getSkinConfig($skin, 'page');
        if (isset($skinConfig[$designPage]) === true) {
            $pageInfo = $skinConfig[$designPage];
        } else {
            $pageInfo['text'] = '';
            $pageInfo['linkurl'] = '';
        }
        unset($skinConfig);

        if ($designPage !== 'default') {
            if (empty($pageInfo['text']) === true || empty($pageInfo['linkurl']) === true) {
                $fileInfo = $this->_getDesignPageHeaderInfo($designPagePath);
                if ($pageInfo['text'] === '') {
                    $pageInfo['text'] = trim($fileInfo[0]);
                }
                if ($pageInfo['linkurl'] === '') {
                    $pageInfo['linkurl'] = trim($fileInfo[1]);
                }
            }
        }

        if (is_array($pageInfo) === true) {
            $getInfo = array_merge($getInfo, $pageInfo);
        }

        return $getInfo;
    }

    /**
     * 디자인 페이지 해더 정보
     * @param  string $designPagePath 디자인 페이지 경로
     * @return array  해더 정보
    */
    private function _getDesignPageHeaderInfo($designPagePath)
    {
        // 화일의 내용을 읽어오기 (화일 설명과 화일명 추출)
        $fd = fopen($designPagePath, 'r');
        $contents = fread($fd, 150);
        fclose($fd);
        preg_match('/\{\*\*\*( .*)\*\*\*\}/i', $contents, $matches);

        // 설명이 있는 경우
        if (isset($matches[1]) === true) {
            $fileInfo = explode('|', $matches[1]);
            $fileInfo[0] = trim(gd_isset($fileInfo[0]));
            $fileInfo[1] = trim(gd_isset($fileInfo[1]));
        } else {
            $fileInfo[0] = '';
            $fileInfo[1] = '';
        }

        return $fileInfo;
    }

    /**
     * 디자인 페이지 정보
     * @param  string $designPage 디자인 페이지
     * @param  array $pageInfo 디자인 페이지
     * @param  array $dirInfo 디자인 페이지
     * @return array  디자인 페이지 정보
    */
    public function getDesignPageInfo($designPage, $pageInfo = null, $dirInfo = null)
    {
        // 디자인 페이지 정보
        if (empty($pageInfo) === true) {
            $pageInfo = $this->_getDesignPage($designPage);
        }

        // 디자인 폴더 정보
        if (empty($dirInfo) === true) {
            $dirInfo = $this->_getDesignDir($designPage);
        }

        // 디자인 폴더 기본 정보
        $defaultInfo = $this->_getDesignPage('default');

        // 화일의 기본 form_type
        if (empty($dirInfo['form_type'])=== true) {
            $pageInfo['form_type'] = 'file';
        } else {
            $pageInfo['form_type'] = $dirInfo['form_type'];
        }

        // 전체레이아웃 설정인경우
        if ($designPage === 'default') {
            $pageInfo['form_type'] = 'default';
        }

        // 내용에 header 여부에 따른 처리
        if ($pageInfo['form_type'] === 'file' || $pageInfo['form_type'] === 'inc') {
            // 디자인 페이지의 실제 경로
            $designPagePath = $this->skinPath->add($designPage);
            if (file_exists($designPagePath)) {
                $source = file_get_contents($designPagePath);
                if (preg_match("/\{ *# *header *\}/is", $source)) {
                    $pageInfo['form_type'] = 'file';
                } else {
                    $pageInfo['form_type'] = 'inc';
                }
            }
        }

        // 상단/하단/측면디자인 파일목록정의
        if ($pageInfo['form_type'] === 'outSection') {
            unset($pageInfo['outline_header']);
            unset($pageInfo['outline_side']);
            unset($pageInfo['outline_footer']);
        }

        $layout = ['header' => [], 'side' => [], 'footer' => []];
        // __('상단감춤')
        // __('측면감춤')
        // __('하단감춤')
        $hidenm = ['header' => '상단감춤', 'side' => '측면감춤', 'footer' => '하단감춤'];
        if ($pageInfo['form_type'] != 'inc') {
            foreach ($layout as $k => $v) {
                $sFile = gd_isset($pageInfo['outline_' . $k]);
                if ($pageInfo['form_type'] === 'file' || $pageInfo['form_type'] === 'outSection') {
                    $sDefault = $defaultInfo['outline_' . $k];
                }

                $opt = &$layout[$k];
                if ($pageInfo['form_type'] === 'default' || $pageInfo['form_type'] === 'file') {
                    $opt[0] = [
                        'text' => $hidenm[$k],
                        'value' => 'noprint',
                        'selected' => ($sFile === 'noprint' ? 'selected="selected"' : ''),
                        ];
                    if ($pageInfo['form_type'] === 'file') {
                        if ($sDefault === 'noprint') {
                            $opt[0]['text'] .= ' ⓑ';
                            $opt[0]['value'] = 'default';
                            $opt[0]['selected'] = ('' === $sFile ? 'selected="selected"' : $opt[0]['selected']);
                        }
                    }
                }

                $dirPath = 'outline/' . $k . '/';
                $ls = $this->_getDirList($dirPath);
                foreach($ls as $file) {
                    $tmp = [
                        'text' => ($file['text'] . ' - ' . $dirPath . $file['name']),
                        'value' => ($dirPath . $file['name']),
                        'selected' => ($sFile === $dirPath . $file['name'] ? 'selected="selected"' : ''),
                        'path' => ($dirPath . $file['name']),
                        ];
                    if ($pageInfo['form_type'] === 'file' || $pageInfo['form_type'] === 'outSection') {
                        if ($tmp['value'] === $sDefault) {
                            $tmp['text'] .= ' ⓑ';
                            $tmp['value'] = 'default';
                            $tmp['selected'] = (empty($sFile) === true ? 'selected="selected"' : $tmp['selected']);
                        }
                    }
                    if ($pageInfo['form_type'] === 'outSection') {
                        if (strpos($designPage, $dirPath) !== false) {
                            $tmp['selected'] = ($designPage === ($dirPath . $file['name']) ? 'selected="selected"' : '');
                        }
                    }
                    $opt[] = $tmp;
                }
                unset($opt);
            }
        }

        // 측면디자인 영역 위치
        $sidefloat = [];
        // __('왼쪽')
        // __('오른쪽')
        $floatnm= ['left' => '왼쪽', 'right' => '오른쪽'];
        if (in_array(gd_isset($pageInfo['outline_sidefloat']), array_keys($floatnm))) {
            $check = gd_isset($pageInfo['outline_sidefloat']);
        } else {
            $check = gd_isset($defaultInfo['outline_sidefloat']);
        }
        $pageInfo['sidefloat'] = $check;
        $checked[$check] = 'checked';
        foreach ($floatnm as $k => $v) {
            $tmp = [
                'text' => $v,
                'value' => $k,
                'checked' => gd_isset($checked[$k]),
                'float' => $k,
                ];

            if ($pageInfo['form_type'] === 'file') {
                if ($tmp['value'] === $defaultInfo['outline_sidefloat']) {
                    $tmp['text'] .= 'ⓑ';
                    $tmp['value'] = 'default';
                    $tmp['checked'] = $checked[$k];
                }
            }
            $sidefloat[] = $tmp;
        }

        // 해당 화일의 제목 처리
        if ($designPage !== 'default') {
            if (empty($dirInfo['text']) === false) {
                $fileText = $dirInfo['text'] . '&nbsp;▶&nbsp;' . $pageInfo['text'] . '&nbsp;&nbsp;I&nbsp;&nbsp;' . $designPage;
            } else {
                $fileText = '▶&nbsp;' . $dirInfo['name'] . '&nbsp;&nbsp;I&nbsp;&nbsp;' . $pageInfo['name'];
            }
        } else {
            $fileText = '';
        }

        $designInfo    = [];
        $designInfo['file'] = $pageInfo;
        $designInfo['dir'] = $dirInfo;
        $designInfo['layout'] = $layout;
        $designInfo['sidefloat'] = $sidefloat;
        $designInfo['fileText'] = $fileText;

        return $designInfo;
    }

    public function setDesignPageUrl($designPage, $skinType = null)
    {
        // 페이지 주소가 없으면 false
        if (empty($designPage) === true) {
            return false;
        }

        // skinType 이 없는 경우 현재 skinType
        if (empty($skinType) === true) {
            $skinType = $this->skinType;
        }

        // 페이지 주소를 / 로 구분함
        $designDiv = explode('/', str_replace(['.html', '.php'], '', $skinType . '/' . $designPage));

        $setData = [];
        foreach ($designDiv as $val) {
            $setData[] = StringUtils::strToCamel($val);
        }

        $designPageController = SYSSRCPATH . '/Bundle/Controller/' . implode('/', $setData) . 'Controller.php';

        // skinType 에 따른 주소 처리
        if ($skinType === 'front') {
            $urlPrefix = URI_HOME;
        } else {
            $urlPrefix = URI_MOBILE;
        }
        if (empty(\Session::has('mallSno')) === false && \Session::get('mallSno') > DEFAULT_MALL_NUMBER) {
            $mall = new Mall();
            $data = $mall->getMall(\Session::get('mallSno'), 'sno');
            $urlPrefix .= $data['domainFl'] . '/';
        }

        // 해당 controller 가 있는지 체크
        if (is_file($designPageController) === true) {
            $designPageUrl = $urlPrefix . str_replace('.html', '.php', $designPage);
        } else {
            $designPageUrl = $urlPrefix . 'main/html.php?htmid='.$designPage;
        }

        // 작업스킨과 사용스킨 체크
        if (Globals::get('gSkin.' . $skinType . 'SkinLive') !== Globals::get('gSkin.' . $skinType . 'SkinWork')) {
            $check = parse_url($designPageUrl);
            if (isset($check['query']) === true) {
                $designPageUrl = $designPageUrl . '&myShop';
            } else {
                $designPageUrl = $designPageUrl . '?myShop';
            }
        }

        return $designPageUrl;
    }

    /**
     * 디자인 페이지 미리보기 URL 정보
     * @param  string $formType 디자인 폴 타입
     * @param  string $designPage 디자인 페이지
     * @return array  디자인 페이지 미리보기 URL 정보
    */
    public function getDesignPageUrl($formType, $designPage)
    {
        $isPreview = false;
        $realLinkUrl = '';

        // formType 에 따른 미리보기와 링크 URL
        if ($formType === 'file') {
            $realLinkUrl = $this->setDesignPageUrl($designPage);
            if (empty($realLinkUrl) === false) {
                $isPreview = true;
            }

            if (preg_match('/goods\/goods_view\.html/i', $designPage)) {
                // 스킨 타입에 따른 처리
                $mobileFl = false;
                if ($this->skinType === 'mobile') {
                    $mobileFl = true;
                }

                // 상품 정보
                $goods = \App::load('\\Component\\Goods\\Goods');
                $goodsNo = $goods->getGoodsNoExtract('last', $mobileFl);
                // 상품 링크 정보
                $realLinkUrl .= '?goodsNo=' . $goodsNo;
            }
        } elseif ($formType === 'inc') {
            // html 만 체크
            $checkHtml = explode('.', $designPage);
            $checkHtml = array_pop($checkHtml);
            if ($checkHtml === 'html') {
                $realLinkUrl = $this->setDesignPageUrl($designPage);
                if (empty($realLinkUrl) === false) {
                    $isPreview = true;
                }
            }
        } elseif ($formType === 'outSection') {
            $skinConfig = $this->getSkinConfig(Globals::get('gSkin.' . $this->skinType . 'SkinWork'), 'page');
            foreach ($skinConfig as $key => $val) {
                if ($key == 'default' || preg_match('/^outline\//', $key) || !gd_isset($val['linkurl'])) {
                    continue;
                }
                if (gd_isset($val['outline_header']) == '') {
                    $val['outline_header'] = $skinConfig['default']['outline_header'];
                }
                if (gd_isset($val['outline_side']) == '') {
                    $val['outline_side'] = $skinConfig['default']['outline_side'];
                }
                if (gd_isset($val['outline_footer']) == '') {
                    $val['outline_footer'] = $skinConfig['default']['outline_footer'];
                }
                foreach ($val as $key2 => $val2) {
                    if (!preg_match('/^outline_/', $key2)) {
                        continue;
                    }
                    if ($val2 == $designPage) {
                        $realLinkUrl = $this->setDesignPageUrl($val['linkurl']);
                        if (empty($realLinkUrl) === false) {
                            $isPreview = true;
                        }
                        break 2;
                    }
                }
            }
            unset($skinConfig);
        } elseif ($formType === 'outline') {
            $skinConfig = $this->getSkinConfig(Globals::get('gSkin.' . $this->skinType . 'SkinWork'), 'page');
            $realLinkUrl = $this->setDesignPageUrl(gd_isset($skinConfig[$designPage]['linkurl']));
            if (empty($realLinkUrl) === false) {
                $isPreview = true;
            }
            unset($skinConfig);
        }

        $designUrl = [];
        $designUrl['isPreview'] = $isPreview;
        $designUrl['realLinkurl'] = $realLinkUrl;

        return $designUrl;
    }

    /**
     * 폴더내 디자인 페이지 정보
     * @param string $dirPath 해당 페이지 주소
     * @param boolean $removeExt return key에 확장자 제외 여부
     * @return array
     */
    public function getDirDesignPageInfo($dirPath, $removeExt = false)
    {
        // 페이지 정보
        $getPageInfo = [];

        // 정의된 폴더내 실제 정보
        $getPath = $this->skinPath->add($dirPath);
        if (is_dir($getPath) === true) {
            foreach (new DirectoryIterator($getPath) as $fileInfo) {
                // 화일 정보 수집
                if ($fileInfo->isDot() === false && $fileInfo->getType() === 'file') {
                    // 허용된 확장자인 경우
                    if (in_array($fileInfo->getExtension(), $this->useFileExt) === true) {
                        if ($removeExt === true) {
                            $keyTmp = '.' . $fileInfo->getExtension();
                            $keyName = $fileInfo->getBasename($keyTmp);
                        } else {
                            $keyName = $fileInfo->getBasename();
                        }

                        // 화일의 내용을 읽어오기 (화일 설명과 화일명 추출)
                        $getPageInfo[$keyName] = $this->_getDesignPageHeaderInfo($fileInfo->getPathname());
                        $getPageInfo[$keyName][] = $fileInfo->getFilename();
                    }
                }
            }
        }

        return $getPageInfo;
    }

    /**
     * 폴더 내용
     * @param null $dirPath
     * @return array 폴더 내용
     * @internal param string $dirpath 스킨이름(폴더명)
     */
    public function getDirList($dirPath = null)
    {
        // 해당 폴더의 폴더 및 화일 정보
        $getData = $this->_getDirList($dirPath);

        // 폴더 정보만 추출
        $setData = [];
        foreach ($getData as $dVal) {
            if ($dVal['type'] === 'dir') {
                $setData[$dVal['name']] = '[' . $dVal['name'] . '] - ' . $dVal['text'];
            }
        }
        return $setData;
    }
    /**
     * 폴더 내용
     * @param null $dirPath
     * @return array 폴더 내용
     * @internal param string $dirpath 스킨이름(폴더명)
     */
    private function _getDirList($dirPath = null)
    {
        $arr = ['dir' => [], 'file' => []];

        if (empty($dirPath) === true) {
            $dirSkinPath = $this->skinPath;
        } else {
            $dirSkinPath = $this->skinPath->add($dirPath);
        }

        if (is_dir($dirSkinPath) === false) {
            return [];
        }

        foreach (new DirectoryIterator($dirSkinPath) as $fileInfo) {
            if ($fileInfo->isDot() === true) {
                continue;
            }

            if (empty($dirPath) === true) {
                $designPage = $fileInfo->getFilename();
            } else {
                $designPage = $dirPath . '/' . $fileInfo->getFilename();
            }

            $checkFile = realpath($dirSkinPath . DS . $fileInfo->getFilename());

            // 허용된 폴더가 아닌 경우
            if ($fileInfo->getType() === 'dir' && in_array($fileInfo->getFilename(), $this->exceptDir)) {
                continue;
            }

            // 허용된 확장자가 아닌 경우
            if ($fileInfo->getType() === 'file' && in_array($fileInfo->getExtension(), $this->useFileExt) === false) {
                continue;
            }

            // 제외 화일인 경우
            if (in_array($fileInfo->getFilename(), $this->exceptFile)) {
                continue;
            }

            if (filetype($checkFile) == 'dir') {
                $arr['dir'][] = $this->_getDesignDir($designPage);
            } else {
                $arr['file'][] = $this->_getDesignPage($designPage);
            }
        }

        // 정렬
        $result = [];
        foreach ($arr as $b_key => $b_arr) {
            if (count($b_arr) <= 1) {
                $result[$b_key] = $b_arr;
                continue;
            }

            $tmp = [];
            // 임시 공간에 저장
            foreach ($b_arr as $s_key => $s_arr) {
                $tmp[$s_key] = strtolower($s_arr[ 'name' ]);
            }

            asort($tmp);
            reset($tmp);

            // 리턴 공간으로 데이타 이전
            foreach ($tmp as $k => $v) {
                $result[$b_key][] = $arr[$b_key][$k];
            }
        }

        // 병합
        $arr = [];
        foreach ($result as $b_arr){
            if (count($b_arr)) {
                $arr = array_merge($arr, $b_arr);
            }
        }

        return $arr;
    }

    /**
     * 디자인 트리 데이타
     * @param null $getTreeId
     * @return array
     * @throws \Exception
     * @internal param string $id jsTree에서 부모ID
     */
    public function getDesignTreeData($getTreeId = null)
    {
        // 결과값
        $getTreeData = [];

        // 구분자
        $divs = [];
        if ($getTreeId) {
            $divs = explode($this->treeIdDivision, $getTreeId);
        }

        // 현재 클릭된 폴더
        $getDir = implode('/', $divs);

        $configDirs = [];
        $treeDirs = [];
        $chkDirs = [];

        // 디자인 폴더 기본 설정 정보
        include $this->skinConfigDir.'default_skin_tree.php';
        if (count($divs) === 0) {
        	$configDirs = $defaultSkinTree;
        } elseif (count($divs) === 1) {
        	$configDirs = gd_isset($defaultSkinTree[$divs[0]]);
        } elseif (count($divs) === 2) {
        	$configDirs = gd_isset($defaultSkinTree[$divs[0]][$divs[1]]);
        }

        // 현재 선택한 정의된 스킨 정보
        if (empty($configDirs) === false) {
            foreach ($configDirs as $name => $node) {
                if (substr($name, 0, 1) === '_' && substr($name, -1, 1) === '_') {
                    unset($configDirs[$name]);
                    continue;
                }
                $treeDirs[$name] = $node['_text_'];
            }
        }

        // 현재 선택한 폴더내 실제 정보
        $jsonDirData = [];
        $jsonFileData = [];
        $selectedPath = $this->skinPath->add($getDir);
        if (is_dir($selectedPath) === true) {
            foreach (new DirectoryIterator($selectedPath) as $fileInfo) {
                if ($fileInfo->isDot() === true) {
                    continue;
                }

                // 폴더 정보 수집
                if ($fileInfo->getType() === 'dir' && in_array($fileInfo->getFilename(), $this->exceptDir) === false) {
                    $chkDirs[] = $fileInfo->getFilename();
                    if (empty($treeDirs[$fileInfo->getFilename()]) === true) {
                        $treeDirs[$fileInfo->getFilename()] = $fileInfo->getFilename();
                    }
                }

                // 화일 정보 수집
                if ($fileInfo->getType() === 'file') {
                    // 허용된 확장자인 경우
                    if (in_array($fileInfo->getExtension(), $this->useFileExt) === true) {
                        // 화일의 내용을 읽어오기 (화일 설명과 화일명 추출)
                        $fileHeader = $this->_getDesignPageHeaderInfo($fileInfo->getPathname());

                        // 디자인 페이지
                        $designPage = (empty($getDir) === false ? $getDir . '/' : '') . $fileInfo->getFilename();

                        // 데이터 정의
                        $node_divs = $divs;
                        array_push($node_divs, $fileInfo->getFilename());
                        if (empty($fileHeader[0]) === false) {
                            $treeText = $fileHeader[0] . '<br />';
                            $treeText .= '<span class="tip2">'.$designPage.'</span>';
                        } else {
                            $treeText = '<span class="tip2 bold">'.$fileInfo->getFilename().'</span>';
                        }

                        $lastNode = array_pop($node_divs);
                        $jsonFileData[$lastNode] = [
                            'attributes' => [
                                'id' => implode($this->treeIdDivision, $node_divs)
                                , 'source' => true
                                , 'rel' => 'designPage'
                                , 'linkType' => 'designPage'
                                , 'linkId' => $designPage,
                            ]
                            , 'data' => [
                                'title' => $treeText
                                , 'attributes' => [
                                    'class' => 'designPage',
                                ],
                            ],
                        ];
                        unset($lastNode);
                    }
                }
            }

            // 배열 파일명으로 재정렬
            ksort($jsonFileData);
            $tmpJsonFileData = $jsonFileData;
            $jsonFileData = [];
            foreach($tmpJsonFileData as $value) {
                $jsonFileData[] = $value;
            }

            // 미리 정의된 폴더와 실제 폴더와 비교를 해서. 실제 폴더를 가지고 옴
            foreach ($treeDirs as $key => $val) {
                if (in_array($key, $chkDirs) === false) {
                    unset($treeDirs[$key]);
                }
            }
        } else {
            throw new \Exception(sprintf(__('%s 폴더가 없습니다.'), '"' . $getDir . '"'));
        }

        // 폴더 이름으로 정렬
        ksort($treeDirs);

        // 폴더 데이터
        foreach ($treeDirs as $name => $text) {
            // 데이터 정의
            $node_divs = $divs;
            array_push($node_divs, $name);

            $treeText = $text;
            $treeText .= '<span class="tip1">/' . $name . '</span>';
            $jsonDirData = [
                'attributes' => [
                    'id' => implode($this->treeIdDivision, $node_divs)
                    , 'source' => false
                    , 'rel' => 'directory'
                    , 'linkId' => implode('/', $node_divs),
                ]
                , 'data' => $treeText,
            ];

            $jsonDirData['state'] = 'closed';
            $jsonDirData['children'] = [];
            array_push($getTreeData, $jsonDirData);
        }

        // 파일 데이터
        if (empty($jsonFileData) === false) {
            array_push($getTreeData, $jsonFileData);
        }

        return $getTreeData;
    }

    /**
     * 디자인 페이지 히스토리 화일 정보
     * @param  string $designPage 디자인 페이지
     * @return array
     */
    public function getDesignHistoryFile($designPage)
    {
        // 스킨 정보
        if (empty($this->skinName) === true) {
            $this->setSkin(Globals::get('gSkin.' . $this->skinType . 'SkinWork'));
        }

    	$savedDir = dirname($designPage);
    	$savedFile = basename($designPage);
    	$historyTempPath = UserFilePath::temporary('skin_history', $this->skinType, $this->skinName)->getRealPath();
    	$historyRealPath = $historyTempPath . DS . $savedDir;
		$historyHomePath = '/' . $this->skinType . '/' . $this->skinName;
		$historyFile = [];

		if (is_dir($historyRealPath) === false) {
			return $historyFile;
		}

		foreach (new DirectoryIterator($historyRealPath) as $fileInfo) {
			if ($fileInfo->isDot() === true) {
				continue;
			}

			if (preg_replace('/^Hx[0-9]*_/', '', $fileInfo->getFilename()) === $savedFile) {
				preg_match('/Hx([^_]*)_/', $fileInfo->getFilename(), $hx);
				$historyFile[$fileInfo->getFilename()]['path'] = $historyHomePath . '/' . $savedDir . '/' . $fileInfo->getFilename();
				$historyFile[$fileInfo->getFilename()]['date'] = date('Y-m-d H:i:s', $hx[1]);
			}
		}

		krsort($historyFile);

    	return $historyFile;
    }

    /**
     * 디자인 페이지 저장
     * @param  string $saveMode mode (save, saveas, create)
     * @return array
     * @throws \Exception
     */
    public function saveDesignPageInfo($saveMode)
    {
        $logger = \App::getInstance('logger')->channel('design');
        $designPagePreview = Request::post()->get('designPagePreview');
        $designPage = Request::post()->get('designPage');

        $setData = [];
        $setData['designPagePreview'] = $designPagePreview;
        $setData['designPage'] = $designPage;
        $setData['linkurl'] = Request::post()->get('linkurl');

        // 새이름으로 저장 시 linkurl이 변경 되지 않아 연결페이지리스트에서 중복처럼 보여 linkurl값 변경처리
        if($saveMode == 'saveas'){
            $setData['linkurl'] = $designPage;
        }
        $setData['content'] = Request::post()->get('content');
        $setData['text'] = Request::post()->get('text');
        $setData['current_page'] = Request::post()->get('current_page');

        // 모바일 페이지 연결 url 디렉토리 체크 후 예외처리
        //$type = ['us', 'cn', 'jp'];
        //$arrConnectPage = array_values(array_filter(explode('/', Request::post()->get('connectPage'))));
        //if(array_intersect($type, $arrConnectPage)){
        //if(in_array('us', $arrConnectPage) || in_array('cn', $arrConnectPage) || in_array('jp', $arrConnectPage)){

        $connectPage = array_values(array_filter(explode('/', Request::post()->get('connectPage'))))[0];
        $session = \App::getInstance('session');
        $mallSno = $session->get('mallSno');

        if($mallSno == 1){
            if($connectPage == 'us' || $connectPage == 'cn' || $connectPage == 'jp'){
                throw new \Exception(__('작업스킨과 동일한 기준몰 상점의 모바일 페이지만 등록 가능합니다.'));
            }
        }else if($mallSno == 2){
            if($connectPage == 'cn' || $connectPage == 'jp'){
                throw new \Exception(__('작업스킨과 동일한 영문몰 상점의 모바일 페이지만 등록 가능합니다.'));
            }
        }else if($mallSno == 3){
            if($connectPage == 'us' || $connectPage == 'jp'){
                throw new \Exception(__('작업스킨과 동일한 중문몰 상점의 모바일 페이지만 등록 가능합니다.'));
            }
        }else if($mallSno == 4){
            if($connectPage == 'us' || $connectPage == 'cn'){
                throw new \Exception(__('작업스킨과 동일한 일문몰 상점의 모바일 페이지만 등록 가능합니다.'));
            }
        }

        // 모바일 페이지 연결
        $setData['connectFl'] = Request::post()->get('connectFl');
        $setData['connectPage'] = Request::post()->get('connectPage');

        if(Request::post()->get('connectFl') == 'n'){
            $setData['connectPage'] = '';
        }else{
            if(empty(Request::post()->get('connectPage'))){
                $setData['connectFl'] = 'n';
            }
        }

        // form_type 이 'outline', 'outSection' 인경우에는 맵정보를 처리하지 않음
        if (in_array(Request::post()->get('form_type'), ['outline', 'outSection']) === false ) {
	        $setData['outline_header'] = Request::post()->get('outline_header');
	        $setData['outline_side'] = Request::post()->get('outline_side');
	        $setData['outline_footer'] = Request::post()->get('outline_footer');
	    }

        $setData['outline_sidefloat'] = Request::post()->get('outline_sidefloat');
        $setData['outbg_color'] = Request::post()->get('outbg_color');
        $setData['inbg_color'] = Request::post()->get('inbg_color');

        $setData['outbg_img'] = Request::post()->get('outbg_img');
        $setData['outbg_img_del'] = Request::post()->get('outbg_img_del');
        $setData['inbg_img'] = Request::post()->get('inbg_img');
        $setData['inbg_img_del'] = Request::post()->get('inbg_img_del');

        $logger->info(__METHOD__ . ', connectFl => ["'. $setData['connectFl'] .'"], connectPageUrl => ["'. $setData['connectPage'] .'"]');

        // 디자인 파일 저장
        if ($designPage !== 'default') {
			if($this->saveDesignPage($saveMode, $setData) === false) {
				throw new \Exception(__('디자인 페이지 저장시 오류가 발생되었습니다.'));
			}
	    }

        // 새이름으로 저장일때 선처리
        if ($saveMode === 'saveas') {
            $setData['outbg_img'] = '';
            $setData['outbg_img_del'] = '';
            $setData['inbg_img'] = '';
            $setData['inbg_img_del'] = '';
        }

        // storageHandler : 저장소 세팅
        if ($this->skinType === 'front') {
            $storage = Storage::disk(Storage::PATH_CODE_FRONT_SKIN_CODI);
        } else if ($this->skinType === 'mobile') {
            $storage = Storage::disk(Storage::PATH_CODE_MOBILE_SKIN_CODI);
        } else {
            throw new \Exception(__('이미지 파일 저장시 오류가 발생되었습니다.'));
        }

        // 이미지 접두사
        $prefixFileNm = preg_replace(["'.html$'si", "'/'si"], ['', '.'], $designPage);

        // 배경이미지 저장
        $checkFile = ['outbg_img' => 'outbg_img_up', 'inbg_img' => 'inbg_img_up'];
        foreach ($checkFile as $fKey => $fVal) {
            if (Request::files()->get($fVal)['error'] == 0 && Request::files()->get($fVal)['size'] > 0) {
                // 이미지명
                $setData[$fKey] = $prefixFileNm . '_' . str_replace('_img_up', '', $fVal) . strtolower(strrchr(Request::files()->get($fVal)['name'], '.'));

                // 이미지 화일 저장
                $result = $storage->upload(Request::files()->get($fVal)['tmp_name'],  $setData[$fKey]);
                /*
                 * @todo : storageHandler 를 이용했을때 이미지 저장 후 결과값 처리
                 */
                if ($result === false) {
                    throw new \Exception(__('이미지 파일 저장시 오류가 발생되었습니다.'));
                }
            }
        }

        // 이미지 삭제인 경우
        $checkDel = ['outbg_img_del' => 'outbg_img', 'inbg_img_del' => 'inbg_img'];
        foreach ($checkDel as $dKey => $dVal) {
			if ($setData[$dKey] === 'Y') {
                $storage->delete($setData[$dVal]);
                unset($setData[$dVal]);
			}
        }

        // 디자인스킨파일 저장
        if ($designPagePreview !== 'y') {
            // 스킨 설정 가져오기
            $getData = $this->getSkinConfig(Globals::get('gSkin.' . $this->skinType . 'SkinWork') ,'page');

            // 저장될 화일의 정보는 삭제
            $getData[$designPage] = [];

            // _POST 정보의 내용 삭제
            $notPostField = ['x', 'y', 'designPage', 'codeact', 'content', 'base_content', 'outbg_img_del', 'inbg_img_del'];
            foreach ($setData as $k => $v){
                if (in_array($k, $notPostField)) {
                    unset($setData[$k]);
                }
            }

            foreach ($setData as $k => $v){
                if ($v == '') continue;
                if ($k == 'outline_header' && $v == 'default') continue;
                if ($k == 'outline_footer' && $v == 'default') continue;
                if ($k == 'outline_side' && $v == 'default') continue;
                if ($k == 'outline_sidefloat' && $v == 'default') continue;

                // 저장될 정보
                if (empty($v) === false) $getData[$designPage][$k] = $v;
            }

            // 저장할 정보를 json_encode 처리
            $getData = $this->setEncode($getData);

            // 저장할 정보 화일
            $skinDesignPageConfigFile = $this->skinConfigDir . $this->skinName . '.json';

			$safe = new SafeFile();
            $safe->open($skinDesignPageConfigFile);
            $safe->write($getData);
            $safe->close();
            @chmod($skinDesignPageConfigFile, 0707);
        }

        if ($designPagePreview === 'y') {
            echo '<script>parent.preview_popup();</script>';
            exit();
        }

        return true;
    }

    /**
     * 디자인 페이지 저장
     * @param  string $saveMode mode (save, saveas, create)
     * @param  array $pageData data
     * @return array
     */
    public function saveDesignPage($saveMode, $pageData)
    {
        // 전체레이아웃 기본페이지 설정 인경우 패스
        if ($pageData['designPage'] === 'default') {
        	return false;
        }

        // 디자인 페이지 내용이 없는 경우
        if (empty($pageData['content']) === true) {
            return false;
        }

        $tmp = explode('/', '/' . $pageData['designPage']);

        // 편집소스 화면보기인 경우 경로
        /*
         * @todo : 편집소스 화면보기 구현이 되어야 함
         */
        if (gd_isset($pageData['designPagePreview']) === 'y') {
            $dir = $this->skinPreviewPath;
        }

        // 일반 저장인 경우 경로
        else {
            $dir = $this->skinPath;
        }

        for ( $i = 0; $i < ( count( $tmp ) - 1 ); $i++ )
        {
            $dir .= $tmp[ $i ] . '/';
            if (!file_exists($dir)) {
                mkdir($dir, 0757, true);
            }
            @chmod($dir, 0757);
        }

        $nowPath = $dir . $tmp[ ( count( $tmp ) - 1 ) ]; // 업로드경로 재정의
        $ext_arr = explode('.', $nowPath);
        $ext = array_pop($ext_arr);    // 파일 확장자

        // 파일정보
        if ( $pageData['designPage'] != 'proc/_agreement.txt' && !in_array($ext, ['js','css']))
        {
            preg_match('/\{\*\*\*( .*)\*\*\*\}/i', $pageData['content'], $matches);

            // 파일내에 파일정보 있는 경우
            if (isset($matches[1]) === true) {
                $fileInfo = explode('|', $matches[1]);
                $fileInfo[0] = trim(gd_isset($fileInfo[0]));
                $fileInfo[1] = trim(gd_isset($fileInfo[1]));

                if ($saveMode === 'saveas') $pageData['linkurl'] = 'main/html.php?htmid='.$pageData['designPage'];
                if ($fileInfo[1] === '' && $pageData['linkurl'] === '') $pageData['linkurl'] = str_replace(['.html', '.txt'], '.php', $pageData['designPage']);

                if (empty($pageData['text']) === false ) $fileInfo[0] = $pageData['text'];
                if (empty($pageData['linkurl']) === false) $fileInfo[1] = $pageData['linkurl'];

                $matches[1] = '{*** ' . implode( ' | ', $fileInfo ) . ' ***}';

                $pageData['content'] = str_replace($matches[0], $matches[1], $pageData['content']);
            }
            // 파일내에 파일정보 없는 경우
            else {
                if ($saveMode === 'saveas') {
                    $pageData['linkurl'] = 'main/html.php?htmid='.$pageData['designPage'];
                } elseif (empty($pageData['linkurl']) === true) {
                    $pageData['linkurl'] = str_replace(['.html', '.txt'], '.php', $pageData['designPage']);
                }
                $pageData['content'] = '{*** ' . $pageData['text'] . ' | ' . $pageData['linkurl'] . ' ***}' . PHP_EOL . $pageData['content'];
            }
        }

        $safe = new SafeFile();
        $safe->open($nowPath);
        $safe->write($pageData['content']);
        $safe->close();
        @chmod($nowPath, 0707);

        // 히스토리 관리
        if ($pageData['designPagePreview'] !== 'y' && $saveMode === 'save') {
            $this->saveDesignHistoryFile($pageData['designPage']);
        }

        return true;
    }

    /**
     * 디자인 페이지 일괄 저장
     * @return array
     * @throws \Exception
     */
    public function saveDesignPageBatch()
    {
    	// 스킨 설정 가져오기
        $getData = $this->getSkinConfig(Globals::get('gSkin.' . $this->skinType . 'SkinWork') ,'page');

        // 배경 초기화 여부
        $bgReset = Request::post()->get('bgReset');

        if ($bgReset === 'yes') {
            // storageHandler : 저장소 세팅
            if ($this->skinType === 'front') {
                $storage = Storage::disk(Storage::PATH_CODE_FRONT_SKIN_CODI);
            } else if ($this->skinType === 'mobile') {
                $storage = Storage::disk(Storage::PATH_CODE_MOBILE_SKIN_CODI);
            } else {
                throw new \Exception(__('이미지 파일 저장시 오류가 발생되었습니다.'));
            }
	    }

        // 정보 초기화
        foreach ( $getData as $filekey => $property ) {
        	// 전체레이아웃 기본 설정인 경우 예외
        	if ($filekey === 'default') continue;

			if (isset($property['outline_header'])) unset($getData[$filekey]['outline_header']);
			if (isset($property['outline_footer'])) unset($getData[$filekey]['outline_footer']);
			if (isset($property['outline_side'])) unset($getData[$filekey]['outline_side']);
			if (isset($property['outline_sidefloat'])) unset($getData[$filekey]['outline_sidefloat']);

            // 배경 초기화 시 배경색 삭제 및 배경이미지 삭제
            if ($bgReset === 'yes') {
				if (isset($property['outbg_color'])) unset($getData[ $filekey ]['outbg_color']);
				if (isset($property['outbg_img'])) {
					if ( trim( $property['outbg_img'] ) !== '' ) {
                        $storage->delete( $property['outbg_img']);
					}
					unset($getData[$filekey]['outbg_img']);
				}
				if (isset($property['inbg_img'])) {
					if ( trim( $property['inbg_img'] ) !== '' ) {
                        $storage->delete($property['inbg_img']);
					}
					unset($getData[$filekey]['inbg_img']);
				}
			}
        }

		// 저장할 정보를 json_encode 처리
        $getData = $this->setEncode($getData);

        // 저장할 정보 화일
        $skinDesignPageConfigFile = $this->skinConfigDir . $this->skinName . '.json';

		$safe = new SafeFile();
        $safe->open($skinDesignPageConfigFile);
        $safe->write($getData);
        $safe->close();
        @chmod($skinDesignPageConfigFile, 0707);
    }

    /**
     * 디자인관리 히스토리 관리 태그 생성
     * @param string $designPage 디자인 파일 경로
     */
    public function saveDesignHistoryFile($designPage)
    {
        if (empty($this->skinPath) === true) {
            $this->setSkin(Globals::get('gSkin.' . $this->skinType . 'SkinWork'));
        }

    	$fileHx = [];
    	$savedDir = dirname($designPage);
    	$savedFile = basename($designPage);
    	$newDesignPage = 'Hx' . time() . '_' . $savedFile;
        $targetPage = UserFilePath::temporary('skin_history', $this->skinType, $this->skinName, $savedDir, $newDesignPage);
    	$sourcePage = $this->skinPath . DS . $designPage;
        $historyTempPath = UserFilePath::temporary('skin_history', $this->skinType, $this->skinName, $savedDir);

		// 화일 복사
		FileHandler::copy($sourcePage, $targetPage);

		foreach (new DirectoryIterator($historyTempPath) as $fileInfo) {
			if ($fileInfo->isDot() === true) {
				continue;
			}

			if (preg_replace('/^Hx[0-9]*_/', '', $fileInfo->getFilename()) == $savedFile) $fileHx[] = $fileInfo->getPathname();
		}
		sort($fileHx);

		if (count($fileHx) > $this->designHistoryFile) {
			for($i = 0; $i < count($fileHx) - $this->designHistoryFile; ++$i) {
				// 오래된 히스토리 화일 삭제
				FileHandler::delete($fileHx[$i]);
			}
		}
    }

    /**
     * 디자인 페이지 삭제
     * @param int $designPage 디자인 파일 경로
     * @return bool
     * @throws \Exception
     */
    public function deleteDesignPage($designPage)
    {
        // 스킨 정보
        if (empty($this->skinPath) === true) {
            $this->setSkin(Globals::get('gSkin.' . $this->skinType . 'SkinWork'));
        }

    	$pageInfo = $this->_getDesignPage($designPage);

    	// 배경이미지 삭제
    	if (isset($pageInfo['outbg_img']) === true || isset($pageInfo['inbg_img']) === true) {
            // storageHandler : 저장소 세팅
            if ($this->skinType === 'front') {
                $storage = Storage::disk(Storage::PATH_CODE_FRONT_SKIN_CODI);
            } else if ($this->skinType === 'mobile') {
                $storage = Storage::disk(Storage::PATH_CODE_MOBILE_SKIN_CODI);
            } else {
                throw new \Exception(__('이미지 파일 저장시 오류가 발생되었습니다.'));
            }

			$bgFile = array();
			$bgFile['outbg_img'] = gd_isset($pageInfo['outbg_img']);
			$bgFile['inbg_img'] = gd_isset($pageInfo['inbg_img']);

			// 이미지 삭제
			foreach ($bgFile as $dKey => $dVal) {
				if (empty($dVal) === false) {
                    $storage->delete($dVal);
				}
	        }
		}

		// 스킨 설정 가져오기
        $getData = $this->getSkinConfig(Globals::get('gSkin.' . $this->skinType . 'SkinWork') ,'page');

        // 스킨 설정 화일 갱신
        if (isset($getData[$designPage])) {
        	// 저장될 화일의 정보는 삭제
	        unset($getData[$designPage]);

	        // 저장할 정보를 json_encode 처리
	        $getData = $this->setEncode($getData);

	        // 저장할 정보 화일
	        $skinDesignPageConfigFile = $this->skinConfigDir . $this->skinName . '.json';

			$safe = new SafeFile();
	        $safe->open($skinDesignPageConfigFile);
	        $safe->write($getData);
	        $safe->close();
	        @chmod($skinDesignPageConfigFile, 0707);
	    }

        // 경로 설정
        $delDesignPage = $this->skinPath->add($designPage);

        // 디자인 파일 삭제 진행
        FileHandler::delete($delDesignPage);

	    return true;
    }

    /**
     * 디자인 페이지 추가하기시의 중복확인
     * @param array $getData 디자인 페이지 정보
     * @return boolean
     */
    public function overlapDesignFile(array $getData)
    {
        // 화일 체크
        $targetSkinPath = $this->skinPath->add($getData['dirPath'], $getData['fileName']);
        if ($targetSkinPath->isFile()) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * getDesignFileTextName
     * 디자인 파일의 한글 파일 설명
     *
     * @param $designPage
     *
     * @return array
     *
     * @author su
     */
    public function getDesignFileTextName($designPage)
    {
        return $this->_getDesignPage($designPage);
    }

}
