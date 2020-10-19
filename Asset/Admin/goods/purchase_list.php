<script type="text/javascript">
    <!--

    $(document).ready(function () {

        // 삭제
        $('button.js-check-delete').click(function () {
            var chkCnt = $('input[name*="purchaseNo["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 매입처가 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개의 매입처를 정말로 삭제하시겠습니까?<br/>삭제시 정보는 복구되지 않으며 상품/추가상품/공급사 등에 등록된 정보도 삭제됩니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete_state');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './purchase_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 등록
        $('.js-register').click(function () {
            location.href = './purchase_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });
    });

    //-->
</script>

<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button"  value="매입처 등록" class="btn btn-red-line js-register" />

    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">

    <div class="table-title">
        매입처 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td colspan="3"><div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td  colspan="3"><div class="form-inline">
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
                <th>사용상태</th>
                <td>
                    <label  class="radio-inline"><input type="radio" name="useFl" value="" <?=gd_isset($checked['useFl']['all']);?> />전체</label>
                    <label  class="radio-inline"><input type="radio" name="useFl" value="y" <?=gd_isset($checked['useFl']['y']);?> />사용함</label>
                    <label  class="radio-inline"><input type="radio" name="useFl" value="n" <?=gd_isset($checked['useFl']['n']);?> />사용안함</label>
                </td>
                <th>거래상태</th>
                <td>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="" <?=gd_isset($checked['businessFl']['all']);?> />전체</label>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="y" <?=gd_isset($checked['businessFl']['y']);?> />거래중</label>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="n" <?=gd_isset($checked['businessFl']['n']);?> />거래중지</label>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="x" <?=gd_isset($checked['businessFl']['x']);?> />거래해지</label>
                </td>
            </tr>
        </table>
    </div>


    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?=number_format($page->recode['amount']);?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>

</form>


<div>
    <form id="frmList" action="" method="get" target="ifrmProcess" >
        <input type="hidden" name="mode" value="">
        <table class="table table-rows">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox"  class="js-checkall" data-target-name="purchaseNo"/></th>
                <th class="width5p">번호</th>
                <th class="width7p">매입처 코드</th>
                <th class="width7p center">매입처 자체코드</th>
                <th class="width15p center">매입처명</th>
                <th class="width5p center">사용상태</th>
                <th class="width5p center">거래상태</th>
                <th class="width15p center">상품유형</th>
                <th class="width10p center">등록일/수정일</th>
                <th class="width5p center">수정</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                $businessFl    = array('y' => '거래중', 'n' => '거래중지', 'x' => '거래해지');
                $useFl        = array('y' => '사용', 'n' => '사용안함');
                foreach ($data as $key => $val) {
                    $modDt = gd_date_format('Y-m-d', $val['modDt']);
                    ?>
                    <tr>
                        <td class="center"><input type="checkbox" name="purchaseNo[<?=$val['purchaseNo']; ?>]" value="<?=$val['purchaseNo']; ?>"/></td>
                        <td class="center number"><?=number_format($page->idx--);?></td>
                        <td class="center"><?=$val['purchaseNo'];?></td>
                        <td class="center"><?=$val['purchaseCd'];?></td>
                        <td ><?=$val['purchaseNm'];?></td>
                        <td class="center"><?=$useFl[$val['useFl']];?></td>
                        <td class="center"><?=$businessFl[$val['businessFl']];?></td>
                        <td class="center"><?=$val['category'];?></td>
                        <td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']);?><?php if($modDt !='-') {
                                echo "</br>".$modDt;
                            } ?>
                        </td>
                        <td class="center">
                            <a href="./purchase_register.php?purchaseNo=<?=$val['purchaseNo'];?>" class="btn btn-white btn-xs"> 수정</a>
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
                <button type="button" class="btn btn-white js-check-delete">선택 삭제</button>
            </div>
        </div>


    </form>
    <div class="center"><?=$page->getPage();?></div>
</div>
