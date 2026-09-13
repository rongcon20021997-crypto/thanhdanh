<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Purchase_invoice extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        if (!has_permission('purchase_invoice', '', 'view')) {
                access_denied('purchase_invoice');
        }
        $data['title']          = _l('ch_purchase_invoice');
        $this->load->view('admin/purchase_invoice/manage', $data);
    }
    public function table()
    {
        if (!has_permission('purchase_invoice', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('purchase_invoice');
    }
    public function add()
    {
        $alert_type = 'wanring';
        $message    = _l('ch_added_successfuly_not');
        if ($this->input->post()) {
        $data = $this->input->post();
        $data['date_invoice'] = to_sql_date($data['date_invoice']);
        $data['date_create'] = date('Y-m-d H:i:s');
        $data['staff_create'] = get_staff_user_id();
        if($this->db->insert('tblpurchase_invoice',$data))
        {
            $this->db->update('tblimport',array('red_invoice'=>1),array('id'=>$data['id_import']));
            $alert_type = 'success';
            $message    = _l('ch_added_successfuly');
        }
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
        ));die;
    }
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Delete Purchases');
        }
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');
        $ktr = get_table_where('tblpurchase_invoice',array('id'=>$id),'','row');
        $response = $this->db->delete('tblpurchase_invoice',array('id'=>$id));
        if ($response) {
            $this->db->update('tblimport',array('red_invoice'=>0),array('id'=>$ktr->id_import));
            $alert_type = 'success';
            $message    = _l('ch_delete');
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    }

}