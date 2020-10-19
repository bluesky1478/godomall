<style>
    .couponzoneGroup .couponDisplayN .couponNoNm{font-style: italic; text-decoration: underline;}
    .couponzoneGroup .couponDisplayN .couponDisplayFl{color: #fa2828;}
</style>
<form id="frmCoupon" name="frmCoupon" action="coupon_ps.php" method="post" class="content_form" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="saveCouponzoneConfig"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <h5 class="table-title gd-help-manual">기본설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용여부</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="useFl" value="y" <?= $checked['useFl']['y']; ?> />사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="useFl" value="n" <?= $checked['useFl']['n']; ?> />사용안함
                    </label>
                </div>
                <div class="notice-info">
                    ‘사용함’설정 시 쿠폰존 페이지가 활성화됩니다.<br/>
                    쿠폰존 url : <?=URI_HOME?>event/couponzone.php
                    <a href="<?=URI_HOME?>event/couponzone.php" target="_blank" class="btn-link-underline">[PC쇼핑몰 화면보기]</a>
                    <a href="<?=URI_MOBILE?>event/couponzone.php" target="_blank" class="btn-link-underline">[모바일쇼핑몰 화면보기]</a>
                </div>
            </td>
        </tr>
        <!-- .autoDisplayY 자동노출 선택 시 노출 | .autoDisplayN 수동노출 선택 시 노출 -->
        <tr>
            <th>쿠폰 노출방식 설정</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="autoDisplayFl" value="y" <?= $checked['autoDisplayFl']['y']; ?> />자동노출
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="autoDisplayFl" value="n" <?= $checked['autoDisplayFl']['n']; ?> />수동노출
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>쿠폰 진열기준</th>
            <td>
                <div class="radio form-inline">
                    <?= gd_select_box('couponzoneSort', 'couponzoneSort', $couponzoneOrderByList, null, $couponConfig['couponzoneSort']); ?>
                    <span class="notice-danger autoDisplayY">
                        ‘자동노출’ 설정 시 쿠폰 진열기준 설정에 따라 최대 9개의 쿠폰만 노출됩니다.
                    </span>
                </div>
            </td>
        </tr>
        <tr class="autoDisplayY">
            <th>노출 제외 쿠폰 선택</th>
            <td>
                <div class="radio form-inline">
                    <input type="button" value="쿠폰선택" class="btn btn-sm btn-gray" id="unexposedCouponBtn" onclick="layer_register('unexposedCoupon');"/>
                    <div id="unexposedCouponLayer" class="selected-btn-group <?=!empty($unexposedCoupon) ? 'active' : ''?>">
                        <h5>선택된 쿠폰 : </h5>
                        <?php
                        foreach($unexposedCoupon as $key => $val) {
                            if($val){
                        ?>
                            <div id="infoMemberGroup_<?= $key ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="unexposedCoupon[]" value="<?= $key ?>"/>
                                <input type="hidden" name="unexposedCouponNm[]" value="<?= $val ?>"/>
                                <span class="btn"><?= $val ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#infoMemberGroup_<?= $key ?>">삭제</button>
                            </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="autoDisplayN">
            <th>쿠폰 그룹 설정</th>
            <td style="margin:0; padding:0;">
                <table id="table-banner" class="table table-cols" style="margin:0;">
                    <colgroup>
                        <col class="width-4xs" />
                        <col class="width-2xs" />
                        <col class="width-xl" />
                        <col />
                    </colgroup>
                    <thead>
                    <tr>
                        <th colspan="4">
                            <div class="form-inline">
                                <div class="btn-group" style="float:left;">
                                    <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                        맨아래
                                    </button>
                                    <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                        아래
                                    </button>
                                    <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                        위
                                    </button>

                                    <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                        맨위
                                    </button>
                                </div>
                                <div style="float:right">
                                    <input type="button" value="+ 그룹 등록" class="btn btn-white btn-add">
                                    <input type="button" value="선택 삭제" class="btn btn-white btn-del">
                                </div>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th class="center"><input type="checkbox" name="allChk" value="y"></th>
                        <th class="center">순서</th>
                        <th class="center">그룹명</th>
                        <th class="center">회원다운로드 쿠폰</th>
                    </tr>
                    </thead>
                    <tbody class="couponzoneGroup">
                        <?php foreach ($couponConfig['groupNm'] as $iKey => $iVal) {?>
                        <tr class="banner-info">
                            <td class="center"><input type="checkbox" name="chk[]" value="<?php echo $iKey; ?>"></td>
                            <td class="center"><?php echo $iKey; ?></td>
                            <td class="center"><input type="text" class="form-control" name="groupNm[<?=$iKey?>]" value="<?=$iVal?>" maxlength="30"/></td>
                            <td>
                                <input type="button" value="쿠폰선택" class="btn btn-sm btn-gray groupCoupon" data-idx="<?=$iKey?>" onclick="layer_register('groupCoupon', this);"/>
                                <table class="table table-cols couponzoneGroupList">
                                    <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>쿠폰명</th>
                                        <th>노출상태</th>
                                        <th>발급상태</th>
                                        <th>삭제</th>
                                    </tr>
                                    </thead>
                                    <tbody id="groupCouponLayer<?= $iKey ?>">
                                    <?php
                                    $cnt = 1;
                                    foreach ($couponConfig['groupCoupon'][$iKey] as $key => $val) {
                                        if($val['couponNo']) {
                                    ?>
                                    <tr id="groupCouponInfo<?= $iKey ?>_<?= $val['couponNo'] ?>" class="<?= ($val['displayFl'] == '미노출' ? 'couponDisplayN' : '') ?>">
                                        <td class="center"><span class="number"><?= $cnt++ ?></span>
                                        <td>
                                            <input type="hidden" name="groupCoupon[<?= $iKey ?>][]" value="<?= $val['couponNo'] ?>">
                                            <a href="../promotion/coupon_regist.php?couponNo=<?= $val['couponNo'] ?>" class="couponNoNm" target="_blank"><?= $val['couponNm'] ?></a>
                                        </td>
                                        <td class="center couponDisplayFl"><?= $val['displayFl'] ?></td>
                                        <td class="center"><?= $val['couponType'] ?></td>
                                        <td class="center">
                                            <input type="button" class="btn btn-sm btn-gray couponDelete" data-target="#groupCouponInfo<?= $iKey ?>_<?= $val['couponNo'] ?>" value="삭제"/>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="pdl0">
                                            <input type="button" value="쿠폰 전체삭제" class="btn btn-sm btn-gray groupCouponBtnDel" data-idx="<?= $iKey ?>" onclick="deleteGroupAllCoupon(this);">
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="4">
                            <div class="form-inline">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                        맨아래
                                    </button>
                                    <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                        아래
                                    </button>
                                    <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                        위
                                    </button>

                                    <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                        맨위
                                    </button>
                                </div>
                                <div style="float:right">
                                    <input type="button" value="+ 그룹 등록" class="btn btn-white btn-add">
                                    <input type="button" value="선택 삭제" class="btn btn-white btn-del">
                                </div>
                            </div>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <h5 class="table-title gd-help-manual">쿠폰존 디자인 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>쿠폰노출이미지</th>
            <td>
                <div class="radio">
                    <label class="mgr10"><input type="radio" name="couponImageType" value="basic" <?= $checked['couponImageType']['basic'] ?> />기본 디자인</label>
                    <img src="<?= PATH_ADMIN_GD_SHARE ?>img/img_sample_coupon_1.png" class="mgr10" alt="기본쿠폰이미지"/>
                    <img src="<?= PATH_ADMIN_GD_SHARE ?>img/img_sample_coupon_2.png" class="mgr10" alt="기본쿠폰이미지"/>
                    <p class="notice-info display-inline-block">쿠폰이미지 위에 쿠폰정보가 표기됩니다.<br>PC쇼핑몰 쿠폰이미지 / 모바일쇼핑몰 쿠폰이미지</p>
                </div>
                <div class="radio form-inline reg-couponimg pdt10">
                    <label>
                        <input type="radio" name="couponImageType" value="self" <?= $checked['couponImageType']['self'] ?> />이미지 직접등록
                    </label>
                    <table class="table table-cols mgt5 couponImageTypeSelf">
                        <colgroup>
                            <col class="width-sm"/>
                            <col>
                        </colgroup>
                        <tr>
                            <th>PC 쇼핑몰</th>
                            <td>
                                <input type="hidden" name="pcCouponImage" value="<?=$couponConfig['pcCouponImage']?>" />
                                <input type="file" name="pcCouponImageFile" class="form-control"/>
                                <?php
                                if ($couponConfig['pcCouponImage']) {
                                    echo '<img src="'.$couponConfig['pcCouponImagePath'].'" alt="쿠폰이미지"/>';
                                }
                                ?>
                                <p class="notice-info">jpg, jpeg, png, gif만 등록 가능하며, 기본 쿠폰 이미지는 300x196 pixel 입니다.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>모바일 쇼핑몰</th>
                            <td>
                                <input type="hidden" name="mobileCouponImage" value="<?=$couponConfig['mobileCouponImage']?>" />
                                <input type="file" name="mobileCouponImageFile" class="form-control"/>
                                <?php
                                if ($couponConfig['mobileCouponImage']) {
                                    echo '<img src="'.$couponConfig['mobileCouponImagePath'].'" alt="쿠폰이미지"/>';
                                }
                                ?>
                                <p class="notice-info">jpg, jpeg, png, gif만 등록 가능하며, 기본 쿠폰 이미지는 178x165 pixel 입니다.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <th>쿠폰존 상단영역</th>
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
                                  class="form-control"><?php echo $couponConfig['pcContents']; ?></textarea>
                    </div>
                    <div class="tab-pane "  id="textareaDescriptionMobile">
                        <textarea name="mobileContents" rows="3" style="width:100%; height:400px;" id="editor2"
                                  class="form-control"><?php echo $couponConfig['mobileContents']; ?></textarea>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript">

    $('input:radio[name="autoDisplayFl"]').click(function (e) {
        changeAutoDisplay();
    });

    function changeAutoDisplay(sort) {
        if ($('input:radio[name="autoDisplayFl"]:checked').val() == 'y') {
            $('.autoDisplayN').hide();
            $('.autoDisplayY').show();
            $('#couponzoneSort option:first').hide();
            if(!sort) sort = 'regDt DESC';
        } else if ($('input:radio[name="autoDisplayFl"]:checked').val() == 'n') {
            $('.autoDisplayN').show();
            $('.autoDisplayY').hide();
            $('#couponzoneSort option:first').show();
            if(!sort) sort = 'custom';
        }
        $('#couponzoneSort').val(sort);
    }

    $('input:radio[name="couponImageType"]').click(function (e) {
        changeCouponImageType();
    });

    function changeCouponImageType() {
        $('.couponImageTypeSelf').hide();
        if ($('input:radio[name="couponImageType"]:checked').val() == 'self') {
            $('.couponImageTypeSelf').show();
        }
    }

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
    // ie에서 스마트에디터 오류로 인해 height값 넣어줌
    $('.editor-tab').bind('click',function(){
        var role = $(this).data('role') - 1;

        $('.tab-pane iframe').css('height', '450px');
        var editorIframe = $('.tab-pane iframe').get(role).contentWindow.document.getElementsByTagName('iframe');

        if (editorIframe[0]) {
            editorIframe[0].style.height = '400px';
        }
    });
    $('.btn-add').click(function(){
        var len = $('.couponzoneGroup').children('tr').length + 1;
        if(len > 20) {
            alert('쿠폰그룹은 최대20개까지 등록이 가능합니다.');
            return false;
        }
        addBanner(len);
    });

    $('.btn-del').click(function(){
        var len = $('.couponzoneGroup').find('.banner-info input[name="chk[]"]:checked').length;
        var bannerLen = $('.couponzoneGroup').find('.banner-info').length;
        if (len <= 0) {
            alert('쿠폰 그룹을 선택해주세요.');
            return false;
        }

        if (len == bannerLen) {
            addBanner(0);
        }

        $('.couponzoneGroup').find('.banner-info input[name="chk[]"]').each(function() {
            if (this.checked === true) {
                $(this).closest('tr').remove();
            }
        });

        bannerSortNum();
        setButtonCustom();
    });

    // 쿠폰 그룹 설정 > 쿠폰 선택 테이블 > 삭제 버튼
    $(document).on('click', '.couponDelete', function(){
        var id = $(this).data('target');
        var tbody = $(id).parent();
        $(id).remove();
        $(tbody).find('tr').each(function(index) {
            $(this).find('td:eq(0)').html(index + 1);
        });
    })

    function addBanner(len) {
        var addHtml = '';
        var complied = _.template($('#couponGroupTemplate').html());
        addHtml += complied({
            key: len
        });
        $('.couponzoneGroup').append(addHtml);
    }

    function deleteGroupAllCoupon(obj) {
        var key = $(obj).data('idx');
        var $layer = $('#groupCouponLayer'+key);
        if($layer.find('input[name^=groupCoupon]').length <= 0) {
            alert('선택된 쿠폰이 없습니다.');
            return false;
        }
        $layer.html('');
    }

    $('.js-moverow').click(function(){
        init_file_style_destroy();
        var $table = $('.couponzoneGroup'), len = $('.couponzoneGroup').find('.banner-info input[name="chk[]"]:checked').length;
        var maxLen = $('.couponzoneGroup').find('.banner-info').length, direction = $(this).data('direction');
        var idx = 0, $target = $('.couponzoneGroup').find('.banner-info input[name="chk[]"]:checked');
        if (len <= 0) {
            alert('쿠폰 그룹을 선택해주세요.');
            return false;
        }
        if (direction == 'down') $target = $($('.couponzoneGroup').find('.banner-info input[name="chk[]"]:checked').get().reverse());

        $target.each(function(){
            var $tr = $(this).closest('tr');
            var index = $tr.index('.banner-info');

            switch (direction) {
                case 'bottom':
                    $table.append($tr.detach());
                    break;
                case 'down':
                    if (index < maxLen - 1) {
                        $tr.next().after($tr.detach());
                    }
                    break;
                case 'up':
                    if (index > 0) {
                        $tr.prev().before($tr.detach());
                    }
                    break;
                case 'top':
                    $table.find('.banner-info:eq(' + idx + ')').before($tr.detach());
                    idx++;
                    break;
            }
        });
        bannerSortNum();
    });

    function bannerSortNum() {
        $('.banner-info').each(function(index) {
            var $this = $(this);
            var num = index + 1;
            $this.find('td:eq(1)').html(num);
            $this.find('input[name^="groupNm["]').attr('name', 'groupNm[' + num + ']');
            $this.find('input[name^="groupCoupon["]').attr('name', 'groupCoupon[' + num + '][]');
            $this.find('.couponzoneGroupList tbody').attr('id', 'groupCouponLayer'+num);
            $this.find('.groupCoupon').attr('data-idx', num);
            $this.find('.groupCouponBtnDel').attr('data-idx',num);
            $this.find('tr[id^=groupCouponInfo]').each(function(){
                var id = $(this).attr('id').split('_')[1];
                $(this).attr('id', 'groupCouponInfo'+num+'_'+id);
                $(this).find('.couponDelete').attr('data-target', '#groupCouponInfo'+num+'_'+id);
            })
        });
    }

    $('input[name="allChk"]').click(function(){
        var checked = $(this).prop('checked');
        $('.couponzoneGroup').find('input[name="chk[]"]').prop('checked', checked);
    });

    function layer_register(typeStr, obj) {
        var addParam = {};
        if (typeStr == 'unexposedCoupon') {
            addParam['mode'] = 'couponzone';
            addParam['parentFormID'] = 'unexposedCouponLayer';
            addParam['dataFormID'] = 'infoMemberGroup';
            addParam['dataInputNm'] = 'unexposedCoupon';
            addParam['couponSaveType'] = 'down';
        }

        if (typeStr == 'groupCoupon') {
            var num = $(obj).data('idx');
            addParam['mode'] = 'couponzoneGroup';
            addParam['parentFormID'] = 'groupCouponLayer'+num;
            addParam['dataFormID'] = 'groupCouponInfo'+num;
            addParam['dataInputNm'] = 'groupCoupon['+num+']';
            addParam['couponSaveType'] = 'down';
        }

        layer_add_info('coupon', addParam);
    }

    $(document).ready(function () {
        $("#frmCoupon").validate({
            submitHandler: function (form) {
                if($('input:radio[name=useFl]:checked').val() == 'y') {
                    var checked = true;
                    if ($('input:radio[name=autoDisplayFl]:checked').val() == 'n') {
                        $('.banner-info').each(function () {
                            if (!$(this).find('input[name^=groupNm]').val() || $(this).find('input[name^=groupCoupon]').length <= 0) {
                                checked = false;
                                return false;
                            }
                        });
                    }
                    if (!checked) {
                        alert('쿠폰 그룹을 설정해주세요.');
                        return false;
                    }

                    if ($('input:radio[name=couponImageType]:checked').val() == 'self') {
                        if ((!$('input[name=pcCouponImageFile]').val() && !$('input[name=pcCouponImage]').val()) || (!$('input[name=mobileCouponImage]').val() && !$('input[name=mobileCouponImageFile]').val())) {
                            alert('쿠폰 이미지를 첨부해주세요.');
                            return false;
                        }
                    }
                }
                oEditors.getById["editor1"].exec("UPDATE_CONTENTS_FIELD", []);
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);
                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        changeAutoDisplay('<?=$couponConfig['couponzoneSort']?>');
        changeCouponImageType();
        loadEditor();
    });
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
<script type="text/html" id="couponGroupTemplate">
    <tr class="banner-info">
        <td class="center"><input type="checkbox" name="chk[]" value=""></td>
        <td class="center"><%=key%></td>
        <td class="center"><input type="text" class="form-control" name="groupNm[<%=key%>]" value="" maxlength="30"/></td>
        <td>
            <input type="button" value="쿠폰선택" class="btn btn-sm btn-gray groupCoupon" data-idx="<%=key%>" onclick="layer_register('groupCoupon', this);"/>
            <table class="table table-cols couponzoneGroupList">
                <thead>
                <tr>
                    <th>번호</th>
                    <th>쿠폰명</th>
                    <th>노출상태</th>
                    <th>발급상태</th>
                    <th>삭제</th>
                </tr>
                </thead>
                <tbody id="groupCouponLayer<%=key%>">
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="5" class="pdl0">
                        <input type="button" value="쿠폰 전체삭제" class="btn btn-sm btn-gray groupCouponBtnDel" data-idx="<%=key%>" onclick="deleteGroupAllCoupon(this);">
                    </td>
                </tr>
                </tfoot>
            </table>
        </td>
    </tr>
</script>