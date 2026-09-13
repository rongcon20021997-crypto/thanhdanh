<?php

// header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Manufactures extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->model('items_model');
        $this->load->model('unit_model');
        $this->load->model('category_model');
        $this->load->model('manufactures_model');
        // $this->lang->load('vietnamese/form_validation_lang');
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '1024';
        $this->upload_path = get_upload_path_by_type('products');
        $this->datetime_now = time();
        $this->tnh = true;
    }

    public function index()
    {
        $data['tnh'] = true;
        $data['title'] = lang('productions_plan');
        $this->load->view('admin/manufactures/productions_plan', $data);
    }

    public function productions_plan()
    {
        $data['tnh'] = true;
        $data['title'] = lang('productions_plan');
        $this->load->view('admin/manufactures/productions_plan', $data);
    }

    function check_options()
    {
        $options1 = $this->input->post('options1');
        $options2 = $this->input->post('options2');

        if (empty($options1) && empty($options2)) {
            $this->form_validation->set_message('check_options', lang('tnh_options_or_required'));
            return false;
        }
        return true;
    }

    public function add_productions_plan()
    {
        if ($this->input->post('add'))
        {
            $data = [];

            $this->form_validation->set_rules('reference_no', lang("tnh_reference_productions_plan"), 'trim|required|is_unique[tbl_productions_plan.reference_no]');
            $this->form_validation->set_rules('date', lang("date"), 'required');
            $this->form_validation->set_rules('planning_cycle', lang("tnh_planning_cycle"), 'required');
            $this->form_validation->set_rules('options1', lang("tnh_options"), 'callback_check_options');
            if ($this->form_validation->run() == true)
            {
                $reference_no = $this->input->post('reference_no');
                $date = to_sql_date($this->input->post('date'), true);
                $planning_cycle = $this->input->post('planning_cycle');
                $options1 = $this->input->post('options1') ? $this->input->post('options1') : 0;
                $options2 = $this->input->post('options2') ? $this->input->post('options2') : 0;
                $safe_inventory = $this->input->post('safe_inventory') ? $this->input->post('safe_inventory') : 0;
                $note = $this->input->post('note');
                $status = 'un_approved';
                $select_custom_fields = "";
                $where_custom_fields = "";
                $date_start = NULL;
                $date_end = NULL;
                $sales_orders = NULL;
                $sales_orders_id = NULL;
                $business_plan = NULL;
                $business_plan_id = NULL;
                $arr_date = [];
                if (!empty($planning_cycle)) {
                    $arr_plan = explode('-', trim($planning_cycle));

                    $date1 = to_sql_date(trim($arr_plan[0]));
                    $date2 = to_sql_date(trim($arr_plan[1]));

                    if ($options1 == true) {
                        $query = "(
                            SELECT
                                GROUP_CONCAT(distinct(tblorders.id) SEPARATOR ',') as id,
                                GROUP_CONCAT(distinct(CONCAT(tblorders.prefix, '', tblorders.code)) SEPARATOR ', ') as reference_orders
                            FROM tblorders
                            INNER JOIN tblorders_items ON tblorders_items.id_orders = tblorders.id
                            WHERE tblorders.status > 0 AND DATE_FORMAT(tblorders.date, '%Y-%m-%d') >= '$date1' AND DATE_FORMAT(tblorders.date, '%Y-%m-%d') <= '$date2' AND tblorders_items.type_items = 'products'
                        )";
                        $rs = $this->db->query($query)->row_array();
                        $sales_orders = $rs['reference_orders'];
                        $sales_orders_id = $rs['id'];
                    }

                    $date_start = $date1;
                    $date_end = $date2;

                    $date1 = new DateTime($date1);
                    $date2 = new DateTime($date2);

                    $diff = $date1->diff($date2);
                    $days = $diff->days;

                    for ($i = 0; $i <= $days; $i++) {
                        $pdate = date('Y-m-d', strtotime(to_sql_date(trim($arr_plan[0])). " + $i days"));
                        $select = " 0  ";
                        $flag = false;
                        if ($options1 == 1) {
                            $flag = true;
                            $select = "COALESCE((
                                SELECT
                                    SUM(tblorders_items.quantity)
                                FROM tblorders
                                INNER JOIN tblorders_items ON tblorders_items.id_orders = tblorders.id
                                WHERE tblorders.status > 0 AND DATE_FORMAT(tblorders.date, '%Y-%m-%d') = '$pdate' AND tblorders_items.type_items = 'products' AND tblorders_items.id_product = tbl_products.id
                            ), 0)";
                        } else if ($options2 == 1) {
                            $flag = true;
                            $select.= " + 0 ";
                        }
                        $select_custom_fields.= ", ". $select." as '".$pdate."'";
                        if ($flag) {
                            $where_custom_fields.= $select . ' > 0 OR ';
                        }
                        array_push($arr_date, $pdate);
                    }
                }

                $warehouses = "(
                    SELECT
                        tblwarehouse_items.id_items,
                        SUM(tblwarehouse_items.product_quantity) as quantity_warehouses
                    FROM tblwarehouse_items
                    WHERE tblwarehouse_items.type_items = 'product'
                    GROUP BY tblwarehouse_items.id_items
                ) warehouses";

                $this->db->select("
                    tbl_products.id as product_id,
                    COALESCE(tbl_products.quantity_minimum, 0) as quantity_minimum,
                    COALESCE(warehouses.quantity_warehouses, 0) as quantity_warehouses
                    $select_custom_fields
                    ", FALSE)
                ->from('tbl_products')
                ->join($warehouses, 'warehouses.id_items = tbl_products.id', 'left');

                if (!empty($where_custom_fields)) {
                    $where_custom_fields = trim($where_custom_fields);
                    $where_custom_fields = substr($where_custom_fields, 0, -2);
                    $this->datatables->where("( $where_custom_fields )");
                }

                if ($safe_inventory) {
                    $this->db->where('tbl_products.quantity_minimum >', 0);
                }

                $result = $this->db->get()->result_array();

                if (empty($result)) {
                    $data['result'] = 0;
                    $data['message'] = lang('not_data_exists');
                    echo json_encode($data); die;
                    // set_alert('danger', lang('not_data_exists'));
                    // redirect('admin/manufactures/add_productions_plan');
                }

                $productions_plan = [
                    'reference_no' => $reference_no,
                    'date' => $date,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'safe_inventory' => $safe_inventory,
                    'options1' => $options1,
                    'options1_reference_no' => $sales_orders,
                    'options1_id' => $sales_orders_id,
                    'options2' => $options2,
                    'options2_reference_no' => $business_plan,
                    'options2_id' => $business_plan_id,
                    'note' => $note,
                    'status' => $status,
                    'date_created' => date('Y-m-d H:i'),
                    'created_by' => get_staff_user_id(),
                ];

                // print_arrays($productions_plan);
                $productions_plan_id = $this->manufactures_model->insertProductionsPlan($productions_plan);
                if ($productions_plan_id) {
                    if (getReference('productions_plan') == $this->input->post('reference_no')) {
                        updateReference('productions_plan');
                    }

                    foreach ($result as $key => $value) {
                        $productions_plan_items = [
                            'productions_plan_id' => $productions_plan_id,
                            'product_id' => $value['product_id'],
                            'quantity_minimum' => $value['quantity_minimum'],
                            'quantity_warehouses' => $value['quantity_warehouses'],
                            'status' => 'not',
                        ];
                        $productions_plan_item_id = $this->manufactures_model->insertProductionsPlanItems($productions_plan_items);
                        foreach ($arr_date as $k => $val) {
                            if (empty($value[$val])) continue;
                            $productions_plan_details = [
                                'productions_plan_item_id' => $productions_plan_item_id,
                                'date' => $val,
                                'quantity' => $value[$val]
                            ];
                            $this->manufactures_model->insertProductionsPlanDetails($productions_plan_details);
                        }
                    }

                    set_alert('success', lang('success'));
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                    // redirect('admin/manufactures/productions_plan');
                    // return;
                }
            } else {
                $data['result'] = 0;
                $data['message'] = validation_errors();
                // set_alert('danger', validation_errors());
                // redirect('admin/manufactures/add_productions_plan');
            }
            echo json_encode($data);
            die;
        }
        $data['tnh'] = $this->tnh;
        $data['reference_no'] = getReference('productions_plan');
        $data['breadcrumb'] = [array('link' => base_url('admin/manufactures/productions_plan'), 'page' => lang('productions_plan')), array('link' => '#', 'page' => lang('tnh_add_productions_plan'))];
        $data['title'] = lang('tnh_add_productions_plan');
        $this->load->view('admin/manufactures/add_productions_plan', $data);
    }

    public function show_table_productions_plan()
    {
        $planning_cycle = $this->input->get('planning_cycle');
        $options1 = $this->input->get('options1');
        $options2 = $this->input->get('options2');
        $safe_inventory = $this->input->get('safe_inventory');
        $sales_orders = '';
        $business_plan = '';
        $arr_plan = [];
        $arr_date = [];
        $th = '';
        $targets = 6;
        $script = '';
        if (!empty($planning_cycle)) {
            $arr_plan = explode('-', trim($planning_cycle));

            $date1 = to_sql_date(trim($arr_plan[0]));
            $date2 = to_sql_date(trim($arr_plan[1]));

            if ($options1 == true) {
                $query = "(
                    SELECT
                        GROUP_CONCAT(distinct(CONCAT(tblorders.prefix, '', tblorders.code)) SEPARATOR ', ') as reference_orders
                    FROM tblorders
                    INNER JOIN tblorders_items ON tblorders_items.id_orders = tblorders.id
                    WHERE tblorders.status > 0 AND DATE_FORMAT(tblorders.date, '%Y-%m-%d') >= '$date1' AND DATE_FORMAT(tblorders.date, '%Y-%m-%d') <= '$date2' AND tblorders_items.type_items = 'products'
                )";
                $rs = $this->db->query($query)->row_array();
                $sales_orders = $rs['reference_orders'];
            }

            $date1 = new DateTime($date1);
            $date2 = new DateTime($date2);

            $diff = $date1->diff($date2);
            $days = $diff->days;

            for ($i = 0; $i <= $days; $i++) {
                $plus_date = date('d/m/Y', strtotime(to_sql_date(trim($arr_plan[0])). " + $i days"));

                $th.= '<th>'.$plus_date.'</th>';
                $script.= '{
                    "targets": '.$targets.', "name": "date'.$i.'", "className": "text-center",
                },';
                $targets++;
            }
        }
        $data['th'] = $th;
        $data['script'] = $script;
        $data['targets'] = $targets;
        $data['safe_inventory'] = $safe_inventory;
        $data['planning_cycle'] = $planning_cycle;
        $data['options1'] = $options1;
        $data['options2'] = $options2;
        $data['sales_orders'] = $sales_orders;
        $data['business_plan'] = $business_plan;
        $this->load->view('admin/manufactures/view_table_productions_plan', $data);
    }

    public function getShowTableProductionsPlan()
    {
        $condition_safe_inventory = $this->input->post('condition_safe_inventory');
        $condition_planning_cycle = $this->input->post('condition_planning_cycle');
        $condition_options1 = $this->input->post('condition_options1');
        $condition_options2 = $this->input->post('condition_options2');

        $select_custom_fields = "";
        $where_custom_fields = "";
        $custom = [];
        $custom_select = [];
        $target = 6;
        if (!empty($condition_planning_cycle)) {
            $arr_plan = explode('-', trim($condition_planning_cycle));
            $date1 = to_sql_date(trim($arr_plan[0]));
            $date2 = to_sql_date(trim($arr_plan[1]));

            $date1 = new DateTime($date1);
            $date2 = new DateTime($date2);

            $diff = $date1->diff($date2);
            $days = $diff->days;

            for ($i = 0; $i <= $days; $i++) {
                $date = date('Y-m-d', strtotime(to_sql_date(trim($arr_plan[0])). " + $i days"));
                $select = " 0  ";
                $flag = false;
                if ($condition_options1 == 1) {
                    $flag = true;
                    $select = "COALESCE((
                        SELECT
                            SUM(tblorders_items.quantity)
                        FROM tblorders
                        INNER JOIN tblorders_items ON tblorders_items.id_orders = tblorders.id
                        WHERE tblorders.status > 0 AND DATE_FORMAT(tblorders.date, '%Y-%m-%d') = '$date' AND tblorders_items.type_items = 'products' AND tblorders_items.id_product = tbl_products.id
                    ), 0)";
                } else if ($condition_options2 == 1) {
                    $flag = true;
                    $select.= " + 0 ";
                }
                $select_custom_fields.= ", ". $select." as date".$i;
                if ($flag) {
                    $where_custom_fields.= $select . ' > 0 OR ';
                }
                $custom[] = [
                    'index' => $target,
                    'select' => "date".$i,
                ];
                $custom_select[$target] = $select;
                $target++;
            }
        }

        $warehouses = "(
            SELECT
                tblwarehouse_items.id_items,
                SUM(tblwarehouse_items.product_quantity) as quantity_warehouses
            FROM tblwarehouse_items
            WHERE tblwarehouse_items.type_items = 'product'
            GROUP BY tblwarehouse_items.id_items
        ) warehouses";

        $this->datatables->select("
            0 as number_records,
            tbl_products.code as product_code,
            tbl_products.name as product_name,
            tblunits.unit as unit,
            tbl_products.quantity_minimum as quantity_minimum,
            COALESCE(warehouses.quantity_warehouses, 0) as quantity_warehouses
            $select_custom_fields
            ", FALSE)
        ->from('tbl_products')
        ->join('tblunits', 'tblunits.unitid = tbl_products.unit_id', 'left')
        ->join($warehouses, 'warehouses.id_items = tbl_products.id', 'left');

        if ($condition_safe_inventory) {
            $this->datatables->where('tbl_products.quantity_minimum >', 0);
        }

        if (!empty($where_custom_fields)) {
            $where_custom_fields = trim($where_custom_fields);
            $where_custom_fields = substr($where_custom_fields, 0, -2);
            $this->datatables->where("( $where_custom_fields )");
        }
        // print_arrays($this->db->get_compiled_select('tbl_products'), FALSE);
        $this->datatables->custom_ordering($custom);
        $this->datatables->custom_select($custom_select);

        $iDisplayStart = $this->input->post('iDisplayStart');
        $data = json_decode($this->datatables->generate());
        foreach ($data->aaData as $key => $value) {
            $data->aaData[$key][0] = ++$iDisplayStart;
        }
        echo json_encode($data);
    }

    public function getProductionsPlan()
    {
        $this->datatables->select("
            tbl_productions_plan.id as id,
            0 as number_records,
            tbl_productions_plan.date as date,
            tbl_productions_plan.reference_no as reference_no,
            CONCAT(DATE_FORMAT(tbl_productions_plan.date_start, '%d/%m/%Y'), ' - ', DATE_FORMAT(tbl_productions_plan.date_end, '%d/%m/%Y'), '') as planning_cycle,
            tbl_productions_plan.safe_inventory as safe_inventory,
            CONCAT(tbl_productions_plan.options1, '-', tbl_productions_plan.options2, '') as options,
            tbl_productions_plan.note as note,
            CONCAT(tblstaff.firstname, ' ', tblstaff.lastname,'') as created_by,
            tbl_productions_plan.status as status,
            CONCAT(tblstaff.firstname, ' ', tblstaff.lastname,'') as user_status
            ", FALSE)
        ->from('tbl_productions_plan')
        ->join('tblstaff', 'tblstaff.staffid = tbl_productions_plan.created_by', 'left')
        ->join('tblstaff staff_status', 'staff_status.staffid = tbl_productions_plan.user_status', 'left');

        $hidden = '<input type="hidden" name="productions_plan_id[]" class="productions_plan_id" value="$1">';

        $view = '<a class="tnh-modal btn btn-primary" title="'.lang('view').'" data-tnh="modal" data-toggle="modal" data-target="#myModal" href="'.base_url('admin/manufactures/view_productions_plan/$1').'"><i class="fa fa-file-text-o width-icon-actions"></i></a>';

        $excel = '<a class="btn btn-success export-excel" value="$1" title="'.lang('excel').'" href="javascript:void(0)"><i class="fa fa-file-excel-o width-icon-actions"></i></a>';

        $delete = '<a type="button" class="po btn btn-danger" title="'.lang('delete').'" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
            <button href=\''.base_url('admin/manufactures/delete_productions_plan/$1').'\' class=\'btn btn-danger po-delete-json\'>'.lang('delete').'</button>
            <button class=\'btn btn-default po-close\'>'.lang('close').'</button>
        "><i class="fa fa-remove width-icon-actions"></i></a>';

        $actions = '<div>'.$view.' '.$excel.' '.$delete.'</div>';
        $this->datatables->add_column('actions', $actions, 'id');
        // $this->datatables->unset_column('id');

        $iDisplayStart = $this->input->post('iDisplayStart');
        $data = json_decode($this->datatables->generate());
        foreach ($data->aaData as $key => $value) {
            $data->aaData[$key][1] = ++$iDisplayStart;
        }
        echo json_encode($data);
    }

    public function view_productions_plan($id) {
        $productions_plan = $this->manufactures_model->rowProductionsPlan($id);

        $th = '';
        $targets = 6;
        $script = '';
        $date1 = date_create($productions_plan['date_start']);
        $date2 = date_create($productions_plan['date_end']);
        $diff = date_diff($date1, $date2);
        $days = $diff->days;

        for ($i = 0; $i <= $days; $i++) {
            $plus_date = date('d/m/Y', strtotime($productions_plan['date_start']. " + $i days"));
            $th.= '<th>'.$plus_date.'</th>';
            $script.= '{
                "targets": '.$targets.', "name": "date'.$i.'", "className": "text-center",
            },';
            $targets++;
        }
        $data['th'] = $th;
        $data['script'] = $script;
        $data['targets'] = $targets;
        $data['created_by'] = get_staff_full_name($productions_plan['created_by']);
        $data['productions_plan'] = $productions_plan;
        $this->load->view('admin/manufactures/view_productions_plan', $data);
    }

    public function getViewProductionsPlan()
    {
        $filter_productions_plan_id = $this->input->post('filter_productions_plan_id');
        $filter_date_start = $this->input->post('filter_date_start');
        $filter_date_end = $this->input->post('filter_date_end');

        $target = 6;
        $date1 = date_create($filter_date_start);
        $date2 = date_create($filter_date_end);
        $diff = date_diff($date1, $date2);
        $days = $diff->days;

        $select_custom_fields = "";
        $custom = [];
        $custom_select = [];

        for ($i = 0; $i <= $days; $i++) {
            $date = date('Y-m-d', strtotime($filter_date_start. " + $i days"));
            $select = "
                COALESCE((
                    SELECT
                        tbl_productions_plan_details.quantity
                    FROM tbl_productions_plan_details
                    WHERE tbl_productions_plan_details.productions_plan_item_id = tbl_productions_plan_items.id AND DATE_FORMAT(tbl_productions_plan_details.date, '%Y-%m-%d') = '$date'
                ), 0)
            ";
            $select_custom_fields.= ", ". $select." as date".$i;

            $custom[] = [
                'index' => $target,
                'select' => "date".$i,
            ];
            $custom_select[$target] = $select;
            $target++;
        }

        $this->datatables->select("
            0 as number_records,
            tbl_products.code as product_code,
            tbl_products.name as product_name,
            tblunits.unit as unit,
            tbl_productions_plan_items.quantity_minimum as quantity_minimum,
            tbl_productions_plan_items.quantity_warehouses as quantity_warehouses,
            $select_custom_fields
            ", false)
        ->from('tbl_productions_plan_items')
        ->join('tbl_products', 'tbl_products.id = tbl_productions_plan_items.product_id')
        ->join('tblunits', 'tblunits.unitid = tbl_products.unit_id', 'left');

        $this->datatables->where('tbl_productions_plan_items.productions_plan_id', $filter_productions_plan_id);

        $this->datatables->custom_ordering($custom);
        $this->datatables->custom_select($custom_select);

        $iDisplayStart = $this->input->post('iDisplayStart');
        $data = json_decode($this->datatables->generate());
        foreach ($data->aaData as $key => $value) {
            $data->aaData[$key][0] = ++$iDisplayStart;
        }
        echo json_encode($data);
    }

    function delete_productions_plan($id)
    {
        $data = [];
        if ($id) {
            if ($this->manufactures_model->deleteProductionsPlan($id)) {
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

    public function export_excel_production_plan()
    {
        $response = [];
        if ($this->input->post('export_excel'))
        {
            ini_set('memory_limit', '3500M');
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');
            $productions_plan_id = $this->input->post('productions_plan_id');
            $productions_plan = $this->manufactures_model->rowProductionsPlan($productions_plan_id);
            if (empty($productions_plan)) {
                $response =  array(
                    'result' => 0,
                    'message' => lang('not_data_exists'),
                );
                echo json_encode($response); die;
            }
            $style_excel = style_excel();
            $cloumns_excel = cloumns_excel();

            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.2); // ~ 1.78cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setHeader(0.2); // ~1.02cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.2); // ~
            $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.2); // ~1.78cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.2); // ~1.73cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setFooter(0); // ~1.02cm

            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

            $objPHPExcel->getActiveSheet()->SetCellValue('A1', lang('date'))->getStyle('A1')->applyFromArray($style_excel['bold']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', _dt($productions_plan['date']));

            $objPHPExcel->getActiveSheet()->SetCellValue('A2', lang('tnh_planning_cycle'))->getStyle('A2')->applyFromArray($style_excel['bold']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B2', _d($productions_plan['date_start']).' - '._d($productions_plan['date_end']));

            $objPHPExcel->getActiveSheet()->SetCellValue('A3', lang('tnh_applied_standard'))->getStyle('A3')->applyFromArray($style_excel['bold']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B3', ($productions_plan['safe_inventory'] == 1 ? lang('tnh_safe_inventory') : lang('tnh_not')));

            $objPHPExcel->getActiveSheet()->SetCellValue('A4', lang('tnh_options'))->getStyle('A4')->applyFromArray($style_excel['bold']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B4', ($productions_plan['options1'] == 1 ? lang('tnh_sales_orders') : '').' - '. ($productions_plan['options2'] == 1 ? lang('tnh_business_plan') : ''));

            $objPHPExcel->getActiveSheet()->SetCellValue('A5', lang('note'))->getStyle('A5')->applyFromArray($style_excel['bold']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B5', strip_tags($productions_plan['note']));

            $objPHPExcel->getActiveSheet()->mergeCells('A6:H6');
            $objPHPExcel->getActiveSheet()->SetCellValue('A6', lang('productions_plan'))->getStyle('A6')->applyFromArray($style_excel['title']);;

            $date1 = date_create($productions_plan['date_start']);
            $date2 = date_create($productions_plan['date_end']);
            $diff = date_diff($date1, $date2);
            $days = $diff->days;
            $select_custom_fields = "";
            $arr_date = [];

            for ($i = 0; $i <= $days; $i++) {
                $date = date('d/m/Y', strtotime($productions_plan['date_start']. " + $i days"));
                $select = "
                    COALESCE((
                        SELECT
                            tbl_productions_plan_details.quantity
                        FROM tbl_productions_plan_details
                        WHERE tbl_productions_plan_details.productions_plan_item_id = tbl_productions_plan_items.id AND DATE_FORMAT(tbl_productions_plan_details.date, '%d/%m/%Y') = '$date'
                    ), 0)
                ";
                $select_custom_fields.= ", ". $select." as '".$date."'";
                array_push($arr_date, $date);
            }

            $this->db->select("
                tbl_productions_plan_items.id as id,
                tbl_products.code as product_code,
                tbl_products.name as product_name,
                tblunits.unit as unit,
                tbl_productions_plan_items.quantity_minimum as quantity_minimum,
                tbl_productions_plan_items.quantity_warehouses as quantity_warehouses
                $select_custom_fields
                ", false)
            ->from('tbl_productions_plan_items')
            ->join('tbl_products', 'tbl_products.id = tbl_productions_plan_items.product_id')
            ->join('tblunits', 'tblunits.unitid = tbl_products.unit_id', 'left');

            $this->db->where('tbl_productions_plan_items.productions_plan_id', $productions_plan_id);

            $data = $this->db->get()->result_array();

            $th = 7;
            $th_continue = 'G';
            $row = 8;
            $number_records = 1;
            foreach ($data as $key => $value) {
                if ($key == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue("A$th", lang('tnh_numbers'));
                    $objPHPExcel->getActiveSheet()->SetCellValue("B$th", lang('tnh_product_code'));
                    $objPHPExcel->getActiveSheet()->SetCellValue("C$th", lang('tnh_product_name'));
                    $objPHPExcel->getActiveSheet()->SetCellValue("D$th", lang('unit'));
                    $objPHPExcel->getActiveSheet()->SetCellValue("E$th", lang('tnh_safe_inventory'));
                    $objPHPExcel->getActiveSheet()->SetCellValue("F$th", lang('tnh_quantity_warehouses'));
                }
                $cloumn = 0;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $number_records);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $value['product_code']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $value['product_name']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $value['unit']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $value['quantity_minimum']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $value['quantity_warehouses']);

                $cloumn = 'G';
                foreach ($arr_date as $k => $val) {
                    if (!empty($value[$val]))
                    {
                        if ($key == 0) {
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($th_continue++, $th, $val);
                            $objPHPExcel->getActiveSheet()->getColumnDimension("$th_continue")->setAutoSize(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue("$th_continue$th", $val);
                            $th_continue++;
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue("$cloumn$row", $value[$val]);
                        $cloumn++;
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cloumn++, $row, $value[$val]);
                    }
                }

                $row++;
                $number_records++;
            }

            $objPHPExcel->getActiveSheet()->getStyle("A7:$th_continue$row")->applyFromArray($style_excel['border']);
            $objPHPExcel->getActiveSheet()->getStyle("A7:$th_continue".'7')->applyFromArray($style_excel['bold']);

            $filename = lang('productions_plan').'.xls';
            $objPHPExcel->getActiveSheet()->freezePane('A1');

            ob_start();
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="$filename"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
            $objWriter->save('php://output');
            $xlsData = ob_get_contents();
            ob_end_clean();

            $response =  array(
                'result' => 1,
                'filename' => $filename,
                'message' => lang('success'),
                'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
            );
        }
        echo json_encode($response);
    }

    function refereshReference()
    {
        $data = [];
        if ($this->input->get('referesh'))
        {
            $reference_no = getReference('productions_plan');
            if ($this->manufactures_model->checkExistProductionsPlanByReferenceNo($reference_no)) {
                $this->db->select('MAX(tbl_productions_plan.reference_no) as reference_no', false);
                $this->db->from('tbl_productions_plan');
                $rs = $this->db->get()->row_array();

                $max = $rs['reference_no'];
                $max = subReference($max);
                updateReferenceNormal('productions_plan', $max);
                $reference_no = getReference('productions_plan');
            }
            $data['reference_no'] = $reference_no;
            $data['message'] = lang('tnh_referesh_success');
        }
        echo json_encode($data);
    }

    function agreeProductionsPlan()
    {
        $data = [];
        if ($this->input->get())
        {
            $productions_plan_id = $this->input->get('productions_plan_id');
            $status = $this->input->get('status');
            $productions_plan = $this->manufactures_model->rowProductionsPlan($productions_plan_id);
            $date = date('Y-m-d H:i');
            $user_id = get_staff_user_id();
            if ($productions_plan == $status) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_please_referesh_table');
                echo json_encode($data); die;
            }
            $up = $this->manufactures_model->updateProductionsPlan($productions_plan_id, [
                'status' => $status,
                'date_status' => $date,
                'user_status' => $user_id
            ]);
            if ($up) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
        }
        echo json_encode($data);
    }
}
