cordova.define('cordova/plugin_list', function(require, exports, module) {
    module.exports = [
        {
            "file": "plugins/org.apache.cordova.device/www/device.js",
            "id": "org.apache.cordova.device.device",
            "clobbers": [
                "device"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/ios/Entry.js",
            "id": "org.apache.cordova.file.Entry1",
            "merges": [
                "window.Entry"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.inappbrowser/www/inappbrowser.js",
            "id": "org.apache.cordova.inappbrowser.inappbrowser",
            "clobbers": [
                "window.open"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.network-information/www/network.js",
            "id": "org.apache.cordova.network-information.network",
            "clobbers": [
                "navigator.connection",
                "navigator.network.connection"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.network-information/www/Connection.js",
            "id": "org.apache.cordova.network-information.Connection",
            "clobbers": [
                "Connection"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.dialogs/www/notification.js",
            "id": "org.apache.cordova.dialogs.notification",
            "merges": [
                "navigator.notification"
            ]
        },
        {
            "file": "plugins/com.godo.cordova.barcodescanner/www/barcodescanner.js",
            "id": "com.godo.cordova.barcodescanner.barcodescanner",
            "pluginId": "cordova-plugin-barcodescanner",
            "clobbers": [
                "window.plugins.barcodeScanner"
            ]
        },
        {
            "file": "plugins/webbridge.js",
            "id": "webbridge",
            "clobbers": [
                "window.plugins.webBridge"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.wkwebview-engine/www/ios-wkwebview-exec.js",
            "id": "org.apache.cordova.wkwebview-engine.ios-wkwebview-exec",
            "pluginId": "cordova-plugin-wkwebview-engine",
            "clobbers": [
                "cordova.exec"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.wkwebview-file-xhr/www/ios/xhr-polyfill.js",
            "id": "cordova-plugin-wkwebview-file-xhr.xhr-polyfill",
            "pluginId": "cordova-plugin-wkwebview-file-xhr",
            "runs": true
        },
        {
            "file": "plugins/org.apache.cordova.wkwebview-file-xhr/www/ios/fetch-bootstrap.js",
            "id": "cordova-plugin-wkwebview-file-xhr.fetch-bootstrap",
            "pluginId": "cordova-plugin-wkwebview-file-xhr",
            "runs": true
        },
        {
            "file": "plugins/org.apache.cordova.wkwebview-file-xhr/www/ios/whatwg-fetch-2.0.3.js",
            "id": "cordova-plugin-wkwebview-file-xhr.fetch-polyfill",
            "pluginId": "cordova-plugin-wkwebview-file-xhr",
            "runs": true
        },
        {
            "file": "plugins/org.apache.cordova.camera/www/CameraConstants.js",
            "id": "org.apache.cordova.camera.Camera",
            "clobbers": [
                "Camera"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.camera/www/CameraPopoverOptions.js",
            "id": "org.apache.cordova.camera.CameraPopoverOptions",
            "clobbers": [
                "CameraPopoverOptions"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.camera/www/Camera.js",
            "id": "org.apache.cordova.camera.camera",
            "clobbers": [
                "navigator.camera"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.camera/www/ios/CameraPopoverHandle.js",
            "id": "org.apache.cordova.camera.CameraPopoverHandle",
            "clobbers": [
                "CameraPopoverHandle"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/DirectoryEntry.js",
            "id": "org.apache.cordova.file.DirectoryEntry",
            "clobbers": [
                "window.DirectoryEntry"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/DirectoryReader.js",
            "id": "org.apache.cordova.file.DirectoryReader",
            "clobbers": [
                "window.DirectoryReader"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/Entry.js",
            "id": "org.apache.cordova.file.Entry",
            "clobbers": [
                "window.Entry"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/File.js",
            "id": "org.apache.cordova.file.File",
            "clobbers": [
                "window.File"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileEntry.js",
            "id": "org.apache.cordova.file.FileEntry",
            "clobbers": [
                "window.FileEntry"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileError.js",
            "id": "org.apache.cordova.file.FileError",
            "clobbers": [
                "window.FileError"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileReader.js",
            "id": "org.apache.cordova.file.FileReader",
            "clobbers": [
                "window.FileReader"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileSystem.js",
            "id": "org.apache.cordova.file.FileSystem",
            "clobbers": [
                "window.FileSystem"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileUploadOptions.js",
            "id": "org.apache.cordova.file.FileUploadOptions",
            "clobbers": [
                "window.FileUploadOptions"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileUploadResult.js",
            "id": "org.apache.cordova.file.FileUploadResult",
            "clobbers": [
                "window.FileUploadResult"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/FileWriter.js",
            "id": "org.apache.cordova.file.FileWriter",
            "clobbers": [
                "window.FileWriter"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/Flags.js",
            "id": "org.apache.cordova.file.Flags",
            "clobbers": [
                "window.Flags"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/LocalFileSystem.js",
            "id": "org.apache.cordova.file.LocalFileSystem",
            "clobbers": [
                "window.LocalFileSystem"
            ],
            "merges": [
                "window"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/Metadata.js",
            "id": "org.apache.cordova.file.Metadata",
            "clobbers": [
                "window.Metadata"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/ProgressEvent.js",
            "id": "org.apache.cordova.file.ProgressEvent",
            "clobbers": [
                "window.ProgressEvent"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/fileSystems.js",
            "id": "org.apache.cordova.file.fileSystems",
            "pluginId": "cordova-plugin-file"
        },
        {
            "file": "plugins/org.apache.cordova.file/www/requestFileSystem.js",
            "id": "org.apache.cordova.file.requestFileSystem",
            "clobbers": [
                "window.requestFileSystem"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/resolveLocalFileSystemURI.js",
            "id": "org.apache.cordova.file.resolveLocalFileSystemURI",
            "clobbers": [
                "window.resolveLocalFileSystemURI"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/browser/isChrome.js",
            "id": "org.apache.cordova.file.isChrome",
            "pluginId": "cordova-plugin-file",
            "runs": true
        },
        {
            "file": "plugins/org.apache.cordova.file/www/ios/FileSystem.js",
            "id": "org.apache.cordova.file.iosFileSystem",
            "pluginId": "cordova-plugin-file",
            "merges": [
                "FileSystem"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file/www/fileSystems-roots.js",
            "id": "org.apache.cordova.file.fileSystems-roots",
            "pluginId": "cordova-plugin-file",
            "runs": true
        },
        {
            "file": "plugins/org.apache.cordova.file/www/fileSystemPaths.js",
            "id": "org.apache.cordova.file.fileSystemPaths",
            "pluginId": "cordova-plugin-file",
            "merges": [
                "cordova"
            ],
            "runs": true
        },
        {
            "file": "plugins/org.apache.cordova.file-transfer/www/FileTransferError.js",
            "id": "org.apache.cordova.file-transfer.FileTransferError",
            "clobbers": [
                "window.FileTransferError"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.file-transfer/www/FileTransfer.js",
            "id": "org.apache.cordova.file-transfer.FileTransfer",
            "clobbers": [
                "window.FileTransfer"
            ]
        },
        {
            "file": "plugins/org.apache.cordova.inappbrowser-wkwebview/www/inappbrowser.js",
            "id": "org.apache.cordova.inappbrowser-wkwebview.inappbrowser",
            "pluginId": "cordova-plugin-inappbrowser-wkwebview",
            "clobbers": [
                "cordova.InAppBrowser.open",
                "window.open"
            ]
        }
    ];
    module.exports.metadata =
// TOP OF METADATA
        {
            "org.apache.cordova.device": "1.1.6",
            "org.apache.cordova.inappbrowser": "1.7.1",
            "org.apache.cordova.network-information": "1.3.3",
            "org.apache.cordova.dialogs": "1.3.3",
            "cordova-plugin-wkwebview-engine": "1.1.3",
            "cordova-plugin-wkwebview-file-xhr": "2.0.0",
            "org.apache.cordova.inappbrowser-wkwebview": "1.0.2",
            "cordova-plugin-camera": "2.4.1",
            "cordova-plugin-file": "4.3.3",
            "cordova-plugin-file-transfer": "1.6.3"
        }
// BOTTOM OF METADATA
});