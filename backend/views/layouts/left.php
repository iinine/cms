<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
                <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>

        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => [
                    ['label' => '菜单栏', 'options' => ['class' => 'header']],
                    ['label' => '权限控制', 'icon' => 'dashboard', 'url' => ['/admin'],
                        'items' => [
                            ['label' => '管理员', 'icon' => 'fa fa-circle-o', 'url' => ['/admin'],],
                            ['label' => '用户', 'icon' => 'fa fa-circle-o', 'url' => ['/fan-user'],],
//                            ['label' => '后台用户', 'icon' => 'fa fa-circle-o', 'url' => ['/user'],],
                            ['label' => '权限管理', 'icon' => 'fa fa-circle-o', 'url' => ['/admin/role'],
                                'items' => [
                                    ['label' => '菜单', 'icon' => 'file-code-o', 'url' => ['/admin/menu'],],
                                    ['label' => '权限', 'icon' => 'file-code-o', 'url' => ['/admin/permission'],],
                                    ['label' => '角色', 'icon' => 'file-code-o', 'url' => ['/admin/role'],],
                                    ['label' => '分配', 'icon' => 'file-code-o', 'url' => ['/admin/assignment'],],
                                    ['label' => '规则', 'icon' => 'file-code-o', 'url' => ['/admin/rule'],],
                                    ['label' => '路由', 'icon' => 'file-code-o', 'url' => ['/admin/route'],],
                                ]
                            ],
                        ],
                    ],
//                    ['label' => '泛目录之升级打怪', 'icon' => 'dashboard', 'url' => ['#'],
//                        'items' => [
                    ['label' => '域名    人物', 'icon' => 'file-code-o', 'url' => ['/domain/index']],
                    ['label' => '栏目    职业', 'icon' => 'file-code-o', 'url' => ['/domain-column/index']],
                    ['label' => '模板    服饰 ', 'icon' => 'file-code-o', 'url' => ['/template/index']],
                    ['label' => '模组    套装 ', 'icon' => 'file-code-o', 'url' => ['/template-tpl/index']],
                    ['label' => '合成    穿戴', 'icon' => 'file-code-o', 'url' => ['/domain-tpl/index']],
                    ['label' => '类型    羁绊', 'icon' => 'file-code-o', 'url' => ['/category/index']],
                    ['label' => '手法    技能', 'icon' => 'file-code-o', 'url' => ['/article-way/index']],
                    ['label' => '规则    经验', 'icon' => 'file-code-o', 'url' => ['/article-rules/index']],
                    ['label' => '文章    金币', 'icon' => 'file-code-o', 'url' => ['/push-article/index?domain_id=16']],
//                        ]
//                    ],

                    ['label' => '爱站规则设定', 'icon' => 'fa fa-circle-o', 'url' => ['/aizhan-rules/index']],
                    ['label' => '爱站关键词', 'icon' => 'fa fa-circle-o', 'url' => ['/keywords/index?sort=search_num&KeywordsSearch%5Bstatus%5D=1&KeywordsSearch%5Bnote%5D=0']],
//                    ['label' => '兔子队列', 'icon' => 'dashboard', 'url' => ['/rabbitemq/index']],
//                    ['label' => '黑帽文章', 'icon' => 'dashboard', 'url' => ['/black-article/index']],
//                    ['label' => '白帽文章', 'icon' => 'dashboard', 'url' => ['/white-article/index']],
                    ['label' => '一级词 & tags', 'icon' => 'fa fa-circle-o', 'url' => ['/baidu-keywords/index']],
                    ['label' => '关键词', 'icon' => 'dashboard', 'url' => ['#'],
                        'items' => [
                            ['label' => '要做的三级词 & tags', 'icon' => 'fa fa-circle-o', 'url' => ['/all-baidu-keywords/index']],
                        ]
                    ],
                    ['label' => '敏感词库', 'icon' => 'dashboard', 'url' => ['/special-keywords/index']],
                    ['label' => '百度推送日志', 'icon' => 'dashboard', 'url' => ['/mip-flag/index']],
                    ['label' => '网站地图生成', 'icon' => 'dashboard', 'url' => ['/site-map/index']],
//                    ['label' => 'CMS 数据库', 'icon' => 'dashboard', 'url' => ['/db-name/index']],
                    ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii']],
                    ['label' => 'Debug', 'icon' => 'file-code-o', 'url' => ['/debug']],
                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                ],
            ]
        ) ?>

    </section>
</aside>
