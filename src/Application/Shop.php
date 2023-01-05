<?php


namespace Onetech\EasyShopee\Application;


use Onetech\EasyShopee\Core\Api;

class Shop extends Api
{
    /**
     * 使用此获取商店的信息
     * @document https://open.shopee.com/documents/v2/v2.shop.get_shop_info?module=92&type=1
     * @throws \Onetech\EasyShopee\Exception\ShopeeException
     * @return mixed
     */
    public function getShopInfo()
    {
        return $this->get('/api/v2/shop/get_shop_info', []);
    }

    /**
     * 此API支持获取店铺信息。
     * @document https://open.shopee.com/documents/v2/v2.shop.get_profile?module=92&type=1
     * @throws \Onetech\EasyShopee\Exception\ShopeeException
     * @return mixed
     */
    public function getProfile()
    {
        return $this->get('/api/v2/shop/get_profile', []);
    }

    public function updateProfile()
    {
    }

    /**
     * 对于给定的店铺ID和区域，返回仓库信息，包括仓库ID、地址ID和位置ID
     * @document https://open.shopee.com/documents/v2/v2.shop.get_warehouse_detail?module=92&type=1
     * @throws \Onetech\EasyShopee\Exception\ShopeeException
     * @return mixed
     */
    public function getWarehouseDetail()
    {
        return $this->get('/api/v2/shop/get_warehouse_detail', []);
    }
}