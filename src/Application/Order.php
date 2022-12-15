<?php


namespace Onetech\EasyShopee\Application;


use Onetech\EasyShopee\Core\Api;
use Onetech\EasyShopee\Exception\InvalidArgumentsException;
use Onetech\EasyShopee\Exception\ShopeeException;

class Order extends Api
{
    /**
     * 获取订单列表(order_sn)
     * @document https://open.shopee.com/documents/v2/v2.order.get_order_list?module=94&type=1
     * @param string $time_range_field create_time/update_time. 创建时间或者更新时间(与time_from,time_to关联)
     * @param int $time_from time_from和time_to字段指定检索订单的日期范围。time_from字段是开始日期范围。time_from和time_to字段指定的最大日期范围为15天。
     * @param int $time_to time_from和time_to字段指定检索订单的日期范围
     * @param int $page_size
     * @param string $cursor 指定要在当前调用中返回的数据的起始条目。默认为“”。如果数据不止一页，偏移量可以是开始下一次调用的某个条目。
     * @param string $order_status The order_status filter for retriveing orders and each one only every request. Available value: UNPAID/READY_TO_SHIP/PROCESSED/SHIPPED/COMPLETED/IN_CANCEL/CANCELLED/INVOICE_PENDING
     * @param string $response_optional_fields
     * @throws ShopeeException
     */
    public function getOrderList(string $time_range_field, int $time_from, int $time_to, int $page_size, string $cursor, string $order_status = '', string $response_optional_fields = '')
    {
        return $this->get('/api/v2/order/get_order_list', [
            'time_range_field' => $time_range_field,
            'time_from' => $time_from,
            'time_to' => $time_to,
            'page_size' => $page_size,
            'cursor' => $cursor,
            'order_status' => $order_status,
            'response_optional_fields' => $response_optional_fields,
        ]);
    }

    /**
     * 获取READY_TO_SHIP状态订单
     * @document https://open.shopee.com/documents/v2/v2.order.get_shipment_list?module=94&type=1
     * @param int $page_size
     * @param string $cursor
     * @throws ShopeeException
     * @return mixed
     */
    public function getShipmentList(int $page_size, string $cursor)
    {
        return $this->get('/api/v2/order/get_shipment_list', [
            'page_size' => $page_size,
            'cursor' => $cursor,
        ]);
    }

    /**
     * 获取订单详情
     * @document https://open.shopee.com/documents/v2/v2.order.get_order_detail?module=94&type=1
     * @param array $order_sn_list
     * @param array $response_optional_fields
     * @throws ShopeeException
     * @return mixed
     */
    public function getOrderDetail(array $order_sn_list, array $response_optional_fields = [])
    {
        if (empty($order_sn_list)) {
            throw new InvalidArgumentsException('Argument order_sn_list is empty');
        }
        if (count($order_sn_list) > 50) {
            throw new InvalidArgumentsException('Argument order_sn_list count limit 1,50');
        }

        return $this->get('/api/v2/order/get_order_detail', [
            'order_sn_list' => implode(',', $order_sn_list),
            'response_optional_fields' => $response_optional_fields,
        ]);
    }

    /**
     * 使用此api将订单拆分为多个包裹。
     * @document https://open.shopee.com/documents/v2/v2.order.split_order?module=94&type=1
     * @param string $order_sn
     * @param array $package_list
     * @throws ShopeeException
     * @return mixed
     */
    public function splitOrder(string $order_sn, array $package_list)
    {
        return $this->post('/api/v2/order/get_order_detail', [
            'order_sn' => $order_sn,
            'package_list' => $package_list,
        ]);
    }

    /**
     * 使用此api撤消订单拆分。撤消拆分后，订单将只有一个包裹。
     * @param string $order_sn
     * @throws ShopeeException
     * @return mixed
     */
    public function unSplitOrder(string $order_sn)
    {
        return $this->post('/api/v2/order/get_order_detail', [
            'order_sn' => $order_sn
        ]);
    }

    public function cancelOrder(string $order_sn, string $cancel_reason, array $item_list)
    {
        return $this->post('/api/v2/order/cancel_order', [
            'order_sn' => $order_sn,
            'cancel_reason' => $cancel_reason,
            'item_list' => $item_list,
        ]);
    }


}