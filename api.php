<?php
session_start();
//error_reporting(0);
include_once "config.php";
/**
 * Created by PhpStorm.
 * User: sasha
 * Date: 07.04.2019
 * Time: 16:26
 */
$api = new Api();
$url = "";
header('Access-Control-Allow-Origin: *');
if (isset($_GET['sent']))
{
    $url = $_GET['sent'];
}
$url = rtrim($url, '/');
$urls = explode('/', $url);
$method = $_SERVER['REQUEST_METHOD'];
if ($urls[0] == "auth")
{
    if ($method == "POST")
    {
        $badout = array("status"=>401, "message"=>"Invalid authorization data");
        if (isset($_POST['login']) && isset($_POST['password']) && !empty($_POST['login']) && !empty($_POST['password']))
        {
            $users = $api->get_users($_POST['login'], $_POST['password']);
            if ($users > 0)
            {
                header("HTTP/1.0 200 Successful authorization");
                $_SESSION['bearer'] = $api->makeBearerToken();
                $_SESSION['auth_login'] = $_POST['login'];
                $out = array("status" => 200, "token" => $_SESSION['bearer']);
                echo json_encode($out);
            }
            else
            {
                header("HTTP/1.0 401 Invalid authorization data");
                echo json_encode($badout);
            }
        }
        else
        {
            header("HTTP/1.0 401 Invalid authorization data");
            echo json_encode($badout);
        }
    }
}

if ($urls[0] == "posts")
{
    $headers = getallheaders();
    if ($headers['Authorization'] != $_SESSION['bearer'])
    {
        header("HTTP/1.0 401 Unauthorized");
        $out = array("status" => 401, "message" => "you are not authorized yet");
        echo json_encode($out);
    }
    else {
        if (isset($urls[1]) && !empty($urls[1]) && $urls[1] == "tag")
        {
            if ($method == "GET" && !empty($urls[2]) && isset($urls[2]))
            {
                $api->search_posts($urls[2]);
            }
        }
        else if (isset($urls[1])) {
            if (isset($urls[2]) && $urls[2] == "comments") {
                if ($method == "POST") {
                    $api->create_comment($urls[1]);
                }
                if (isset($urls[3]) && $method == "DELETE" && !empty($urls[3]))
                {
                    $api->delete_comment($urls[1],$urls[3]);
                }
            }
            else
            {
                if ($method == "POST")
                {
                    $api->update_post($urls[1]);
                }
                else if ($method == "DELETE")
                {
                    $api->delete_post($urls[1]);
                }
                else if ($method == "GET")
                {
                    $api->get_post($urls[1]);
                }
            }
        } else {
            if ($method == "POST")
            {
                $api->create_post();
            }
            if ($method == "GET")
            {
                $api->get_all_posts();
            }
        }
    }
}

/*
if ($urls[0] == "Тоня")
{
    $result = "";
    foreach ($urls as $url)
    {
        if ($url == "фото")
        {
            echo "<img width='250' src='https://i.imgur.com/Q7ohslA.jpg'>";
        }
        else
        {
            $result .= " Тоня это ".$url;
        }
    }
    echo $result;
}*/
?>
