<?php
namespace Ezrun\Core\Translation;

class TwigTranslation extends Translation {
    
    public function translate($label) {
        
        $translated = $label;
        
        foreach($this->getLabels() as $key => $value) {
            
            if($key == $label) $translated = $value;
        }
        
        return $translated;
    }
}