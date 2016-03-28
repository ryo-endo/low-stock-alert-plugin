<?php

namespace Plugin\LowStockAlert\Tests\Entity;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Symfony\Component\Config\Definition\Exception\Exception;

class UnitTest extends AbstractAdminWebTestCase
{
    /**
     * 設定画面へのルーティングテスト
     */
    public function test_routing_config_page()
    {
        $this->client->request(
            'GET',
            $this->app->url('plugin_LowStockAlert_config')
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * 閾値の在庫数の変更(POSTしてDBに反映されるか)
     */
    public function test_product_quantity()
    {
        $this->client->request(
            'POST',
            $this->app->path('plugin_LowStockAlert_config'),
            array('lowstockalert_config' => $this->createFormData())
        );
        $LowStockAlert = $this->app['eccube.plugin.repository.lowstockalert']->find(1);
        $quantity = $LowStockAlert->getNum();
        $this->expected = $quantity;
        $this->actual = $this->createFormData()['num'];
        $this->verify();
    }

    /**
     * POST FORM DATA
     */
    protected function createFormData()
    {
        $form = array(
            'num' => 300,
            '_token' => 'dummy'
        );
        return $form;
    }

    /**
     * 在庫数が閾値を下回ったときに、「残りあとわずか！」が表示されること
     */
    public function test_display_alarm()
    {
        $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => '2')));
        $this->expected = ' 残りあとわずか！';
        $this->actual = $crawler->filter('#low_stock_alert')->text();
        $this->verify();
    }

    /**
     * 在庫無制限＝ONの商品の場合は、「残りあとわずか！」が表示されないこと
     */
    public function test_not_display_alarm()
    {
        try{
            $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => '1')));
            //Exception catch(empty node exception)
            $crawler->filter('#low_stock_alert')->text();
            $this->assertTrue(false);
        }catch (\InvalidArgumentException $e){
            $this->assertTrue(true);
        }
    }


}
