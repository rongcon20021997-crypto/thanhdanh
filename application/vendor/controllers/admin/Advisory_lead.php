<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Advisory_lead extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /* List all leads */
    public function index()
    {
        $data['title'] = _l('cong_advisory_lead');
        $this->db->select(db_prefix().'procedure_client_detail.*');
        $this->db->join(db_prefix().'procedure_client', db_prefix().'procedure_client.id = '.db_prefix().'procedure_client_detail.id_detail');
        $this->db->where('type', 'lead');
        $data['client_detail'] = $this->db->get(db_prefix().'procedure_client_detail')->result_array();
        $this->load->view('admin/advisory_lead/manage', $data);
    }

    public function table()
    {
        $this->app->get_table_data('advisory_lead');
    }
    public function table_advisory_lead_tab($id_lead = "")
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('advisory_lead_tab', ['id_lead' => $id_lead]);
    }

    public function getModal()
    {

        $this->db->select(db_prefix().'procedure_client_detail.*');
        $this->db->where('type' ,'lead');
        $this->db->join(db_prefix().'procedure_client', db_prefix().'procedure_client.id = '.db_prefix().'procedure_client_detail.id_detail');
        $this->db->order_by('orders', 'asc');
        $data['procedure_detail'] = $this->db->get(db_prefix().'procedure_client_detail')->result_array();
        if($this->input->post('id'))
        {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $data['advisory_lead'] = $this->db->get(db_prefix().'advisory_lead')->row();
            if(!empty($data['advisory_lead']))
            {
                $this->db->select('count(id) as count_id');
                $this->db->where('id_advisory', $id);
                $data['advisory_lead']->count_advisory = $this->db->get(db_prefix().'procedure_advisory_lead')->row();
            }
        }

        $this->db->select('group_concat(lead) as list_id');
        $this->db->where('status_break' , 0);
        $list_advisory_lead = $this->db->get(db_prefix().'advisory_lead')->row();
        if(!empty(explode(',', $list_advisory_lead->list_id)))
        {
            $this->db->where_not_in('id', explode(',', $list_advisory_lead->list_id));
            if(!empty($data['advisory_lead']))
            {
                $this->db->or_where('id', $data['advisory_lead']->lead);
            }
        }
        $data['lead'] = $this->db->get(db_prefix().'leads' )->result_array();

        $this->load->view('admin/advisory_lead/modal', $data);
    }
    public function detail()
    {
        if($this->input->post())
        {
            $data = $this->input->post();
            if(!empty($data['id']))
            {
                $id = $data['id'];
                unset($data['id']);


                $this->db->where('id', $id);
                $advisory_lead = $this->db->get('tbladvisory_lead')->row();
                if(!empty($advisory_lead)) {
                    $array_update = [
                        'lead' => $data['lead'],
                        'product_other_buy' => $data['product_other_buy'],
                        'address_other_buy' => $data['address_other_buy'],
                    ];
                    if (!empty($data['date'])) {
                        $array_update['date'] = to_sql_date($data['date']);
                    }


                    $this->db->select('count(id) as count_id');
                    $this->db->where('id_advisory', $id);
                    $advisory_lead_procedure = $this->db->get(db_prefix() . 'procedure_advisory_lead')->row();

                    if (empty($advisory_lead_procedure->count_id)) {
                        if($advisory_lead->status_first != $data['status_first'] || $advisory_lead->date != $array_update['date'])
                        {
                            $_date = $array_update['date'];
                            $this->db->where('id', $data['status_first']);
                            $procedure_detail = $this->db->get(db_prefix() . 'procedure_client_detail')->row();
                            $date = date("Y-m-d", strtotime("$_date +$procedure_detail->leadtime day"));
                            $array_update['date_expected'] = $date;
                            $array_update['status_first'] = $data['status_first'];
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'alert_type' => 'danger',
                            'message' => _l('cong_data_to_change_not_update')
                        ]);
                        die();
                    }


                    $this->db->where('id', $id);
                    if ($this->db->update(db_prefix() . 'advisory_lead', $array_update)) {
                        echo json_encode([
                            'success' => true,
                            'alert_type' => 'success',
                            'message' => _l('cong_update_true')
                        ]);
                        die();
                    }
                }
                echo json_encode([
                    'success' => false,
                    'alert_type' => 'danger',
                    'message' => _l('cong_update_false')
                ]);die();
            }
            else
            {
                $this->db->where('id', $data['status_first']);
                $procedure_detail = $this->db->get(db_prefix().'procedure_client_detail')->row();
                $_date = to_sql_date($data['date']);
                $date =  date("Y-m-d", strtotime("$_date +$procedure_detail->leadtime day"));
                $array_add = [
                    'lead' => $data['lead'],
                    'date' => to_sql_date($data['date']),
                    'date_expected' => $date,
                    'status_first' => $data['status_first'],
                    'product_other_buy' => $data['product_other_buy'],
                    'address_other_buy' => $data['address_other_buy'],
                    'date_create' => date('Y-m-d H:i:s'),
                    'create_by' => get_staff_user_id()
                ];

                $this->db->insert(db_prefix().'advisory_lead', $array_add);
                if($this->db->insert_id())
                {
                    $id = $this->db->insert_id();
                    //Thêm Mã tư vấn cho Phiếu
                    $code   =   sprintf("%06s", $id);
                    $prefix = get_option('advisory_lead_prefix');
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix().'advisory_lead', ['code' => $code, 'prefix' => $prefix]);
                    //End thêm mã tư vấn cho phiếu

                    echo json_encode([
                        'success' => true,
                        'alert_type' => 'success',
                        'message' => _l('cong_add_true')
                    ]);die();
                }
                echo json_encode([
                    'success' => false,
                    'alert_type' => 'danger',
                    'message' => _l('cong_add_false')
                ]);die();
            }
        }
    }
    public function delete_advisory_lead()
    {
        if($this->input->post('id'))
        {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            if($this->db->delete(db_prefix().'advisory_lead'))
            {
                $this->db->where('id_advisory', $id);
                $this->db->delete('procedure_advisory_lead');
                echo json_encode([
                    'success' => true,
                    'alert_type' => 'success',
                    'message' => _l('cong_delete_true')
                ]);die();
            }
            echo json_encode([
                'success' => false,
                'alert_type' => 'danger',
                'message' => _l('cong_delete_false')
            ]);die();

        }
    }

    public function update_status()
    {
        if($this->input->post())
        {
            $data = $this->input->post();
            if(!empty($data['id']) && !empty($data['status_procedure']))
            {
                $this->db->where('id', $data['id']);
                $advisory_lead = $this->db->get(db_prefix().'advisory_lead')->row();
                if(!empty($advisory_lead))
                {
                    //Kiểm tra phiếu đã kế thúc chưa
                    if($advisory_lead->status_break == 1)
                    {
                        echo json_encode([
                            'success' => true,
                            'alert_type' => 'danger',
                            'message' => _l('cong_advisory_status_break')
                        ]);die();
                    }

                    //kiểm tra phiếu đã được duyệt qua chưa
                    $this->db->where('id_advisory', $data['id']);
                    $this->db->where('status_procedure', $data['status_procedure']);
                    $action_advisory = $this->db->get(db_prefix().'procedure_advisory_lead')->row();
                    if(!empty($action_advisory))
                    {
                        echo json_encode([
                            'success' => true,
                            'alert_type' => 'danger',
                            'message' => _l('cong_update_isset')
                        ]);die();
                    }
                    else
                    {
                        //Duyệt trạng thái
                        $this->db->insert(db_prefix().'procedure_advisory_lead', [
                            'create_by' => get_staff_user_id(),
                            'date_create' => date('Y-m-d H:i:s'),
                            'id_advisory' => $data['id'],
                            'status_procedure' => $data['status_procedure'],
                            'active' => 1
                        ]);

                        if($this->db->insert_id())
                        {
                            $this->db->where('id_advisory', $data['id']);
                            $this->db->where('id !='. $this->db->insert_id());
                            $this->db->update(db_prefix().'procedure_advisory_lead', ['active' => 0]);
                            //Thêm vào lịch sử chăn sóc khách hàng
                            addLog_advisory_lead([
                                'id_lead' => $advisory_lead->lead,
                                'staff' => get_staff_user_id(),
                                'id_procedure' => $data['status_procedure']
                            ], 1);

                            $array_update_advisory = [
                                'date_expected' => date('Y-m-d')
                            ];

                            $this->db->where('id', $data['status_procedure']);
                            $client_detail = $this->db->get(db_prefix().'procedure_client_detail')->row(); // lấy quy trình cập nhật
                            if(!empty($client_detail))
                            {
                                $this->db->where('orders > ', $client_detail->orders);
                                $this->db->order_by('orders', 'asc');
                                $this->db->where('id_detail', $client_detail->id_detail);
                                $client_detail_next = $this->db->get(db_prefix().'procedure_client_detail')->row();
                                if(empty($client_detail_next))
                                {
                                    $array_update_advisory['status_break'] = 1;
                                    addLog_advisory_lead([
                                        'id_lead' => $advisory_lead->lead,
                                        'staff' => get_staff_user_id(),
                                        'id_procedure' => $data['status_procedure']
                                    ], 3);
                                }
                                else
                                {
                                    $_date = date('Y-m-d');
                                    $date =  date("Y-m-d", strtotime("$_date +$client_detail_next->leadtime day"));
                                    $array_update_advisory['date_expected'] = $date;
                                }
                            }

                            $this->db->where('id', $data['id']);
                            $this->db->update(db_prefix().'advisory_lead', $array_update_advisory);
                            echo json_encode([
                                'success' => true,
                                'alert_type' => 'success',
                                'message' => _l('cong_update_true')
                            ]);die();
                        }
                    }
                }
            }
        }
        echo json_encode([
            'success' => false,
            'alert_type' => 'danger',
            'message' => _l('cong_update_false')
        ]);die();
    }

    public function restore_advisory_lead()
    {
        if($this->input->post())
        {
            $data = $this->input->post();
            if(!empty($data['id']))
            {
                $id = $data['id'];
                $this->db->where('id', $id);
                $advisory_lead = $this->db->get(db_prefix().'advisory_lead')->row();
                if(!empty($advisory_lead))
                {
                    $this->db->where('id_advisory', $id);
                    $this->db->order_by('date_create', 'desc');
                    $action_advisory = $this->db->get(db_prefix().'procedure_advisory_lead')->row();
                    if(!empty($action_advisory))
                    {
                        $this->db->where('id', $action_advisory->id);
                        if($this->db->delete(db_prefix().'procedure_advisory_lead'))
                        {
                            $this->db->where('id_advisory', $id);
                            $this->db->order_by('date_create', 'desc');
                            $action_advisory_old = $this->db->get(db_prefix().'procedure_advisory_lead')->row(); // lui lại bước để làm active
                            if(!empty($action_advisory_old))
                            {
                                $this->db->where('id', $action_advisory_old->id);
                                $this->db->update(db_prefix().'procedure_advisory_lead', ['active' => 1]);
                            }

                            //Thêm vào lịch sử chăn sóc khách hàng
                            addLog_advisory_lead([
                                'id_lead' => $advisory_lead->lead,
                                'staff' => get_staff_user_id(),
                                'id_procedure' => $action_advisory->status_procedure
                            ], 0);

                            $this->db->where('id', $action_advisory->status_procedure);
                            $client_detail = $this->db->get(db_prefix().'procedure_client_detail')->row(); // lấy quy trình cập nhật

                            if(!empty($client_detail))
                            {
                                if(!empty($action_advisory_old))
                                {
                                    $this->db->where('orders > ', $client_detail->orders);
                                    $this->db->order_by('orders', 'asc');
                                    $this->db->where('id_detail', $client_detail->id_detail);
                                    $client_detail_next = $this->db->get(db_prefix().'procedure_client_detail')->row();
                                }
                                else
                                {
                                    $client_detail_next = $client_detail;
                                }
                                if(!empty($client_detail_next))
                                {
                                    if(!empty($action_advisory_old))
                                    {
                                        $_date = $advisory_lead->date_expected;
                                        $date = date("Y-m-d", strtotime("$_date -$client_detail_next->leadtime day"));
                                    }
                                    else
                                    {
                                        $_date = $advisory_lead->date;
                                        $date = date("Y-m-d", strtotime("$_date +$client_detail_next->leadtime day"));
                                    }
                                    $this->db->where('id', $data['id']);
                                    $this->db->update(db_prefix() . 'advisory_lead', ['date_expected' => $date]);
                                }

                                echo json_encode([
                                    'success' => true,
                                    'alert_type' => 'success',
                                    'message' => _l('cong_restore_advisory_true')
                                ]);die();
                            }
                            echo json_encode([
                                'success' => true,
                                'alert_type' => 'success',
                                'message' => _l('cong_restore_advisory_true')
                            ]);die();
                        }
                    }
                    echo json_encode([
                        'success' => true,
                        'alert_type' => 'danger',
                        'message' => _l('cong_data_not_isset')
                    ]);die();
                }
            }
            echo json_encode([
                'success' => false,
                'alert_type' => 'danger',
                'message' => _l('cong_update_false')
            ]);die();
        }
    }

    public function break_advisory()
    {
        $id = $this->input->post('id');
        if(!empty($id))
        {
            $status = $this->input->post('status');
            $this->db->where('id', $id);
            $advisory_lead = $this->db->get(db_prefix().'advisory_lead')->row();
            if(!empty($advisory_lead))
            {
                $this->db->where('id', $id);
                if($this->db->update(db_prefix().'advisory_lead' , [
                    'status_break' => $status
                ]))
                {
                    $this->db->where('id_advisory', $id);
                    $this->db->order_by('date_create', 'desc');
                    $action_advisory = $this->db->get(db_prefix().'procedure_advisory_lead')->row();

                    addLog_advisory_lead([
                        'id_lead' => $advisory_lead->lead,
                        'staff' => get_staff_user_id(),
                        'id_procedure' => !empty($action_advisory->status_procedure) ? $action_advisory->status_procedure : $advisory_lead->status_first
                    ], 3);

                    echo json_encode([
                        'success' => true,
                        'alert_type' => 'success',
                        'message' => _l('cong_break_advisory_true')
                    ]);die();
                }
            }
        }
        echo json_encode([
            'success' => false,
            'alert_type' => 'false',
            'message' => _l('cong_break_advisory_false')
        ]);die();
    }

}
