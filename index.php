<script src="js/jquery.min.js"></script>
<button onclick="auth()">auth</button><br><br><br>
<input type="file" id="file">
<input type="text" placeholder="create_text" id="text__">
<input type="text" placeholder="create_anons" id="anons__">
<input type="text" placeholder="create_tags" id="tags__">
<input type="text" placeholder="create_title" id="title__">
<button onclick="create_post()">create</button><br><br><br><br><br><br>
<input type="text" placeholder="post_id" id="post_id"><br><br><br><br><br><br>
<button onclick="update_post()">update post</button>
<button onclick="get_post()">get post</button>
<button onclick="delete_post()">delete post</button>
<button onclick="get_all_posts()">get all posts</button><br><br><br><br>
<input type="text" placeholder="author_name" id="author_name">
<input type="text" placeholder="comment" id="comment">
<button onclick="create_comment()">create comment</button><br><br><br><br><br>
<input type="text" placeholder="comment id" id="comment_id">
<button onclick="delete_comment()">delete comment</button><br><br><br><br><br>
<input type="text" placeholder="tag_name" id="tag_name">
<button onclick="search_post()">search</button>
<script src="js/index.js"></script>