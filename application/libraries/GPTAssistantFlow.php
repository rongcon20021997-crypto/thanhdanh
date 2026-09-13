<?php

class GPTAssistantFlow
{
    private $apiKey;
    private $apiBase = 'https://api.openai.com/v1';
    private $fileIds = [];
    private $assistantId;
    private $threadId;
    private $runId;

    public function __construct($apiKey)
    {
        $this->apiKey = 'Bearer ' . $apiKey;
        $CI = &get_instance();
        $this->db = $CI->db;
    }

    public function run($files, $question = 'Hãy tóm tắt nội dung chính của 3 file PDF.')
    {
        $question = 'Bạn nhận được 3 file PDF đính kèm.  
                    Hãy đọc kỹ nội dung của 3 file và trích xuất dữ liệu điền vào các trường JSON dưới đây.  

                    📋 Quy tắc:
                    - Chỉ trả về duy nhất **một JSON**, đúng tên các trường.
                    - Với mỗi trường, hãy cố gắng tìm dữ liệu hợp lý nhất từ 3 file theo đúng ý nghĩa đã định nghĩa bên dưới.
                    - Nếu không tìm thấy dữ liệu hoặc không rõ → **xóa trường đó khỏi JSON, không cần để null hoặc ""**.
                    - Không đổi tên trường.
                    - 📅 Tất cả các trường ngày (ví dụ: `"date"`, `"date_delivery"`, …) phải trả về theo định dạng: `dd/mm/yyyy`.

                    ---

                    ### Định nghĩa từng trường (các trường ngày đã được ghi chú rõ):
                    - `"reference"`: Mã Sales Order (SO) — thường nằm trong file MO hoặc PO, ký hiệu: Sales Order No. hoặc SO#.
                    - `"date"`: Ngày của Sales Order (SO) — nếu không có thì lấy ngày của PO — định dạng **dd/mm/yyyy**.
                    - `"customer"`: Tên khách hàng — thường là “The LYCRA Company”.
                    - `"person_contact"`: Tên người liên hệ trực tiếp nếu có.
                    - `"address_delivery"`: Địa chỉ giao hàng — thường nằm trong MO hoặc PO.
                    - `"id_branch"`: Tên chi nhánh hoặc nhà máy — ví dụ: “r-pac Vietnam Ltd (factory 6)”.
                    - `"currencies"`: Loại tiền tệ — thường là “VND” hoặc “USD”.
                    - `"amount_to_vnd"`: Giá trị quy đổi ra VND (nếu có).
                    - `"type_orders"`: Loại đơn hàng — thường thấy như “SALES - FG - MAKE TO ORDER”.
                    - `"type_items"`: Loại sản phẩm — ví dụ: “HANG TAG”.
                    - `"status_orders"`: Trạng thái đơn hàng — nếu có ghi rõ (ví dụ: Completed, Pending, …).
                    - `"employees"`: Tên nhân viên phụ trách — nếu có ghi rõ.
                    - `"tax"`: Số tiền thuế nếu có — ví dụ: “258,000”.
                    - `"cost_delivery"`: Chi phí giao hàng — thường thấy như “0”.
                    - `"transporters"`: Tên đơn vị vận chuyển — nếu có.
                    - `"charge_party"`: Bên chịu phí vận chuyển — nếu có.
                    - `"note"`: Ghi chú tổng của đơn hàng nếu có.
                    - `"item_code"`: Mã thành phẩm (mã sản phẩm) — ví dụ: “CM-012-EU.3”.
                    - `"item_name"`: Tên thành phẩm — ví dụ: “HANG TAG”.
                    - `"product_name_customer"`: Tên sản phẩm theo cách gọi của khách hàng — ví dụ: “HANG TAG, INVISTA, COOLMAX”.
                    - `"unit"`: Đơn vị tính — ví dụ: “PCS”.
                    - `"order_code"`: Mã đơn đặt hàng — giống hoặc tương đương với SO#.
                    - `"command"`: Chỉ lệnh sản xuất (nếu có).
                    - `"quantity_put"`: Số lượng đặt hàng — ví dụ: “25000”.
                    - `"quantity_loss"`: Số lượng hao hụt nếu có.
                    - `"sample_quantity_item"`: Số lượng mẫu (sample) — ví dụ: “200”.
                    - `"total_quantity_item"`: Tổng số lượng — ví dụ: “25000”.
                    - `"price"`: Giá đơn vị — ví dụ: “129”.
                    - `"amount"`: Tổng số tiền — ví dụ: “3,225,000”.
                    - `"date_delivery"`: Ngày giao hàng dự kiến — thường nằm trong MO hoặc PO — định dạng **dd/mm/yyyy**.
                    - `"detail_delivery"`: Chuỗi chi tiết giao hàng: ngày + số lượng — ví dụ: “18/07/2025 - 25000”.
                    - `"note_item"`: Ghi chú chi tiết cho mặt hàng nếu có.
                    - `"so"`: Sales Order số (SO#).
                    - `"pi"`: Proforma Invoice số (PI#) — nếu có.
                    - `"po_style"`: Mã PO hoặc Style — ví dụ: “250063257”.
                    - `"item_code_tem"`: Item Code (mã hệ thống) — ví dụ: “F011010295”.

                    ---

                    📌 Chỉ trả về JSON kết quả, không kèm theo bất cứ văn bản hay giải thích nào.
                    📌 Các trường ngày phải đúng định dạng `dd/mm/yyyy`.

                    ---

                    ### Mẫu JSON:
                    ```json
                    {
                    "reference": "",
                    "date": "",
                    "customer": "",
                    "person_contact": "",
                    "address_delivery": "",
                    "id_branch": "",
                    "currencies": "",
                    "amount_to_vnd": "",
                    "type_orders": "",
                    "type_items": "",
                    "status_orders": "",
                    "employees": "",
                    "tax": "",
                    "cost_delivery": "",
                    "transporters": "",
                    "charge_party": "",
                    "note": "",
                    "item_code": "",
                    "item_name": "",
                    "product_name_customer": "",
                    "unit": "",
                    "order_code": "",
                    "command": "",
                    "quantity_put": "",
                    "quantity_loss": "",
                    "sample_quantity_item": "",
                    "total_quantity_item": "",
                    "price": "",
                    "amount": "",
                    "date_delivery": "",
                    "detail_delivery": "",
                    "note_item": "",
                    "so": "",
                    "pi": "",
                    "po_style": "",
                    "item_code_tem": ""
                    }
        ';
        $this->uploadFiles($files);
        $this->createAssistant();
        $this->createThread();
        $this->sendMessage($question);
        $this->runAssistant();
        $result = $this->getResult();
        $this->deleteFiles();
        return $result;
    }

    private function uploadFiles($files)
    {
        foreach ($files as $index => $file) {
            if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File PDF #" . ($index + 1) . " không hợp lệ.");
            }

            $tmpPath = $file['tmp_name'];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiBase . '/files',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => [
                    'purpose' => 'assistants',
                    'file' => new CURLFile($tmpPath, 'application/pdf', $file['name'])
                ],
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->apiKey,
                    'OpenAI-Beta: assistants=v2'
                ]
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            if (!isset($result['id'])) {
                throw new Exception("Upload file thất bại: {$response}");
            }

            $this->fileIds[] = $result['id'];
        }

        if (count($this->fileIds) !== 3) {
            throw new Exception("Không upload đủ 3 file PDF.");
        }
    }

    private function createAssistant()
    {
        $payload = [
            'name' => 'PDF Analyst',
            'instructions' => 'Giải thích nội dung của file mà người dùng tải lên.',
            'tools' => [['type' => 'file_search']],
            'model' => 'gpt-4o-mini'
        ];

        $res = $this->callAPI('/assistants', $payload);

        $this->db->insert('tblget_api', [
            'type' => 112233,
            'data' => json_encode($res),
            'date' => date('Y-m-d H:i:s')
        ]);

        $this->assistantId = $res['id'];
    }

    private function createThread()
    {
        $res = $this->callAPI('/threads', []);

        $this->db->insert('tblget_api', [
            'type' => 112234,
            'data' => json_encode($res),
            'date' => date('Y-m-d H:i:s')
        ]);

        $this->threadId = $res['id'];
    }

    private function sendMessage($question)
    {
        $payload = [
            'role' => 'user',
            'content' => $question,
            'attachments' => array_map(function ($fileId) {
                return [
                    'file_id' => $fileId,
                    'tools' => [['type' => 'file_search']]
                ];
            }, $this->fileIds)
        ];

        $this->db->insert('tblget_api', [
            'type' => 112235,
            'data' => json_encode([
                'question' => $question,
                'attachments' => $this->fileIds
            ]),
            'date' => date('Y-m-d H:i:s')
        ]);

        $this->callAPI("/threads/{$this->threadId}/messages", $payload);
    }

    private function runAssistant()
    {
        $payload = [
            'assistant_id' => $this->assistantId
        ];

        $res = $this->callAPI("/threads/{$this->threadId}/runs", $payload);

        $this->db->insert('tblget_api', [
            'type' => 112236,
            'data' => json_encode($res),
            'date' => date('Y-m-d H:i:s')
        ]);

        $this->runId = $res['id'];
    }

    private function getResult()
    {
        $reply = null;

        while (true) {
            sleep(5);

            $runRes = $this->callAPI("/threads/{$this->threadId}/runs/{$this->runId}", null, 'GET');

            $this->db->insert('tblget_api', [
                'type' => 112237,
                'data' => json_encode($runRes),
                'date' => date('Y-m-d H:i:s')
            ]);

            if (!empty($runRes['status'])) {
                if ($runRes['status'] === 'completed') {
                    break;
                }

                if (in_array($runRes['status'], ['failed', 'cancelled', 'expired'])) {
                    throw new Exception("Run kết thúc với trạng thái: {$runRes['status']}");
                }
            }
        }

        $msgRes = $this->callAPI("/threads/{$this->threadId}/messages", null, 'GET');

        $this->db->insert('tblget_api', [
            'type' => 112238,
            'data' => json_encode($msgRes),
            'date' => date('Y-m-d H:i:s')
        ]);

        foreach ($msgRes['data'] as $msg) {
            if ($msg['role'] === 'assistant') {
                foreach ($msg['content'] as $content) {
                    if ($content['type'] === 'text' && !empty($content['text']['value'])) {
                        $reply = $content['text']['value'];
                        break 2;
                    }
                }
            }
        }

        return $reply;
    }

    private function deleteFiles()
    {
        foreach ($this->fileIds as $fileId) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiBase . '/files/' . $fileId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $this->apiKey,
                'OpenAI-Beta: assistants=v2'
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    private function callAPI($endpoint, $payload = null, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBase . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->apiKey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception("Invalid JSON response: {$response}");
        }

        return $data;
    }
}
