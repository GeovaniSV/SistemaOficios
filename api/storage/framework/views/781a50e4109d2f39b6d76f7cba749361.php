<?php if($deprecated !== false): ?>
<?php ($text = $deprecated === true ? 'deprecated' : "deprecated:$deprecated"); ?>
<?php $__env->startComponent('scribe::components.badges.base', ['colour' => 'darkgoldenrod', 'text' => $text]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Hugo\Documents\GitHub\SistemaOficios\api\vendor\knuckleswtf\scribe\src/../resources/views//components/badges/deprecated.blade.php ENDPATH**/ ?>