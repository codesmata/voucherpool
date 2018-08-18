<?php

require_once __DIR__.'/../vendor/autoload.php';

/**
 * @author Arotimi Busayo <arotimi.busayo@gmail.com>
 *
 * DI Container for easy dependency resolution.
 */

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;
use Symfony\Component\EventDispatcher;
use Vanhack\Kernel;

$containerBuilder = new DependencyInjection\ContainerBuilder();
$containerBuilder->register('context', Routing\RequestContext::class);
$containerBuilder->register('matcher', Routing\Matcher\UrlMatcher::class)
    ->setArguments(array($routes, new Reference('context')))
;
$containerBuilder->register('config', \Vanhack\Voucher\Repositories\VoucherRepository::class);
$containerBuilder->register('auth', \Vanhack\Voucher\Authenticator\Auth::class)
    ->addArgument(new Reference('context'));
;
$containerBuilder->register('request_stack', HttpFoundation\RequestStack::class);
$containerBuilder->register('controller_resolver', HttpKernel\Controller\ControllerResolver::class)
;
$containerBuilder->register('argument_resolver', HttpKernel\Controller\ArgumentResolver::class)
;
$containerBuilder->register('listener.router', HttpKernel\EventListener\RouterListener::class)
    ->setArguments(array(new Reference('matcher'), new Reference('request_stack')))
;
$containerBuilder->register('listener.response', HttpKernel\EventListener\ResponseListener::class)
    ->setArguments(array('UTF-8'))
;

$containerBuilder->register('dispatcher', EventDispatcher\EventDispatcher::class)
    ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
    ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
;
$containerBuilder->register('cosy.kernel', Kernel::class)
    ->setArguments(array(
        new Reference('dispatcher'),
        new Reference('controller_resolver'),
        new Reference('request_stack'),
        new Reference('argument_resolver'),
    ))
;

return $containerBuilder;
