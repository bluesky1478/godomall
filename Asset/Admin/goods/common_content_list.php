<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="공통정보 등록" class="btn btn-red-line" onclick="location.href = './common_content_regist.php';" />
    </div>
</div>

<form id="frmSearchContent" name="frmSearchContent" method="get">
    <div class="table-title ">
        공통정보 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm" /><col /><col class="width-sm" /><col /></colgroup>
            <tbody>
            <tr>
                <th>공통정보 제목</th>
                <td  colspan="3">
                    <input type="text" name="title" value="<?=$search['title'];?>" class="form-control width30p" />
                </td>
            </tr>
            <tr>
                <th >기간검색</th>
                <td  colspan="3"> <div class="form-inline">
                    <select name="searchDateFl" class="form-control">
                        <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                        <option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                    </select>

                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" >
                        <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                        </span>
                    </div>

                    ~  <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" >
                        <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                        </span>
                    </div>
                    <?=gd_search_date($search['searchPeriod'])?>
                </td>
            </tr>
            <tr>
                <th>노출기간</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="dateFl" value="" <?=gd_isset($checked['dateFl']['']);?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="dateFl" value="n" <?=gd_isset($checked['dateFl']['n']);?> />제한없음</label>
                    <label class="radio-inline"><input type="radio" name="dateFl" value="y" <?=gd_isset($checked['dateFl']['y']);?> />기간제한용</label>
                </td>
                <th>진행상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="stateFl" value="" <?=gd_isset($checked['stateFl']['']);?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="stateFl" value="s" <?=gd_isset($checked['stateFl']['s']);?> />대기</label>
                    <label class="radio-inline"><input type="radio" name="stateFl" value="i" <?=gd_isset($checked['stateFl']['i']);?> />진행중</label>
                    <label class="radio-inline"><input type="radio" name="stateFl" value="e" <?=gd_isset($checked['stateFl']['e']);?> />종료</label>
                </td>
            </tr>
            <tr>
                <th>노출상태</th>
                <td colspan="3">
                    <label class="radio-inline"><input type="radio" name="useFl" value="" <?=gd_isset($checked['useFl']['']);?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="useFl" value="y" <?=gd_isset($checked['useFl']['y']);?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="useFl" value="n" <?=gd_isset($checked['useFl']['n']);?> />노출안함</label>
                </td>
            </tr>
            <tr>
                <th>상품조건</th>
                <td colspan="3">
                    <?php foreach ($targetFl as $k => $v) { ?>
                        <label class="radio-inline"><input type="radio" name="targetFl" value="<?=$k; ?>" <?=gd_isset($checked['targetFl'][$k]);?> /><?=$v; ?></label>
                    <?php } ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            공통정보 리스트 (검색 <strong><?=number_format($page->recode['total']);?></strong>개 / 전체 <strong><?=number_format($page->recode['amount']);?></strong>개)
        </div>
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<form id="frmList" action=""  target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" id="allCheck" value="y" /></th>
            <th class="width5p">번호</th>
            <th>공통정보 제목</th>
            <th class="width15p">노출기간</th>
            <th class="width5p">진행상태</th>
            <th class="width10p">노출상태</th>
            <th class="width10p">상품조건</th>
            <th class="width10p">예외조건</th>
            <th class="width10p">등록일/수정일</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                $viewDate = '제한없음';
                $status = '진행중';
                if ($val['commonStatusFl'] == 'y') {
                    $viewDate = substr($val['commonStartDt'], 0, 16) . '<br />~' . substr($val['commonEndDt'], 0, 16);
                    if ($val['commonStartDt'] > date('Y-m-d H:i')) {
                        $val['commonUseFl'] = 'n';
                        $status = '<span style="color:#0070c0;">대기</span>';
                    } elseif ($val['commonEndDt'] < date('Y-m-d H:i')) {
                        $val['commonUseFl'] = 'n';
                        $status = '<span style="color:#ff0000;">종료</span>';
                    }
                }
                ?>
                <tr>
                    <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>"/></td>
                    <td class="center number"><?=number_format($page->idx--);?></td>
                    <td><?=$val['commonTitle']; ?></td>
                    <td class="center"><?=$viewDate; ?></td>
                    <td class="center"><?=$status;?></td>
                    <td class="center"><?=$useFl[$val['commonUseFl']];?></td>
                    <td class="center"><?php if ($val['commonTargetFl'] != 'all') {?><input type="button" value="<?=$targetFl[$val['commonTargetFl']];?>" onclick="layer_info_view('commonCd', 'info', '<?=$val['commonCd'];?>','<?=$val['commonTargetFl'];?>');" class="btn btn-black btn-xs" /><?php } else { ?><?=$targetFl[$val['commonTargetFl']];?><?php } ?></td>
                    <td class="center">
                        <?php
                        foreach ($targetFl as $k => $v) {
                            if (empty($val['commonEx' . ucwords($k)]) === false) {
                                echo '<div><input type="button" value="'.$v.'" onclick="layer_info_view(\'commonEx' . ucwords($k) . '\', \'info\', \''.$val['commonEx' . ucwords($k)].'\', \'' . $k . '\');"  class=" btn-black btn-xs mgb5" style="border:0px;" /></div>';
                            }
                        }
                        ?>
                    </td>
                    <td class="center">
                        <?php
                            echo gd_date_format('Y-m-d', $val['regDt']);
                            if ($val['modDt'] != '0000-00-00 00:00:00') {
                                echo '<br />' . gd_date_format('Y-m-d', $val['modDt']);
                            }
                        ?>
                    </td>
                    <td class="center padlr10">
                        <a href="./common_content_regist.php?sno=<?=$val['sno']; ?>" class="btn btn-white btn-xs">수정</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="10">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>

        </tbody>
    </table>


    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white checkDelete">선택 삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchContent" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-target-list-form="frmList" data-target-list-sno="sno">엑셀다운로드</button>
        </div>
    </div>
