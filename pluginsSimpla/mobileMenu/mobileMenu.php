<?php


trait mobileMenu {

    function getMobileMenu($params = array(), &$smarty)  {


        // actions
        switch ($params['get']) {

            case 'mobileMenuPopulars':

                $populars = $this->mobileMenuPopulars($var_id = $params['cat_id'], $smarty);
                $smarty->assign($params['var'], $populars);

                return;
                break;

            case 'none':
                return false;
                break;

        }

        $output = $this->design->fetch(__DIR__.'/'.str_replace('.php', '' ,basename(__FILE__)).'.tpl');

        echo $output;

    }

    public function mobileMenuPopulars($cat_id) {

        $pupulars = $this->categories->get_populars($cat_id);

        return $pupulars;

    }

}