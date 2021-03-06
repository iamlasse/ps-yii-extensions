<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * Lists posts
 * 
 * @package 	blog.views
 * @subpackage 	post
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: list.php 362 2010-01-03 05:34:35Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

//	Show tags header 
if ( $_sTag = PS::o( $_GET, 'tag' ) )
	echo PS::tag( 'h3', array(), 'Posts Tagged with "' . PS::encode( $_sTag ) . '"' );

if ( $postDate )
	echo PS::tag( 'h3', array(), 'Posts from ' . $postDate );

//	Show posts
foreach ( $posts as $_oPost ) 
	$this->renderPartial( '_post', array( 'post' => $_oPost, 'show' => false, 'postDate' => $postDate ) );

echo '<br/>';

//echo $this->widget( 'CPSLinkPager', array( 'pages' => $pages ) );
