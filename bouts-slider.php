<?php
/*
Plugin Name: bouts slider
Description: slideshow responsive
Version: 1.0.0
Author: Laurent Botella
Author URI: http://www.laurentbotella-designer.com
Licence: GPL
*/

add_action('init', 'boutsslider_init');
add_action('add_meta_boxes', 'boutsslider_metaboxes');
add_action('save_post', 'boutsslider_savepost', 10, 4);
add_action('manage_edit-slide_columns', 'boutsslider_columnsfilter');
add_action('manage_posts_custom_column', 'boutsslider_columns');


function boutsslider_init() {

	//permet d'initialiser les fonctionalités back-office du slider
	$labels = array(
		'name'               => 'Slide',
		'singular_name'      => 'Slide',
		'add_new'            => 'Ajouter un Slide',
		'add_new_item'       => 'Ajouter un nouveau Slide',
		'edit_item'          => 'Editer un Slide',
		'new_item'           => 'Nouveau Slide',
		'view_item'          => 'Voir l\'Slide',
		'search_items'       => 'Chercher un Slide',
		'not_found'          => 'Aucun Slide',
		'not_found_in_trash' => 'aucun Slide dans la corbeille',
		'parent_item_colon'  => '',
		'menu_name'          => 'Slides',
		);

	register_post_type('slide', array(
		'public'             => true,
		'publicly_queryable' => false,
		'labels'             => $labels,
		'menu_position'      => 21,
		'capability_type'    => 'post',
		'supports'           => array('title', 'thumbnail'),
		));
	//definition de la taille du slider
	add_image_size('slider', 1000, 300, true);

}

function boutsslider_columnsfilter($columns) {

	//permet d'ajouter une vignette dans les colone
	$thumb = array('thumbnail' => 'image');
	$columns = array_slice($columns, 0, 1) + $thumb + array_slice($columns, 1, null);
	return $columns; 

}

function boutsslider_columns($column) {

	//insere le lien dsur la vignette
	global $post;

	if ($column == 'thumbnail') {
		echo edit_post_link(get_the_post_thumbnail($post->ID, 'thumbnail'), null, null, $post->ID);
	}

}

function boutsslider_metaboxes() {

	// 1- permet de gerer les metaboxes
	add_meta_box('boutsslider', 'slogan', 'boutsslider_metabox', 'Slide', 'normal', 'high');
	add_meta_box('boutsslider2', 'legend', 'boutsslider_metabox2', 'Slide', 'normal', 'core');
	add_meta_box('boutsslider3', 'lien', 'boutsslider_metabox3', 'Slide', 'normal', 'low');

}

function boutsslider_metabox($object) {

	//insertion d'un champ caché
	wp_nonce_field('boutsslider', 'boutsslider_nonce');

	// 2 - permet de gerer le lien metabox
	?>
		<div class="meta-box-item-title">
			<h4>slogan</h4>
		</div>
		<div class="meta-box-item-content">
			<input type="text" name="boutsslider_slogan" style="width:100%;" value="<?= esc_attr(get_post_meta($object->ID,'_slogan',true)); ?>">
		</div>
	<?php

}

function boutsslider_metabox2($object) {

	//insertion d'un champ caché
	wp_nonce_field('boutsslider2', 'boutsslider_nonce');

	// 2 - permet de gerer le lien metabox
	?>
	
		<div class="meta-box-item-title">
			<h4>Legende</h4>
		</div>
		<div class="meta-box-item-content">
			<input type="text" name="boutsslider_legend" style="width:100%;" value="<?= esc_attr(get_post_meta($object->ID,'_legend',true)); ?>">
		</div>
	<?php

}

function boutsslider_metabox3($object) {

	//insertion d'un champ caché
	wp_nonce_field('boutsslider3', 'boutsslider_nonce');

	// 2 - permet de gerer le lien metabox
	?>
		<div class="meta-box-item-title">
			<h4>texte</h4>
		</div>
		<div class="meta-box-item-content">
			<input type="text" name="boutsslider_more" style="width:100%;" value="<?= esc_attr(get_post_meta($object->ID,'_more',true)); ?>">
		</div>
		<div class="meta-box-item-title">
			<h4>url</h4>
		</div>
		<div class="meta-box-item-content">
			<input type="text" name="boutsslider_link" style="width:100%;" value="<?= esc_attr(get_post_meta($object->ID,'_link',true)); ?>">
		</div>
	<?php

}

function boutsslider_savepost($post_id, $post) {

	//securisation des input
	if (!isset($_POST['boutsslider_link']) || !wp_verify_nonce($_POST['boutsslider_nonce'], 'boutsslider3')) {
		return $post_id;
	}
	if (!isset($_POST['boutsslider_more']) || !wp_verify_nonce($_POST['boutsslider_nonce'], 'boutsslider3')) {
		return $post_id;
	}

	$type = get_post_type_object($post->post_type);
	if (!current_user_can($type->cap->edit_post)) {
		return $post_id;
	}
	// 3 - enregistre le lien en bdd
	update_post_meta($post_id,'_slogan',$_POST['boutsslider_slogan']);
	update_post_meta($post_id,'_legend',$_POST['boutsslider_legend']);
	update_post_meta($post_id,'_more',$_POST['boutsslider_more']);
	update_post_meta($post_id,'_link',$_POST['boutsslider_link']);

}

function boutsslider_show($limit = 10) {

	//inclusion script
	wp_enqueue_script('slider-jquery', plugins_url() . '/bouts-slider/js/slider.js', array('jquery'), true);
	wp_enqueue_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js', null, true);
	wp_enqueue_style('animate', plugins_url() . '/bouts-slider/css/animate.css', null, true);
	wp_enqueue_style('slider-style', plugins_url() . '/bouts-slider/css/slider.css', null, true);
	add_action('wp_footer', 'boutsslider_script', 30);

	//permet d'afficher le slider
	$slides = new WP_query('post_type=slide & posts_per_page = $limit'); ?>
	<div id="boutsslider">
	<?php
		while ($slides->have_posts()) {
			$slides->the_post(); ?>
			<div style="width:100%;">
				<?php global $post; ?>
				<div class="legende">
					<h3><?= esc_attr(get_post_meta($post->ID, '_slogan', true)); ?></h3>
					<p><?= esc_attr(get_post_meta($post->ID, '_legend', true)); ?></p>
					<?php if (get_post_meta($post->ID, '_link', true)) { ?>
					<div class="lien">
						<a href="<?= esc_attr(get_post_meta($post->ID, '_link', true)); ?>">
							<h4><?= esc_attr(get_post_meta($post->ID, '_more', true)); ?></h4> 
						</a>
					</div>
					<?php
					}
					?>
				</div>
				<?php the_post_thumbnail('slider'); ?>
			</div>
		<?php 
	} ?>
	</div>
<?php

}

function boutsslider_script() {

	//inclusion initialisation slide jquery
	?>

	<script>
	jQuery(function($) {
	$('#boutsslider').sss({
		slideShow : true, // lancement automatique
		startOn : 0, // point de depart du slide
		transition : 400, // temps de la transition
		speed : 4000, // vitesse du defilement
		showNav : true // affiche la navigation
		})
	});
	</script>


	<?php
	
}
