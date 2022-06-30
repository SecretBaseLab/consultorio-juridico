<?php
namespace App\Controllers;

class assetsControler{
    public function alert($message, $typeAlert='info', $icon=''){
        if($message==NULL) return '';
        
        return '<div class="alert alert-'.$typeAlert.' alert-dismissible fade show" role="alert">
                    '.$icon.'
                    <div class="d-inline ms-1">
                        '.$message.'
                    </div>
                    <button id="btn_close_alert" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script> window.onload = ()=>{ setTimeout(() => {btn_close_alert.click()}, 2500); }</script>';
    }

    public function alertAjax($message, $typeAlert='info', $icon=''){
        if($message==NULL) return '';
        
        return '<div class="alert alert-'.$typeAlert.' alert-dismissible fade show" role="alert">
                    '.$icon.'
                    <div class="d-inline ms-1">
                        '.$message.'
                    </div>
                    <button id="btn_close_alert" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
}