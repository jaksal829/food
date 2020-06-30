<?php
  header('Content-Type: text/html; charset=utf-8');
?>
<?php
// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "gunan", "pwd" => "app2020!", "Database" => "foodcloud", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:foodcloud.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);
    if (!$conn) {
        echo "conn: false";
    }
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $row = 1;
    $handle = fopen("myfile.csv", "r+");
    //$sql = "INSERT INTO latlng VALUES ('".$data[0]."','".$data[1]."');";
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $num = count($data);

        $row++;

        $sql = "INSERT INTO food VALUES (";

        for ($c=0; $c < $num; $c++) {
            $sql .= "'" . $data[$c] . "'";
            if($c+1 !== $num){
                $sql .= ", ";
            }
        }
        $sql .= ");";
        $getResults = sqlsrv_query($conn,$sql);
        //echo "$sql<br />";
    }
    fclose($handle);
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .map {
            width :100%;
            height : 50%;
        }
    </style>
    <title>맛집 검색</title>
</head>
<body>
<div id="map" class="map"></div>

<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=3c564aa9dfa0c70f5fd1a02484baf5e9"></script>
<script>
var mapContainer = document.getElementById('map'), // 지도를 표시할 div 
    mapOption = { 
        center: new kakao.maps.LatLng(35.179783, 129.075003), // 지도의 중심좌표
        level: 9 // 지도의 확대 레벨
    };

</script>
</body>
</html>
