<div class="wcdn_container">
    <?php 
        $tab = array(   'wcdn_general' => array( 'General','wcdn_general.php' ),
                        'wcdn_document' => array( 'Document','wcdn_document.php' ),
                        'wcdn_helpguide' => array( 'Help & Guide','wcdn_helpguide.php' )
        );
        $file = 'wcdn_general.php';
    ?>
    <ul class="nav-tabs wcdn_main_tab">
        <li style="padding: 0px 30px;">
            <img src="<?php echo WooCommerce_Delivery_Notes::$plugin_url.'assets/images/invoice-logo.svg'; ?>">
        </li>
        <?php 
            foreach ($tab as $key => $value) {
                $class = '';
                if(isset( $_GET['setting'] ) && $_GET['setting'] == $key ) {
                    $class = 'active';
                    $file = $value[1];
                }else if (!isset( $_GET['setting'] ) && $key == "wcdn_general") {
                    $class = 'active';
                }
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $class; ?>" href="<?php echo get_admin_url().'admin.php?page=wc-settings&tab=wcdn-settings&setting='.$key ?>"><?php echo $value[0]; ?></a>
                </li>
                <?php
            }
        ?>
        <li>
            <?php echo WooCommerce_Delivery_Notes::$plugin_version; ?>
        </li>
    </ul>
    <div class="tab_container">
        <?php 
            include_once $file;
        ?>
    </div>
</div>