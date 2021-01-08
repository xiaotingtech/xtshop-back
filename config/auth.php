<?php
//RBAC权限配置
return [
    'PASSWORD_SALT'=>'xtshop_2020',
    //RBAC认证配置信息----start
    'RBAC_SUPERADMIN' => 'super', //超级管理员名称，对应用户表中某一用户名-username
    'ADMIN_AUTH_KEY' => 'superadmin', //超级管理员识别
    'USER_AUTH_ON' =>  true,  //是否需要认证
    'USER_AUTH_TYPE' =>  1,  //认证类型 1为登录后才认证 2 为实时认证
    'USER_AUTH_KEY' => 'authId',  //认证识别号，此名称可以自已取
    //'REQUIRE_AUTH_MODULE' =>    //需要认证模块
    'NOT_AUTH_MODULE' => 'Test',  //无需认证模块，和上重复，我们只用一个
    'NOT_AUTH_ACTION' => '',  //无需认证操作
    //'USER_AUTH_GATEWAY' => '/Login/doLogin',  //认证网关，此处可以不用
    //'RBAC_DB_DSN' => 'mysql://root:123456@localhost:3306/studyimrbac',   //数据库连接DSN，公用的，此处可以省略
    'RBAC_ROLE_TABLE' => 'l_role',  //角色表名称
    'RBAC_USER_TABLE' => 'l_role_user',  //用户表名称
    'RBAC_ACCESS_TABLE' => 'l_access',  //权限表名称
    'RBAC_NODE_TABLE' => 'l_node',  //节点表名称
    //RBAC认证----end
    'GUEST_AUTH_ON'=>false,
    'ACCESS_LIST'=>'allow_visit_menu',
];