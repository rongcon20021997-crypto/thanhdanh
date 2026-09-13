<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Business_plan extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->model('items_model');
        $this->load->model('unit_model');
        $this->load->model('category_model');
        $this->load->model('manufactures_model');
        // $this->lang->load('vietnamese/form_validation_lang');
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '1024';
        $this->upload_path = get_upload_path_by_type('products');
        $this->datetime_now = time();
        $this->tnh = true;
    }

    public function index()
    {
    	$data['tnh'] = true;
        $data['title'] = lang('tnh_business_plan');
        $this->load->view('admin/business_plan/manage', $data);
    }

    public function add()
    {
    	$data['reference_no'] = getReference('business_plan');
    	$data['tnh'] = true;
        $data['title'] = lang('tnh_add_business_plan');
        $data['breadcrumb'] = [array('link' => base_url('admin/business_plan'), 'page' => lang('tnh_business_plan')), array('link' => '#', 'page' => lang('tnh_add_business_plan'))];
        $this->load->view('admin/business_plan/add', $data);
    }
}

?>