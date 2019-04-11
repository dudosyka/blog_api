<?php
/**
 * Created by PhpStorm.
 * User: sasha
 * Date: 07.04.2019
 * Time: 18:07
 */
include_once "config_db.php";
class connect_db
{
    function connect ()
    {
        return new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", "$user", "$password");
    }
}

class Api extends connect_db
{
    function db_q($sql)
    {
        return $this->connect()->query($sql);
    }

    function db_e($sql)
    {
        return $this->connect()->exec($sql);
    }

    function myFetchAll($pdo)
    {
        $result = [];
        while ($row = $pdo->fetch(PDO::FETCH_ASSOC)) {
            $result[] = mb_convert_encoding($row, "UTF-8");
        }
        return $result;
    }

    function makeBearerToken($length = 18)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123567890';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

    function myRowCount($pdo)
    {
        $array = $this->myFetchAll($pdo);
        $i = 0;
        foreach ($array as $item) {
            $i++;
        }
        return $i;
    }

    function get_users($login, $password)
    {
        $result = $this->db_q("SELECT * FROM `api_users` WHERE `user_login` = '$login' AND `user_password` = '$password'");
        return $this->myRowCount($result);
    }

    function get_post_id($title)
    {
        $result = $this->db_q("SELECT `post_id` FROM `api_posts` WHERE `post_title` = '$title'");
        $result = $this->myFetchAll($result);
        foreach ($result as $item) {
            return $item['post_id'];
        }
    }

