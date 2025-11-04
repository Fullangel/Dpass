<footer class="main-footer">
    <div class="footer-left">
    	<?php echo e(setting('site_footer')); ?>

    </div>
    <div class="footer-right">v<?php echo e(\App\Libraries\MyString::version(config('site.version'))); ?></div>
</footer>
<?php /**PATH /var/www/html/resources/views/admin/layouts/components/footer.blade.php ENDPATH**/ ?>