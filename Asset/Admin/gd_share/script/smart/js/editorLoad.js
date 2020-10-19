var uploadImages = [];

function addUploadImages(data) {
    uploadImages.push(data);
}

function cleanUploadImages() {
    uploadImages = null;
}

var oEditors = [];
nhn.husky.EZCreator.createInIFrame({
    oAppRef: oEditors,
    elPlaceHolder: "editor",
    sSkinURI: "/admin/gd_share/script/smart/SmartEditor2Skin.html?t=2",
    htParams: {
        bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
        bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
        bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
        /*fOnBeforeUnload: function () {
            if(!uploadImages) {
                return ;
            }
            $.ajax({
                method: "GET",
                url: "/share/editor_file_uploader.php",
                data: {mode: 'deleteGarbage', uploadImages : uploadImages.join('^|^')},
                cache: false,
            }).success(function (data) {
            }).error(function (e) {
            });
        }*/
    }, //boolean
    fOnAppLoad: function () {
        //예제 코드
        //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
    },
    fCreator: "createSEditor2"
});/**
 * Created by LeeNamJu on 2015-10-06.
 */
