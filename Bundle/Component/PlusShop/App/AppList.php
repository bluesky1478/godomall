<?php

namespace Bundle\Component\PlusShop\App;


use App;
use Bundle\Service\GDUtils;
use Request;
use UserFilePath;

class AppList
{
    const PRODUCTION = 'http://gongji.godo.co.kr/userinterface/plusshop/appGoodsShareAPI.php';
    const DEVELOPMENT = 'https://gongjifinal.godo.co.kr/userinterface/plusshop/appGoodsShareAPI.php';

    private $appList;

    /**
     * 앱 리스트 조회
     *
     * @param int $page 페이지
     * @param string|null $sortBy 정렬 기준
     * @param string $free 무료 앱/유료 앱
     * @param string|null $category 분류
     * @param bool $isMyApp 마이앱
     * @return array
     */
    public function getList($page = 1, $sortBy = null, $free = null, $category = null, $isMyApp = false)
    {
        $appList = $this->getAppDetailList();

        if ($sortBy) {
            $appList = $this->getSort($sortBy, $appList);
        }

        if ($category) {
            $appList = $this->getCategoryFilter($category, $appList);
        }

        if ($free) {
            $appList = $this->getFreeFilter($free, $appList);
        }

        if ($isMyApp) {
            $appList = $this->getMyFilter($appList);
        }

        return [
            'total' => count($appList),
            'list' => array_slice($appList, $page * 10 - 10, 10),
        ];
    }

    /**
     * 분류 조회
     *
     * @return array
     */
    public function getCategories()
    {
        $appList = $this->getAppList();

        return array_reverse($appList['category_list']);
    }

    private function getAppList()
    {
        if (empty($this->appList)) {
            $list = $this->getByCache();
            if (!$list) {
                $list = $this->getByApi();
            }
            $this->appList = $list;
        }

        return $this->appList;
    }

    private function getAppDetailList()
    {
        $list = $this->getAppList();
        $appList = $list['app_list'];
        $settableAppList = GDUtils::getPlusShopList();

        for ($i = 0; $i < count($appList); $i++) {
            $appList[$i]["expire_date"] = "";
            $appList[$i]["is_purchased"] = false;
            $appList[$i]["is_installed"] = false;
            $appList[$i]["sale_price"] = number_format($appList[$i]["sale_price"]);
            $appList[$i]["image"] = isset($appList[$i]["images"][0]) ? $appList[$i]["images"][0] : null;
            $appList[$i]["is_my_app"] = false;
            if (strlen($appList[$i]["detail"]) > 250) {
                $appList[$i]["detail"] = substr($appList[$i]["detail"], 0, 250) . "...";
            }
            $appList[$i]["grade"] = ($appList[$i]["grade"] * 10) * 2 > 100 ? 100 : ($appList[$i]["grade"] * 10) * 2;

            $appList[$i]["is_paused"] = false;

            if (!empty($settableAppList) && $appList[$i]["type"] == "c" && !empty($appList[$i]["key_code"])) {
                $code = $appList[$i]["key_code"];
                $appList[$i]["is_my_app"] = false;
                $key_code = "cGodo_" . ucfirst($appList[$i]["key_code"]);

                if (array_key_exists($key_code, $settableAppList)
                    && array_key_exists("appCode", $settableAppList[$key_code])
                    && $settableAppList[$key_code]["appCode"] == $code) {
                    $info = $settableAppList[$key_code];
                    $appList[$i]["register_date"] = $info["appRegData"];
                    $appList[$i]["setting_url"] = $this->getSettingUrl($info["adminUrl"]);
                    $appList[$i]["is_my_app"] = true;
                    $appList[$i]["is_purchased"] = true;
                    $appList[$i]["is_paused"] = $info["appUseFl"] == "n" ? true : false;
                    $appList[$i]["is_installed"] = isset($info['appInstallFl']) && $info['appInstallFl'] == "y" ? true : false;
                    $appList[$i]["expire_date"] = "";
                }
            }
        }

        return $appList;
    }

    private function getMyFilter($appList)
    {
        return array_values(
            array_filter($appList, function ($app) {
                return $app['is_my_app'] === true;
            }
        ));
    }

    private function getFreeFilter($isFree, $appList)
    {
        if ($isFree == 'y') {
            $appList = array_filter($appList, function ($app) {
                return $app['sale_price'] == 0;
            });
        }

        if ($isFree == 'n') {
            $appList = array_filter($appList, function ($app) {
                return $app['sale_price'] > 0;
            });
        }

        return array_values($appList);
    }

    private function getCategoryFilter($filter, $appList)
    {
        $filteredList = array_filter($appList, function ($app) use ($filter) {
            return $app['category'] == $filter;
        });

        return array_values($filteredList);
    }

    private function getSort($sortBy, $appList)
    {
        $sortPurchaseCount = [];
        $sortReleaseDate = [];

        foreach ($appList as $key) {
            $sortPurchaseCount[] = $key['purchase_count'];
            $sortReleaseDate[] = $key['release_date'];
        }

        if ($sortBy == 'purchase_count') {
            array_multisort($sortPurchaseCount, SORT_DESC, $appList);
        }

        if ($sortBy == 'release_date') {
            array_multisort($sortReleaseDate, SORT_DESC, $appList);
        }

        if ($sortBy == 'is_unlimited') {
            $appList = array_values(
                array_filter($appList, function ($app) {
                    return $app['expire_date'] == '';
                }
            ));
        }

        if ($sortBy == 'is_limited') {
            $appList = array_values(
                array_filter($appList, function ($app) {
                    return $app['expire_date'] != '';
                }
            ));
        }

        return $appList;
    }

    private function getByApi()
    {
        if (App::isProduction()) {
            $apiUri = self::PRODUCTION;
        } else {
            $apiUri = self::DEVELOPMENT;
        }

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $apiUri);
        curl_setopt($c, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($c);
        $info = curl_getinfo($c);
        curl_close($c);

        $list = null;
        if ($info['http_code'] == 200 && $info['content_type'] == 'application/json') {
            $this->writeCache($response);
            $list = json_decode($response, true);
        }

        return $list;
    }

    private function writeCache($data)
    {
        $cacheDir = $this->getCacheDir();

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        return file_put_contents($this->getCacheFilepath(), json_encode([
            'data' => $data,
            'ttl' => time()
        ]));
    }

    private function getCacheDir()
    {
        return UserFilePath::get('tmp', ...explode('/', 'cache/plusshop'))->getRealPath();
    }

    private function getCacheFilepath()
    {
        $cacheDir = $this->getCacheDir();

        return "$cacheDir/PartnerCenterAppList.cache";
    }

    private function getByCache()
    {
        $cacheFile = $this->getCacheFilepath();

        if (file_exists($cacheFile)) {
            $contents = json_decode(file_get_contents($cacheFile), true);

            if (time() - $contents['ttl'] > 1800) {
                return false;
            }

            return json_decode($contents['data'], true);
        } else {
            return false;
        }
    }

    private function getSettingUrl($url = '')
    {
        return !empty($url) ? 'http://' . Request::server()->get("SERVER_NAME") . $url : '';
    }
}
