<?php

/**
 * Putting this here to help remind you where this came from.
 *
 * I'll get back to improving this and adding more as time permits
 * if you need some help feel free to drop me a line.
 *
 * * Twenty-Years Experience
 * * PHP, JavaScript, Laravel, MySQL, Java, Python and so many more!
 *
 *
 * @author  Simple-Pleb <plebeian.tribune@protonmail.com>
 * @website https://www.simple-pleb.com
 * @source https://github.com/simplepleb/article-module
 *
 * @license MIT For Premium Clients
 *
 * @since 1.0
 *
 */

namespace Modules\Inboxer\Http\Middleware;

use Closure;

class GenerateMenus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Menu::make('admin_sidebar', function ($menu) {

            $articles_menu = $menu->add('<i class="c-sidebar-nav-icon fas fa-envelope"></i> Email Marketing', [
                'class' => 'c-sidebar-nav-dropdown',
            ])
            ->data([
                'order'         => 87,
                'activematches' => [
                    'admin/lists*',
                   /* 'admin/categories*',*/
                ],
                'permission' => ['view_posts', 'view_categories'],
            ]);
            $articles_menu->link->attr([
                'class' => 'c-sidebar-nav-dropdown-toggle',
                'href'  => '#',
            ]);

            // Submenu: Posts
            $articles_menu->add(' Mailing Lists', [
                'route' => 'backend.lists.index',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 88,
                'activematches' => 'admin/lists*',
                'permission'    => ['edit_posts'],
            ])
            ->link->attr([
                'class' => "c-sidebar-nav-link",
            ]);
            $articles_menu->add(' Campaigns', [
                'route' => 'backend.campaigns.index',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 88,
                'activematches' => [
                    'admin/campaigns*',
                ],
                'permission'    => ['edit_posts'],
            ])
            ->link->attr([
                'class' => "c-sidebar-nav-link",
            ]);
            $articles_menu->add(' Templates', [
                'route' => 'backend.templates.index',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 88,
                'activematches' => [
                    'admin/templates*',
                ],
                'permission'    => ['edit_posts'],
            ])
            ->link->attr([
                'class' => "c-sidebar-nav-link",
            ]);


            // Submenu: Categories
            /*$articles_menu->add('<i class="c-sidebar-nav-icon fas fa-sitemap"></i> Categories', [
                'route' => 'backend.categories.index',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 89,
                'activematches' => 'admin/categories*',
                'permission'    => ['edit_categories'],
            ])
            ->link->attr([
                'class' => "c-sidebar-nav-link",
            ]);*/
        })->sortBy('order');

        return $next($request);
    }
}
