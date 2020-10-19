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

/**
 * 디자인 페이지 내 모바일 페이지 연결 클래스
 * @author choisueun <cseun555@godo.co.kr>
 */
class DesignConnectUrl extends \Component\Design\SkinBase
{
    // 모바일 연결 가능 폴더
    private $allowFolder = [
        'main',
        'intro',
        'member',
        'mypage',
        'goods',
        'board',
        'service',
        'event',
        'proc',
    ];

    /**
     * 디자인 페이지 연결/미연결 노출 여부 체크
     *
     * @param $getPageID 디자인 페이지
     * @return bool
     */
    public function getMobileConnect($getPageID)
    {
        $redirectFl = false;

        $skinDesign = new SkinDesign();

        $designInfo = $skinDesign->getDesignPageInfo($getPageID);

        // 기존 리다이렉트 페이지
        $arrDefaultFolder = [];
        $arrMobilePage = \App::getConfig('app.mobilepagelist')->toArray();
        $arrMobileExceptPage = \App::getConfig('app.mobilepageexceptlist')->toArray();
        $arrExceptPageList = array_merge($arrMobilePage, $arrMobileExceptPage);

        // 확장자 변경
        $redirectFileNm = str_replace('.html', '.php', $designInfo['file']['name']);

        $allowFolderNm = $designInfo['dir']['name'];

        foreach ($arrExceptPageList as $val) {
            $arrDefaultFolder[$val['folder']][] = $val['page'];
        }
        // 허용 폴더 체크
        if(in_array($allowFolderNm, $this->allowFolder)){
            if(!in_array($redirectFileNm, $arrDefaultFolder[$designInfo['dir']['name']])){
                $redirectFl = true;
            }
        }

        return $redirectFl;
    }

    /**
     * 모바일 연결 페이지 리스트
     *
     * @param $skinNm
     * @param int $page
     * @param int $pageNum
     * @return bool
     */
    public function getMobileConnectPageList($skinNm, $page = 1, $pageNum = 10)
    {
        $start = $pageNum * ($page - 1);
        $skinJsonFile = file_get_contents($this->skinConfigDir .$skinNm . '.json');
        $arrMConnectPageList = json_decode($skinJsonFile, true);
        $arrMobilePage = \App::getConfig('app.mobilepagelist')->toArray();
        $arrMobileExceptPage = \App::getConfig('app.mobilepageexceptlist')->toArray();
        $arrExceptPageList = array_merge($arrMobilePage, $arrMobileExceptPage);
        foreach ($this->allowFolder as $allowFolder) {
            foreach ($arrMConnectPageList as $folderNm => $val) {
                if (empty($val['linkurl'])) {
                    $val['linkurl'] = $folderNm;
                }
                $val['folder'] = explode('/', $val['linkurl'])[0];
                $val['url'] = explode('/', $val['linkurl'])[1];
                $newFolderNm = explode('/', $folderNm);
                if ($newFolderNm[0] == $allowFolder) {
                    $cnt = substr_count($folderNm, '/');

                    // 허용 폴더중 하위 폴더를 포함하고 있다면 제외
                    if ($cnt == 1) {
                        $arrFolder[] = $val;
                    }
                }
            }
        }
        // 연결불가 페이지 + 시스템 리다이렉트 페이지 들을 제외 하기 위함
        foreach ($arrExceptPageList as $res) {
            $page = explode('.', $res['page'])[0];
            $arrAllUrl[] = $page;
        }
        foreach ($arrFolder as $k => $v) {
            $fileNm = explode('.', $v['url'])[0];
            if (!in_array($fileNm, $arrAllUrl)) {
                $resort[$v['folder']][] = $v['url'];
                $arrList[] = $v;
            }
        }
        // 디자인 스킨 트리와 같이 정렬하기 위함
        foreach($resort as $key => $val){
            natcasesort($val);
            $list[$key] = array_values($val);
        }

        // 연결 페이지 최종 리스트
        foreach($list as $folder => $item){
            foreach($item as $value){
                $pullLink = $folder.'/'.$value;
                foreach($arrList as $info){
                    $url = explode('.', $info['url'])[0];
                    $link = explode('.', $value)[0];
                    if($url == $link){
                    //if($info['linkurl'] == $link) {
                        $info['linkurl'] = $pullLink;
                        $arrMobilePageList[] = $info;
                    }
                }
            }
        }
        $arrMobileConnectPage['total'] = count($arrMobilePageList);
        $arrMobileConnectPage['data'] = array_slice($arrMobilePageList, $start, $pageNum);
        $arrMobileConnectPage['all'] = $arrMobilePageList;

        return $arrMobileConnectPage;
    }

}