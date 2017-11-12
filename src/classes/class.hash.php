<?php
    class Hash{
        public function make(){
            return (hash('whirlpool', $str));
        }
    }
?>