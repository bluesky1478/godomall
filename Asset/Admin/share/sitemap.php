<script type="text/javascript">
    <!--
    function searchMenu() {
        var sword = $("#search").val().toUpperCase();
        var menus = $(".each");
        for (var i = 0; i < menus.length; i++) {
            var obj = $(menus[i]).find("a");
            var menuTxt = obj.text();
            var html = menuTxt.replace(sword, "<span style='background-color:#ffff00; color:#000000;'>" + sword + "</span>");
            obj.html(html);
        }
    }

    $(document).ready(function () {
        $("#search").keydown(function (evt) {
            if (evt.keyCode == 13) {
                searchMenu();
                returnValue = false;
                return false;
            }
        });
    });
    //-->
</script>

<div class="page-header js-affix">
    <h3>사이트맵
        <small>관리자 페이지의 전체 메뉴를 확인 하실수 있습니다.</small>
    </h3>
</div>

<table id="sitemapSrch">
    <colgroup>
        <col width="85"/>
        <col width="85"/>
        <col/>
        <col width="300"/>
    </colgroup>
    <tr>
        <td><img src="<?= PATH_ADMIN_GD_SHARE ?>img/tab_sitemap01_on.gif"/></td>
        <td><a href="sitemap_index.php"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/tab_sitemap02_off.gif"/></a></td>
        <td class="text borderBottom">
            빠르게 찾고 싶으실 때는 키보드 Ctrl+F 또는 우측검색을 이용하세요.
        </td>
        <td class="borderBottom">
            <input type="text" id="search" class="sitemap-search-input"/>
            <span class="sitemap-search-btn"><a onclick="searchMenu()">검색</a></span>
        </td>
    </tr>
</table>

<div id="sitemap">
    <?php
    foreach ($menuTreeList['top'] as $menuTreeKey => $menuTreeVal) {
        if ($menuTreeVal['display'] == 'y') {
            ?>
            <ul class="snb">
                <div class="menu_title"><?= $menuTreeVal['name']; ?></div>
                <?php
                foreach ($menuTreeVal['mid'] as $subTreeKey => $subTreeVal) {
                    if ($subTreeVal['display'] == 'y') {
                        ?>
                        <li class="nav">
                            <h3><?php echo $subTreeVal['name'] ?></h3>
                            <div class="btn_collapse"></div>
                            <?php
                            foreach ($subTreeVal['last'] as $lastTreeKey => $lastTreeVal) {
                                if ($lastTreeVal['display'] == 'y') {
                                    ?>
                                    <ul class="each_box">
                                        <li class="each">
                                            <a href="<?php echo $adminMenuLink . strtolower($menuTreeVal['code']) . DS . $lastTreeVal['url'] ?>"><?php echo $lastTreeVal['name'] ?></a>
                                        </li>
                                    </ul>
                                    <?php
                                }
                            }
                            ?>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>
            <?php
        }
    }
    ?>
</div>
