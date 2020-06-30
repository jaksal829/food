<?php
  header('Content-Type: text/html; charset=utf-8');
?>
<?php
// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "gunan", "pwd" => "app2020!", "Database" => "app", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:appcen.database.windows.net,1433";
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
    <title>맛집 검색</title>
</head>
<body>
<p> 테스트 </p>
</body>
</html>
