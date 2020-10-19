<style>
    .permission-kind {
        width:100%;
    }

    .permission-kind > ul {
        margin:0;
        padding:0;
        list-style:none;
        width:100%;
    }
<?php
if ($adminMenuType == 's') {
?>
    .permission-kind > ul > li {
        float:left;
        width:300px;
        min-height:350px;
    }
<?php
} else if ($adminMenuType == 'd') {
?>
    .permission-kind > ul > li {
        float:left;
        width:300px;
        min-height:550px;
    }
<?php
}
?>
    .permission-kind table > thead th {
        text-align:left;
    }

    .permission-kind table > thead th > label {
        font-weight:bold;
    }

    .js-layer-permission {
        float:right;
    }

    .layer-permission-last {
        position:absolute;
        width:300px;
        background-color:#fff;
        padding:10px;
        border:solid 1px;
    }

    .layer-permission-last > h4 {
        font-weight:bold;
        padding:10px;
        border-bottom:solid 1px;
    }

    .layer-permission-last > ul {
        margin:0;
        padding:0;
        list-style:none;
        width:100%;
    }

    .layer-permission-last > ul > li {
        padding-left:10px;
        width:100%;
        height:30px;
    }

    .layer-permission-close {
        width:280px;
        height:50px;
        text-align:center;
    }

    #clear_div {
        clear:both;
    }

    .permission-item > td {
        width:100%;
    }

    .permission-item > td > table > td {
        width:50%;
        text-align:center;
    }
</style>

<div class="table-title">
    접근 권한
    <span class="notice-info">접근 권한 설정은 메뉴의 상세설정 하위 메뉴 중 최소 1개 이상 설정하셔야 합니다.</span>
</div>
<div class="permission-kind">
    <ul>
        <?php
        foreach ($menuTreeList['top'] as $menuTreeKey => $menuTreeVal) {
            if ($menuTreeKey == 'godo00519' || $menuTreeKey == 'godo00467' || $menuTreeKey == 'godo00470' || $menuTreeKey == 'godo00611') {
                // 본사 - [메뉴 관리, 관리자 메인, 시스템] // 공급사 - [관리자 메인] 는 제외
            } else {
                ?>
                <li>
                    <table class="table table-cols">
                        <thead>
                        <tr>
                            <th>
                                <label class="hand">
                                    <input type="checkbox" id="permission_<?= $menuTreeKey; ?>" name="permission_1[]" value="<?= $menuTreeKey; ?>" class="js-checkall" data-target-id="permission_<?= $menuTreeKey; ?>" <?= gd_isset($checked['permission_1'][$menuTreeKey]); ?> /><?= $menuTreeVal['name']; ?>
                                </label>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // 2차메뉴 숨김처리
                        $hiddenSubMenu = [
                            'godo00780', // 모바일앱
                        ];
                        foreach ($menuTreeVal['mid'] as $subTreeKey => $subTreeVal) {
                            if ($subTreeKey == 'godo00644' || ($paycosearchHide && $subTreeKey == 'godo00576')) {
                                // 본사 - [언어번역 관리] / 공급사 - [] , 페이코서치(godo00576)는 제외
                            } else {
                                if (in_array($subTreeKey, $hiddenSubMenu)) {
                                    $hiddenStyle = 'class="display-none"';
                                }
                                ?>
                                <tr <?=$hiddenStyle;?>>
                                    <td>
                                        <label class="hand">
                                            <input type="checkbox"
                                                   id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>"
                                                   name="permission_2[<?= $menuTreeKey; ?>][]"
                                                   value="<?= $subTreeKey; ?>" class="js-checkall"
                                                   data-target-id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>" <?= gd_isset($checked['permission_2'][$menuTreeKey][$subTreeKey]); ?> /><?= $subTreeVal['name']; ?>
                                        </label>
                                        <button type="button" id="button_<?= $subTreeKey; ?>"
                                                data-no="<?= $subTreeKey; ?>" class="js-layer-permission">상세설정
                                        </button>
                                    </td>
                                    <div class="layer-permission-last" id="layer_last_<?= $subTreeKey; ?>">
                                        <h4><?= $menuTreeVal['name']; ?> > <?= $subTreeVal['name']; ?></h4>
                                        <ul>
                                            <?php
                                            foreach ($subTreeVal['last'] as $lastTreeKey => $lastTreeVal) {
                                                if ($lastTreeKey == 'godo00042' || $lastTreeKey == 'godo00294' || ($paycosearchHide && ($lastTreeKey == 'godo00577' || $lastTreeKey == 'godo00578' || $lastTreeKey == 'godo00751'))) {
                                                    // 본사 - [무통장 입금 은행 관리, 방문자 설정] / 공급사 - [] , 페이코서치(godo00577, godo00578, godo00751)는 제외
                                                } else {
                                                    ?>
                                                    <li>
                                                        <label class="checkbox-inline">
                                                            <input type="checkbox"
                                                                   id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>_<?= $lastTreeKey; ?>"
                                                                   name="permission_3[<?= $subTreeKey; ?>][]"
                                                                   value="<?= $lastTreeKey; ?>" <?= gd_isset($checked['permission_3'][$subTreeKey][$lastTreeKey]); ?> /><?= $lastTreeVal['name']; ?>
                                                        </label>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                        <button type="button" class="layer-permission-close">닫기</button>
                                    </div>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </li>
                <?php
            }
        }
        ?>
    </ul>
</div>

<div id="clear_div"></div>
<div class="table-title">
    기능 권한
</div>
<?php
if ($adminMenuType == 'd') {
    include_once "_manage_function_auth_center.php";
} else if ($adminMenuType == 's') {
    include_once "_manage_function_auth_scm.php";
}
?>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.layer-permission-last').hide();
        $('.js-layer-permission').click(function (e) {
            $('#layer_last_'+$(this).data('no')).show();
        });
        $('.layer-permission-close').click(function () {
            $(this).parents('.layer-permission-last').hide();
        });
<?php
if ($adminMenuType == 's') {
?>
        $('input[type="checkbox"][name^="functionAuth["]').prop('disabled', true);

    <?php
    foreach ($disabledScript as $disabledKey => $disabledVal) {
    ?>
        $("input[type='checkbox'][name='functionAuth[<?= $disabledKey; ?>]'").prop('disabled', <?= $disabledVal; ?>);
    <?php
        if (($disabledKey == 'goodsStockModify' && $disabledVal == 'false') === false && $dataFunctionAuth['functionAuth']['goodsStockModify'] != 'y') {
    ?>
        $('#permission-default input[name="functionAuth[goodsStockModify]"]').prop('checked', false);
    <?php
        }
    }
} else { ?>
        $('input[type="checkbox"][name^="functionAuth["]').prop('disabled', false);
<?php
}?>
    });
    //-->
</script>


