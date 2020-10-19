<style>
    .js-contents-short {
        display: inline-block;
        width: 95%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .js-preview{
        position:relative
    }
    .plusPreview{
        width:500px;
        height:550px;
        position: absolute;
        display: none;
        border: 2px solid #aaaaaa;
        padding: 0px;
        margin: 0px;
        background-color: #ffffff;
        z-index: 5000;
    }
    .bgColor{
        background-color:  #E8E8E8;
    }
</style>
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
        <small>플러스리뷰에 등록된 게시글을 관리합니다.</small>
    </h3>
</div>
<div class="table-title">플러스리뷰 게시글 관리</div>
<form name="frmSearch" id="frmSearch" action="plus_review_list.php" class="frmSearch js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <tr>
                <th>게시판</th>
                <td colspan="3"><b>플러스리뷰 게시판</b></td>
            </tr>
            <tr>
                <th>일자</th>
                <td class="form-inline" colspan="3">
                    <select name="searchDateFl" class="form-control">
                        <option value="regDt" <?php if ($req['searchDateFl'] == 'regDt') echo 'selected' ?>>
                            등록일
                        </option>
                        <option value="modDt" <?php if ($req['searchDateFl'] == 'modDt') echo 'selected' ?>>
                            수정일
                        </option>
                    </select>

                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="rangDate[]"
                               value="<?= $req['rangDate'][0]; ?>">
                        <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="rangDate[]"
                               value="<?= $req['rangDate'][1]; ?>">
                        <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                    </div>
                    <?= gd_search_date(gd_isset($req['searchPeriod'], 6), 'rangDate', false) ?>
                </td>
            </tr>
            <tr>
                <th class="width-md">검색어</th>
                <td class="form-inline" colspan="3">
                    <select class="form-control" name="searchField">
                        <option value="goodsNm" <?php if ($req['searchField'] == 'goodsNm') echo 'selected' ?>>상품명
                        </option>
                        <option value="contents" <?php if ($req['searchField'] == 'contents') echo 'selected' ?>>내용
                        </option>
                        <option value="writerNm" <?php if ($req['searchField'] == 'writerNm') echo 'selected' ?>>이름
                        </option>
                        <option value="writerNick" <?php if ($req['searchField'] == 'writerNick') echo 'selected' ?>>닉네임
                        </option>
                        <option value="writerId" <?php if ($req['searchField'] == 'writerId') echo 'selected' ?>>아이디
                        </option>
                    </select>
                    <input name="searchWord" value="<?= gd_isset($req['searchWord']) ?>" class="form-control form-control width-3xl">
                </td>
            </tr>
            <tr>
                <th class="width-md">속성</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="reviewType" value="" <?php if ($req['reviewType'] == '') echo 'checked' ?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="reviewType" value="photo" <?php if ($req['reviewType'] == 'photo') echo 'checked' ?>>포토리뷰</label>
                    <label class="radio-inline"><input type="radio" name="reviewType" value="text" <?php if ($req['reviewType'] == 'text') echo 'checked' ?>>일반리뷰</label>
                </td>
                <th class="width-md">댓글여부</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="isMemo" value="" <?php if ($req['isMemo'] == '') echo 'checked' ?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="isMemo" value="y" <?php if ($req['isMemo'] == 'y') echo 'checked' ?>>댓글있음</label>
                    <label class="radio-inline"><input type="radio" name="isMemo" value="n" <?php if ($req['isMemo'] == 'n') echo 'checked' ?>>댓글없음</label>
                </td>
            </tr>
            <tr>
                <th class="width-md">마일리지 지급</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="mileage" value="" <?php if ($req['mileage'] == '') echo 'checked' ?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="mileage" value="y" <?php if ($req['mileage'] == 'y') echo 'checked' ?>>지급완료</label>
                    <label class="radio-inline"><input type="radio" name="mileage" value="w" <?php if ($req['mileage'] == 'w') echo 'checked' ?>>지급예정</label>
                    <label class="radio-inline"><input type="radio" name="mileage" value="n" <?php if ($req['mileage'] == 'n') echo 'checked' ?>>미지급</label>
                    <label class="radio-inline"><input type="radio" name="mileage" value="i" <?php if ($req['mileage'] == 'i') echo 'checked' ?>>지급불가</label>
                </td>
                <th class="width-md">승인여부</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="applyFl" value="" <?php if ($req['applyFl'] == '') echo 'checked' ?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="applyFl" value="y" <?php if ($req['applyFl'] == 'y') echo 'checked' ?>>승인</label>
                    <label class="radio-inline"><input type="radio" name="applyFl" value="n" <?php if ($req['applyFl'] == 'n') echo 'checked' ?>>미승인</label>
                </td>
            </tr>
        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>
</form>

<div class="table-header">
    <div class="pull-left">
        검색&nbsp;<strong><?= number_format($list['cnt']['search']) ?></strong>개/
        전체&nbsp;<strong><?= number_format($list['cnt']['total']) ?></strong>개
    </div>
    <div class="pull-right">
        <div class="form-inline">
            <?= gd_select_box('sort', 'sort', $list['sort'], null, $req['sort']); ?>
            <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
        </div>
    </div>
</div>

<form name="frmList" id="frmList" action="plus_review_ps.php" method="post" class="content-form js-list-form" target="ifrmProcess">
    <input type="hidden" name="mode" value="delete">
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width-2xs"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width-2xs">번호</th>
            <th>내용</th>
            <th class="width-3xs">속성</th>
            <th class="width-3xs">댓글</th>
            <th class="width-3xs">평가</th>
            <th class="width-sm">주문 실 결제금액<br>(상품 실구매 금액)</th>
            <th class="width-xs">주문일</th>
            <th class="width-2xs">처리상태</th>
            <th class="width-sm">작성자</th>
            <th class="width-xs">작성일</th>
            <th class="width-xs">발급일</th>
            <th class="width-3xs">추천</th>
            <th class="width-sm">마일리지</th>
            <th class="width-2xs">승인</th>
            <th class="width-2xs">수정</th>
        </tr>
        </thead>
        <?php
        if (gd_array_is_empty($list['list']) === false) {
            foreach ($list['list'] as $val) {
                ?>
                <tr class="center">
                    <td><input name="sno[]" type="checkbox" value="<?= $val['sno'] ?>"><input name="goodsNoArry[<?= $val['sno'] ?>]" type="hidden" value="<?= $val['goodsNo'] ?>"></td>
                    <td class="font-num">
                        <?= $val['no'] ?>
                    </td>
                    <td align="left" class="js-preview" data-sno="<?= $val['sno'] ?>">
                        <a href="javascript:view(<?=$val['sno']?>)" class="js-contents-short">
                            <?php if($val['isFile'] == 'y') {?>
                                <img src="<?=PATH_ADMIN_GD_SHARE?>img/ico_bd_file.gif" />
                            <?php }?>
                            <?php if($val['isNew'] == 'y') {?>
                                <img src="<?=PATH_ADMIN_GD_SHARE?>img/ico_bd_new.gif" />
                            <?php }?>
                            <?= $val['listContents'] ?>
                        </a>
                        <?php if($val['orderChannelFl'] == 'naverpay') {?>
                        <img src="<?=\UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www()?>">
                        <?php }?>
                        <div class="plusPreview loading"></div>
                    </td>
                    <td align="center">
                        <?= $val['reviewTypeText'] ?>
                    </td>
                    <td align="center">
                        <?= $val['memoCnt'] ?>
                    </td>
                    <td align="center">
                        <?= $val['goodsPt'] ?>
                    </td>

                    <td align="center"><?= $val['orderPrice']?></td>
                    <td align="center"><?= $val['buyGoodsRegDt']?></td>
                    <td align="center"><?= $val['orderStatus']?></td>

                    <td>
                        <?php if($val['memNo'] > 0 && gd_is_provider() == false) {?>
                        <a href="javascript:void(0)" class='js-layer-crm hand' data-member-no="<?=$val['memNo']?>"><?= $val['writer'] ?></a>
                        <?php }
                        else {?>
                            <?= $val['writer'] ?>
                        <?php }?>
                    </td>
                    <td>
                        <?= $val['regDate'] ?>
                    </td>
                    <td>
                        <?= $val['mileageGiveDt'] ?>
                    </td>
                    <td>
                        <?= $val['recommend'] ?>
                    </td>

                    <td class="js-apply-milage-<?= $val['sno'] ?>">
                        <?php if($val['channel']  == 'naverpay' || $val['memNo']  == 0 ) {?>
                            <span class="text-gray">지급불가</span>
                        <?php }else if($val['mileage']  > 0) {?>
                            지급완료
                        <?php }else if($val['mileage'] == 0 && $val['mileageGiveDt'] != '0000-00-00' && empty($val['mileagePolicy']) === false) {?>
                            지급예정
                        <?php }else{?>
                            <input type="button" value="마일리지" class="btn btn-white" onclick="milageAdd(<?= $val['sno'] ?>)">
                        <?php }?>
                    </td>
                    <td class="js-apply-button-<?= $val['sno'] ?>">
                        <?php if($val['applyFl'] != 'y'){?>
                        <input type="button" value="승인" class="btn btn-white" onclick="applySet(<?= $val['sno'] ?>,<?= $val['goodsNo'] ?>)">
                        <?php }else{?>
                            승인완료
                        <?php }?>
                    </td>
                    <td>
                        <?php if($val['channel']  != 'naverpay') {?>
                            <input type="button" value="수정" class="btn btn-white" onclick="modify(<?= $val['sno'] ?>)">
                        <?php }?>
                    </td>
                </tr>

                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="15" height="50" class="no-data">검색된 정보가 없습니다.</td>
            </tr>
        <?php } ?>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 게시글</span>
            <button type="button" class="btn btn-white js-btn-delete">삭제</button>
            <button type="button" class="btn btn-white js-btn-apply" data-value="y">승인</button>
            <button type="button" class="btn btn-white js-btn-apply" data-value="n">미승인</button>
            <button type="button" class="btn btn-white js-btn-milage">마일리지 지급</button>
            <a href="../member/member_batch_mileage_list.php" >마일리지 지급 내역 보기</a>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearch" data-target-list-form="frmList" data-target-list-sno="sno" data-search-count="<?=$list['cnt']['search'];?>" data-total-count="<?=$list['cnt']['total'];?>">엑셀다운로드</button>
        </div>
    </div>
    <div class="center"><?= $list['pagination'] ?></div>
</form>
</div>
<script language="javascript">
    var mileageFlConfig = '<?=$mileageFlConfig;?>';

    function modify(sno) {
        location.href = 'plus_review_register.php?sno=' + sno+"&mode=modify&<?=Request::getQueryString()?>";
    }

    function view(sno) {
        location.href = 'plus_review_view.php?sno=' + sno+"&<?=Request::getQueryString()?>";
    }

    function milageAdd(sno) {

        var title = "마일리지 지급";
        $.get('../board/plus_review_milage.php',{ sno: sno }, function(data){
            if (data.result && data.result === 'fail') {
                alert(data.message);
                return false;
            }

            data = '<div id="viewInfoForm">'+data+'</div>';

            var layerForm = data;

            BootstrapDialog.show({
                title:title,
                size: get_layer_size('wide-sm'),
                message: $(layerForm),
                closable: true
            });
        });
    }

    function applySet(sno,goodsNo) {
        $.post('../board/plus_review_ps.php', {'mode': 'applySet', 'sno': sno, 'goodsNo': goodsNo}, function (data) {
            if (data.result == 'ok') {
                {
                    alert(data.msg);
                    $('.js-apply-button-'+sno).text('승인완료');
                }
            }
        });
    }

    $(document).ready(function () {
        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearch').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearch').submit();
        });

        $('button.js-btn-delete').click(function () {
            var obj = $('input[name*="sno["]:checkbox:checked');
            var chkCnt = obj.length;
            if (chkCnt == 0) {
                alert('선택된 게시글이 없습니다.');
                return;
            }

            dialog_confirm('선택한 게시글을 삭제하시겠습니까?\n\r영구 삭제되어 복원 불가능합니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete');
                    $('#frmList').submit();
                }
            });
        });

        $('button.js-btn-apply').click(function () {
            var obj = $('input[name*="sno["]:checkbox:checked');
            var chkCnt = obj.length;
            var chkCnt2 = 0;
            var val = $(this).data('value');
            if (chkCnt == 0) {
                alert('선택된 게시글이 없습니다.');
                return;
            }
            if(val == 'y'){
                $.each(obj, function (index, item) {
                    if( $('.js-apply-button-' + item.value).find('input[type=\'button\']').length == 0 ){
                        chkCnt2++;
                    };
                });
                if (chkCnt2 != 0) {
                    alert('승인완료된 게시글이 존재합니다.');
                    return;
                }
            }else{
                $.each(obj, function (index, item) {
                    if( $('.js-apply-button-' + item.value).find('input[type=\'button\']').length == 1 ){
                        chkCnt2++;
                    };
                });
                if (chkCnt2 != 0) {
                    alert('마승인된 게시글이 존재합니다.');
                    return;
                }
            }
            if(val == 'y') {
                dialog_confirm('선택한 ' + chkCnt + '개의 게시글을 승인처리 하시겠습니까?.', function (result) {
                    if (result) {
                        $('#frmList input[name=\'mode\']').val('applySet');
                        $('#frmList').submit();
                    }
                });
            }
            else {  //미승인
                dialog_confirm('선택한 ' + chkCnt + '개의 게시글을 미승인처리 하시겠습니까?.', function (result) {
                    if (result) {
                        $('#frmList input[name=\'mode\']').val('notApplySet');
                        $('#frmList').submit();
                    }
                });
            }

        });

        $('button.js-btn-milage').click(function () {
            var obj = $('input[name*="sno["]:checkbox:checked');
            var chkCnt = obj.length;
            var chkCnt2 = 0;
            if (mileageFlConfig !== 'y') {
                alert("마일리지 지급 기능을 사용하시려면, 플러스리뷰 게시판 설정과 마일리지 기본 설정 메뉴에서 마일리지 사용유무 설정을 '사용함'으로 설정해주세요.");
                return;
            }
            if (chkCnt == 0) {
                alert('선택된 게시글이 없습니다.');
                return;
            }
            if (chkCnt > 100) {
                alert('마일리지 일괄 지급은 최대 100개까지 가능합니다.');
                return;
            }
            var chkArry = [];
            $.each(obj, function (index, item) {
                if( $('.js-apply-milage-' + item.value).find('input[type=\'button\']').length == 0 ){
                    chkCnt2++;
                } else {
                    chkArry.push(item.value);
                }
            });
            if (chkCnt == chkCnt2) {
                alert('마일리지 지급 가능한 게시글이 없습니다.');
                return;
            }

            if (chkCnt2 === 0) {
                milageAdd(chkArry);
            } else {
                BootstrapDialog.show({
                    title: '확인',
                    message: '마일리지 지급완료/지급예정/지급불가 게시글은 제외됩니다.',
                    buttons: [{
                        label: '확인',
                        cssClass: 'btn-black',
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (dialog) {
                            dialog.close();
                            milageAdd(chkArry);
                        }
                    }]
                });
            }
        });

        $('.js-preview').hover(function () {

            var previewLayer = $(this).find(".plusPreview");
            var scrollOffset = $('html').scrollTop();
            var winHeight = $('html').height() + scrollOffset;
            var thisOffset = $(this).offset().top + previewLayer.outerHeight();
            var hoverEleWidth = $(this).width();
            var maxHeight = $('#footer').offset().top;

            $(this).addClass('bgColor');

            if(thisOffset > winHeight){
                if(maxHeight > winHeight){
                    var setTopPosition = (thisOffset - winHeight) * -1 ;
                    previewLayer.css('top',setTopPosition - 10).css('left',hoverEleWidth-10).show();

                }else{
                    thisOffset = $(this).offset().top + previewLayer.outerHeight();
                    previewLayer.css('top',maxHeight-thisOffset).css('left',hoverEleWidth-10).show();
                }
            }else{
                previewLayer.css('top',-20).css('left',hoverEleWidth-10).show();
            }

            var self = this;
            if(previewLayer.html() == '') {
                $.get('../board/plus_preview.php', {sno: $(this).data('sno')}, function (data) {
                    var layerForm = '<div id="viewInfoForm">' + data + '</div>';
                    $(self).find(".plusPreview").empty().append(layerForm).removeClass('loading');
                });
            }

        },function(){
            $(this).find(".plusPreview").hide();
            $(this).removeClass('bgColor');

        });

    });

</script>
