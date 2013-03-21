<?php
namespace StockRest;

return array(

    // Controllers in this module
    'controllers' => array(
        'invokables' => array(
            'Product' => 'StockRest\Controller\ProductController',
        ),
    ),

    // Routes for this module
    'router' => array(
        'routes' => array(
            // Products
            'product-rest' => array(
                'type'    => 'segment',
                'options' => array(
                    'route' => '/product-rest[/:params]',
                    'constraints' => array(
                        'params' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Product',
                    ),
                ),
            ),
        ),
    ),
    
    // View Strategy
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    // Doctrine configuration
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ),
            ),
        ),
    ),

);


