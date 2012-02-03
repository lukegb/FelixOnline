<?php
$timing->log('page');
$header = array(
    'title' => $page->getTitle().' - Felix Online'
); 

$theme->render('header', $header);
?>
<!-- Page wrapper -->
<div class="container_12">
	<!-- Sidebar -->
	<div class="sidebar grid_4 push_8 contact">
		<?php 
            $theme->setSidebar(array(
                'fbActivity', 
                //'mediaBox',
                'mostPopular'
            ));
            $theme->renderSidebar();
		?>
	</div>
	<!-- End of sidebar -->

	<!-- Page container -->
	<div class="grid_8 pull_4">
        <?php echo $page->getContent(); ?>
	</div>
	<!-- End of contact container -->
</div>
<!-- End of page -->

<?php $timing->log('end of page');?>
<?php $theme->render('footer'); ?>
