<?php


namespace Onetech\EasyShopee\Application;


use Onetech\EasyShopee\Core\Api;

class Returns extends Api
{

    /**
     * 使用此api通过店铺id获取许多退货的详细信息
     * @document https://open.shopee.com/documents/v2/v2.returns.get_return_list?module=102&type=1
     * @param int $page_no 指定要在当前调用中返回的数据的起始条目。默认值为0。如果数据多于一页，偏移量可以是开始下一个调用的某个条目。
     * @param int $page_size 如果有许多项目可供检索，您可能需要多次调用GetReportnList来检索所有数据。每个结果集作为一页条目返回。默认值为40。使用分页过滤器来控制每页检索的最大条目数（<=100），即每次调用开始下一次调用的偏移数。此整数值用于指定在单个数据“页面”中返回的最大条目数。
     * @param int $create_time_form create_time_from和create_time_to字段指定检索订单的日期范围（基于返回创建时间）。create_time_from字段是开始日期范围。create_time_from和create_time_to字段指定的最大日期范围为15天。
     * @param int $create_time_to create_time_from和create_time_to字段指定检索订单的日期范围（基于返回创建时间）。create_time_from字段是开始日期范围。create_time_from和create_time_to字段指定的最大日期范围为15天。
     * @param string $status 这用于按返回状态过滤返回请求。请参阅“数据定义-返回状态” https://open.shopee.com/developer-guide/31
     * @param string $negotiation_status 这是用于按计数器状态过滤返回请求。请参阅“数据定义-协商状态”
     * @param string $seller_proof_status 这是用于按证明状态过滤返回请求。请参阅“数据定义-SellerProofState”
     * @param string $seller_compensation_status 这是用于按补偿状态过滤返回请求。请参阅“数据定义-Seller补偿状态”
     * @throws \Onetech\EasyShopee\Exception\ShopeeException
     * @return mixed
     */
    public function getReturnList(int $page_no, int $page_size, int $create_time_form, int $create_time_to, string $status, string $negotiation_status, string $seller_proof_status, string $seller_compensation_status)
    {
        return $this->get('/api/v2/returns/get_return_list', [
            'page_no' => $page_no,
            'page_size' => $page_size,
            'create_time_form' => $create_time_form,
            'create_time_to' => $create_time_to,
            'status' => $status,
            'negotiation_status' => $negotiation_status,
            'seller_proof_status' => $seller_proof_status,
            'seller_compensation_status' => $seller_compensation_status,
        ]);
    }
}