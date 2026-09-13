<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Orders extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('orders_model');
        $this->load->model('quotes_orders_model');
        $this->load->model('products_model');
    }

    public function index()
    {
        if (!has_permission('orders', '', 'view')) {
            if (!has_permission('import', '', 'create')) {
                access_denied('import');
            }
        }
        $data['title']          = _l('cong_orders');
        $procedure = get_table_where(db_prefix().'procedure_client', [
            'type' => 'orders'
        ] ,'', 'row');
        $this->db->where('id_detail', $procedure->id);
        $this->db->order_by('orders', 'ASC');
        $data['procedure_detail'] = $this->db->get(db_prefix().'procedure_client_detail')->result_array();
        $this->load->view('admin/orders/manage', $data);
    }

    public function table()
    {
        if (!has_permission('orders', '', 'view')) {
                ajax_access_denied();
        }
        $this->app->get_table_data('orders');
    }

    public function detail($id = '')
    {
        if($this->input->post())
        {
            $data  = $this->input->post();
            if(empty($id))
            {
                $data['note'] = $this->input->post('note', false);
                $success = $this->orders_model->add($data);
                if($success)
                {
                    set_alert('success', _l('cong_add_true'));
                    redirect(admin_url('orders'));
                }
                else
                {
                    set_alert('danger', _l('cong_add_false'));
                    redirect(admin_url('orders/detail'));
                }
            }
            else
            {
                $data['note'] = $this->input->post('note', false);
                $success = $this->orders_model->update($id, $data);
                if($success)
                {
                    set_alert('success', _l('cong_update_true'));
                    redirect(admin_url('orders'));
                }
                else
                {
                    set_alert('danger', _l('cong_update_false'));
                    redirect(admin_url('orders/detail'));
                }

            }
        }
        else
        {
            if (!has_permission('orders', '', 'create')) {
                    ajax_access_denied();
            }
            if($id != '')
            {
                $data['title']          = _l('cong_update_orders');
                $data['orders'] = $this->orders_model->get($id);
                $data['orders']->name_status = $this->orders_model->get_status_orders($id, $data['orders']->status);
                $data['staff'] = get_table_where(db_prefix().'staff', '( active = 1 or staffid = '.$data['orders']->assigned.' )');
                $this->db->where('userid', $data['orders']->client);
                $data['clients'] = $this->db->get(db_prefix().'clients')->result_array();

                $data['shipping'] = get_table_where('tblshipping_client', ['client' => $data['orders']->client]);
            }
            else
            {

                if($this->input->get('convert_quotes')) // tại phiếu từ phiếu báo giá
                {
                    $id_quotes = $this->input->get('convert_quotes');
                    $this->db->where('id_quotes_orders', $id_quotes);
                    $orders = $this->db->get(db_prefix().'orders')->num_rows();
                    if($orders == 0)
                    {
                        $data['title']          = _l('cong_add_orders_to_quotes');
                        $data['orders'] = $this->quotes_orders_model->get($id_quotes);
                        if( $data['orders']->status != 2)
                        {
                            $data['orders']->name_status = _l('cong_add_orders_to_quotes');
                            $data['staff'] = get_table_where(db_prefix().'staff', '( active = 1 or staffid = '.$data['orders']->assigned.' )');
                            $this->db->where('userid', $data['orders']->client);
                            $data['clients'] = $this->db->get(db_prefix().'clients')->result_array();
                            $data['shipping'] = get_table_where('tblshipping_client', ['client' => $data['orders']->client]);
                            $data['convert_quotes'] = $id_quotes;
                        }
                        else
                        {
                            set_alert('danger', _l('cong_quotes_orders_cancel'));
                            redirect(admin_url('quotes_orders'));
                        }
                    }
                    else
                    {
                        set_alert('danger', _l('cong_quotes_orders_isset_orders'));
                        redirect(admin_url('quotes_orders'));
                    }
                }
                else
                {
                    //Lấy khách hàng
                    $this->db->limit(10);
                    $data['clients'] = $this->db->get(db_prefix().'clients')->result_array();
                    //End lấy khách hàng

                    $data['staff'] = get_table_where(db_prefix().'staff', ['active' => 1]);
                    $data['title']          = _l('cong_add_orders');
                    $data['shipping'] = [];
                }
            }
            $this->load->view('admin/orders/detail', $data);
        }
    }

    public function getItemsAjax()
    {
        $search = $this->input->post('q');
        $limit = 100;
        if(!empty($search))
        {
            $this->db->select('id, name, code, price, avatar, "items" as type_items');
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('code', $search);
            $this->db->group_end();
            $this->db->limit($limit);
            $items = $this->db->get('tblitems')->result_array();

            $group_items = [['id' => 'group', 'name' => lang('ch_items'), 'code' => lang('ch_items'), 'price' => 0, 'avatar' => false, 'type_items' => "items"]];
            $list = array_merge($group_items, $items);

            $this->db->select('id, name, code, price_sell as price, CONCAT("uploads/products/", "", images, "") as avatar, "products" as type_items');
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('code', $search);
            $this->db->group_end();
            $this->db->limit($limit);
            $products = $this->db->get('tbl_products')->result_array();
            $group_products = [['id' => 'group', 'name' => lang('tnh_products'), 'code' => lang('tnh_products'), 'price' => 0, 'avatar' => false, 'type_items' => "items"]];
            $list = array_merge($list, $group_products);
            $list = array_merge($list, $products);

            echo json_encode($list);
            die();

            // print_arrays(json_encode($items));
            // echo json_encode($items);die();
        }
        echo json_encode([]);die();
    }

    public function getCustomerAjax()
    {
        $search = $this->input->post('q');
        if(!empty($search))
        {
            $this->db->select('userid, company,concat(prefix_client,code_client," - ",code_type) as full_code');
            $this->db->like('company', $search);
            $customer = $this->db->get('tblclients')->result_array();
            echo json_encode($customer);die();
        }
        echo json_encode([]);die();
    }

    //Cập nhật trạng thái
    public function update_status()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        if(!empty($id))
        {
            $this->db->where('id', $id);
            $orders = $this->db->get(db_prefix().'orders')->row();
            if(!empty($orders))
            {
                if($status != -1 && $status != -2 && $status != -3)
                {
                    $produce = GetProducedure($orders->status);
                    if($produce->id == $status)
                    {
                        $this->db->where('id', $id);
                        $success = $this->db->update('tblorders', ['status' => $status]);
                        if($success)
                        {
                            $this->db->insert('tblorders_step', [
                                'id_orders' => $id,
                                'id_procedure' => $status,
                                'date_create' => date('Y-m-d H:i:s'),
                                'id_staff' => get_staff_user_id(),
                                'name_procedure' => $produce->name,
                                'active' => 1,
                                'order_by' => $produce->orders
                            ]);
                            $id_step = $this->db->insert_id();
                            if(!empty($id_step))
                            {
                                $this->db->where('id_orders', $id);
                                $this->db->where('id !=', $id_step);
                                $this->db->where('id_procedure != ', $status);
                                $this->db->update('tblorders_step', ['active' => 0]);
                            }

                            $produce_next = GetProducedure($status);
                            if(empty($produce_next))
                            {
                                $this->db->where('id', $id);
                                $success = $this->db->update('tblorders', ['status' => '-1']);
                            }


                            echo json_encode([
                                'success' => true,
                                'alert_type' => 'success',
                                'message' => _l('cong_change_status_true')
                            ]);die();
                        }
                        echo json_encode([
                            'success' => false,
                            'alert_type' => 'danger',
                            'message' => _l('cong_change_status_false')
                        ]);die();
                    }
                }
                else if($status != -1)
                {
                    $this->db->where('id', $id);
                    $success = $this->db->update('tblorders', ['status' => $status]);
                    if($success)
                    {
                        if($orders->status != -2)
                        {
                            $this->db->select('max(order_by) as max_order');
                            $this->db->where('id_orders', $id);
                            $max_step = $this->db->get('tblorders_step')->row();
                            $this->db->insert('tblorders_step', [
                                'id_orders' => $id,
                                'id_procedure' => $status,
                                'date_create' => date('Y-m-d H:i:s'),
                                'id_staff' => get_staff_user_id(),
                                'name_procedure' => $status == -2 ? _l('cong_orders_delay') : _l('cong_orders_cancel'),
                                'active' => 1,
                                'order_by' => ($max_step->max_order + 1)
                            ]);
                            $id_step = $this->db->insert_id();
                            if(!empty($id_step))
                            {
                                $this->db->where('id_orders', $id);
                                $this->db->where('id != ', $id_step);
                                $this->db->where('id_procedure != ', $status);
                                $this->db->update('tblorders_step', ['active' => 0]);
                            }
                            echo json_encode([
                                'success' => true,
                                'alert_type' => 'success',
                                'message' => _l('cong_change_status_true')
                            ]);die();
                        }
                    }
                }
            }
        }
        echo json_encode([
            'success' => false,
            'alert_type' => 'danger',
            'message' => _l('cong_data_change_pls')
        ]);die();
    }

    public function restore_step()
    {
        if($this->input->post('id'))
        {
            $id = $this->input->post('id');
            if(!empty($id))
            {
                $this->db->where('id', $id);
                $orders = $this->db->get('tblorders')->row();
                if($orders->status > 0 || $orders->status == -1)
                {
                    if($orders->status == -1)
                    {
                        $this->db->where('id_orders', $orders->id);
                        $this->db->where('id_procedure > 0');
                        $this->db->order_by('order_by', 'desc');
                        $orders_step = $this->db->get('tblorders_step')->row();
                        if(!empty($orders_step))
                        {
                            $orders->status = $orders_step->id_procedure;
                        }
                        else
                        {
                            $orders->status = 0;
                        }
                    }
                    $producedure = ResProducedure($orders->status);
                    if(!empty($producedure))
                    {
                        $this->db->where('id', $id);
                        $success = $this->db->update('tblorders', ['status' => $producedure->id]);
                        if($success)
                        {
                            $this->db->where('id_orders', $id);
                            $this->db->where('id_procedure', $orders->status);
                            $this->db->delete('tblorders_step');
                            echo json_encode([
                                'success' => true,
                                'alert_type' => 'success',
                                'message' => _l('cong_restore_true')
                            ]);die();
                        }
                    }
                }
                echo json_encode([
                    'success' => false,
                    'alert_type' => 'danger',
                    'message' => _l('cong_restore_false')
                ]);die();
            }
        }
    }

    public function restore_orders()
    {
        if($this->input->post('id'))
        {
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            if(!empty($id))
            {
                $this->db->where('id', $id);
                $orders = $this->db->get('tblorders')->row();
                if( ($orders->status == -2 || $orders->status == -3) && $orders->status == $status)
                {
                    $this->db->where('id_orders', $id);
                    $this->db->where('id_procedure !='.$status);
                    $this->db->order_by('order_by', 'DESC');
                    $orders_step = $this->db->get('tblorders_step')->row();

                    $this->db->where('id', $id);
                    $success = $this->db->update('tblorders', ['status' => !empty($orders_step->id_procedure) ? $orders_step->id_procedure: '0']);
                    if($success)
                    {
                        $this->db->where('id_orders', $id);
                        $this->db->where('id_procedure', $status);
                        $this->db->delete('tblorders_step');
                        if(!empty($orders_step->id_procedure))
                        {
                            $this->db->where('id_orders', $id);
                            $this->db->where('id_procedure', $orders_step->id_procedure);
                            $this->db->update('tblorders_step', [
                                'active' => 1
                            ]);
                        }
                        echo json_encode([
                            'success' => true,
                            'alert_type' => 'success',
                            'message' => _l('cong_change_status_true')
                        ]);die();
                    }
                }
                else
                {
                    echo json_encode([
                        'success' => false,
                        'alert_type' => 'danger',
                        'message' => _l('cong_data_change_pls')
                    ]);die();
                }
            }
            echo json_encode([
                'success' => false,
                'alert_type' => 'danger',
                'message' => _l('cong_change_status_false')
            ]);die();
        }
    }

    public function delete_orders()
    {
        $id = $this->input->post('id');
        if(!empty($id))
        {
            $this->db->where('id', $id);
            $orders = $this->db->get('tblorders')->row();
            $this->db->where('id', $id);
            $success = $this->db->delete('tblorders');
            if(!empty($success))
            {
                $this->db->where('id_orders', $id);
                $this->db->delete('tblorders_detail_shipping');

                $this->db->where('id_orders', $id);
                $this->db->delete('tblorders_items');

                $this->db->where('id_orders', $id);
                $this->db->delete('tblorders_step');
                if(!empty($orders->id_quotes_orders))
                {
                    $this->db->where('id', $orders->id_quotes_orders);
                    $this->db->update('tblquotes_orders', ['status' => 0]);
                }
                echo json_encode([
                    'success' => true,
                    'alert_type' => 'success',
                    'message' => _l('cong_delete_true')
                ]);die();

            }
        }
        echo json_encode([
            'success' => false,
            'alert_type' => 'danger',
            'message' => _l('cong_delete_false')
        ]);die();
    }

    public function loadViewOrder($id = "")
    {
        if(!empty($id))
        {
            $data['title'] = _l('cong_detail_orders');
            $data['orders'] = $this->orders_model->get_view($id);
            $data['orders']->name_status = $this->orders_model->get_status_orders($id, $data['orders']->status);
            echo json_encode(['success' => true, 'data' => $this->load->view('admin/orders/view_modal', $data , true)]);die();
        }
        echo json_encode(['success' => false, 'alert_type' => 'danger', 'message' => _l('cong_found_data')]);die();
    }

    public function getShipping()
    {
        $client = $this->input->post('client');
        if(!empty($client))
        {
            $this->db->where('client', $client);
            $shipping_client = $this->db->get('tblshipping_client')->result_array();
            if(!empty($shipping_client))
            {
                echo json_encode($shipping_client);die();
            }
        }
        echo json_encode([]);die();
    }

    public function ViewModalShipping()
    {
        $client = $this->input->post('client');
        $idSelect = $this->input->post('idSelect');
        if(!empty($client))
        {
            echo json_encode([
                'data' => $this->load->view('admin/orders/Addshipping', ['client' => $client, 'idSelect' => $idSelect], true),
                'success' => true
                ]);die();
        }
        echo json_encode([
            'success' => false,
            'message' => _l('cong_not_found_client_shipping')
        ]);die();
    }
}