<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Barcode extends App_Controller
{
    public function set_barcode($code)
    {
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        Zend_Barcode::render('code39', 'image', array('text'=>$code), array());
    }
}
