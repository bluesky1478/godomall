<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="인기상품 노출 등록" class="btn btn-red-line js-register"/>
    </div>
</div>
<?php include($goodsSearchFrm); ?>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="populateSno"></th>
            <th class="width15p">순위타입</th>
            <th class="width35p">인기상품노출명</th>
            <th class="width5p">노출상태</th>
            <th class="width10p">치환코드</th>
            <th class="width5p">PC 페이지</th>
            <th class="width5p">모바일 페이지</th>
            <th class="width10p">URL 복사</th>
            <th class="width10p">등록일/수정일</th>
            <th class="width10p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $arrPopulateType = array('sell'=>'상품 판매순위(판매금액)', 'hit'=>'상품 클릭수 순위', 'sellCnt'=>'상품 판매순위(판매횟수)', 'view'=>'상품 조회수 순위', 'cart'=>'장바구니 담기 순위', 'wishlist'=>'찜리스트 담기 순위', 'review'=>'상품 후기 작성 순위', 'score'=>'상품 후기 평점 순위');
            $arrPopulateDisplay = array('' => '노출안함', 'y' => '노출함', 'n' => '노출안함');
            $arrPopulateUse = array('' => '사용안함', 'y' => '사용', 'n' => '사용안함');

            foreach ($data as $key => $val) {
                list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);


                if($val['applyFl'] !='y') {
                    $displayText = $arrGoodsApply[$val['applyFl']];
                    $sellText = $arrGoodsApply[$val['applyFl']];
                } else {
                    $displayText = $arrGoodsDisplay[$val['goodsDisplayFl']];
                    $sellText = $arrGoodsSell[$val['goodsSellFl']];
                }
                if($val['orderRate'] == 0 || !$val['orderRate']) {
                    $val['orderRate'] = 0;
                }
                ?>
                <tr>
                    <td class="center"><input type="checkbox" name="populateSno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>" /></td>
                    <td class="center number"><?=$arrPopulateType[$val['type']]; ?></td>
                    <td class="width-2xs center"><?=$val['populateName']; ?></td>
                    <td class="center"><?=$arrPopulateDisplay[$val['displayFl']]?></td>
                    <td class="center">
                        <a href="#" class="btn btn-gray btn-sm btn-preview" title="{=includeWidget('proc/_populate.html', 'sno', '<?=$val['sno']?>')}">코드보기</a>
                        <button type="button" title="<?php echo $val['populateName'] ?>" class="btn btn-white btn-sm btn-copy js-clipboard" data-clipboard-text="{=includeWidget('proc/_populate.html', 'sno', '<?=$val['sno']?>')}">복사</button>
                    </td>
                    <td class="center lmenu"><?=$arrPopulateUse[$val['useFl']]; ?></td>
                    <td class="center lmenu"><?=$arrPopulateUse[$val['mobileUseFl']]; ?></td>
                    <td class="center lmenu">
                        <button type="button" title="<?php echo $val['populateName'] ?>" class="btn btn-white btn-sm btn-copy js-clipboard" data-clipboard-text="<?=URI_HOME?>goods/populate.php?sno=<?=$val['sno']?>">PC</button>
                        <button type="button" title="<?php echo $val['populateName'] ?>" class="btn btn-white btn-sm btn-copy js-clipboard" data-clipboard-text="<?=URI_MOBILE?>goods/populate.php?sno=<?=$val['sno']?>">모바일</button>
                    </td>
                    <td class="center lmenu">
                        <?=gd_date_format('Y-m-d', $val['regdt']); ?><br />
                        <?=gd_date_format('Y-m-d', $val['moddt']); ?>
                    </td>
                    <td class="center padlr10"><a href="./populate_config.php?sno=<?=$val['sno']; ?>&page=<?=$page->page[now]?>" class="btn btn-white btn-sm">수정</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="12">검색된 정보가 없습니다!</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</form>
<div class="table-action">
    <div class="pull-left" style="width:100%; padding-top: 5px;">
        <button type="button" class="btn btn-white js-check-delete">선택 삭제</button>
    </div>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 삭제
        $('button.js-check-delete').click(function () {

            var chkCnt = $('input[name*="populateSno"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 값이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개를  정말로 삭제하시겠습니까?\n삭제 된 정보는 복구되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('populate_delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 등록
        $('.js-register').click(function () {
            if(<?=$totalCnt?> >= 10){
                alert("인기상품 노출 관리는 최대 10개까지만 등록 가능합니다.");
            }else{
                location.href = './populate_config.php';
            }
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });
    });

    /**
     * 메인상품진열 등록/수정 팝업창
     *
     * @author sueun
     */
    function display_main_popup(isProvider, page) {
        if (isProvider) var url = '/provider/share/popup_display_main_list.php?popupMode=yes';
        else var url = '/share/popup_display_main_list.php?popupMode=yes';

        if (page) url += page;

        win = popup({
            url: url,
            width: 1000,
            height: 800,
            resizable: 'yes'
        });
    }

    /**
     * 분류관리 팝업창
     *
     * @author sueun
     */
    function category_popup(isProvider, page) {
        if (isProvider) var url = '/provider/share/popup_display_main_group.php?popupMode=yes';
        else var url = '/share/popup_display_main_group.php?popupMode=yes';

        if (page) url += page;

        win = popup({
            url: url,
            width: 1000,
            height: 800,
            resizable: 'yes'
        });
    }

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

    var option = {
        trigger: 'hover',
        container: '#content',
    };
    $('.btn-preview').tooltip(option);

    /**
     * 위젯 미리보기
     *
     */
    $('.js-btn-preview').click(function() {
        $('.js-btn-preview').prop('disabled', true);
        var sno = $(this).data('sno');
        var title = "위젯 미리보기";
        $.get('./insgo_widget_preview.php',{ mode : 'list', sno : sno }, function(data){

            data = '<div id="viewInfoForm">'+data+'</div>';

            var layerForm = data;

            BootstrapDialog.show({
                title:title,
                size: get_layer_size('wide-lg'),
                message: $(layerForm),
                closable: true
            });
        });
    });

    $(document).on('click', '.modal, .modal .close', function(){
        $('.js-btn-preview').prop('disabled', false);
    });
    //-->
</script>
