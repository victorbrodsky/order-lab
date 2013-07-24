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
    
    public function getReturnSlide() {        
        $arr = array( 'Filing Room'=>'Filing Room', "Me"=>"Me" );
        
        return $arr;
    }
    
    public function getBlock() {        
        $arr = array();
        
        for( $i=0; $i<=100; $i++ ) {
            array_push($arr, $i);
        }
        
        return $arr;
    }

    public function getPart() {        
        $arr = array();
        $letters = array();
        $letters = range('A', 'Z');
        $count = 0;
        for( $i=0; $i<=100; $i++ ) {               
            if( $count == 0 ) {
                array_push( $arr, $letters[$i%26] );
                //$arr[$letters[$i%26]]  = $letters[$i%26];
            } else {
                array_push( $arr, $arr[$count-1].$letters[$i%26] );
                //$arr[$letters[$i%26]]  = $arr[$count-1].$letters[$i%26];
            }       
            if(  ($i % 26) == 25 ) $count++; //1,2,3,4...26
        }          
        
        return $arr;
    }
    
}
?>
