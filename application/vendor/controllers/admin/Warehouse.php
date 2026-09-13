<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Warehouse extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        if (!has_permission('warehouse', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('warehouse', '', 'create')) {
                access_denied('warehouse');
            }
        }
        $data['group']          = get_table_where('tblgroup_warehouse');
        $data['title']          = _l('warehouse');
        $this->load->view('admin/warehouse/manage', $data);
    }
    public function detail($id='')
    {
        $data = $this->input->post();
        if($id == "") {
            $in = array(
                'id_group_warehouse'=>$data['id_group_warehouse'],
                'code'=>$data['code'],
                'name'=>$data['name'],
                'address'=>$data['address'],
                'note'=>$data['note']
            );
            $this->db->insert('tblwarehouse',$in);
            echo json_encode(array('success' => true, 'alert_type' => 'success', 'message' => _l('add_warehouse_success')));
        }
        else {
            $in = array(
                'id_group_warehouse'=>$data['id_group_warehouse'],
                'code'=>$data['code'],
                'name'=>$data['name'],
                'address'=>$data['address'],
                'note'=>$data['note']
            );
            $this->db->where('id',$id);
            $this->db->update('tblwarehouse',$in);
            echo json_encode(array('success' => true, 'alert_type' => 'success', 'message' => _l('edit_warehouse_success')));
        }
    }
    public function group()
    {
        if (!has_permission('warehouse', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('warehouse', '', 'create')) {
                access_denied('warehouse');
            }
        }
        $data['title']          = _l('kb_dt_group_name');
        $this->load->view('admin/warehouse/group', $data);
    }
    public function table_warehouse($value='')
    {
        if (!has_permission('warehouse', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('warehouse');
    }
    public function table_warehouse_group($value='')
    {
        if (!has_permission('warehouse', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('warehouse_group');
    }
    public function getData($id='')
    {
        $data = get_table_where('tblwarehouse',array('id'=>$id),'','row');
        echo json_encode($data);
    }
    public function getData_group($id='')
    {
        $data = get_table_where('tblgroup_warehouse',array('id'=>$id),'','row');
        echo json_encode($data);
    }
    public function group_detail($id='')
    {
        $data = $this->input->post();
        if($id == "") {
            $in = array(
                'code'=>$data['code'],
                'name'=>$data['name'],
            );
            $this->db->insert('tblgroup_warehouse',$in);
            echo json_encode(array('success' => true, 'alert_type' => 'success', 'message' => _l('add_warehouse_group_success')));
        }
        else {
            $in = array(
                'code'=>$data['code'],
                'name'=>$data['name'],
            );
            $this->db->where('id',$id);
            $this->db->update('tblgroup_warehouse',$in);
            echo json_encode(array('success' => true, 'alert_type' => 'success', 'message' => _l('edit_warehouse_group_success')));
        }
    }
    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('warehouse/group'));
        }
        $checkWarehouse = get_table_where('tblwarehouse',array('id_group_warehouse'=>$id),'','row');
        if(!$checkWarehouse) {
            $this->db->where('id',$id);
            $response = $this->db->delete('tblgroup_warehouse');
        }
        else {
            $response = false;
        }
        if ($response == true) {
            echo json_encode(array('success' => true, 'alert_type' => 'success', 'message' => _l('delete_warehouse_group')));
        } else {
            echo json_encode(array('success' => false, 'alert_type' => 'warning', 'message' => _l('problem_deleting')));
        }
    }
    public function delete_main($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('warehouse'));
        }
        $this->db->where('id',$id);
        $response = $this->db->delete('tblwarehouse');
        if ($response == true) {
            echo json_encode(array('success' => true, 'alert_type' => 'success', 'message' => _l('delete_warehouse')));
        } else {
            echo json_encode(array('success' => false, 'alert_type' => 'warning', 'message' => _l('problem_deleting')));
        }
    }
    public function localtion()
    {
        if (!has_permission('warehouse', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('warehouse', '', 'create')) {
                access_denied('warehouse');
            }
        }
        $data['warehouse']=get_table_where('tblwarehouse');
        $data['title']          = _l('warehouse_localtion_t');
        $this->load->view('admin/warehouse/warehouse_localtion', $data);
    }
    public function table_warehouse_localtion()
    {
        if (!has_permission('warehouse', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('warehouse_localtion');
    }
    public function add_location_warehouse()
    {
        if($this->input->post())
        {
            $data=$this->input->post();
            if(!empty($data['id']))
            {
                $data['child']=0;
                $kt_parent=get_table_where('tbllocaltion_warehouses',array('id_parent'=>$data['id']),'','row');
                if(empty($kt_parent))
                {
                    $data['child']=1;
                }
                $id=$data['id'];
                unset($data['id']);
                $this->db->where('id',$id);
                if($this->db->update('tbllocaltion_warehouses',$data))
                {
                    if(!empty($data['id_parent']))
                    {
                        $get_parent=get_table_where('tbllocaltion_warehouses',array('id'=>$data['id_parent']),'','row');
                        $name_parent=$get_parent->name_parent." <i class='fa fa-caret-right text-danger' aria-hidden='true'></i> ".$data['name'];
                    }
                    else
                    {
                        $name_parent=$data['name'];
                    }
                    $this->db->where('id',$id);
                    $this->db->update('tbllocaltion_warehouses',array('name_parent'=>$name_parent));

                    if(!empty($data['id_parent']))
                    {
                        $this->db->where('id',$data['id_parent']);
                        $this->db->update('tbllocaltion_warehouses', array('child' => 0));
                    }

                    $this->update_parents($id,$name_parent);
                    echo json_encode(array('success'=>true,'message'=>'Cập nhật dữ liệu thành công'));die();
                }
                echo json_encode(array('success'=>false,'message'=>'Cập nhật dữ liệu không thành công'));die();
            }
            else
            {
                unset($data['id']);
                $data['child']=1;
                $data['create_by']=get_staff_user_id();
                $data['date_create']=date('Y-m-d H:i:s');
                    if(empty($data['id_parent']))
                    {
                    $data['lever']=1;
                    }else
                    {
                        $lever = 1;
                        $parent = $data['id_parent'];
                             

                        while ($parent > 0) {
                           $ktr = get_table_where('tbllocaltion_warehouses',array('id'=>$parent,'warehouse'=>$data['warehouse']),'','row');
                           $parent = $ktr->id_parent;
                           $lever++; 

                        }
                        $data['lever'] = $lever;
                    }
                $this->db->insert('tbllocaltion_warehouses',$data);
                if($this->db->insert_id())
                {
                    if(!empty($data['id_parent']))
                    {
                        $this->db->update('tbllocaltion_warehouses',array('child'=>0),array('id'=>$data['id_parent']));
                    }
                    echo json_encode(array('success'=>true,'message'=>'Thêm dữ liệu thành công'));die();
                }
                echo json_encode(array('success'=>false,'message'=>'Thêm dữ liệu không thành công'));die();
            }
        }
    }
    // update lại tên cha của code
    public function update_parents($id_parent="",$name_parent="")
    {
          if(!empty($id_parent))
          {
              $this->db->where('id_parent',$id_parent);
              $localtion_warehouses=$this->db->get('tbllocaltion_warehouses')->result_array();
              foreach($localtion_warehouses as $key=>$value)
              {
                  $this->db->where('id',$value['id']);
                  $new_name_parent=(!empty($name_parent)?($name_parent." <i class='fa fa-caret-right text-danger' aria-hidden='true'></i> ".$value['name']):$value['name']);
                  $this->db->update('tbllocaltion_warehouses',array('name_parent'=>$new_name_parent));
                  $this->update_parents($value['id'],$new_name_parent);
              }
          }
    }
    public function updadte_all()
    {
        $this->db->where('(id_parent is null or id_parent = 0)');
        $localtion_parent=$this->db->get('tbllocaltion_warehouses')->result_array();


        foreach($localtion_parent as $key=>$value)
        {
            $this->db->where('id',$value['id']);
            $this->db->update('tbllocaltion_warehouses',array('name_parent'=>$value['name']));
            $this->update_parents($value['id'],$value['name']);
        }
    }
    public function change_warehouse_localtion_v2($id='')
    {   
        $parent = get_table_where('tbllocaltion_warehouses',array('id_parent'=>$id));
        if(!empty($parent))
        {
        foreach ($parent as $key => $value) {
        $this->db->where('id', $value['id']);
        $this->db->update('tbllocaltion_warehouses', [
            'status' => 2,
        ]);
        $this->change_warehouse_localtion_v2($value['id']);
        }
        }else
        {
            return false;
        }
    }
    public function change_warehouse_localtion_v3($id='')
    {   
        $parent = get_table_where('tbllocaltion_warehouses',array('id'=>$id),'','row');
        if(!empty($parent->id_parent))
        {
        $this->db->where('id', $parent->id_parent);
        $this->db->update('tbllocaltion_warehouses', [
            'status' => 0,
        ]);
        $this->change_warehouse_localtion_v3($parent->id_parent);
        }else
        {
            return false;
        }
    }
    public function change_warehouse_localtion_status($id, $status)
    {
        if($status == 0)
        {
            $status = 2;
        }elseif($status == 2)
        {
            $status = 0;
        }
        $this->db->where('id', $id);
        $this->db->update('tbllocaltion_warehouses', [
            'status' => $status,
        ]);
        $message ='Chuyển không thành công!';
        $success ='warning';
        if ($this->db->affected_rows() > 0) {
            if($status == 2)
            {
                $this->change_warehouse_localtion_v2($id);
            }elseif($status == 0)
            {
                $this->change_warehouse_localtion_v3($id);
            }

            log_activity('Warehouse localtion Status Changed [ID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

        $message ='Chuyển thành công!';
        $success ='success';
        }
        echo json_encode([
                    'alert_type' => $success,
                    'message' => $message,
                ]);die;
    }
    public function delete_location_warehouse()
    {
        $id=$this->input->post('id');
        $id_new=$this->input->post('id_new');
        if(!empty($id))
        {
            $list_id=array();
            $localtion=get_table_where('tbllocaltion_warehouses',array('id'=>$id),'','row');
            $list_id[]=$localtion->id;
            $this->get_list_id_child($localtion->id,$list_id);
            foreach($list_id as $key=>$value)
            {
                if($value==$id_new)
                {
                    echo json_encode(array('success'=>false,'message'=>'Xóa dữ liệu không thành công vì vị trí chuyển đến sẽ bị xóa'));die();
                }
            }

            $array_localtion_product=array();
            foreach($list_id as $key=>$value)
            {
                $product_warehouse=get_table_where('tblwarehouses_products',array('localtion'=>$value,'warehouse_id'=>$localtion->warehouse));
                foreach($product_warehouse as $k=>$v)
                {
                    if(!empty($v['product_id']))
                    {
                        if(empty($array_localtion_product[$v['product_id']]))
                        {
                            $array_localtion_product[$v['product_id']]=$v['product_quantity'];
                        }
                        else
                        {
                            $array_localtion_product[$v['product_id']]+=$v['product_quantity'];
                        }
                    }
                }
            }
            $localtion_warehouse_new=get_table_where('tbllocaltion_warehouses',array('id'=>$id_new),'','row');
            foreach($array_localtion_product as $key=>$value)
            {
                $kt_warehouse=get_table_where('tblwarehouses_products',array('localtion'=>$id_new,
                                                                                    'warehouse_id'=>$localtion_warehouse_new->warehouse,
                                                                                    'product_id'=>$key),'','row');
                if(empty($kt_warehouse))
                {
                    $this->db->insert('tblwarehouses_products',array('warehouse_id'=>$localtion_warehouse_new->warehouse,
                                                                        'localtion'=>$id_new,'product_id'=>$key,'product_quantity'=>$value));
                }
                else
                {
                    $this->db->where('id',$kt_warehouse->id);
                    $this->db->update('tblwarehouses_products',array('product_quantity'=>($kt_warehouse->product_quantity+$value)));
                }
            }

            $this->db->where_in('localtion',$list_id);
            $this->db->where('warehouse_id',$localtion_warehouse_new->warehouse);
            $this->db->delete('tblwarehouses_products');

            if(!empty($localtion->id_parent))
            {
                $this->db->where('id_parent',$localtion->id_parent);
                $this->db->where_not_in('id',$list_id);
                $kt_localtion_parent=$this->db->get('tbllocaltion_warehouses')->result_array();
                if(empty($kt_localtion_parent))
                {
                    $this->db->where('id',$localtion->id_parent);
                    $this->db->update('tbllocaltion_warehouses',array('child'=>0));
                }
            }

            $this->db->where_in('id',$list_id);
            if($this->db->delete('tbllocaltion_warehouses'))
            {
                echo json_encode(array('success'=>true,'message'=>'Xóa dữ liệu thành công'));die();
            }
        }
        echo json_encode(array('success'=>false,'message'=>'Xóa dữ liệu không thành công'));die();
    }
    public function get_localtion_warehouse($id="") // lấy vị trí
    {
        if(is_numeric($id))
        {
            $this->db->where('id',$id);
            $localtion_warehouses=$this->db->get('tbllocaltion_warehouses')->row();
            echo json_encode($localtion_warehouses);die();
        }
    }
    public function list_localtion() // lấy danh sách vị trí
    {
        $warehouse=$this->input->post('warehouse');
        $lever=$this->input->post('lever');
        $checked=$this->input->post('checked');

        if(!empty($warehouse))
        {
            echo get_localtion_warehouses(array('warehouse'=>$warehouse),$lever,$checked);die();
        }
    }
    public function detail_warehouse($id = '')
    {
        if (!has_permission('warehouse', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('warehouse', '', 'create')) {
                access_denied('warehouse');
            }
        }
        $data['localtion'] = get_localtion_warehouses(array('warehouse'=>$id));
        $data['type_items'] = get_table_where('tbltype_items',array('active'=>1));
        $data['id']=$id;
        $warehouse = get_table_where('tblwarehouse',array('id'=>$id),'','row');
        $data['title']          = _l('ch_warehouse_t').' '.$warehouse->name;
        $this->load->view('admin/warehouse/detail', $data);
    }
    public function table_warehouse_items($id='')
    {
        if (!has_permission('warehouse', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('warehouse_items',array('id'=>$id));
    }

}