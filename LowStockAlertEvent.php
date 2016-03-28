<?php
namespace Plugin\LowStockAlert;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
class LowStockAlertEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onProductDetailBefore(FilterResponseEvent $event)
    {
        try{
            $request = $event->getRequest();
            $response = $event->getResponse();
            $html = $this->getHtmlLowStockAlert($request, $response);
            $response->setContent($html);
            $event->setResponse($response);
        }catch(\Exception $e){
            throw  $e;
        }

    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    private function getHtmlLowStockAlert(Request $request, Response $response)
    {
        $LowStockAlert = $this->app['eccube.plugin.repository.lowstockalert']->find(1);
        $plQuantity = $LowStockAlert->getNum();
        $product_id =  $request->get('id');
        $Product= $this->app['eccube.repository.product']->get($product_id);
        $stock = $Product->getStockMax();
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        $part = '';
        //stock limit
        if($stock > 0){
            $part = '<span id="low_stock">残り在庫数 :  ' . $stock;
            if($stock < $plQuantity)
                $part .=  '<span style="color:red;" id="low_stock_alert"> 残りあとわずか！</span></span>';
        }

        try {
            $oldHtml = $crawler->filter('#detail_cart_box__cart_quantity')->last()->html();
            $newHtml = $oldHtml . $part; // 変更箇所
            $html = str_replace($oldHtml, $newHtml, $html);

        } catch (\InvalidArgumentException $e) {
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }



}
