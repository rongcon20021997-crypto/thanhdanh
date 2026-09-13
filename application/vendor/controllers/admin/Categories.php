<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Categories extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('category_model');

    }
    public function index()
    {
        if (!has_permission('categories', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('categories', '', 'create')) {
                access_denied('categories');
            }
        }
        $data['categories'] = [];
        $this->category_model->get_by_id(0,$data['categories']);
        $full_categories = $this->category_model->get_full_detail();
        $data['full_categories'] = $full_categories;
        $data['title']          = _l('ch_categories');
        $this->load->view('admin/categories/manage', $data);
    }
    public function get_exist($id='')
    {

        $items = get_table_where('tblitems',array('category_id'=>$id),'','row');
        if(!empty($items))
        {
            echo json_encode(true);die;
        }else
        {
            $parent = get_table_where('tblcategories',array('category_parent'=>$id),'','row');
            if(!empty($parent))
            {
            echo json_encode(true);die;    
            }
            $success = $this->db->delete('tblcategories',array('id'=>$id));

                if ($success) {
                    $success = true;
                    $message = _l('ch_delete_successfuly', _l('ch_categories'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));die;
        }
    }
    public function table($value='')
    {
        if (!has_permission('categories', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('categories_items');
    }
    public function delete_categories()
    {
      if ($this->input->post()) {
            $message = '';
                $id = $this->category_model->delete_categories($this->input->post(NULL, FALSE));
                if ($id) {
                    $success = true;
                    $message = _l('ch_delete_successfuly', _l('ch_categories'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            die;
        }
    }
    public function add_category()
    {
        if ($this->input->post()) {
            $message = '';
                $id = $this->category_model->add_category($this->input->post(NULL, FALSE));
                if ($id) {
                    $success = true;
                    $message = _l('ch_added_successfuly', _l('ch_categories'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            die;
        }
    }
    public function update_category($id="")
    {
        if($id!=""){
            $message    = '';
            $alert_type = 'warning';
            if ($this->input->post()) {
                $success = $this->category_model->update_category($this->input->post(), $id);
                if ($success) {
                    $message = _l('ch_updated_successfuly', _l('ch_categories'));
                };
            }
            echo json_encode(array(
                'success' => $success,
                'message' => $message
            ));
        }
        else
        {
            if ($this->input->post()) {
                $success = $this->category_model->add_category($this->input->post());
                if ($success) {
                    $alert_type = 'success';
                    $message = _l('ch_added_successfuly', _l('ch_categories'));
                }
            }
            echo json_encode(array(
                'alert_type' => $alert_type,
                'message' => $message
            ));
        }
        die;
    }

    public function capacity()
    {
        $data['tnh'] = true;
        $data['title'] = _l('tnh_categories_capacity');
        $this->load->view('admin/categories/capacity', $data);
    }

    public function add_capacity()
    {
        $data = [];
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_capacity.code]');
            if ($this->form_validation->run() == true)
            {
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $note = $this->input->post('note');

                $options = [
                    'name' => $name,
                    'code' => $code,
                    'note' => $note,
                ];

                $id = $this->category_model->insertCapacity($options);
                if ($id) {
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    $data['result'] = 0;
                    $data['message'] = lang('fail');
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
            }
            echo json_encode($data);
            return;
        } else {
            $this->load->view('admin/categories/add_capacity', $data);
        }
    }

    public function edit_capacity($id)
    {
        $data = [];
        $capacity = $this->category_model->rowCapacity($id);
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            if ($capacity['code'] != $this->input->post('code'))
            {
                $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_capacity.code]');
            }
            if ($this->form_validation->run() == true)
            {
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $note = $this->input->post('note');

                $options = [
                    'name' => $name,
                    'code' => $code,
                    'note' => $note,
                ];

                $id = $this->category_model->updateCapacity($id, $options);
                if ($id) {
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    $data['result'] = 0;
                    $data['message'] = lang('fail');
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
            }
            echo json_encode($data);
            return;
        } else {
            $data['capacity'] = $capacity;
            $this->load->view('admin/categories/edit_capacity', $data);
        }
    }

    function getCapacity()
    {
        $this->datatables->select("
            tbl_capacity.id as id,
            tbl_capacity.code as code,
            tbl_capacity.name as name,
            tbl_capacity.note as note,
            ", FALSE)
        ->from('tbl_capacity');

        $this->datatables->add_column('actions', '
            <div>
                <a class="tnh-modal btn btn-success btn-icon" data-tnh="modal" data-toggle="modal" data-target="#myModal" href="'.base_url().'admin/categories/edit_capacity/$1"><i class="fa fa-pencil"></i></a>
                <button type="button" class="btn btn-danger po btn-icon" data-container="body" data-html="true" data-toggle="popover" data-placement="bottom" data-content="
                        <button href=\''.base_url('admin/categories/delete_capacity/$1').'\' class=\'btn btn-danger po-delete-json\'>'.lang('delete').'</button>
                        <button class=\'btn btn-default po-close\'>'.lang('close').'</button>
                    "><i class="fa fa-remove"></i></button>
            </div>
        ', 'id');
        $result = json_decode($this->datatables->generate());
        echo (json_encode($result));
    }

    function delete_capacity($id)
    {
        $data = [];
        if ($id) {
            // if (!$this->items_model->checkExistCategory($id)) {
            //     $data['result'] = 0;
            //     $data['message'] = lang('tnh_exist_not_delete');
            //     echo json_encode($data);
            //     return;
            // }
            // return;
            if ($this->category_model->deleteCapacity($id)) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
        } else {
            $data['result'] = 0;
            $data['message'] = lang('fail');
        }
        echo json_encode($data);
    }


    public function machines()
    {
        $data['tnh'] = true;
        $data['title'] = _l('tnh_categories_machines');
        $this->load->view('admin/categories/machines', $data);
    }

    public function add_machines()
    {
        $data = [];
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('status', lang("status"), 'required');
            $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_machines.code]');
            if ($this->form_validation->run() == true)
            {
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $product_in_month = number_unformat($this->input->post('product_in_month'));
                $status = $this->input->post('status');
                $specifications = $this->input->post('specifications');
                $note = $this->input->post('note');

                $options = [
                    'name' => $name,
                    'code' => $code,
                    'product_in_month' => $product_in_month,
                    'status' => $status,
                    'specifications' => $specifications,
                    'note' => $note,
                ];

                $id = $this->category_model->insertMachines($options);
                if ($id) {
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    $data['result'] = 0;
                    $data['message'] = lang('fail');
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
            }
            echo json_encode($data);
            return;
        } else {
            $this->load->view('admin/categories/add_machines', $data);
        }
    }

    public function edit_machines($id)
    {
        $data = [];
        $machines = $this->category_model->rowMachines($id);
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            if ($machines['code'] != $this->input->post('code'))
            {
                $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_machines.code]');
            }
            if ($this->form_validation->run() == true)
            {
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $product_in_month = number_unformat($this->input->post('product_in_month'));
                $status = $this->input->post('status');
                $specifications = $this->input->post('specifications');
                $note = $this->input->post('note');

                $options = [
                    'name' => $name,
                    'code' => $code,
                    'product_in_month' => $product_in_month,
                    'status' => $status,
                    'specifications' => $specifications,
                    'note' => $note,
                ];

                $id = $this->category_model->updateMachines($id, $options);
                if ($id) {
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    $data['result'] = 0;
                    $data['message'] = lang('fail');
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
            }
            echo json_encode($data);
            return;
        } else {
            $data['machines'] = $machines;
            $this->load->view('admin/categories/edit_machines', $data);
        }
    }

    function getMachines()
    {
        $this->datatables->select("
            tbl_machines.id as id,
            tbl_machines.code as code,
            tbl_machines.name as name,
            tbl_machines.product_in_month as product_in_month,
            tbl_machines.status as status,
            tbl_machines.specifications as specifications,
            tbl_machines.note as note,
            ", FALSE)
        ->from('tbl_machines');

        $this->datatables->add_column('actions', '
            <div>
                <a class="tnh-modal btn btn-primary btn-icon tip" data-tnh="modal" title="'.lang('history').'" data-toggle="modal" data-target="#myModal" href="'.base_url().'admin/categories/history_machines/$1"><i class="fa fa-history"></i></a>
                <a class="tnh-modal btn btn-success btn-icon tip" title="'.lang('edit').'"  data-tnh="modal" data-toggle="modal" data-target="#myModal" href="'.base_url().'admin/categories/edit_machines/$1"><i class="fa fa-pencil"></i></a>
                <button type="button" class="btn btn-danger po btn-icon tip" title="'.lang('delete').'" data-container="body" data-html="true" data-toggle="popover" data-placement="bottom" data-content="
                        <button href=\''.base_url('admin/categories/delete_machines/$1').'\' class=\'btn btn-danger po-delete-json\'>'.lang('delete').'</button>
                        <button class=\'btn btn-default po-close\'>'.lang('close').'</button>
                    "><i class="fa fa-remove"></i></button>
            </div>
        ', 'id');
        $result = json_decode($this->datatables->generate());
        echo (json_encode($result));
    }

    function delete_machines($id)
    {
        $data = [];
        if ($id) {
            // if (!$this->items_model->checkExistCategory($id)) {
            //     $data['result'] = 0;
            //     $data['message'] = lang('tnh_exist_not_delete');
            //     echo json_encode($data);
            //     return;
            // }
            // return;
            if ($this->category_model->deleteMachines($id)) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
        } else {
            $data['result'] = 0;
            $data['message'] = lang('fail');
        }
        echo json_encode($data);
    }

    function searchMachines()
    {
        $data = [];
        if ($this->input->get())
        {
            $q = $this->input->get('q');
            $limit = 50;
            $data = $this->category_model->searchMachines($q, $limit);
        }
        echo json_encode($data);
    }

    function history_machines($id)
    {
        $data['id'] = $id;
        $this->load->view('admin/categories/history_machines', $data);
    }

    function getHistoryMachines()
    {
        $machine_id = $this->input->post('machine_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $this->datatables->select("
            tbl_history_machines.date as date,
            tbl_history_machines.production_id as production,
            tbl_products.name as product_name,
            tbl_machines.name as machine_name,
            tbl_history_machines.time_used as time_used,
            0 as time_rest
            ", FALSE)
        ->from('tbl_history_machines')
        ->join('tbl_products', 'tbl_products.id = tbl_history_machines.product_id')
        ->join('tbl_machines', 'tbl_machines.id = tbl_history_machines.machine_id');

        $this->datatables->where('tbl_history_machines.machine_id', $machine_id);
        if ($start_date) {
            $this->datatables->where('DATE_FORMAT(tbl_history_machines.date, "%Y-%m-%d") >=', to_sql_date($start_date));
        }
        if ($end_date) {
            $this->datatables->where('DATE_FORMAT(tbl_history_machines.date, "%Y-%m-%d") <=', to_sql_date($end_date));
        }
        echo $this->datatables->generate();
    }

    public function packaging()
    {
        $data['tnh'] = true;
        $data['title'] = _l('tnh_categories_packaging');
        $this->load->view('admin/categories/packaging', $data);
    }

    public function add_packaging()
    {
        $data = [];
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_packaging.code]');
            if ($this->form_validation->run() == true)
            {
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $constitutive = $this->input->post('constitutive');
                $note = $this->input->post('note');

                $options = [
                    'name' => $name,
                    'code' => $code,
                    'constitutive' => $constitutive,
                    'note' => $note,
                ];

                $id = $this->category_model->insertPackaging($options);
                if ($id) {
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    $data['result'] = 0;
                    $data['message'] = lang('fail');
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
            }
            echo json_encode($data);
            return;
        } else {
            $this->load->view('admin/categories/add_packaging', $data);
        }
    }

    public function edit_packaging($id)
    {
        $data = [];
        $packaging = $this->category_model->rowPackaging($id);
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            if ($packaging['code'] != $this->input->post('code'))
            {
                $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_packaging.code]');
            }
            if ($this->form_validation->run() == true)
            {
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $constitutive = $this->input->post('constitutive');
                $note = $this->input->post('note');

                $options = [
                    'name' => $name,
                    'code' => $code,
                    'constitutive' => $constitutive,
                    'note' => $note,
                ];

                $id = $this->category_model->updatePackaging($id, $options);
                if ($id) {
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    $data['result'] = 0;
                    $data['message'] = lang('fail');
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
            }
            echo json_encode($data);
            return;
        } else {
            $data['packaging'] = $packaging;
            $this->load->view('admin/categories/edit_packaging', $data);
        }
    }

    function getPackaging()
    {
        $this->datatables->select("
            tbl_packaging.id as id,
            tbl_packaging.code as code,
            tbl_packaging.name as name,
            tbl_packaging.constitutive as constitutive,
            tbl_packaging.note as note,
            ", FALSE)
        ->from('tbl_packaging');

        $this->datatables->add_column('actions', '
            <div>
                <a class="tnh-modal btn btn-success btn-icon" data-tnh="modal" data-toggle="modal" data-target="#myModal" href="'.base_url().'admin/categories/edit_packaging/$1"><i class="fa fa-pencil"></i></a>
                <button type="button" class="btn btn-danger po btn-icon" data-container="body" data-html="true" data-toggle="popover" data-placement="bottom" data-content="
                        <button href=\''.base_url('admin/categories/delete_packaging/$1').'\' class=\'btn btn-danger po-delete-json\'>'.lang('delete').'</button>
                        <button class=\'btn btn-default po-close\'>'.lang('close').'</button>
                    "><i class="fa fa-remove"></i></button>
            </div>
        ', 'id');
        $result = json_decode($this->datatables->generate());
        echo (json_encode($result));
    }

    function delete_packaging($id)
    {
        $data = [];
        if ($id) {
            // if (!$this->items_model->checkExistCategory($id)) {
            //     $data['result'] = 0;
            //     $data['message'] = lang('tnh_exist_not_delete');
            //     echo json_encode($data);
            //     return;
            // }
            // return;
            if ($this->category_model->deletePackaging($id)) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
        } else {
            $data['result'] = 0;
            $data['message'] = lang('fail');
        }
        echo json_encode($data);
    }

}