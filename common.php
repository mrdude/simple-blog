<?php
$db_host   = "localhost";
$db_user   = "web";
$db_pass   = "web_pass_!Qaz@WsxVCFDre43";
$db_dbname = "blog";

$use_clean_urls = true; //if true, then the apache mod_rewrite rules are on
/* The Apache rewrite rules:
# Rewrite rules!!!
RewriteEngine On
RewriteRule /index/(.*) /index.php?q=$1
RewriteRule /writer/(.*) /writer.php?q=$1
RewriteRule /admin/(.*) /admin.php?q=$1
*/
$posts_per_page = 10;
$blogname = "My Blag";

$q = "view";
if( isset($_GET["q"]) )
	$q = $_GET["q"];

function connect_to_db()
{
	global $db_host, $db_user, $db_pass, $db_dbname;
	
	$db_connection = mysqli_connect($db_host, $db_user, $db_pass, $db_dbname);
	
	//create the schema if it doesn't exist
	mysqli_query($db_connection, "CREATE TABLE IF NOT EXISTS posts (post_id INT PRIMARY KEY AUTO_INCREMENT, title TEXT NOT NULL, author TEXT NOT NULL, posttime DATETIME NOT NULL, slug TEXT NOT NULL, body TEXT NOT NULL)");
	
	return $db_connection;
}

function generate_slug($post_data)
{
	$date = date( "Y-m-d", strtotime($post_data['posttime']) );
	$slug = $date. "/" .mt_rand(). "/" .$post_data['author']. "/" .$post_data['title'];
	$slug = str_replace(" ", "-", $slug);
	return $slug;
}

function generate_summary_for_post($body)
{
	if( strlen($body) > 200 )
		return substr($body, 0, 200). "...";
	else
		return $body;
}

function get_post_url($post_id)
{
	global $use_clean_urls;
	
	if( $use_clean_urls )
		return "/index/view_post/$post_id";
	else
		return "/index.php?q=view_post/$post_id";
}

function get_pageview_url($page)
{
	global $use_clean_urls;
	
	if( $use_clean_urls )
		return "/index/view/$page";
	else
		return "/index.php?q=view/$page";
}

function get_writer_newpost_url()
{
	global $use_clean_urls;
	
	if( $use_clean_urls )
		return "/writer/edit";
	else
		return "/writer.php";
}

function get_writer_editpost_url($post_id)
{
	global $use_clean_urls;
	
	if( $use_clean_urls )
		return "/writer/edit/$post_id";
	else
		return "/writer.php?q=edit/$post_id";
}

function get_post_permalink($slug)
{
	global $use_clean_urls;
	
	if( $use_clean_urls )
		return "/index/view_slug/$slug";
	else
		return "/index.php?q=view_slug/$slug";
}

function emit_html_for_post($post_id, $title, $author, $sql_posttime, $slug, $body, $summary)
{
	echo "<div class='post'>";
	echo "<span class='title'>$title</span>";
	echo "<span class='byline'>by $author - $sql_posttime - <a href='" .get_writer_editpost_url($post_id). "'>Edit</a> - <a href='" .get_post_permalink($slug). "'>Permalink</a></span>";
	echo "<span class='body'>";
	
	if( $summary )
		echo generate_summary_for_post($body);
	else
		echo $body;
	
	echo "</span>";
	
	if( $summary )
		echo "<a href='" .get_post_url($post_id). "'>Read the rest of this post here</a>";
	
	echo "</div>";
}

?>