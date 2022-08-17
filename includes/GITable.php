<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Tokens_Table extends WP_List_Table
{

    public function __construct()
    {

        global $status, $page;

        parent::__construct(array(
            'singular' => __('token', 'tokens_table'),
            'plural' => __('tokens', 'tokens_table'),
            'ajax' => false,
        ));

        add_action('admin_head', array(&$this, 'admin_header'));
    }

    public function admin_header()
    {
        $page = (isset($_GET['page'])) ? sanitize_key($_GET['page']) : false;
        return;
    }

    public function no_items()
    {
        _e('No tokens found.');
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'card':
            case 'username':
            case 'token':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'ccard' => array('ccard', false),
            'username' => array('username', false),
            'token' => array('token', false),
        );
        return $sortable_columns;
    }

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'ccard' => geideaTokensCard,
            'username' => geideaTokensUsername,
            'token' => geideaTokensToken,
        );
        return $columns;
    }

    public function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? sanitize_key($_GET['orderby']) : 'ccard';

        $order = (!empty($_GET['order'])) ? sanitize_key($_GET['order']) : 'asc';

        $result = strcmp($a[$orderby], $b[$orderby]);

        return ($order === 'asc') ? $result : -$result;
    }

    public function column_ccard($item)
    {
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $result_url = $uri_parts[0] . "?";
        foreach ($_GET as $k => $v) {
            $k = sanitize_key($k);
            $v = sanitize_key($v);

            if ($k != 'token' && $k != 'action') {
                $result_url .= $k . "=" . $v . "&";
            }
        }
        $result_url = rtrim($result_url, "&");

        $actions = array(
            'delete' => sprintf('<a href="%s&action=%s&token=%s">Delete</a>', $result_url, 'delete', $item['ID']),
        );

        return sprintf('%1$s %2$s', $item['ccard'], $this->row_actions($actions));
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => geideaTokensDelete,
        );
        return $actions;
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="delete_token_%s" value="%s" />', $item['ID'], $item['ID']
        );
    }

    public function prepare_items()
    {
        //TODO now get_tokens returns all of the tokens
        // may be better to make query to database with offset and limit
        // because we need only n-items(e.g. 10) on the page
        $tokens = WC_Payment_Tokens::get_tokens(
            array(
                'gateway_id' => 'geidea',
            )
        );

        $total_tokens = count($tokens);

        $all_data = [];
        foreach ($tokens as $t) {
            $data = $t->get_data();
            $card = $data['card_type'] . ' ' . geideaTokensEndingIn . ' ' . $data['last4'] . " (" . geideaTokensExpires . " " . $data['expiry_month'] . "/" . $data['expiry_year'] . ")";
            $card = ucfirst($card);

            $user = get_userdata($data['user_id']);
            $all_data[] = array(
                'ID' => $data['id'],
                'ccard' => $card,
                'username' => $user->user_nicename,
                'token' => $data['token'],
            );
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($all_data, array(&$this, 'usort_reorder'));

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($all_data);

        $page_data = array_slice($all_data, (($current_page - 1) * $per_page), $per_page);
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
        ));

        $this->items = $page_data;
    }
}

function render_tokens_table()
{
    global $tokensTable;
    $tokensTable = new Tokens_Table();
    $tokensTable->prepare_items();
    $tokensTable->display();
}
