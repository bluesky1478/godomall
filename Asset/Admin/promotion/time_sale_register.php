<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 등록
        $("#frmGoods").validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.

                if($("input[name='itemGoodsNm[]']").length == 0) {
                    alert('타임세일 상품을 선택해주세요.');
                    return false;
                }


                if($("input[name='benefit']").val() >  100) {
                    alert('타임세일 판매가를 확인해주세요.');
                    return false;
                }

                if ($("input[name='promotionDate[]']").eq(0).val() && $("input[name='promotionDate[]']").eq(1).val() && $("input[name='promotionDate[]']").eq(0).val() > $("input[name='promotionDate[]']").eq(1).val()) {
                    alert("진행기간을 확인해주세요.");
                    return false;
                }

                var tbodyCnt = $("#tbl_add_goods_set tbody").length;
                var fixGoods = [];


                for(var i = 0; i < tbodyCnt; i ++ ) {
                    if($("#tabGoodsCnt"+i).length > 0  || i == 0) {
                        if($("#add_goods"+i+" input[name='sortFix[]']").length) {

                            var tmpFixGoods = [];
                            $("#add_goods"+i+" input[name='sortFix[]']:checked").each(function () {
                                tmpFixGoods.push($(this).val());
                            });

                            fixGoods.push(tmpFixGoods.join('<?=INT_DIVISION?>'));
                        }
                    } else {
                        $("#add_goods"+i).remove();
                    }
                }
                var fixGoodsNo = fixGoods.join('<?=STR_DIVISION?>');


                if(fixGoodsNo !='null') {
                    $("#frmGoods").append("<input type='hidden' name='fixGoodsNo' value='"+fixGoodsNo+"'>");
                }

                form.target='ifrmProcess';
                form.submit();
            },
            rules: {
                timeSaleTitle: 'required',
                'promotionDate[]': 'required',
                benefit : 'required'
            },
            messages: {
                timeSaleTitle: {
                    required: '이벤트명을 입력해주세요.'
                },
                'promotionDate[]': {
                    required: '진행기간을 입력해주세요.'
                },
                benefit: {
                    required: '타임세일 판매가를 설정해주세요.',
                },
            }
        });

        viewThemeConfig('tbl_pcThemeInfo',$('select[name="pcThemeCd"]').val());
        viewThemeConfig('tbl_mobileThemeInfo',$('select[name="mobileThemeCd"]').val());


        // 상세 설명 전환
        $("#btnDescriptionShop, #btnDescriptionMobile").click(function () {

            if (this.id == 'btnDescriptionShop') {
                $('#btnDescriptionShop').addClass('active');
                $('#btnDescriptionMobile').removeClass('active');
                $("#textareaDescriptionShop").show();
                $("#textareaDescriptionMobile").hide();
            } else {
                $('#btnDescriptionShop').removeClass('active');
                $('#btnDescriptionMobile').addClass('active');
                $("#textareaDescriptionShop").hide();
                $("#textareaDescriptionMobile").show();
            }
            return false;
        });

        $("input[name='displayFl']").click(function () {

            if($(this).val() =='p') {
                $("#btnDescriptionShop").click();
                $(".js-pc-theme").show();
                $(".js-mobile-theme").hide();
                $("select[name='pcThemeCd']").prop("disabled",false);
                $("select[name='mobileThemeCd']").prop("disabled",true);
            } else if($(this).val() =='m') {
                $("#btnDescriptionMobile").click();
                $(".js-pc-theme").hide();
                $(".js-mobile-theme").show();
                $("select[name='pcThemeCd']").prop("disabled",true);
                $("select[name='mobileThemeCd']").prop("disabled",false);
            } else {
                $(".js-pc-theme").show();
                $(".js-mobile-theme").show();
                $("#btnDescriptionShop").click();
                $("select[name='pcThemeCd']").prop("disabled",false);
                $("select[name='mobileThemeCd']").prop("disabled",false);
            }

        });


        <?php if($data['mode'] =='modify') { ?>view_display();<?php } ?>

        // 등록 기간 초기화
        <?php if ($data['mode'] == 'register') { ?>
        if ($('.js-dateperiod input:radio[value="6"]').length) {
            $('.js-dateperiod input:radio[value="6"]').trigger('click');
        }
        <?php } ?>
    });

    /**
     * 테마보기
     */
    function viewThemeConfig(tbl,themeCd) {

        var parameters = {
            'mode': 'theme_ajax',
            'themeCd': themeCd
        };

        $.post("../goods/display_config_ps.php",parameters,
            function(data){
                $.each(data, function(i,item){

                    $("#"+tbl+" .tbl_theme_"+i).html(item);
                });
            }, "json");
    }

    var activeGoodsBody = "0";

    /**
     * 상품 선택
     *
     * @param string orderNo 주문 번호
     */
    function goods_search_popup()
    {
        var addParam= "";
        var mobileFl =  $("input[name='displayFl']:checked").val();
        /*if(mobileFl =='p') {
            mobileFl = 'n';
            addParam = "&goodsSellFl=y";
        }
        else if(mobileFl =='m') {
            mobileFl = 'y';
            addParam = "&goodsSellMobileFl=y";
        }
        else {
            mobileFl = 'all';
            addParam = "&goodsSellFl=y&goodsSellMobileFl=y";
        }*/

        var timeSaleStartDt = $("input[name='promotionDate[]']").eq(0).val();
        var timeSaleEndDt = $("input[name='promotionDate[]']").eq(1).val();
        if(timeSaleStartDt && timeSaleEndDt) {
            window.open('../share/popup_goods.php?timeSaleFl=y&soldOut=n&timeSaleStartDt='+timeSaleStartDt+'&timeSaleEndDt='+timeSaleEndDt+addParam, 'popup_goods_search', 'width=1255, height=790, scrollbars=no');
        } else {
            alert("타임세일 시작일/종료일은 먼저 설정해주세요.")
        }
    }

    function setAddGoods(frmData) {
        var addHtml = "";

        $.each(frmData.info, function (key, val) {
            var stockText = "";
            // 상품 재고
            if (val.stockFl == 'n') {
                totalStock    = '∞';
            } else {
                totalStock    = val.totalStock ;
            }


            if(val.soldOutFl =='y' || totalStock== 0) stockText = "품절";
            else stockText = "정상";


            if(val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            } else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }

            if(val.goodsDisplayFl =='y') goodsDisplayFlText = "노출함";
            else goodsDisplayFlText = "노출안함";

            if(val.goodsDisplayMobileFl =='y') goodsDisplayMobileFlText = "노출함";
            else goodsDisplayMobileFlText = "노출안함";

            if(val.goodsSellFl =='y') goodsSellFlText = "판매함";
            else goodsSellFlText = "판매안함";

            if(val.goodsSellMobileFl =='y') goodsSellMobileFlText = "판매함";
            else goodsSellMobileFlText = "판매안함";


            addHtml += '<tr id="tbl_add_goods_'+val.goodsNo+'" '+tableCss+'>';
            addHtml += '<td class="center">';
            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="'+val.goodsNm+'" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="'+val.goodsPrice+'" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="'+val.scmNm+'" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="'+val.totalStock+'" />';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="'+val.brandNm+'" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="'+val.makerNm+'" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="'+val.image+'" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="'+val.soldOutFl+'" />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="'+val.stockFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsDisplayFl[]" value="'+val.goodsDisplayFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsDisplayMobileFl[]" value="'+val.goodsDisplayMobileFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsSellFl[]" value="'+val.goodsSellFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsSellMobileFl[]" value="'+val.goodsSellMobileFl+'" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_'+val.goodsNo+'"  value="'+val.goodsNo+'"/></td>';
            addHtml += '<td class="center number addGoodsNumber_'+val.goodsNo+'">'+(key+1)+'</td>';
            addHtml += '<td class="center">'+decodeURIComponent(val.image)+'</td>';
            addHtml += '<td><a href="../goods/goods_register.php?goodsNo='+val.goodsNo+'" target="_blank">'+val.goodsNm+'</a><input type="hidden" name="goodsNoData[]" value="'+val.goodsNo+'" />';
            addHtml += '<input type="checkbox" name="sortFix[]" class="layer_sort_fix_'+val.goodsNo+'"  value="'+val.goodsNo+'" '+sortFix+' style="display:none" ></td>';
            addHtml += '<td class="center">'+val.goodsPrice+'</td>';
            addHtml += '<td class="center">'+val.scmNm+'</td>';
            addHtml += '<td class="center">'+totalStock+'</td>';
            addHtml += '<td class="center">'+stockText+'</td>';
            addHtml += '<td class="center js-goodschoice-hide">'+goodsDisplayFlText+'</td>';
            addHtml += '<td class="center js-goodschoice-hide">'+goodsDisplayMobileFlText+'</td>';
            addHtml += '<td class="center js-goodschoice-hide">'+goodsSellFlText+'</td>';
            addHtml += '<td class="center js-goodschoice-hide">'+goodsSellMobileFlText+'</td>';
            addHtml += '</tr>';

        });

        $("#add_goods"+activeGoodsBody).html(addHtml);


        var cnt = $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').length;

        if($("#tabGoodsCnt"+activeGoodsBody).length) {
            $("#tabGoodsCnt"+activeGoodsBody).html(cnt);
            $("input[name='tabGoodsCnt["+activeGoodsBody+"]']").val(cnt);
        }

        $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').each(function (idx) {
            $('#add_goods'+activeGoodsBody+' .addGoodsNumber_'+$(this).val()).html(idx+1);
        });
    }

    function delete_option() {

        var chkCnt = $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 상품이 없습니다.');
            return;
        }
        if (confirm('선택한 ' + chkCnt + '개 상품을 삭제하시겠습니까?')) {
            $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]:checked').each(function () {
                //field_remove('tbl_add_goods_' + $(this).val());
                $(this).closest("tr").remove();

            });

            var cnt = $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').length;
            if($("#tabGoodsCnt"+activeGoodsBody).length) {
                $("#tabGoodsCnt"+activeGoodsBody).html(cnt);
                $("input[name='tabGoodsCnt["+activeGoodsBody+"]']").val(cnt);
            }

            $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').each(function (idx) {
                $('#add_goods'+activeGoodsBody+' .addGoodsNumber_'+$(this).val()).html(idx+1);
            });
        }
    }

    function view_display() {

        var chk = $("input[name='orderCntDisplayFl']:checked").val();
        if(chk =='n') $(".js-order-cnt-date").hide();
        else $(".js-order-cnt-date").show();

    }

    /**
     * 테마 입력
     *
     * @param string orderNo 주문 번호
     */
    function add_theme_popup(mobileFl)
    {
        if(mobileFl =="n") var addTheme = "pcThemeCd";
        else var addTheme = "mobileThemeCd";
        window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate=F&addTheme='+addTheme+'&mobileFl='+mobileFl, 'member_crm', 'width=1210, height=700, scrollbars=yes');
    }



    //-->
