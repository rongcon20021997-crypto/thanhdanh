<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Supplier_quotes extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('supplier_quotes_model');
        $this->load->model('invoice_items_model');
        $this->load->model('purchases_model');
    }
    public function index()
    {
        if (!has_permission('supplier_quotes', '', 'view')) {
            if (!has_permission('supplier_quotes', '', 'create')) {
                access_denied('supplier_quotes');
            }
        }
        $data['title']          = _l('ch_supplier_quotes');
        $this->load->view('admin/supplier_quotes/manage', $data);
    }
    public function table()
    {
        if (!has_permission('supplier_quotes', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('supplier_quotes');
    }

    public function get_items($id='',$type='',$load = 0)
    {
        
        $items = $this->invoice_items_model->get_full_item($id,$type);
        if($load == 1)
        {
            $html='<option value=""></option>';
            foreach (get_options_search_cbo('items',$items->id,$type) as $key => $value) {
            $html.='<option selected value="'.$value['id'].'">'.$value['name'].'</option>';
            }
        $items->html = $html;
        }

        if($items->avatar == '') {
            $items->avatar = 'uploads/no-img.jpg';
        }
        echo json_encode($items);

    }
    public function get_items_order_import($id='',$type='',$id_quote='')
    {
        
        $items = $this->supplier_quotes_model->get_items_order($id,$type,$id_quote);
        
        $items->avatar = (file_exists($items->avatar) ? base_url($items->avatar) : (file_exists('uploads/materials/'.$items->avatar) ? base_url('uploads/materials/'.$items->avatar) : (file_exists('uploads/products/'.$items->avatar) ? base_url('uploads/products/'.$items->avatar) : base_url('assets/images/preview-not-available.jpg'))));
        
        echo json_encode($items);

    }    
    public function detail($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('supplier_quotes', '', 'create')) {
                    access_denied('supplier_quotes');
                }

                $data                 = $this->input->post();
                
                if(isset($data['items']) && count($data['items']) > 0)
                {
                    $id = $this->supplier_quotes_model->add($data);
                }
                
                if ($id) {
                    set_alert('success', _l('ch_added_successfuly'));
                    redirect(admin_url('supplier_quotes/detail/' . $id));
                }
            } else {
                if (!has_permission('supplier_quotes', '', 'edit')) {
                        access_denied('supplier_quotes');
                }
                $success = $this->supplier_quotes_model->update($this->input->post(), $id);
                if ($success == true) {
                    $this->load->model('misc_model');
                    $this->misc_model->changeRowNew_model('tblsupplier_quotes',$id);
                    
                    set_alert('success', _l('ch_updated_successfuly'));
                }
                redirect(admin_url('supplier_quotes/detail/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('ch_supplier_quotes_add');
        } else {
            $title = _l('ch_supplier_quotes_edit');
            $item = $this->supplier_quotes_model->get_full_edit($id);
            $data['items'] = $item;
        }
        $data['taxes'] = get_taxes_dropdown_template('',0);
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['suppliers'] = get_table_where('tblsuppliers');
        $data['cell_excel'] = get_table_where('tbl_cell_excel');
        $data['title'] = $title;
        $this->load->view('admin/supplier_quotes/detail', $data);
    }
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Delete Supplier quotes');
        }
        $response = $this->supplier_quotes_model->delete_supplier_quotes($id);
        $alert_type = 'warning';
        $message    = _l('Không thể xóa dữ liệu');
        if ($response) {
            $alert_type = 'success';
            $message    = _l('Xóa dữ liệu thành công');
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    } 
    public function update_status($value='')
    {
        if ($this->input->post()) {
            $id=$this->input->post('id');
            $status=$this->input->post('status');
            $supplier_quotes = get_table_where('tblsupplier_quotes',array('id'=>$id),'','row');
            $staff_id=get_staff_user_id();
            $date=date('Y-m-d H:i:s');
            $history_status = $supplier_quotes->history_status;
            $history_status.='|'.$staff_id.','.$date;
            $data =array(
                'history_status'=>$history_status,
                'status' => ($status+1),
            );
            $success=$this->supplier_quotes_model->update_status($id,$data);
        }
        if($success) {
            echo json_encode(array(
                'success' => $success,
                'message' => _l('ch_successful_approval')
            ));
        }
        else
        {
            echo json_encode(array(
                'success' => $success,
                'message' => _l('ch_successful_approval')
            ));
        }
        die;
    }  
    public function view_supplier_quotes($id = '')
    {
        $data['items'] = $this->supplier_quotes_model->get_full_edit($id);
        $this->load->view('admin/supplier_quotes/view_modal',$data);
    }
    public function detail_create($id_rfq = '',$supplier_id = '',$type ='0')
    {
        if ($this->input->post()) {

                if (!has_permission('supplier_quotes', '', 'create')) {
                    access_denied('supplier_quotes');
                }

                $data                 = $this->input->post();
                
                if(isset($data['items']) && count($data['items']) > 0)
                {
                    if(!empty($supplier_id)&&($supplier_id > 0))
                    {
                        $data['suppliers_id']=$supplier_id;
                    }
                    $id = $this->supplier_quotes_model->add($data,$id_rfq);
                }
                
                if ($id) {
                    set_alert('success', _l('ch_added_successfuly'));
                    redirect(admin_url('supplier_quotes/detail/' . $id));
                }
        }
        
        $title = _l('ch_supplier_quotes_add');
        
        if($type == 1)
        {
        $data['id_purchases'] = $id_rfq;   
        $data['items_purchases'] = get_table_where('tblpurchases_items',array('purchases_id'=>$id_rfq)); 
        }
        else
        {
        $data['items_ask'] = get_table_where('tblrfq_ask_price_items',array('id_rfq_ask_price'=>$id_rfq,'suppliers_id'=>$supplier_id));
        $data['id_rfq'] = $id_rfq;
        $data['supplier_id'] = $supplier_id;
        }
        $data['taxes'] = get_taxes_dropdown_template('',0);
        $type_items = get_table_where('tbltype_items',array('active'=>1));
        $count = 0;
        $data['type_items'][0] = array('type'=>'-1','name'=>_l('task_list_all'));
        foreach ($type_items as $key => $value) {
            $count++;
            $data['type_items'][$count] = $value;
        }
        $data['suppliers'] = get_table_where('tblsuppliers');
        $data['title'] = $title;
        $this->load->view('admin/supplier_quotes/detail_create', $data);
    }
    public function add_tagert($id='',$idquote='')
    {
       if ($this->input->post()) {
       $success = $this->supplier_quotes_model->add_targert($this->input->post(),$id,$idquote);
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
                $success = false;
                $message = _l('ch_added_successfuly_not', _l('ch_evaluation_criteria'));
        }
        echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));die;
    }
    public function evaluate_modal_view($id='',$supplier_id,$idquote)
    {
        $data['ask_price'] = $id;
        $data['idquote'] = $idquote;
        $data['targert'] = $this->purchases_model->get_targert($id);
        $data['supplier_id'] = $supplier_id;
        $data['title'] = 'ch_evaluation_criteria';
        $this->load->view('admin/supplier_quotes/evaluate', $data);
    }
    public function import_items()
    {
        if ($this->input->post()) {
            $message='';
            $items = $this->input->post();
            var_dump($_FILES['file_import']['name']);die;
                if (isset($_FILES['file_import']['name']) && $_FILES['file_import']['name'] != '') {
                $tmpFilePath = $_FILES['file_import']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['file_import']['name'], PATHINFO_EXTENSION));
                $type = $_FILES["file_import"]["type"];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    $newFilePath = TEMP_FOLDER . $_FILES['file_import']['name'];
                    if (!file_exists(TEMP_FOLDER)) {
                        mkdir(TEMP_FOLDER, 777);
                    }                   
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $import_result = true;
                        $fd            = fopen($newFilePath, 'r');
                        $rows          = array();
                        if($ext == 'csv') {
                            while ($row = fgetcsv($fd)) {
                                $rows[] = $row;
                            }
                        }
                        else if($ext == 'xlsx' || $ext == 'xls') {
                            if($type == "application/octet-stream" || $type == "application/vnd.ms-excel" || $type == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                                require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
                                $inputFileType = PHPExcel_IOFactory::identify($newFilePath);

                                $objReader = PHPExcel_IOFactory::createReader($inputFileType);

                                $objReader->setReadDataOnly(true);

                                /**  Load $inputFileName to a PHPExcel Object  **/
                                $objPHPExcel =           $objReader->load($newFilePath);
                                $allSheetName       = $objPHPExcel->getSheetNames();
                                $objWorksheet       = $objPHPExcel->setActiveSheetIndex(0);
                                $highestRow         = $objWorksheet->getHighestRow();
                                $highestColumn      = $objWorksheet->getHighestColumn();

                                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                                if($items['number_end'] < $highestRow)
                                {
                                    $highestRow = $items['number_end'];
                                }
                                for ($row = $items['number']; $row <= $highestRow; ++$row) {
                                    for ($col = 0; $col < $highestColumnIndex; ++$col) {
                                        $value                     = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                                        $rows[$row - 2][$col] = $value;
                                    }
                                }
                            }
                        }
                        else {
                            fclose($fd);
                            unlink($newFilePath);
                            redirect('/');
                        }
                        fclose($fd);
                        $data['total_rows_post'] = count($rows);
                        unlink($newFilePath);
                        $backup_rows = $rows;
                        $fetch_columns_step = true;
                        $fetch_product_step = false;
                        $columns_found = 0;
                        $product_count = 0;
                        $data = [];
                        $data_ok = true;
                        $reason = "";
                        $dem_temp = 0;
                        $alert['success'] = 0;
                        $alert['fail'] = 0;
                        $fail=array();;
                                foreach ($rows as $key => $row) {
                                 $_data[$key]['type'] = $row[($items['type_itemss']-1)];
                                 $_data[$key]['name'] = $row[($items['name']-1)];
                                 $_data[$key]['unit'] = $row[($items['unit']-1)];
                                     if($_data[$key]['type'] == 1)
                                     {
                                        $item = get_table_where('tblitems',array('name'=>trim($_data[$key]['name'])),'','row');
                                        if($item)
                                        {
                                        $_data[$key]['id_product'] = $item->id;
                                        $_data[$key]['unit'] = $item->id; 
                                        $_data[$key]['quantity'] = $row[($items['quantity']-1)];
                                        $_data[$key]['cost'] = $row[($items['cost']-1)];
                                        $_data[$key]['tax'] = $row[($items['tax']-1)];
                                        $dem_temp++;
                                        }else
                                        {
                                            $fail[$key]['type'] = $row[($items['type_itemss']-1)];
                                            $fail[$key]['name'] = $row[($items['name']-1)];
                                            $fail[$key]['unit'] = $row[($items['unit']-1)];
                                            $fail[$key]['quantity'] = $row[($items['quantity']-1)];
                                            $fail[$key]['cost'] = $row[($items['cost']-1)];
                                            $fail[$key]['tax'] = $row[($items['tax']-1)];
                                            continue;
                                        }
                                     }
                                  }
                        $message = "Có  " . $dem_temp . " được import.";
                        }
                    }
                }
                echo json_encode(array(
                    'message' => $message
                ));die;
        }
    }
    public function print_pdf($id='')
    {
        ob_start();
        $data = new stdClass();
        $dataField = get_table_where('tbl_field_pdf',array('parent_field'=>'supplier_quotes'),'','row');
        $dataMain = get_table_where('tblsupplier_quotes',array('id'=>$id),'','row');
        $dataSub = get_table_where('tblsupplier_quote_items',array('id_supplier_quotes'=>$id));
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
        $data->content .= '<span style="text-align: center;font-size: 20px;font-weight: bold;">BÁO GIÁ NHÀ CUNG CẤP</span><br><br>';
        $data->content .= '<span style="text-align: right;font-style: italic;">'._l('ch_code_p').': '.$dataMain->prefix.'-'.$dataMain->code.'</span><br>';
        $data->content .= '<span style="text-align: right;font-style: italic;">'._l('ch_date_p').': '._d($dataMain->date).'</span><br><br>';
        $data->content .= '
            <span style="font-weight: bold;">'._l('ch_staff_p').': </span><span>'.get_staff_full_name($dataMain->staff_create).'</span><br>
            <span style="font-weight: bold;">'._l('ch_note_t').': </span><span>'.$dataMain->note.'</span><br><br>
        ';

        $widthStt = '';
        $widthItem = '';
        $widthUnit = '';
        $widthQuantity = '';
        $widthPrice = '';
        $widthAmount = '';
        $widthTax = '';
        $widthEstimate = '';
        $widthNote = '';
        $dem_temp = 2;
        if(isset($dataField->arr_field)) {
            $arr = explode(',', $dataField->arr_field);
            foreach ($arr as $key => $value) {
                if($value == 'item_unit_supplier_quotes') {
                    $item_unit_supplier_quotes = true;
                    $dem_temp = 3;
                }
                if($value == 'item_quantity_supplier_quotes') {
                    $item_quantity_supplier_quotes = true;
                }
                if($value == 'item_price_supplier_quotes') {
                    $item_price_supplier_quotes = true;
                }
                if($value == 'item_amount_supplier_quotes') {
                    $item_amount_supplier_quotes = true;
                }
                if($value == 'item_tax_supplier_quotes') {
                    $item_tax_supplier_quotes = true;
                }
                if($value == 'item_estimate_total_supplier_quotes') {
                    $item_estimate_total_supplier_quotes = true;
                }
                if($value == 'item_note_supplier_quotes') {
                    $item_note_supplier_quotes = true;
                }
            }
            if(isset($item_unit_supplier_quotes) && isset($item_quantity_supplier_quotes) && isset($item_price_supplier_quotes) && isset($item_amount_supplier_quotes) && isset($item_tax_supplier_quotes) && isset($item_estimate_total_supplier_quotes) && isset($item_note_supplier_quotes)) {
                $widthStt = 'width: 5%;';
                $widthItem = 'width: 25%;';
                $widthUnit = 'width: 10%;';
                $widthQuantity = 'width: 10%;';
                $widthPrice = 'width: 10%;';
                $widthAmount = 'width: 10%;';
                $widthTax = 'width: 10%;';
                $widthEstimate = 'width: 10%;';
                $widthNote = 'width: 10%;';
            }
        }
        $table = '
            <table class="table table-bordered" border="1" width="100%">
                <thead>
                    <tr>
                        <td style="'.$widthStt.'text-align: center;font-weight: bold;">'._l('STT').'</td>
        ';
        $table .= '<td style="'.$widthItem.'text-align: center;font-weight: bold;">'._l('ch_items_name_t').'</td>';
        
        if(isset($item_unit_supplier_quotes)) {
            $table .= '<td style="'.$widthUnit.'text-align: center;font-weight: bold;">'._l('item_unit').'</td>';
        }
        if(isset($item_quantity_supplier_quotes)) {
            $table .= '<td style="'.$widthQuantity.'text-align: center;font-weight: bold;">'._l('item_quantity').'</td>';
        }
        if(isset($item_price_supplier_quotes)) {
            $table .= '<td style="'.$widthPrice.'text-align: center;font-weight: bold;">'._l('price').'</td>';
        }
        if(isset($item_amount_supplier_quotes)) {
            $table .= '<td style="'.$widthAmount.'text-align: center;font-weight: bold;">'._l('amount').'</td>';
        }
        if(isset($item_tax_supplier_quotes)) {
            $table .= '<td style="'.$widthTax.'text-align: center;font-weight: bold;">'._l('tax').'</td>';
        }
        if(isset($item_estimate_total_supplier_quotes)) {
            $table .= '<td style="'.$widthEstimate.'text-align: center;font-weight: bold;">'._l('estimate_total').'</td>';
        }
        if(isset($item_note_supplier_quotes)) {
            $table .= '<td style="'.$widthNote.'text-align: center;font-weight: bold;">'._l('note').'</td>';
        }
        $table .= '</tr>
                </thead>
                <tbody>';
        $sum_quantity = 0;
        $sum_price = 0;
        $sum_amount = 0;
        $sum_tax = 0;
        $sum_estimate = 0;
        foreach ($dataSub as $key => $value) {
            $table .= '<tr>';
            $dataItem = $this->invoice_items_model->get_full_item($value['product_id'],$value['type']);
            $table .= '<td style="'.$widthStt.'text-align: center;">'.++$key.'</td>';
            $table .= '<td style="'.$widthItem.'text-align: left;">'.$dataItem->name.'</td>';
            
            if(isset($item_unit_supplier_quotes)) {
                $table .= '<td style="'.$widthUnit.'text-align: center;">'.$dataItem->unit_name.'</td>';
            }
            if(isset($item_quantity_supplier_quotes)) {
                $table .= '<td style="'.$widthQuantity.'text-align: center;">'.number_format($value['quantity']).'</td>';
                $sum_quantity += $value['quantity'];
            }
            if(isset($item_price_supplier_quotes)) {
                $table .= '<td style="'.$widthPrice.'text-align: center;">'.number_format($value['unit_cost']).'</td>';
                $sum_price += $value['unit_cost'];
            }
            if(isset($item_amount_supplier_quotes)) {
                $table .= '<td style="'.$widthAmount.'text-align: center;">'.number_format($value['unit_cost']).'</td>';
                $sum_amount += $value['unit_cost'];
            }
            if(isset($item_tax_supplier_quotes)) {
                $table .= '<td style="'.$widthTax.'text-align: center;">'.number_format($value['tax_rate']).' %</td>';
                $sum_tax += $value['subtotal'] - $value['unit_cost'];
            }
            if(isset($item_estimate_total_supplier_quotes)) {
                $table .= '<td style="'.$widthEstimate.'text-align: right;">'.number_format($value['subtotal']).'</td>';
                $sum_estimate += $value['subtotal'];
            }
            if(isset($item_note_supplier_quotes)) {
                $table .= '<td style="'.$widthNote.'text-align: center;">'.$value['note'].'</td>';
            }
            $table .= '</tr>';
        }
        $table .= '<tr>
                <td colspan="'.$dem_temp.'" style="text-align: center;font-weight: bold;">'._l('invoice_dt_table_heading_amount').'</td>';
        if(isset($item_quantity_supplier_quotes)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_quantity).'</td>';
        }
        if(isset($item_price_supplier_quotes)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_price).'</td>';
        }
        if(isset($item_amount_supplier_quotes)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_amount).'</td>';
        }
        if(isset($item_tax_supplier_quotes)) {
            $table .= '<td style="text-align: center;">'.number_format($sum_tax).'</td>';
        }
        if(isset($item_estimate_total_supplier_quotes)) {
            $table .= '<td style="text-align: right;">'.number_format($sum_estimate).'</td>';
        }
        if(isset($item_note_supplier_quotes)) {
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
                            <span style="font-weight: bold;">Trưởng Bộ Phận</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Lãnh Đạo</span><br>
                            <span>(ký, ghi rõ họ tên)</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">Nhà Cung Cấp</span><br>
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

