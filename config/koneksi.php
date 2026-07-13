<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "topup_game"
);

if(!$conn){
    die("Koneksi gagal");
}
?>