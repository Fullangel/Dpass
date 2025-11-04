<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?php echo e(route('admin.dashboard.index')); ?>"><?php echo e(setting('site_name')); ?></a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?php echo e(route('admin.dashboard.index')); ?>">
                <?php 
                    if(setting('site_name')) {
                        $sitenames = explode(' ', setting('site_name'));
                        if(count($sitenames) > 1) {
                            foreach ($sitenames as $sitename) {
                                echo $sitename[0];
                            }
                        } else {
                            echo substr(setting('site_name'), 0, 2);
                        }
                    }
                ?>
            </a>
        </div>

        
        <ul class="sidebar-menu">
            
            <?php echo $backendMenus; ?>


        </ul>
    </aside>
</div>
<?php /**PATH /var/www/html/resources/views/admin/layouts/components/sidebar.blade.php ENDPATH**/ ?>