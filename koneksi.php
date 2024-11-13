<?php
    $koneksi = new mysqli("localhost", "root", "", "tb_cuaca");
    
    if($koneksi -> connect_error){
        echo "koneksi gagal";
    }

?>