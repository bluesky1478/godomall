<div class="page-header js-affix">
    <h3>
        <?= end($naviMenu->location); ?>
        <small>엑셀로 업로드한 상품에 대해서 이미지를 일괄 등록할 수 있습니다.</small>
    </h3>
</div>

<div class="table-title gd-help-manual">
    일괄처리   <span class="notice-info">"/data/goods/tmp/" 폴더에 업로드한 이미지는 파일정보를 불러와야 내역 확인 및 이미지 일괄 처리가 가능합니다.</span>
</div>

<div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>진행 정보</th>
            <td>
                처리할 이미지 개수 : <?=number_format($data['totalCnt'])?>개
                (처리가능 : <span class="text-blue"><?=number_format($data['possibleCount'])?></span>개 / 처리불가 : <span class="text-red"><?=number_format($data['totalCnt']-$data['possibleCount'])?></span>개)
                &nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-gray js-get-tmp">파일정보 불러오기</a>
            </td>
        </tr>
    </table>
</div>


<div id="tmpGoodsList" <?php if($data['totalCnt']<1) echo "style='display:none'"?>>
    <div class="table-title gd-help-manual">일괄처리 예정 내역보기</div>
    <form name="frmSearch" id="frmSearch" method="get">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-2xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>이미지 파일명</th>
                <td><input type="text" name="imageName" class="form-control" style="width:350px" value="<?= stripslashes($req['imageName']) ?>"></td>
                <th>적용가능상품</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="isApplyGoods" value="" <?= $checked['isApplyGoods']['all'] ?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="isApplyGoods" value="y" <?= $checked['isApplyGoods']['y'] ?>>처리가능</label>
                    <label class="radio-inline"><input type="radio" name="isApplyGoods" value="n" <?= $checked['isApplyGoods']['n'] ?>>처리불가</label>
                </td>
            </tr>
            <tr>
                <th>상품정보</th>
                <td colspan="3" class="form-inline">
                    <select class="form-control" name="searchField">
                        <option value="g.goodsNm" <?= $selected['g.goodsNm'] ?>>상품명</option>
                        <option value="g.goodsNo" <?= $selected['g.goodsNo'] ?>>상품코드</option>
                        <option value="g.goodsCd" <?= $selected['g.goodsCd'] ?>>자체상품코드</option>
                    </select>
                    <input type="text" name="searchKeyword" class="form-control width-xl" value="<?= $req['searchKeyword'] ?>">
                </td>
            </tr>
        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>

        <div class="table-title gd-help-manual">일괄처리 예정 리스트
            <span class="notice-danger">이미지수가 많은 경우 “전체 일괄처리”는 비권장합니다. 가능하면 한 페이지씩 선택하여 처리하세요.</span>
        </div>
        <div class="table-header">
            <div class="pull-left">
                검색&nbsp;<strong><?= number_format($data['searchCnt']) ?></strong>개/
                전체&nbsp;<strong><?= number_format($data['totalCnt']) ?></strong>개
            </div>
            <div class="pull-right">
                <div class="form-inline">
                    <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
                </div>
            </div>
        </div>
    </form>
    <form id="frmList" method="post" action="./goods_image_batch_ps.php" target="ifrmProcess">
        <input type="hidden" name="mode" value="">
        <table id="listTbl" cellpadding="0" cellspacing="0" class="table table-rows table-fixed">
            <thead>
            <tr class="center">
                <th class="width-3xs"><input type="checkbox" class="js-checkall" data-target-name="imageName"></th>
                <th class="width-3xs">번호</th>
                <th class="width-sm">이미지</th>
                <th>이미지 파일명</th>
                <th class="width-sm">적용 상품 개수</th>
                <!--            <th class="width-xl">비고</th>-->
                <th class="width-md">상품정보</th>
            </tr>
            </thead>
            <?php foreach ($data['list'] as $val) {
                ?>
                <tr class="center">
                    <td><input name="imageName[]" type="checkbox" value="<?= $val['imageName'] ?>"></td>
                    <td><?= $val['no'] ?></td>
                    <td><img src="<?= stripslashes($val['tmpPath']) ?>" width="30"></td>
                    <td><?= stripslashes($val['imageName']) ?></td>
                    <td><?= $val['applyGoodsCount'] ?>개</td>
                    <!--                <td>--><? //= $val['failMsg'] ?><!--</td>-->
                    <td>
                        <button type="button" class="btn btn-popup-view">보기</button>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <div class="table-action">
            <div class="pull-left form-inline">
                <button type="button" class="btn btn-red js-delete"/>
                선택삭제</button>

                <button type="button" class="btn btn-white js-select-batch"/>
                선택 일괄처리</button>
                <button type="button" class="btn btn-white js-all-batch"/>
                전체 일괄처리</button>
            </div>
        </div>
    </form>

    <div class="center"><?= $data['pageHtml'] ?></div>
</div>

<!-- 처리 Iframe -->
<iframe name="goodsImageProcess" id="goodsImageProcess" src="<?=URI_HOME ?>blank.php" width="100%" height="300" class="display-none"></iframe>
<!-- //처리 Iframe -->

<script type="text/javascript">
    $(document).ready(function () {
        var form = $('#frmList');
        var checkedLength = function () {
            return form.find("input[name='imageName[]']:checked").length;
        }

        $('.js-delete').bind('click', function () {
            if (checkedLength() <1) {
                alert('선택하신 이미지가 없습니다.');
                return false;
            }

            form.find('input[name=mode]').val('delete');
            dialog_confirm('선택된 '+checkedLength()+'개의 이미지를 정말로 삭제 하시겠습니까?\n\r삭제 시 정보는 복구되지 않습니다.', function (result) {
                if (result) {
                    form.submit();
                }
            });
        })

        $('.js-select-batch').bind('click', function () {
            if (checkedLength() <1) {
                alert('선택하신 이미지가 없습니다.');
                return false;
            }
            form.find('input[name=mode]').val('goods_image_batch');
            dialog_confirm('선택한 이미지를 일괄처리 하시겠습니까?', function (result) {
                if (result) {
                    form.submit();
                }
            });
        })

        $('.js-all-batch').bind('click', function () {
            var totalCnt = <?=$data['totalCnt']?>;
            var confirmMsg = '전체 일괄처리 하시겠습니까?';
            var maxAction = 500;
            if(totalCnt>=maxAction){
                confirmMsg = '서버 부하등 안정적인 서비스를 위해서 500개 이상 이미지의 일괄처리는 비권장합니다. <br> 전체 일괄처리 하시겠습니까?'
            }
            dialog_confirm(confirmMsg, function (result) {
                if (result) {
                    form.find('input[name=mode]').val('goods_image_batch');
                    form.find("input[name='imageName[]']").prop('checked', false);
                    form.submit();
                }
            });
        })

        $('.btn-popup-view').bind('click', function () {
            var imageName = $(this).closest('tr').find("input[name='imageName[]']").val();
            $.ajax({
                url: './layer_tmp_goods_info.php',
                type: 'get',
                data: {'imageName': imageName},
                async: false,
                success: function (data) {
                    BootstrapDialog.show({
                        title: '상품정보',
                        size: get_layer_size('nomal'),
                        message: $(data),
                        closable: true,
                    });
                }
            });
        })


        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearch').submit();
        });

        $('.js-get-tmp').bind('click', function () {
            BootstrapDialog.show({
                title: '로딩중',
                message: '<img src="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/ajax-loader.gif"> <b>파일정보를 가져오는중입니다.</b>',
                closable: true
            });

            ifrmProcess.location.href = 'goods_image_batch_ps.php?mode=saveTempGoodsImage';
        })
    })

 /*   function actionProgress(per, count) {
        $('#goodsImagesCnt').html(count);
        $('#progressBar').css('width', per + '%');
        $('#progressText').html(per + ' %');
    }*/

    function complete(total, success, fail) {

        var options = {};
        var defaultOptions = {
            type: BootstrapDialog.TYPE_PRIMARY,
            title: null,
            message: null,
            closable: false,
            draggable: false,
            buttonLabel: BootstrapDialog.DEFAULT_TEXTS.OK,
            callback: null
        };

        if (typeof arguments[0] === 'object' && arguments[0].constructor === {}.constructor) {
            options = $.extend(true, defaultOptions, arguments[0]);
        } else {
            options = $.extend(true, defaultOptions, {
                message: arguments[0],
                callback: typeof arguments[1] !== 'undefined' ? arguments[1] : null
            });
        }

        var html = '<table class="table table-cols">';
        html += '<colgroup><col width="33%"><col width="33%"><col></colgroup>';
        html += '<tr><th class="text-center">전체건수</th><th class="text-center">처리건수</th><th class="text-center">미처리건수</th></tr>';
        html += '<tr><td class="text-center">' + total + '</td><td class="text-center" style="color:#0079B6">' + success + '</td><td class="text-center" style="color:rgb(255, 0, 0)">' + fail + '</td></tr></table>';


        return new BootstrapDialog({
            type: options.type,
            title: '상품 이미지 일괄처리가 완료되었습니다',
            message: html,
            closable: true,
            draggable: false,
            data: {
                callback: options.callback
            },
            onhide: function (dialog) {
                $('.js-get-tmp').trigger('click');
              //  location.reload();  //TODO:파일정보 불러오기로 수정
            },
            buttons: [{
                label: options.buttonLabel,
                action: function (dialog) {
                    $('.js-get-tmp').trigger('click');
//                    location.reload();
                    dialog.close();
                }
            }]
        }).open();
    }
</script>
