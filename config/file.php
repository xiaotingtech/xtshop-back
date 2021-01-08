<?php
/**
 * 文件验证的配置文件
 */
return [
    //是否要上传OSS
    'is_oss'=>false,
    //图片配置
    'image_max_width'=>1024,
    'image_max_height'=>1024,
    'svg_max_size'=>15728640,//15M
    //SVG和Image公共的ext和mime
    'image_svg_ext'=>"png,jpg,jpeg,gif,bmp,svg",
    'svg_mime'=>['image/svg+xml'],
    'image_svg_ext_arr'=>["png","jpg","jpeg","gif","bmp","svg"],
    'image_svg_mime'=>['image/bmp','image/jpg','image/gif','image/png','image/jpeg','image/svg+xml'],

    //视频配置
    'video_ext'=>"flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4",
    'video_mime'=>["video/mp4","video/flv","video/swf","video/mkv","video/rm","video/mpeg"
        ,"video/avi","video/rm","video/rmvb","video/x-ms-wmv","video/x-ms-asf",
    "video/x-la-asf","application/octet-stream","video/mpeg","video/x-msvideo","video/quicktime","video/x-sgi-movie"],
    'video_max_size'=>52428800,//50M
];