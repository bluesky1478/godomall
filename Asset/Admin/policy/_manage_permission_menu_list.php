<style>
    #function_dropdown_godo00051 .dropdown-menu ol { height:280px; } /* 추가설정> 본사> 상품 */
    #function_dropdown_godo00099 .dropdown-menu ol { height:160px; } /* 추가설정> 본사> 주문 */
    #function_dropdown_godo00138 .dropdown-menu ol { height:80px; } /* 추가설정> 본사> 회원 */
    #function_dropdown_godo00176 .dropdown-menu ol { height:80px; } /* 추가설정> 본사> 게시판 */
    #function_dropdown_godo00271 .dropdown-menu ol { height:80px; } /* 추가설정> 본사> 공급사 */
    #function_dropdown_godo00292 .dropdown-menu ol { height:80px; } /* 추가설정> 본사> 통계 */
    #function_dropdown_godo00458 .dropdown-menu ol { height:80px; } /* 추가설정> 본사> 개발소스관리 */
    #function_dropdown_godo00384 .dropdown-menu ol { height:280px; } /* 추가설정> 공급사> 상품 */
    #function_dropdown_godo00416 .dropdown-menu ol { height:80px; } /* 추가설정> 공급사> 주문 */
    #function_dropdown_godo00431 .dropdown-menu ol { height:80px; } /* 추가설정> 공급사> 회원 */
    .loading {padding:0px !important; background-position: 50% 50% !important;} /* 로딩 배경이미지 속성 재정의 */
</style>

<!-- 노출 메뉴 목록 -->
<div class="exposure-top-menu-list-template">
    <ol>
        <li class="dropdown-item"><label><input type="checkbox" name="exposure_top_menu[]" value="all" class="js-not-checkall" data-target-name="exposure_top_menu" checked="checked"/> 전체 메뉴</label></li>
        <li class="dropdown-divider"></li>
        <?php
        foreach ($menuTopList as $code => $name) {
            ?>
            <li class="dropdown-item"><label><input type="checkbox" name="exposure_top_menu[]" value="<?= $code; ?>" /> <?= $name; ?></label></li>
            <?php
        }
        ?>
    </ol>
</div>

<div class="menu-list-head">
    <table class="table table-rows table-fixed mgb0">
        <thead>
        <tr>
            <th class="width10p center"><input type="checkbox" class="js-menu-checkall" <?= gd_isset($disabled['settingItem']); ?> /></th>
            <th class="center">메뉴명</th>
            <th class="center" style="width:150px">권한설정</th>
            <th class="width10p center">추가설정</th>
        </tr>
        </thead>
    </table>
