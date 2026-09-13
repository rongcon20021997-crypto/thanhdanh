<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_order_model');
        $this->load->model('purchases_model');
        $this->load->model('invoice_items_model');
        $this->load->model('supplier_quotes_model');
        $this->load->model('misc_model');
    }
    public function index()
    {
        if (!has_permission('purchase_order', '', 'view')) {
            if (!has_permission('purchase_order', '', 'create')) {
                access_denied('purchase_order');
            }
        }
        $data['title']          = _l('ch_order');
        //cập nhật dữ liệu mức độ ưu tiên cho người đăng nhập đầu tiên trong ngày
        $checkDone = get_table_where('tbl_update_on_day',array(),'','row');
        if($checkDone->date != date('Y-m-d')) {
            $this->misc_model->update_priority();

            $this->db->set('date',date('Y-m-d'));
            $this->db->update('tbl_update_on_day');
        }
        //end
        $this->load->view('admin/purchase_order/manage', $data);
    }
    public function table()
    {
        if (!has_permission('purchase_order', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('purchase_order');
    }
    public function detail($id='')
    {
        if ($this->input->post()) {
            if ($id == '') {

                if (!has_permission('purchase_order', '', 'create')) {
                    access_denied('purchase_order');
                }

                $data = $this->input->post();
                
                if(isset($data['items']) && count($data['items']) > 0)
                {
                    $id = $this->purchase_order_model->add($data);
                }
                
                if ($id) {
                    set_alert('success', _l('ch_added_successfuly'));
                    redirect(admin_url('purchase_order'));
                }
            } else {
                if (!has_permission('purchase_order', '', 'edit')) {
                        access_denied('purchase_order');
                }
                $success = $this->purchase_order_model->update($this->input->post(), $id);
                if ($success == true) {
                    $this->load->model('misc_model');
                    $this->misc_model->changeRowNew_model('tblpurchase_order',$id);
                    
                    set_alert('success', _l('ch_updated_successfuly'));
                }
                redirect(admin_url('purchase_order/detail/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('ch_purchase_order'));

        } else {
            $title = _l('edit', _l('ch_purchase_order'));
            $data['items'] = $this->purchase_order_model->get($id);
        }
        $data['staff'] = get_table_where('tblstaff');
        $data['suppliers'] = get_table_where('tblsuppliers');
        $data['tax'] = get_table_where('tbltaxes');
        $data['taxes'] = get_taxes_dropdown_template('',0);
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['title'] = $title;
        $this->load->view('admin/purchase_order/detail', $data);
    } 
    public function purchases_detail($id='')
    {
        if ($this->input->post()) {
            if (!has_permission('purchase_order', '', 'create')) {
                access_denied('purchase_order');
            }

            $data = $this->input->post();
            
            $success = $this->purchase_order_model->update($this->input->post(), $id);
                if ($success == true) {
                    $this->load->model('misc_model');
                    $this->misc_model->changeRowNew_model('tblpurchase_order',$id);
                    
                    set_alert('success', _l('ch_updated_successfuly'));
                }
                redirect(admin_url('purchase_order/purchases_detail/' . $id));
        }

        $data['type_of_document'] = 1;

        $title = _l('edit', _l('ch_purchase_order'));
        $data['items'] = $this->purchase_order_model->get($id);
        $data['purchase'] = $this->purchases_model->get_create_purchase_order($data['items']->id_purchases);
        $data['idd'] = $id;
        $data['id'] = $data['items']->id_purchases;
        $data['staff'] = get_table_where('tblstaff');
        $data['suppliers'] = get_table_where('tblsuppliers');
        $data['taxes'] = get_taxes_dropdown_template('',0);
        $data['tax'] = get_table_where('tbltaxes');
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['title'] = $title;
        $this->load->view('admin/purchase_order/create_detail', $data);
    }
    public function create_detail($id='')
    {
        if ($this->input->post()) {
            if (!has_permission('purchase_order', '', 'create')) {
                access_denied('purchase_order');
            }

            $data = $this->input->post();
            
            if(isset($data['items']) && count($data['items']) > 0)
            {
                $id = $this->purchase_order_model->add($data);
            }
            if ($id) {
                set_alert('success', _l('ch_added_successfuly'));
                redirect(admin_url('purchase_order'));
            }
        }
        $data['type_of_document'] = 1;
        $title = _l('add_new', _l('ch_purchase_order'));
        $data['purchase'] = $this->purchases_model->get_create_purchase_order($id);
        $data['id'] = $id;
        $data['idd'] = 0;
        $data['staff'] = get_table_where('tblstaff');
        $data['suppliers'] = get_table_where('tblsuppliers');
        $data['taxes'] = get_taxes_dropdown_template('',0);
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['title'] = $title;
        $this->load->view('admin/purchase_order/create_detail', $data);
    }
    public function create_detailquotes($id='')
    {
        if ($this->input->post()) {

            if (!has_permission('purchase_order', '', 'create')) {
                access_denied('purchase_order');
            }

            $data = $this->input->post();
            
            if(isset($data['items']) && count($data['items']) > 0)
            {
                $id = $this->purchase_order_model->add($data);
            }
            
            if ($id) {
                set_alert('success', _l('ch_added_successfuly'));
                redirect(admin_url('purchase_order'));
            }
        }
        $title = _l('add_new', _l('ch_purchase_order'));
        $data['quotes'] = $this->supplier_quotes_model->get_full_edit($id);
        $data['id'] = $id;
        $data['staff'] = get_table_where('tblstaff');
        $data['suppliers'] = get_table_where('tblsuppliers');
        $data['taxes'] = get_taxes_dropdown_template('',0);
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['title'] = $title;
        $this->load->view('admin/purchase_order/create_detailquotes', $data);
    }
    public function view_purchase_order($id = '')
    {
        $data['items'] = $this->purchase_order_model->get($id);
        $this->load->view('admin/purchase_order/view_modal',$data);
    }
    public function update_status($value='')
    {
        if ($this->input->post()) {
            $id=$this->input->post('id');
            $checkCancel = get_table_where('tblpurchase_order',array('id'=>$id,'cancel <>'=>0),'','row');
            if($checkCancel) {
                $success = false;
            }
            else {
                $status=$this->input->post('status');
                $purchases = get_table_where('tblpurchase_order',array('id'=>$id),'','row');
                $staff_id=get_staff_user_id();
                $date=date('Y-m-d H:i:s');
                $history_status = $purchases->history_status;
                $history_status.='|'.$staff_id.','.$date;
                $data =array(
                    'history_status'=>$history_status,
                    'status' => ($status+1),
                );
                $success=$this->purchase_order_model->update_status($id,$data);
            }
        }
        if($success) {
            echo json_encode(array(
                'success' => $success,
                'alert_type' => 'success',
                'message' => _l('Xác nhận đề xuất thành công')
            ));
        }
        else
        {
            echo json_encode(array(
                'success' => $success,
                'alert_type' => 'danger',
                'message' => _l('Không thể cập nhật dữ liệu')
            ));
        }
        die;
    }
    public function cancel_status($value='')
    {
        if ($this->input->post()) {
            $id=$this->input->post('id');
            $cancel = get_staff_user_id().','.date('Y-m-d H:i:s');
            $data =array(
                'cancel'=>$cancel
            );
            $this->db->where('id',$id);
            $success = $this->db->update('tblpurchase_order',$data);
        }
        if($success) {
            $this->db->set('id_tickets_priorities',NULL);
            $this->db->where('id',$id);
            $this->db->update('tblpurchase_order');
            echo json_encode(array(
                'success' => $success,
                'message' => _l('Xác nhận hủy đơn hàng')
            ));
        }
        else
        {
            echo json_encode(array(
                'success' => $success,
                'message' => _l('Không thể cập nhật dữ liệu')
            ));
        }
        die;
    }
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Delete Purchases order');
        }
        $response = $this->purchase_order_model->delete_purchase_order($id);
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');  
        if ($response) {
            $alert_type = 'success';
            $message    = _l('ch_delete');
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    }
    public function items($type_of_document='',$id='',$type='')
    {
        if($type_of_document == 1)
        {
            $items = $this->purchase_order_model->get_items_purchase($type,$id);
            echo json_encode($items);die; 
        }
    }
    public function get_items($id='',$type='',$type_of_document='',$id_product)
    {
        if($type_of_document == 1)
        {
            $items = $this->purchase_order_model->get_items_purchases_item($type,$id,$id_product);
            if($items->avatar == '') {
                $items->avatar = 'uploads/no-img.jpg';
            }
            echo json_encode($items);die; 
        }
    }
    public function get_items_order($id='',$type='',$id_purchases='')
    {
        $items = $this->purchases_model->get_items_order($id,$type,$id_purchases);
        
        $items->avatar = (file_exists($items->avatar) ? base_url($items->avatar) : (file_exists('uploads/materials/'.$items->avatar) ? base_url('uploads/materials/'.$items->avatar) : (file_exists('uploads/products/'.$items->avatar) ? base_url('uploads/products/'.$items->avatar) : base_url('assets/images/preview-not-available.jpg'))));
        echo json_encode($items);
    }    
    public function items_quote($id='',$type='')
    {
        echo json_encode($this->purchase_order_model->get_items_quote($type,$id));die; 
    }    
    public function test_quantity($type='',$type_of_document='',$id='',$id_product='')
    {   
        $type_of_document=$this->input->post('type_of_document');
        $id=$this->input->post('id');
        $id_order=$this->input->post('id_order');
        $test_quantity = 0;
        if($type_of_document == 1)
        {
            
            $product=explode(',', trim($this->input->post('product_id'),','));

            foreach ($product as $key => $v) {
                $product_id=explode('|', $v);

                $data['items'][$key]['quantity']=$this->purchase_order_model->test_quantity($product_id[0],$id,$product_id[1]);
                $data['items'][$key]['type'] = $product_id[0];
                $data['items'][$key]['id_product'] = $product_id[1];
                $quantity_old = 0;
                if(!empty($id_order))
                    {
                    $quantity_old = get_table_where('tblpurchase_order_items',array('id_purchase_order'=>$id_order,'product_id'=>$product_id[1],'type'=>$product_id[0]),'','row')->quantity;
                    }
                if($product_id[2] > ($data['items'][$key]['quantity'] + $quantity_old))
                {
                 $test_quantity++;   
                }
            }
        }
        $data['test_quantity'] = $test_quantity;
        echo json_encode($data);die; 

    }

    public function print_pdf($id='')
    {
        ob_start();
        $data = new stdClass();
        $dataField = get_table_where('tbl_field_pdf',array('parent_field'=>'purchase_order'),'','row');
        $dataMain = get_table_where('tblpurchase_order',array('id'=>$id),'','row');
        $dataSub = get_table_where('tblpurchase_order_items',array('id_purchase_order'=>$id));
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
        $data->content .= '<span style="text-align: center;">____________________________________________________________________________________________________________________________________________</span><br><br>';
        $data->content .= '<span style="text-align: center;font-size: 20px;font-weight: bold;">ĐƠN ĐẶT HÀNG</span><br><br>';
        $data->content .= '<span style="text-align: right;font-style: italic;">'._l('ch_code_p').': '.$dataMain->prefix.'-'.$dataMain->code.'</span><br>';
        $data->content .= '<span style="text-align: right;font-style: italic;">'._l('ch_date_p').': '._d($dataMain->date).'</span><br><br>';
        $data->content .= '
            <span style="font-weight: bold;">'._l('ch_staff_p').': </span><span>'.get_staff_full_name($dataMain->staff_create).'</span><br>
            <span style="font-weight: bold;">'._l('ch_note_t').': </span><span>'.$dataMain->note.'</span><br><br>
        ';

        $width1 = '';
        $width2 = '';
        $width3 = '';
        $width4 = '';
        $width5 = '';
        $width6 = '';
        $width7 = '';
        $width8 = '';
        $width9 = '';
        $width10 = '';
        $width11 = '';
        $width12 = '';
        $dem_temp = 2;
        if(isset($dataField->arr_field)) {
            $arr = explode(',', $dataField->arr_field);
            foreach ($arr as $key => $value) {
                if($value == 'item_quantity_purchase_order') {
                    $item_quantity_purchase_order = true;
                }
                if($value == 'item_quantity_suppliers_purchase_order') {
                    $item_quantity_suppliers_purchase_order = true;
                }
                if($value == 'item_unit_purchase_order') {
                    $item_unit_purchase_order = true;
                }
                if($value == 'item_price_expected_purchase_order') {
                    $item_price_expected_purchase_order = true;
                }
                if($value == 'item_price_suppliers_purchase_order') {
                    $item_price_suppliers_purchase_order = true;
                }
                if($value == 'item_amount_expected_purchase_order') {
                    $item_amount_expected_purchase_order = true;
                }
                if($value == 'item_promotion_suppliers_purchase_order') {
                    $item_promotion_suppliers_purchase_order = true;
                }
                if($value == 'item_tax_purchase_order') {
                    $item_tax_purchase_order = true;
                }
                if($value == 'item_amount_suppliers_purchase_order') {
                    $item_amount_suppliers_purchase_order = true;
                }
                if($value == 'item_note_purchase_order') {
                    $item_note_purchase_order = true;
                }
            }
            if(isset($item_quantity_purchase_order) && isset($item_quantity_suppliers_purchase_order) && isset($item_unit_purchase_order) && isset($item_price_expected_purchase_order) && isset($item_price_suppliers_purchase_order) && isset($item_amount_expected_purchase_order) && isset($item_promotion_suppliers_purchase_order) && isset($item_tax_purchase_order) && isset($item_amount_suppliers_purchase_order) && isset($item_note_purchase_order)) {
                $width1 = 'width: 5%;';
                $width2 = 'width: 15%;';
                $width3 = 'width: 8%;';
                $width4 = 'width: 8%;';
                $width5 = 'width: 8%;';
                $width6 = 'width: 8%;';
                $width7 = 'width: 8%;';
                $width8 = 'width: 8%;';
                $width9 = 'width: 8%;';
                $width10 = 'width: 8%;';
                $width11 = 'width: 8%;';
                $width12 = 'width: 8%;';
            }
        }
        $table = '
            <table class="table table-bordered" border="1" width="100%">
                <thead>
                    <tr>
                        <td style="'.$width1.'text-align: center;font-weight: bold;">'._l('STT').'</td>
        ';
        $table .= '<td style="'.$width2.'text-align: center;font-weight: bold;">'._l('ch_items_name_t').'</td>';
        
        if(isset($item_quantity_purchase_order)) {
            $table .= '<td style="'.$width3.'text-align: center;font-weight: bold;">'._l('item_quantity').'</td>';
        }
        if(isset($item_quantity_suppliers_purchase_order)) {
            $table .= '<td style="'.$width4.'text-align: center;font-weight: bold;">'._l('quantity_suppliers').'</td>';
        }
        if(isset($item_unit_purchase_order)) {
            $table .= '<td style="'.$width5.'text-align: center;font-weight: bold;">'._l('item_unit').'</td>';
        }
        if(isset($item_price_expected_purchase_order)) {
            $table .= '<td style="'.$width6.'text-align: center;font-weight: bold;">'._l('price_expected').'</td>';
        }
        if(isset($item_price_suppliers_purchase_order)) {
            $table .= '<td style="'.$width7.'text-align: center;font-weight: bold;">'._l('price_suppliers').'</td>';
        }
        if(isset($item_amount_expected_purchase_order)) {
            $table .= '<td style="'.$width8.'text-align: center;font-weight: bold;">'._l('amount_expected_vnd').'</td>';
        }
        if(isset($item_promotion_suppliers_purchase_order)) {
            $table .= '<td style="'.$width9.'text-align: center;font-weight: bold;">'._l('promotion_suppliers').'</td>';
        }
        if(isset($item_tax_purchase_order)) {
            $table .= '<td style="'.$width10.'text-align: center;font-weight: bold;">'._l('tax').'</td>';
        }
        if(isset($item_amount_suppliers_purchase_order)) {
            $table .= '<td style="'.$width11.'text-align: center;font-weight: bold;">'._l('amount_suppliers_vnd').'</td>';
        }
        if(isset($item_note_purchase_order)) {
            $table .= '<td style="'.$width12.'text-align: center;font-weight: bold;">'._l('note').'</td>';
        }
        $table .= '</tr>
                </thead>
                <tbody>';
        $sum_quantity = 0;
        $sum_quantity_suppliers = 0;
        $sum_price_expected = 0;
        $sum_price_suppliers = 0;
        $sum_total_expected = 0;
        $sum_promotion_expected = 0;
        $sum_total_suppliers = 0;
        foreach ($dataSub as $key => $value) {
            $table .= '<tr>';
            $dataItem = $this->invoice_items_model->get_full_item($value['product_id'],$value['type']);
            $table .= '<td style="'.$width1.'text-align: center;">'.++$key.'</td>';
            $table .= '<td style="'.$width2.'text-align: left;">'.$dataItem->name.'</td>';
            
            if(isset($item_quantity_purchase_order)) {
                $table .= '<td style="'.$width3.'text-align: center;">'.number_format($value['quantity']).'</td>';
                $sum_quantity += $value['quantity'];
            }
            if(isset($item_quantity_suppliers_purchase_order)) {
                $table .= '<td style="'.$width4.'text-align: center;">'.number_format($value['quantity_suppliers']).'</td>';
                $sum_quantity_suppliers += $value['quantity_suppliers'];
            }
            if(isset($item_unit_purchase_order)) {
                $table .= '<td style="'.$width5.'text-align: center;">'.$dataItem->unit_name.'</td>';
            }
            if(isset($item_price_expected_purchase_order)) {
                $table .= '<td style="'.$width6.'text-align: center;">'.number_format($value['price_expected']).'</td>';
                $sum_price_expected += $value['price_expected'];
            }
            if(isset($item_price_suppliers_purchase_order)) {
                $table .= '<td style="'.$width7.'text-align: center;">'.number_format($value['price_suppliers']).'</td>';
                $sum_price_suppliers += $value['price_suppliers'];
            }
            if(isset($item_amount_expected_purchase_order)) {
                $table .= '<td style="'.$width8.'text-align: right;">'.number_format($value['total_expected']).'</td>';
                $sum_total_expected += $value['total_expected'];
            }
            if(isset($item_promotion_suppliers_purchase_order)) {
                $table .= '<td style="'.$width9.'text-align: right;">'.number_format($value['promotion_expected']).'</td>';
                $sum_promotion_expected += $value['promotion_expected'];
            }
            if(isset($item_tax_purchase_order)) {
                $table .= '<td style="'.$width10.'text-align: center;">'.number_format($value['tax_rate']).' %</td>';
            }
            if(isset($item_amount_suppliers_purchase_order)) {
                $table .= '<td style="'.$width11.'text-align: right;">'.number_format($value['total_suppliers']).'</td>';
                $sum_total_suppliers += $value['total_suppliers'];
            }
            if(isset($item_note_purchase_order)) {
                $table .= '<td style="'.$width12.'text-align: center;">'.$value['note'].'</td>';
            }
            $table .= '</tr>';
        }
        $table .= '<tr>
                <td colspan="'.$dem_temp.'" style="text-align: center;font-weight: bold;">'._l('invoice_dt_table_heading_amount').'</td>';
        if(isset($item_quantity_purchase_order)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_quantity).'</td>';
        }
        if(isset($item_quantity_suppliers_purchase_order)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_quantity_suppliers).'</td>';
        }
        if(isset($item_unit_purchase_order)) {
            $table .= '<td></td>';
        }
        if(isset($item_price_expected_purchase_order)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_price_expected).'</td>';
        }
        if(isset($item_price_suppliers_purchase_order)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_price_suppliers).'</td>';
        }
        if(isset($item_amount_expected_purchase_order)) {
            $table .= '<td style="text-align: right;">'.number_format($sum_total_expected).'</td>';
        }
        if(isset($item_promotion_suppliers_purchase_order)) {
            $table .= '<td style="text-align: right;">'.number_format($sum_promotion_expected).'</td>';
        }
        if(isset($item_tax_purchase_order)) {
            $table .= '<td></td>';
        }
        if(isset($item_amount_suppliers_purchase_order)) {
            $table .= '<td style="text-align: right;">'.number_format($sum_total_suppliers).'</td>';
        }
        if(isset($item_note_purchase_order)) {
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
                            <span style="font-weight: bold;">Người Giao</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Người Nhận</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Thủ Kho</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                    </tr>
                </tbody>
            </table>';
        $data->content .= $table;
        $pdf      = print_pdf_L($data);
        $type     = 'I';
        $pdf->Output(slug_it('') . '.pdf', $type);
    }
}