<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flow_chart extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function add()
    {
        if($this->input->post()) {
            $data=$this->input->post();
            var_dump($data);die;
            $in = array(
                'solution' => $data['solution'],
            );
            $this->db->where('id',$id);
            $result = $this->db->update('tblwarranty',$in);
            if($result)
            {
                $message=_l('updated_successfuly');
                $alert_type='success';
            }
            echo json_encode(array(
                            'success' => $result,
                            'message' => $message,
                            'alert_type'=>$alert_type
                        ));
                        die;
        }
    }
    public function getData_main()
    {
        $get = get_table_where('tbldiagram_chart',array('id_parent'=>NULL));
        echo json_encode($get);
    }
    public function getData_sub($id_parent='')
    {
        $get = get_table_where('tbldiagram_chart',array('id_parent'=>$id_parent));
        echo json_encode($get);
    }
    public function test_login()
    {
        $this->load->view('admin/flow_chart/chart');
    }
}
