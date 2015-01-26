<?php
require 'common.php';

//parse the query
$_parsedQuery = explode("/", $q);
$page_action = "view";
$post_id = 1;
$page = 1;
$slug = "";

if( sizeof($_parsedQuery) >= 1 )
	$page_action = $_parsedQuery[0];

switch( $page_action )
{
	case "view":
		if( sizeof($_parsedQuery) >= 2 )
			$page = intval( $_parsedQuery[1] );
		
		if( $page < 1 )
			$page = 1;
		
		break;
	case "view_post":
		if( sizeof($_parsedQuery) >= 2 )
			$post_id = intval( $_parsedQuery[1] );
		break;
	case "view_slug":
		if( sizeof($_parsedQuery) >= 2 )
			$slug = implode("/", array_slice($_parsedQuery, 1));
		break;
}

function emit_html_for_view_page($page)
{
	global $posts_per_page;
	
	$db_connection = connect_to_db();
	
	$result = mysqli_query($db_connection, "SELECT * FROM posts ORDER BY posttime DESC LIMIT " .($posts_per_page+1). " OFFSET " .(($page-1) * $posts_per_page));
	
	$rowcount = 0;
	while( $row = mysqli_fetch_assoc($result) )
	{
		if( $rowcount < $posts_per_page )
		{
			emit_html_for_post( $row["post_id"], $row["title"], $row["author"], $row["posttime"], $row["slug"], $row["body"], true );
			$rowcount++;
		}
		else
		{
			echo "<div class='post'>";
			echo "<a href='" .get_pageview_url($page+1). "' class='older_posts_link'>Older Posts</a>";
			echo "</div>";
		}
	}
	
	if( $rowcount == 0 )
	{
		echo "No more posts.";
	}
	
	mysqli_close($db_connection);
}

function emit_html_for_view_post($post_id)
{
	$db_connection = connect_to_db();
	
	$result = mysqli_query($db_connection, "SELECT * FROM posts WHERE post_id=$post_id");
	
	$rowcount = 0;
	while( $row = mysqli_fetch_assoc($result) )
	{
		emit_html_for_post( $row["post_id"], $row["title"], $row["author"], $row["posttime"], $row["slug"], $row["body"], false );
		$rowcount++;
	}
	
	if( $rowcount == 0 )
	{
		echo "There is no post with that ID.";
	}
	
	mysqli_close($db_connection);
}

function emit_html_for_view_slug($slug)
{
	$db_connection = connect_to_db();
	
	$slug = mysqli_real_escape_string($db_connection, $slug);
	
	$result = mysqli_query($db_connection, "SELECT * FROM posts WHERE slug=\"$slug\"");
	
	$rowcount = 0;
	while( $row = mysqli_fetch_assoc($result) )
	{
		emit_html_for_post( $row["post_id"], $row["title"], $row["author"], $row["posttime"], $row["slug"], $row["body"], false );
		$rowcount++;
	}
	
	if( $rowcount == 0 )
	{
		echo "There is no post with that slug. --> $slug";
	}
	
	mysqli_close($db_connection);
}
?>
<!DOCTYPE html>
<html>

<head>
	<title><?php echo $blogname ?></title>
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script src="/js/marked.js"></script>
	
	<link href="/css/index.css" rel="stylesheet" type="text/css" />
	
	<script>
	$(document).ready( function() {
		//compile all post bodies from markdown -> HTML
		$("#main-page-content .post .body:not([data-compiled-markdown])").each( function(index, element) {
			var post = $(this).text();
			$(this).html( marked(post) );
			$(this).attr("data-compiled-markdown", "true");
		} );
		
		//fix the sidebar and page content
		var page_content_x = $("#sidebar").position().left + $("#sidebar").outerWidth(true);
		var page_content_width = $("body").outerWidth() - $("#sidebar").outerWidth(true);
		$("#main-page-content").css({
			"width": page_content_width+ "px",
			"left": page_content_x+ "px"
		});
	} );
	</script>
</head>

<body>
	<div id="sidebar">
		<ul>
			<li class='blogname'><a href='/'><?php echo $blogname; ?></a></li>
			<li>
				<?php
				echo "<a href='" .get_writer_newpost_url(). "'>Write a post</a>";
				?>
			</li>
		</ul>
	</div>
	
	<div id="main-page-content">
		<?php
		switch( $page_action )
		{
			case "view":
				emit_html_for_view_page($page);
				break;
			case "view_post":
				emit_html_for_view_post($post_id);
				break;
			case "view_slug":
				emit_html_for_view_slug($slug);
				break;
		}
		?>
	</div>
</body>

</html>