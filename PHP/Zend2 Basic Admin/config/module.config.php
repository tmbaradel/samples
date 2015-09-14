<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
            'Admin\Controller\Usersadmin' => 'Admin\Controller\UsersadminController',
        	'Admin\Controller\Contentadmin' => 'Admin\Controller\ContentadminController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Admin',
                        'action'     => 'index',
                    ),
                ),
            ),
            'usersadmin' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/usersadmin[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Usersadmin',
                        'action'     => 'index',
                    ),
                ),
            ),
        	'contentadmin' => array(
        			'type'    => 'segment',
        			'options' => array(
        					'route'    => '/contentadmin[/:action][/:id]',
        					'constraints' => array(
        							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
        							'id'     => '[0-9]+',
        					),
        					'defaults' => array(
        							'controller' => 'Admin\Controller\Contentadmin',
        							'action'     => 'index',
        					),
        			),
        	),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view',
        ),
    ),
	'module_layouts' => array(
		'Admin' => 'layout/admin_layout.phtml',
	),
		
);