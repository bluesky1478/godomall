<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $("#frmIcon").validate({
            submitHandler: function (form) {
                if (!$('input[name="displayField[]"]:checked').length) {
                    alert('노출항목을 선택하세요.');
                    return false;
                }
                if (!$('input[name="mobileDisplayField[]"]:checked').length) {
                    alert('노출항목을 선택하세요.');
                    return false;
                }

                form.target = 'ifrmProcess';
                form.submit();
                return false;
            },
            rules: {
            },
            messages: {
            }
        });

        $('input[name="displayType"][value="12"]').on('click', function(e){
            $('select[name="lineCnt"] option').eq(4).prop('selected', true);
            $('select[name="rowCnt"] option').eq(1).prop('selected', true);
        });

        $('input[name="mobileDisplayType"]').click(function(){
            mobileDisplayType(this.value, true);
        });

        set_soldOutDisplay();
        display_switch('displayType_','<?=$data['displayType']?>');
        display_switch('mobileDisplayType_','<?=$data['mobileDisplayType']?>');
        <?php if (in_array($data['mobileDisplayType'], ['02', '09', '11']) === true) { ?>
        mobileDisplayType('<?=$data['mobileDisplayType']?>', false);
        <?php } ?>
    });

    /**
     * 테마분류 선택
     *
     * @param string thisID 종류 ID
     */
    function display_switch(prefix,thisID)
    {
        $('.'+prefix).addClass('display-none');
        $('.'+prefix+thisID).removeClass('display-none');

    }

    function set_soldOutDisplay() {

        var soldOutFl = $('input[name="soldOutFl"]:checked').val();

        if(soldOutFl =='n') $('input[name="soldOutDisplayFl"]').attr('disabled',true);
        else $('input[name="soldOutDisplayFl"]').attr('disabled',false);

    }

    function mobileDisplayType(value, defaultFl)
    {
        switch (value){
            case '01'://갤러리형
            case '04'://상품이동형
            case '06'://스크롤형
            case '12'://스크롤형
                if ($('select[name="mobileLineCnt"] option').parent().is('span') === true) {
                    $('select[name="mobileLineCnt"] span > option').unwrap();
                }
                if (value == '01') {
                    $('select[name="mobileLineCnt"] option').eq(1).prop('selected', true);
                    $('select[name="mobileRowCnt"] option').eq(1).prop('selected', true);
                } else if (value == '04') {
                    $('select[name="mobileLineCnt"] option').eq(0).prop('selected', true);
                    $('select[name="mobileRowCnt"] option').eq(0).prop('selected', true);
                } else if (value == '06') {
                    $('select[name="mobileLineCnt"] option').eq(2).prop('selected', true);
                    $('select[name="mobileRowCnt"] option').eq(0).prop('selected', true);
                } else if (value == '12') {
                    $('select[name="mobileLineCnt"] option').eq(1).prop('selected', true);
                    $('select[name="mobileRowCnt"] option').eq(1).prop('selected', true);
                }
                break;
            case '02'://리스트형
            case '09'://심플이미지형
            case '11'://장바구니형
                if ($('select[name="mobileLineCnt"] option').parent().is('span') === false) {
                    $('select[name="mobileLineCnt"] option').prop('selected', false).not(':eq(0)').wrap('<span>').parent().hide();
                }
                if (defaultFl!=false && value == '11') {
                    $('select[name="mobileRowCnt"] option').eq(1).prop('selected', true);
                }
                break;
        }
    }
    //-->
