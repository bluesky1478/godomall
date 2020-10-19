<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#selectedAll").bind('click', function () {
            $("input[name='chk[]']").prop("checked", $("#selectedAll").prop("checked"));
        });

        $('input[name=mallSno]').bind('click',function(){
            var mallSno = $(this).val();
            location.href="faq_list.php?mallSno="+mallSno;
//            $('#frmSearchBase').submit();
        })

        // 등록
        $('.js-btn-register').click(function () {
            location.href = 'faq_register.php?mallSno=<?=$search['mallSno']?>';
        });

        $("#frmList").validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                dialog_confirm('선택한 글을 삭제하시겠습니까?\n\r영구 삭제되어 복원 불가능합니다.', function (result) {
                    if (result) {
                        form.submit();
                    }
                });
            },
            rules: {
                "chk[]": 'required'
            },
            messages: {
                "chk[]": '삭제할 FAQ를 선택하세요.'
            }
        });
    });
    //-->
</script>


<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?>
        <small>FAQ를 수정하고 관리합니다.</small>
    </h3>
    <input type="button" value="등록" class="btn btn-red js-btn-register"/>
</div>
<div class="table-title gd-help-manual">
    FAQ 검색
</div>
<form id="frmSearchBase" method="get" class="js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <?php if($gGlobal['isUse']){?>
                <tr>
                    <th>상점</th>
                    <td colspan="3">
                        <?php foreach($gGlobal['useMallList'] as $val) {
                            ?>
                            <label class="radio-inline"><input type="radio" name="mallSno" value="<?=$val['sno']?>" <?=$checked['mallSno'][$val['sno']]?>>
                                <span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span>
                                <?=$val['mallName']?></label>
                        <?php }?>
                    </td>
                </tr>
            <?php }?>
            <tr>
                <th class="width-sm">카테고리</th>
                <td>
                    <?php
                    echo gd_select_box('category', 'category', gd_code('03001',$search['mallSno']), null, gd_isset($search['category']), '=전체='); ?>
                </td>
                <th class="width-sm">유형</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="isBest"
                                                       value="" <?=$checked['isBest'][''] ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="isBest"
                                                       value="n" <?=$checked['isBest']['n'] ?>/>일반</label>
                    <label class="radio-inline"><input type="radio" name="isBest"
                                                       value="y" <?=$checked['isBest']['y'] ?>/>베스트</label>
                </td>
            </tr>
            <tr>
                <th>등록일</th>
                <td colspan="3" class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder="수기입력 가능"
                               value="<?=$search['regDt'][0] ?>"/>
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder="수기입력 가능"
                               value="<?=$search['regDt'][1] ?>"/>
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                    </div>

                </td>
            </tr>
            <tr>
                <th>검색</th>
                <td class="form-inline" colspan="3">
                    <?=gd_select_box('searchKey', 'searchKey', array('all' => '=통합검색=', 'subject' => '제목', 'contents' => '내용', 'answer' => '답변'), null, gd_isset($search['searchKey']), null); ?>
                    <input type="text" name="searchWord" value="<?=gd_isset($search['searchWord']); ?>"
                           class="form-control"/>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>
</form>

<div class="table-header">
    <div class="pull-left">
        검색 <strong><?=$pageInfo['searchCount'] ?></strong> /
        전체<strong><?=$pageInfo['totalCount'] ?></strong></strong>
    </div>
</div>
<form id="frmList" action="./faq_ps.php" method="post">
    <input type="hidden" name="mode" value="delete">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-3xs"><input type="checkbox" id="selectedAll"/></th>
            <th class="width-xs">번호</th>
            <?php if($gGlobal['isUse']){?>
                <th class="width-xs">상점 구분</th>
            <?php }?>
            <th class="width-md"/>카테고리</th>
            <th>제목</th>
            <th class="width-sm">유형</th>
            <th class="width-sm">등록일</th>
            <th class="width-sm">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $i = 0;
            foreach ($data as $val) {
                $i++;
                ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="chk[]" value="<?=$val['sno']; ?>"/></td>
                    <td><?=$i ?></td>
                    <?php if($gGlobal['isUse']){?>
                        <td>
                            <span class="flag flag-16 flag-<?= gd_isset($gGlobal['mallList'][$val['mallSno']]['domainFl'], 'kr'); ?>"></span><?= gd_isset($gGlobal['mallList'][$val['mallSno']]['mallName'], '기준몰'); ?>
                        </td>
                    <?php }?>
                    <td><?=gd_code_item($val['category'],$val['mallSno']); ?></td>
                    <td align="left">
                        <a href="./faq_register.php?sno=<?=$val['sno']; ?>"><?=$val['subject']; ?></a>
                    </td>
                    <td>
                        <?=($val['isBest'] == 'y') ? '베스트' : '일반'; ?>
                    </td>
                    <td><?=gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    <td><a href="./faq_register.php?sno=<?=$val['sno']; ?>" class="btn btn-white btn-sm">수정</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="7">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 FAQ</span>
            <button type="submit" class="btn btn-white js-btn-delete" />삭제</button>
        </div>
    </div>

</form>


<div class="one-line"></div>
