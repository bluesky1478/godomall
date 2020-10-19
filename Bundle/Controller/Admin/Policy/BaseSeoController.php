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
namespace Bundle\Controller\Admin\Policy;

use App;
use Exception;
use Request;
use Component\Mall\Mall;
use Component\Mall\MallDAO;

/**
 * 기본 정보 설정
 * @author atomyang
 */
class BaseSeoController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'seo');

        // --- 기본 정보
        try {
            $mallSno = gd_isset(\Request::get()->get('mallSno'), 1);
            $this->setData('mallInputDisp', $mallSno == 1 ? false : true);

            // 모듈 설정
            $policy = App::load('\\Component\\Policy\\Policy');
            $data = gd_policy('basic.info', $mallSno);

            $mall = new Mall();
            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
                $this->setData('mallSno', $mallSno);
                if ($mallSno > 1) {
                    $defaultData = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
                    foreach ($defaultData as $key => $value) {
                        if (in_array($key, Mall::GLOBAL_MALL_BASE_INFO) === true) $data[$key] = $value;
                    }
                    $data['mallDomain'] .= "/".$mallList[$mallSno]['domainFl'];

                    $disabled = ' disabled = "disabled"';
                    $readonly = ' readonly = "readonly"';
                    $this->setData('disabled', $disabled);
                    $this->setData('readonly', $readonly);
                }
            }

            $seoTag = App::load('\\Component\\Policy\\SeoTag');

            //태그설정
            $seoConfig = $seoTag->seoConfig;
            $seoConfig['pageGroup'] = array_flip($seoConfig['commonPage']);

            //주요 페이지 SEO태그 관련
            $seoTagCommonList = $seoTag->getSeoTagCommonList(['deviceFl' => 'c','mallSno' => $mallSno]);
            $seoTagCommonList = array_combine(array_column($seoTagCommonList,'path'),$seoTagCommonList);

            $this->setData('seoTagCommonList', $seoTagCommonList);


            // 검색로봇 설정
            $data['robotsTxt'] = gd_policy('basic.robotsTxt');
            if (empty($data['robotsTxt']['front']) === true) {
                $data['robotsTxt']['front'] = 'User-agent: *' . PHP_EOL . 'Disallow: /' . PHP_EOL . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Googlebot' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Cowbot' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: NaverBot' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Yeti' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Daumoa' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'Disallow: /admin/' . PHP_EOL . 'Disallow: /config/' . PHP_EOL . 'Disallow: /data/' . PHP_EOL . 'Disallow: /module/' . PHP_EOL . 'Disallow: /tmp/' . PHP_EOL;
            }
            if (empty($data['robotsTxt']['mobile']) === true) {
                $data['robotsTxt']['mobile'] = 'User-agent: *' . PHP_EOL . 'Disallow: /' . PHP_EOL . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Googlebot' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Cowbot' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: NaverBot' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Yeti' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Daumoa' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'Disallow: /admin/' . PHP_EOL . 'Disallow: /config/' . PHP_EOL . 'Disallow: /data/' . PHP_EOL . 'Disallow: /module/' . PHP_EOL . 'Disallow: /tmp/' . PHP_EOL;
            }

            // 사이트맵 설정
            $data['sitemap'] = gd_policy('basic.sitemap');
            gd_isset($data['sitemap']['sitemapAutoFl'],"n");
            $checked['sitemap']['sitemapAutoFl'][$data['sitemap']['sitemapAutoFl']] = 'checked="checked"';

            //소셜관련
            $socialShare = App::load('\\Component\\Promotion\\SocialShare');
            $socialData = $socialShare->getConfig();
            gd_isset($socialData['snsRepresentTitle'],'{=gMall.mallNm}');


            //RSS 설정 ,페이지 경로 설정 , 대표URL 설정 , 연관채널 설정
            $seoData = gd_policy('basic.seo', $mallSno);
            $data['rss'] = $seoData['rss'];
            $data['errPage'] = $seoData['errPage'];
            $data['canonicalUrl'] = $seoData['canonicalUrl'];
            $data['relationChannel'] = array_filter($seoData['relationChannel']);
            $checked['rss']['useFl'][$data['rss']['useFl']] = $checked['errPage']['useFl'][$data['errPage']['useFl']] = $checked['canonicalUrl']['useFl'][$data['canonicalUrl']['useFl']] = 'checked="checked"';

        } catch (Exception $e) {
            //echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('seoConfig', $seoConfig);
        $this->setData('socialData', $socialData);

    }
}
