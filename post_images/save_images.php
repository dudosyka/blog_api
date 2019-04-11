<?php
/**
 * Created by PhpStorm.
 * User: sasha
 * Date: 09.04.2019
 * Time: 22:43
 */
if (isset($_FILES['img']))
{
    $img          = $_FILES['img']['name'];
    $img_tmp_name = $_FILES['img']['tmp_name'];
    $img_type     = explode(".", $img)[count(explode(".", $img)) - 1];
    $img          = md5($img).".".$img_type;
    if (move_uploaded_file($img_tmp_name, $img))
    {
        echo "success uploaded";
    }
}