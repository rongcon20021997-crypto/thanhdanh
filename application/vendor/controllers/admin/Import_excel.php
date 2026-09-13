<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Import_excel extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('suppliers_model');
    }
    public function index()
    {

    }

    public function import_client()
    {
        $data['title'] = _l('cong_import_data_client');
        $data['colum_client'] = $this->db->list_fields('clients');
        $data['colum_client'] = array_diff($data['colum_client'], [
            'userid' ,
            'country' ,
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

        $data['colum_info_client'] = $this->db->get(db_prefix().'client_info_detail')->result_array();
        $data['columsExcel'] = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
            'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
            'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
        ];

        $data['country'] = get_table_where(db_prefix().'countries');
        $this->load->view('admin/import_excel/import_client', $data);
    }
    public function action_imports_client()
    {
        ob_end_clean();
        if($this->input->post())
        {
            require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
            $this->load->helper('security');
            $data = $this->input->post();
            $row_start = $data['row_start'];
            $row_end = $data['row_end'];
            $fieldsColums = $data['fieldsColums'];
            $Colum = $data['Colum'];

            $country = !empty($data['country']) ? $data['country'] : 0;

            $TypeData = !empty($data['type_data']) ? $data['type_data'] : [];
            $TypeEvent = !empty($data['type_event']) ? $data['type_event'] : [];

            $CountAdd = 0;
            $CountAll = 0;
            if (!empty($_FILES['file']))
            {
                $fullfile = $_FILES['file']['tmp_name'];

                $extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                if($extension != 'XLSX' && $extension != 'XLS'){
                    echo json_encode(['success' => false, 'alert_type' => 'success', 'message' => _l('cong_not_type')]);die();
                }

                $inputFileType = PHPExcel_IOFactory::identify($fullfile); $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load("$fullfile");
                $total_sheets = $objPHPExcel->getSheetCount();
                $allSheetName = $objPHPExcel->getSheetNames();
                $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString('ZZ');
                $list_data = array(); // tất cả dữ liệu lấy từ file excel
                $list_colums = array(); // lưu trử key của colums
                $row_start = !empty($row_start) ? $row_start : 1; // read start
                $row_end = !empty($row_end) ? $row_end : $highestRow; // read end
                for ($row = $row_start; $row <= $row_end; ++$row) // dòng
                {
                    //($value, $row) là tọa độ cột
                    //Cộng được tính bằng số không tính bằng Chử cái
                    foreach($Colum as $key => $value)
                    {
                        $Val = $objWorksheet->getCellByColumnAndRow($value, $row)->getValue();
                        if(!empty($fieldsColums[$key]) && (isset($value)) && $value != "" )
                        {
                            if(is_numeric($fieldsColums[$key]))
                            {
                                $list_data[$row - 1]['info'][$fieldsColums[$key]] = $Val;
                            }
                            else
                            {
                                $list_data[$row - 1][$fieldsColums[$key]] = $Val;
                            }
                            $list_colums[$fieldsColums[$key]] = $key;
                        }
                    }
                }

                $array_combobox = ['dt', 'kt', 'marriage', 'religion']; // các trường động lưu trong tblcombobox_client
                $CountAll = count($list_data);

                $client_info_detail = get_table_where(db_prefix().'client_info_detail');

                if($CountAll > 0)
                {
                    foreach ($list_data as $key => $row)
                    {
                        $continue = false;
                        $info = !empty($row['info']) ? $row['info'] : [];
                        unset($row['info']);
                        $row['datecreated'] = date('Y-m-d H:i:s');
                        $row['country'] = $country;
                        $row['prefix_client'] = !empty($row['prefix_client']) ? $row['prefix_client'] :date('ymd');
                        $row['gender'] = empty($row['gender']) ? 1 : ($row['gender'] == 'Nam' ? 1 : 2);
                        if(!empty($row['code_type']))
                        {
                            $row['code_type'] = 'NEW';
                        }

                        if(empty($row['company']))
                        {
                            $continue = true;
                        }

                        //Loại khách hàng
                        if(!empty($row['type_client']))
                        {
                            //Điều kiện combobox
                            if((empty($TypeData[$list_colums['type_client']])) || $TypeData[$list_colums['type_client']] == 1)
                            {
                                $this->db->where('name', trim($row['type_client']));
                            }
                            else if($TypeData[$list_colums['type_client']] == 2)
                            {
                                $this->db->like('name', $row['type_client']);
                            }
                            else
                            {
                                continue;
                            }
                            $type_client = $this->db->get(db_prefix().'type_client')->row();
                            if(!empty($type_client))
                            {
                                $row['type_client'] = $type_client->id;
                            }
                            else if(!empty($TypeEvent[$list_colums['type_client']]) && $TypeEvent[$list_colums['type_client']] == 1)
                            {
                                $this->db->insert(db_prefix().'type_client', [
                                        'name' => $row['type_client'],
                                        'create_by' => get_staff_user_id(),
                                        'date_create' => date('Y-m-d H:i:s')
                                    ]
                                );
                                $idType = $this->db->insert_id();
                                if(!empty($idType))
                                {
                                    $row['type_client'] = $idType;
                                }

                            }
                            else if(!empty($TypeEvent[$list_colums['type_client']]) && $TypeEvent[$list_colums['type_client']] == 2)
                            {

                                continue;
                            }
                            else
                            {
                                $row['type_client'] = NULL;
                            }
                        }
                        //Nguồn
                        if(!empty($row['sources']))
                        {
                            //Điều kiện combobox
                            if((empty($TypeData[$list_colums['sources']])) || $TypeData[$list_colums['sources']] == 1)
                            {
                                $this->db->where('name', trim($row['sources']));
                            }
                            else if($TypeData[$list_colums['sources']] == 2)
                            {
                                $this->db->like('name', $row['sources']);
                            }
                            else
                            {
                                continue;
                            }
                            $sources = $this->db->get(db_prefix().'leads_sources')->row();
                            if(!empty($sources))
                            {
                                $row['sources'] = $sources->id;
                            }
                            else if(!empty($TypeEvent[$list_colums['sources']]) && $TypeEvent[$list_colums['sources']] == 1)
                            {
                                $this->db->insert(db_prefix().'leads_sources', [
                                        'name' => $row['type_client']
                                    ]
                                );
                                $idsources = $this->db->insert_id();
                                if(!empty($idsources))
                                {
                                    $row['sources'] = $idsources;
                                }

                            }
                            else if(!empty($TypeEvent[$list_colums['sources']]) && $TypeEvent[$list_colums['sources']] == 2)
                            {

                                continue;
                            }
                            else
                            {
                                $row['sources'] = NULL;
                            }
                        }
                        //Thành phố
                        if(!empty($row['city']))
                        {
                            if($TypeData[$list_colums['city']] == 1 || empty($TypeData[$list_colums['city']]))
                            {
                                $this->db->where('name', trim($row['city']));
                            }
                            else if($TypeData[$list_colums['city']] == 2)
                            {
                                $this->db->like('name', trim($row['city']));
                            }
                            else
                            {
                                continue;
                            }
                            $province = $this->db->get(db_prefix().'province')->row();
                            if(!empty($province))
                            {
                                $row['city'] = $province->provinceid;
                            }
                            else if(empty($TypeEvent[$list_colums['city']]) || $TypeEvent[$list_colums['city']] == 2)
                            {
                                continue;
                            }
                            else
                            {
                                $row['city'] = NULL;
                            }
                        }

                        //Quận huyện
                        if(!empty($row['district']))
                        {
                            if($TypeData[$list_colums['district']] == 1 || empty($TypeData[$list_colums['district']]))
                            {
                                $this->db->where('name', trim($row['district']));
                            }
                            else if($TypeData[$list_colums['district']] == 2)
                            {
                                $this->db->like('name', trim($row['district']));
                            }
                            else
                            {
                                continue;
                            }
                            $district = $this->db->get(db_prefix().'district')->row();
                            if(!empty($district))
                            {
                                $row['district'] = $district->districtid;
                            }
                            else if(empty($TypeEvent[$list_colums['district']]) || $TypeEvent[$list_colums['district']] == 2)
                            {
                                continue;
                            }
                            else
                            {
                                $row['district'] = NULL;
                            }
                        }

                        //Quận huyện
                        if(!empty($row['ward']))
                        {
                            if($TypeData[$list_colums['ward']] == 1 || empty($TypeData[$list_colums['ward']]))
                            {
                                $this->db->where('name', trim($row['ward']));
                            }
                            else if($TypeData[$list_colums['ward']] == 2)
                            {
                                $this->db->like('name', trim($row['ward']));
                            }
                            else
                            {
                                continue;
                            }
                            $ward = $this->db->get(db_prefix().'ward')->row();
                            if(!empty($ward))
                            {
                                $row['ward'] = $ward->wardid;
                            }
                            else if(empty($TypeEvent[$list_colums['ward']]) || $TypeEvent[$list_colums['ward']] == 2)
                            {
                                continue;
                            }
                            else
                            {
                                $row['ward'] = NULL;
                            }
                        }

                        //Các trường động combobox
                        foreach($array_combobox as $valCBB)
                        {

                            if($valCBB == 'dt' || $valCBB == 'kt' || $valCBB == 'marriage' || $valCBB == 'religion')
                            {
                                if(!empty($row[$valCBB]))
                                {
                                    if(empty($TypeData[$list_colums[$valCBB]]) || $TypeData[$list_colums[$valCBB]] == 1)
                                    {
                                        $this->db->where('name', trim( $row[$valCBB] ));
                                    }
                                    else if($TypeData[$list_colums[$valCBB]] == 2)
                                    {
                                        $this->db->like('name', trim( $row[$valCBB] ));
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                    $this->db->where('type', $valCBB);
                                    $CBB = $this->db->get(db_prefix().'combobox_client')->row();
                                    if(!empty($CBB))
                                    {
                                        $row[$valCBB] = $CBB->id;
                                    }
                                    else
                                    {
                                        if(!empty($TypeEvent[$list_colums[$valCBB]]) && $TypeEvent[$list_colums[$valCBB]] == 1)
                                        {
                                            $this->db->insert(db_prefix().'combobox_client', [
                                                    'name' => $row['type_client'],
                                                    'create_by' => get_staff_user_id(),
                                                    'date_create' => date('Y-m-d H:i:s'),
                                                    'type'  =>  $valCBB
                                                ]
                                            );
                                            $idCbb = $this->db->insert_id();
                                            if(!empty($idCbb))
                                            {
                                                $row[$valCBB] = $idCbb;
                                            }
                                            else
                                            {
                                                $row[$valCBB] = NULL;
                                            }
                                        }
                                        else if(empty($TypeEvent[$list_colums[$valCBB]]) || $TypeEvent[$list_colums[$valCBB]] == 2)
                                        {
                                            break;
                                            $continue = true;
                                        }
                                        else
                                        {
                                            $row[$valCBB] = NULL;
                                        }
                                    }
                                }
                            }
                        }
                        if(!empty($continue))
                        {
                            continue;
                        }
                        if(empty($row['debt_limit']))
                        {
                            $row['debt_limit'] = 0;
                        }
                        if(empty($row['debt_limit_day']))
                        {
                            $row['debt_limit_day'] = 0;
                        }
                        if(empty($row['discount']))
                        {
                            $row['discount'] = 0;
                        }
                        if(empty($row['vip_rating']))
                        {
                            $row['vip_rating'] = 0;
                        }
                        if(!empty($client_info_detail) && !$continue)
                        {
                            foreach($info as $Kinfo => $Vinfo)
                            {
                                if(!empty($Vinfo))
                                {
                                    foreach($client_info_detail as $kdetail => $vdetail)
                                    {
                                        if(!empty($continue))
                                        {
                                            break;
                                        }
                                        if($vdetail['id'] == $Kinfo)
                                        {
                                            if($vdetail['type_form'] == 'select' || $vdetail['type_form'] == 'radio')
                                            {
                                                if(empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1)
                                                {
                                                    $this->db->where('name', $Vinfo);
                                                }
                                                else if($TypeData[$list_colums[$Kinfo]] == 2)
                                                {
                                                    $this->db->like('name', $Vinfo);
                                                }
                                                else
                                                {
                                                    continue;
                                                }
                                                $info_detail_value = $this->db->get(db_prefix().'client_info_detail_value')->row();
                                                if(!empty($info_detail_value))
                                                {
                                                    $row['info_detail'][$Kinfo] = $info_detail_value->id;
                                                }
                                                else if(!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1)
                                                {
                                                    $this->db->insert(db_prefix().'client_info_detail_value', [
                                                            'name' => $Vinfo,
                                                            'id_info_detail' => $Kinfo
                                                        ]
                                                    );
                                                    $idType = $this->db->insert_id();
                                                    if(!empty($idType))
                                                    {
                                                        $row['info_detail'][$Kinfo] = $idType;
                                                    }

                                                }
                                                else if(empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2)
                                                {

                                                    $continue = true;
                                                    break;
                                                }
                                                else
                                                {
                                                    $row['info_detail'][$Kinfo] = NULL;
                                                }
                                            }
                                            else if($vdetail['type_form'] == 'select multiple' || $vdetail['type_form'] == 'checkbox')
                                            {
                                                $valData = explode(',', $Vinfo);
                                                foreach($valData as $VKeyData)
                                                {

                                                    if(empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1)
                                                    {
                                                        $this->db->where('name', $VKeyData);
                                                    }
                                                    else if($TypeData[$list_colums[$Kinfo]] == 2)
                                                    {
                                                        $this->db->like('name', $VKeyData);
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                    $info_detail_value = $this->db->get(db_prefix().'client_info_detail_value')->row();
                                                    if(!empty($info_detail_value))
                                                    {
                                                        $row['info_detail'][$Kinfo][] = $info_detail_value->id;
                                                    }
                                                    else if(!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1)
                                                    {
                                                        $this->db->insert(db_prefix().'client_info_detail_value', [
                                                                'name' => $VKeyData,
                                                                'id_info_detail' => $Kinfo
                                                            ]
                                                        );
                                                        $idType = $this->db->insert_id();
                                                        if(!empty($idType))
                                                        {
                                                            $row['info_detail'][$Kinfo][] = $idType;
                                                        }

                                                    }
                                                    else if(empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2)
                                                    {

                                                        $continue = true;
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        $row['info_detail'][$Kinfo] = NULL;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $row['info_detail'][$Kinfo] = $Vinfo;
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if(!empty($continue))
                        {
                            continue;
                        }
                        $userid = $this->clients_model->add($row);
                        if(!empty($userid))
                        {
                            $CountAdd++;
                        }
                    }
                }
                echo json_encode([
                    'success' => true,
                    'message' => _l('cong_insert_client_quantity').' '.$CountAdd.'/'.count($list_data),
                    'alert_type' => 'success'
                ]);die();
            }
            echo json_encode([
                'success' => false,
                'message' => _l('cong_not_found_file_excel'),
                'alert_type' => 'danger'
            ]);die();
        }
        die();

    }
    public function import_suppliers()
    {
        $data['title'] = _l('Nhập dữ liệu nhà cung cấp');
        $data['colum_suppliers'] = $this->db->list_fields(db_prefix().'suppliers');
        $data['colum_suppliers'] = array_diff($data['colum_suppliers'], [
            'default_language' ,
            'default_currency',
        ]);

        $data['colum_info_suppliers'] = $this->db->get(db_prefix().'suppliers_info_detail')->result_array();
        $data['columsExcel'] = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
            'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
            'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
        ];
        $data['country'] = get_table_where(db_prefix().'countries');
        $this->load->view('admin/import_excel/import_suppliers', $data);
    }
public function action_imports_suppliers()
    {
        ob_end_clean();
        if($this->input->post())
        {
            require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
            $this->load->helper('security');
            $data = $this->input->post();
            $row_start = $data['row_start'];
            $row_end = $data['row_end'];
            $fieldsColums = $data['fieldsColums'];
            $Colum = $data['Colum'];

            $country = !empty($data['country']) ? $data['country'] : 0;

            $TypeData = !empty($data['type_data']) ? $data['type_data'] : [];
            $TypeEvent = !empty($data['type_event']) ? $data['type_event'] : [];

            $CountAdd = 0;
            $CountAll = 0;
            if (!empty($_FILES['file']))
            {
                $fullfile = $_FILES['file']['tmp_name'];

                $extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                if($extension != 'XLSX' && $extension != 'XLS'){
                    $this->session->set_flashdata('warning', lang('Không đúng định dạng excel'));
                    redirect($_SERVER["HTTP_REFERER"]);
                    return;
                }

                $inputFileType = PHPExcel_IOFactory::identify($fullfile); $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load("$fullfile");
                $total_sheets = $objPHPExcel->getSheetCount();
                $allSheetName = $objPHPExcel->getSheetNames();
                $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString('ZZ');
                $list_data = array(); // tất cả dữ liệu lấy từ file excel
                $list_colums = array(); // lưu trử key của colums
                $row_start = !empty($row_start) ? $row_start : 1; // read start
                $row_end = !empty($row_end) ? $row_end : $highestRow; // read end
                for ($row = $row_start; $row <= $row_end; ++$row) // dòng
                {
                    //($value, $row) là tọa độ cột
                    //Cộng được tính bằng số không tính bằng Chử cái
                    foreach($Colum as $key => $value)
                    {
                        $Val = $objWorksheet->getCellByColumnAndRow($value, $row)->getValue();
                        if(!empty($fieldsColums[$key]) && (isset($value)) && $value != "" )
                        {
                            if(is_numeric($fieldsColums[$key]))
                            {
                                $list_data[$row - 1]['info'][$fieldsColums[$key]] = $Val;
                            }
                            else
                            {
                                $list_data[$row - 1][$fieldsColums[$key]] = $Val;
                            }
                            $list_colums[$fieldsColums[$key]] = $key;
                        }
                    }
                }
                $CountAll = count($list_data);

                $client_info_detail = get_table_where(db_prefix().'suppliers_info_detail');

                if($CountAll > 0)
                {
                    foreach ($list_data as $key => $row)
                    {
                        $continue = false;
                        $info = !empty($row['info']) ? $row['info'] : [];
                        unset($row['info']);
                        $row['datecreated'] = date('Y-m-d H:i:s');
                        $row['country'] = $country;
                        $row['prefix'] = get_option('prefix_supplier');
                        if(empty($row['company']))
                        {
                            $continue = true;
                        }
                        //nhóm
                        if(!empty($row['groups_in']))
                        {
                            //Điều kiện combobox
                            if((empty($TypeData[$list_colums['groups_in']])) || $TypeData[$list_colums['groups_in']] == 1)
                            {
                                $this->db->where('name', trim($row['groups_in']));
                            }
                            else if($TypeData[$list_colums['groups_in']] == 2)
                            {
                                $this->db->like('name', $row['groups_in']);
                            }
                            else
                            {
                                continue;
                            }
                            $groups_in = $this->db->get(db_prefix().'suppliers_groups')->row();
                            if(!empty($groups_in))
                            {
                                $row['groups_in'] = $groups_in->id;
                            }
                            else if(!empty($TypeEvent[$list_colums['groups_in']]) && $TypeEvent[$list_colums['groups_in']] == 1)
                            {
                                $this->db->insert(db_prefix().'suppliers_groups', [
                                        'name' => $row['groups_in']
                                    ]
                                );
                                $idsources = $this->db->insert_id();
                                if(!empty($idsources))
                                {
                                    $row['groups_in'] = $idsources;
                                }

                            }
                            else if(!empty($TypeEvent[$list_colums['groups_in']]) && $TypeEvent[$list_colums['groups_in']] == 2)
                            {

                                continue;
                            }
                            else
                            {
                                $row['groups_in'] = NULL;
                            }
                        }
                        //Thành phố
                        if(!empty($row['city']))
                        {
                            if($TypeData[$list_colums['city']] == 1 || empty($TypeData[$list_colums['city']]))
                            {
                                $this->db->where('name', trim($row['city']));
                            }
                            else if($TypeData[$list_colums['city']] == 2)
                            {
                                $this->db->like('name', trim($row['city']));
                            }
                            else
                            {
                                continue;
                            }
                            $province = $this->db->get(db_prefix().'province')->row();
                            if(!empty($province))
                            {
                                $row['city'] = $province->provinceid;
                            }
                            else if(empty($TypeEvent[$list_colums['city']]) || $TypeEvent[$list_colums['city']] == 2)
                            {
                                continue;
                            }
                            else
                            {
                                $row['city'] = NULL;
                            }
                        }
                        //Quận huyện
                        if(!empty($row['district']))
                        {
                            if($TypeData[$list_colums['district']] == 1 || empty($TypeData[$list_colums['district']]))
                            {
                                $this->db->where('name', trim($row['district']));
                            }
                            else if($TypeData[$list_colums['district']] == 2)
                            {
                                $this->db->like('name', trim($row['district']));
                            }
                            else
                            {
                                continue;
                            }
                            $district = $this->db->get(db_prefix().'district')->row();
                            if(!empty($district))
                            {
                                $row['district'] = $district->districtid;
                            }
                            else if(empty($TypeEvent[$list_colums['district']]) || $TypeEvent[$list_colums['district']] == 2)
                            {
                                continue;
                            }
                            else
                            {
                                $row['district'] = NULL;
                            }
                        }

                        //Quận huyện
                        if(!empty($row['ward']))
                        {
                            if($TypeData[$list_colums['ward']] == 1 || empty($TypeData[$list_colums['ward']]))
                            {
                                $this->db->where('name', trim($row['ward']));
                            }
                            else if($TypeData[$list_colums['ward']] == 2)
                            {
                                $this->db->like('name', trim($row['ward']));
                            }
                            else
                            {
                                continue;
                            }
                            $ward = $this->db->get(db_prefix().'ward')->row();
                            if(!empty($ward))
                            {
                                $row['ward'] = $ward->wardid;
                            }
                            else if(empty($TypeEvent[$list_colums['ward']]) || $TypeEvent[$list_colums['ward']] == 2)
                            {
                                continue;
                            }
                            else
                            {
                                $row['ward'] = NULL;
                            }
                        }

                        if(!empty($continue))
                        {
                            continue;
                        }
                        if(!empty($client_info_detail) && !$continue)
                        {
                            foreach($info as $Kinfo => $Vinfo)
                            {

                                if(!empty($Vinfo))
                                {

                                    foreach($client_info_detail as $kdetail => $vdetail)
                                    {

                                        if(!empty($continue))
                                        {
                                            break;
                                        }
                                        if($vdetail['id'] == $Kinfo)
                                        {

                                            if($vdetail['type_form'] == 'select' || $vdetail['type_form'] == 'radio')
                                            {
                                                if(empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1)
                                                {
                                                    $this->db->where('name', $Vinfo);
                                                }
                                                else if($TypeData[$list_colums[$Kinfo]] == 2)
                                                {
                                                    $this->db->like('name', $Vinfo);
                                                }
                                                else
                                                {
                                                    continue;
                                                }
                                                $info_detail_value = $this->db->get(db_prefix().'suppliers_info_detail_value')->row();
                                                if(!empty($info_detail_value))
                                                {
                                                    $row['info_detail'][$Kinfo] = $info_detail_value->id;
                                                }
                                                else if(!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1)
                                                {
                                                    $this->db->insert(db_prefix().'suppliers_info_detail_value', [
                                                            'name' => $Vinfo,
                                                            'id_info_detail' => $Kinfo
                                                        ]
                                                    );
                                                    $idType = $this->db->insert_id();
                                                    if(!empty($idType))
                                                    {
                                                        $row['info_detail'][$Kinfo] = $idType;
                                                    }

                                                }
                                                else if(empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2)
                                                {

                                                    $continue = true;
                                                    break;
                                                }
                                                else
                                                {
                                                    $row['info_detail'][$Kinfo] = NULL;
                                                }
                                            }
                                            else if($vdetail['type_form'] == 'select multiple' || $vdetail['type_form'] == 'checkbox')
                                            {
                                                $valData = explode(',', $Vinfo);
                                                foreach($valData as $VKeyData)
                                                {

                                                    if(empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1)
                                                    {
                                                        $this->db->where('name', $VKeyData);
                                                    }
                                                    else if($TypeData[$list_colums[$Kinfo]] == 2)
                                                    {
                                                        $this->db->like('name', $VKeyData);
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                    $info_detail_value = $this->db->get(db_prefix().'suppliers_info_detail_value')->row();
                                                    if(!empty($info_detail_value))
                                                    {
                                                        $row['info_detail'][$Kinfo][] = $info_detail_value->id;
                                                    }
                                                    else if(!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1)
                                                    {
                                                        $this->db->insert(db_prefix().'suppliers_info_detail_value', [
                                                                'name' => $VKeyData,
                                                                'id_info_detail' => $Kinfo
                                                            ]
                                                        );
                                                        $idType = $this->db->insert_id();
                                                        if(!empty($idType))
                                                        {
                                                            $row['info_detail'][$Kinfo][] = $idType;
                                                        }

                                                    }
                                                    else if(empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2)
                                                    {

                                                        $continue = true;
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        $row['info_detail'][$Kinfo] = NULL;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $row['info_detail'][$Kinfo] = $Vinfo;
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        if(!empty($continue))
                        {
                            continue;
                        }
                        if(!empty($row['code']))
                        {
                            $ktr_code = get_table_where('tblsuppliers',array('code'=>$row['code']),'','row');
                            if(!empty($ktr_code))
                            {
                                continue;
                            }
                        }
                        // if(!empty($row['vat']))
                        // {
                        //     $ktr_code = get_table_where('tblsuppliers',array('vat'=>$row['vat']),'','row');
                        //     if(!empty($ktr_code))
                        //     {
                        //         continue;
                        //     }
                        // }
                        // if(!empty($row['email']))
                        // {
                        //     $ktr_code = get_table_where('tblsuppliers',array('email'=>$row['email']),'','row');
                        //     if(!empty($ktr_code))
                        //     {
                        //         continue;
                        //     }
                        // }
                        $userid = $this->suppliers_model->add_suppliers($row);
                        if(!empty($userid))
                        {
                            $CountAdd++;
                        }
                    }
                }
                echo json_encode([
                    'success' => true,
                    'message' => _l('cong_insert_client_quantity').' '.$CountAdd.'/'.count($list_data),
                    'alert_type' => 'success'
                ]);die();
            }
            echo json_encode([
                'success' => false,
                'message' => _l('cong_not_found_file_excel'),
                'alert_type' => 'danger'
            ]);die();
        }
        die();

    }
}
