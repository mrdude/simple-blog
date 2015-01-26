<?php
require 'common.php';

//parse the query
$_parsedQuery = explode("/", $q);
$page_action = "edit";
$post_id = -1;

if( sizeof($_parsedQuery) >= 1 )
	$page_action = $_parsedQuery[0];

if( sizeof($_parsedQuery) >= 2 )
	$post_id = intval( $_parsedQuery[1] );

//
function get_blog_post($post_id)
{
	$post = array(
		post_id => -1,
		title => "",
		author => "",
		body => ""
	);
	
	if( $post_id == -1 )
		return $post;
	
	$db_connection = connect_to_db();
	
	$result = mysqli_query($db_connection, "SELECT * FROM posts WHERE post_id=$post_id LIMIT 1");
	
	while( $row = mysqli_fetch_assoc($result) )
	{
		$post[post_id]  = $row[post_id];
		$post[title]    = $row[title];
		$post[author]   = $row[author];
		$post[body]     = $row[body];
	}
	
	mysqli_close($db_connection);
	
	return $post;
}

function post_blog_post()
{
	if( !isset( $_POST["post_id"] ) || !isset( $_POST["author"] ) || !isset( $_POST["title"] ) || !isset( $_POST["body"] ) )
	{
		return array(
			success => false,
			err => "Missing required post information!"
		);
	}
	
	$db_connection = connect_to_db();
	
	$post = array();
	$post["post_id"]  = intval( $_POST["post_id"] );
	$post["title"]    = mysqli_real_escape_string( $db_connection, $_POST["title"] );
	$post["author"]   = mysqli_real_escape_string( $db_connection, $_POST["author"] );
	$post["body"]     = mysqli_real_escape_string( $db_connection, $_POST["body"] );
	$post["posttime"] = date( "Y-m-d H:i:s" );
	$post["slug"]     = mysqli_real_escape_string( $db_connection, generate_slug( $post ) );
	
	if( $post["post_id"] == -1 )
	{
		$result = mysqli_query($db_connection, "INSERT INTO posts (title, author, posttime, slug, body) VALUES (\"${post['title']}\", \"${post['author']}\", \"${post['posttime']}\", \"${post['slug']}\", \"${post['body']}\")");
		
		if( $result == false )
		{
			return array(
				success => false,
				err => "DB Error -> " .mysqli_error($db_connection)
			);
		}
		
		//get the post's ID
		$post["post_id"] = mysqli_insert_id($db_connection);
	}
	else
	{
		mysqli_query($db_connection, "UPDATE posts SET title=\"${post['title']}\", author=\"${post['author']}\", posttime=\"${post['posttime']}\", slug=\"${post['slug']}\", body=\"${post['body']}\" WHERE post_id=\"${post['post_id']}\"");
	}
	
	mysqli_close($db_connection);
	
	return array(
		success => true,
		post_id => $post["post_id"]
	);
}

//execute the page_action
switch( $page_action )
{
	case "edit":
		$post = get_blog_post($post_id);
		//Continue on to the rest of the page
		break;
	case "post":
		$res = post_blog_post();
		if( $res["success"] )
		{
			//redirect to the post
			header("Location: " .get_post_url($res[post_id]), true, 302); //302 temporary redirect
		}
		else
		{
			echo "<html>";
			echo "<head>";
			echo "<title>Post failed</title>";
			echo "</head>";
			echo "<body>";
			echo "Post error: ${res['err']}";
			echo "</body>";
			echo "</html>";
			exit();
		}
		break;
}

?>
<html>

<head>
	<title><?php echo $blogname ?> - Writer</title>
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script src="/js/marked.js"></script>
	<script src="/js/writer.js"></script>
	<link href="/css/writer.css" rel="stylesheet" type="text/css" />
	
	<script>
	$(document).ready( function() {
		init_writer( $("#preview"), $("#writer") );
	} );
	</script>
</head>

<body>
	<h1>
		<?php
		echo $blogname. " - Writer ";
		
		if( $post['post_id'] == -1 )
			echo "(Create Post)";
		else
			echo "(Update Post)";
		?>
	</h1>
	
	<?php
	if( $post['post_id'] != $post_id && $post_id != -1 )
	{
		//The user is trying to edit a post that doesn't exist. Instead, we're going to create a new post, and tell the user when is happening.
		echo "<p>Yikes...that post doesn't exist! Would you like to create a new one?</p>";
	}
	?>
	
	<div class="main-page-content">
		<div id="preview"></div>
		<div id="writer">
			<form method="post" action="/writer.php?q=post">
				<?php
					echo "<input type=\"text\" style='display: none' name=\"post_id\" readonly=\"true\" value=\"${post['post_id']}\" />";
				?>
				
				<input type="text" name="title" placeholder="title" required="true" />
				<input type="text" name="author" placeholder="author" required="true" />
				<?php
				echo "<textarea name='body' rows='10'>";
				
				if( $post['post_id'] != -1 )
					echo $post['body'];
				
				echo "</textarea>";
				?>
				
				<?php
					if( $post['post_id'] == -1 )
						echo "<input type=\"submit\" value=\"Create Post\" />";
					else
						echo "<input type=\"submit\" value=\"Update Post\" />";
				?>
			</form>
		</div>
	</div>
	
	<?php
	if( $post['post_id'] != -1 )
	{
		echo "<script>\n";
		//echo "$(document).ready( function() {\n";
		echo "$(\"#writer form input[name='title']\").val(\"${post['title']}\");\n";
		echo "$(\"#writer form input[name='author']\").val(\"${post['author']}\");\n";
		//echo "});\n";
		echo "</script>\n";
	}
	?>
</body>

</html>