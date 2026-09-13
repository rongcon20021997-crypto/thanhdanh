<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Purchases extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchases_model');
        $this->load->model('invoice_items_model');
    }
    public function index()
    {
        if (!has_permission('purchases', '', 'view')) {
            if (!has_permission('purchases', '', 'create')) {
                access_denied('purchases');
            }
        }
        $data['title']          = _l('ch_purchases');
        $this->load->view('admin/purchases/manage', $data);
    }
    public function note_cancel($id ='')
    {
        $data = $this->input->post();
            $purchases = get_table_where('tblpurchases',array('id'=>$id),'','row');
            $staff_id=get_staff_user_id();
            $date=date('Y-m-d H:i:s');
            $history_status = $purchases->history_status;
            $history_status.='|'.$staff_id.','.$date;
        $in = array(
            'history_status'=>$history_status,
            'note_cancel' => $data['note_cancel'],
            'status' => 4,
        );
        $this->db->where('id', $id);
        $result = $this->db->update('tblpurchases', $in);
        if ($result) {
            $message = _l('updated_successfuly');
            $alert_type = 'success';
            log_activity('Purchases cancel [ID: ' . $id . ']');
        }
        echo json_encode(array(
            'success' => $result,
            'message' => $message,
            'alert_type' => $alert_type
        ));
        die;
    }
    public function no_note_cancel($id ='')
    {
            $purchases = get_table_where('tblpurchases',array('id'=>$id),'','row');
            $staff_id=get_staff_user_id();
            $date=date('Y-m-d H:i:s');
            $history_no_note_cancel = $staff_id.','.$date;
            $history_statuss = '';
            $history_status = $purchases->history_status;
            $history = explode('|', $history_status);
            foreach ($history as $key => $value) {
                if($key > 0)
                {
                if($key < 3)
                {
               $history_statuss.='|'.$value; 
                }
                }
            }
            $history_status = trim($history_status,'|');
        $in = array(
            'history_status'=>$history_statuss,
            'note_cancel' => '',
            'status' => 3,
            'history_no_note_cancel'=>$history_no_note_cancel,
        );
        $this->db->where('id', $id);
        $result = $this->db->update('tblpurchases', $in);
        if ($result) {
            $message = _l('updated_successfuly');
            $alert_type = 'success';
            log_activity('Cancel purchases cancel [ID: ' . $id . ']');
        }
        echo json_encode(array(
            'success' => $result,
            'message' => $message,
            'alert_type' => $alert_type
        ));
        die;
    }        
    public function table()
    {
        if (!has_permission('purchases', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('purchases');
    }
    public function detail($id='')
    {
        if ($this->input->post()) {
            if ($id == '') {

                if (!has_permission('purchases', '', 'create')) {
                    access_denied('purchases');
                }

                $data                 = $this->input->post();
                
                if(isset($data['items']) && count($data['items']) > 0)
                {
                    
                    $id = $this->purchases_model->add($data);
                }
                
                if ($id) {
                    set_alert('success', _l('ch_added_successfuly'));
                    redirect(admin_url('purchases'));
                }
            } else {
                if (!has_permission('purchases', '', 'edit')) {
                        access_denied('purchases');
                }
                $success = $this->purchases_model->update($this->input->post(), $id);
                if ($success == true) {
                    $this->load->model('misc_model');
                    $this->misc_model->changeRowNew_model('tblpurchases',$id);

                    set_alert('success', _l('ch_updated_successfuly'));
                }
                redirect(admin_url('purchases/detail/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('ch_purchases'));

        } else {
            $title = _l('edit', _l('ch_purchases'));
            $data['purchase'] = $this->purchases_model->get($id);
        }
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['title'] = $title;
        $this->load->view('admin/purchases/detail', $data);
    } 
    public function add_rfq($id='')
     {  
         if ($this->input->post()) {
            $data = $this->input->post();
            if(empty($data['id']))
            {
                unset($data['id']);
            $success = $this->purchases_model->add_rfq($data,$id);
            if($success)
            {
                $success = true;
                $message = _l('ch_added_successfuly', _l('ch_evaluation_criteria'));
            }else
            {
                $success = false;
                $message = _l('ch_added_successfuly_not', _l('ch_evaluation_criteria'));
            }
            }else
            {
                $success = $this->purchases_model->update_rfq($data,$id);
                $success = true;
                $message = _l('updated_successfully', _l('ch_evaluation_criteria'));
            }
         }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));die;
     } 
    public function views_purchases($id = '')
    {
        $data['purchase'] = $this->purchases_model->get($id);
        $this->load->view('admin/purchases/view_modal',$data);
    }
     
    public function test_quotes_suppliers()
     {
        if ($this->input->post()) {
            $data =$this->input->post();
            $quotes_suppliers = get_table_where('tblsupplier_quotes',array('id_ask_price'=>$data['id'],'suppliers_id'=>$data['supplier_id']),'','row');
            if($quotes_suppliers)
            {
                $ktr = true;
            }else
            {
                $ktr = false;
            }
        }else{
            $ktr = false;
        }
        echo json_encode($ktr);
     } 
    public function rfq_modal($id = '',$type=1)
    {
        $data['purchasess'] = get_table_where('tblpurchases',array('id'=>$id),'','row');
        $data['purchase'] = $this->purchases_model->get_items_purchases($id);
        $data['id'] = $id;
        $data['suppliers'] = get_table_where('tblsuppliers');
        $ktr = get_table_where('tblrfq_ask_price',array('id_purchases'=>$id),'','row');
        if($ktr)
        {
            $data['ask_price'] = $this->purchases_model->get_ask_price($id);
            $suppliers_id = explode(',', $data['ask_price']->suppliers_id);
            foreach ($suppliers_id as $key => $value) {
                $data['suppliers_id'][$key]['id']=$value;
                $data['suppliers_id'][$key]['company']=get_table_where('tblsuppliers',array('id'=>$value),'','row')->company;
            }
            $data['suppliers_id'] = array_reverse($data['suppliers_id'], true);
            $data['targert'] = $this->purchases_model->get_targert($data['ask_price']->id);
        }
        $data['type_items'] = get_table_where('tbltype_items',array('active'=>1));
        $data['type'] = $type;

        //hoàng crm bổ xung
        // $dem_temp = 0;
        // $main = get_table_where('tblevaluation_criteria');
        // foreach ($main as $key => $value) {
        //     $data['dataMain'][$dem_temp]['id_main'] = $value['id'];
        //     $data['dataMain'][$dem_temp]['main'] = $value['name'];
        //     $sub = get_table_where('tblevaluation_criteria_children',array('id_evaluation'=>$value['id']));
        //     $dem_temp_sub = 0;
        //     foreach ($sub as $keySub => $valueSub) {
        //         $data['dataMain'][$dem_temp]['sub'][$dem_temp_sub]['id_sub'] = $valueSub['id'];
        //         $data['dataMain'][$dem_temp]['sub'][$dem_temp_sub]['name'] = $valueSub['name_children'];
        //         $dem_temp_sub++;
        //     }
        //     $dem_temp++;
        // }
        //end
        $this->load->view('admin/purchases/rfq_modal',$data);
    }     
    public function get_items_supplier()
    {
        if ($this->input->post()) {
            $data =$this->input->post();
            $mainstream_goods = $this->purchases_model->get_items_supplier($data['id'],$data['supplier_id']);
            echo json_encode($mainstream_goods);
        }
    }
    public function update_status($value='')
    {
        if ($this->input->post()) {
            $id=$this->input->post('id');
            $status=$this->input->post('status');
            $purchases = get_table_where('tblpurchases',array('id'=>$id),'','row');
            $staff_id=get_staff_user_id();
            $date=date('Y-m-d H:i:s');
            $history_status = $purchases->history_status;
            $history_status.='|'.$staff_id.','.$date;
            $data =array(
                'history_status'=>$history_status,
                'status' => ($status+1),
            );
            $success=$this->purchases_model->update_status($id,$data);
        }
        if($success) {
            echo json_encode(array(
                'success' => $success,
                'message' => _l('ch_confirm_3')
            ));
        }
        else
        {
            echo json_encode(array(
                'success' => $success,
                'message' => _l('ch_confirm_3_no')
            ));
        }
        die;
    }
    public function items($type='')
    {
    //HAU
        if($type == 'items')
        {
            echo json_encode($this->purchases_model->get_items_ch());
        }
    }  
    public function get_items($id='')
    {
        echo json_encode($this->invoice_items_model->get_full_edit($id));
    }
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Delete Purchases');
        }
        $ktr = get_table_where('tblrfq_ask_price',array('id_purchases'=>$id),'','row');
        if($ktr)
        {
            $response = false;
            $alert_type = 'warning';
            $message    = _l('ch_exsit_rfq');
        }else
        {
        $ktr_supplier_quotes = get_table_where('tblsupplier_quotes',array('id_purchases'=>$id),'','row');
        if($ktr_supplier_quotes)
        {
            $response = false;
            $alert_type = 'warning';
            $message    = _l('ch_exsit_quotes');
        }else
        {
        $response = $this->purchases_model->delete_purchases($id);
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');  
        }
        }
        if ($response) {
            $alert_type = 'success';
            $message    = _l('ch_delete');
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    }
    public function change_purchases_type($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->purchases_model->change_purchases_type($id, $status);
        }
    }
    public function print_pdf($id='')
    {
        ob_start();
        $data = new stdClass();
        $dataField = get_table_where('tbl_field_pdf',array('parent_field'=>'purchases'),'','row');
        $dataMain = get_table_where('tblpurchases',array('id'=>$id),'','row');
        $dataSub = get_table_where('tblpurchases_items',array('purchases_id'=>$id));
        $table = '';
        $img = file_get_contents(base_url('uploads/company/').get_option('company_logo'));
        $data->img = '<img width="100" src="data:image/png;base64,'.base64_encode($img).'"/>';
        $data->content = '<table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 20%;">
                        </td>
                        <td style="width: 80%;">
                            <span style="font-weight: bold;font-size: 14px;">'.get_option('invoice_company_name').'</span><br>
                            <span>'.lang('tnh_address').': '.get_option('invoice_company_address').'</span><br>
                            <span>'.lang('tnh_phone').': '.get_option('invoice_company_phonenumber').'</span><br>
                            <span>'._l('Email').': </span><br>
                            <span>'.lang('tnh_website').': '.get_option('company_website').'</span><br>
                        </td>
                    </tr>
                </tbody>
            </table>';
        $data->content .= '<span style="text-align: center;">______________________________________________________________________________________________</span><br><br>';
        $data->content .= '<span style="text-align: center;font-size: 20px;font-weight: bold;">PHIẾU YÊU CẦU MUA HÀNG</span><br><br>';
        $data->content .= '<span style="text-align: right;font-style: italic;">'._l('ch_code_p').': '.$dataMain->prefix.$dataMain->code.'</span><br>';
        $data->content .= '<span style="text-align: right;font-style: italic;">'._l('ch_date_p').': '._d($dataMain->date).'</span><br><br>';
        $data->content .= '
            <span style="font-weight: bold;">'._l('ch_name_p').': </span><span>'.$dataMain->name_purchase.'</span><br>
            <span style="font-weight: bold;">'._l('ch_staff_p').': </span><span>'.get_staff_full_name($dataMain->staff_create).'</span><br><br>
        ';

        $widthSTT = '';
        $widthITEM = '';
        $widthUNIT = '';
        $widthQUANTITY = '';
        $widthQUANTITY_NET = '';
        $widthNOTE = '';
        $dem_temp = 2;
        if(isset($dataField->arr_field)) {
            $arr = explode(',', $dataField->arr_field);
            foreach ($arr as $key => $value) {
                if($value == 'item_unit_purchases') {
                    $item_unit_purchases = true;
                    $dem_temp = 3;
                }
                if($value == 'item_quantity_purchases') {
                    $item_quantity_purchases = true;
                }
                if($value == 'item_quantity_confirm_purchases') {
                    $item_quantity_confirm_purchases = true;
                }
                if($value == 'item_note_purchases') {
                    $item_note_purchases = true;
                }
            }
            if(isset($item_unit_purchases) && isset($item_quantity_purchases) && isset($item_quantity_confirm_purchases) && isset($item_note_purchases)) {
                $widthSTT = 'width: 5%;';
                $widthITEM = 'width: 30%;';
                $widthUNIT = 'width: 12%;';
                $widthQUANTITY = 'width: 15%;';
                $widthQUANTITY_NET = 'width: 18%;';
                $widthNOTE = 'width: 20%;';
            }
        }
        $table = '
            <table class="table table-bordered" border="1" width="100%">
                <thead>
                    <tr>
                        <td style="'.$widthSTT.'text-align: center;font-weight: bold;">'._l('STT').'</td>
        ';
        $table .= '<td style="'.$widthITEM.'text-align: center;font-weight: bold;">'._l('ch_items_name_t').'</td>';
        
        if(isset($item_unit_purchases)) {
            $table .= '<td style="'.$widthUNIT.'text-align: center;font-weight: bold;">'._l('item_unit').'</td>';
        }
        if(isset($item_quantity_purchases)) {
            $table .= '<td style="'.$widthQUANTITY.'text-align: center;font-weight: bold;">'._l('item_quantity_all').'</td>';
        }
        if(isset($item_quantity_confirm_purchases)) {
            $table .= '<td style="'.$widthQUANTITY_NET.'text-align: center;font-weight: bold;">'._l('item_quantity_confirm').'</td>';
        }
        if(isset($item_note_purchases)) {
            $table .= '<td style="'.$widthNOTE.'text-align: center;font-weight: bold;">'._l('note').'</td>';
        }
        $table .= '</tr>
                </thead>
                <tbody>';
        $sum_quantity = 0;
        $sum_quantity_net = 0;
        foreach ($dataSub as $key => $value) {
            $table .= '<tr>';
            $dataItem = $this->invoice_items_model->get_full_item($value['product_id'],$value['type']);
            $table .= '<td style="'.$widthSTT.'text-align: center;">'.++$key.'</td>';
            $table .= '<td style="'.$widthITEM.'text-align: left;">'.$dataItem->name.'</td>';
            if(isset($item_unit_purchases)) {
                $table .= '<td style="'.$widthUNIT.'text-align: center;">'.$dataItem->unit_name.'</td>';
            }
            if(isset($item_quantity_purchases)) {
                $table .= '<td style="'.$widthQUANTITY.'text-align: center;">'.$value['quantity'].'</td>';
                $sum_quantity += $value['quantity'];
            }
            if(isset($item_quantity_confirm_purchases)) {
                $table .= '<td style="'.$widthQUANTITY_NET.'text-align: center;">'.$value['quantity_net'].'</td>';
                $sum_quantity_net += $value['quantity_net'];
            }
            if(isset($item_note_purchases)) {
                $table .= '<td style="'.$widthNOTE.'text-align: center;">'.$value['note'].'</td>';
            }
            $table .= '</tr>';
        }
        $table .= '<tr>
                <td colspan="'.$dem_temp.'" style="text-align: center;font-weight: bold;">'._l('invoice_dt_table_heading_amount').'</td>';
        if(isset($item_quantity_purchases)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_quantity).'</td>';
        }
        if(isset($item_quantity_confirm_purchases)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_quantity_net).'</td>';
        }
        if(isset($item_note_purchases)) {
            $table .= '<td></td>';
        }
        $table .= '</tr>';
        $table .= '</tbody>
            </table>';
        $data->content .= $table;


        $table = '<table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Người Đề Nghị</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Trưởng Bộ Phận</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Lãnh Đạo</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                    </tr>
                </tbody>
            </table>';
        $data->content .= $table;
        $pdf      = print_pdf($data);
        $type     = 'I';
        $pdf->Output(slug_it('') . '.pdf', $type);
    }
}