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
            array('lowstockalert_config' => $this->createFormData(30))
        );
        $LowStockAlert = $this->app['eccube.plugin.repository.lowstockalert']->find(1);
        $quantity = $LowStockAlert->getNum();
        $this->expected = $this->createFormData(30)['num'];
        $this->actual = $quantity;
        $this->verify();
    }



    /**
     * 在庫数が閾値を下回ったときに、「残りあとわずか！」が表示されること
     */
    public function test_display_alarm()
    {
        //stock limited
        $crawler = $this->getProductDetailCrawler(20, 40, 0);
        $this->expected = ' 残りあとわずか！';
        $this->actual = $crawler->filter('#low_stock_alert')->text();
        $this->verify();
    }

    /**
     * 在庫数が閾値よりも多いときには、「残りあとわずか！」が表示されないこと
     */
    public function test_not_display_alarm()
    {
        try{
            //stock limited
            $crawler = $this->getProductDetailCrawler(50, 40, 0);
            //Exception catch(empty node exception)
            $crawler->filter('#low_stock_alert')->text();
            $this->assertTrue(false);
        }catch (\InvalidArgumentException $e){
            $this->assertTrue(true);
        }
    }

    /**
     * 在庫無制限＝ONの商品の場合は、「残りあとわずか！」が表示されないこと
     */
    public function test_not_display_alarm_stock_unlimited()
    {
        try{
            //stock unlimited
            $crawler = $this->getProductDetailCrawler(0, 40, 1);
            //Exception catch(empty node exception)
            $crawler->filter('#low_stock_alert')->text();
            $this->assertTrue(false);
        }catch (\InvalidArgumentException $e){
            $this->assertTrue(true);
        }
    }


    /**
     * Get product detail crawler
     */
    public function getProductDetailCrawler($quantity, $lowStockNum, $stock_unlimited){
        //get product test id
        $id = $this->createProduct()->getId();
        $formData = $this->createProductFormData($quantity, $stock_unlimited);
        $this->client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $id)),
            array('admin_product' => $formData)
        );
        $this->change_stock_alert($lowStockNum);
        $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => $id)));
        return $crawler;
    }

    /**
     * Change low stock quantity
     */
    public function change_stock_alert($num){
        $this->client->request(
            'POST',
            $this->app->path('plugin_LowStockAlert_config'),
            array('lowstockalert_config' => $this->createFormData($num))
        );
    }

    /**
     * Create new product data
     */
    public function createProductFormData($stock, $stock_unlimited)
    {
        $faker = $this->getFaker();
        $form = array(
            'class' => array(
                'product_type' => 1,
                'price01' => $faker->randomNumber(5),
                'price02' => $faker->randomNumber(5),
                'stock' => $stock,
                'stock_unlimited' => $stock_unlimited,
                'code' => $faker->word,
                'sale_limit' => null,
                'delivery_date' => ''
            ),
            'name' => $faker->word,
            'product_image' => null,
            'description_detail' => $faker->text,
            'description_list' => $faker->paragraph,
            'Category' => null,
            'tag' => $faker->word,
            'search_word' => $faker->word,
            'free_area' => $faker->text,
            'Status' => 1,
            'note' => $faker->text,
            'tags' => null,
            'images' => null,
            'add_images' => null,
            'delete_images' => null,
            '_token' => 'dummy',
        );
        return $form;
    }

    /**
     * Low stock alert data
     */
    protected function createFormData($num)
    {
        $form = array(
            'num' => $num,
            '_token' => 'dummy'
        );
        return $form;
    }

}
