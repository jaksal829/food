<?
// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "lee", "pwd" => "app2020!", "Database" => "lee", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:jaeran.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);

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
<p> 테 </p>
</body>
</html>
