<?php
// Menambahkan header CORS dan header JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once("koneksi.php");

// Memeriksa koneksi database
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// 1. Mengambil nilai suhumax, suhumint, dan suhurata
$queryStats = "SELECT 
    MAX(suhu) AS suhumax, 
    MIN(suhu) AS suhumin, 
    AVG(suhu) AS suhurata 
FROM tb_cuaca";

$resultStats = $koneksi->query($queryStats);
if (!$resultStats) {
    die("Query gagal: " . $koneksi->error);
}

$stats = $resultStats->fetch_assoc();
$suhumax = isset($stats['suhumax']) ? (int)$stats['suhumax'] : 0;
$suhumin = isset($stats['suhumin']) ? (int)$stats['suhumin'] : 0;
$suhurata = isset($stats['suhurata']) ? round((float)$stats['suhurata'], 2) : 0;

// 2. Mengambil data nilai_suhu_max dengan suhu tertinggi
$queryMaxValues = "SELECT id, suhu, humid, lux, ts FROM tb_cuaca WHERE suhu = $suhumax";
$resultMaxValues = $koneksi->query($queryMaxValues);
if (!$resultMaxValues) {
    die("Query gagal: " . $koneksi->error);
}

$nilaiSuhuMax = [];
while ($row = $resultMaxValues->fetch_assoc()) {
    $nilaiSuhuMax[] = $row;
}

// 3. Mengambil data mount_years dalam format bulan-tahun dari ts
$queryMountYears = "SELECT DISTINCT DATE_FORMAT(ts, '%c-%Y') AS mount_years FROM tb_cuaca WHERE suhu = $suhumax";
$resultMountYears = $koneksi->query($queryMountYears);
if (!$resultMountYears) {
    die("Query gagal: " . $koneksi->error);
}

$mountYearMax = [];
while ($row = $resultMountYears->fetch_assoc()) {
    $mountYearMax[] = ["mount_years" => $row['mount_years']];
}

// 4. Menggabungkan semua data ke dalam array respons
$response = [
    "suhumax" => $suhumax,
    "suhumin" => $suhumin,
    "suhurata" => $suhurata,
    "nilai_suhu_max_humid_max" => $nilaiSuhuMax,
    "mount_year_max" => $mountYearMax
];

// Menyusun dan mengembalikan data respons dalam JSON
echo json_encode($response, JSON_PRETTY_PRINT);

// Menutup koneksi
$koneksi->close();
?>
