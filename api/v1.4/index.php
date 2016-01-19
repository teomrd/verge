<?php  session_start();
ini_set('mongo.long_as_object', 1);

include '../../configuration.php'; 
require '../../plugins/slim/2.6.0/Slim/Slim.php';
require('UploadHandler.php');
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
$app->response->headers->set('Access-Control-Allow-Origin', '*');

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$pageSize = isset($_GET['pageSize']) ? (int) $_GET['pageSize'] : 100;
$language = isset($_GET['language']) ? (string) $_GET['language'] : '*';
$offset = ($page-1)*$pageSize;
$pager = " LIMIT ".$offset.",".$pageSize;
$serverFiltering = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
$serverSorting = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null;
$skip = ($page - 1) * $pageSize;

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
$video = isset($_REQUEST['video']) ? $_REQUEST['video'] : null;
$frame = isset($_REQUEST['frame']) ? $_REQUEST['frame'] : null;

/****************************************** Middleware functions  ************************************/
function ImageCreateFromBMP($filename) { 
   if (! $f1 = fopen($filename,"rb")) return FALSE; 
   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
   if ($FILE['file_type'] != 19778) return FALSE; 
   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'. 
                 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'. 
                 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40)); 
   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']); 
   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset']; 
   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8; 
   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']); 
   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4); 
   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4); 
   $BMP['decal'] = 4-(4*$BMP['decal']); 
   if ($BMP['decal'] == 4) $BMP['decal'] = 0; 
   $PALETTE = array(); 
   if ($BMP['colors'] < 16777216) 
   { 
    $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4)); 
   } 
   $IMG = fread($f1,$BMP['size_bitmap']); 
   $VIDE = chr(0); 
   $res = imagecreatetruecolor($BMP['width'],$BMP['height']); 
   $P = 0; 
   $Y = $BMP['height']-1; 
   while ($Y >= 0) 
   { 
    $X=0; 
    while ($X < $BMP['width']) 
    { 
     if ($BMP['bits_per_pixel'] == 24) 
        $COLOR = unpack("V",substr($IMG,$P,3).$VIDE); 
     elseif ($BMP['bits_per_pixel'] == 16) 
     {   
        $COLOR = unpack("n",substr($IMG,$P,2)); 
        $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
     } 
     elseif ($BMP['bits_per_pixel'] == 8) 
     {   
        $COLOR = unpack("n",$VIDE.substr($IMG,$P,1)); 
        $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
     } 
     elseif ($BMP['bits_per_pixel'] == 4) 
     { 
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1)); 
        if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F); 
        $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
     } 
     elseif ($BMP['bits_per_pixel'] == 1) 
     { 
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1)); 
        if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7; 
        elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6; 
        elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5; 
        elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4; 
        elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3; 
        elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2; 
        elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1; 
        elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1); 
        $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
     } 
     else 
        return FALSE; 
     imagesetpixel($res,$X,$Y,$COLOR[1]); 
     $X++; 
     $P += $BMP['bytes_per_pixel']; 
    } 
    $Y--; 
    $P+=$BMP['decal']; 
   } 
   fclose($f1); 
 return $res; 
} 
/****************************************** Middleware functions  ************************************/
/* Hello World GET */
$app->get('/', function () {
	echo 'Hello World, from VERGE API v1.4';
});
/* Hello World GET */
	
/* Crop Image */
$app->post('/crop', function () {
	header("Pragma-directive: no-cache");
	header("Cache-directive: no-cache");
	header("Cache-control: no-cache");
	header("Pragma: no-cache");
	header("Expires: 0");
	    
	$image = $_POST['img'];
	$uploadingDirectory = "../../camera_shots/";
	$directory = $_POST['dir'];
	
	$targ_w = $_POST['w'];
	$targ_h = $_POST['h'];
	
	$src = $directory.$image;
	
	exif_imagetype($src);
	if(exif_imagetype($src)==2){
		header('Content-type: image/jpeg');
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		imagejpeg($dst_r,$uploadingDirectory."q_".$_SERVER['REQUEST_TIME']."_".$image,100);
		
		echo json_encode("q_".$_SERVER['REQUEST_TIME']."_".$image);
	}
	else if(exif_imagetype($src)==6){//.bmp
		header('Content-type: image/bmp');
		$img_r = ImageCreateFromBMP($src);
		$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		imagejpeg($dst_r,$uploadingDirectory."q_".$_SERVER['REQUEST_TIME']."_".$image,100);

		echo json_encode("q_".$_SERVER['REQUEST_TIME']."_".$image);
	}
	else{
		echo json_encode("Image type not supported");
	}
	
});
/* Crop Image */

