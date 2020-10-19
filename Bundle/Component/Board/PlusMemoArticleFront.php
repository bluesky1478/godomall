<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Board;


use Component\Member\Manager;
use Framework\Utility\StringUtils;
use Session;

class PlusMemoArticleFront extends AbstractPlusMemoArticle
{

    public function __construct()
    {
        if (Session::has('member.memNo')) {
            $this->member = ['memNo' => Session::get('member.memNo'), 'memNm' => Session::get('member.memNm'), 'memId' => Session::get('member.memId'), 'groupSort' => Session::get('member.groupSort'), 'memNick' => Session::get('member.nickNm'), 'groupSno' => Session::get('member.groupSno')];
        }
        parent::__construct();
    }

    public function getMemberAuth($plusMemoSno) {
        $this->config = $this->plusMemoManager->get($plusMemoSno);
        return ['write'=>$this->canWrite(),'access' => $this->canAccess(),'comment'=>$this->canComment()];
    }

    public function getList($req, $isPaging = true)
    {
        if (!$req['plusMemoSno']) {
            throw new \Exception(__('잘못된 경로로 접근하셨습니다.'));
        }

        $this->config = $this->plusMemoManager->get($req['plusMemoSno']);

        if ($this->canAccess() === false) {
            return null;
        }

        if (!$req['pageNum']) {
            $req['pageNum'] = $this->config['listCount'];
        }
        if ($req['cutContents']) {
            $this->config['cutContents'] = $req['cutContents'];
        }

        $data = parent::getList($req, $isPaging);
        $data['pagination'] = $data['paging']->getPage('plusGoPaging(\'PAGELINK\')');   //ajax처리
        return $data;
    }

    protected function convertArticle($article)
    {
        if($this->config['cutContents']){
            $article['contents'] = StringUtils::strCut($article['contents'],$this->config['cutContents']);
        }

        $article['auth']['modifyAndRemove'] = $this->canModifyAndRemove($article);
        $article['auth']['read'] = $this->canRead($article);
        if($article['auth']['read'] != 'y') {
            $article['searetViewContents'] =  str_replace(['\r\n', '\n', chr(10)], '<br>', $article['contents']);
            $article['viewContents'] = '비밀글 입니다.';
        }
        else {
            $article['viewContents'] = str_replace(['\r\n', '\n', chr(10)], '<br>', $article['contents']);
        }

        $article['contents'] = str_replace(['\r\n'], chr(10), $article['contents']);
        $article['icon'] = $article['isSecret'] == 'y' ? '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_secret.gif" />' : '';
        $article['writer'] = $this->getWriter($article);

        $article['regDate'] = gd_date_format('Y.m.d', $article['regDt']);
        $article['modDate'] = gd_date_format('Y.m.d', $article['modDt']);

        return $article;
    }


    protected function getWriter($article){
        $memberType = $this->getTypeMember($article['memNo']);
        switch ($memberType) {
            case 'member' :
                if ($this->config['writerDisplay'] == 'id') {
                    $writer = $article['writerId'];
                } else if ($this->config['writerDisplay'] == 'nick') {

                    $writer = $article['writerNick'];
                } else {
                    $writer = $article['writerNm'];
                }

                break;
            case 'admin' :
                if ($this->config['managerDisplay'] == 'image') {
                    $manager = new Manager();
                    $managerData = $manager->getManagerInfo(abs($article['memNo']));
                    $writer = "<img src='" . $managerData['dispImage'] . "' style='max-width:200px' />";
                } else if ($this->config['managerDisplay'] == 'nick') {
                    $writer = $article['writerNick'];
                } else {    //hide
                    $writer = '-';
                }
                break;
            default :
                $writer = $article['writerNm'];
        }
        if($memberType != 'admin') {
            if ($this->config['writerDisplayLimit']) {
                if (iconv_strlen($writer, SET_CHARSET) > $this->config['writerDisplayLimit']) {
                    $starCnt = iconv_strlen($writer, SET_CHARSET) - $this->config['writerDisplayLimit'];
                } else {
                    $starCnt = 0;
                }
                $star = '';
                for ($i = 0; $i < $starCnt; $i++) {
                    $star .= '*';
                }

                $restWriterNm = iconv_substr($writer, 0, $this->config['writerDisplayLimit'], SET_CHARSET);
                $writer = $restWriterNm . $star;
            }
        }

        return $writer;
    }


}
