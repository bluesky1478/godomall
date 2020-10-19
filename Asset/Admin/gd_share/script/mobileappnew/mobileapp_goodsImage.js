/**
 * 모바일앱 상품이미지 등록
 **/
var goodImage_destination;
var goodImage_pictureSource;
var goodImage_fileMode;
var goodImage_fileName;

function onDeviceReadyGoods() {
    /*
     * 네이티브 기능 사용 후 > 페이지 재접속 > 네이티브 기능 사용 불가.
     * 위 현상으로 인해 네이티브 환경과의 통신(cordova.exec)을 지속적으로 실행하는 구문.
     */
    // setInterval(function () {
    //     cordova.exec(null, null, '', '', []);
    // }, 200);
    goodImage_destination = Camera.DestinationType.FILE_URI;
    goodImage_pictureSource = Camera.PictureSourceType.SAVEDPHOTOALBUM;
}
function onPhotoURISuccess(imageURI) {
    var imageDataSelector = $("#mobileapp_goodsImage_layout_selector");
    var device_uid = imageDataSelector.attr("data-deviceUid");
    var shop_domain = imageDataSelector.attr("data-shopDomain");
    goodImage_fileMode = imageDataSelector.attr("data-fileMode");
    goodImage_fileName = device_uid + '_' + goodImage_fileMode;
    console.log("onPhotoURISuccess " + shop_domain +" "+  goodImage_fileName);
    options_godo = new FileUploadOptions();
    options_godo.fileKey = 'file';
    options_godo.fileName = goodImage_fileName + ".jpg";
    //options_godo.fileName = imageURI.substr(imageURI.lastIndexOf('/') + 1);
    options_godo.mimeType = "image/jpg";

    params_godo = new Object();
    params_godo.mode = "imageUpload";
    params_godo.file_mode = goodImage_fileMode;
    params_godo.device_uid = device_uid;

    options_godo.params = params_godo;
    options_godo.chunkedMode = false;

    ft_godo = new FileTransfer();
    ft_godo.upload(imageURI, shop_domain, uploadCBPok, uploadCBPfail, options_godo);
}

function uploadCBPok(data){
    //alert("Code = " + r.responseCode + "\nResponse = " + r.response + "\nSent = " + r.bytesSent);
    console.log("f", arguments.callee.name.toString(), data.response);
    var timestp = new Date().getTime();
    $("#"+goodImage_fileMode).attr('src', '/data/mobileapp/'+goodImage_fileName+'.jpg?ts=' + timestp);
    $("#"+goodImage_fileMode+"_input").val(goodImage_fileName+'.jpg');

    //저장 후 창 닫기
    $('.bootstrap-dialog-close-button').trigger('click');
}
function uploadCBPfail(){
    //alert("An error has occurred: Code = " + error.code + "\n upload error source " + error.source + "\n upload error target " + error.target);
}
function onFail(message) {

}

$(document).ready(function(){
    if(navigator.userAgent.match(/android/ig)){
        $(document).on("click", "#mobileapp_layer_camera", function() {
            navigator.camera.getPicture(onPhotoURISuccess, onFail, {
                quality : 10,
                targetWidth: 400,
                targetHeight: 400,
                destinationType : goodImage_destination,
                encodingType: Camera.EncodingType.PNG
            });
        });

        $(document).on("click", "#mobileapp_layer_gallery", function() {
            navigator.camera.getPicture(onPhotoURISuccess, onFail, {
                sourceType : goodImage_pictureSource,
                cameraDirection : 0, //카메라 방향 0(후면),1(전면)
                destinationType : goodImage_destination
            });
        });
    }
    else {
        $(document).on("click", "#mobileapp_layer_camera", function() {
            navigator.camera.getPicture(onPhotoURISuccess, onFail, {
                quality : 10,
                targetWidth: 400,
                targetHeight: 400,
                destinationType : goodImage_destination,
                encodingType: Camera.EncodingType.PNG
            });
        });

        $(document).on("click", "#mobileapp_layer_gallery", function() {
            navigator.camera.getPicture(onPhotoURISuccess, onFail, {
                sourceType : goodImage_pictureSource,
                allowEdit : true,
                saveToPhotoAlbum : false, //카메라롤 저장
                cameraDirection : 0, //카메라 방향 0(후면),1(전면)
                correctOrientation : true,
                destinationType : goodImage_destination
            });
        });
    }
});
