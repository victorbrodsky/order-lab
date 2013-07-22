<?php

namespace Oleg\OrderformBundle\Helper;

class FormHelper {
    
    public function getStains() {
        $arr = array(
            'H&E'=>'H&E','2-Oct'=>'2-Oct','4-Oct'=>'4-Oct','A103 (Melan-A)'=>'A103 (Melan-A)'
        );
        
        return $arr;
    }
    
    public function getMags() {        
        $arr = array( '20X'=>'20X', '40X'=>'40X' );
        
        return $arr;
    }
    
    public function getPriority() {        
        $arr = array( 'Routine'=>'Routine', 'Stat'=>'Stat' );
        
        return $arr;
    }
    
    public function getSlideDelivery() {        
        $arr = array( 'I will drop ...'=>'I will drop ...', "I'll give slides to .."=>"I'll give slides to .." );
        
        return $arr;
    }
    
}
?>
