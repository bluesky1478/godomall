<?php
//펼침,닫힘 정보
$toggle = gd_policy('display.toggle');
$SessScmNo = Session::get('manager.scmNo');
?>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        <?php if(gd_isset($data['quick']['quickFl']) =='y') {?>display_switch('tr_quick_show');<?php } ?>
        <?php if(gd_isset($data['keyword']['keywordFl']) =='y') {?>display_switch('tr_keyword_show');<?php } ?>

        initDepthToggle(<?=$SessScmNo?>);//4depth 메뉴 보임안보임처리
    });

    $(document).ready(function () {
        $("#frmGoods").validate({
            submitHandler: function (form) {
                var elName, elNameVal;
                for (var i=0;i < form.elements.length;i++)
                {
                    elName = form.elements[i].name;
                    elNameVal = form.elements[i].value;
                    //키원드
                    if (elName.indexOf("keyword[pr_text]") > -1 && elNameVal.length > 30) {
                        alert("홍보문구는 30자를 넘을 수 없습니다.");
                        return false;
                    }

                    //인기검색어
                    if (elName.indexOf("hitKeyword[keyword]") > -1 && elNameVal.length > 30) {
                        alert("인기 검색어는 30자를 넘을 수 없습니다.");
                        return false;
                    }
                }

                form.submit();
            }

        });
    });


    /**
     * 상품검색 키워드 추가
     */
    function add_info()
    {
        var fieldID		= 'addInfo';
        var fieldNoChk	= $('#'+fieldID).find('tr:last').get(0).id.replace(fieldID,'');
        if (fieldNoChk == '') {
            var fieldNoChk	= 0;
            $("#"+fieldID+" thead").show();
        }

        var fieldNo		= parseInt(fieldNoChk) + 1;
        if(fieldNo < 10) {


            var addHtml		= '';
            addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
            addHtml	+= '<td class="center"><input type="text" name="keyword[pr_text]['+fieldNo+']" value="" class="form-control width_lLarge" /></td>';
            addHtml	+= '<td class="center"><input type="text" name="keyword[link_url]['+fieldNo+']" value="" class="form-control" /></td>';
            addHtml	+= '<td class="center"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\''+fieldID+'\',\''+fieldNo+'\');" value="삭제" /></td>';
            addHtml	+= '</tr>';
            $('#'+fieldID).append(addHtml);
        } else {
            alert('키워드는 10개까지 등록가능합니다');
        }

    }

    //인기검색어
    function add_hitinfo(){
        var fieldID		= 'addHitInfo';
        var fieldNoChk	= $('#'+fieldID).find('tr:last').get(0).id.replace(fieldID,'');
        if (fieldNoChk == '') {
            var fieldNoChk	= 0;
            $("#"+fieldID+" thead").show();
        }
        var fieldNo		= parseInt(fieldNoChk) + 1;
        var addHtml		= '';
        addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
        addHtml	+= '<td><input type="text" name="hitKeyword[keyword]['+fieldNo+']" value="" class="form-control width80p nobr" /> ';
        addHtml	+= '<input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="field_remove(\''+fieldID+'\',\''+fieldNo+'\');" value="삭제" /></td>';
        addHtml	+= '</tr>';
        $('#'+fieldID).append(addHtml);
    }

    function display_switch(prefix)
    {
        if($('.'+prefix).is(':hidden')) $('.'+prefix).show();
        else $('.'+prefix).hide();


    }

    function field_remove(fieldID,fieldNo) {
        $("#"+fieldID+fieldNo).remove();
        if($("tr[id*='"+fieldID+"']").length  == 0)  $("#"+fieldID+" thead").hide();
    }
    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./search_ps.php" method="post" target="ifrmProcess" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="search_settings" />
    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>
    <div class="table-title gd-help-manual">
        검색창 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="searchInfo"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-searchInfo" value="<?=$toggle['searchInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-searchInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-searchInfo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>통합검색 조건 선택</th>
                <td class="input_area">
                    <input type="hidden" name="terms[settings][]" value="goodsNm">
                    <?php foreach($data['set']['terms'] as $k => $v) { ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="terms[settings][]" value="<?=$k?>" <?=gd_isset($checked['terms']['settings'][$k]); ?> <?php if($k =='goodsNm')  { echo "disabled='true'"; } ?> />
                            <?=$v?>
                        </label>
                    <?php } ?>
                    <div class="notice-info">검색조건이 많을 수록 쇼핑몰 내 검색속도가 느려질 수 있습니다.</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        상품 검색 키워드 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="keywordSearch"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-keywordSearch" value="<?=$toggle['keywordSearch_'.$SessScmNo]?>">
    <div id="depth-toggle-line-keywordSearch" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-keywordSearch">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th >사용상태</th>
                <td colspan="2">
                    <label  class="radio-inline"><input type="radio" name="keyword[keywordFl]" value="y" <?=gd_isset($checked['keyword']['keywordFl']['y']);?> onclick="display_switch('tr_keyword_show')"/>사용함</label>
                    <label  class="radio-inline"><input type="radio" name="keyword[keywordFl]" value="n" <?=gd_isset($checked['keyword']['keywordFl']['n']);?> onclick="display_switch('tr_keyword_show')"/>사용안함</label>
                </td>
            </tr>
            <tr class="tr_keyword_show" style="display:none">
                <th >키워드 노출 방식</th>
                <td colspan="2">
                    <label  class="radio-inline"><input type="radio" name="keyword[display]" value="random" checked>랜덤</label>
                </td>
            </tr>
            <tr class="tr_keyword_show" style="display:none">
                <th >키워드 등록

                </th>
                <td  >
                    <div class="notice-info">문구를 등록하고 문구를 검색했을 때 연결되는 쇼핑몰 페이지 링크정보를 입력해 주세요.</div>
                    <input type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_info();" value="키워드 추가" />
                    <table id="addInfo" class="table table-rows mgt5" style="width:80%;">
                        <thead <?php if(!gd_isset($data['keyword']['pr_text'])) { echo 'style="display:none"'; } ?>>
                        <tr>
                            <th class='input_title'>홍보문구</th>
                            <th class='input_title' colspan="2">링크페이지</th>
                        </tr>
                        </thead>
                        <?php if(gd_isset($data['keyword']['pr_text'])) { ?>
                            <?php
                            $i = 0;
                            foreach($data['keyword']['pr_text'] as $k => $v) { ?>
                                <tr id="addInfo<?=$i?>">
                                    <td><input type="text" name="keyword[pr_text][<?=$i?>]"  class="form-control width_lLarge" value="<?=$v?>"></td>
                                    <td class="center"><input type="text" name="keyword[link_url][<?=$i?>]" class="form-control" value="<?=$data['keyword']['link_url'][$k]?>"></td>
                                    <td class="center"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove('addInfo','<?=$i?>');" value="삭제" /></td>
                                </tr>
                                <?php
                                $i++;
                            }
                        } ?>
                        </tbody>
                    </table>

                </td>
                <td class="width50p search-keyword-box">
                    <div>[쇼핑몰 예시화면]</div>
                    <img src="<?=PATH_ADMIN_GD_SHARE?>img/keyword-search1.jpg">
                </td>
            </tr>
        </table>
    </div>
    <div class="keyword-search-info notice-info">쇼핑몰 검색어 입력란에 검색 키워드를 노출하여 고객들에게 여러 가지 이벤트 프로모션을 노출 할 수 있습니다.</div>

    <div class="table-title gd-help-manual">
        최근 검색어 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="recentSearch"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-recentSearch" value="<?=$toggle['recentSearch_'.$SessScmNo]?>">
    <div id="depth-toggle-line-recentSearch" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-recentSearch">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-3xl"/>
                <col class="width50p"/>
            </colgroup>
            <tr>
                <th>PC쇼핑몰<br />노출개수</th>
                <td>
                    <select name="recent[pcCount]" class="form-control recent-select">
                        <?php for($i=10 ; $i>=0 ; $i--) { ?>
                            <option value="<?=$i?>" <?=gd_isset($selected['recentKeyword']['pcCount'][$i]); ?>><?=$i?></option>
                        <?php } ?>
                    </select>
                </td>
                <td rowspan="2" class="search-recent-box">
                    <div>[쇼핑몰 예시화면]</div>
                    <img src="<?=PATH_ADMIN_GD_SHARE?>img/keyword-search2.jpg">
                </td>
            </tr>
            <tr>
                <th>모바일쇼핑몰<br />노출개수</th>
                <td>
                    <select name="recent[mobileCount]" class="form-control recent-select">
                        <?php for($i=10 ; $i>=0 ; $i--) { ?>
                            <option value="<?=$i?>" <?=gd_isset($selected['recentKeyword']['mobileCount'][$i]); ?>><?=$i?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        </table>
        <div class="keyword-search-info notice-info">쇼핑몰 내 최근 검색했던 단어를 빠르고 편리하게 재검색 할 수 있도록 최근검색어를 제공합니다.</div>
    </div>

    <div class="table-title gd-help-manual">
        인기 검색어 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="bestSearch"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-bestSearch" value="<?=$toggle['bestSearch_'.$SessScmNo]?>">
    <div id="depth-toggle-line-bestSearch" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-bestSearch">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-3xl"/>
                <col class="width50p"/>
            </colgroup>
            <tr>
                <th >인기 검색어 등록</th>
                <td  >
                    <input type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_hitinfo();" value="검색어 추가" />
                    <table id="addHitInfo"  class="table table-rows mgt5" style="width:80%;">
                        <thead <?php if(!gd_isset($data['hitKeyword']['keyword'])) { echo 'style="display:none"'; } ?>>
                        <tr >
                            <th>검색어</th>
                        </tr>
                        </thead>
                        <?php if(gd_isset($data['hitKeyword']['keyword'])) { ?>
                            <?php foreach($data['hitKeyword']['keyword'] as $k => $v) { ?>
                                <tr id="addHitInfo<?=$k+1?>">
                                    <td>
                                        <input type="text" name="hitKeyword[keyword][<?=$k+1?>]"  class="form-control width80p nobr" value="<?=$v?>">
                                        <input type="button" onclick="field_remove('addHitInfo','<?=$k+1?>');" class="btn btn-sm btn-white btn-icon-minus" value="삭제" />
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                        </tbody>
                    </table>
                </td>
                <td class="search-best-box">
                    <div>[쇼핑몰 예시화면]</div>
                    <img src="<?=PATH_ADMIN_GD_SHARE?>img/keyword-search3.jpg">
                </td>
            </tr>
        </table>
        <div class="notice-info">검색 페이지 내 인기 검색어를 노출하여 주력상품, 이벤트상품 등을 검색할 수 있도록 유도합니다.</div>
        <div class="keyword-search-info notice-info">다른 페이지에서의 인기 검색어 노출은, 해당 디자인 수정페이지에서 {=dataHitKeyword()} 치환코드 예제를 활용하여 적용하시기 바랍니다.</div>
    </div>

    <div class="table-title gd-help-manual">
        Quick 검색 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="quickSearch"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-quickSearch" value="<?=$toggle['quickSearch_'.$SessScmNo]?>">
    <div id="depth-toggle-line-quickSearch" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-quickSearch">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th >사용상태</th>
                <td  >
                    <label  class="radio-inline"><input type="radio" name="quick[quickFl]" value="y" <?=gd_isset($checked['quick']['quickFl']['y']);?> onclick="display_switch('tr_quick_show')"/>사용함</label>
                    <label  class="radio-inline"><input type="radio" name="quick[quickFl]" value="n" <?=gd_isset($checked['quick']['quickFl']['n']);?> onclick="display_switch('tr_quick_show')"/>사용안함</label>

                    <!--<label><input type="checkbox" name="quick[mobileFl]" value="y" <?=gd_isset($checked['quick']['mobileFl']['y']);?> />모바일샵 사용</label>-->
                </td>
            </tr>
            <tr class="tr_quick_show"  style="display:none">
                <th >노출 위치 선택</th>
                <td><div class="form-inline">
                        <div class="pd10" style="float:left" >
                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/quick_search_right.png"><br/>
                            <label  class="radio-inline mgt10 "><input type="radio" name="quick[area]" value="right" <?=gd_isset($checked['quick']['area']['right']);?>/>우측</label>
                        </div>
                        <div class="pd10" style="float:left" >
                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/quick_search_left.png"><br/>
                            <label  class="radio-inline mgt10 "><input type="radio" name="quick[area]" value="left" <?=gd_isset($checked['quick']['area']['left']);?>/>좌측</label>

                        </div>
                        <div class="pd10" style="float:left" >
                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/quick_search_top.png"><br/>
                            <label  class="radio-inline mgt10 "><input type="radio" name="quick[area]" value="top" <?=gd_isset($checked['quick']['area']['top']);?>/>상단</label>
                        </div></div>
                </td>
            </tr>
            <tr class="tr_quick_show"  style="display:none">
                <th >검색 조건 선택</th>
                <td>

                    <input type="hidden" name="quick[searchType][]" value="keyword">
                    <?php foreach($data['set']['searchType'] as $k => $v) { ?>
                        <label class="checkbox-inline"><input type="checkbox" name="quick[searchType][]" value="<?=$k?>"  <?=gd_isset($checked['quick']['searchType'][$k]); ?>   <?php if($k =='keyword')  { echo "disabled='true'"; } ?> > <?=$v?></label>
                    <?php } ?>

                </td>
            </tr>
        </table>
    </div>
</form>
