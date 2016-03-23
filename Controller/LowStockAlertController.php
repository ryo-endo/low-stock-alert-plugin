<?php
namespace Plugin\LowStockAlert\Controller;

use Eccube\Application;
use Plugin\LowStockAlert\Entity\LowStockAlert;
use Symfony\Component\HttpFoundation\Request;

class LowStockAlertController
{

    /**
     * ラッピング用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        $LowStockAlert = $app['eccube.plugin.repository.lowstockalert']->find(1);

        if (!$LowStockAlert) {
            $LowStockAlert = new LowStockAlert();
        }

        $form = $app['form.factory']->createBuilder('lowstockalert_config', $LowStockAlert)->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $LowStockAlert = $form->getData();
                // IDは1固定
                $LowStockAlert->setId(1);
                $app['orm.em']->persist($LowStockAlert);
                $app['orm.em']->flush($LowStockAlert);
                $app->addSuccess('admin.lowstockalert.save.complete', 'admin');
            }
        }

        return $app->render('LowStockAlert/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
            'LowStockAlert' => $LowStockAlert,
        ));
    }

}