</form>

<div class="center"><?=$page->getPage();?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $('select[name="sort"]').change(function(){
            $('#frmSearchContent').submit();
        });

        $('select[name="pageNum"]').change(function () {
            $('#frmSearchContent').submit();
        });

        $('#allCheck').click(function(){
            var checked = $(this).prop('checked');
            $('#frmList table tbody').find('input[type="checkbox"]').prop('checked', checked);
        });

        $('.checkDelete').click(function(){
            var len = $('#frmList table tbody').find('input[type="checkbox"]:checked').length;
            if (len <= 0) {
                alert('선택된 공통정보가 없습니다.');
                return;
            } else {
                dialog_confirm('선택한 ' + len + '개의 공통정보를 정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                    if (result) {
                        $('#frmList input[name="mode"]').val('delete');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('action', './common_content_ps.php');
                        $('#frmList').submit();
                    }
                });
            }
        });
    });

    function layer_info_view(modeStr, typeStr, sno, modeType)
    {
        var loadChk	= $('#viewInfoForm').length;
        var title =  "";
        var mode = "";

        if (modeStr == 'commonCd') {
            title += '상품조건 - ';
        } else {
            title += '예외조건 - ';
        }

        if(typeStr =='info') {
            if (modeType == 'goods') {
                title += '특정 상품';
            } else if (modeType == 'category') {
                title += '특정 카테고리';
            } else if (modeType == 'brand') {
                title += '특정 브랜드';
            } else if (modeType == 'scm') {
                title += '특정 공급사';
            }
        }

        $.post('../share/layer_terms_view.php',{ mode : modeType, sno : sno }, function(data){
            if (loadChk == 0) {
                data = '<div id="viewInfoForm">'+data+'</div>';
            }
            var layerForm = data;

            BootstrapDialog.show({
                title:title,
                message: $(layerForm),
                closable: true
            });
        });
    }
    //-->
</script>
