<?php
// form_type 이 inc 인경우 디자인 맵을 보여주지 않습니다.
if ($designInfo['file']['form_type'] !== 'inc') {
?>
<div class="table-title gd-help-manual">
    레이아웃 설정
</div>
<div id="design_page_map" class="design_page_map">
    <div id="frame_main_mobile">

        <!-- outline/_header.htm : Start -->
        <div id="frame_outHeader">
            <div class="design-font-eng pdl15 pdr15">
                outline/_header.html
                <a href="./design_page_edit.php?designPageId=outline/_header.html" class="btn btn-sm btn-link bold pull-right">편집하기</a>
            </div>
        </div>
        <!-- outline/_header.htm : End -->

        <!-- 1. 상단디자인 : Start -->
        <div id="frame_header">
            <div class="ready">
                <div class="form-inline">
                    <span class="design-title">상단영역</span>
                    <select name="outline_header" id="outline_header" onchange="DCMAPM.file_outline( this.name )" class="form-control eng">
<?php
    foreach($designInfo['layout']['header'] as $opt){
        echo '<option value="' . gd_isset($opt['value']) . '" ' . gd_isset($opt['selected']) . ' path="' .gd_isset($opt['path']) . '">' .gd_isset($opt['text']) . '</option>';
    }
?>
                    </select>
                    <a href="javascript:designPageMove($('#outline_header option:selected'), '<?php echo $skinType;?>');" class="btn btn-sm btn-link bold">편집하기</a>
                </div>
            </div>
        </div>
        <!-- 1. 상단디자인 : End -->

        <!-- 3. 본문디자인 : Start -->
        <div id="frame_body">
            <div class="ready">
                <div>
                    <span class="design-title">본문영역</span>
                    <a href="./design_page_edit.php?designPageId=main/index.html" class="btn btn-sm btn-link bold">편집하기</a>
                </div>
            </div>
        </div>
        <!-- 3. 본문디자인 : End -->

        <!-- 4. 하단디자인 : Start -->
        <div id="frame_footer">
            <div class="ready">
                <div class="form-inline">
                    <span class="design-title">하단영역</span>
                    <select name="outline_footer" id="outline_footer" onchange="DCMAPM.file_outline( this.name )" class="form-control eng">
<?php
    foreach($designInfo['layout']['footer'] as $opt){
        echo '<option value="' . gd_isset($opt['value']) . '" ' . gd_isset($opt['selected']) . ' path="' .gd_isset($opt['path']) . '">' .gd_isset($opt['text']) . '</option>';
    }
?>
                    </select>
                    <a href="javascript:designPageMove($('#outline_footer option:selected'), '<?php echo $skinType;?>');" class="btn btn-sm btn-link bold">편집하기</a>
                </div>
            </div>
        </div>
        <!-- 4. 하단디자인 : End -->

        <!-- outline/_footer.htm : Start -->
        <div id="frame_outFooter">
            <div class="design-font-eng pdl15 pdr15">
                outline/_footer.html
                <a href="./design_page_edit.php?designPageId=outline/_footer.html" class="btn btn-sm btn-link bold pull-right">편집하기</a>
            </div>
        </div>
        <!-- outline/_footer.htm : End -->

    </div>

    <div class="clear-both"></div>
</div>

<script language="javascript">
var designPage = '<?php echo gd_isset($getPageID); ?>';
var form_type = '<?php echo $designInfo['file']['form_type'];?>';
var float_type = '<?php echo $designInfo['file']['sidefloat'];?>';

/* 본문디자인 파일명 출력 */
if (form_type == 'file'){
    // print file_name
    var pNode = document.createElement('p')
    pNode.innerHTML = designPage;
    _ID('frame_body').getElementsByTagName('div')[0].getElementsByTagName('div')[0].appendChild(pNode);
}
else if (form_type != 'default'){
    _ID('frame_body').getElementsByTagName('a')[0].parentNode.removeChild(_ID('frame_body').getElementsByTagName('a')[0]);
}

/* 편집중 활성화 */
if (form_type == 'outline' && designPage == 'outline/_header.html'){ // outline/_header.htm
    _ID('frame_outHeader').style.background = '#777';
    _ID('frame_outHeader').getElementsByTagName('div')[0].style.color = '#FFFFFF';
    _ID('frame_outHeader').getElementsByTagName('a')[0].style.color = '#FFFFFF';
    _ID('frame_header').getElementsByTagName('div')[0].style.background = '#fcfcfc';
    _ID('frame_header').getElementsByTagName('div')[0].style.borderColor = '#777';
}
else if (form_type == 'outSection' && designPage.match(/outline\/header/) != null){ // 1. 상단디자인
    _ID('frame_header').getElementsByTagName('div')[0].style.background = '#fcfcfc';
    _ID('frame_header').getElementsByTagName('div')[0].style.borderColor = '#777';
}
else if (form_type == 'file'){ // 3. 본문디자인
    _ID('frame_body').getElementsByTagName('div')[0].style.background = '#fcfcfc';
    _ID('frame_body').getElementsByTagName('div')[0].style.borderColor = '#777';
}
else if (form_type == 'outSection' && designPage.match(/outline\/footer/) != null){ // 4. 하단디자인
    _ID('frame_footer').getElementsByTagName('div')[0].style.background = '#fcfcfc';
    _ID('frame_footer').getElementsByTagName('div')[0].style.borderColor = '#777';
}
else if (form_type == 'outline' && designPage == 'outline/_footer.html'){ // outline/_footer.htm

    _ID('frame_outFooter').style.background = '#777';
    _ID('frame_outFooter').getElementsByTagName('div')[0].style.color = '#FFFFFF';
    _ID('frame_outFooter').getElementsByTagName('a')[0].style.color = '#FFFFFF';
    _ID('frame_footer').getElementsByTagName('div')[0].style.background = '#fcfcfc';
    _ID('frame_footer').getElementsByTagName('div')[0].style.borderColor = '#777';
}
</script>
<?php
}
?>
