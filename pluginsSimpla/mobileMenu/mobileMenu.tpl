
<div class="navbar-mobile-inner">
    <div class="container">
        <div class="burger">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>

        <strong>Выбор модели</strong>
    </div>
</div>


<!-- Navigation -->
<nav class="mobile-nav">
    <div class="container">
        <ul id="accordion" class="main-level panel-group">

            {*СТАЦИОНАРНЫЕ пункты меню*}

            <li><a href="/aktsii" class="main-level-item collapsed">
                    <i class="icon_wallet_black"></i>Акции
                </a>
                <div class="submenu">
                    <ul class="sub-menu panel-collapse" role="tabpanel" id="main_{$c->url}">
                        <li>
                            <a href="/aktsii" class="sub-menu-item">Акции</a>
                        </li>
                        {*<li>
                            <a href="/about" class="sub-menu-item">О нас</a>
                        </li>*}
                    </ul>
                </div>
            </li>
            <li><a href="/delivery" class="main-level-item collapsed">
                    <i class="icon_person"></i>Доставка / О нас
                </a>
                <div class="submenu">
                    <ul class="sub-menu panel-collapse" role="tabpanel" id="main_{$c->url}">
                        <li>
                            <a href="/delivery" class="sub-menu-item">Доставка и оплата</a>
                        </li>
                        {*<li>
                            <a href="/about" class="sub-menu-item">О нас</a>
                        </li>*}
                    </ul>
                </div>
            </li>

            {*END СТАЦИОНАРНЫЕ пункты меню*}

            {foreach from=$categories.{0}->subcategories item=c}

                {if $c->visible && $c->in_main_menu}

                    <li class="panel">

                        <a href="/{$c->url}" class="main-level-item collapsed" data-toggle="collapse" data-parent="#accordion">
                            {*<i class="icon_whatshot"></i>*}
                            {$c->name}
                        </a>

                        <div class="submenu">

                            <div class="filter-block">
                                <input type="text" class="form-control livefilter-input" data-list="#main_{$c->url}" placeholder="Фильтр по моделям">
                            </div>

                            <ul class="sub-menu panel-collapse" role="tabpanel" id="main_{$c->url}">
                                 {*<li class="mobile-sub-search">
                                    <input type="text" class="form-control livefilter-input" data-list="#main_{$c->url}" placeholder="Поиск по моделям">
                                </li>*}

                                {* получаем от плагина список популярных и помещаем в переменную $populars*}
                                {getMobileMenu var="populars" get = "mobileMenuPopulars" cat_id = $c->id}

                                    {foreach $populars as $popular}
                                        <li>
                                            <a href="/{$popular->url}" class="sub-menu-item">
                                                <i class="icon_whatshot"></i>
                                                {$popular->name}
                                            </a>
                                        </li>
                                    {/foreach}
                                {foreach $c->subcategories as $sub name=subcategories}
                                    {if $smarty.foreach.subcategories.iteration < 9}
                                        <li>
                                            <a href="/{$sub->url}" class="sub-menu-item">
                                                {if $sub->visible}{$sub->name}{/if}
                                            </a>
                                        </li>
                                    {/if}
                                {/foreach}

                                {if $c->subcategories|@count > 8}
                                    <li class="show_all"><a href="/{$c->url}" class="sub-menu-item">Все модели ({$c->subcategories|@count})</a></li>
                                {/if}

                            </ul>

                        </div>

                    </li>

                {/if}

            {/foreach}

        </ul>
    </div>
</nav>