</script>
<form id="frmIcon" name="frmIcon" action="./display_config_ps.php" method="post" enctype="multipart/form-data">

    <input type="hidden" name="mode" value="relation_register"/>


    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red" />

        </div>
    </div>

    <div class="table-title gd-help-manual">
        PC <?=end($naviMenu->location); ?>
    </div>

    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col /><col class="width" /><col /></colgroup>
        <tr>
            <th >이미지설정 </th>
            <td  colspan="3"><div class="form-inline">
                    <?php
                    foreach ($confImage as $key => $val) {
                        $arrImage[$key]    = $val['text'].' - '.$val['size1'].'pixel';
                    }
                    echo gd_select_box('imageCd','imageCd',$arrImage,null,$data['imageCd'],null,null,'form-control width-lg');
                    ?>
                    <span class="notice-info">이미지는 <a href="/policy/goods_images.php" target="_blank" class="btn-link">[기본설정>상품 정책>상품 이미지 사이즈 설정]</a>에서 관리할 수 있습니다.</span>
                </div>
            </td>
        </tr>
        <tr>
            <th >상품 노출 개수 </th>
            <td  colspan="3"><div class="form-inline">
                    가로 : <select  name="lineCnt"  class="form-control"  style="width:40px" ><?php for($i = 1; $i < 11; $i++) {?>
                            <option value="<?=$i?>" <?=gd_isset($selected['lineCnt'][$i]);?>> <?=$i?> </option>
                        <?php } ?></select> X  세로 : <select  name="rowCnt"  class="form-control" style="width:40px" ><?php for($i = 1; $i < 6; $i++) {?>
                            <option value="<?=$i?>" <?=gd_isset($selected['rowCnt'][$i]);?>> <?=$i?> </option>
                        <?php } ?></select>
                </div>
            </td>
        </tr>
        <tr>
            <th >품절상품 노출 </th>
            <td >
                <label class="radio-inline"><input type="radio" name="soldOutFl" value="y" <?=gd_isset($checked['soldOutFl']['y']);?> onclick="set_soldOutDisplay()"/>노출함</label>
                <label class="radio-inline"><input type="radio" name="soldOutFl" value="n" <?=gd_isset($checked['soldOutFl']['n']);?> onclick="set_soldOutDisplay()"/>노출안함</label>
            </td>
            <th >품절상품 진열</th>
            <td >
                <label class="radio-inline"><input type="radio" name="soldOutDisplayFl" value="y" <?=gd_isset($checked['soldOutDisplayFl']['y']);?>/>정렬 순서대로 보여주기</label>
                <label class="radio-inline"><input type="radio" name="soldOutDisplayFl" value="n" <?=gd_isset($checked['soldOutDisplayFl']['n']);?>/>리스트 끝으로 보내기</label>
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
                    <label class="checkbox-inline"  title=""><input type="checkbox" name="displayField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['displayField']))) { echo "checked"; } ?>  >  <?=$v?></label>
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
            <th >관련상품 연결 </th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="relationLinkFl" value="self" <?=gd_isset($checked['relationLinkFl']['self']);?>/>현재창에서 관련상품 상세페이지 연결</label>
                <label class="radio-inline"><input type="radio" name="relationLinkFl" value="blank" <?=gd_isset($checked['relationLinkFl']['blank']);?>/>새창으로 관련상품 상세페이지 연결</label>
            </td>
        </tr>
        <tr>
            <th >디스플레이 유형 </th>
            <td  colspan="3"><div class="form-inline">

                    <?php foreach($themeDisplayType as $k => $v) { ?>
                        <div  class="pd10  display_ <?=$v['class']?> <?php if($v['mobile'] != 'y') { echo ' display_pc'; }?> " style="float:left"><span>
                                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/display_theme_<?=$k?>.jpg"><br/>
                                            <label class="radio-inline mgt5" title="" ><input type="radio" name="displayType" value="<?=$k?>" <?=gd_isset($checked['displayType'][$k]);?>  onclick="display_switch('displayType_',this.value);" /><?=$v['name']?></label></span></div>
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
                        <td><label title="" class="radio-inline"><input type="radio" name="detailSet[06]" value="W" <?=gd_isset($checked['detailSet']['06']['W']);?>>가로형 </label><label title="" class="radio-inline"> <input type="radio" name="detailSet[06]" value="H" <?=gd_isset($checked['detailSet']['06']['H']);?>>세로형 </label></td>
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
                        <td><label title="" class="radio-inline"><input type="radio" name="detailSet[07][]" value="W" <?=gd_isset($checked['detailSet']['07']['W']);?>>가로형 </label><label title="" class="radio-inline"> <input type="radio" name="detailSet[07][]" value="H" <?=gd_isset($checked['detailSet']['07']['H']);?>>세로형 </label></td>
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
        <!--  세부설정 말풍선형 시작 -->
        <?php $checked['detailSetButton']['12'][$data['detailSetButton']['12']['0']] = 'checked="checked"';
        $checked['detailSetPosition']['12'][$data['detailSetPosition']['12']['0']] = 'checked="checked"';?>
        <tr class='displayType_ displayType_12'>
            <th >세부설정 </th>
            <td  colspan="5">
                <table class="table-cols">
                    <tr>
                        <th class='input_title'>노출버튼</th>
                        <td><label title="" class="radio-inline"><input type="radio" name="detailSetButton[12][]"   value="B" <?=gd_isset($checked['detailSetButton']['12']['B']);?>>장바구니 담기 + 바로구매</label><label title="" class="radio-inline"> <input type="radio" name="detailSetButton[12][]" value="C" <?=gd_isset($checked['detailSetButton']['12']['C']);?>>장바구니 담기 </label><label title="" class="radio-inline"> <input type="radio" name="detailSetButton[12][]" value="D" <?=gd_isset($checked['detailSetButton']['12']['D']);?>>바로구매</label></td>
                    </tr>
                    <tr>
                        <th class='input_title'>버튼노출 위치</th>
                        <td><label title=""><label title="" class="radio-inline"><input type="radio" name="detailSetPosition[12][]"   value="UB" <?=gd_isset($checked['detailSetPosition']['12']['UB']);?>>상단 + 하단 노출</label><label title="" class="radio-inline"> <input type="radio" name="detailSetPosition[12][]" value="UP" <?=gd_isset($checked['detailSetPosition']['12']['UP']);?>>상단 노출 </label><label title="" class="radio-inline"> <input type="radio" name="detailSetPosition[12][]" value="BO" <?=gd_isset($checked['detailSetPosition']['12']['BO']);?>>하단 노출</label></td>
                    </tr>
                </table>
            </td>
        </tr>
        <!--  세부설정 말풍선형 끝-->
    </table>

    <div class="table-title gd-help-manual">
        모바일 <?=end($naviMenu->location); ?>
    </div>

    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col /><col class="width" /><col /></colgroup>
        <tr>
            <th >이미지설정 </th>
            <td  colspan="3"><div class="form-inline">
                    <?php
                    foreach ($confImage as $key => $val) {
                        $arrImage[$key]    = $val['text'].' - '.$val['size1'].'pixel';
                    }
                    echo gd_select_box('mobileImageCd','mobileImageCd',$arrImage,null,$data['mobileImageCd'],null,null,'form-control width-lg');
                    ?>
                    <span class="notice-info">이미지는 <a href="/policy/goods_images.php" target="_blank" class="btn-link">[기본설정>상품 정책>상품 이미지 사이즈 설정]</a>에서 관리할 수 있습니다.</span>
                </div>
            </td>
        </tr>
        <tr>
            <th >상품 노출 개수 </th>
            <td  colspan="3"><div class="form-inline">
                    가로 : <select  name="mobileLineCnt"  class="form-control"  style="width:40px" ><?php for($i = 1; $i < 6; $i++) {?>
                            <option value="<?=$i?>" <?=gd_isset($selected['mobileLineCnt'][$i]);?>> <?=$i?> </option>
                        <?php } ?></select> X  세로 : <select  name="mobileRowCnt"  class="form-control" style="width:40px" ><?php for($i = 1; $i < 6; $i++) {?>
                            <option value="<?=$i?>" <?=gd_isset($selected['mobileRowCnt'][$i]);?>> <?=$i?> </option>
                        <?php } ?></select>
                </div>
            </td>
        </tr>
        <tr>
            <th >품절상품 노출 </th>
            <td >
                <label class="radio-inline"><input type="radio" name="mobileSoldOutFl" value="y" <?=gd_isset($checked['mobileSoldOutFl']['y']);?> onclick="set_soldOutDisplay()"/>노출함</label>
                <label class="radio-inline"><input type="radio" name="mobileSoldOutFl" value="n" <?=gd_isset($checked['mobileSoldOutFl']['n']);?> onclick="set_soldOutDisplay()"/>노출안함</label>
            </td>
            <th >품절상품 진열</th>
            <td >
                <label class="radio-inline"><input type="radio" name="mobileSoldOutDisplayFl" value="y" <?=gd_isset($checked['mobileSoldOutDisplayFl']['y']);?>/>정렬 순서대로 보여주기</label>
                <label class="radio-inline"><input type="radio" name="mobileSoldOutDisplayFl" value="n" <?=gd_isset($checked['mobileSoldOutDisplayFl']['n']);?>/>리스트 끝으로 보내기</label>
            </td>
        </tr>
        <th >품절 아이콘 노출 </th>
        <td >
            <label class="radio-inline"><input type="radio" name="mobileSoldOutIconFl" value="y" <?=gd_isset($checked['mobileSoldOutIconFl']['y']);?>/>노출함</label>
            <label class="radio-inline"><input type="radio" name="mobileSoldOutIconFl" value="n" <?=gd_isset($checked['mobileSoldOutIconFl']['n']);?>/>노출안함</label>
        </td>
        <th >아이콘 노출 </th>
        <td >
            <label class="radio-inline"><input type="radio" name="mobileIconFl" value="y" <?=gd_isset($checked['mobileIconFl']['y']);?>/>노출함</label>
            <label class="radio-inline"><input type="radio" name="mobileIconFl" value="n" <?=gd_isset($checked['mobileIconFl']['n']);?>/>노출안함</label>
        </td>
        </tr>
        <tr>
            <th class="require" >노출항목 설정 </th>
            <td  colspan="3">
                <?php foreach($themeDisplayField as $k => $v) { ?>
                    <label class="checkbox-inline"  title=""><input type="checkbox" name="mobileDisplayField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobileDisplayField']))) { echo "checked"; } ?>  >  <?=$v?></label>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th >할인적용가 설정</th>
            <td >
                <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                    <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="mobileGoodsDiscount[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobileGoodsDiscount']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                <?php } ?>
                <div class="notice-info">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
            </td>
            <th >취소선 추가 설정</th>
            <td >
                <?php foreach($themePriceStrike as $k => $v) { ?>
                    <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="mobilePriceStrike[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobilePriceStrike']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
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
                    <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="mobileDisplayAddField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobileDisplayAddField']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                <?php } ?>
                <div class="notice-info">[할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)</div>
            </td>
        </tr>
        <tr>
            <th >관련상품 연결 </th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="mobileRelationLinkFl" value="self" <?=gd_isset($checked['mobileRelationLinkFl']['self']);?>/>현재창에서 관련상품 상세페이지 연결</label>
                <label class="radio-inline"><input type="radio" name="mobileRelationLinkFl" value="blank" <?=gd_isset($checked['mobileRelationLinkFl']['blank']);?>/>새창으로 관련상품 상세페이지 연결</label>
            </td>
        </tr>
        <tr>
            <th >디스플레이 유형 </th>
            <td  colspan="3"><div class="form-inline">

                    <?php foreach($mobileThemeDisplayType as $k => $v) { ?>
                        <div  class="pd10  display_ <?=$v['class']?>" style="float:left"><span>
                                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/display_theme_<?=$k?>.jpg"><br/>
                                            <label class="radio-inline mgt5" title="" ><input type="radio" name="mobileDisplayType" value="<?=$k?>" <?=gd_isset($checked['mobileDisplayType'][$k]);?>  onclick="display_switch('mobileDisplayType_',this.value);" /><?=$v['name']?></label></span></div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php $checked['mobileDetailSet']['04'][$data['mobileDetailSetConfig']['04']['0']] = 'checked="checked"'; ?>
        <tr class='mobileDisplayType_ mobileDisplayType_04'>
            <th >세부설정 </th>
            <td  colspan="5">
                <table class="table-cols">
                    <tr>
                        <th class='input_title'>이동방향</th>
                        <td><label title="" class="radio-inline"><input type="radio" name="mobileDetailSet[04]" value="R" <?=gd_isset($checked['mobileDetailSet']['04']['R']);?> >오른쪽에서 왼쪽으로 </label><label title="" class="radio-inline"> <input type="radio" name="mobileDetailSet[04]" value="L" <?=gd_isset($checked['mobileDetailSet']['04']['L']);?>>왼쪽에서 오른쪽으로 </label></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php $checked['mobileDetailSet']['06'][$data['mobileDetailSetConfig']['06']['0']] = 'checked="checked"'; ?>
        <tr class='mobileDisplayType_ mobileDisplayType_06'>
            <th >세부설정 </th>
            <td  colspan="5">
                <table class="table-cols">
                    <tr>
                        <th class='input_title'>이동방향</th>
                        <td><label title="" class="radio-inline"><input type="radio" name="mobileDetailSet[06]" value="W" <?=gd_isset($checked['mobileDetailSet']['06']['W']);?>>가로형 </label></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php $checked['mobileDetailSetButton']['12'][$data['mobileDetailSetButton']['12']['0']] = 'checked="checked"';
        $checked['mobileDetailSetPosition']['12'][$data['mobileDetailSetPosition']['12']['0']] = 'checked="checked"';?>
        <tr class='mobileDisplayType_ mobileDisplayType_12'>
            <th >세부설정 </th>
            <td  colspan="5">
                <table class="table-cols">
                    <tr>
                        <th class='input_title'>노출버튼</th>
                        <td><label title="" class="radio-inline"><input type="radio" name="mobileDetailSetButton[12][]"   value="B" <?=gd_isset($checked['mobileDetailSetButton']['12']['B']);?>>장바구니 담기 + 바로구매</label><label title="" class="radio-inline"> <input type="radio" name="mobileDetailSetButton[12][]" value="C" <?=gd_isset($checked['mobileDetailSetButton']['12']['C']);?>>장바구니 담기 </label><label title="" class="radio-inline"> <input type="radio" name="mobileDetailSetButton[12][]" value="D" <?=gd_isset($checked['mobileDetailSetButton']['12']['D']);?>>바로구매</label></td>
                    </tr>
                    <tr>
                        <th class='input_title'>버튼노출 위치</th>
                        <td><label title=""><label title="" class="radio-inline"><input type="radio" name="mobileDetailSetPosition[12][]"   value="UB" <?=gd_isset($checked['mobileDetailSetPosition']['12']['UB']);?>>상단 + 하단 노출</label><label title="" class="radio-inline"> <input type="radio" name="mobileDetailSetPosition[12][]" value="UP" <?=gd_isset($checked['mobileDetailSetPosition']['12']['UP']);?>>상단 노출 </label><label title="" class="radio-inline"> <input type="radio" name="mobileDetailSetPosition[12][]" value="BO" <?=gd_isset($checked['mobileDetailSetPosition']['12']['BO']);?>>하단 노출</label></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
