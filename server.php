<?php
session_start();

$username = "";
$email = "";
$password = "";
$errors = array();

$db = mysqli_connect('localhost', 'root', '', 'breast');

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