</script>

<form id="frmGoods" name="frmGoods"  target ="ifrmProcess" action="./time_sale_ps.php" method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?=$data['mode']?>"/>
    <?php if($data['mode'] =='modify') { ?><input type="hidden" name="sno" value="<?=$data['sno']?>"/><?php } ?>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./time_sale_list.php');" />
            <input type="submit"   value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title">
        기본설정
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">이벤트명</th>
            <td class="input_area" colspan="3" > <div class="form-inline">
                    <input type="text" name="timeSaleTitle"  class="form-control width-2xl js-maxlength" value="<?=$data['timeSaleTitle']?>" maxlength="30"></div>
            </td>
        </tr>
        <tr>
            <th >이벤트 페이지 주소</th>
            <td class="input_area" colspan="3" >
                <?php if($data['mode'] =='register') { ?>
                    타임세일 등록 완료 시 자동으로 생성됩니다.
                <?php } else { ?>
                <p><span class="btn btn-gray btn-xs" >&nbsp;&nbsp;P C&nbsp;&nbsp;</span> <a href="<?=URI_HOME;?>event/time_sale.php?sno=<?=$data['sno'];?>" target="_blank"><?=URI_HOME;?>event/time_sale.php?sno=<?=$data['sno'];?></a></p>
                    <p><span class="btn btn-gray btn-xs">모바일</span> <a href="<?=URI_MOBILE;?>event/time_sale.php?sno=<?=$data['sno'];?>" target="_blank"><?=URI_MOBILE;?>event/time_sale.php?sno=<?=$data['sno'];?></a></p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th class="require">진행기간</th>
            <td class="input_area" colspan="3">
                <div class="form-inline">
                <div class="input-group js-datetimepicker">
                    <input type="text" name="promotionDate[]" class="form-control width-md"  value="<?=$data['startDt']?>" data-init="n"  placeholder="수기입력 가능">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                </div>
                ~
                <div class="input-group js-datetimepicker">
                    <input type="text" name="promotionDate[]" class="form-control width-md"  value="<?=$data['endDt']?>" placeholder="수기입력 가능">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                </div>
                <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="promotionDate[]" data-target-inverse="promotionDate[]">
                    <label class="btn btn-white btn-sm"><input type="radio" value="0">오늘</label>
                    <label class="btn btn-white btn-sm"><input type="radio" value="6">7일</label>
                    <label class="btn btn-white btn-sm"><input type="radio" value="14">15일</label>
                    <label class="btn btn-white btn-sm"><input type="radio" value="29">1개월</label>
                    <label class="btn btn-white btn-sm"><input type="radio" value="89">3개월</label>
                    <label class="btn btn-white btn-sm"><input type="radio" value="364">1년</label>
                </div>
                </div>
            </td>
        </tr>
        <tr>
            <th >상품명 말머리</th>
            <td class="input_area" colspan="3" > <div class="form-inline">
                    <input type="text" name="goodsNmDescription"  class="form-control js-maxlength" value="<?=$data['goodsNmDescription']?>" maxlength="20">
                <p class="notice-info">타임세일 상품상세페이지의 상품명 앞에 노출되는 정보입니다.</p>
                </div>
            </td>
        </tr>
        <tr>
            <th >리스트 내<br />남은 기간 노출</th>
            <td class="input_area" colspan="3" >
                <div class="form-inline">
                    <label class="checkbox-inline" style="padding-top:5px;">
                        <input type="checkbox" name="leftTimeDisplayType[]" value="PC" <?=gd_isset($checked['leftTimeDisplayType']['pc']);?> /> PC 쇼핑몰 노출
                    </label>
                    <label class="checkbox-inline" style="padding-top:5px;">
                        <input type="checkbox" name="leftTimeDisplayType[]" value="MOBILE" <?=gd_isset($checked['leftTimeDisplayType']['m']);?> /> 모바일 쇼핑몰 노출
                    </label>
                    <p class="notice-info">노출 설정 시 리스트 내 타임세일가 우측에 남은 기간이 노출됩니다.</p>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">타임세일 판매가</th>
            <td class="input_area" colspan="3" > <div class="form-inline">
                 각 상품의 판매가 - <input type="text" name="benefit"  class="form-control width-2xs js-number-only" value="<?=$data['benefit']?>" maxlength="3"> % (판매가기준)</div>
                <label class="checkbox-inline" style="padding-top:5px;"><input type="checkbox" name="goodsPriceViewFl" value="y"  <?=gd_isset($checked['goodsPriceViewFl']['y']);?> >상품 리스트/상세 페이지 내 상품 판매가 노출</label>
                <div class="notice-info">타임세일 할인은 추가상품가, 텍스트옵션가 제외 <b style="color: #fa2828;">판매가, 옵션가에만 적용</b>됩니다.</div>
            </td>
        </tr>
        <tr>
            <th >상세페이지 내 <br/>판매개수 노출</th>
            <td class="input_area">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="orderCntDisplayFl" value="y"  <?=gd_isset($checked['orderCntDisplayFl']['y']);?> onclick="view_display()" >노출함</label>
                    <label class="radio-inline"><input type="radio" name="orderCntDisplayFl" value="n"  <?=gd_isset($checked['orderCntDisplayFl']['n']);?>  onclick="view_display()" >노출안함</label><br/>
                    <label class="checkbox-inline js-order-cnt-date" style="padding-top:5px;"><input type="checkbox" name="orderCntDateFl" value="y"  <?=gd_isset($checked['orderCntDateFl']['y']);?> >타임세일 진행기간 기준</label>
                </div>
            </td>
        </tr>
        <tr>
            <th >회원등급 혜택 적용</th>
            <td class="input_area" colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="memberDcFl" value="y"  <?=gd_isset($checked['memberDcFl']['y']);?> >적용함</label>
                    <label class="radio-inline"><input type="radio" name="memberDcFl" value="n"  <?=gd_isset($checked['memberDcFl']['n']);?> >적용안함</label>
                    <div class="notice-info">회원등급별 할인/적립혜택 적용여부를 설정합니다.</div>
                </div>
            </td>
        </tr>
        <tr>
            <th >마일리지 적립</th>
            <td class="input_area" colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="mileageFl" value="y"  <?=gd_isset($checked['mileageFl']['y']);?> >적립함</label>
                    <label class="radio-inline"><input type="radio" name="mileageFl" value="n"  <?=gd_isset($checked['mileageFl']['n']);?> >적립안함</label>
                    <div class="notice-info">상품 &gt; 상품관리 &gt; 상품등록에서 설정한 마일리지 적립여부를 설정합니다.</div>
                </div>
            </td>
        </tr>
        <tr>
            <th >상품적용 쿠폰 사용</th>
            <td class="input_area" colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="couponFl" value="y"  <?=gd_isset($checked['couponFl']['y']);?> >사용가능</label>
                    <label class="radio-inline"><input type="radio" name="couponFl" value="n"  <?=gd_isset($checked['couponFl']['n']);?> >사용불가</label>
                    <div class="notice-info">타임세일 상품으로 선택된 상품에 적용된 쿠폰의 사용여부를 설정합니다.</div>
                </div>
            </td>
        </tr>
        </table>

        <div class="table-title ">
            이벤트 노출 설정
        </div>
        <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th >노출범위</th>
            <td class="input_area" colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="displayFl" value="all"  <?=gd_isset($checked['displayFl']['all']);?>>PC+모바일</label>
                    <label class="radio-inline"><input type="radio" name="displayFl" value="p"  <?=gd_isset($checked['displayFl']['p']);?>>PC쇼핑몰</label>
                    <label class="radio-inline"><input type="radio" name="displayFl" value="m"  <?=gd_isset($checked['displayFl']['m']);?>>모바일쇼핑몰</label>
                </div>
            </td>
        </tr>
        <tr>
            <th >이벤트내용</th>
            <td class="input_area" colspan="3">
                <div class="desc_box">
                    <ul class="nav nav-tabs nav-tabs-sm">
                        <li class="active display-inline" id="btnDescriptionShop"><a href="#textareaDescriptionShop">PC쇼핑몰</a></li>
                        <li class="display-inline" id="btnDescriptionMobile"><a href="#textareaDescriptionMobile">모바일쇼핑몰</a></li>
                    </ul>
                    <div id="textareaDescriptionShop">
                        <textarea name="pcDescription"  rows="3" style="width:100%; height:400px;" id="editor" class="form-control"><?=$data['pcDescription'];?></textarea></div>
                    <div id="textareaDescriptionMobile" >
                        <textarea  name="mobileDescription" rows="3" style="width:100%; height:400px;" id="editor2" class="form-control"><?=$data['mobileDescription'];?></textarea>
                    </div>

                </div>
            </td>
        </tr>
            <tr>
                <th >진열방법 선택</th>
                <td class="input_area" colspan="3" > <div class="form-inline">
                        <?=gd_select_box('sort', 'sort', $data['sortList'], null, $data['sort'], null); ?></div>
                </td>
            </tr>
        <tr>
            <th>PC쇼핑몰 테마선택</th>
            <td class="input_area" ><div class="form-inline">
                    <select name="pcThemeCd" onchange="viewThemeConfig('tbl_pcThemeInfo',this.value);" class="form-control">
                        <?php foreach($data['pcThemeList'] as $k => $v) { ?>
                            <option value="<?=$v['themeCd']?>" <?=gd_isset($selected['pcThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
                        <?php } ?>
                    </select>
                    <input type="button" class="btn btn-sm btn-black" value="테마 등록" onclick="add_theme_popup('n')" /></div>
            </td>
            <th>모바일쇼핑몰 테마선택</th>
            <td class="input_area" ><div class="form-inline">
                    <select name="mobileThemeCd" onchange="viewThemeConfig('tbl_mobileThemeInfo',this.value);" class="form-control">
                        <?php foreach($data['mobileThemeList'] as $k => $v) { ?>
                            <option value="<?=$v['themeCd']?>" <?=gd_isset($selected['mobileThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
                        <?php } ?>
                    </select>
                    <input type="button" class="btn btn-sm btn-black" value="테마 등록" onclick="add_theme_popup('y')" /></div>
            </td>
        </tr>
        <tr>
            <th>하단 더보기 노출 상태</th>
            <td colspan="3"><div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="moreBottomFl" value="y" <?=gd_isset($checked['moreBottomFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="moreBottomFl" value="n"  <?=gd_isset($checked['moreBottomFl']['n']);?>/>노출안함</label>
                </div>
            </td>
        </tr>
    </table>

    <div class="js-pc-theme">
    <div class="table-title ">
        선택된 PC쇼핑몰 테마 정보
    </div>

    <table class="table table-cols" id="tbl_pcThemeInfo">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th >이미지 설정</th>
            <td  colspan="3" class="tbl_theme_imageCdNm">  </td>
        </tr>
        <tr>
            <th >상품 노출 개수</th>
            <td  colspan="3"  class="tbl_theme_cntNm">  </td>
        </tr>
        <tr>
            <th >품절상품 노출</th>
            <td  class="tbl_theme_soldOutFlNm">  </td>
            <th >품절상품 진열</th>
            <td  class="tbl_theme_soldOutDisplayFlNm">  </td>
        </tr>
        <tr>
            <th >품절 아이콘 노출</th>
            <td  class="tbl_theme_soldOutIconFlNm">  </td>
            <th >아이콘 노출</th>
            <td  class="tbl_theme_iconFlNm">  </td>
        </tr>
        <tr>
            <th >노출항목 설정</th>
            <td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
        </tr>
        <tr>
            <th >디스플레이 유형</th>
            <td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
        </tr>
    </table>

    </div>
    <div class="js-mobile-theme">
    <div class="table-title ">
        선택된 모바일쇼핑몰 테마 정보
    </div>

    <table class="table table-cols" id="tbl_mobileThemeInfo">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th >이미지 설정</th>
            <td  colspan="3" class="tbl_theme_imageCdNm">  </td>
        </tr>
        <tr>
            <th >상품 노출 개수</th>
            <td  colspan="3"  class="tbl_theme_cntNm">  </td>
        </tr>
        <tr>
            <th >품절상품 노출</th>
            <td  class="tbl_theme_soldOutFlNm">  </td>
            <th >품절상품 진열</th>
            <td  class="tbl_theme_soldOutDisplayFlNm">  </td>
        </tr>
        <tr>
            <th >품절 아이콘 노출</th>
            <td  class="tbl_theme_soldOutIconFlNm">  </td>
            <th >아이콘 노출</th>
            <td  class="tbl_theme_iconFlNm">  </td>
        </tr>
        <tr>
            <th >노출항목 설정</th>
            <td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
        </tr>
        <tr>
            <th >디스플레이 유형</th>
            <td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
        </tr>
    </table>
    </div>
    <div class="table-title ">
        진열상품 설정
    </div>
    <div id="tabTitle">


    </div>
    <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows">
        <thead>
        <tr id="goodsRegisteredTrArea">
            <th class="width2p" rowspan="2"><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="itemGoodsNo[]"/></th>
            <th class="width2p center" rowspan="2">번호</th>
            <th class="width5p center" rowspan="2">이미지</th>
            <th rowspan="2">상품명</th>
            <th class="width10p center" rowspan="2">판매가</th>
            <th class="width10p center" rowspan="2">공급사</th>
            <th class="width5p center" rowspan="2">재고</th>
            <th class="width5p center" rowspan="2">품절상태</th>
            <th class="width5p center" colspan="2">노출상태</th>
            <th class="width5p center" colspan="2">판매상태</th>
        </tr>
        <tr>
            <th class="width5p center">PC쇼핑몰</th>
            <th class="width5p center">모바일쇼핑몰</th>
            <th class="width5p center">PC쇼핑몰</th>
            <th class="width5p center">모바일쇼핑몰</th>
        </tr>
        </thead>

                    <tbody id="add_goods0"  class="active">
                    <?php

                    if($data['goodsNo']) {

                        $arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');
                        $arrGoodsSell = array('y' => ' 판매함', 'n' => '판매안함');
                        $cnt = 1;

                    foreach ($data['goodsNo'] as $key => $val) {

                        list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                        ?>

                        <tr id="tbl_add_goods_<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], array_values($data['fixGoodsNo']))) { ?>style='background:#d3d3d3' class="add_goods_fix" <?php } else { ?>class="add_goods_free" <?php } ?>>
                            <td class="center">
                                <input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
                                <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice']) ?>"/>
                                <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                                <input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
                                <input type="hidden" name="itemImage[]" value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
                                <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                                <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                                <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                                <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
                                <input type="hidden" name="itemGoodsDisplayFl[]" value="<?=gd_isset($val['goodsDisplayFl'])?>" />
                                <input type="hidden" name="itemGoodsDisplayMobileFl[]" value="<?=gd_isset($val['goodsDisplayMobileFl'])?>" />
                                <input type="hidden" name="itemGoodsSellFl[]" value="<?=gd_isset($val['goodsSellFl'])?>" />
                                <input type="hidden" name="itemGoodsSellMobileFl[]" value="<?=gd_isset($val['goodsSellMobileFl'])?>" />
                                <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>"/>
                            </td>
                            <td class="center number addGoodsNumber_<?=$val['goodsNo']; ?>"><?=number_format($cnt++);?></td>
                            <td class="center"><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                            <td>
                                <a href="../goods/goods_register.php?goodsNo=<?=$val['goodsNo'];?>" target="_blank"><?=$val['goodsNm'];?></a>
                                <input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
                                <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], $data['fixGoodsNo'])) {
                                    echo "checked='true'";
                                } ?> style="display:none">
                            </td>
                            <td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
                            <td class="center"><?=$val['scmNm']; ?></td>
                            <td class="center"><?= $totalStock ?></td>
                            <td class="center"><?= $stockText ?></td>
                            <td class="center js-goodschoice-hide"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                            <td class="center js-goodschoice-hide"><?=$arrGoodsDisplay[$val['goodsDisplayMobileFl']]; ?></td>
                            <td class="center js-goodschoice-hide"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                            <td class="center js-goodschoice-hide"><?=$arrGoodsSell[$val['goodsSellMobileFl']]; ?></td>
                        </tr>
                        <?php
                    } ?>


                <?php } else {  ?>
                    <tr id="tbl_add_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
                <?php }
            ?>
                    </tbody>
    </table>




    <div class="table-action">
        <div class="pull-left">
            <button class="btn btn-white checkDelete" type="button" onclick="delete_option()">선택 삭제</button>
        </div>

        <div class="pull-right">
            <button class="btn btn-white checkRegister" type="button"  onclick="goods_search_popup()">상품 선택</button>
        </div>

    </div>


</form>

<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor2",
        sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.html",
        htParams: {
            bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
            //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
            fOnBeforeUnload: function () {
                //alert("완료!");
            }
        }, //boolean
        fOnAppLoad: function () {
            //예제 코드
            //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);

            $("#textareaDescriptionMobile").hide();
        },
        fCreator: "createSEditor2"
    });


    $(document).ready(function () {
    });


</script>
