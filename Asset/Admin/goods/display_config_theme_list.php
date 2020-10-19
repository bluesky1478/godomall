<script type="text/javascript">
    <!--
    $(document).ready(function(){
        // 삭제
        $('button.checkDelete').click(function() {
            var chkCnt = $('input[name*="themeCd["]:checkbox:checked').length;
            if (chkCnt == 0 ) {
                alert('선택된 테마가 없습니다.');
                return;
            }

            dialog_confirm('선택한 '+chkCnt+'개 테마를 정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('theme_delete');
                    $('#frmList').attr('method','post');
                    $('#frmList').attr('action','./display_config_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 등록
        $('#checkRegister').click(function(){
            location.href = './display_config_theme_register.php';
        });

        $('select[name=\'pageNum\']').change(function(){
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function(){
            $('#frmSearchBase').submit();
        });

    });
    //-->
</script>

<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="테마 등록" class="btn btn-red-line" />
    </div>
</div>

<div class="table-title gd-help-manual">
    테마 검색
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?=$search['detailSearch'];?>" />
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm" /><col /><col class="width-sm" /><col /></colgroup>
            <tbody>
            <tr>
                <th >테마명</th>
                <td colspan="3"><div class="form-inline">
                        <input type="text" name="themeNm" value="<?=$search['themeNm'];?>" class="form-control" />
                    </div>
                </td>
            </tr>
            <tr>
                <th >기간검색</th>
                <td colspan="3"> <div class="form-inline">
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
                        <?=gd_search_date($search['searchPeriod'], 'searchDate', true)?>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail" style="display: none;">
            <tr>
                <th >테마분류</th>
                <td class="contents" colspan="3">
                    <label title=""  class="radio-inline" ><input type="radio" name="themeCate" value="all" <?=gd_isset($checked['themeCate']['all']);?>  />전체</label>
                    <?php foreach($themeCategory as $k => $v) { ?>
                        <label  class="radio-inline"  title=""><input type="radio" name="themeCate" value="<?=$k?>" <?=gd_isset($checked['themeCate'][$k]);?>  /><?=$v?></label>
                    <?php  } ?>
                </td>
            </tr>
            <tr>
                <th>쇼핑몰 유형</th>
                <td>
                    <label  class="radio-inline" ><input type="radio" name="mobileFl"
                                  value="all" <?=gd_isset($checked['mobileFl']['all']); ?>/>전체</label>
                    <label  class="radio-inline" ><input type="radio" name="mobileFl"
                                  value="n" <?=gd_isset($checked['mobileFl']['n']); ?>/>PC쇼핑몰</label>
                    <label  class="radio-inline" ><input type="radio" name="mobileFl"
                                  value="y" <?=gd_isset($checked['mobileFl']['y']); ?>/>모바일쇼핑몰</label>
                </td>
                <th >리스트이미지</th>
                <td class="contents"><div class="form-inline">
                        <?php
                        foreach ($confImage as $key => $val) {
                            if($key == 'imageType') {
                                continue;
                            }

                            $arrImage[$key]    = $val['text'].' - '.$val['size1'][0].' pixel';
                            if($confImage['imageType'] == 'fixed') {
                                $arrImage[$key] .= ' / 세로 ' . $val['size1'][1] . ' pixel';
                            }
                        }
                        echo gd_select_box('imageCd','imageCd',$arrImage,null,$search['imageCd'],'= 선택하세요 =');
                        ?></div>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 <span>펼침</span></button>
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

<form id="frmList" action="" method="get"  target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" id="allCheck" value="y"  class="js-checkall"  data-target-name="themeCd" /></th>
            <th class="width5p">번호</th>
            <th class="width10p center">쇼핑몰 유형</th>
            <th class="width10p">테마코드</th>
            <th class="width10p">테마분류</th>
            <th class="width15p">테마명</th>
            <th class="width15p">리스트 이미지 사이즈</th>
            <th class="width10p">리스트 상품 노출개수</th>
            <th class="width10p">적용개수</th>
            <th class="width10p">등록일</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {

            foreach ($data as $key => $val) {
                ?>

                <tr>
                    <td class="center"><input type="checkbox" name="themeCd[<?=$val['themeCd'];?>]" value="<?=$val['themeCd'];?>"  <?php if($val['useCnt'] > 0 || $val['deleteFl'] =='n') { ?> disabled='true' <?php } ?>/></td>
                    <td class="center number"><?=number_format($page->idx--);?></td>
                    <td  class="center"><?=$val['mobileFl'] =='n' ? 'PC쇼핑몰' : '모바일쇼핑몰' ?></td>
                    <td class="center"><?=$val['themeCd'];?></td>
                    <td class="center"><?=$themeCategory[$val['themeCate']];?></td>
                    <td class="center hand" onclick="show_popup('./display_config_theme_register.php?popupMode=yes&themeCd=<?=$val['themeCd'];?>')" class="hand"><?=$val['themeNm'];?></td>
                    <td class="center"><?=$useImage[$val['imageCd']];?></td>
                    <td class="center"><?=$val['lineCnt'];?> X <?=$val['rowCnt'];?> </td>
                    <td class="center"><?=$val['useCnt'];?></td>
                    <td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']);?><?php if( $val['modDt']) { echo "<br/>".gd_date_format('Y-m-d', $val['modDt']);} ?></td>
                    <td class="center padlr10"><a href="./display_config_theme_register.php?themeCd=<?=$val['themeCd'];?>" class="btn btn-white btn-xs">수정</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="11">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn  btn-white checkDelete">선택 삭제</button>
        </div>
        <div class="pull-right">

        </div>
    </div>



</form>

<div class="center"><?=$page->getPage();?></div>