/**************** RESTful Mongo DATABASE HANDLING ***************/
$app->get('/:database/:table',function ($database,$table) use ($page,$skip,$pageSize,$serverFiltering,$serverSorting,$con) {
    
    $db = $con->$database;
    $collection = $db->$table;
    $query = array();
    $sorter = array();
        
		if($serverSorting!=null){
			$i=0;
			foreach($serverSorting as $sortingField){
				$order = $sortingField['dir'] == "asc" ? 1 : -1;
				$sorter[$i] = array($sortingField['field'] => $order);	
			$i++;
			}
		}
		if($serverFiltering!=null){
			$logic = isset($serverFiltering['logic']) ? (string) $serverFiltering['logic'] : 'or';
			$filters = $serverFiltering['filters'];
			$i = 0;
			foreach ($filters as $filter){
	            switch ($filter['operator']) {
				  case "eq":
				  	$query[$filter['field']] = $filter['value']*1;
				  break;
				  case "string_eq":
				  	$query[$filter['field']] = $filter['value'];
				  break;
				  case "neq":
				  		if(is_numeric($filter['value'])){
					  		$query[$filter['field']] = array('$ne' => $filter['value']*1);
				  		}
				  		else{
					  		$query[$filter['field']] = array('$exists' => true , '$ne' => $filter['value'] );
				  		}
				  break;
				  case "startswith":
				    	$query[$filter['field']] = array('$exists' => true , '$regex' => $filter['value'].'.*' , '$options' => 'i' );
				  break;
				  case "contains":
				    	$query[$filter['field']] = array('$exists' => true ,'$regex' => '.*'.$filter['value'].'.*' , '$options' => 'i' );
				  break;
				  case "doesnotcontain":
						$query[$filter['field']] = array('$exists' => true ,'$ne' => array( '$regex' => '.*'.$filter['value'].'.*' , '$options' => 'i' ));
				  break;
				  case "endswith":
					    $query[$filter['field']] = array('$exists' => true ,'$regex' => '.*'.$filter['value'] , '$options' => 'i');
				  break;
				  case "gt":
				    	$query[$filter['field']] = array('$exists' => true ,'$gt' => $filter['value']*1);
				  break;
				  case "gte":
				    	$query[$filter['field']] = array('$exists' => true ,'$gte' => $filter['value']*1);
				  break;
				  case "lt":
				    	$query[$filter['field']] = array('$exists' => true ,'$lt' => $filter['value']*1);
				  break;
				  case "lte":
				    	$query[$filter['field']] = array('$exists' => true ,'$lte' => $filter['value']*1);
				  break;				  
				  default:
				    $query[$filter['field']] = array('$exists' => true);
				}	
			}   
		}
	$cursor = $collection->find($query)->skip($skip)->limit($pageSize)->sort($sorter);
	$data = iterator_to_array($cursor, false);
	
	$result['query']= $query;
    $result['filter']= $serverFiltering;
    $result['sorter'] = $sorter;
    $result['total'] = $collection->find($query)->count();
    $result['page'] = $page;
    $result['pageSize'] = $pageSize;
    $result['data'] = $data;
    echo json_encode($result, JSON_NUMERIC_CHECK);
});

$app->get('/:database/:table/:id', function ($database,$table,$id) use ($page,$skip,$pageSize,$serverFiltering,$serverSorting,$con) {
    $db = $con->$database;
    $collection = $db->$table;
    
    $query = array('name' => $id*1 );
	$result['data'] = $collection->findOne($query);
    echo json_encode($result);
});
/**************** RESTful Mongo DATABASE HANDLING ***************/

$app->post('/upload', function(){
	//uploads a file to server
	$upload_handler = new UploadHandler();
});

$app->run();
