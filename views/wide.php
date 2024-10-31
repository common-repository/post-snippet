<a href="<?php echo (!empty($instance['is_accordion'])) ? '#' : get_the_permalink( $instance['post_id'] ); ?>" title="<?php echo (!empty($instance['is_accordion'])) ? 'Click to expand/collapse' : ''; ?>" class="post_snippet_anchor<?php echo (!empty($instance['is_accordion'])) ? ' accordion' : ''; ?>">
	<div class="post_snippet wide" <?php echo $instance['style_bg']; ?>>
			<?php 
					$ps_display = empty( $instance['dont_display_image'] ) ? 'ps_show' : 'ps_hide'; 
					$ps_display_responsive = empty( $instance['dont_display_image_responsive'] ) ? 'ps_show_responsive' : 'ps_hide_responsive'; 
				?>
				<? if( has_post_thumbnail( $instance['post_id'] ) ): ?>
					<div style="display:none;" class="post_snippet_image" data-show="<?php  echo $ps_display; ?>" data-show-responsive="<?php  echo $ps_display_responsive; ?>">
						<img src="<?=wp_get_attachment_url( get_post_thumbnail_id() ); ?>" style="width:auto; height:<?php echo $instance['height']; ?>px;">
					</div>
				<? endif; ?>
				
			<!-- title and excerpt -->
			<div class="post_snippet_container"> 
				<?php 
					$ps_display = empty( $instance['dont_display_title'] ) ? 'ps_show' : 'ps_hide'; 
					$ps_display_responsive = empty( $instance['dont_display_title_responsive'] ) ? 'ps_show_responsive' : 'ps_hide_responsive'; 
				?>
				<h4 class="post_snippet_title" <?php echo $instance['style_title']; ?> data-show="<?php  echo $ps_display; ?>" data-show-responsive="<?php  echo $ps_display_responsive; ?>"><?php echo $instance['title']; ?></h4> 
				
				<?php 
					$ps_display = empty( $instance['dont_display_excerpt'] ) ? 'ps_show' : 'ps_hide'; 
					$ps_display_responsive = empty( $instance['dont_display_excerpt_responsive'] ) ? 'ps_show_responsive' : 'ps_hide_responsive'; 
				?>
				<p class="post_snippet_content" <?php echo $instance['style_content']; ?> data-show="<?php  echo $ps_display; ?>" data-show-responsive="<?php  echo $ps_display_responsive; ?>" data-excerpt="<?php  echo $instance['excerpt']; ?>" data-excerpt-responsive="<?php  echo $instance['excerpt-responsive']; ?>"><?php echo $instance['excerpt']; ?></p>				
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
