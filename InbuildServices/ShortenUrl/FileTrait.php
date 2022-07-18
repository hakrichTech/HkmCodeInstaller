<?php
namespace Hkm_services\ShortenUrl;

/**
 * File Entity
 */
trait FileTrait
{
    protected static $filesType = [
			'application/pdf'=>'Pdf Document',
			'application/force-download'=>'Pdf Document',
			'application/x-download'=>'Pdf Document',
            'application/msword'=>'doc Document',
            'application/vnd.ms-office'=>'doc Document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'doc Document',
            'application/octet-stream'=>'doc Document',
            'application/vnd.ms-powerpoint'=>'powerpoint Document',
            'application/powerpoint'=>'powerpoint Document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'powerpoint Document',
            'application/excel'=>'excel Document',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12'=>'excel Document',
		    'application/vnd.ms-excel.sheet.macroEnabled.12'=>'excel Document',
			'application/vnd.ms-excel'=>'excel Document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'excel Document',
            'application/x-zip'=>'archive',
            'application/zip'=>'archive',
            'application/x-zip-compressed'=>'archive',
            'application/s-compressed'=>'archive',
            'multipart/x-zip'=>'archive',
            'application/vnd.rar'=>'archive',
            'application/rar'=>'archive',
            'application/x-rar'=>'archive',
            'application/x-rar-compressed'=>'archive',
            'application/x-tar'=>'archive',
			'application/x-gzip-compressed'=>'archive',
            'application/x-gtar'=>'archive',
    ];

    protected static $typesFile = [
        'Audio'=>[],
        'Video'=>[],
        'Image'=>[],
        'Archive'=>[],
        'Document'=>[],
        'Others'=>[],
    ];

    protected static $icons = [
        'Doc'=>'fa fa-file-text FILE',
        'Pdf'=>'fa fa-file-pdf-o FILE',
        'Audio' => 'fa fa-file-audio-o FILE',
        'Video' => 'fa fa-file-video-o FILE',
        'Image' => 'fa fa-file-image-o FILE',
        'Archive' => 'fa fa-file-archive-o FILE',
        'Excel' => 'fa fa-file-excel-o FILE',
        'Powerpoint'=> 'fa fa-file-powerpoint-o FILE',
        'Others'=> 'fa fa-file-o FILE'
    ];

    protected static $datesData = [];
}
