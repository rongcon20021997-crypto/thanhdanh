<?php

// header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Items extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('items_model');
        $this->load->model('unit_model');
        // $this->lang->load('vietnamese/form_validation_lang');
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '1024';
        $this->upload_path = get_upload_path_by_type('materials');
        $this->datetime_now = time();
        $this->custom_fields = get_custom_fields('materials');
        $this->show_table_custom_fields = get_table_custom_fields('materials');
    }

    public function index()
    {
        $data['tnh'] = true;
        $data['path'] = $this->upload_path;
        $data['title'] = _l('tnh_item_materials_list');
        $th = '';
        $targets = 10;
        $script = '';
        if (!empty($this->show_table_custom_fields))
        {
            foreach ($this->show_table_custom_fields as $key => $value) {
                $th.= '<th>'._maybe_translate_custom_field_name($value['name'], $value['slug']).'</th>';
                $script.= '{
                    "targets": '.$targets.', "name": "'.$value['slug'].'"
                },';
                $targets++;
            }
        }
        $data['targets'] = $targets;
        $data['script'] = $script;
        $data['th'] = $th;
        $this->load->view('admin/items/manage', $data);
    }

    function getMaterials()
    {

        $exchange_items = "(
            SELECT
                tbl_exchange_items.item_id,
                GROUP_CONCAT(CONCAT(tblunits.unit, '::', tbl_exchange_items.number_exchange, '') SEPARATOR ':::') as ex
            FROM tbl_exchange_items
            LEFT JOIN tblunits ON tblunits.unitid = tbl_exchange_items.unit_id
            GROUP BY tbl_exchange_items.item_id
        ) as exchange_items";

        $select_custom_fields = "";
        $custom = [];
        $custom_select = [];
        $target = 10;
        if (!empty($this->show_table_custom_fields))
        {
            foreach ($this->show_table_custom_fields as $key => $value) {
                $select = "COALESCE((
                    SELECT tblcustomfieldsvalues.value
                    FROM tblcustomfieldsvalues
                    WHERE tblcustomfieldsvalues.fieldto = 'materials' AND tblcustomfieldsvalues.relid = tbl_materials.id AND tblcustomfieldsvalues.fieldid = ".$value['id']."
                ), '') ";
                $select_custom_fields.= ", ". $select." as ".$value['slug'];
                $custom[] = [
                    'index' => $target,
                    // 'cloumn' => $select,
                    'select' => $value['slug'],
                ];
                $custom_select[$target] = $select;
                $target++;
            }
        }

        // print_arrays($custom_ordering);

        $this->datatables->select("
            tbl_materials.id as id,
            tbl_materials.images as images,
            tbl_category_items.name as category_name,
            tbl_materials.code as code,
            tbl_materials.name as name,
            tblunits.unit as unit_name,
            exchange_items.ex as exchange,
            tbl_materials.price_import as price_import,
            tbl_materials.price_sell as price_sell,
            tbl_materials.note as note
            $select_custom_fields
            ", FALSE)
        ->from('tbl_materials')
        ->join('tbl_category_items', 'tbl_category_items.id = tbl_materials.category_id', 'left')
        ->join('tblunits', 'tblunits.unitid = tbl_materials.unit_id', 'left')
        ->join($exchange_items, 'exchange_items.item_id = tbl_materials.id', 'left');

        $this->datatables->custom_ordering($custom);
        $this->datatables->custom_select($custom_select);
        $this->datatables->add_column('actions', '
            <div>
                <a class="tnh-modal btn btn-success btn-icon" data-tnh="modal" data-toggle="modal" data-target="#myModal" href="'.base_url().'admin/items/edit_item/$1"><i class="fa fa-pencil"></i></a>
                <button type="button" class="btn btn-danger po btn-icon" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                        <button href=\''.base_url('admin/items/delete_material/$1').'\' class=\'btn btn-danger po-delete-json\'>'.lang('delete').'</button>
                        <button class=\'btn btn-default po-close\'>'.lang('close').'</button>
                    "><i class="fa fa-remove"></i></button>
            </div>
        ', 'id');
        echo $this->datatables->generate();
    }

    public function add_item()
    {
        $data = [];
        if ($this->input->post())
        {
            $this->form_validation->set_rules('category', lang("tnh_item_materials_category"), 'required');
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('unit', lang("unit"), 'required');
            $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_materials.code]');
            if ($this->form_validation->run() == true)
            {
                $category = $this->input->post('category');
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $note = $this->input->post('note');
                $quantity_begin = number_unformat($this->input->post('quantity_begin'));
                $price_import = number_unformat($this->input->post('price_import'));
                $price_sell = number_unformat($this->input->post('price_sell'));
                $unit = $this->input->post('unit');
                $note = $this->input->post('note');

                $options = [
                    'category_id' => $category,
                    'name' => $name,
                    'code' => $code,
                    'quantity_begin' => $quantity_begin,
                    'price_import' => $price_import,
                    'price_sell' => $price_sell,
                    'unit_id' => $unit,
                    'note' => $note,
                ];

                //exchange
                $exchange = false;
                $ex = $this->input->post('unit_exchange');
                if (!empty($ex)) {
                    foreach ($ex as $key => $value) {
                        if (empty($value)) continue;
                        $number_exchange = $this->input->post('number_exchange')[$key];
                        $exchange[$key]['unit_id'] = $value;
                        $exchange[$key]['number_exchange'] = $number_exchange;
                    }
                }
                //image
                $this->load->library('upload');
                if (!empty($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    // $config['max_size'] = $this->allowed_file_size;
                    // $config['max_width'] = $this->Settings->iwidth;
                    // $config['max_height'] = $this->Settings->iheight;
                    $config['file_name'] = vn_to_str($code).'_'.$this->datetime_now;
                    $config['overwrite'] = TRUE;
                    $config['max_filename'] = 25;
                    $config['encrypt_name'] = false;
                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload('image')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        $data['result'] = 0;
                        $data['message'] = $error;
                        echo json_encode($data);
                        return;
                    }
                    $images = $this->upload->file_name;
                    $options['images'] = $images;
                } else {
                    $options['images'] = NULL;
                }
                $id = $this->items_model->insertMaterials($options);
                if ($id) {
                    if (!empty($exchange)) {
                        foreach ($exchange as $key => $value) {
                            $exchange[$key]['item_id'] = $id;
                        }
                        $this->items_model->insertExchangeItems($exchange);
                    }
                    if ($this->input->post('custom_fields')) {
                        handle_custom_fields_post($id, $this->input->post('custom_fields'));
                    }
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    if (file_exists($this->upload_path.''.$images)) {
                        unlink($this->upload_path.''.$images);
                    }
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
            $data['custom_fields'] = $this->custom_fields;
            $data['units'] = $this->unit_model->getUnits();
            $this->load->view('admin/items/add_item', $data);
        }
    }

    public function edit_item($id)
    {
        $data = [];
        $material = $this->items_model->rowMaterial($id);
        if ($this->input->post())
        {
            $this->form_validation->set_rules('category', lang("tnh_item_materials_category"), 'required');
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('unit', lang("unit"), 'required');
            if ($material['code'] != $this->input->post('code'))
            {
                $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_materials.code]');
            }
            if ($this->form_validation->run() == true)
            {
                // print_arrays($this->input->post());
                $images_old = $material['images'];
                $category = $this->input->post('category');
                $name = $this->input->post('name');
                $code = $this->input->post('code');
                $note = $this->input->post('note');
                $quantity_begin = number_unformat($this->input->post('quantity_begin'));
                $price_import = number_unformat($this->input->post('price_import'));
                $price_sell = number_unformat($this->input->post('price_sell'));
                $unit = $this->input->post('unit');
                $note = $this->input->post('note');

                $options = [
                    'category_id' => $category,
                    'name' => $name,
                    'code' => $code,
                    'quantity_begin' => $quantity_begin,
                    'price_import' => $price_import,
                    'price_sell' => $price_sell,
                    'unit_id' => $unit,
                    'note' => $note,
                ];

                //exchange
                $exchange = false;
                $ex = $this->input->post('unit_exchange');
                if (!empty($ex)) {
                    foreach ($ex as $key => $value) {
                        if (empty($value)) continue;
                        $number_exchange = $this->input->post('number_exchange')[$key];
                        $exchange[$key]['item_id'] = $id;
                        $exchange[$key]['unit_id'] = $value;
                        $exchange[$key]['number_exchange'] = $number_exchange;
                    }
                }

                $this->load->library('upload');
                if (!empty($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    // $config['max_size'] = $this->allowed_file_size;
                    // $config['max_width'] = $this->Settings->iwidth;
                    // $config['max_height'] = $this->Settings->iheight;
                    $config['file_name'] = vn_to_str($code).'_'.$this->datetime_now;
                    $config['overwrite'] = TRUE;
                    $config['max_filename'] = 25;
                    $config['encrypt_name'] = false;
                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload('image')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        $data['result'] = 0;
                        $data['message'] = $error;
                        echo json_encode($data);
                        return;
                    }
                    $images = $this->upload->file_name;
                    $options['images'] = $images;
                } else {
                    $options['images'] = $images_old;
                }

                $up = $this->items_model->updateMaterials($id, $options);
                if ($up) {
                    if (!empty($exchange)) {
                        $this->items_model->deleteExchangeByItemId($id);
                        $this->items_model->insertExchangeItems($exchange);
                    }
                    if ($this->input->post('custom_fields')) {
                        handle_custom_fields_post($id, $this->input->post('custom_fields'));
                    }
                    if (!empty($images)) {
                        if (file_exists($this->upload_path.''.$images_old)) {
                            @unlink($this->upload_path.''.$images_old);
                        }
                    }
                    $data['result'] = 1;
                    $data['message'] = lang('success');
                } else {
                    if (file_exists($this->upload_path.''.$images)) {
                        unlink($this->upload_path.''.$images);
                    }
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
            $data['custom_fields'] = $this->custom_fields;
            $data['material'] = $material;
            $data['exchanges'] = $this->items_model->getExchangeItemsByItemId($id);
            $data['units'] = $this->unit_model->getUnits();
            $this->load->view('admin/items/edit_item', $data);
        }
    }

    function delete_material($id)
    {
        $data = [];
        if ($id) {
            $material = $this->items_model->rowMaterial($id);
            if ($this->items_model->checkMaterialUse($id)) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_exist_not_delete');
                echo json_encode($data); return;
            }
            if ($this->items_model->deleteMaterials($id)) {
                $this->items_model->deleteExchangeByItemId($id);
                deleteCustomFields('materials', $id);
                if (!empty($material['images'])) {
                    if (file_exists($this->upload_path.''.$material['images'])) {
                        @unlink($this->upload_path.''.$material['images']);
                    }
                }
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

    function delete_material_multiple()
    {
        $data = [];
        if ($this->input->post()) {
            if (!$this->input->post('material_id')) {
                $data['result'] = 0;
                $data['message'] = lang('no_data_exists');
                echo json_encode($data); return;
            }
            $errors = '';
            $count = 0;
            foreach ($this->input->post('material_id') as $key => $id) {
                $material = $this->items_model->rowMaterial($id);
                if ($this->items_model->checkMaterialUse($id)) {
                    $errors .= '<div class="text-danger">'.$material['code'].' '.lang('tnh_exist_not_delete').'</div>';
                    continue;
                }
                if ($this->items_model->deleteMaterials($id)) {
                    $this->items_model->deleteExchangeByItemId($id);
                    deleteCustomFields('materials', $id);
                    if (!empty($material['images'])) {
                        if (file_exists($this->upload_path.''.$material['images'])) {
                            @unlink($this->upload_path.''.$material['images']);
                        }
                    }
                    $count++;
                }
            }
            if ($count) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
            $data['errors'] = $errors;
            echo json_encode($data); return;
        } else {
            $data['result'] = 0;
            $data['message'] = lang('fail');
        }
        echo json_encode($data);
    }

    public function category()
    {
        $data['tnh'] = true;
        $data['title'] = _l('tnh_item_materials_category');
        $this->load->view('admin/items/category', $data);
    }

    public function add_category()
    {
        $data = [];
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_category_items.code]');
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

                $id = $this->items_model->insertCategoryItems($options);
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
            $this->load->view('admin/items/add_category', $data);
        }
    }

    public function edit_category($id)
    {
        $data = [];
        $category = $this->items_model->rowCategoryItems($id);
        if ($this->input->post())
        {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            if ($category['code'] != $this->input->post('code'))
            {
                $this->form_validation->set_rules('code', lang("code"), 'required|is_unique[tbl_category_items.code]');
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

                $id = $this->items_model->updateCategoryItems($id, $options);
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
            $data['category'] = $category;
            $this->load->view('admin/items/edit_category', $data);
        }
    }

    function getCategory()
    {
        $this->datatables->select("
            tbl_category_items.id as id,
            tbl_category_items.code as code,
            tbl_category_items.name as name,
            tbl_category_items.note as note,
            ", FALSE)
        ->from('tbl_category_items');

        $this->datatables->add_column('actions', '
            <div>
                <a class="tnh-modal btn btn-success btn-icon" data-tnh="modal" data-toggle="modal" data-target="#myModal" href="'.base_url().'admin/items/edit_category/$1"><i class="fa fa-pencil"></i></a>
                <button type="button" class="btn btn-danger po btn-icon" data-container="body" data-html="true" data-toggle="popover" data-placement="bottom" data-content="
                        <button href=\''.base_url('admin/items/delete_category/$1').'\' class=\'btn btn-danger po-delete-json\'>'.lang('delete').'</button>
                        <button class=\'btn btn-default po-close\'>'.lang('close').'</button>
                    "><i class="fa fa-remove"></i></button>
            </div>
        ', 'id');
        $result = json_decode($this->datatables->generate());
        echo (json_encode($result));
    }

    function delete_category($id)
    {
        $data = [];
        if ($id) {
            if ($this->items_model->checkExistCategory($id)) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_exist_not_delete');
                echo json_encode($data);
                return;
            }
            if ($this->items_model->deleteCategoryItems($id)) {
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

    function delete_category_multiple()
    {
        $data = [];
        if ($this->input->post()) {
            if (!$this->input->post('category_id')) {
                $data['result'] = 0;
                $data['message'] = lang('no_data_exists');
                echo json_encode($data); return;
            }
            $errors = '';
            $count = 0;
            foreach ($this->input->post('category_id') as $key => $id) {
                if ($this->items_model->checkExistCategory($id)) {
                    $row = $this->items_model->rowCategoryItems($id);
                    $errors .= '<div class="text-danger">'.$row['code'].' '.lang('tnh_exist_not_delete').'</div>';
                    continue;
                }
                if ($this->items_model->deleteCategoryItems($id)) {
                    $count++;
                }
            }
            if ($count) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
            $data['errors'] = $errors;
            echo json_encode($data); return;
        } else {
            $data['result'] = 0;
            $data['message'] = lang('fail');
        }
        echo json_encode($data);
    }

    function searchCategory()
    {
        $data = [];
        if ($this->input->get())
        {
            $q = $this->input->get('q');
            $limit = 50;
            $data = $this->items_model->searchCategory($q, $limit);
        }
        echo json_encode($data);
    }

    function searchMaterials()
    {
        $data = [];
        if ($this->input->get())
        {
            $q = $this->input->get('q');
            $limit = 50;
            $data = $this->items_model->searchMaterials($q, $limit);
        }
        echo json_encode($data);
    }

    function export_excel_category()
    {
        if ($this->input->post('export_excel'))
        {
            ini_set('memory_limit', '3500M');
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');

            // print_arrays($this->input->post());
            $cloumns = $this->input->post('cloumns');
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

            $this->db->select(implode(',', $cloumns), false);
            $this->db->from('tbl_category_items');
            $data = $this->db->get()->result_array();

            foreach($cloumns as $key => $value)
            {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cloumns_excel[$key])->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->SetCellValue($cloumns_excel[$key].'1', _l('tnh_'.$value))->getStyle($cloumns_excel[$key].'1')->applyFromArray($style_excel['Background_header']);
            }

            $row = 2;
            foreach ($data as $key => $value) {
                foreach ($cloumns as $k => $val) {
                    $index = $cloumns_excel[$k].$row;
                    $el = $value[$val];
                    $objPHPExcel->getActiveSheet()->SetCellValue($index, $el)->getStyle($index)->applyFromArray($style_excel['BStyle']);
                }
                $row++;
            }

            $filename = lang('tnh_item_materials_category').'.xls';
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
            die(json_encode($response));
        } else {
            $data['link'] = 'admin/items/export_excel_category';
            $list = [];
            $fields = get_fields_export($table = 'tbl_category_items', $arr_diff = false);
            foreach ($fields as $key => $value) {
                $list[] = [$value => mb_strtoupper(_l('tnh_' . $value), 'UTF-8')];
            }
            $data['list'] = $list;
            $this->load->view('admin/export_excel/export_excel', $data);
        }
    }

    function export_excel_items()
    {
        if ($this->input->post('export_excel'))
        {
            ini_set('memory_limit', '3500M');
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');

            // print_arrays($this->input->post());
            $cloumns = $this->input->post('cloumns');
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

            // $this->db->select(implode(',', $cloumns), false);
            // $this->db->from('tbl_category_items');
            // $data = $this->db->get()->result_array();

            $select = '';
            $left_join = '';

            foreach($cloumns as $key => $value)
            {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cloumns_excel[$key])->setAutoSize(true);

                //custom fields
                $custom = false;
                if (!empty($this->custom_fields)) {
                    foreach ($this->custom_fields as $k => $val) {
                        if ($value == ('custom_fields_'.$val['fieldto'].'_'.$val['id']))
                        {

                            $objPHPExcel->getActiveSheet()->SetCellValue($cloumns_excel[$key].'1', $val['name'])->getStyle($cloumns_excel[$key].'1')->applyFromArray($style_excel['Background_header']);

                            $value = $val['slug'];
                            $cloumns[$key] = $value;
                            $select.= "COALESCE((
                                            SELECT tblcustomfieldsvalues.value
                                            FROM tblcustomfieldsvalues
                                            WHERE tblcustomfieldsvalues.fieldto = 'materials' AND tblcustomfieldsvalues.relid = tbl_materials.id AND tblcustomfieldsvalues.fieldid = ".$val['id']."
                                        ), '') as $value, ";
                            $custom = true;
                            break;
                        }
                    }
                }
                if ($custom == true) continue;
                //end custom fields

                $objPHPExcel->getActiveSheet()->SetCellValue($cloumns_excel[$key].'1', _l('tnh_'.$value))->getStyle($cloumns_excel[$key].'1')->applyFromArray($style_excel['Background_header']);

                if ($value == "category_id")
                {
                    $value = 'category_name';
                    $cloumns[$key] = $value;
                    $select.= "tbl_category_items.name as $value, ";
                    $left_join.= " LEFT JOIN tbl_category_items ON tbl_category_items.id = tbl_materials.category_id";
                }
                else if ($value == "unit_id")
                {
                    $value = 'unit_name';
                    $cloumns[$key] = $value;
                    $select.= "tblunits.unit as $value, ";
                    $left_join.= " LEFT JOIN tblunits ON tblunits.unitid = tbl_materials.unit_id";
                }
                else if ($value == "exchange")
                {
                    $exchange_items = "(
                        SELECT
                            tbl_exchange_items.item_id,
                            GROUP_CONCAT(CONCAT(tblunits.unit, '->', tbl_exchange_items.number_exchange, '') SEPARATOR '\n') as ex
                        FROM tbl_exchange_items
                        LEFT JOIN tblunits ON tblunits.unitid = tbl_exchange_items.unit_id
                        GROUP BY tbl_exchange_items.item_id
                    ) as exchange_items";

                    $value = 'ex';
                    $cloumns[$key] = $value;
                    $select.= "exchange_items.ex as $value, ";
                    $left_join.= " LEFT JOIN $exchange_items ON exchange_items.item_id = tbl_materials.id";
                }
                else
                {
                    $select.= "tbl_materials.$value, ";
                }
            }
            // print_arrays($cloumns);

            $select = trim($select);
            $select = substr($select, 0, -1);
            $query = "
                SELECT $select
                FROM tbl_materials
                $left_join
            ";
            $data = $this->db->query($query)->result_array();
            $row = 2;
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    foreach ($cloumns as $k => $val) {
                        $index = $cloumns_excel[$k].$row;
                        $el = $value[$val];
                        if ($val == "price_import" || $val == "price_sell") {
                            $objPHPExcel->getActiveSheet()->SetCellValue($index, $el)->getStyle($index)->applyFromArray($style_excel['BStyle']);
                            $objPHPExcel->getActiveSheet()->getStyle($index)->getNumberFormat()->setFormatCode('#,##0.00');
                        }
                        else if ($val == "images")
                        {
                            $objPHPExcel->getActiveSheet()->SetCellValue($index, !empty($el) ? base_url('uploads/materials/').''.$el : '')->getStyle($index)->applyFromArray($style_excel['BStyle']);
                        }
                        else {
                            $objPHPExcel->getActiveSheet()->SetCellValue($index, $el)->getStyle($index)->applyFromArray($style_excel['BStyle']);
                        }
                    }
                    $row++;
                }
            }

            $filename = lang('materials').'.xls';
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
            die(json_encode($response));
        } else {
            $data['link'] = 'admin/items/export_excel_items';
            $list = [];
            $fields = get_fields_export($table = 'tbl_materials',
                $arr_diff = [
                    'quantity_begin'
                ],
                $arr_more = [
                    'exchange'
                ]
            );
            foreach ($fields as $key => $value) {
                $list[] = [$value => mb_strtoupper(_l('tnh_' . $value), 'UTF-8')];
            }
            foreach ($this->custom_fields as $key => $value) {
                $list[] = ['custom_fields_'.$value['fieldto'].'_'.$value['id'] => mb_strtoupper($value['name'], 'UTF-8')];
            }
            $data['list'] = $list;
            $this->load->view('admin/export_excel/export_excel', $data);
        }
    }

    public function import_category()
    {
        if ($this->input->post('save'))
        {
            $data = [];
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');

            $fullfile = $_FILES['file']['tmp_name'];
            if (empty($fullfile)) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_file_not_required');
                echo json_encode($data); return;
            }
            $extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if($extension != 'XLSX' && $extension != 'XLS'){
                $data['result'] = 0;
                $data['message'] = lang('tnh_not_format_excel');
                echo json_encode($data); return;
            }

            if (empty($fullfile)) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_file_not_required');
                echo json_encode($data); return;
            }

            if (!$this->input->post('fields')) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_fields_not_required');
                echo json_encode($data); return;
            }
            // if ($_FILES['userfile']['size'] > $this->allowed_file_size * 1024) {
            //     $this->session->set_flashdata('warning', lang('Không vượt quá '. $this->allowed_file_size. ' size'));
            //     redirect($_SERVER["HTTP_REFERER"]);
            //     return;
            // }
            $inputFileType  = PHPExcel_IOFactory::identify($fullfile);
            $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);

            /**  Load $inputFileName to a PHPExcel Object  **/
            $objPHPExcel = $objReader->load("$fullfile");

            $total_sheets = $objPHPExcel->getSheetCount();

            $allSheetName       = $objPHPExcel->getSheetNames();
            $objWorksheet       = $objPHPExcel->setActiveSheetIndex(0);
            $highestRow         = $objWorksheet->getHighestRow();
            $highestColumn      = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $arraydata          = array();

            $row_start = $this->input->post('row_start') ? $this->input->post('row_start') : 2;
            $row_end = $this->input->post('row_end') ? $this->input->post('row_end') : $highestRow;
            $cloumn_excel = $this->input->post('cloumn_excel');
            $fields = $this->input->post('fields');
            for ($row = $row_start; $row <= $row_end; ++$row) {
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    // $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    if (!empty($cloumn_excel[$col]))
                    {
                        $cloumn_current = $cloumn_excel[$col];
                        $index_current = $fields[$col];
                    } else {
                        continue;
                    }
                    $value = $objWorksheet->getCell($cloumn_current.$row)->getValue();
                    $arraydata[$row][$index_current] = $value;
                }
            }
            $options = [];
            $count = 0;
            $errors = '';
            foreach ($arraydata as $key => $value) {
                $code = !empty($value['code']) ? trim($value['code']) : '';
                $name = !empty($value['name']) ? trim($value['name']) : '';
                $note = !empty($value['note']) ? trim($value['note']) : '';
                if (empty($code) || empty($name)) {
                    continue;
                }
                $options = [
                    'code' => $code,
                    'name' => $name,
                    'note' => $note,
                ];
                if ($this->items_model->checkCategoryItemsByCode($code)) {
                    $errors.= '<div class="text-danger">'.$code.' '.lang('tnh_exist_data').'</div>';
                    continue;
                }
                $id = $this->items_model->insertCategoryItems($options);
                if ($id) {
                    $count++;
                }
            }
            if ($count) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('fail');
            }
            $data['errors'] = $errors;
            echo json_encode($data);
            // print_arrays($arraydata, $this->input->post());
        } else {
            $data['tnh'] = true;
            $data['title'] = _l('tnh_import_excel');
            $list = [];
            $fields = get_fields_export($table = 'tbl_category_items', $arr_diff = ['id']);
            foreach ($fields as $key => $value) {
                $list[$value] = mb_strtoupper(lang('tnh_' . $value), 'UTF-8');
            }
            $required = [lang('tnh_name'), lang('tnh_code')];
            $data['list'] = $list;
            $data['required'] = $required;
            $this->load->view('admin/items/import_category', $data);
        }
    }

    public function import_items()
    {
        if ($this->input->post('save'))
        {
            ini_set('max_execution_time', 600);
            $data = [];
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');

            $fullfile = $_FILES['file']['tmp_name'];
            if (empty($fullfile)) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_file_not_required');
                echo json_encode($data); return;
            }
            $extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if($extension != 'XLSX' && $extension != 'XLS'){
                $data['result'] = 0;
                $data['message'] = lang('tnh_not_format_excel');
                echo json_encode($data); return;
            }

            if (empty($fullfile)) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_file_not_required');
                echo json_encode($data); return;
            }

            if (!$this->input->post('fields')) {
                $data['result'] = 0;
                $data['message'] = lang('tnh_fields_not_required');
                echo json_encode($data); return;
            }
            // if ($_FILES['userfile']['size'] > $this->allowed_file_size * 1024) {
            //     $this->session->set_flashdata('warning', lang('Không vượt quá '. $this->allowed_file_size. ' size'));
            //     redirect($_SERVER["HTTP_REFERER"]);
            //     return;
            // }
            $inputFileType  = PHPExcel_IOFactory::identify($fullfile);
            $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);

            /**  Load $inputFileName to a PHPExcel Object  **/
            $objPHPExcel = $objReader->load("$fullfile");

            $total_sheets = $objPHPExcel->getSheetCount();

            $allSheetName       = $objPHPExcel->getSheetNames();
            $objWorksheet       = $objPHPExcel->setActiveSheetIndex(0);
            $highestRow         = $objWorksheet->getHighestRow();
            $highestColumn      = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $arraydata          = array();

            $row_start = $this->input->post('row_start') ? $this->input->post('row_start') : 2;
            $row_end = $this->input->post('row_end') ? $this->input->post('row_end') : $highestRow;
            $cloumn_excel = $this->input->post('cloumn_excel');
            $fields = $this->input->post('fields');
            for ($row = $row_start; $row <= $row_end; ++$row) {
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    // $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    if (!empty($cloumn_excel[$col]))
                    {
                        $cloumn_current = $cloumn_excel[$col];
                        $index_current = $fields[$col];
                    } else {
                        continue;
                    }
                    $value = $objWorksheet->getCell($cloumn_current.$row)->getValue();
                    $arraydata[$row][$index_current] = $value;
                }
            }

            $options = [];
            $count = 0;
            $errors = '';

            //option category
            $category_id_1 = $this->input->post('category_id_1');//where or like
            $category_id_2 = $this->input->post('category_id_2');//add or continue
            //unit
            $unit_id_1 = $this->input->post('unit_id_1');//where or like
            $unit_id_2 = $this->input->post('unit_id_2');//add or continue
            //exchange
            $exchange_1 = $this->input->post('exchange_1');//where or like
            $exchange_2 = $this->input->post('exchange_2');//add or continue

            //custom fields
            // $custom_fields = [];
            // foreach ($this->custom_fields as $key => $value) {
            //     $custom_fields[$value['fieldto']][$value['id']] = false;
            // }

            // print_arrays($custom_fields, $arraydata);

            foreach ($arraydata as $key => $value) {
                $category = !empty($value['category_id']) ? trim($value['category_id']) : '';
                $code = !empty($value['code']) ? trim($value['code']) : '';
                $name = !empty($value['name']) ? trim($value['name']) : '';
                $price_import = !empty($value['price_import']) ? number_unformat($value['price_import']) : 0;
                $price_sell = !empty($value['price_sell']) ? number_unformat($value['price_sell']) : 0;
                $unit = !empty($value['unit_id']) ? trim($value['unit_id']) : '';
                $note = !empty($value['note']) ? trim($value['note']) : '';
                $exchange = !empty($value['exchange']) ? trim($value['exchange']) : '';

                if (empty($code) || empty($name) || empty($category) || empty($unit)) {
                    continue;
                }
                //category
                if ($category_id_1) {
                    $row_category = $this->items_model->rowCategoryItemsByCode($category, 'id', $category_id_1);
                    if (!empty($row_category)) {
                        $category_id = $row_category['id'];
                    } else if ($category_id_2 == 'add') {
                        $category_id = $this->items_model->insertCategoryItems([
                            'code' => $category,
                            'name' => $category,
                        ]);
                    } else {
                        continue;
                    }
                }
                //unit
                if ($unit_id_1) {
                    $row_unit = $this->unit_model->rowUnitByCode($unit, 'unitid', $unit_id_1);
                    if (!empty($row_unit)) {
                        $unit_id = $row_unit['unitid'];
                    } else if ($unit_id_2 == 'add') {
                        $unit_id = $this->unit_model->insertUnit([
                            'unit' => $unit
                        ]);
                    } else {
                        continue;
                    }
                }
                //exchange
                $arr_ex = '';
                if (!empty($exchange))
                {
                    $exchange = explode('//', $exchange);
                    foreach ($exchange as $k => $val) {
                        if (empty($val)) continue;
                        $info_ex = explode('=>', $val);
                        $number_exchange = !empty($info_ex[0]) ? number_unformat($info_ex[0]) : 0;
                        $unit_exchange = !empty($info_ex[1]) ? trim($info_ex[1]) : 0;
                        $row_unit_exchange = $this->unit_model->rowUnitByCode($unit_exchange, 'unitid', $exchange_1);
                        if (!empty($row_unit_exchange)) {
                            $unit_exchange_id = $row_unit_exchange['unitid'];
                        } else if ($exchange_2 == 'add') {
                            $unit_exchange_id = $this->unit_model->insertUnit([
                                'unit' => $unit_exchange
                            ]);
                        } else {
                            continue;
                        }
                        $arr_ex[$k]['unit_id'] = $unit_exchange_id;
                        $arr_ex[$k]['number_exchange'] = $number_exchange;
                    }
                }

                //custom fields
                $custom_fields = [];
                foreach ($this->custom_fields as $k => $val) {
                    if (!empty($value['custom_fields_'.$val['fieldto'].'_'.$val['id']]))
                    {
                        $custom_fields[$val['fieldto']][$val['id']] = $value['custom_fields_'.$val['fieldto'].'_'.$val['id']];
                    }
                }
                // print_arrays($custom_fields);
                $options = [
                    'category_id' => $category_id,
                    'name' => $name,
                    'code' => $code,
                    'quantity_begin' => 0,
                    'price_import' => $price_import,
                    'price_sell' => $price_sell,
                    'unit_id' => $unit_id,
                    'note' => $note,
                ];
                //check exist
                if ($this->items_model->checkMaterialsByCode($code)) {
                    $errors.= '<div class="text-danger">'.$code.' '.lang('tnh_exist_data').'</div>';
                    continue;
                }
                $id = $this->items_model->insertMaterials($options);
                if ($id) {
                    $count++;
                    if (!empty($arr_ex)) {
                        foreach ($arr_ex as $key => $value) {
                            $arr_ex[$key]['item_id'] = $id;
                        }
                        $this->items_model->insertExchangeItems($arr_ex);
                    }
                    if (!empty($custom_fields)) {
                        handle_custom_fields_post($id, $custom_fields);
                    }
                }
            }
            if ($count) {
                $data['result'] = 1;
                $data['message'] = lang('success');
            } else {
                $data['result'] = 0;
                $data['message'] = lang('tnh_not_data_add');
            }
            $data['errors'] = $errors;
            echo json_encode($data); die;
        } else {
            $data['tnh'] = true;
            $data['title'] = _l('tnh_import_excel');
            $list = [];
            // print_arrays($this->custom_fields);
            $fields = get_fields_export($table = 'tbl_materials', $arr_diff = ['id', 'images', 'quantity_begin'], $arr_more = ['exchange']);
            foreach ($fields as $key => $value) {
                $list[$value] = mb_strtoupper(lang('tnh_' . $value), 'UTF-8');
            }
            //custom fields
            foreach ($this->custom_fields as $key => $value) {
                $list['custom_fields_'.$value['fieldto'].'_'.$value['id']] = $value['name'];
            }
            $required = [lang('tnh_category_id'), lang('tnh_name'), lang('tnh_code'), lang('tnh_unit_id')];
            $data['list'] = $list;
            $data['required'] = $required;
            $this->load->view('admin/items/import_items', $data);
        }
    }
}
