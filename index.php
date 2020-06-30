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
        .map_wrap {position:relative;width:100%;height:800px;}
        .title {font-weight:bold;display:block;}
        .hAddr {position:absolute;left:10px;top:10px;border-radius: 2px;background:#fff;background:rgba(255,255,255,0.8);z-index:1;padding:5px;}
        #centerAddr {display:block;margin-top:2px;font-weight: normal;}
        .bAddr {padding:5px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
        #foodTbody {padding: 3px; text-align: center;}
        #foodThead {padding: 3px; text-align: center;}
        #foodTable{border-collapse: collapse;width: 100%;}
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
<p>
    <button onclick="Mkall()">처음화면으로</button>
    <button onclick="Mk1()">수영구 맛집</button>
    <button onclick="Mk2()">강서구 맛집</button>
    <button onclick="Mk3()">금정구 맛집</button>
    <button onclick="Mk4()">기장군 맛집</button>
    <button onclick="Mk5()">남구 맛집</button>
    <button onclick="Mk6()">동구 맛집</button>
    <button onclick="Mk7()">동래구 맛집</button>
    <button onclick="Mk8()">부산진구 맛집</button>
    <button onclick="Mk9()">북구 맛집</button>
    <button onclick="Mk10()">사상구 맛집</button>
    <button onclick="Mk11()">사하구 맛집</button>
    <button onclick="Mk12()">서구 맛집</button>
    <button onclick="Mk13()">연제구 맛집</button>
    <button onclick="Mk14()">영도구 맛집</button>
    <button onclick="Mk15()">중구 맛집</button>
    <button onclick="Mk16()">해운대구 맛집</button>
</p> 
<table border="1" id="foodTable">
    <thead id="foodThead">
        <tr>
            <!-- 0 -->
            <th>가게 이름</th>
            <th>음식종류</th>
            <th>전화번호</th>
            <th>가게 위치</th>
        </tr>
    </thead>
    <tbody border="1" id="foodTbody">
    </tbody>
</table>


<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=3c564aa9dfa0c70f5fd1a02484baf5e9&libraries=services,clusterer,drawing"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs=" crossorigin="anonymous"></script>
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
var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png"; 
// 마커 이미지의 이미지 크기 입니다
var imageSize = new kakao.maps.Size(24, 35); 
      
// 마커 이미지를 생성합니다    
var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize); 

<?
for($i = 0; $i < count($lname); $i++){
    ?>
    if("<? echo $lname[$i]; ?>" == "수영구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk1.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "강서구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk2.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "금정구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk3.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "기장군"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk4.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "남구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk5.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "동구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk6.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "동래구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk7.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "부산진구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk8.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "북구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk9.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "사상구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk10.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "사하구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk11.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "서구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk12.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "연제구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk13.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "영도구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk14.push(foodmk);
    }
    else if("<? echo $lname[$i]; ?>" == "중구"){
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk15.push(foodmk);
    }
    else {
        var foodmk = new kakao.maps.Marker({
            map: map,
            position: new kakao.maps.LatLng(<? echo $lat[$i]; ?>,<? echo $lng[$i]; ?>),
            image: markerImage
        });
        mk16.push(foodmk);
    }
    
    var foodinfo = new kakao.maps.InfoWindow({content : '<div style="padding:5px;">위치 : <? echo $lname[$i]; ?> <br><p>가게 : <? echo $loc_name[$i]; ?></p></div>', removable : true , zindex : 1});
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
<script>
function setmk1(map) {
  for(var j = 0; j < mk1.length; j++){
    mk1[j].setMap(map);
  }
}
function setmk2(map) {
  for(var j = 0; j < mk2.length; j++){
    mk2[j].setMap(map);
  }
}
function setmk3(map) {
  for(var j = 0; j < mk3.length; j++){
    mk3[j].setMap(map);
  }
}
function setmk4(map) {
  for(var j = 0; j < mk4.length; j++){
    mk4[j].setMap(map);
  }
}
function setmk5(map) {
  for(var j = 0; j < mk5.length; j++){
    mk5[j].setMap(map);
  }
}
function setmk6(map) {
  for(var j = 0; j < mk6.length; j++){
    mk6[j].setMap(map);
  }
}
function setmk7(map) {
  for(var j = 0; j < mk7.length; j++){
    mk7[j].setMap(map);
  }
}
function setmk8(map) {
  for(var j = 0; j < mk8.length; j++){
    mk8[j].setMap(map);
  }
}
function setmk9(map) {
  for(var j = 0; j < mk9.length; j++){
    mk9[j].setMap(map);
  }
}
function setmk10(map) {
  for(var j = 0; j < mk10.length; j++){
    mk10[j].setMap(map);
  }
}
function setmk11(map) {
  for(var j = 0; j < mk11.length; j++){
    mk11[j].setMap(map);
  }
}
function setmk12(map) {
  for(var j = 0; j < mk12.length; j++){
    mk12[j].setMap(map);
  }
}
function setmk13(map) {
  for(var j = 0; j < mk13.length; j++){
    mk13[j].setMap(map);
  }
}
function setmk14(map) {
  for(var j = 0; j < mk14.length; j++){
    mk14[j].setMap(map);
  }
}
function setmk15(map) {
  for(var j = 0; j < mk15.length; j++){
    mk15[j].setMap(map);
  }
}
function setmk16(map) {
  for(var j = 0; j < mk16.length; j++){
    mk16[j].setMap(map);
  }
}
function Mkall() {
  var html = '';
  $("#foodTbody").empty();
  $("#foodTbody").append(html);
  map.setCenter(new kakao.maps.LatLng(35.179783, 129.075003));
  map.setLevel(9);
  setmk1(map);
  setmk2(map);
  setmk3(map);
  setmk4(map);
  setmk5(map);
  setmk6(map);
  setmk7(map);
  setmk8(map);
  setmk9(map);
  setmk10(map);
  setmk11(map);
  setmk12(map);
  setmk13(map);
  setmk14(map);
  setmk15(map);
  setmk16(map);
}
function Mk1() {
  var html = '';
<?for($i = 0; $i < count($lname);$i++){?>
      if("<? echo $lname[$i];?>" == "수영구") {
          html += '<tr>';
          html += '<td><? echo $loc_name[$i];?></td>';
          html += '<td><? echo $b_name[$i];?></td>';
          html += '<td><? echo $phone[$i];?></td>';
          html += '<td><? echo $loc[$i];?></td>';
          html += '</tr>';
      }
<? } ?>
  $("#foodTbody").empty();
  $("#foodTbody").append(html);

  setmk1(map);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk2() {
  var html = '';
<?for($i = 0; $i < count($lname);$i++){?>
    if("<? echo $lname[$i];?>" == "강서구") {
      html += '<tr>';
      html += '<td><? echo $loc_name[$i];?></td>';
      html += '<td><? echo $b_name[$i];?></td>';
      html += '<td><? echo $phone[$i];?></td>';
      html += '<td><? echo $loc[$i];?></td>';
      html += '</tr>';
    }
<?  } ?>
  $("#foodTbody").empty();
  $("#foodTbody").append(html);
  setmk1(null);
  setmk2(map);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk3() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
      if("<? echo $lname[$i];?>" == "금정구") {
        html += '<tr>';
        html += '<td><? echo $loc_name[$i];?></td>';
        html += '<td><? echo $b_name[$i];?></td>';
        html += '<td><? echo $phone[$i];?></td>';
        html += '<td><? echo $loc[$i];?></td>';
        html += '</tr>';
      }
<?  } ?>
  $("#foodTbody").empty();
  $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(map);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk4() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "기장군") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(map);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk5() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "남구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(map);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk6() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "동구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(map);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk7() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "동래구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(map);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk8() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "부산진구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(map);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk9() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "북구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(map);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk10() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "사상구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(map);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk11() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "사하구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(map);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk12() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "서구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(map);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk13() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "연제구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(map);
  setmk14(null);
  setmk15(null);
  setmk16(null);
}
function Mk14() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "영도구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(map);
  setmk15(null);
  setmk16(null);
}
function Mk15() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "중구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(map);
  setmk16(null);
}
function Mk16() {
    var html = '';
  <?for($i = 0; $i < count($lname);$i++){?>
        if("<? echo $lname[$i];?>" == "해운대구") {
            html += '<tr>';
            html += '<td><? echo $loc_name[$i];?></td>';
            html += '<td><? echo $b_name[$i];?></td>';
            html += '<td><? echo $phone[$i];?></td>';
            html += '<td><? echo $loc[$i];?></td>';
            html += '</tr>';
        }
<?  } ?>
    $("#foodTbody").empty();
    $("#foodTbody").append(html);
  setmk1(null);
  setmk2(null);
  setmk3(null);
  setmk4(null);
  setmk5(null);
  setmk6(null);
  setmk7(null);
  setmk8(null);
  setmk9(null);
  setmk10(null);
  setmk11(null);
  setmk12(null);
  setmk13(null);
  setmk14(null);
  setmk15(null);
  setmk16(map);
}
</script>
</body>
</html>