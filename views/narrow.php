<a href="<?php echo (!empty($instance['is_accordion'])) ? '#' : get_the_permalink( $instance['post_id'] ); ?>" title="<?php echo (!empty($instance['is_accordion'])) ? 'Click to expand/collapse' : ''; ?>" class="post_snippet_anchor<?php echo (!empty($instance['is_accordion'])) ? ' accordion' : ''; ?>">
	<div class="post_snippet narrow" <?php echo $instance['style_bg']; ?>>
		<?php if( empty( $instance['dont_display_image'] ) ) { ?>
			<div class="post_snippet_image"><?php echo get_the_post_thumbnail($instance['post_id']); ?></div>
		<?php } ?>
		<!-- title and excerpt -->
		<div class="post_snippet_container">
			<?php if( empty( $instance['dont_display_title'] ) ) { ?>
				<h4 class="post_snippet_title" <?php echo $instance['style_title']; ?>><?php echo $instance['title']; ?></h4> 
			<?php } ?>
			<?php if( empty( $instance['dont_display_excerpt'] ) ) { ?>
				<p class="post_snippet_content" <?php echo $instance['style_content']; ?>><?php echo $instance['excerpt']; ?></p>
			<?php } ?>
		</div>
	</div>
</a>
<?php if( !empty( $instance['is_accordion'] ) ) { ?>
	<div class="post_content_inner" style="border:1px solid <?php echo $instance['bg_color']; ?>">
		<?php
			echo $instance['content'];
		?>
		<div class="top_collapse" title="Click to collapse">
			<a>&#x21C8; TOP &#x21C8;</a>
		</div>
	</div>
<?php } ?>