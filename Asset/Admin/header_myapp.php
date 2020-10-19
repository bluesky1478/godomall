<?php
/**
 * 관리자 상단
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 * @author    Shin Donggyu <artherot@godo.co.kr>
 */
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">

            <div class="navbar-header">
                <a class="navbar-brand" href="<?php echo $getTopMenuArr['link']; ?>base/index.php">Godomall5</a>
                <span class="<?php echo Globals::get('gLicense.ecKind'); ?>"></span>
                <?php if (gd_is_provider() === false) { ?>
                    <?php if (Globals::get('gLicense.ecCode') === 'rental_mx_pro' && Globals::get('gLicense.restDay') <= 60) { ?>
                        <span class="display-inline-block expire <?php if (Globals::get('gLicense.restDay') >= 0) { ?>expire_info<?php } else { ?>expire_warning<?php } ?>">
                            <?php if (Globals::get('gLicense.restDay') >= 0) { ?>
                                <span>기간만료</span>
                                <span class="day_info"><?php echo Globals::get('gLicense.restDay'); ?>일 전</span>
                            <?php } else { ?>
                                <span>기간만료</span>
                                <span class="day_warning"><?php echo (Globals::get('gLicense.restDay') * -1); ?>일 경과</span>
                            <?php } ?>
                            <a href="javascript:gotoGodomall('extend');" class="btn btn-sm btn-bright-black mgl8 service-extension">연장</a>
                        </span>
                    <?php } ?>
                <?php } ?>

            </div>

            <div class="gnb">
                <ul class="list-inline">
                    <li class="no-bar">
                        <div class="dropdown">
                            <!--js-btn-layer-sub-menu-->
                            <a href="#" class="dropdown-toggle gnb-idinfo" id="headerSubMenuManager" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                                <span><?php echo Session::get('manager.managerNm'); ?></span>님 (
                                <span><?php echo Session::get('manager.managerId'); ?></span>
                                ) </a>
                            <ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="headerSubMenuManager">
                                <li class="dropdown-item"><a href="<?php echo $_managerUri_ ?>policy/manage_register.php?sno=<?php echo Session::get('manager.sno'); ?>">운영자정보</a></li>
                                <li class="dropdown-item"><a href="<?php echo URI_ADMIN ?>base/login_ps.php?mode=logout">로그아웃</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="pos-r no-bar">
                        <a href="javascript:Schedule.addToday()" class="schedule-a"> <img src="<?php echo PATH_ADMIN_GD_SHARE ?>img/icon_bell.png" style="margin-right: 6px;">
                            <?php if (count($schedule['todayList']) > 0) { ?>
                                <span class="badge" style="position:absolute;left:12px;top:-6px;"><?php echo count($schedule['todayList']) ?></span>
                            <?php } ?>
                        </a>
                    </li>
                    <li class="no-bar">
                        <span class="dropdown" style="margin: 0; padding: 0;">
                            <?php if (count($gGlobal['useMallList']) > 1) { ?>
                                <a href="#" class="dropdown-toggle gnb-myshop" id="headerMyShop" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">내쇼핑몰</a>
                                <ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="headerMyShop">
                                <?php foreach ($shopMallList as $val) { ?>
                                    <li class="dropdown-item"><a href="<?=$val['domain']; ?>" target="_blank"><span class="flag flag-16 flag-<?=$val['domainFl']?>"></span> <?=$val['mallName']?></a></li>
                                <?php } ;?>
                            </ul>
                            <?php } else { ?>
                                <a href="<?php echo URI_HOME; ?>" class="gnb-myshop" id="headerMyShop" target="_blank">내쇼핑몰</a>
                            <?php } ?>
                        </span>
                        <?php
                        if (gd_is_provider() === false) {
                            ?>
                            <span style="margin: 0; padding: 0;">
                            <a href="../plusshop/" target="_blank" class="gnb-myshop text-red">플러스샵</a>
                        </span>
                            <span style="margin: 0; padding: 0;">
                            <a href="<?php echo $_managerUri_ ?>share/shoplinker.php" class="gnb-myshop pdr0">마켓연동</a>
                        </span>
                            <?php
                        }
                        ?>
                    </li>
                    <?php if ($showHeaderOrderPresentation) { ?>
                        <li class="no-bar new">
                            <div class="dropdown gnb-orderinfo" id="dropDownOrderPresentation">
                                <a href="#" class="dropdown-toggle dropdown-toggle-arr" id="headerSubMenuOrderPresentation" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                                <span class="js-oder-count-new display-none">
                                    <img src="<?= PATH_ADMIN_GD_SHARE ?>img/icon_new.png" width="14">
                                </span>
                                    주문현황
                                </a>
                                <ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="headerSubMenuOrderPresentation">
                                    <li class="dropdown-head">주문상태설정
                                        <div class="pull-right">
                                            <a href="#" class="js-setting-order" data-role="orderPresentation"> <img src="<?= PATH_ADMIN_GD_SHARE ?>img/icon_gear.png" alt=""> </a>
                                        </div>
                                    </li>
                                    <li class="dropdown-noitem">조회할 주문상태를<br>선택해주세요.</li>
                                </ul>
                            </div>
                        </li>
                        <li class="devide"></li>
                    <?php } ?>
                    <li class="no-bar">
                        <div class="dropdown gnb-favorite" id="dropDownFavoriteMenu">
                            <a href="#" class="dropdown-toggle dropdown-toggle-arr" id="headerSubMenuFavoriteMenu" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false"> 자주쓰는메뉴 </a>
                            <ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="headerSubMenuFavoriteMenu">
                                <li class="dropdown-head">메뉴설정
                                    <div class="pull-right">
                                        <a href="#" class="js-setting-favorite-menu"> <img src="<?= PATH_ADMIN_GD_SHARE ?>img/icon_gear.png" alt=""> </a>
                                    </div>
                                </li>
                                <li class="dropdown-noitem">자주쓰는 메뉴를<br>설정해주세요.</li>
                            </ul>
                        </div>
                    </li>
                    <li class="devide"></li>
                    <li class="no-bar">
                        <div class="dropdown gnb-btn-more">
                            <a href="#" class="dropdown-toggle dropdown-toggle-arr" id="headerSubMenuMore" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">더보기</a>
                            <ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="headerSubMenuMore">
                                <?php
                                if (gd_is_provider() === false) {
                                    echo '<li class="dropdown-item"><a href="https://www.godo.co.kr/mygodo/main.gd" target="_blank" class="pdr0">마이고도</a></li>';
                                }
                                ?>
                                <li class="dropdown-item"><a href="<?php echo $manualData['manual_domain']; ?>" target="_blank" class="pdr0">매뉴얼</a></li>
                                <li class="dropdown-item"><a href="<?php echo $_managerUri_ ?>share/sitemap.php" class="pdr0">사이트맵</a></li>
                            </ul>
                        </div>
                    </li>
                    <?php
                    // 고도 내부에서만 보여지도록 개발 진행 중
                    if (gd_is_provider() === false && Session::get('manager.workPermissionFl') == 'y') {
                        echo '<li class="devide"></li><li class="admin"><a href="javascript:development_manager();" class="font">개발소스관리</a></li>';
                    }
                    ?>
                    <li class="no-bar">
                        <div class="form-inline gnb-search">
                            <div class="gnb-search-head">
                                <?php if (gd_is_provider()) { ?>
                                    <input type="hidden" name="headerSearchType" value="menu">
                                <?php } else { ?>
                                    <select id="headerSearchType" name="headerSearchType" class="header-select">
                                        <option value="menu">메뉴</option>
                                        <option value="member">회원</option>
                                    </select>
                                <?php } ?>
                            </div>
                            <div class="gnb-search-body">
                                <span class="icon-magni"><img src="/admin/gd_share/img/ico_search_btn.png" alt=""></span>
                                <input type="text" id="headerSearchKeyword" class="form-control" name="headerSearchKeyword" placeholder="메뉴검색"/>
                                <div class="gnb-search-hint form-inline display-none">
                                    <select class="form-control multiple-select" size="5" id="headerSearchMenuList" style="height:100px;"></select>
                                </div>
                            </div>
                        </div>
                        <!--<a href="#" class="js-btn-layer-search-member pdr0 ">회원검색</a>-->
                    </li>
                    <!-- 스타일 안보임 처리 : 컨트롤러 제거는 하지 않음. 직접 URL 입력시 접속됨 -->
                    <!-- <li><a href="../bootstrap/index" style="color:yellow;" target="_blank">스타일</a></li>-->
                </ul>
            </div>
        </div>

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav reform">
                <?php
                // 공급사 관리자 여부에 따른 상단메뉴 링크 변경 처리
                if (isset($naviMenu)) {
                    $menuWidth = 1165 / $naviMenu->menuCnt;
                    foreach ($getTopMenuArr['data'] as $key => $val) {
                        if ($val['adminMenuDisplayType'] == 'y') {
                            ?>
                            <li class="<?php echo gd_isset($naviMenu->menuSelected['top'][$val['adminMenuNo']]); ?>">
                                <a href="<?php echo $getTopMenuArr['link'] . $val['adminMenuCode']; ?>/index.php" id="menu_<?php echo $val['adminMenuCode'] ?>"
                                   style="width:<?php echo $menuWidth; ?>px;"><?php echo $val['adminMenuName'] ?></a>
                            </li>
                            <?php
                        }
                    }
                } ?>
            </ul>
        </div><!-- /.navbar-collapse -->

    </div>
