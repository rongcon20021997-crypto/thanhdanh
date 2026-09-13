<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Units extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('unit_model');
    }
    /* Open also all taks if user access this /tasks url */
    public function index()
    {

        $this->list_units();
    }

    /* List all tasks */
    public function list_units()
    {

       
        $data['roles']=$this->unit_model->get_roles();
        // var_dump($data['roles']);die();
        $data['title'] = _l('item_unit');
        $this->load->view('admin/units/manage', $data);
    }
    public function table()
    {
        $this->app->get_table_data('units');
    }
    /* Get task data in a right pane */
    public function delete_unit($id)
    {
        if (!$id) {
            die('ch_no_items');
        }
        $success    = $this->unit_model->delete_unit($id);
        $alert_type = 'warning';
        $message    = _l('ch_no_delete');
        if ($success) {
            $alert_type = 'success';
            $message    = _l('ch_delete');
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message
        ));

    }
     public function add_unit()
    {
        if ($this->input->post()) {
            $message = '';
                $id = $this->unit_model->add_unit($this->input->post(NULL, FALSE));
                if ($id) {
                    $success = true;
                    $message = _l('added_successfuly', _l('als_units'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            die;
        }
    }
    public function update_unit($id="")
    {
        if($id!=""){
            $message    = '';
            $alert_type = 'warning';
            if ($this->input->post()) {
                $success = $this->unit_model->update_unit($this->input->post(), $id);
                if ($success) {
                    $message    = 'ch_updatee_items';
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
                $success = $this->unit_model->add_unit($this->input->post());
                if ($success) {
                    $alert_type = 'success';
                    $message    = 'ch_adde_items';
                }
            }
            echo json_encode(array(
                'alert_type' => $alert_type,
                'message' => $message
            ));
        }
        die;
    }



    public function get_row_unit($id)
    {
        echo json_encode($this->unit_model->get_row_unit($id));
    }


}
