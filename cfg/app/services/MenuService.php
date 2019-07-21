<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 17/08/2015
 * Time: 15:12
 */

namespace cfg\app\services;


use cfg\app\Application;
use cfg\app\Controller;

/**
 * @property array pages
 */
class MenuService extends Controller {

    private static $pages = array();
    private static $out_html;

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param array $pages
     */
    public static function setPages($pages)
    {
        self::$pages = $pages;
    }

    /**
     * @return mixed
     */
    public static function getOutHtml()
    {
        return self::$out_html;
    }

    /**
     * @param mixed $out_html
     */
    public function setOutHtml($out_html)
    {
        $this->out_html = $out_html;
    }

    public final function composeMenu() {

        foreach (self::$pages as $page) {

            if ($page->getHasChild() && $page->getSideNavigation()) {

                self::$out_html .= '<li>';
                self::$out_html .= '<a href="#"><span class="' . $page->getIcon() . '"></span>  '. ucfirst($page->getDisplayName()) .'<span class="fa arrow"></span></a>';


                if ($page->getChildren()) {

                    self::$out_html .= '<ul class="nav nav-second-level">';

                    foreach ($page->getChildren() as $child) {

                        if (gettype($child) == "array") $child = $child[0];

                        if ($child->getSideNavigation()) {

                            self::$out_html .= '<li>';
                            self::$out_html .= '<a href="'. $this->generateURL($child->getUrl()) .'" onclick="_system.select_type(this.href, 1); return false;"><span class="' . $child->getIcon() . '"></span>  '. ucfirst($child->getDisplayName()) .'</a>';
                            self::$out_html .= '</li>';

                        }
                    }

                    self::$out_html .= '</ul>';

                }

                self::$out_html .= '</li>';

            } elseif ($page->getParent() == 0) {

                if ($page->getSideNavigation()) {

                    self::$out_html .= '<li>';
                    self::$out_html .= '<a href="'. $this->generateURL($page->getUrl()) .'" onclick="_system.select_type(this.href, 1); return false;"><span class="' . $page->getIcon() . '"></span>  '. ucfirst($page->getDisplayName()) .'</a>';
                    self::$out_html .= '</li>';

                }
            }
        }
    }

}