</div>
<div class="menu-list-body">
    <table class="table table-rows table-fixed mgb0">
        <colgroup>
            <col class="width10p"/>
            <col/>
            <col style="width:150px"/>
            <col class="width10p"/>
        </colgroup>
        <?php
        foreach ($menuTreeList['top'] as $menuTreeKey => $menuTreeVal) {
            if ($disabled['settingItem'] != '') { // 설정 기능 disabled
                $disabled['chk_1'][$menuTreeKey] = $disabled['permission_1'][$menuTreeKey] = $disabled['settingItem'];
            }
            $default = (isset($menuTreeVal['default']) === true ? $menuTreeVal['default'] : 'writable'); // 기본권한 정의
            $defaulted[$default] = 'data-defaulted="defaulted"';
            if (($adminMenuType == 'd' && $isSuper == 'y') || $permissionFl == 's') { // 본사 최고운영자 또는 전체권한 경우 기본권한 부여
                $selected['permission_1'][$menuTreeKey][$default] = 'selected="selected"';
            } else if (isset($selected['permission_1'][$menuTreeKey]) === false) { // 수정일 때 권한 설정 안된 메뉴인 경우 "권한없음" 권한 부여
                $selected['permission_1'][$menuTreeKey][''] = 'selected="selected"';
            }

            // 하위 메뉴 노출 여부 정의
            $topSubDisplay = [];
            $topSubDisplay['data'] = 'hide';
            $topSubDisplay['menuIcon'] = 'ui-icon-stop';
            if (count($menuTreeVal['mid']) > 0) {
                $topSubDisplay['menuRoll'] = 'menu-roll';
                if ($viewMemuDepth > 1) {
                    $topSubDisplay['data'] = 'show';
                    $topSubDisplay['menuIcon'] = 'ui-icon-circle-minus';
                } else {
                    $topSubDisplay['style'] = 'display: none;';
                    $topSubDisplay['menuIcon'] = 'ui-icon-circle-plus';
                }
            }
            ?>
            <tbody>
            <tr class="permission-top" data-sub-display="<?= $topSubDisplay['data']; ?>">
                <td class="center">
                    <input type="checkbox"
                           name="chk[]"
                           data-target-id="permission_<?= $menuTreeKey; ?>" <?= gd_isset($disabled['chk_1'][$menuTreeKey]); ?> />
                </td>
                <td class="menu-name"><label class="<?= $topSubDisplay['menuRoll']; ?>" data-target-id="top_sub_<?= $menuTreeKey; ?>"><span class="ui-icon <?= $topSubDisplay['menuIcon']; ?>"></span><?= $menuTreeVal['name']; ?></label></td>
                <td class="center">
                    <select name="permission_1[<?= $menuTreeKey; ?>]" id="permission_<?= $menuTreeKey; ?>" <?= gd_isset($disabled['permission_1'][$menuTreeKey]); ?>>
                        <option value="individual" <?= gd_isset($selected['permission_1'][$menuTreeKey]['individual']); ?> <?= gd_isset($defaulted['individual']); ?> disabled="disabled">2차 메뉴 개별설정</option>
                        <option value="" <?= gd_isset($selected['permission_1'][$menuTreeKey]['']); ?> <?= gd_isset($defaulted['']); ?> <?= gd_isset($menuTreeVal['disabled']['']); ?>>권한없음</option>
                        <option value="readonly" <?= gd_isset($selected['permission_1'][$menuTreeKey]['readonly']); ?> <?= gd_isset($defaulted['readonly']); ?> <?= gd_isset($menuTreeVal['disabled']['readonly']); ?>>읽기</option>
                        <option value="writable" <?= gd_isset($selected['permission_1'][$menuTreeKey]['writable']); ?> <?= gd_isset($defaulted['writable']); ?> <?= gd_isset($menuTreeVal['disabled']['writable']); ?>>읽기+쓰기</option>
                    </select>
                </td>
                <td class="center">
                    <?php
                    if (is_array($functionList[$menuTreeKey]) === true && count($menuTreeKey) > 0) {
                        ?>
                        <div class="dropdown display-inline manager-dropdown function-auth-selection" id="function_dropdown_<?= $menuTreeKey; ?>">
                            <button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">보기</button>
                            <div class="dropdown-menu mgt10">
                                <div class="dropdown-header">추가 기능 권한 설정</div>
                                <ol>
                                    <?php
                                    foreach ($functionList[$menuTreeKey] as $code => $name) {
                                        switch ($code) {
                                            case 'goodsStockExceptView': // '상품 상세 재고 수정 제외' 항목
                                            case 'goodsStockModify': // '상품 재고 수정' 항목
                                                if ($adminMenuType == 'd' && $isSuper == 'y') { // 본사 최고운영자 수정 일 경우
                                                    $disabled['functionAuth'][$code] = 'data-disabled-lock="lock"';
                                                    if ($code != 'goodsStockExceptView' && $mode !== 'modify' && $reCall !== 'true') { // 등록 경우 "읽기+쓰기" 권한 부여
                                                        $checked['functionAuth'][$code]['y'] = 'checked="checked"';
                                                    }
                                                    break;
                                                }
                                            case 'workPermissionFl': // '개발권한' 항목
                                            case 'debugPermissionFl': // '디버그권한' 항목
                                                if ($adminMenuType == 'd' && $isSuper != 'y') { // 본사 부운영자 등록/수정 일 경우
                                                    $disabled['functionAuth'][$code] = 'data-disabled-lock="lock"';
                                                    if ($code != 'goodsStockExceptView' && $mode !== 'modify' && $reCall !== 'true') { // 등록 경우 "읽기+쓰기" 권한 부여
                                                        $checked['functionAuth'][$code]['y'] = 'checked="checked"';
                                                    }

                                                    // 디버그권한은 개발권한이 체크된 경우 체크박스 활성화 됨
                                                    if ($code == 'debugPermissionFl' && $checked['functionAuth']['workPermissionFl']['y'] == '') {
                                                        $disabled['functionAuth'][$code] .= 'disabled="disabled"';
                                                    }
                                                    break;
                                                }
                                            default:
                                                if ($adminMenuType == 'd' && $isSuper == 'y') { // 본사 최고운영자 수정 일 경우
                                                    $disabled['functionAuth'][$code] = 'disabled="disabled"';
                                                    $checked['functionAuth'][$code]['y'] = 'checked="checked"';
                                                } else if ($adminMenuType == 'd' && $isSuper != 'y') { // 본사 부운영자 등록/수정 일 경우
                                                    if ($permissionFl == 's') { // 전체권한 경우 disabled
                                                        $disabled['functionAuth'][$code] = 'disabled="disabled"';
                                                    }
                                                    if ($permissionFl == 's') { // 전체권한 경우 "읽기+쓰기" 권한 부여
                                                        $checked['functionAuth'][$code]['y'] = 'checked="checked"';
                                                    }
                                                } else if ($adminMenuType == 's' && $isSuper == 'y') { // 공급사 대표운영자 등록/수정 일 경우
                                                    if (gd_is_provider() !== true) { // 본사 ADMIN 에서 대표운영자 등록/수정 일 경우
                                                        $disabled['functionAuth'][$code] = 'data-disabled-lock="lock"';
                                                        if ($code != 'goodsStockExceptView' && $mode !== 'modify' && $reCall !== 'true') { // 등록 경우 "읽기+쓰기" 권한 부여
                                                            $checked['functionAuth'][$code]['y'] = 'checked="checked"';
                                                        }
                                                    } else { // 공급사 ADMIN 에서 대표운영자 수정 일 경우
                                                        $disabled['functionAuth'][$code] = 'data-disabled-lock="lock" disabled="disabled"';
                                                    }
                                                } else if ($adminMenuType == 's' && $isSuper != 'y') { // 공급사 부운영자 등록/수정 일 경우
                                                    if ($scmFunctionAuth[$code] != 'y') {
                                                        $disabled['functionAuth'][$code] = 'data-disabled-lock="lock" disabled="disabled"';
                                                        unset($checked['functionAuth'][$code]);
                                                    } else {
                                                        if ($permissionFl == 's') { // 전체권한 경우 disabled
                                                            $disabled['functionAuth'][$code] = 'disabled="disabled"';
                                                        }
                                                        if ($permissionFl == 's') { // 전체권한 경우 "읽기+쓰기" 권한 부여
                                                            $checked['functionAuth'][$code]['y'] = 'checked="checked"';
                                                        }
                                                    }
                                                }
                                                break;
                                        }
                                        ?>
                                        <li class="dropdown-item"><label><input type="checkbox" name="functionAuth[<?= $code; ?>]" data-value="<?= $code; ?>" value="y" <?= gd_isset($disabled['functionAuth'][$code]); ?> <?= gd_isset($checked['functionAuth'][$code]['y']); ?> /> <?= $name; ?></label></li>
                                        <?php
                                        // 속도 높이기 위해 불필요해진 변수 unset
                                        unset($disabled['functionAuth'][$code]);
                                        if ($code != 'workPermissionFl') unset($checked['functionAuth'][$code]);
                                        if ($code == 'debugPermissionFl') unset($checked['functionAuth']['workPermissionFl']);
                                        unset($code, $name);
                                    }
                                    ?>
                                </ol>
                                <div class="dropdown-footer"><button type="button" class="btn btn-white close-function-dropdown">닫기</button></div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            </tbody>
            <tbody id="top_sub_<?= $menuTreeKey; ?>" style="<?= $topSubDisplay['style']; ?>" class="top-menu-on">
            <?php
            foreach ($menuTreeVal['mid'] as $subTreeKey => $subTreeVal) {
                if ($disabled['settingItem'] != '') { // 설정 기능 disabled
                    $disabled['chk_2'][$menuTreeKey][$subTreeKey] = $disabled['permission_2'][$menuTreeKey][$subTreeKey] = $disabled['settingItem'];
                }
                $default = (isset($subTreeVal['default']) === true ? $subTreeVal['default'] : 'writable'); // 기본권한 정의
                $defaulted[$default] = 'data-defaulted="defaulted"';
                if (($adminMenuType == 'd' && $isSuper == 'y') || $permissionFl == 's') { // 본사 최고운영자 또는 전체권한 경우 기본권한 부여
                    $selected['permission_2'][$menuTreeKey][$subTreeKey][$default] = 'selected="selected"';
                } else if (isset($selected['permission_2'][$menuTreeKey][$subTreeKey]) === false) { // 수정일 때 권한 설정 안된 메뉴인 경우 "권한없음" 권한 부여
                    $selected['permission_2'][$menuTreeKey][$subTreeKey][''] = 'selected="selected"';
                }

                // 하위 메뉴 노출 여부 정의
                $midSubDisplay = [];
                $midSubDisplay['data'] = 'hide';
                $midSubDisplay['menuIcon'] = 'ui-icon-stop';
                if (count($subTreeVal['last']) > 0) {
                    $midSubDisplay['menuRoll'] = 'menu-roll';
                    if ($topSubDisplay['data'] == 'show' && $viewMemuDepth > 2) {
                        $midSubDisplay['data'] = 'show';
                        $midSubDisplay['menuIcon'] = 'ui-icon-circle-minus';
                    } else {
                        $midSubDisplay['style'] = 'display: none;';
                        $midSubDisplay['menuIcon'] = 'ui-icon-circle-plus';
                    }
                }
                ?>
                <tr class="permission-mid" data-sub-display="<?= $midSubDisplay['data']; ?>">
                    <td class="center">
                        <input type="checkbox"
                               name="chk[]"
                               data-target-id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>" <?= gd_isset($disabled['chk_2'][$menuTreeKey][$subTreeKey]); ?> />
                    </td>
                    <td class="menu-name"><label class="<?= $midSubDisplay['menuRoll']; ?>" data-target-id="mid_sub_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>"><span class="ui-icon <?= $midSubDisplay['menuIcon']; ?>"></span><?= $subTreeVal['name']; ?></label></td>
                    <td class="center">
                        <select name="permission_2[<?= $menuTreeKey; ?>][<?= $subTreeKey; ?>]" id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>" <?= gd_isset($disabled['permission_2'][$menuTreeKey][$subTreeKey]); ?>>
                            <option value="individual" <?= gd_isset($selected['permission_2'][$menuTreeKey][$subTreeKey]['individual']); ?> <?= gd_isset($defaulted['individual']); ?> disabled="disabled">3차 메뉴 개별설정</option>
                            <option value="" <?= gd_isset($selected['permission_2'][$menuTreeKey][$subTreeKey]['']); ?> <?= gd_isset($defaulted['']); ?> <?= gd_isset($subTreeVal['disabled']['']); ?>>권한없음</option>
                            <option value="readonly" <?= gd_isset($selected['permission_2'][$menuTreeKey][$subTreeKey]['readonly']); ?> <?= gd_isset($defaulted['readonly']); ?> <?= gd_isset($subTreeVal['disabled']['readonly']); ?>>읽기</option>
                            <option value="writable" <?= gd_isset($selected['permission_2'][$menuTreeKey][$subTreeKey]['writable']); ?> <?= gd_isset($defaulted['writable']); ?> <?= gd_isset($subTreeVal['disabled']['writable']); ?>>읽기+쓰기</option>
                        </select>
                    </td>
                    <td class="center"></td>
                </tr>
                <?php
                foreach ($subTreeVal['last'] as $lastTreeKey => $lastTreeVal) {
                    if ($disabled['settingItem'] != '') {
                        $disabled['chk_3'][$subTreeKey][$lastTreeKey] = $disabled['permission_3'][$subTreeKey][$lastTreeKey] = $disabled['settingItem'];
                    }
                    $default = (isset($lastTreeVal['default']) === true ? $lastTreeVal['default'] : 'writable'); // 기본권한 정의
                    $defaulted[$default] = 'data-defaulted="defaulted"';
                    if (($adminMenuType == 'd' && $isSuper == 'y') || $permissionFl == 's') { // 본사 최고운영자 또는 전체권한 경우 기본권한 부여
                        $selected['permission_3'][$subTreeKey][$lastTreeKey][$default] = 'selected="selected"';
                    } else if (isset($selected['permission_3'][$subTreeKey][$lastTreeKey]) === false) { // 수정일 때 권한 설정 안된 메뉴인 경우 "권한없음" 권한 부여
                        $selected['permission_3'][$subTreeKey][$lastTreeKey][''] = 'selected="selected"';
                    }
                    ?>
                    <tr class="permission-last" id="mid_sub_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>_<?= $lastTreeKey; ?>" style="<?= $midSubDisplay['style']; ?>">
                        <td class="center">
                            <input type="checkbox"
                                   name="chk[]"
                                   data-target-id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>_<?= $lastTreeKey; ?>" <?= gd_isset($disabled['chk_3'][$subTreeKey][$lastTreeKey]); ?> />
                        </td>
                        <td class="menu-name"><label><span class="ui-icon ui-icon-stop"></span><?= $lastTreeVal['name']; ?></label></td>
                        <td class="center">
                            <select name="permission_3[<?= $menuTreeKey; ?>][<?= $subTreeKey; ?>][<?= $lastTreeKey; ?>]" id="permission_<?= $menuTreeKey; ?>_<?= $subTreeKey; ?>_<?= $lastTreeKey; ?>" <?= gd_isset($disabled['permission_3'][$subTreeKey][$lastTreeKey]); ?>>
                                <option value="" <?= gd_isset($selected['permission_3'][$subTreeKey][$lastTreeKey]['']); ?> <?= gd_isset($defaulted['']); ?> <?= gd_isset($lastTreeVal['disabled']['']); ?>>권한없음</option>
                                <option value="readonly" <?= gd_isset($selected['permission_3'][$subTreeKey][$lastTreeKey]['readonly']); ?> <?= gd_isset($defaulted['readonly']); ?> <?= gd_isset($lastTreeVal['disabled']['readonly']); ?>>읽기</option>
                                <option value="writable" <?= gd_isset($selected['permission_3'][$subTreeKey][$lastTreeKey]['writable']); ?> <?= gd_isset($defaulted['writable']); ?> <?= gd_isset($lastTreeVal['disabled']['writable']); ?>>읽기+쓰기</option>
                            </select>
                        </td>
                        <td class="center"></td>
                    </tr>
                    <?php
                    // 속도 높이기 위해 불필요해진 변수 unset
                    unset($disabled['chk_3'][$subTreeKey][$lastTreeKey]);
                    unset($selected['permission_3'][$subTreeKey][$lastTreeKey]);
                    unset($lastTreeKey, $lastTreeVal, $default, $defaulted);
                }
                ?>
                <?php
                // 속도 높이기 위해 불필요해진 변수 unset
                unset($disabled['chk_2'][$menuTreeKey][$subTreeKey]);
                unset($selected['permission_2'][$menuTreeKey][$subTreeKey]);
                unset($disabled['chk_3'][$subTreeKey]);
                unset($selected['permission_3'][$subTreeKey]);
                unset($subTreeKey, $subTreeVal, $default, $defaulted);
            }
            ?>
            </tbody>
            <?php
            // 속도 높이기 위해 불필요해진 변수 unset
            unset($disabled['chk_1'][$menuTreeKey]);
            unset($selected['permission_1'][$menuTreeKey]);
            unset($disabled['chk_2'][$menuTreeKey]);
            unset($selected['permission_2'][$menuTreeKey]);
            unset($menuTreeKey, $menuTreeVal, $default, $defaulted);
        }

        // 속도 높이기 위해 불필요해진 변수 unset
        unset($disabled);
        unset($selected);
        ?>
    </table>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 기존 운영자 권한 불러오기 또는 권한 초기화, 운영자 권한 설정에서 개별 권한보기 할 때 권한 범위 셋팅
        set_menu_list_permissionFl('<?= $isSuper; ?>', '<?= $permissionFl; ?>', '<?= $changeType; ?>', <?= (gd_is_provider() ? 'true' : 'false'); ?>);

        // 노출 메뉴 목록 출력
        if ($('.exposure-top-menu-list-template').length) {
            $('.exposure-top-menu-list').html( $('.exposure-top-menu-list-template').html() );
            $('.exposure-top-menu-list-template').html('');
        }

        // 메뉴 리스트 메뉴별 Show/Hide 이벤트
        $('.menu-roll').click(view_menu_roll);

        // 메뉴 리스트 선택 체크박스 이벤트
        $('.js-menu-checkall').click(function () {
            set_menu_list_checkbox(this.checked);
        });

        // 권한설정(1차) 이벤트
        $('#menuList select[name^="permission_1"]').change(set_menu_permission_top);

        // 권한설정(2차) 이벤트
        $('#menuList select[name^="permission_2"]').change(set_menu_permission_mid);

        // 권한설정(3차) 이벤트
        $('#menuList select[name^="permission_3"]').change(set_menu_permission_last);

        // 추가설정(기능권한) 닫기 이벤트
        $('#menuList .close-function-dropdown').click(close_function_dropdown);

        // 추가설정(기능권한) > 개발권한 이벤트 (디버그권한 Disabled 처리)
        $('#menuList input[name="functionAuth[workPermissionFl]"]').click(set_debug_disable);

        // 공급사 대표운영자 등록/수정 일 경우 노티
        <?php if (gd_is_provider() === false && $adminMenuType == 's') { ?>
        $("#scmSuperNotice").removeClass('display-none').addClass('display-block');
        <?php } else { ?>
        $("#scmSuperNotice").removeClass('display-block').addClass('display-none');
        <?php } ?>
    });
    //-->
</script>