<?php
header('Content-Type: text/html; charset=utf-8');
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
    
    $lat = [];
    $lng = [];
    $b_name = [];
    $sector = [];
    $loc_name = [];
    $loc = [];
    $menu = [];
    $phone = [];
    $lname = [];
    $sql = "SELECT b_name, sectors, loc_name, loc, menu, phone, lname, lat, lng FROM food";
    $getResults = sqlsrv_query($conn,$sql);
    while ($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      $lat[] = $row['lat'];
      $lng[] = $row['lng'];
      $b_name[] = $row['b_name'];
      $sector[] = $row['sectors'];
      $loc[] = $row['loc'];
      $loc_name[] = $row['loc_name'];
      $menu[] = $row['menu'];
      $phone[] = $row['phone'];
      $lname[] = $row['lname'];
    }

    sqlsrv_close($conn);
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .map_wrap {position:relative;width:50%;height:750px;}
        .title {font-weight:bold;display:block;}
        .hAddr {position:absolute;left:10px;top:10px;border-radius: 2px;background:#fff;background:rgba(255,255,255,0.8);z-index:1;padding:5px;}
        #centerAddr {display:block;margin-top:2px;font-weight: normal;}
        .bAddr {padding:5px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
    </style>
    <title>맛집 검색</title>
</head>
<body>
<div class="map_wrap">
    <div id="map" style="width:100%;height:100%;position:relative;overflow:hidden;"></div>
    <div class="hAddr">
        <span class="title">지도중심기준 행정동 주소정보</span>
        <span id="centerAddr"></span>
    </div>
</div>
<div class="tb">
    <table>
    </table>
</div>

<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=3c564aa9dfa0c70f5fd1a02484baf5e9&libraries=services,clusterer,drawing"></script>
<script>
var mapContainer = document.getElementById('map'), // 지도를 표시할 div 
    mapOption = { 
        center: new kakao.maps.LatLng(35.179783, 129.075003), // 지도의 중심좌표
        level: 9 // 지도의 확대 레벨
    };

// 지도를 생성합니다    
var map = new kakao.maps.Map(mapContainer, mapOption); 

// 주소-좌표 변환 객체를 생성합니다
var geocoder = new kakao.maps.services.Geocoder();

var marker = new kakao.maps.Marker(), // 클릭한 위치를 표시할 마커입니다
    infowindow = new kakao.maps.InfoWindow({zindex:1}); // 클릭한 위치에 대한 주소를 표시할 인포윈도우입니다

// 현재 지도 중심좌표로 주소를 검색해서 지도 좌측 상단에 표시합니다
searchAddrFromCoords(map.getCenter(), displayCenterInfo);

// 지도를 클릭했을 때 클릭 위치 좌표에 대한 주소정보를 표시하도록 이벤트를 등록합니다
kakao.maps.event.addListener(map, 'click', function(mouseEvent) {
    searchDetailAddrFromCoords(mouseEvent.latLng, function(result, status) {
        if (status === kakao.maps.services.Status.OK) {
            var detailAddr = !!result[0].road_address ? '<div>도로명주소 : ' + result[0].road_address.address_name + '</div>' : '';
            detailAddr += '<div>지번 주소 : ' + result[0].address.address_name + '</div>';
            
            var content = '<div class="bAddr">' +
                            '<span class="title">법정동 주소정보</span>' + 
                            detailAddr + 
                        '</div>';

            // 마커를 클릭한 위치에 표시합니다 
            marker.setPosition(mouseEvent.latLng);
            marker.setMap(map);

            // 인포윈도우에 클릭한 위치에 대한 법정동 상세 주소정보를 표시합니다
            infowindow.setContent(content);
            infowindow.open(map, marker);
        }   
    });
});
var mk1 = [];
var mk2 = [];
var mk3 = [];
var mk4 = [];
var mk5 = [];
var mk6 = [];
var mk7 = [];
var mk8 = [];
var mk9 = [];
var mk10 = [];
var mk11 = [];
var mk12 = [];
var mk13 = [];
var mk14 = [];
var mk15 = [];
var mk16 = [];
var mk17 = [];
var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png"; 
// 마커 이미지의 이미지 크기 입니다
var imageSize = new kakao.maps.Size(24, 35); 
      
// 마커 이미지를 생성합니다    
var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize); 

<?
for($i = 0; $i < count($lname); $i++){
    ?>
    if(<? echo $lname[$i]; ?> == "수영구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk1.push(foodmk);
    }
    var foodinfo = new kakao.maps.InfoWindow({content : '<div style="padding:5px;">위치 : <? echo $lname[$i]; ?> <br><p>기간 : <? echo $loc_name[$i]; ?></p></div>', removable : true , zindex : 1});
    kakao.maps.event.addListener(foodmk, 'click', makerClick(map,foodmk,foodinfo));
    <?
}
?>
function makerClick(map, marker, infowindow) {
    return function() {
        infowindow.open(map,marker);
    };
}



// 중심 좌표나 확대 수준이 변경됐을 때 지도 중심 좌표에 대한 주소 정보를 표시하도록 이벤트를 등록합니다
kakao.maps.event.addListener(map, 'idle', function() {
    searchAddrFromCoords(map.getCenter(), displayCenterInfo);
});

function searchAddrFromCoords(coords, callback) {
    // 좌표로 행정동 주소 정보를 요청합니다
    geocoder.coord2RegionCode(coords.getLng(), coords.getLat(), callback);         
}

function searchDetailAddrFromCoords(coords, callback) {
    // 좌표로 법정동 상세 주소 정보를 요청합니다
    geocoder.coord2Address(coords.getLng(), coords.getLat(), callback);
}

// 지도 좌측상단에 지도 중심좌표에 대한 주소정보를 표출하는 함수입니다
function displayCenterInfo(result, status) {
    if (status === kakao.maps.services.Status.OK) {
        var infoDiv = document.getElementById('centerAddr');

        for(var i = 0; i < result.length; i++) {
            // 행정동의 region_type 값은 'H' 이므로
            if (result[i].region_type === 'H') {
                infoDiv.innerHTML = result[i].address_name;
                break;
            }
        }
    }    
}
</script>
</body>
</html>