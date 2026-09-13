<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Suppliers extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('suppliers_model');
        $this->load->model('currencies_model');
        $this->load->model('dashboard_model');
        $this->load->model('purchases_model');
    }

    public function index()
    {
        if (!has_permission('suppliers', '', 'view')) {
            if (!has_permission('suppliers', '', 'create')) {
                access_denied('suppliers');
            }
        }
        $data['title']          = _l('ch_suppliers');
        $whereContactsLoggedIn = '';
        // if (!has_permission('customers', '', 'view')) {
        //     $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        // }
        $this->load->view('admin/suppliers/manage', $data);
    }
    public function items_supplier()
    {
       if (!has_permission('suppliers', '', 'view')) {
                access_denied('suppliers');
        }
        $data['title']          = _l('mainstream_items');
        $this->load->view('admin/suppliers/mainstream_items', $data); 
    }
    public function info_suppliers()
    {
       if (!has_permission('suppliers', '', 'view')) {
            if (!has_permission('suppliers', '', 'create')) {
                access_denied('suppliers');
            }
        }
        $data['type_form'] = [
            ['name' => 'input'],
            ['name' => 'date'],
            ['name' => 'datetime'],
            ['name' => 'checkbox'],
            ['name' => 'select'],
            ['name' => 'select multiple'],
            ['name' => 'radio'],
            // ['name' => 'password']
        ];
        $data['title']          = _l('ch_group_information_suppliers');
        $this->load->view('admin/suppliers/info_suppliers/manage', $data); 
    }
    public function table_info_suppliers()
    {
       if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_info_suppliers');
    }    
    public function get_info_suppliers($id='')
    {
        echo json_encode(get_table_where('tblsuppliers_info_group',array('id'=>$id),'','row'));
    }
    public function get_info_suppliers_datail($id='')
    {
        echo json_encode(get_table_where('tblsuppliers_info_detail',array('id'=>$id),'','row'));
    }   
    public function get_suppliers_info_detail_value($id='')
    {
        echo json_encode(get_table_where('tblsuppliers_info_detail_value',array('id'=>$id),'','row'));
    }     
    public function add_info_suppliers()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if(empty($data['id']))
            {
            $_data['name'] = $data['name'];
            $_data['color'] = $data['color'];
            $this->db->insert('tblsuppliers_info_group',$_data);
            $alert_type = 'success';
            $message    = _l('ch_added_successfuly');
            }else
            {
            $_data['name'] = $data['name'];
            $_data['color'] = $data['color'];
            $this->db->update('tblsuppliers_info_group',$_data,array('id'=>$data['id']));  
            $alert_type = 'success';
            $message    = _l('ch_updated_successfuly');  
            }
        }else
        {
            $alert_type = 'warning';
            $message    = _l('ch_no_updated_successfuly'); 
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            )); 
    }
    public function add_suppliers_info_detail_value()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if(empty($data['id_suppliers_info_detail_value']))
            {
            $_data['name'] = $data['name_detail_value'];
            $_data['id_info_detail'] = $data['id_info_suppliers_datails'];
            $this->db->insert('tblsuppliers_info_detail_value',$_data);
            $alert_type = 'success';
            $message    = _l('ch_added_successfuly');
            }else
            {
            $_data['name'] = $data['name_detail_value'];
            $this->db->update('tblsuppliers_info_detail_value',$_data,array('id'=>$data['id_suppliers_info_detail_value']));  
            $alert_type = 'success';
            $message    = _l('ch_updated_successfuly');  
            }
        }else
        {
            $alert_type = 'warning';
            $message    = _l('ch_no_updated_successfuly'); 
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            )); 
    }   
    public function add_info_suppliers_datail()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if(empty($data['id_info_suppliers_datail']))
            {
            if(empty($data['is_required']))
            {
                $data['is_required']=0;
            }
            $_data['name'] = $data['name_detail'];
            $_data['type_form'] = $data['type_form'];
            $_data['is_required'] = $data['is_required'];
            $_data['id_suppliers_info'] = $data['id_info_suppliers'];
            $this->db->insert('tblsuppliers_info_detail',$_data);
            $alert_type = 'success';
            $message    = _l('ch_added_successfuly');
            }else
            {
            $_data['name'] = $data['name_detail'];
            $_data['type_form'] = $data['type_form'];
            if(empty($data['is_required']))
            {
                $data['is_required']=0;
            }
            $_data['is_required'] = $data['is_required'];
            $this->db->update('tblsuppliers_info_detail',$_data,array('id'=>$data['id_info_suppliers_datail']));  
            $alert_type = 'success';
            $message    = _l('ch_updated_successfuly');  
            }
        }else
        {
            $alert_type = 'warning';
            $message    = _l('ch_no_updated_successfuly'); 
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            )); 
    }        
    public function all_contact_suppliers()
    {
        if (!has_permission('suppliers', '', 'view')) {
            if (!has_permission('suppliers', '', 'create')) {
                access_denied('suppliers');
            }
        }

        $data['title']          = _l('ch_all_contact_suppliers');
        $this->load->view('admin/suppliers/all_contact_supp', $data);
    }
    public function add_suppliers($value='')
    {

        if ($this->input->post()) {
            $data = $this->input->post();
            unset($data['items_combo']);
            unset($data['DataTables_Table_2_length']);
            if ($data['id'] == '') {
                if (!has_permission('suppliers', '', 'create')) {
                access_denied('suppliers');
                }
                $id      = $this->suppliers_model->add_suppliers($data);
                $message = $id ? _l('added_successfully', _l('ch_groups_suppliers')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['company'],
                ]);
            } else {
                if (!has_permission('suppliers', '', 'edit')) {
                access_denied('suppliers');
                }
            $success = $this->suppliers_model->edit_suppliers($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('ch_groups_suppliers'));
                }
                echo json_encode([
                    'id' => $data['id'],
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }
    public function get_suppliers($id='')
    {
        echo json_encode($this->suppliers_model->get_suppliers($id));
    }
    public function table()
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('suppliers');
    } 
    public function table_contacts()
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_contacts');
    }
    public function change_suppliers_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->suppliers_model->change_suppliers_status($id, $status);
        }
    }
    public function groups()
    {
        if (!is_admin()) {
            access_denied('Suppliers Groups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('suppliers_groups');
        }
        $data['title'] = _l('ch_groups_suppliers');
        $this->load->view('admin/suppliers/groups_manage', $data);
    }
    public function group()
    {
        if (!is_admin()) {
            access_denied('Suppliers Groups');
        }
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            unset($data['inline']);
            // var_dump($data);die;
            if (empty($data['id'])) {
                $id      = $this->suppliers_model->add_group($data);
                $message = $id ? _l('added_successfully', _l('ch_groups_suppliers')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['name'],
                ]);
            } else {
                $success = $this->suppliers_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('ch_groups_suppliers'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }
    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Suppliers Group');
        }
        if (!$id) {
            redirect(admin_url('suppliers/groups'));
        }
        $response = $this->suppliers_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('ch_groups_suppliers')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ch_groups_suppliers')));
        }
        redirect(admin_url('suppliers/groups'));
    }
    public function int_suppliers_view($edit='',$id='',$type = 2)
    {
        if(!empty($id))
        {

            $data['suppliers']=$this->suppliers_model->get_suppliers($id); 
            $data['title']=$data['suppliers']->company;
            if($edit != 'true')
            {
            $data['suppliers']->default_currency = $this->currencies_model->get($data['suppliers']->default_currency);
            }else
            {
            $data['title']=_l('ch_suppliers_edit_heading'); 

            }
            $data['suppliers']->contacts=$this->suppliers_model->get_suppliers_contacts($id);  
            $data['id'] = $id;
            $data['province'] = get_table_where('province', array('countries' => $data['suppliers']->country));
            $data['district'] = get_table_where('district', array('provinceid' => $data['suppliers']->city));
            $data['ward'] = get_table_where('ward', array('districtid' => $data['suppliers']->district));

        }else
        {
        $data['title']=_l('ch_suppliers_add_heading'); 
        }
        $data['info_suppliers'] =  get_table_where('tblsuppliers_info_group');
      
        $data['currencies'] = $this->currencies_model->get();
        $data['groups']         = $this->suppliers_model->get_groups();
        $data['openEdit'] = $edit;
        $data['type'] = $type;
        $data['items'] = get_table_where('tblitems');

        $this->load->view('admin/suppliers/suppliers_modal',$data);
    }
    public function int_suppliers_view_data($edit='',$id='',$type = 2)
    {
        if(!empty($id))
        {
            $data['supplierss']=$this->suppliers_model->get_suppliers($id); 
            $data['title']=$data['supplierss']->company;

            if($edit != 'true')
            {   
                $default_currency = $this->currencies_model->get_hau($data['supplierss']->default_currency);
                $data['supplierss']->default_currency = (!empty($default_currency) ? $default_currency->name : '');
                $data['province'] = get_table_where('province', array('countries' => $data['supplierss']->country));
            $data['district'] = get_table_where('district', array('provinceid' => $data['supplierss']->city));
            $data['ward'] = get_table_where('ward', array('districtid' => $data['supplierss']->district));
            }else
            {
                $data['title']=_l('ch_suppliers_edit_heading'); 

            }
            $data['supplierss']->contacts=$this->suppliers_model->get_suppliers_contacts($id);  
            $data['id'] = $id;

        }else
        {
        $data['title']=_l('ch_suppliers_add_heading'); 
        }
        $data['info_suppliers'] =  get_table_where('tblsuppliers_info_group');
        $data['currencies'] = $this->currencies_model->get();
        $data['groups']         = $this->suppliers_model->get_groups();
        $data['openEdit'] = $edit;
        $data['type'] = $type;
        $data['items'] = get_table_where('tblitems');

        $this->load->view('admin/suppliers/suppliers_modal_data',$data);
    }   
    public function table_mainstream_items($id)
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_mainstream_items',array('id'=>$id));
    }  
    public function table_history_ask_suppliert($id)
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_history_ask_suppliert',array('id'=>$id));
    }  
    public function table_history_quotes($id)
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_history_quotes',array('id'=>$id));
    }     
    public function table_history_order($id)
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_history_order',array('id'=>$id));
    }         
    public function table_mainstream_items_all()
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_mainstream_items_all');
    }      
    public function mainstream_items($id='')
    {
        $result=array(
            'alert_type'=>'danger',
            'message'=>'',
            'success'=>false
            );
        if(is_numeric($id))
        {  
        $data=$this->input->post();

        $this->db->select('id');
        $this->db->where('id_suppliers',$id);
        $this->db->where('id_items',$data['id_items']);
        $items = $this->db->get('tblmainstream_goods')->row();
        if($items == NULL)
        {
        $table = get_table_where('tbltype_items',array('type'=>$data['type']),'','row');
        if($data['type'] == 'items')
        {
        $this->db->select('price');    
        $this->db->where('id',$data['id_items']);
        $pruduct=$this->db->get('tblitems')->row();   
        }else
        {
        $this->db->select('price_sell as price');    
        $this->db->where('id',$data['id_items']);
        $pruduct=$this->db->get($table->table)->row();    
        }
        
        if($pruduct)
        {
        $_data['id_suppliers'] = $id;
        $_data['id_items'] =$data['id_items'];
        $_data['price'] = $pruduct->price;
        $_data['type'] = $data['type'];
        $response = $this->db->insert('tblmainstream_goods',$_data);
        }
        if($response)
            {
                $result=array(
                    'alert_type'=>'success',
                    'message'=>_l('Thêm sản phẩm thành công'),
                    'success'=>true
                    );
            }
        }
        else{
        $result=array(
                    'alert_type'=>'success',
                    'message'=>_l('Sản phẩm đã tồn tại'),
                    'success'=>true
                    );
    }
    }
        echo json_encode($result);        
    }
    public function delete_items($id)
    {  
            if(is_numeric($id))
            {
                $this->db->where('id',$id);
             
                if($this->db->delete('tblmainstream_goods')){
                     echo json_encode(array(
                            'success' => true,
                            'alert_type'=>'success',
                            'message' => 'Xóa dữ liệu thành công'
                        ));die();
                }
                else
                {
                    echo json_encode(array(
                            'success' => false,
                            'alert_type'=>'danger',
                            'message' => 'Xóa dữ liệu không thành công'
                        ));die();
                }
            }
            echo json_encode(array(
                            'success' => false,
                            'alert_type'=>'danger',
                            'message' => 'Xóa dữ liệu không thành công'
                        ));die();
    }
    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Delete Suppliers Group');
        }
        $response = $this->suppliers_model->delete_suppliers($id);
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
    public function delete_contact($id)
    {
        if (!is_admin()) {
            access_denied('Delete Suppliers Contact');
        }
        $response = $this->suppliers_model->delete_contact($id);
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
    public function table_contacts_all()
    {
       if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_contacts_all_suppliers');  
    }
    public function evaluation_criteria($value='')
    {
        if(!has_permission('suppliers', '', 'view')) {
            if (!has_permission('suppliers', '', 'create')) {
                access_denied('suppliers');
            }
        }
        $data['title']          = _l('ch_evaluation_criteria');
        $this->load->view('admin/suppliers/evaluation_criteria', $data);
    }
    public function add_evaluation_criteria()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if(empty($data['id']))
            {
            unset($data['id']);
            $_data['name'] = $data['name'];
            $_data['color'] = $data['color'];
            $this->db->insert('tblevaluation_criteria',$_data);
            $alert_type = 'success';
            $message    = _l('ch_added_successfuly');
            }else
            {
            $_data['name'] = $data['name'];
            $_data['color'] = $data['color'];
            $this->db->update('tblevaluation_criteria',$_data,array('id'=>$data['id']));  
            $alert_type = 'success';
            $message    = _l('ch_updated_successfuly');  
            }
        }else
        {
            $alert_type = 'warning';
            $message    = _l('ch_no_updated_successfuly'); 
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    }
    public function add_evaluation_criteria_children()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if(empty($data['id_evaluation_children']))
            {
            unset($data['id_evaluation_children']);
            $_data['name_children'] = $data['name_children'];
            $_data['id_evaluation'] = $data['id_evaluation'];
            $this->db->insert('tblevaluation_criteria_children',$_data);
            $alert_type = 'success';
            $message    = _l('ch_added_successfuly');
            }else
            {
            $_data['name_children'] = $data['name_children'];
            $this->db->update('tblevaluation_criteria_children',$_data,array('id'=>$data['id_evaluation_children']));  
            $alert_type = 'success';
            $message    = _l('ch_updated_successfuly');  
            }
        }else
        {
            $alert_type = 'warning';
            $message    = _l('ch_no_updated_successfuly'); 
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    }        
    public function delete_evaluation_criteria($id='')
    {   
        if (!is_admin()) {
            access_denied('Delete Suppliers Contact');
        }
        $this->db->where('id',$id);
        $response = $this->db->delete('tblevaluation_criteria');
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
    public function delete_evaluation_criteria_children($id='')
    {   
        if (!is_admin()) {
            access_denied('Delete Suppliers Contact');
        }
        $this->db->where('id',$id);
        $response = $this->db->delete('tblevaluation_criteria_children');
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
    public function get_evaluation_criteria($id='')
    {
        echo json_encode(get_table_where('tblevaluation_criteria',array('id'=>$id),'','row'));
    }
    public function get_evaluation_criteria_children($id='')
    {
        echo json_encode(get_table_where('tblevaluation_criteria_children',array('id'=>$id),'','row'));
    }    
    public function table_evaluation_criteria($value='')
    {
        if (!has_permission('suppliers', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('table_evaluation_criteria'); 
    }    
    //Hoàng CRM bổ xung dashboard
    public function dashboard_suppliers()
    {
        $data['title'] = _l('dashboard_suppliers');
        $tickets_awaiting_reply_by_status     = $this->dashboard_model->tickets_awaiting_reply_by_status();
        $tickets_awaiting_reply_by_department = $this->dashboard_model->tickets_awaiting_reply_by_department();

        $data['tickets_reply_by_status']              = json_encode($tickets_awaiting_reply_by_status);
        $data['tickets_awaiting_reply_by_department'] = json_encode($tickets_awaiting_reply_by_department);

        $data['tickets_reply_by_status_no_json']              = $tickets_awaiting_reply_by_status;
        $data['tickets_awaiting_reply_by_department_no_json'] = $tickets_awaiting_reply_by_department;

        $data['client_time_stats']   = json_encode($this->dashboard_model->client_time_status());
        $data['leads_time_stats']    = json_encode($this->dashboard_model->lead_time_status());
        $this->load->view('admin/suppliers/dashboard_suppliers', $data);
    }
    public function delete_info_suppliers($id = '')
    {
        if($id != '') {
        $response = $this->db->delete('tblsuppliers_info_group',array('id'=>$id));
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
    }
    public function delete_info_suppliers_detail($id = '')
    {
        $ktr = NULL;
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');
        $ktr = get_table_where('tblsuppliers_value',array('id_detail'=>$id),'','row');    
        if(empty($ktr)) {
        $response = $this->db->delete('tblsuppliers_info_detail',array('id'=>$id));
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');
        if ($response) {
            $this->db->delete('tblsuppliers_info_detail_value',array('id_info_detail'=>$id));
            $alert_type = 'success';
            $message    = _l('ch_delete');
        }
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    } 
    public function delete_info_suppliers_detail_value($id = '',$id_detail='')
    {
        $ktr = NULL;
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');
        $ktr = get_table_where('tblsuppliers_value',array('id_detail'=>$id_detail,'value'=>$id),'','row');    
        if(empty($ktr)) {
        $response = $this->db->delete('tblsuppliers_info_detail_value',array('id'=>$id));
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');
        if ($response) {
            $alert_type = 'success';
            $message    = _l('ch_delete');
        }
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
            ));
    }       
    public function get_html_evaluate($id = '',$stt = '')
    {
        if($id != '') {
            $data['id'] = $id;
            $data['stt'] = $stt;
            $dem_temp = 0;
            $main = get_table_where('tblevaluation_criteria');
            foreach ($main as $key => $value) {
                $data['dataMain'][$dem_temp]['id_main'] = $value['id'];
                $data['dataMain'][$dem_temp]['main'] = $value['name'];
                $sub = get_table_where('tblevaluation_criteria_children',array('id_evaluation'=>$value['id']));
                $dem_temp_sub = 0;
                foreach ($sub as $keySub => $valueSub) {
                    $data['dataMain'][$dem_temp]['sub'][$dem_temp_sub]['id_sub'] = $valueSub['id'];
                    $data['dataMain'][$dem_temp]['sub'][$dem_temp_sub]['name'] = $valueSub['name_children'];
                    $dem_temp_sub++;
                }
                $dem_temp++;
            }
            echo json_encode([
                'data' => $this->load->view('admin/suppliers/init_evaluate', $data, true),
                'data_left' => $this->load->view('admin/suppliers/init_evaluate_left', $data, true),
            ]);
        }
    }
    //end
}