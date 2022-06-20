<?php
namespace App\Controllers;

class assetsControler{
    public function alert($message, $typeAlert, $icon=''){
        if($message==NULL) return '';
        
        return '<div class="alert alert-'.$typeAlert.' alert-dismissible fade show" role="alert">
                    '.$icon.'
                    <div class="d-inline ms-1">
                        '.$message.'
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
    
}