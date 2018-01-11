phalcon-test
====


phalcon目录以及模块化，路由等 初始化

Synopsis
========

```框架配置 
   入口文件 /public/index.php
   配置文件目录 /app/config/
   控制器和视图每个 module都有 比如、app/home 这个home module
   模型层目录 /app/common/models
   常用类库文件放 /app/common/library

   框架入口初始化 工厂类 \Phalcon\Di\FactoryDefault 为 $di 对象
   配置路由
   赋予$di对象 公共属性共享在控制器访问 如：1加载配置 2url组件3模板引擎
   4db类初始化实例5session组件6cache组件7cookie组件等
   配置自动加载的命名空间 以及默认 的module
   
   给application类 注册服务模块 并实例化时候传$di对象 

   $application->handle()->getContent()
   



```

Copyright and License
=====================

This module is licensed under the BSD license.

Copyright (C) 2009-2010, by Royee guanhui07@gmail.com

Copyright (C) 2010-2014, by Royee guanhui07@gmail.com

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

