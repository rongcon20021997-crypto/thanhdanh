<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Export_excel extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {

    }

    public function action_export_client()
    {
        ob_end_clean();
        if($this->input->post())
        {

            $this->db->order_by('id','desc');
            $info_detail = $this->db->get('tblclient_info_detail')->result_array();

            $group_by = [];


            $data = $this->input->post('list_colum');
            $select_colum = $data;
            $select_colum[] = db_prefix().'type_client.name as type_client';
            $select_colum[] = 'r.name as religion';
            $select_colum[] = 'm.name as marriage';
            $select_colum[] = 'd.name as dt';
            $select_colum[] = 'k.name as kt';
            $select_colum[] = db_prefix().'countries.short_name as country';
            $select_colum[] = db_prefix().'province.name as city';
            $select_colum[] = db_prefix().'district.name as district';
            $select_colum[] = db_prefix().'ward.name as ward';
            $select_colum[] = db_prefix().'leads_sources.name as sources';

            $this->db->join(db_prefix().'type_client', db_prefix().'type_client.id = '.db_prefix().'clients.type_client', 'left');
            $this->db->join(db_prefix().'combobox_client r', 'r.id = '.db_prefix().'clients.religion', 'left');
            $this->db->join(db_prefix().'combobox_client m', 'm.id = '.db_prefix().'clients.marriage', 'left');
            $this->db->join(db_prefix().'combobox_client d', 'd.id = '.db_prefix().'clients.dt', 'left');
            $this->db->join(db_prefix().'combobox_client k', 'd.id = '.db_prefix().'clients.kt', 'left');
            $this->db->join(db_prefix().'countries', db_prefix().'countries.country_id = '.db_prefix().'clients.country', 'left');
            $this->db->join(db_prefix().'province', db_prefix().'province.provinceid = '.db_prefix().'clients.city', 'left');
            $this->db->join(db_prefix().'district', db_prefix().'district.districtid = '.db_prefix().'clients.district', 'left');
            $this->db->join(db_prefix().'ward', db_prefix().'ward.wardid = '.db_prefix().'clients.ward', 'left');
            $this->db->join(db_prefix().'leads_sources', db_prefix().'leads_sources.id = '.db_prefix().'clients.sources', 'left');

            $array_name_info = [];
            foreach($info_detail as $key => $value)
            {
                $array_name_info[$value['id']] = $value['name'];
                if($value['type_form'] == 'select multiple' || $value['type_form'] == 'checkbox') {
                    $select_colum []= '(
                            SELECT 
                                GROUP_CONCAT(table_detail_info_value_'.$value['id'].'.name)
                            FROM  '.db_prefix().'client_value table_detail_info_'.$value['id'].'
                            LEFT JOIN '.db_prefix().'client_info_detail_value  table_detail_info_value_'.$value['id'].' ON table_detail_info_value_'.$value['id'].'.id = table_detail_info_'.$value['id'].'.value
                            WHERE client = tblclients.userid AND  table_detail_info_'.$value['id'].'.id_detail = '.$value['id'].'
                        ) as value_info_'.$value['id'];
                    $join[] = 'LEFT JOIN tblclient_info_detail_value as table_detail_info_value_' . $value['id'] . '
                        ON table_detail_info_value_' . $value['id'] . '.id = table_detail_info_' . $value['id'] . '.value';
                    $group_by[] = 'table_detail_info_' . $value['id'] . '.client';

                    $this->db->join('tblclient_value as table_detail_info_' . $value['id'], db_prefix() . 'clients.userid = table_detail_info_' . $value['id'] . '.client AND  table_detail_info_' . $value['id'] . '.id_detail =' . $value['id'], 'left');
                    $this->db->join('tblclient_info_detail_value as table_detail_info_value_' . $value['id'], 'table_detail_info_value_' . $value['id'] . '.id = table_detail_info_' . $value['id'] . '.value', 'left');
                }
                else if($value['type_form'] == 'select' || $value['type_form'] == 'radio')
                {
                    $select_colum []= 'table_detail_info_value_' . $value['id'] . '.name as value_info_'.$value['id'];
                    $this->db->join('tblclient_value table_detail_info_' . $value['id'], db_prefix() . 'clients.userid = table_detail_info_' . $value['id'] . '.client AND  table_detail_info_' . $value['id'] . '.id_detail =' . $value['id'], 'left');
                    $this->db->join('tblclient_info_detail_value as table_detail_info_value_' . $value['id'], 'table_detail_info_value_' . $value['id'] . '.id = table_detail_info_' . $value['id'] . '.value', 'left');
                }
                else
                {
                    $select_colum []= 'table_detail_info_' . $value['id'] . '.value as value_info_'.$value['id'];
                    $this->db->join('tblclient_value table_detail_info_' . $value['id'], db_prefix() . 'clients.userid = table_detail_info_' . $value['id'] . '.client AND  table_detail_info_' . $value['id'] . '.id_detail =' . $value['id'], 'left');

                }
            }

            $this->db->select(implode(',', $select_colum));
            $this->db->group_by('tblclients.userid'.(!empty($group_by) ? (','.implode(',',$group_by)) : ''));

            $clients = $this->db->get(db_prefix().'clients')->result_array();

            $columsExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
                'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
                'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
                'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
            ];
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.2); // ~ 1.78cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setHeader(0.2); // ~1.02cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.2); // ~
            $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.2); // ~1.78cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.2); // ~1.73cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setFooter(0); // ~1.02cm

            //end caách lề phiếu in

            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

            //định dạng kiểu in ngang giấy A4
            $BStyle_center = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'font'  => array(
                    'bold'  => false,
                    'color' => array('rgb' => '111112'),
                    'size'  => 12,
                    'name'  => 'Times New Roman'
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            );
            $BStyle = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'font'  => array(
                    'bold'  => false,
                    'color' => array('rgb' => '111112'),
                    'size'  => 12,
                    'name'  => 'Times New Roman'
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY
                )
            );

            $Background_header= array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '14b8e9'),
                    'size'  => 14,
                    'bold'  => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )

            );

            foreach($data as $key => $value)
            {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columsExcel[$key])->setAutoSize(true);
                if(!is_numeric($value))
                {
                    $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].'1', _l('cong__'.$value))->getStyle($columsExcel[$key].'1')->applyFromArray($Background_header);
                }
                else
                {
                    $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].'1', $array_name_info[$value])->getStyle($columsExcel[$key].'1')->applyFromArray($Background_header);
                }
            }
            $stt = 1;
            foreach($clients as $kClient => $Client)
            {
                foreach($data as $key => $value)
                {
                    if($value == 'code_client' || $value == 'numberphone')
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($columsExcel[$key].($stt + $kClient + 1), $Client[$value], PHPExcel_Cell_DataType::TYPE_STRING)->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                    }
                    else if($value == 'gender')
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($columsExcel[$key].($stt + $kClient + 1), ($Client[$value] == 1 ? 'Nam' : 'Nữ'), PHPExcel_Cell_DataType::TYPE_STRING)->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle_center);
                    }
                    else if($value == 'date_create_company' || $value == 'birtday')
                    {
                        $objPHPExcel->getActiveSheet()
                            ->SetCellValue($columsExcel[$key].($stt + $kClient + 1), _d($Client[$value]))
                            ->getStyle($columsExcel[$key].($stt + $kClient + 1))
                            ->applyFromArray($BStyle)->getNumberFormat()->setFormatCode(get_current_date_format());
                    }
                    else if($value == 'datecreated')
                    {
                        $objPHPExcel->getActiveSheet()
                            ->SetCellValue($columsExcel[$key].($stt + $kClient + 1), _dt($Client[$value]))
                            ->getStyle($columsExcel[$key].($stt + $kClient + 1))
                            ->applyFromArray($BStyle)->getNumberFormat()->setFormatCode(get_current_date_format(true));
                    }
                    else if($value == 'debt_limit' || $value == 'debt_limit_day' || $value == 'discount')
                    {
                        $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), $Client[$value])->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle_center);
                    }
                    else if(is_numeric($value))
                    {
                        $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), $Client['value_info_'.$value])->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                    }
                    else if($value == 'introduction')
                    {
                        if(!empty($Client[$value]))
                        {
                            $client_introduction = get_table_where(db_prefix().'clients', ['userid' => $Client[$value]], '' ,'row');
                            $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), !empty($client_introduction->company) ? $client_introduction->company : '')->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                        }
                        else
                        {
                            $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), '')->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                        }
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), $Client[$value])->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                    }
                }
            }


            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="LIST_CLIENTS.xls"');
            header('Cache-Control: max-age=0');

            $objWriter->save('php://output');
            exit();
        }
    }
    public function action_export_suppliers()
    {
        ob_end_clean();
        if($this->input->post())
        {
            $data = $this->input->post('list_colum');
            $this->db->order_by('id','desc');
            $info_detail = $this->db->get('tblsuppliers_info_detail')->result_array();
            $select_colum = $data;
            $select_colum = array_diff($select_colum, [
            'id' ,
            'email',
            'datecreated',
            'active',
            ]);
            $select_colum[] = db_prefix().'suppliers.id as id';
            $select_colum[] = db_prefix().'suppliers.email as email';
            $select_colum[] = db_prefix().'suppliers_groups.name as groups_in';
            $select_colum[] = db_prefix().'suppliers.datecreated as datecreated';
            $select_colum[] = db_prefix().'suppliers.active as active';
            $select_colum[] = 'GROUP_CONCAT(tblstaff.firstname," ",tblstaff.lastname) as addedfrom';
            $select_colum[] = db_prefix().'countries.short_name as country';
            $select_colum[] = db_prefix().'province.name as city';
            $select_colum[] = db_prefix().'district.name as district';
            $select_colum[] = db_prefix().'ward.name as ward';
            $this->db->join(db_prefix().'countries', db_prefix().'countries.country_id = '.db_prefix().'suppliers.country', 'left');
            $this->db->join(db_prefix().'province', db_prefix().'province.provinceid = '.db_prefix().'suppliers.city', 'left');
            $this->db->join(db_prefix().'district', db_prefix().'district.districtid = '.db_prefix().'suppliers.district', 'left');
            $this->db->join(db_prefix().'ward', db_prefix().'ward.wardid = '.db_prefix().'suppliers.ward', 'left');

            
            $this->db->join(db_prefix().'suppliers_groups', db_prefix().'suppliers_groups.id = '.db_prefix().'suppliers.groups_in', 'left');
            $this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().'suppliers.addedfrom', 'left');
                        $array_name_info = [];
            foreach($info_detail as $key => $value)
            {
                $array_name_info[$value['id']] = $value['name'];
                if($value['type_form'] == 'select multiple' || $value['type_form'] == 'checkbox') {
                    $select_colum []= '(
                            SELECT 
                                GROUP_CONCAT(table_detail_info_value_'.$value['id'].'.name)
                            FROM  '.db_prefix().'suppliers_value table_detail_info_'.$value['id'].'
                            LEFT JOIN '.db_prefix().'client_info_detail_value  table_detail_info_value_'.$value['id'].' ON table_detail_info_value_'.$value['id'].'.id = table_detail_info_'.$value['id'].'.value
                            WHERE id_suppliert = tblsuppliers.id AND  table_detail_info_'.$value['id'].'.id_detail = '.$value['id'].'
                        ) as value_info_'.$value['id'];
                    $join[] = 'LEFT JOIN tblsuppliers_info_detail_value as table_detail_info_value_' . $value['id'] . '
                        ON table_detail_info_value_' . $value['id'] . '.id = table_detail_info_' . $value['id'] . '.value';
                    $group_by[] = 'table_detail_info_' . $value['id'] . '.id_suppliert';

                    $this->db->join(db_prefix() . 'suppliers_value as table_detail_info_' . $value['id'], db_prefix() . 'suppliers.id = table_detail_info_' . $value['id'] . '.id_suppliert AND  table_detail_info_' . $value['id'] . '.id_detail =' . $value['id'], 'left');
                    $this->db->join('tblsuppliers_info_detail_value as table_detail_info_value_' . $value['id'], 'table_detail_info_value_' . $value['id'] . '.id = table_detail_info_' . $value['id'] . '.value', 'left');
                }else if($value['type_form'] == 'select' || $value['type_form'] == 'radio')
                {
                    $select_colum []= 'table_detail_info_value_' . $value['id'] . '.name as value_info_'.$value['id'];
                    $this->db->join(db_prefix() . 'suppliers_value table_detail_info_' . $value['id'], db_prefix() . 'suppliers.id = table_detail_info_' . $value['id'] . '.id_suppliert AND  table_detail_info_' . $value['id'] . '.id_detail =' . $value['id'], 'left');
                    $this->db->join('tblsuppliers_info_detail_value as table_detail_info_value_' . $value['id'], 'table_detail_info_value_' . $value['id'] . '.id = table_detail_info_' . $value['id'] . '.value', 'left');
                }
                else
                {
                    $select_colum []= 'table_detail_info_' . $value['id'] . '.value as value_info_'.$value['id'];
                    $this->db->join(db_prefix() . 'suppliers_value table_detail_info_' . $value['id'], db_prefix() . 'suppliers.id = table_detail_info_' . $value['id'] . '.id_suppliert AND  table_detail_info_' . $value['id'] . '.id_detail =' . $value['id'], 'left');

                }
            }
            $this->db->select(implode(',', $select_colum));
            $this->db->group_by('tblsuppliers.id'.(!empty($group_by) ? (','.implode(',',$group_by)) : ''));
            $suppliers = $this->db->get(db_prefix().'suppliers')->result_array();
            // echo '<pre>';
            // var_dump($suppliers);die;
            $columsExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
                'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
                'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
                'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
            ];
            include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            $this->load->library('PHPExcel');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.2); // ~ 1.78cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setHeader(0.2); // ~1.02cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.2); // ~
            $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.2); // ~1.78cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.2); // ~1.73cm
            $objPHPExcel->getActiveSheet()->getPageMargins()->setFooter(0); // ~1.02cm

            //end caách lề phiếu in

            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

            //định dạng kiểu in ngang giấy A4
            $BStyle_center = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'font'  => array(
                    'bold'  => false,
                    'color' => array('rgb' => '111112'),
                    'size'  => 12,
                    'name'  => 'Times New Roman'
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            );
            $BStyle = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'font'  => array(
                    'bold'  => false,
                    'color' => array('rgb' => '111112'),
                    'size'  => 12,
                    'name'  => 'Times New Roman'
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY
                )
            );

            $Background_header= array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '14b8e9'),
                    'size'  => 14,
                    'bold'  => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )

            );

            foreach($data as $key => $value)
            {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columsExcel[$key])->setAutoSize(true);
                if(!is_numeric($value))
                {
                    $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].'1', _l('ch_'.$value))->getStyle($columsExcel[$key].'1')->applyFromArray($Background_header);
                }
                else
                {
                    $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].'1', $array_name_info[$value])->getStyle($columsExcel[$key].'1')->applyFromArray($Background_header);
                }
            }
            $stt = 1;
            foreach($suppliers as $kClient => $Client)
            {
                foreach($data as $key => $value)
                {
                    if($value == 'code' || $value == 'phone')
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($columsExcel[$key].($stt + $kClient + 1), $Client[$value], PHPExcel_Cell_DataType::TYPE_STRING)->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                    }
                    else if($value == 'date')
                    {
                        $objPHPExcel->getActiveSheet()
                            ->SetCellValue($columsExcel[$key].($stt + $kClient + 1), _d($Client[$value]))
                            ->getStyle($columsExcel[$key].($stt + $kClient + 1))
                            ->applyFromArray($BStyle)->getNumberFormat()->setFormatCode(get_current_date_format());
                    }
                    else if($value == 'active')
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($columsExcel[$key].($stt + $kClient + 1), ($Client[$value] == 1 ? 'Hoạt động' : 'Không hoạt động'), PHPExcel_Cell_DataType::TYPE_STRING)->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle_center);
                    }else if(is_numeric($value))
                    {
                        $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), $Client['value_info_'.$value])->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                    }
                    else if($value == 'datecreated')
                    {
                        $objPHPExcel->getActiveSheet()
                            ->SetCellValue($columsExcel[$key].($stt + $kClient + 1), _dt($Client[$value]))
                            ->getStyle($columsExcel[$key].($stt + $kClient + 1))
                            ->applyFromArray($BStyle)->getNumberFormat()->setFormatCode(get_current_date_format(true));
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->SetCellValue($columsExcel[$key].($stt + $kClient + 1), $Client[$value])->getStyle($columsExcel[$key].($stt + $kClient + 1))->applyFromArray($BStyle);
                    }
                }
            }


            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="LIST_SUPPLIERS.xls"');
            header('Cache-Control: max-age=0');

            $objWriter->save('php://output');
            exit();
        }
    }
}
