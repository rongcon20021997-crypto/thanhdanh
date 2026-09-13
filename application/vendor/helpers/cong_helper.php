<?php
//Helpers của công
    defined('BASEPATH') or exit('No direct script access allowed');

    /*
     *
     * Lấy colum trong điều kiện của Module automations
     *
     */
    function GetAutomationWhereColum($action = "")
    {
        if(!empty($action))
        {
            if($action == 1)
            {
                $colums_where = array(
                    ['id' => 'datecreated', 'name' => _l('cong_date_create')],
                    ['id' => 'city', 'name' => _l('cong_city')],
                    ['id' => 'type_client', 'name' => _l('cong_type_client')],
                );
            }
            else if($action ==2)
            {
                $colums_where = array(
                    ['id' => 'dateadded', 'name' => _l('cong_date_create')],
                    ['id' => 'city', 'name' => _l('cong_city')],
                    ['id' => 'status', 'name' => _l('cong_status_lead')],
                    ['id' => 'source', 'name' => _l('cong_type_client')],
                );
            }
            return  $colums_where;
        }
        return false;
    }

    function GetActionAutomation($List = true)
    {
        if($List == true)
        {
            $array_action = array(
                ['id' => '1', 'name' => _l('cong_client')],
                ['id' => '2', 'name' => _l('cong_lead')],
                ['id' => '3', 'name' => _l('create_orders')], //đặt hàng
                ['id' => '4', 'name' => _l('create_receipts')], // phiếu thu
                ['id' => '5', 'name' => _l('create_pay')], //phiếu chi
                ['id' => '6', 'name' => _l('create_purchase_proposal')], //đề xuất mua hàng
                ['id' => '7', 'name' => _l('create_imports')], // nhập hàng
                ['id' => '8', 'name' => _l('create_exports')], //xuất hàng
                ['id' => '9', 'name' => _l('create_sell')], //bán hàng
            );
        }
        else {
            $array_action = [
                '1' => _l('cong_client'),
                '2' => _l('cong_lead'),
                '3' => _l('create_orders'),
                '4' => _l('create_receipts'),
                '5' => _l('create_pay'),
                '6' => _l('create_purchase_proposal'),
                '7' => _l('create_imports'),
                '8' => _l('create_exports'),
                '9' => _l('create_sell')
            ];
        }
        return $array_action;
    }




    function image_client_upload($userid = '')
    {

        if (is_numeric($userid)) {

            if (isset($_FILES['client_image']['name']) && $_FILES['client_image']['name'] != '') {

                $path = get_upload_path_by_type('customer') . $userid . '/';
                // Get the temp file path
                $tmpFilePath = $_FILES['client_image']['tmp_name'];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    // Getting file extension
                    $extension          = strtolower(pathinfo($_FILES['client_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = [
                        'jpg',
                        'jpeg',
                        'png',
                    ];
                    if (!in_array($extension, $allowed_extensions)) {
                        set_alert('warning', _l('file_php_extension_blocked'));

                        return false;
                    }
                    _maybe_create_upload_path($path);
                    $filename    = unique_filename($path, $_FILES['client_image']['name']);
                    $newFilePath = $path . '/' . $filename;
                    // Upload the file into the company uploads dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $CI                       = & get_instance();
                        $config                   = [];
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $newFilePath;
                        $config['new_image']      = 'thumb_' . $filename;
                        $config['maintain_ratio'] = true;
                        $config['width']          = 320;
                        $config['height']         = 320;
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $newFilePath;
                        $config['new_image']      = 'small_' . $filename;
                        $config['maintain_ratio'] = true;
                        $config['width']          = 32;
                        $config['height']         = 32;
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->db->where('userid', $userid);
                        $CI->db->update(db_prefix().'clients', [
                            'client_image' => $filename,
                        ]);
                        // Remove original image
                        unlink($newFilePath);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    function image_lead_upload($id = '')
    {

        if (is_numeric($id)) {
            if (isset($_FILES['lead_image']['name']) && $_FILES['lead_image']['name'] != '') {

                $path = get_upload_path_by_type('lead') . $id . '/';
                // Get the temp file path
                $tmpFilePath = $_FILES['lead_image']['tmp_name'];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    // Getting file extension
                    $extension          = strtolower(pathinfo($_FILES['lead_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = [
                        'jpg',
                        'jpeg',
                        'png',
                    ];
                    if (!in_array($extension, $allowed_extensions)) {
                        set_alert('warning', _l('file_php_extension_blocked'));

                        return false;
                    }
                    _maybe_create_upload_path($path);
                    $filename    = unique_filename($path, $_FILES['lead_image']['name']);
                    $newFilePath = $path . '/' . $filename;
                    // Upload the file into the company uploads dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $CI                       = & get_instance();
                        $config                   = [];
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $newFilePath;
                        $config['new_image']      = 'thumb_' . $filename;
                        $config['maintain_ratio'] = true;
                        $config['width']          = 320;
                        $config['height']         = 320;
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $newFilePath;
                        $config['new_image']      = 'small_' . $filename;
                        $config['maintain_ratio'] = true;
                        $config['width']          = 32;
                        $config['height']         = 32;
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->db->where('id', $id);
                        $CI->db->update(db_prefix().'leads', [
                            'lead_image' => $filename,
                        ]);
                        // Remove original image
                        unlink($newFilePath);

                        $profileImagePath = 'uploads/leads/'.$id.'/thumb_'.$filename;
                        $url = base_url('download/preview_image?path='.$profileImagePath);
                        return ['url' =>$url, 'name_img' => $filename];
                    }
                }
            }
        }

        return false;
    }


    function get_district($id)
    {
        if(!empty($id))
        {
            $CI = & get_instance();
            $CI->db->where('districtid', $id);
            $district = $CI->db->get(db_prefix().'district')->row();
            if(!empty($district))
            {
                return $district;
            }
            return NULL;
        }
    }
    function get_ward($id = "")
    {
        if(!empty($id))
        {
            $CI = & get_instance();
            $CI->db->where('wardid', $id);
            $ward = $CI->db->get(db_prefix().'ward')->row();
            if(!empty($ward))
            {
                return $ward;
            }
            return NULL;
        }
    }
    function get_DataCombobox($id = "")
    {
        if(!empty($id)) {
            $CI = &get_instance();
            $CI->db->where('id', $id);
            $data = $CI->db->get(db_prefix().'combobox_client')->row();
            if(!empty($data))
            {
                return $data;
            }
        }
        return NULL;
    }

    function get_type_client($id = "")
    {
        if(!empty($id)) {
            $CI = &get_instance();
            $CI->db->where('id', $id);
            $data = $CI->db->get(db_prefix().'type_client')->row();
            if(!empty($data))
            {
                return $data;
            }
        }
        return NULL;
    }


    function addLog_advisory_lead($data = [], $type = 1)
    {
        $CI = & get_instance();
        if(!empty($data))
        {
            $CI->db->where('id', $data['id_procedure']);
            $procedure_detail = $CI->db->get(db_prefix().'procedure_client_detail')->row();
            if(!empty($procedure_detail))
            {
                $CI->db->insert(db_prefix().'log_advisory_lead', [
                    'id_lead' => $data['id_lead'],
                    'staff' => $data['staff'],
                    'id_procedure' => $data['id_procedure'],
                    'date_create' => date('Y-m-d H:i:s'),
                    'create_by' => get_staff_user_id(),
                    'note' => $procedure_detail->name,
                    'type' => $type
                ]);
                if($CI->db->insert_id())
                {
                    return true;
                }
            }
        }
        return false;
    }

    function addLog_care_of($data = [], $type = 1)
    {
        $CI = & get_instance();
        if(!empty($data))
        {
            $CI->db->where('id', $data['id_procedure']);
            $procedure_detail = $CI->db->get(db_prefix().'procedure_client_detail')->row();
            if(!empty($procedure_detail))
            {
                $CI->db->insert(db_prefix().'log_care_of', [
                    'id_client' => $data['id_client'],
                    'staff' => $data['staff'],
                    'id_procedure' => $data['id_procedure'],
                    'date_create' => date('Y-m-d H:i:s'),
                    'create_by' => get_staff_user_id(),
                    'note' => $procedure_detail->name,
                    'type' => $type
                ]);
                if($CI->db->insert_id())
                {
                    return true;
                }
            }
        }
        return false;
    }

    function get_fields_export_excel()
    {
        $CI = & get_instance();
        $colum_client = $CI->db->list_fields(db_prefix().'clients');
        $colum_client = array_diff($colum_client, [
            'billing_city' ,
            'billing_street',
            'billing_state',
            'billing_zip',
            'billing_country',
            'shipping_street',
            'shipping_city',
            'shipping_country',
            'shipping_state',
            'shipping_zip',
            'client_image',
            'longitude',
            'latitude',
            'default_language',
            'show_primary_contact',
            'stripe_id',
            'registration_confirmed',
            'addedfrom',
            'active',
            'leadid',
            'default_currency',
        ]);
        $colum_info_client = $CI->db->get(db_prefix().'client_info_detail')->result_array();
        return ['colum_client' => $colum_client, 'colum_info_client' => $colum_info_client];
    }
    function get_fields_import_excel()
    {
        $CI = & get_instance();
        $colum_client = $CI->db->list_fields(db_prefix().'clients');
        $colum_client = array_diff($colum_client, [
            'userid' ,
            'introduction' ,

            'billing_city' ,
            'billing_street',
            'billing_state',
            'billing_zip',
            'billing_country',
            'shipping_street',
            'shipping_city',
            'shipping_country',
            'shipping_state',
            'shipping_zip',
            'client_image',
            'longitude',
            'latitude',
            'default_language',
            'show_primary_contact',
            'stripe_id',
            'registration_confirmed',
            'addedfrom',
            'active',
            'leadid',
            'default_currency',
        ]);
        $colum_info_client = $CI->db->get(db_prefix().'client_info_detail')->result_array();
        return ['colum_client' => $colum_client, 'colum_info_client' => $colum_info_client];
    }

    function get_care_of_clients_priorities()
    {
        return [
                [
                    'id'     => 1,
                    'name'   => _l('task_priority_low'),
                    'color' => '#777',

                ],
                [
                    'id'     => 2,
                    'name'   => _l('task_priority_medium'),
                    'color' => '#03a9f4',

                ],
                [
                    'id'    => 3,
                    'name'  => _l('task_priority_high'),
                    'color' => '#ff6f00',
                ],
                [
                    'id'    => 4,
                    'name'  => _l('task_priority_urgent'),
                    'color' => '#fc2d42',
                ],
            ];
    }
    function care_of_priority($id)
    {
        foreach (get_care_of_clients_priorities() as $priority) {
            if ($priority['id'] == $id) {
                return $priority;
            }
        }

        // Not exists?
        return '#333';
    }


    function init_not_head()
    {
        $CI = &get_instance();
        $CI->load->view('admin/includes/not_head/not_head');
    }

    function acction_delete_ajax($type = 1)
    {
        if($type == 1)
        {
            echo json_encode(['success' => true, 'alert_type' => 'success', 'message' => _l('cong_action_delete_true')]);die();
        }
        else if($type == 0)
        {
            echo json_encode(['success' => false, 'alert_type' => 'success', 'message' => _l('cong_action_delete_false')]);die();
        }
        else if($type == 2)
        {
            echo json_encode(['success' => false, 'alert_type' => 'success', 'message' => _l('cong_access_denied')]);die();
        }
        else if($type == 3)
        {
            echo json_encode(['success' => false, 'alert_type' => 'warning', 'message' => _l('cong_is_referenced')]);die();
        }
    }


    function get_tags_color()
    {
        $CI = &get_instance();
        $CI->db->select('group_concat(color order by name asc) as list_color');
        $tags = $CI->db->get('tbltags')->row();
        if(!empty($tags->list_color))
        {
            return explode(',', $tags->list_color);
        }
        return [];
    }
    function get_tags_background_color()
    {
        $CI = &get_instance();
        $CI->db->select('group_concat(background_color order by name asc) as list_background_color');
        $tags = $CI->db->get('tbltags')->row();
        if(!empty($tags->list_background_color))
        {
            return explode(',', $tags->list_background_color);
        }
        return [];
    }

    function get_tags_table($where = "", $limit = "")
    {
        $CI = &get_instance();
        if(!empty($where))
        {
            $CI->db->where($where);
        }
        if(!empty($limit))
        {
            $CI->db->limit($limit);
        }
        $CI->db->order_by('id', 'asc');
        $tags = $CI->db->get('tbltags')->result_array();

        return $tags;
    }

    //Định dạng ngày new
    function _dC($date)
    {
        $formatted = '';

        if ($date == '' || is_null($date) || $date == '0000-00-00') {
            return $formatted;
        }

        $format    = get_current_date_format();
        $formatted = strftime($format, strtotime($date));

        return hooks()->apply_filters('after_format_date', $formatted, $date);
    }

    function C_initNumber($number = 0)
    {
        if(is_numeric((double)$number))
        {
            return str_replace(',', '', $number);
        }
        return 0;
    }

    function GetProducedure($status = 0)
    {
        $CI = &get_instance();
        $CI->db->where('type', 'orders');
        $procedure = $CI->db->get(db_prefix().'procedure_client')->row();
        if(!empty($procedure))
        {
            if($status == 0)
            {
                $CI->db->order_by('orders asc');
                $CI->db->where('id_detail', $procedure->id);
                $procedure_detail = $CI->db->get('tblprocedure_client_detail')->row();
                if(!empty($procedure_detail))
                {
                    return $procedure_detail;
                }
            }
            else
            {
                $CI->db->order_by('orders asc');
                $CI->db->where('orders > (select orders from tblprocedure_client_detail where id = '.$status.' and id_detail = '.$procedure->id.')');
                $CI->db->where('id_detail', $procedure->id);
                $procedure_detail = $CI->db->get('tblprocedure_client_detail')->row();
                if(!empty($procedure_detail))
                {
                    return $procedure_detail;
                }
            }
        }
        return false;
    }
    function ResProducedure($status = 0)
    {
        $CI = &get_instance();
        $CI->db->where('type', 'orders');
        $procedure = $CI->db->get(db_prefix().'procedure_client')->row();
        if(!empty($procedure))
        {
            $CI->db->order_by('orders desc');
            $CI->db->where('orders < (select orders from tblprocedure_client_detail where id = '.$status.' and id_detail = '.$procedure->id.')');
            $CI->db->where('id_detail', $procedure->id);
            $procedure_detail = $CI->db->get('tblprocedure_client_detail')->row();

            if(!empty($procedure_detail))
            {
                return $procedure_detail;
            }
        }
        return false;
    }

    function getConnectLead($lead = "")
    {
        $CI = &get_instance();
        $result = [];
        if(!empty($lead))
        {
            $CI->db->where('lead', $lead);
            $advisory_lead = $CI->db->get('tbladvisory_lead')->num_rows();

            $CI->db->where('rel_type', 'lead');
            $CI->db->where('rel_id', $lead);
            $tasks = $CI->db->get('tbltasks')->num_rows();
            if($advisory_lead > 0)
            {
                $result[] = [
                    'message' => _l('cong_isset_list')._l('cong_advisory_lead'),
                    'data' => $advisory_lead
                ];
            }
            if($tasks > 0)
            {
                $result[] = [
                    'message' => _l('cong_isset_list')._l('cong_tasks'),
                    'data' => $tasks
                ];
            }
        }
        return $result;
    }

    function getConnectClient($client = "")
    {
        $CI = &get_instance();
        $result = [];
        if(!empty($client))
        {
            $CI->db->where('client', $client);
            $care_of_client = $CI->db->get('tblcare_of_clients')->num_rows();

            $CI->db->where('client', $client);
            $orders = $CI->db->get('tblorders')->num_rows();

            $CI->db->where('rel_type', 'customer');
            $CI->db->where('rel_id', $client);
            $tasks = $CI->db->get('tbltasks')->num_rows();
            if($care_of_client > 0)
            {
                $result[] = [
                    'message' => _l('cong_isset_list')._l('cong_care_of_clients'),
                    'data' => $care_of_client
                ];
            }
            if($tasks > 0)
            {
                $result[] = [
                    'message' => _l('cong_isset_list')._l('cong_tasks'),
                    'data' => $tasks
                ];
            }
            if($orders > 0)
            {
                $result[] = [
                    'message' => _l('cong_isset_list')._l('cong_orders'),
                    'data' => $orders
                ];
            }
        }
        return $result;
    }
