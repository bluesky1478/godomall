<form id="frmGoods" name="frmGoods" target="ifrmProcess" action="../goods/display_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="main_<?= $data['mode']; ?>"/>
    <input type="hidden" name="kind" value="event"/>
    <?php if ($data['mode'] == 'modify') { ?>
        <input type="hidden" name="sno" value="<?= gd_isset($data['sno']); ?>" id="eventSno" />
    <?php } ?>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./event_sale_list.php');"/>
            <input type="submit" value="저장" class="btn btn-red"/>

        </div>
    </div>


    <div class="table-title gd-help-manual">
        기본정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">기획전명</th>
            <td colspan="3">
                <label title=""><input type="text" name="themeNm" value="<?= gd_isset($data['themeNm']); ?>"
                                       class="form-control js-maxlength" maxlength="30"/></label>
            </td>
        </tr>
        <?php if ($data['mode'] == 'modify') { ?>
            <tr>
                <th>기획전페이지 주소</th>
                <td colspan="3">
                    <ul style="padding:0px;list-style: none">
                        <li class="js-clipboard-pc" style="padding-bottom:3px">
                            <button type="button" title="<?= $data['themeNm']; ?>" data-clipboard-text="<?= gd_isset($data['eventSaleUrl']) ?>"
                                    class="btn btn-info btn-xs width-3xs js-clipboard">PC
                            </button> <?= gd_isset($data['eventSaleUrl']) ?></li>
                        <li class="js-clipboard-mobile">
                            <button type="button" title="<?= $data['themeNm']; ?>" data-clipboard-text="<?= gd_isset($data['mobileEventSaleUrl']) ?>"
                                    class="btn btn-info btn-xs width-3xs js-clipboard">모바일
                            </button> <?= gd_isset($data['mobileEventSaleUrl']) ?></li>
                    </ul>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th class="require">진행기간</th>
            <td colspan="3">
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" name="displayStartDate[date]" class="form-control" placeholder="수기입력 가능" value="<?= gd_isset($data['displayStartDate']['date']) ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <input type="text" name="displayStartDate[time]" class="form-control js-timepicker" name="datepicker2" placeholder="수기입력 가능"
                           value="<?= gd_isset($data['displayStartDate']['time']) ?>">
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="displayEndDate[date]" class="form-control" placeholder="수기입력 가능" value="<?= gd_isset($data['displayEndDate']['date']) ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <input type="text" name="displayEndDate[time]" class="form-control js-timepicker" name="datepicker2" placeholder="수기입력 가능"
                           value="<?= gd_isset($data['displayEndDate']['time']) ?>">
                </div>
            </td>
        </tr>
        <tr>
            <th>진열유형</th>
            <td colspan="3" id="eventDisplayCategoryArea">
                <label class="radio-inline">
                    <span id="eventDisplayCategory_n"><input type="radio" name="displayCategory" value="n" <?= gd_isset($checked['displayCategory']['n']); ?>/>일반형</span>
                    <img src="/admin/gd_share/img/event/event_general_type.png" border="0" />
                </label>
                <label class="radio-inline">
                    <span id="eventDisplayCategory_g"><input type="radio" name="displayCategory" value="g" <?= gd_isset($checked['displayCategory']['g']); ?>/>그룹형</span>
                    <img src="/admin/gd_share/img/event/event_group_type.png" border="0" />
                </label>
            </td>
        </tr>
        <tr>
            <th>노출범위</th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="device" value="yy" <?= gd_isset($checked['device']['yy']); ?>/>PC+모바일</label>
                <label class="radio-inline"><input type="radio" name="device" value="yn" <?= gd_isset($checked['device']['yn']); ?>/>PC쇼핑몰</label>
                <label class="radio-inline"><input type="radio" name="device" value="ny" <?= gd_isset($checked['device']['ny']); ?>/>모바일쇼핑몰</label>
            </td>
        </tr>
        <tr>
            <th>이벤트내용</th>
            <td colspan="3">
                <div class="desc_box">
                    <ul class="nav nav-tabs">
                        <li class="editor-tab active" data-role="1" id="btnDescriptionShop"><a href="#textareaDescriptionShop" data-toggle="tab">PC 쇼핑몰</a></li>
                        <li class="editor-tab" data-role="2" id="btnDescriptionMobile"><a href="#textareaDescriptionMobile" data-toggle="tab">모바일 쇼핑몰</a></li>
                        <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['descriptionSameFl']['y']); ?> name="descriptionSameFl"/> PC/모바일 상세설명 동일사용</label></li>
                    </ul>
                </div>
                <div class="tab-content clearfix">
                    <div class="tab-pane active"  id="textareaDescriptionShop">
                        <textarea name="pcContents" rows="3" style="width:100%; height:400px;" id="editor1"
                                  class="form-control"><?php echo $data['pcContents']; ?></textarea>
                    </div>
                    <div class="tab-pane "  id="textareaDescriptionMobile">
                        <textarea name="mobileContents" rows="3" style="width:100%; height:400px;" id="editor2"
                                  class="form-control"><?php echo $data['mobileContents']; ?></textarea>
                    </div>
                </div>
            </td>
        </tr>

        <tr class="js-excludeGroup">
            <th>진열방법선택</th>
            <td colspan="3">
                <div class="form-inline">
                    <?= gd_select_box('sort', 'sort', $data['sortList'], null, $data['sort'], null); ?>
                </div>
            </td>
        </tr>
        <tr class="js-excludeGroup">
            <th>PC쇼핑몰 테마선택</th>
            <td>
                <div class="form-inline">
                    <input type="hidden" name="themeCdChk" value="<?= $data['themeCd'] ?>">
                    <select name="themeCd" onchange="viewThemeConfig(this.value,'p');" class="form-control input-sm">
                    </select>
                    <input type="button" class="btn btn-sm btn-gray" value="테마등록" onclick="add_theme_popup('n')"/>
                </div>
            </td>
            <th>모바일 쇼핑몰 테마선택</th>
            <td>
                <div class="form-inline">
                    <input type="hidden" name="mobileThemeCdChk" value="<?= $data['mobileThemeCd'] ?>">
                    <select name="mobileThemeCd" onchange="viewThemeConfig(this.value,'m');" class="form-control input-sm">
                    </select>
                    <input type="button" class="btn btn-sm btn-gray" value="테마등록" onclick="add_theme_popup('y')"/>
                </div>
            </td>
        </tr>
        <tr class="js-excludeGroup">
            <th>상단 더보기 노출 상태</th>
            <td> <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="moreTopFl" value="y" <?=gd_isset($checked['moreTopFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="moreTopFl" value="n"  <?=gd_isset($checked['moreTopFl']['n']);?>/>노출안함</label>
                </div>
            </td>
            <th>하단 더보기 노출 상태</th>
            <td><div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="moreBottomFl" value="y" <?=gd_isset($checked['moreBottomFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="moreBottomFl" value="n"  <?=gd_isset($checked['moreBottomFl']['n']);?>/>노출안함</label>
                </div>
            </td>
        </tr>

    </table>

    <div class="theme-info js-excludeGroup" id="pc-theme-info">
        <div class="table-title gd-help-manual">
            선택된 PC쇼핑몰 테마정보
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="event-pc-theme-info"><span>닫힘</span></button></span>
        </div>
        <input type="hidden" id="depth-toggle-hidden-event-pc-theme-info" value="<?=$toggle['event-pc-theme-info_'.$SessScmNo]?>">
        <div id="depth-toggle-line-event-pc-theme-info" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-event-pc-theme-info">
            <table class="table table-cols" id="event-pc-theme-info">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>이미지 설정</th>
                    <td colspan="3" id="tbl_p_theme_imageCdNm"></td>
                </tr>
                <tr>
                    <th>상품 노출 개수</th>
                    <td colspan="3" id="tbl_p_theme_cntNm"></td>
                </tr>
                <tr>
                    <th>품절상품 노출</th>
                    <td id="tbl_p_theme_soldOutFlNm"></td>
                    <th>품절상품 진열 상품</th>
                    <td id="tbl_p_theme_soldOutDisplayFlNm"></td>
                </tr>
                <tr>
                    <th>품절 아이콘 노출</th>
                    <td id="tbl_p_theme_soldOutIconFlNm"></td>
                    <th>아이콘 노출</th>
                    <td id="tbl_p_theme_iconFlNm"></td>
                </tr>
                <tr>
                    <th>노출항목 설정</th>
                    <td colspan="3" id="tbl_p_theme_displayFieldNm"></td>
                </tr>
                <tr>
                    <th>갤러리형</th>
                    <td colspan="3" id="tbl_p_theme_displayTypeNm"></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="theme-info js-excludeGroup" id="mobile-theme-info">
        <div class="table-title gd-help-manual">
            선택된 모바일 쇼핑몰 테마정보
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="event-mobile-theme-info"><span>닫힘</span></button></span>
        </div>
        <div>
            <input type="hidden" id="depth-toggle-hidden-event-mobile-theme-info" value="<?=$toggle['event-mobile-theme-info_'.$SessScmNo]?>">
            <div id="depth-toggle-line-event-mobile-theme-info" class="depth-toggle-line display-none"></div>
            <div id="depth-toggle-layer-event-mobile-theme-info">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>이미지 설정</th>
                        <td colspan="3" id="tbl_m_theme_imageCdNm"></td>
                    </tr>
                    <tr>
                        <th>상품 노출 개수</th>
                        <td colspan="3" id="tbl_m_theme_cntNm"></td>
                    </tr>
                    <tr>
                        <th>품절상품 노출</th>
                        <td id="tbl_m_theme_soldOutFlNm"></td>
                        <th>품절상품 진열 상품</th>
                        <td id="tbl_m_theme_soldOutDisplayFlNm"></td>
                    </tr>
                    <tr>
                        <th>품절 아이콘 노출</th>
                        <td id="tbl_m_theme_soldOutIconFlNm"></td>
                        <th>아이콘 노출</th>
                        <td id="tbl_m_theme_iconFlNm"></td>
                    </tr>
                    <tr>
                        <th>노출항목 설정</th>
                        <td colspan="3" id="tbl_m_theme_displayFieldNm"></td>
                    </tr>
                    <tr>
                        <th>갤러리형</th>
                        <td colspan="3" id="tbl_m_theme_displayTypeNm"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="form-inline">
        <div class="table-title gd-help-manual js-excludeGroup flo-left">
            진열 상품 설정
        </div>
        <?php if($data['mode'] === 'modify' && $data['displayCategory'] !== 'g'){ ?>
        <div class="flo-right">
            <input type="button" class="btn btn-sm btn-gray js-goods-price-modify" value="빠른 가격 수정" />
            <input type="button" class="btn btn-sm btn-gray js-goods-mileage-modify" value="빠른 마일리지/할인 수정" />
        </div>
        <?php } ?>
    </div>

    <div class="js-excludeGroup">
        <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows">
            <thead>
            <tr id="goodsRegisteredTrArea">
                <th class="width2p"><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="itemGoodsNo[]"/></th>
                <th class="width2p center">번호</th>
                <th class="width5p center">이미지</th>
                <th>상품명</th>
                <th class="width10p center">판매가</th>
                <th class="width10p center">공급사</th>
                <th class="width5p center">재고</th>
                <th class="width5p center">품절여부</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count(gd_isset($data['goodsNo']))) {
                foreach ($data['goodsNo'] as $key => $val) {
                    $val = $val[0];
                    $stockText = "";

                    if ($val['soldOutFl'] == 'y') {
                        $stockText = "품절";
                    }
                    else {
                        $stockText = "정상";
                    }

                    // 상품 재고
                    if ($val['stockFl'] == 'n') {
                        $totalStock = '∞';
                    } else {
                        $totalStock = number_format($val['totalStock']);
                        if ($val['totalStock'] == 0) $stockText = "품절";
                    }
                    ?>

                    <tr id="tbl_add_goods_<?= $val['goodsNo']; ?>"
                        <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], array_values($data['fixGoodsNo']))) { ?>style='background:#d3d3d3' class="add_goods_fix"
                        <?php } else { ?>class="add_goods_free" <?php } ?>>
                        <td class="center">
                            <input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
                            <input type="hidden" name="itemGoodsPrice[]" value="<?= gd_currency_display($val['goodsPrice']) ?>"/>
                            <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                            <input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
                            <input type="hidden" name="itemImage[]"
                                   value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
                            <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                            <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                            <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                            <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
                            <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?= $val['goodsNo']; ?>" value="<?= $val['goodsNo']; ?>"/></td>
                        <td class="center number" id="addGoodsNumber_<?= $val['goodsNo']; ?>"><?= $key + 1 ?></td>
                        <td class="center"><?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                        <td>
                            <a href="../goods/goods_register.php?goodsNo=<?= $val['goodsNo'] ?>" target="_blank"><?= $val['goodsNm'] ?></a>
                            <input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
                            <input type="checkbox" name="sortFix[]" id="layer_sort_fix_<?= $val['goodsNo']; ?>"
                                   value="<?= $val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], $data['fixGoodsNo'])) {
                                echo "checked='true'";
                            } ?> style="display:none">
                        </td>
                        <td class="center"><?= gd_currency_display($val['goodsPrice']) ?></td>
                        <td class="center"><?= $val['scmNm']; ?></td>
                        <td class="center"><?= $totalStock ?></td>
                        <td class="center"><?= $stockText ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr id="tbl_add_goods_tr_none">
                    <td colspan="11" class="no-data">선택된 상품이 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button class="btn btn-white checkDelete" type="button" onclick="delete_option()">선택 삭제</button>
            </div>
            <div class="pull-right">
                <button class="btn btn-white checkRegister" type="button" onclick="goods_search_popup()">상품 선택</button>
            </div>
        </div>
    </div>

    <!-- 그룹형 항목 --><!-- 그룹형 항목 --><!-- 그룹형 항목 -->
    <div class="js-includeGroup">
        <div class="table-title gd-help-manual">
            그룹 추가
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col />
            </colgroup>
            <tr>
                <th>그룹추가</th>
                <td>
                    <button class="btn btn-white" id="eventSaleGroupRegister" type="button">그룹 등록</button>
                    <button class="btn btn-white" id="eventSaleGroupLoad" type="button">그룹 불러오기</button>
                </td>
            </tr>
        </table>

        <div class="table-title gd-help-manual">
            그룹 리스트
        </div>

        <div class="table-action mgb0 mgt0 bg-clear" style="border-top: 0px;">
            <div class="btn-group">
                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">맨아래 </button>
                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">아래</button>
                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">위</button>
                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">맨위</button>
            </div>
        </div>
        <table class="table table-rows" id="eventGroupLayout">
            <colgroup>
                <col class="width-3xs" />
                <col class="width-3xs" />
                <col />
                <col class="width-xs" />
                <col class="width-2xs" />
                <col class="width-3xs" />
                <col class="width-3xs" />
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="eventGroupCheckNo"/></th>
                <th>진열순서</th>
                <th>그룹명</th>
                <th>진열상품수</th>
                <th>정보수정</th>
                <th>복사</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($data['eventGroup'] as $key => $eventGroup){
                ?>
                <tr class="center">
                    <td>
                        <input type="checkbox" name="eventGroupCheckNo[]" value="" />
                        <input type="hidden" name="eventGroupNo[]" value="<?php echo $eventGroup['sno']; ?>" /><!-- 실제 event group 번호-->
                        <input type="hidden" name="eventGroupTmpNo[]" value="" /><!-- 임시 event group 번호-->
                    </td>
                    <td class="js-eventGroupIndexArea"><?php echo ++$key; ?></td>
                    <td class="js-eventGroupNameArea"><?php echo $eventGroup['groupName']; ?></td>
                    <td class="js-eventGroupCountArea"><?php echo number_format($eventGroup['groupGoodsCount']); ?></td>
                    <td><button type="button" class="btn btn-white btn-sm js-eventGroupModify">정보수정</button></td>
                    <td><button type="button" class="btn btn-white btn-sm js-eventGroupCopy">복사</button></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white" id="eventGroupSelectDelete">선택 삭제</button>
            </div>
        </div>
    </div>
    <!-- 그룹형 항목 --><!-- 그룹형 항목 --><!-- 그룹형 항목 -->

    <div class="table-title gd-help-manual">
        기획전 개별 SEO 태그 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="seoTag"><span>닫힘</span></button></span>
        <div class="pull-right form-inline">
            <button type="button" class="btn btn-sm btn-gray js-code-view" data-target="event">치환코드 보기</button>
        </div>
    </div>
    <?php include($seoTagFrm); ?>

</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<!--<script type="text/javascript" src="--><?//= PATH_ADMIN_GD_SHARE ?><!--script/smart/js/editorLoad.js" charset="utf-8"></script>-->
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmGoods").validate({
            submitHandler: function (form) {
                oEditors.getById["editor1"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },

            rules: {
                themeNm: {
                    required: true,
                    maxlength: 30,
                    addGoods: true,
                },
                'displayStartDate[date]': {
                    required: true,
                },
                'displayEndDate[date]': {
                    required: true,
                },
                themeCd: {
                    required: function () {
                        if($("[name=device][value='ny']").is(':checked') == false && $("[name=displayCategory][value='g']").is(':checked') == false){
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                },
                mobileThemeCd: {
                    required: function () {
                        if($("[name=device][value='yn']").is(':checked') == false && $("[name=displayCategory][value='g']").is(':checked') == false){
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                }

            },
            messages: {
                themeNm: {
                    required: '기획전명을 입력해주세요.',
                    maxlength: '최대 {0} 자까지 입력해주세요.'
                },
                'displayStartDate[date]': {
                    required: '진행기간을 입력해주세요.',
                },
                'displayEndDate[date]': {
                    required: '진행기간을 입력해주세요.',
                },
                themeCd: {
                    required: 'PC 쇼핑몰 테마를 선택해주세요.'
                },
                mobileThemeCd: {
                    required: '모바일 쇼핑몰 테마를 선택해주세요.'
                },

            }
        });

        $.validator.addMethod("addGoods", function (value, element) {
            var displayCategory = $(':radio[name="displayCategory"]:checked').val();

            if(displayCategory === 'n'){
                //일반형
                if ($('input[name="itemGoodsNo[]"]').length == 0) {
                    return false;
                }
            }
            else if(displayCategory === 'g'){
                if ($('input[name="eventGroupCheckNo[]"]').length == 0) {
                    return false;
                }
            }
            else {}

            return true;
        }, " 기획전을 진행할 상품(그룹)을 선택해주세요.");

        $('input[name=device]').bind('click', function () {
            $('.js-clipboard-pc').show();
            $('.js-clipboard-mobile').show();
            $('.theme-info').show();

            if ($(this).val() == 'ny') { //모바일만일때
                if($("input[name='descriptionSameFl']").prop('checked')) {
                    $('#btnDescriptionShop>a').trigger('click');
                }
                else {
                    $('#btnDescriptionMobile>a').trigger('click');
                }

                $('select[name=themeCd]').prop('disabled', true);
                $('select[name=mobileThemeCd]').prop('disabled', false);
                $('.js-clipboard-pc').hide();
                $('#pc-theme-info').hide();
            }
            else if ($(this).val() == 'yn') {     //pc만
                $('#btnDescriptionShop>a').trigger('click');
                $('select[name=themeCd]').prop('disabled', false);
                $('select[name=mobileThemeCd]').prop('disabled', true);
                $('.js-clipboard-mobile').hide();
                $('#mobile-theme-info').hide();
            }
            else {
                $('#btnDescriptionShop>a').trigger('click');
                $('select[name=themeCd]').prop('disabled', false);
                $('select[name=mobileThemeCd]').prop('disabled', false);
            }

            if($(':radio[name="displayCategory"]:checked').val() === 'g'){
                $('#pc-theme-info').hide();
                $('#mobile-theme-info').hide();
            }
        });

        //PC/모바일 상세설명 동일사용 를 체크시 이벤트내용: PC쇼핑몰 노출
        $("input[name='descriptionSameFl']").click(function () {
            if($("input[name='descriptionSameFl']").prop('checked')) {
                $('#btnDescriptionShop>a').trigger('click');
            }
        });
        //이벤트내용: 모바일 쇼핑몰 클릭시 'PC/모바일 상세설명 동일사용' 체크 여부에 따라 이벤트 막음
        $("#btnDescriptionMobile>a").click(function (e) {
            if($("input[name='descriptionSameFl']").prop('checked')) {
                $(this).blur();
                e.preventDefault();
                return false;
            }
        });

        //진열유형에 따른 디스플레이 항목 변경
        $("input[name='displayCategory']").click(function () {
            if('<?php echo $data[mode]; ?>' === 'modify'){
                var addNewEl = $("#eventDisplayCategory_" +$(this).val() ).clone(true, true);
                $("#eventDisplayCategoryArea").empty();
                $("#eventDisplayCategoryArea").append(addNewEl);
            }
            if($(this).val() === 'g'){
                $(".js-excludeGroup").hide();
                $(".js-includeGroup").show();
            }
            else {
                $(".js-excludeGroup").show();
                $(".js-includeGroup").hide();
            }
        });

        //기획전 그룹형 그룹 등록
        $("#eventSaleGroupRegister").click(function () {
            window.open('../promotion/popup_event_sale_group_register.php', 'event_sale_group_register', 'width=1200,height=850,scrollbars=yes');
        });
        //기획전 그룹형 그룹 불러오기
        $("#eventSaleGroupLoad").click(function () {
            window.open('../promotion/popup_event_sale_group_load.php', 'event_sale_group_load', 'width=1200,height=850,scrollbars=yes');
        });
        //기획전 그룹형 그룹리스트 선택 삭제
        $("#eventGroupSelectDelete").on('click', function () {
            //실제 데이터는 삭제 값을 남겨 뒤에서 삭제처리
            //임시 데이터는 스케쥴러로 일괄 삭제 처리
            $.each($("input[name='eventGroupCheckNo[]']:checked"), function() {
                var targetEl = $(this).closest('tr');
                var eventGroupNo = targetEl.find("input[name='eventGroupNo[]']").val();
                if(parseInt(eventGroupNo) > 0){
                    $("#frmGoods").prepend('<input type="hidden" value="'+eventGroupNo+'" name="eventGroupDeleteNo[]">');
                }
                targetEl.remove();
            });
        });
        //기획전 그룹형 그룹 정보수정
        $(document).on("click",  ".js-eventGroupModify",function(){
            var parentEl = $(this).closest('tr');
            var eventGroupNo = parentEl.find("input[name='eventGroupNo[]']").val();
            var eventGroupTempNo = parentEl.find("input[name='eventGroupTmpNo[]']").val();

            window.open('../promotion/popup_event_sale_group_register.php?eventGroupNo='+eventGroupNo+'&eventGroupTempNo='+eventGroupTempNo, 'event_sale_group_register', 'width=1200,height=850,scrollbars=yes');
        });
        //기획전 그룹형 그룹 복사
        $(document).on("click",  ".js-eventGroupCopy",function(){
            var parentEl = $(this).closest('tr');
            var eventGroupNo = parentEl.find("input[name='eventGroupNo[]']").val();
            var eventGroupTempNo = parentEl.find("input[name='eventGroupTmpNo[]']").val();
            var parameters = {
                'mode': 'event_group_copy',
                'eventGroupNo': eventGroupNo,
                'eventGroupTempNo': eventGroupTempNo,
            };

            $.post( "../goods/display_ps.php", parameters, function( insertID ) {
                if(parseInt(insertID) > 0){
                    alert("복사되었습니다.");

                    var addNewTrEl = parentEl.clone(true, true);
                    var addIndex = $("#eventGroupLayout>tbody>tr").length + 1;
                    $("#eventGroupLayout>tbody").append(addNewTrEl);

                    addNewTrEl.find(".js-eventGroupIndexArea").html(addIndex);
                    addNewTrEl.find("input[name='eventGroupNo[]']").val('');
                    addNewTrEl.find("input[name='eventGroupTmpNo[]']").val(insertID);
                }
                else {
                    alert("복사를 실패하였습니다.\n다시 한번 시도해 주세요.");
                }
            });
        });

        //빠른 가격 수정
        $(document).on("click",  ".js-goods-price-modify",function(){
            window.open("../goods/goods_batch_price.php?detailSearch=y&event_text=" + $("input[name='themeNm']").val() + "&eventThemeSno=" + $("#eventSno").val() + "#eventSearchArea", '_blank');
        });
        //빠른 마일리지 수정
        $(document).on("click",  ".js-goods-mileage-modify",function(){
            window.open("../goods/goods_batch_mileage.php?locateOther=y&detailSearch=y&event_text=" + $("input[name='themeNm']").val() + "&eventThemeSno=" + $("#eventSno").val() + "#eventSearchArea", '_blank');
        });

        // ie에서 스마트에디터 오류로 인해 height값 넣어줌
        $('.editor-tab').bind('click',function(){
            var role = $(this).data('role') - 1;

            $('.tab-pane iframe').css('height', '450px');
            var editorIframe = $('.tab-pane iframe').get(role).contentWindow.document.getElementsByTagName('iframe');

            if (editorIframe[0]) {
                editorIframe[0].style.height = '400px';
            }
        });

        var move_row = {
            up: function () {
                var $checkbox = $('#eventGroupLayout').find(':checkbox[name="eventGroupCheckNo[]"]:checked');
                $checkbox.each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    $row.insertBefore($row.prev());
                });
            }, down: function () {
                var $checkbox = $('#eventGroupLayout').find(':checkbox[name="eventGroupCheckNo[]"]:checked');
                $($checkbox.get().reverse()).each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    var $next = $row.next();
                    var enableCheckboxLength = $next.find(':checkbox[name="eventGroupCheckNo[]"]').length;
                    if (enableCheckboxLength > 0) {
                        $row.insertAfter($next);
                    }
                });
            }, top: function () {
                var $checkbox = $('#eventGroupLayout').find(':checkbox[name="eventGroupCheckNo[]"]:checked');
                $checkbox.each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    var $targetRow = $(':checkbox[name="eventGroupCheckNo[]"]').first().closest('tr');
                    $row.insertBefore($targetRow);
                });
            }, bottom: function () {
                var $checkbox = $('#eventGroupLayout').find(':checkbox[name="eventGroupCheckNo[]"]:checked');
                $($checkbox.get().reverse()).each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    var $targetRow = $(':checkbox[name="eventGroupCheckNo[]"]').last().closest('tr');
                    $row.insertAfter($targetRow);
                });

            }
        };

        $('.js-moverow').on('click', function (e) {
            var $target = $(e.target);
            $moveChecked = $(':checked[name="eventGroupCheckNo[]"]', 'tbody');
            $moveUnChecked = $(':checkbox[name="eventGroupCheckNo[]"]:not(:checked)', 'tbody');
            if ($moveChecked.length > 0) {
                var direction = $target.data('direction');
                if (_.isUndefined(direction)) {
                    direction = $target.closest('button').data('direction');
                }
                switch (direction) {
                    case 'up':
                        move_row.up();
                        break;
                    case 'down':
                        move_row.down();
                        break;
                    case 'top':
                        move_row.top();
                        break;
                    case 'bottom':
                        move_row.bottom();
                        break;
                }
            }
            else {
                alert("선택된 그룹이 없습니다.");
            }
        });

        loadEditor();
        set_theme_list('y');
        set_theme_list('n');
        initDepthToggle(<?=$SessScmNo?>);

        $('input[name=device]:checked').trigger('click');
        $('input[name="displayCategory"]:checked').trigger('click');
    });

    /**
     * 테마보기
     */
    function viewThemeConfig(themeCd, device) {
        var parameters = {
            'mode': 'theme_ajax',
            'themeCd': themeCd
        };
        $.post("../goods/display_config_ps.php", parameters,
            function (data) {
                $.each(data, function (i, item) {

                    $("#tbl_" + device + "_theme_" + i).html(item);
                });
            }, "json");
    }

    /**
     * 상품 선택
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function goods_search_popup() {
        window.open('../share/popup_goods.php', 'popup_goods_search', 'width=1255, height=790, scrollbars=no, resizable=yes');
    }


    /**
     * 테마 입력
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function add_theme_popup(mobileFl) {
        if (mobileFl =="n") var addTheme = "themeCd";
        else var addTheme = "mobileThemeCd";
        window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate=F&addTheme='+addTheme+'&mobileFl='+mobileFl, 'member_crm', 'width=1210, height=700, scrollbars=yes');
    }

    function delete_option() {

        var chkCnt = $('input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 상품이 없습니다.');
            return;
        }
        if (confirm('선택한 ' + chkCnt + '개 상품을 삭제하시겠습니까?')) {
            $('input[name="itemGoodsNo[]"]:checked').each(function () {
                field_remove('tbl_add_goods_' + $(this).val());
            });

            var cnt = $('input[name="itemGoodsNo[]"]').length;

            $('input[name="itemGoodsNo[]"]').each(function (idx) {
                $("#addGoodsNumber_" + $(this).val()).html(idx+1);
            });

        }

    }

    function set_theme_list(mobileFl) {
        $.post('../goods/display_ps.php', {'mode': 'search_theme', 'mobileFl': mobileFl, 'themeCd': 'F'}, function (data) {

            var themeinfo = $.parseJSON(data);

            if ($.type(themeinfo) != 'array') var themeinfo = {};
            var addHtml = "<option value=''>==== 테마 선택 ====</option>";
            if (themeinfo) {
                $.each(themeinfo, function (key, val) {
                    addHtml += "<option value='" + val.themeCd + "'>" + val.themeNm + "</option>";
                });
            }

            if (mobileFl == 'y') {

                $('select[name="mobileThemeCd"]').html(addHtml);
                <?php if($data['mobileThemeCd']) { ?>
                $('select[name="mobileThemeCd"]').val("<?=$data['mobileThemeCd']?>");
                <?php } ?>
                viewThemeConfig($('select[name="mobileThemeCd"]').val(), 'm');
            }
            else {
                $('select[name="themeCd"]').html(addHtml);
                <?php if($data['themeCd']) { ?>
                $('select[name="themeCd"]').val("<?=$data['themeCd']?>");
                <?php } ?>
                viewThemeConfig($('select[name="themeCd"]').val(), 'p');
            }
        });


    }


    function setAddGoods(frmData) {

        var addHtml = "";


        $.each(frmData.info, function (key, val) {
            var stockText = "";

            if (val.soldOutFl == 'y') {
                stockText = "품절";
            }
            else {
                stockText = "정상";
            }

            // 상품 재고
            if (val.stockFl == 'n') {
                totalStock = '∞';
            } else {
                totalStock = val.totalStock;
                if (val.totalStock == 0) stockText = "품절";
            }


            if (val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            }
            else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }


            addHtml += '<tr id="tbl_add_goods_' + val.goodsNo + '" ' + tableCss + '>';
            addHtml += '<td class="center">';

            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="' + val.goodsNm + '" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="' + val.goodsPrice + '" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="' + val.scmNm + '" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="' + val.totalStock + '" />';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="' + val.brandNm + '" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="' + val.makerNm + '" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="' + val.image + '" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="' + val.soldOutFl + '" />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="' + val.stockFl + '" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_' + val.goodsNo + '"  value="' + val.goodsNo + '"/></td>';
            addHtml += '<td class="center number" id="addGoodsNumber_' + val.goodsNo + '">' + (key+1) + '</td>';
            addHtml += '<td class="center">' + decodeURIComponent(val.image) + '</td>';
            addHtml += '<td><a href="../goods/goods_register.php?goodsNo='+ val.goodsNo +'" target="_blank">' + val.goodsNm + '</a><input type="hidden" name="goodsNoData[]" value="' + val.goodsNo + '" /><input type="checkbox" name="sortFix[]" id="layer_sort_fix_' + val.goodsNo + '"  value="' + val.goodsNo + '" ' + sortFix + ' style="display:none"></td>';
            addHtml += '<td class="center">' + val.goodsPrice + '</td>';
            addHtml += '<td class="center">' + val.scmNm + '</td>';
            addHtml += '<td class="center">' + totalStock + '</td>';
            addHtml += '<td class="center">' + stockText + '</td>';
            addHtml += '</tr>';


        });
        $("#tbl_add_goods_set tbody").html(addHtml);

    }
    //-->
</script>
<script type="text/javascript">
    var uploadImages = [];
    var oEditors = [];
    var isLoadEditor = [];

    function addUploadImages(data) {
        uploadImages.push(data);
    }

    function cleanUploadImages() {
        uploadImages = null;
    }

    function loadEditor() {
        $('.editor-tab').each(function(idx){
            idx = idx + 1;

            if (isLoadEditor[idx] === true) {
                return;
            }
            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: "editor"+idx,
                sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.html",
                htParams: {
                    bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload: function () {
                        if (!uploadImages) {
                            return;
                        }
                        $.ajax({
                            method: "GET",
                            url: "/share/editor_file_uploader.php",
                            data: {mode: 'deleteGarbage', uploadImages: uploadImages.join('^|^')},
                            cache: false,
                        }).success(function (data) {
                        }).error(function (e) {
                        });
                    }
                }, //boolean
                fOnAppLoad: function () {
                    //예제 코드
                    //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                },
                fCreator: "createSEditor2"
            });

            isLoadEditor[idx] = true;
        });
    }
</script>

