<header class="banner navbar navbar-default navbar-fixed-top" role="banner">
	<div class="container" >
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only"><?= __('Toggle navigation', 'sage'); ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php bloginfo('url'); ?>">
				<img src="<?php bloginfo('template_url') ?>/dist/images/logo.png" alt="Logotype" alt="<?php bloginfo('description'); ?>" title="<?php bloginfo('name'); ?>" />
			</a>
		</div>
			<h3 class="navbar-text"><?php bloginfo( 'description' ); ?></h3>
		<nav class="collapse navbar-collapse" role="navigation">
	 <!--  <h3 class="navbar-text "> -->
			<?php
			if (has_nav_menu('primary_navigation')) :
				wp_nav_menu(['theme_location' => 'primary_navigation', 'walker' => new wp_bootstrap_navwalker(), 'menu_class' => 'nav navbar-nav navbar-right']);
			endif;
			?>
		</nav>
	</div>
</header>