<?php

/**
 * Bono App Configuration
 *
 * @category  PHP_Framework
 * @package   Bono
 * @author    Ganesha <reekoheek@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version   0.10.0
 * @link      http://xinix.co.id/products/bono
 */

use Norm\Schema\String;
use Norm\Schema\Password;
use Norm\Schema\Integer;

return array(
    'application' => array(
        'title' => 'Bono Application',
        'subtitle' => 'One great application'
    ),
    'bono.salt' => 'please change this',
    'bono.providers' => array(
        'Norm\\Provider\\NormProvider' => array(
            'datasources' => array(
                'filedb' => array(
                    'driver' => 'ROH\\FDB\\Connection',
                    'dataDir' => '../data',
                ),
            ),
            'collections' => array(
                'default' => array(
                    'observers' => array(
                        'Norm\\Observer\\Timestampable' => null,
                    ),
                ),
                'mapping' => array(
                    'User' => array(
                        'schema' => array(
                            'username' => String::create('username')->filter('trim|required|unique:User,username'),
                            'password' => Password::create('password')->filter('trim|confirmed|salt'),
                            'email' => String::create('email')->filter('trim|required|unique:User,email'),
                            'first_name' => String::create('first_name')->filter('trim|required'),
                            'last_name' => String::create('last_name')->filter('trim|required'),
                        ),
                    ),
                    'Item' => array(
                        'schema' => array(
                            'code' => String::create('code')->filter('trim|required|unique:Item,code'),
                            'name' => String::create('name')->filter('trim|required'),
                            'price' => Integer::create('price'),
                        ),
                    ),
                ),
            ),
        ),
        'Xinix\\Migrate\\Provider\\MigrateProvider' => array(
            // 'token' => 'changetokenherebeforeenable',
        ),
        'ROH\\FDB\\Provider\\FDBProvider',
    ),
    'bono.middlewares' => array(
        'Bono\\Middleware\\StaticPageMiddleware' => null,
        'Bono\\Middleware\\ControllerMiddleware' => array(
            'default' => 'Norm\\Controller\\NormController',
            'mapping' => array(
                '/user' => null,
                '/item' => null,
            ),
        ),
        'Bono\\Middleware\\ContentNegotiatorMiddleware' => array(
            'extensions' => array(
                'json' => 'application/json',
            ),
            'views' => array(
                'application/json' => 'Bono\\View\\JsonView',
            ),
        ),
        // uncomment below to enable auth
        // 'ROH\\BonoAuth\\Middleware\\AuthMiddleware' => array(
        //     'driver' => 'ROH\\BonoAuth\\Driver\\NormAuth',
        // ),
        'Bono\\Middleware\\NotificationMiddleware' => null,
        'Bono\\Middleware\\SessionMiddleware' => null,
    ),
);