    function check_unique_title($title)
    {
        $result = $this->db_q("SELECT * FROM `api_posts` WHERE `post_title` = '$title'");
        if ($this->myRowCount($result) > 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    function create_post()
    {
        $img_types = array("jpg", "png");
        $title = $_POST['title'];
        $anons = $_POST['anons'];
        $text  = $_POST['text'];
        $tags  = $_POST['tags'];
        $tags  = trim($tags);
        $tags  = trim($tags, ",");
        $tags  = explode(",", $tags);
        $tags  = json_encode($tags, JSON_UNESCAPED_UNICODE);
        $img   = $_FILES['img']['name'];
        $img_tmp_name = $_FILES['img']['tmp_name'];
        $img_type = explode(".", $img)[count(explode(".", $img)) - 1];
        $img = md5($img).".".$img_type;
        if(
            !empty($title) && isset($title) &&
            !empty($anons) && isset($anons) &&
            !empty($text) && isset($text) &&
            !empty($tags) && isset($tags) &&
            in_array($img_type, $img_types) &&
            $this->check_unique_title($title)
        )
        {
            $tags = json_encode($tags, JSON_UNESCAPED_UNICODE);
            $result = $this->db_e("INSERT INTO `api_posts`
                (`post_title`, `post_anons`, `post_text`, `post_tags`, `post_img`)
                VALUES ('$title','$anons','$text','$tags','$img')");
            if ($result)
            {
                if (is_uploaded_file($img_tmp_name))
                {
                    move_uploaded_file($img_tmp_name, "post_images/".$img);
                    header('HTTP/1.0 201 Successful creating');
                    $post_id = $this->get_post_id($title);
                    $out = array("status" => 201, "post_id" => $post_id);
                    echo json_encode($out, JSON_UNESCAPED_UNICODE);
                }
                else
                {
                    header("HTTP/1.0 403 Bad request");
                    $this->check_unique_title($title) ? $errors = "not unique title" : $errors = "";
                    $out = array("status" => 403, "message" => "Upload error", "errors" => $errors);
                    echo json_encode($out, JSON_UNESCAPED_UNICODE);
                }
            }
            else
            {
                header("HTTP/1.0 403 Bad request");
                $this->check_unique_title($title) ? $errors = "not unique title" : $errors = "";
                $out = array("status" => 403, "message" => "Error when create post check all data that you sent", "errors" => $errors);
                echo json_encode($out, JSON_UNESCAPED_UNICODE);
            }
        }
        else
        {
            header("HTTP/1.0 403 Bad request");
            $out = array("status" => 403, "message" => "Error when create post check all data that you sent");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        }
    }

    function isset_post($post_id)
    {
        $result = $this->db_q("SELECT * FROM `api_posts` WHERE `post_id` = $post_id");
        if ($this->myRowCount($result) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function update_post($post_id)
    {
        if (!$this->isset_post($post_id))
        {
            header("HTTP/1.0 404 Post not found");
            $out = array("status" => 404, "message" => "Post not found");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            return;
        }
        $img_types = array("jpg", "png");
        $title = $_POST['title'];
        $anons = $_POST['anons'];
        $text  = $_POST['text'];
        $tags  = $_POST['tags'];
        $tags  = trim($tags);
        $tags  = trim($tags, ",");
        $tags  = explode(",", $tags);
        $tags  = json_encode($tags, JSON_UNESCAPED_UNICODE);
        $img   = $_FILES['img']['name'];
        $img_tmp_name = $_FILES['img']['tmp_name'];
        $img_type = explode(".", $img)[count(explode(".", $img)) - 1];
        $img = md5($img).".".$img_type;
        if(
            !empty($title) && isset($title) &&
            !empty($anons) && isset($anons) &&
            !empty($text) && isset($text) &&
            !empty($tags) && isset($tags) &&
            in_array($img_type, $img_types)
        )
        {
            $tags = json_encode($tags, JSON_UNESCAPED_UNICODE);
            $result = $this->db_e("UPDATE `api_posts` 
            SET
            `post_title`='$title',
            `post_anons`='$anons',
            `post_text`='$text',
            `post_tags`='$tags',
            `post_img`='$img' 
            WHERE `post_id` = $post_id");
            if ($result)
            {
                if (is_uploaded_file($img_tmp_name))
                {
                    move_uploaded_file($img_tmp_name, "post_images/".$img);
                    header('HTTP/1.0 201 Successful creation');
                    $post_id = $this->get_post_id($title);
                    $out = array("status" => 201, "post" => array("title" => $title, "anons" => $anons, "text" => $text, "tags" => $tags, "image" => $img));
                    echo json_encode($out, JSON_UNESCAPED_UNICODE);
                }
                else
                {
                    header("HTTP/1.0 403 Bad request");
                    $out = array("status" => 403, "message" => "Editing error, isn`t uploaded");
                    echo json_encode($out, JSON_UNESCAPED_UNICODE);
                }
            }
            else
            {
                header("HTTP/1.0 403 Bad request");
                $out = array("status" => 403, "message" => "Editing error database error");
                echo json_encode($out, JSON_UNESCAPED_UNICODE);
            }
        }
        else
        {
            header("HTTP/1.0 403 Bad request");
            $out = array("status" => 403, "message" => "Editing error check all data");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        }
    }

    function delete_post($post_id)
    {
        if (!$this->isset_post($post_id))
        {
            header("HTTP/1.0 404 Post not found");
            $out = array("status" => 404, "message" => "Post not found");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            return;
        }
        $result = $this->db_e("DELETE FROM `api_posts` WHERE `post_id` = $post_id");
        if ($result)
        {
            header("HTTP/1.0 201 Successful deleted");
            $out = array("status" => 201, "message" => "Successful deleted");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        }
        else
        {
            header("HTTP/1.0 400 Bad request");
            $out = array("status" => 400, "message" => "Bad request check your sent data");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        }
    }

    function get_all_posts()
    {
        $result = $this->db_q("SELECT * FROM `api_posts`");
        header("HTTP/1.0 200 List posts");
        echo json_encode(array("status" => 200, "message" => "List posts", "posts" => $this->myFetchAll($result)), JSON_UNESCAPED_UNICODE);
    }

    function get_comments($post_id)
    {
        $result = $this->db_q("SELECT `com`.comment_id,`com`.comment_text AS `comment`, `user`.user_login AS `author` FROM `api_comments` AS `com` 
                LEFT JOIN `api_users` AS `user` ON `com`.comment_author_id = `user`.user_id
                WHERE `father_post_id` = $post_id");
        return $this->myFetchAll($result);
    }

    function get_post($post_id)
    {
        if (!$this->isset_post($post_id))
        {
            header("HTTP/1.0 404 Post not found");
            $out = array("status" => 404, "message" => "Post not found");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            return;
        }
        $result = $this->db_q("SELECT * FROM `api_posts` WHERE `post_id` = $post_id");
        header("HTTP/1.0 200 View post");
        echo json_encode(array_merge($this->myFetchAll($result),array("comment" => $this->get_comments($post_id))), JSON_UNESCAPED_UNICODE);
    }

    function get_user_id()
    {
        $login = $_SESSION['auth_login'];
        $result = $this->db_q("SELECT * FROM `api_users` WHERE `user_login` = '$login'");
        $result = $this->myFetchAll($result);
        if (is_array($result))
        {
            foreach ($result as $item)
            {
                return $item['user_id'];
            }
        }
    }

    function create_comment($post_id)
    {
        //var_dump($post_id);
        if (!$this->isset_post($post_id))
        {
            header("HTTP/1.0 404 Post not found");
            $out = array("status" => 404, "message" => "Post not found");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            return;
        }
        if (isset($_POST['comment']) && !empty($_POST['comment']))
        {
            $author_id = $this->get_user_id();
            $comment = $_POST['comment'];
            $result = $this->db_e("
                    INSERT INTO `api_comments`
                    (`comment_author_id`, `father_post_id`, `comment_text`) 
                    VALUES ($author_id,$post_id,'$comment');
                    ");
            if ($result)
            {
                header("HTTP/1.0 201 Successful creation");
                $out = array("status" => 201, "message" => "Successful creation");
                echo json_encode($out, JSON_UNESCAPED_UNICODE);
                return;
            }
            else
            {
                header("HTTP/1.0 400 Creating error");
                $out = array("status" => 400, "message" => "Creating error");
                echo json_encode($out, JSON_UNESCAPED_UNICODE);
                return;
            }
         }
    }

    function isset_comment($comment_id)
    {
        $result = $this->db_q("SELECT * FROM `api_comments` WHERE `comment_id` = $comment_id");
        if ($this->myRowCount($result) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function delete_comment($post_id, $comment_id)
    {
        if (!$this->isset_post($post_id))
        {
            header("HTTP/1.0 404 Post not found");
            $out = array("status" => 404, "message" => "Post not found");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!$this->isset_comment($comment_id))
        {
            header("HTTP/1.0 404 Comment not found");
            $out = array("status" => 404, "message" => "Comment not found");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            return;
        }
        $result = $this->db_q("DELETE FROM `api_comments` WHERE `comment_id` = $comment_id");
        if ($result)
        {
            header("HTTP/1.0 201 Successful deleted");
            $out = array("status" => 201, "message" => "Successful deleted");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        }
        else
        {
            header("HTTP/1.0 400 Bad request");
            $out = array("status" => 400, "message" => "Bad request check your sent data");
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        }
    }

    function search_posts($tag)
    {
        $result = $this->db_q("SELECT * FROM `api_posts` WHERE `post_tags` LIKE '%$tag%'");
        $result = $this->myFetchAll($result);
        header("HTTP/1.0 201 Found posts");
        $out = array("status" => 200, "Found posts" => $result);
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        return;
    }

}