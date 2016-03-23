<?php

namespace Plugin\LowStockAlert\ServiceProvider;

use Eccube\Application;
use Plugin\LowStockAlert\Form\Type\LowStockAlertConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class LowStockAlertServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // 管理画面
        $app->match('/' . $app['config']['admin_route'] . '/plugin/lowstockalert/config', 'Plugin\LowStockAlert\Controller\LowStockAlertController::index')->bind('plugin_LowStockAlert_config');

        //$app->match('/plugin/LowStockAlert/checkout', 'Plugin\LowStockAlert\Controller\LowStockAlertController::index')->bind('plugin_LowStockAlert_index');

        // Repository
        $app['eccube.plugin.repository.lowstockalert'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\LowStockAlert\Entity\LowStockAlert');
        });

        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new LowStockAlertConfigType($app);
            return $types;
        }));

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }
            return $translator;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
