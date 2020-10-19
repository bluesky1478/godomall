<?php if (gd_is_provider() === false) { ?>
    <div class="main-section">
        <div class="table-title">
            <span class="">플러스샵 이용현황</span>
            <div class="pull-right more-view">
                <a href="http://plus.godo.co.kr/member/my-app.gd" target="_blank" class="btn btn-sm btn-link">더보기</a>
            </div>
        </div>
        <?php if (empty($plusShopUsedList)) { ?>
            <div class="plusshop-stat-outer no-item">
                <div class="mask-plusshop">
                    <p class="mask-notice">사용중인 플러스샵 앱이 없습니다.</p>
                    <div class="plusshop-ad-link">
                        <img src="<?= PATH_ADMIN_GD_SHARE ?>img/plusshop_bi.png">
                        <p class="text1">
                            우리 쇼핑몰에 꼭 필요한 기능들만 모아서<br> 나만의 쇼핑몰을 만들어보세요.
                        </p>
                        <a href="http://plus.godo.co.kr" target="_blank">내게 필요한 앱 찾아보기</a>
                    </div>
                    <!-- 플러스샵 앱 없을 경우 no-item 클래스 적용 // -->
                </div>
            </div>
        <?php } else { ?>
            <div class="plusshop-stat-outer">
                <table class="table table-bordered">
                    <colgroup>
                        <col class="width20p">
                        <col class="width20p">
                    </colgroup>
                    <?php
                    $plusShopHtml = [];
                    $plusShopCount = count($plusShopUsedList);
                    for ($i = 0; $i < $plusShopCount; $i += 2) {
                        $item1 = $plusShopUsedList[$i];
                        $item2 = $plusShopUsedList[$i + 1];
                        $plusShopHtml[] = '<tr>';
                        $plusShopHtml[] = '<td class="' . ($item1['appUseFl'] == 'y' && $item1['appInstallFl'] == 'y' ? 'active' : '') . '">';
                        $plusShopHtml[] = '<a href="' . $item1['appLink'] . '" target="_blank">' . $item1['appName'] . '</a>';
                        $plusShopHtml[] = '</td>';
                        $plusShopHtml[] = '<td class="' . ($item2['appUseFl'] == 'y' && $item2['appInstallFl'] == 'y' ? 'active' : '') . '">';
                        $plusShopHtml[] = '<a href="' . $item2['appLink'] . '" target="_blank">' . $item2['appName'] . '</a>';
                        $plusShopHtml[] = '</td>';
                        $plusShopHtml[] = '</tr>';
                    }
                    echo implode('', $plusShopHtml);
                    ?>
                </table>
            </div>
        <?php } ?>
    </div>
<?php } ?>
