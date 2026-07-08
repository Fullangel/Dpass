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


            <?php if(
                auth()->check() &&
                (
                    auth()->user()->hasRole('Admin') ||
                    auth()->user()->hasRole('admin') ||
                    auth()->user()->hasRole('supervisor') ||
                    auth()->user()->hasRole('Supervisor')
                )
            ): ?>
                <li class="<?php echo e(request()->segment(2) === 'internal-staff-data' ? 'active' : ''); ?>">
                    <a class="nav-link" href="<?php echo e(route('admin.internal-staff-data.create')); ?>">
                        <i class="fas fa-file-signature"></i>
                        <span>Carga Data Interna</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if(
                auth()->check() &&
                app(\App\Services\VisitDestinationService::class)->userCanAccessDestinationQueue(auth()->user())
            ): ?>
                <li class="<?php echo e(request()->segment(2) === 'visit-destination-queue' ? 'active' : ''); ?>">
                    <a class="nav-link" href="<?php echo e(route('admin.visit-destination-queue.index')); ?>">
                        <i class="fas fa-route"></i>
                        <span>Cola por destino</span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </aside>
</div>
<?php /**PATH /var/www/html/resources/views/admin/layouts/components/sidebar.blade.php ENDPATH**/ ?>