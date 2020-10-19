<script type="text/javascript">
    <!--
    $(document).ready(function(){

        $("#frmIcon").validate({
            submitHandler: function (form) {

                if (check_display_form() == false) {
                    return false;
                }

                form.target='ifrmProcess';
                form.submit();
            },
            rules: {
                themeCd: 'required',
                themeNm: 'required'
            },
            messages: {
                themeCd: {
                    required: '테마코드 입력하세요.'
                },
                themeNm: {
                    required: '테마명 입력하세요.'
                }
            }
        });

        display_theme();
        check_opener_mobileFl();
        <?php if ($data['mobileFl'] == 'y') { ?>
        $('.mobile-display-none').hide();
        $('.mobile-display-show').removeClass('display-none');
        <?php } ?>
        <?php if(gd_isset($data['themeDisabled'])) { ?> display_site(); <?php } ?>
        display_switch('displayType_','<?=$data['displayType']?>',true);
    });

    /**
     * 테마분류 선택
     *
     * @param string thisID 종류 ID
     */
    function display_switch(prefix,thisID,loadFl)
    {
        $('.'+prefix).addClass('display-none');
        $('.'+prefix+thisID).removeClass('display-none');

        if(loadFl === false && $('input[name="mobileFl"]:checked').val()=='y') {
            $('.mobile-display-none').hide();
            switch ($("input[name='displayType']:checked").val()) {
                case '01'://갤러리형
                    $("input[name='lineCnt']").attr("readonly", false);
                <?php if($data['mode'] =='theme_register') { ?>
                    $("input[name='lineCnt']").val("2");
                <?php } ?>
                    break;
                /*case '02'://리스트형
                 case '09'://심플이미지형
                 $("input[name='lineCnt']").val("1");
                 $("input[name='lineCnt']").attr("readonly", true);
                 break;*/
                case '04'://상품이동형
                    $("input[name='lineCnt']").val("1");
                    $("input[name='rowCnt']").val("1");
                    $("input[name='lineCnt']").attr("readonly", false);
                    break;
                case '06'://스크롤형
                    $("input[name='lineCnt']").val("3");
                    $("input[name='rowCnt']").val("1");
                    $("input[name='lineCnt']").attr("readonly", false);
                    break;
                    break;
                case '11'://장바구니형
                    $("input[name='lineCnt']").val("1");
                    $("input[name='rowCnt']").val("2");
                    $("input[name='lineCnt']").attr("readonly", true);
                    break;
                case '07'://탭진열형
                    $("input[name='lineCnt']").val("2");
                    $("input[name='rowCnt']").val("1");
                    $("input[name='lineCnt']").attr("readonly", false);
                    break;
                default:
                    $("input[name='lineCnt']").val("1");
                    $("input[name='lineCnt']").attr("readonly", true);
                    break;
            }
        } else if (loadFl === false && $('input[name="mobileFl"]:checked').val()=='n') {
            $('.mobile-display-none').show();
        }
    }

    function setLength() {
        setLength($(".smsChr").get(0), $(".smsChrCount").get(0));
    }

    function setAddTheme(themeCd,themeNm){
        $("select[name='<?=$addTheme?>']",opener.document).append("<option value='"+themeCd+"'>"+themeNm+"</option>");
        $("select[name='<?=$addTheme?>']",opener.document).val(themeCd);
        $("select[name='<?=$addTheme?>']",opener.document).change();
        // $("select[name='themeCd']",opener.document).viewThemeConfig(themeCd);
    }

    function display_site() {

        if($('input[name="mobileFl"]:checked').val()=='n') {
            $("input[name='lineCnt']").attr("readonly",false);
            $('.mobile-display-show').addClass('display-none');
        } else {
            $('.mobile-display-show').removeClass('display-none');
        }

        <?php if($data['mode'] =='theme_register') { ?>
        $("select[name='imageCd']").val($("select[name='imageCd'] option:first").val());

        if($('input[name="mobileFl"]:checked').val()=='n') {
            $("input[name='lineCnt']").val("4");
            $("input[name='rowCnt']").val("5");
        } else {
            $("input[name='lineCnt']").val("2");
            $("input[name='rowCnt']").val("1");
        }


        $("input[name='soldOutFl'][value='y']").prop("checked",true);
        $("input[name='soldOutDisplayFl'][value='y']").prop("checked",true);
        $("input[name='soldOutIconFl'][value='y']").prop("checked",true);
        $("input[name='iconFl'][value='y']").prop("checked",true);

        $("input[name='displayField[]']").prop("checked",false);
        $("input[name='displayField[]'][value='img']").prop("checked",true);
        $("input[name='displayField[]'][value='goodsNm']").prop("checked",true);

        $("input[name='displayType'][value='01']").prop("checked",true);
        <?php } ?>

        display_theme();
    }

    function display_theme() {

        var themeCate = $("input[name='themeCate']:checked").val();

        $(".paycosearch-msg").addClass('display-none');
        if(themeCate == 'A') {
            $(".paycosearch-msg").removeClass('display-none');
        }

        if($('input[name="mobileFl"]:checked').val()=='n') {
            $(".display_pc").removeClass('display-none');

            $('.display_').addClass('display-none');
            $('.display_'+themeCate).removeClass('display-none');
            $('.displayType_').addClass('display-none');

        } else {
            $(".display_pc").addClass('display-none');
            $('.display_').addClass('display-none');
            $('.display_'+themeCate).not('.display_pc').removeClass('display-none');
            $('.displayType_').addClass('display-none');
        }
    }

    /**
     * 팝업등록인경우
     *
     * @param string modeStr 사은품 모드
     */
    function popup_submit()
    {

        <?php if($data['mode'] =='theme_modify') { ?>
        $('input[name=\'mode\']').val('theme_modify_ajax');
        <?php } else { ?>
        $('input[name=\'mode\']').val('theme_register_ajax');
        <?php } ?>
        var parameters =  $("#frmIcon").serialize();

        if (check_display_form() == false) {
            return false;
        }

        if ($('#frmIcon').valid() == false) {
            return false;
        }

        $.post('display_config_ps.php',parameters, function(data){

            var theme = $.parseJSON(data);

            if(theme.state ==  true) {
                <?php if($callFunc) {?>
                opener.parent.<?=$callFunc?>(theme.themeCd,theme.themeNm,'<?=$addTheme?>');
                <?php } else { ?>
                $("select[name='<?=$addTheme?>']",opener.document).append("<option value='"+theme.themeCd+"'>"+theme.themeNm+"</option>");
                $("select[name='<?=$addTheme?>']",opener.document).val(theme.themeCd);
                $("select[name='<?=$addTheme?>']",opener.document).change();
                <?php } ?>

                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_PRIMARY,
                    message: theme.msg,
                    onshown: function(dialogRef){
                        setTimeout(function(){
                            dialogRef.close();
                            self.close();
                        }, 1000);
                    }
                });

            } else {
                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_PRIMARY,
                    message: theme.msg,
                    onshown: function(dialogRef){
                        setTimeout(function(){
                            dialogRef.close();
                        }, 1000);
                    }
                });
            }
        });
    }

    /**
     * 탭유형 이름 추 가
     *
     * @param int val 탭 개수
     */
    function add_display_tab(val)
    {
        $('#tbl_displaytype_tab .cla_tab_info').remove();
        for(var i = 1; i <= val; i++)
        {
            $('#tbl_displaytype_tab > tbody:last-child').append('<tr class="cla_tab_info"><th>'+i+'번탭이름</th><td><label title=""><input type="text" name="detailSet[07][]" class="form-control"/></label></td></tr>');
        }
    }

    // 폼 체크
    function check_display_form() {
        if($("input[name='displayField[]']:checked").length == 0) {
            alert("노출항목을 선택하세요.");
            return false;
        }

        if($('input[name="mobileFl"]:checked').val()=='y') {
            switch ($("input[name='displayType']:checked").val()) {
                case '01':
                case '04':
                case '06':
                case '07':
                    if($("input[name='lineCnt']").val() > 5) {
                        alert("가로 노출 개수는 5개를 넘을수 없습니다.");
                        return false;
                    }
                    break;
                case '11':
                    if($("input[name='lineCnt']").val() > 1) {
                        alert("가로 노출 개수는 1개를 넘을수 없습니다.");
                        return false;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    // 쇼핑몰 유형 체크
    function check_opener_mobileFl() {
        var openerMobileFl = $('input[name="openerMobileFl"]').val();

        if (openerMobileFl != null) {
            $('input[name="mobileFl"][value=openerMobileFl]').prop('checked', 'checked');

            if (openerMobileFl == 'n') {
                $('input[name="mobileFl"][value="y"]').prop('disabled', 'disabled');
            } else if (openerMobileFl == 'y') {
                $('input[name="mobileFl"][value="n"]').prop('disabled', 'disabled');
            }
        }
    }
    //-->
</script>
<form id="frmIcon" name="frmIcon" action="./display_config_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?=$data['mode'];?>" />
    <input type="hidden" name="deleteFl" value="<?=gd_isset($data['deleteFl']);?>" />
    <input type="hidden" name="addTheme" value="<?=$addTheme?>" />
    <input type="hidden" name="openerMobileFl" value="<?=gd_isset($openerMobileFl)?>" />

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./display_config_theme_list.php');" />
            <?php if(Request::get()->get('addTheme') && Request::get()->get('popupMode')) { ?>
                <input type="button" value="저장" class="btn btn-red" onclick="popup_submit()" />
            <?php } else { ?>
                <input type="submit" value="저장" class="btn btn-red" />
            <?php } ?>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본정보
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>쇼핑몰 유형</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mobileFl"
                                                       value="n" <?=gd_isset($checked['mobileFl']['n']); ?> onclick="display_site()"  <?=gd_isset($data['mobileDisabled']);?> />PC쇼핑몰</label>
                    <label class="radio-inline"><input type="radio" name="mobileFl"
                                                       value="y" <?=gd_isset($checked['mobileFl']['y']); ?> onclick="display_site()"  <?=gd_isset($data['mobileDisabled']);?> />모바일쇼핑몰</label>

                    <?php if(gd_isset($data['mobileDisabled'])) { ?><input type="hidden" name="mobileFl" value="<?=$data['mobileFl']?>"><?php } ?>

                </td>
            </tr>
            <tr>
                <th>테마코드 </th>
                <td>
                    <?php  if($data['themeCd']) { ?><?=$data['themeCd']?> <label title=""><input type="hidden" name="themeCd" value="<?=gd_isset($data['themeCd']);?>" /></label>
                    <?php } else { echo '테마 등록 저장 시 자동 생성됩니다.'; } ?>
                </td>
            </tr>
            <tr>
                <th class="require" >테마명</th>
                <td >
                    <label title=""><input type="text" name="themeNm" value="<?=gd_isset($data['themeNm']);?>" class="form-control width_lLarge js-maxlength"  maxlength="60" /></label>
                </td>
            </tr>
            <tr>
                <th >테마분류 </th>
                <td class="theme_list">
                    <?php foreach($themeCategory as $k => $v) { ?>
                        <span>
                        <label class="radio-inline" title=""><input type="radio" name="themeCate" value="<?=$k?>" <?=gd_isset($data['themeDisabled']);?> <?=gd_isset($checked['themeCate'][$k]);?> onclick="display_theme();"   /><?=$v?></label>
                        </span>
                    <?php  } ?>
                    <?php if(gd_isset($data['themeDisabled'])) { ?><input type="hidden" name="themeCate" value="<?=$data['themeCate']?>"><?php } ?>

                </td>
            </tr>
        </table>
    </div>



    <div class="table-title gd-help-manual">
        리스트 영역 상세 설정
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col class="width-3xl" /><col class="width-md" /><col /></colgroup>
            <tr>
                <th >이미지설정 </th>
                <td  colspan="3"><div class="form-inline">
                        <?php
                        foreach ($confImage as $key => $val) {
                            if ($key == 'imageType') {
                                continue;
                            }
                            $arrImage[$key] = $val['text'] . ' - ' . $val['size1'][0] . ' pixel';
                            if($confImage['imageType'] == 'fixed') {
                                $arrImage[$key] .= ' / 세로 ' . $val['size1'][1] . ' pixel';
                            }
                        }

                        echo gd_select_box('imageCd','imageCd',$arrImage,null,$data['imageCd'],null);
                        ?>
                        <span class="notice-info">이미지는 <a href="/policy/goods_images.php" target="_blank" class="btn-link">기본설정>상품 정책>상품 이미지 사이즈 설정</a>에서 관리할 수 있습니다.</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th >상품 노출 개수 </th>
                <td  colspan="3"><div class="form-inline">
                        가로 : <input type="text" name="lineCnt" value="<?=$data['lineCnt'];?>" class="form-control width-3xs js-number" /> X
                        세로 : <input type="text" name="rowCnt" value="<?=$data['rowCnt'];?>" class="form-control width-3xs js-number" /> </div>
                </td>
            </tr>
            <tr>
                <th >품절상품 노출 </th>
                <td >
                    <label class="radio-inline"><input type="radio" name="soldOutFl" value="y" <?=gd_isset($checked['soldOutFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="soldOutFl" value="n" <?=gd_isset($checked['soldOutFl']['n']);?>/>노출안함</label>
                    <div class="notice-info paycosearch-msg display-none">페이코 서치 사용 시 해당 옵션은 페이코서치 데이터 수집 주기 시점에 적용됩니다.</div>
                </td>
                <th >품절상품 진열</th>
                <td >
                    <label class="radio-inline"><input type="radio" name="soldOutDisplayFl" value="y" <?=gd_isset($checked['soldOutDisplayFl']['y']);?>/>정렬 순서대로 보여주기</label>
                    <label class="radio-inline"><input type="radio" name="soldOutDisplayFl" value="n" <?=gd_isset($checked['soldOutDisplayFl']['n']);?>/>리스트 끝으로 보내기</label>
                    <div class="notice-info paycosearch-msg display-none">페이코 서치 사용 시 '리스트 끝으로 보내기'옵션은 적용되지 않습니다.</div>
                </td>
            </tr>
            <th >품절 아이콘 노출 </th>
            <td >
                <label class="radio-inline"><input type="radio" name="soldOutIconFl" value="y" <?=gd_isset($checked['soldOutIconFl']['y']);?>/>노출함</label>
                <label class="radio-inline"><input type="radio" name="soldOutIconFl" value="n" <?=gd_isset($checked['soldOutIconFl']['n']);?>/>노출안함</label>
            </td>
            <th >아이콘 노출 </th>
            <td >
                <label class="radio-inline"><input type="radio" name="iconFl" value="y" <?=gd_isset($checked['iconFl']['y']);?>/>노출함</label>
                <label class="radio-inline"><input type="radio" name="iconFl" value="n" <?=gd_isset($checked['iconFl']['n']);?>/>노출안함</label>
            </td>
            </tr>
            <tr>
                <th class="require" >노출항목 설정 </th>
                <td  colspan="3">
                    <?php foreach($themeDisplayField as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="displayField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['displayField']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th >할인적용가 설정</th>
                <td >
                    <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="goodsDiscount[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['goodsDiscount']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
                </td>
                <th >취소선 추가 설정</th>
                <td >
                    <?php foreach($themePriceStrike as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="priceStrike[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['priceStrike']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">
                        체크시 쇼핑몰에 취소선 효과가 적용되어 출력됩니다. (예시. 정가 <span style="text-decoration: line-through;">12,000</span>원)<br />
                        단, 판매가의 경우 할인가가 없는 상품에는 취소선이 적용되지 않습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th >노출항목 추가 설정</th>
                <td colspan="3">
                    <?php foreach($themeDisplayAddField as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="displayAddField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['displayAddField']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">[할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)</div>
                </td>
            </tr>
            <tr>
                <th >디스플레이 유형 </th>
                <td  colspan="3"><div class="form-inline js-theme-display">

                        <?php foreach($themeDisplayType as $k => $v) { ?>
                            <div  class="pd10  display_ <?=$v['class']?> <?php if($v['mobile'] != 'y') { echo ' display_pc'; }?> " style="float:left"><span>
                                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/display_theme_<?=$k?>.jpg"><br/>
                                            <label class="radio-inline mgt5" title="" ><input type="radio" name="displayType" value="<?=$k?>" <?=gd_isset($checked['displayType'][$k]);?>  onclick="display_switch('displayType_',this.value,false);" /><?=$v['name']?></label></span></div>
                        <?php } ?>


                    </div>
                </td>
            </tr>
            <!--  세부설정 상품이동형 시작 -->
            <?php $checked['detailSet']['04'][$data['detailSetConfig']['04']['0']] = 'checked="checked"'; ?>
            <tr class='displayType_ displayType_04'>
                <th >세부설정 </th>
                <td  colspan="5">
                    <table class="table-cols">
                        <tr>
                            <th class='input_title'>이동방향</th>
                            <td><label title="" class="radio-inline"><input type="radio" name="detailSet[04]" value="R" <?=gd_isset($checked['detailSet']['04']['R']);?> >오른쪽에서 왼쪽으로 </label><label title="" class="radio-inline"> <input type="radio" name="detailSet[04]" value="L" <?=gd_isset($checked['detailSet']['04']['L']);?>>왼쪽에서 오른쪽으로 </label></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!--  세부설정 상품이동형 끝-->
            <!--  세부설정 세로이동형 시작 -->
            <!--
            <?php $checked['detailSet']['05'][$data['detailSetConfig']['05']['0']] = 'checked="checked"'; ?>
            <tr  class='displayType_ displayType_05'>
                <th >세부설정 </th>
                <td  colspan="5">
                    <table class="table-cols">
                        <tr>
                            <th class='input_title'>이동방향</th>
                            <td><label title=""><input type="radio" name="detailSet[05]" value="T" <?=gd_isset($checked['detailSet']['05']['T']);?>>위에서 아래롤 </label><label title=""> <input type="radio" name="detailSet[05]" value="B" <?=gd_isset($checked['detailSet']['05']['B']);?>>아래에서 위로 </label></td>
                        </tr>
                    </table>
                </td>
            </tr>-->
            <!--  세부설정 세로이동형 끝-->
            <!--  세부설정 스크롤형 시작 -->
            <?php $checked['detailSet']['06'][$data['detailSetConfig']['06']['0']] = 'checked="checked"'; ?>
            <tr class='displayType_ displayType_06'>
                <th >세부설정 </th>
                <td  colspan="5">
                    <table class="table-cols">
                        <tr>
                            <th class='input_title'>이동방향</th>
                            <td><label title="" class="radio-inline"><input type="radio" name="detailSet[06]" value="W" <?=gd_isset($checked['detailSet']['06']['W']);?>>가로형 </label><label title="" class="radio-inline mobile-display-none"> <input type="radio" name="detailSet[06]" value="H" <?=gd_isset($checked['detailSet']['06']['H']);?>>세로형 </label></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!--  세부설정 스크롤형 끝-->
            <!--  세부설정 탭 진열형 시작 -->
            <?php $checked['detailSet']['07'][$data['detailSetConfig']['07']['1']] = 'checked="checked"'; ?>
            <tr class='displayType_ displayType_07'>
                <th >세부설정 </th>
                <td  colspan="5">
                    <table id="tbl_displaytype_tab" class="table-cols">
                        <tr>
                            <th class='input_title'>탭개수</th>
                            <td><label title="">
                                    <select name="detailSet[07][]" onchange="add_display_tab(this.value)">
                                        <?php for($i = 1; $i < 8; $i++) { ?>
                                            <option value="<?=$i?>" <?php if($i == $data['detailSetConfig']['07']['0']) { echo 'selected';} ?>><?=$i?></option>
                                        <?php } ?>
                                    </select>
                                </label></td>
                        </tr>
                        <tr>
                            <th class='input_title'>이동방향</th>
                            <td><label title="" class="radio-inline"><input type="radio" name="detailSet[07][]" value="W" <?=gd_isset($checked['detailSet']['07']['W']);?>>가로형 </label><label title="" class="radio-inline mobile-display-none"> <input type="radio" name="detailSet[07][]" value="H" <?=gd_isset($checked['detailSet']['07']['H']);?>>세로형 </label></td>
                        </tr>
                        <?php for($i = 1 ; $i <= $data['detailSetConfig']['07']['0']; $i++ ) { ?>
                            <tr class="cla_tab_info">
                                <th class='input_title'><?=$i?>번탭이름</th>
                                <td><label title=""><input type="text"  name="detailSet[07][]" class="form-control"  value="<?=gd_isset($data['detailSetConfig']['07'][1+$i])?>" /></label></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <!--  세부설정 탭 진열형 끝-->
            <!--  세부설정 선택 강조형 시작 -->
            <?php $checked['detailSet']['08'][$data['detailSetConfig']['08']['0']] = 'checked="checked"'; ?>
            <tr class='displayType_ displayType_08'>
                <th >세부설정 </th>
                <td  colspan="5">
                    <table class="table-cols">
                        <tr>
                            <th class='input_title'>효과대상</th>
                            <td><label title="" class="radio-inline"><input type="radio" name="detailSet[08][]" value="S" <?=gd_isset($checked['detailSet']['08']['S']);?>>선택한 상품만 흐리게 </label><label title="" class="radio-inline"> <input type="radio" name="detailSet[08][]" value="O" <?=gd_isset($checked['detailSet']['08']['O']);?>>선택한 나머지 상품 흐리게 </label></td>
                        </tr>
                        <tr>
                            <th class='input_title'>투명도</th>
                            <td><label title=""><input type="text" name="detailSet[08][]" class="form-control js-number"  value="<?=gd_isset($data['detailSetConfig']['08']['1'])?>" /></label> 0%에 가까울수록 투명해 집니다.</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!--  세부설정  선택 강조형 끝-->
            <!--  세부설정 말풍선형 시작 -->
            <?php $checked['detailSet']['10'][$data['detailSetConfig']['10']['0']] = 'checked="checked"';?>
            <tr class='displayType_ displayType_10'>
                <th >세부설정 </th>
                <td  colspan="5">
                    <table class="table-cols">
                        <tr>
                            <th class='input_title'>배경색</th>
                            <td><label title="" class="radio-inline"><input type="radio" name="detailSet[10][]"   value="B" <?=gd_isset($checked['detailSet']['10']['B']);?>>타입1(Black)</label><label title="" class="radio-inline"> <input type="radio" name="detailSet[10][]" value="W" <?=gd_isset($checked['detailSet']['10']['W']);?>>타입2(White) </label></td>
                        </tr>
                        <tr>
                            <th class='input_title'>투명도</th>
                            <td><label title=""><input type="text" name="detailSet[10][]" class="form-control js-number" value="<?=gd_isset($data['detailSetConfig']['10']['1'])?>"  /></label>0%에 가까울수록 투명해 집니다.</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!--  세부설정 말풍선형 끝-->
        </table>
    </div>

</form>