</nav>

<!-- 고도 사이트로 이동 시작 -->
<form name="frmGotoGodomall" action="" method="post">
    <input type="hidden" name="pageKey" value="<?php echo Globals::get('gLicense.godosno'); ?>">
    <input type="hidden" name="sno" value="<?php echo Globals::get('gLicense.godosno'); ?>">
    <input type="hidden" name="mode" value="">
</form>
<!-- 고도 사이트로 이동 끝 -->

<script type="text/javascript">
    $(document).ready(function () {
        var gd_main_layer = {
            presentation: {id: "presentation", selector: "#layerPresentation", title: "주요현황 조회설정"},
            order_presentation: {id: "orderPresentation", get_id: "getOrderPresentation", title: "주문현황 조회설정"},
            order_setting: {id: "orderSetting", title: "주문관리 조회설정"},
            favorite_menu: {id: "favoriteMenu", get_id: "getFavoriteMenu", selector: "#layerFavorite", title: "자주쓰는 메뉴 설정"}
        };
        var _bootstrap_dialog = BootstrapDialog;

        $('#dropDownFavoriteMenu').on('show.bs.dropdown', function () {
            var dropdown_container = $(this);
            var dropdown_menu = dropdown_container.find('.dropdown-menu');
            $.ajax('../base/main_setting_ps.php', {
                method: "post",
                data: {mode: gd_main_layer.favorite_menu.get_id},
                global_complete: false,
                success: function () {
                    if (arguments[0].success === 'OK') {
                        var result = arguments[0].result;
                        if (result.length > 0) {
                            var html = [];
                            $.each(result, function (idx, item) {
                                var menuName = 'none';
                                var menuLink = '';
                                if (typeof item.tName === 'string') {
                                    menuName = item.tName;
                                } else if (typeof item.sName === 'string') {
                                    menuName = item.sName;
                                } else if (typeof item.fName === 'string') {
                                    menuName = item.fName;
                                }
                                if (typeof item.tUrl === 'string') {
                                    menuLink = '/' + item.tUrl;
                                } else if (typeof item.sUrl === 'string') {
                                    menuLink = '/' + item.sUrl;
                                } else {
                                    menuLink = '/index.php';
                                }
                                if (typeof item.fCode === 'string') {
                                    menuLink = item.fCode + menuLink;
                                }
                                // html.push('<li role="separator" class="divider"></li>');
                                html.push('<li class="dropdown-item"><a href="../' + menuLink + '">' + menuName + '</a></li>');
                            });
                            dropdown_menu.find('li:gt(0)').remove();
                            dropdown_menu.append(html.join(''));
                        } else {
                            if (dropdown_menu.find('.dropdown-noitem').length == 0) {
                                dropdown_menu.find('li:gt(0)').remove();
                                dropdown_menu.append('<li class="dropdown-noitem">자주쓰는 메뉴를<br>설정해주세요.</li>');
                            }
                        }
                    } else {
                        console.log(arguments);
                    }
                }
            });
        });

        $('#dropDownOrderPresentation').on('show.bs.dropdown', function () {
            var dropdown_container = $(this);
            $.ajax('../base/main_setting_ps.php', {
                method: "post",
                data: {mode: gd_main_layer.order_presentation.get_id},
                global_complete: false,
                success: function () {
                    if (arguments[0].success === 'OK') {
                        var result = arguments[0].result;
                        if (result.length > 0) {
                            var html = [];
                            $.each(result, function (idx, item) {
                                // html.push('<li role="separator" class="divider"></li>');
                                html.push('<li class="dropdown-item"><a href="' + item.link + '">' + item.name + '<span class="dropdown-item-val"><span class="text-red">' + item.count + '</span>건</span></a></li>');
                            });
                            var dropdown_menu = dropdown_container.find('.dropdown-menu');
                            dropdown_menu.find('li:gt(0)').remove();
                            dropdown_menu.append(html.join(''));
                        }
                    } else {
                        console.log(arguments);
                    }
                }
            });
        });

        $('.js-setting-presentation').click(function () {
            $.post('/share/layer_presentation_setting.php', {mode: gd_main_layer.presentation.id}, function (data) {
                var options = {title: gd_main_layer.presentation.title, message: $(data), size: _bootstrap_dialog.SIZE_WIDE};
                _bootstrap_dialog.show(options);
            });
        });

        $('.js-setting-cs').bind('click', function () {
            $.post('/share/layer_cs_setting.php', null, function (data) {
                _bootstrap_dialog.show({
                    title: '문의/답변관리 조회설정 ',
                    message: $(data)
                });
            });
        });

        $('.js-setting-order').bind('click', function () {
            var layer_id = this.dataset.role;
            var title = (layer_id === gd_main_layer.order_presentation.id) ? gd_main_layer.order_presentation.title : gd_main_layer.order_setting.title;
            $.post('../base/layer_order_setting.php', {mode: layer_id}, function (data) {
                var options = {title: title, message: $(data)};
                _bootstrap_dialog.show(options);
            });
        });

        $('.js-setting-favorite-menu').bind('click', function () {
            $.post('/share/layer_favorite_menu.php', {mode: gd_main_layer.favorite_menu.id}, function (data) {
                var options = {title: gd_main_layer.favorite_menu.title, message: $(data), size: _bootstrap_dialog.SIZE_WIDE};
                _bootstrap_dialog.show(options);
            });
        });

        $('#headerSearchType').change(function () {
            if (this.value === 'member') {
                layer_member_search();
                document.getElementById('headerSearchType').options[0].selected = true;
            }
        });

        $('#headerSearchKeyword').keyup(function (e) {
            if (e.keyCode === 13 && this.value.length > 1) {
                $.ajax('../base/main_setting_ps.php', {
                    method: "post",
                    global_complete: false,
                    data: {mode: "searchMenu", keyword: this.value},
                    success: function () {
                        var result = arguments[0].result;
                        var optionHtml = [];
                        $.each(result, function (idx, item) {
                            var link = '../' + item.fCode + '/' + item.tUrl;
                            optionHtml.push('<option value="' + link + '">' + item.tName + '</option>');
                        });
                        var $headerSearchMenuList = $('#headerSearchMenuList');
                        $headerSearchMenuList.closest('div').removeClass('display-none');
                        $headerSearchMenuList.find('option').remove();
                        if (optionHtml.length == 0) {
                            $headerSearchMenuList.append('<option>검색된 메뉴가 없습니다.</option>');
                        } else {
                            $headerSearchMenuList.append(optionHtml.join(''));
                        }
                    }
                });
            }
        });

        $('#headerSearchMenuList').on('click', function () {
            location.href = this.value;
        });

        $(window).click(function () {
            var $headerSearchMenuList = $('#headerSearchMenuList').closest('div');
            if ($headerSearchMenuList.hasClass('display-none') === false) {
                $headerSearchMenuList.addClass('display-none');
            }
        });
    });
</script>
