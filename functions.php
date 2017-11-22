<?php
include("top.php");

////////////////////////////////////////////////////////////////////////////////////////////////////

function getPlaylist($arr_detail){
	global $db;
	$book = json_decode($arr_detail);
	$book_id 		= $book->book_id;
	$chapter_id		= $book->chapter_id;
	$volunteer_id 	= $book->volunteer_id;
	// echo "vo".$volunteer_id;
	// echo "book".$book_id;
	// echo "chap".$chapter_id;

	$sql_playlist = "SELECT A.playlist_link 
				FROM CHAPTER A JOIN CREATED_BOOK B ON A.book_id = B.book_id
				WHERE B.volunteer_id = '".$volunteer_id."' 
				AND A.book_id = ".$book_id."
				AND A.chapter_id = ".$chapter_id." ";
	
	$q_playlist = $db->query($sql_playlist);

	$f_playlist = $q_playlist->fetch_array();
	$playlist_link = $f_playlist['playlist_link'];

	echo json_encode($playlist_link);
}
///////////////////////////////////////////////////////////////////////////////////////
function getChapterOfOwner($book_id){
	global $db;
	$arr_result  =	array();
	$arr_chap = array();
	$sql_chap = "SELECT chapter_id FROM CHAPTER WHERE book_id =".$book_id." ORDER BY chapter_id";
	$q_chap = $db->query($sql_chap);

	$q_maxchap = $db->query("SELECT A.no_of_chapter
							FROM BOOK A
							JOIN CREATED_BOOK B ON A.ISBN = B.ISBN
							WHERE B.book_id = ".$book_id." ");
	$f_maxchap = $q_maxchap->fetch_array();
	$maxchap = $f_maxchap['no_of_chapter'];

	while($f_chap = $q_chap->fetch_array()){
		array_push($arr_chap, $f_chap['chapter_id']);
	}

	for($i=1 ; $i<=$maxchap; $i++){
		if(!in_array($i, $arr_chap)){
			
			array_push($arr_result, $i);
		}
	}

	echo json_encode($arr_result);

}
///////////////////////////////////////////////////////////////////////////////////////
function getJoinChapter($arr_data){ //functions
	global $db;
	$arr_result = array();
	$join = json_decode($arr_data);
	$book_id			= $join->book_id;
	$requester_id 		= $join->requester_id;

	$arr_readedchap = array();

	$sql_nochap = "SELECT no_of_chapter 
					FROM BOOK A
					JOIN CREATED_BOOK B ON A.ISBN = B.ISBN
					WHERE B.book_id = ".$book_id."  ";
	$q_nochap = $db->query($sql_nochap);
	$f_nochap = $q_nochap->fetch_array();
	$no_of_chapter = $f_nochap['no_of_chapter'];

	$sql_chapter = "SELECT distinct chapter_id FROM CHAPTER WHERE book_id= ".$book_id." 
					UNION SELECT distinct chapter_id from JOIN_REQUEST WHERE book_id = ".$book_id." AND requester_id = '".$requester_id."' ";
	$q_chapter = $db->query($sql_chapter);

	while($f_chapter = $q_chapter->fetch_array()){
		array_push($arr_readedchap, $f_chapter['chapter_id']);
	}


	for($i=1 ; $i<=$no_of_chapter; $i++){
		if(!in_array($i, $arr_readedchap)){
			array_push($arr_result, $i);
		}
	}
	echo json_encode($arr_result);
}
////////////////////////////////////////////////////////////////////////////////////////////////////

function getCategories(){ //getCategories(); //use to insert book
	global $db;
	$arr_category = array();
	$arr_result = array();
	$q_cat = $db->query("SELECT * FROM CATEGORY");
	while($f_cat = $q_cat->fetch_array()){
		$arr_category['category_id'] = $f_cat['category_id'];
		$arr_category['category_name'] = $f_cat['category_name'];
		
		array_push($arr_result, $arr_category);

	}
	echo  json_encode($arr_result);
}

////////////////////////////////////////////////////////////////////////////////////////////////////
function searchReadedBook($condi){
	global $db;
	$arr_result = array();
	$arr_books = array();


	$sql_search = "SELECT A.*, B.category_name , D.*,C.book_id
				FROM BOOK A
				JOIN CATEGORY B ON A.category_id = B.category_id 
				JOIN CREATED_BOOK C ON A.ISBN = C.ISBN
				JOIN VOLUNTEER D ON C.volunteer_id = D.volunteer_id
				WHERE A.book_name LIKE '%".$condi."%'  
				OR A.ISBN LIKE '%".$condi."%'
				OR A.publish LIKE '%".$condi."%'
				OR A.author LIKE '%".$condi."%'
				OR A.translator LIKE '%".$condi."%' 
				OR D.name LIKE '%".$condi."%' ";

	$q_search = $db->query($sql_search);

	while($f_search = $q_search->fetch_array()){
		$arr_books['ISBN'] 			= $f_search['ISBN'];
		$arr_books['book_name'] 	= $f_search['book_name'];
		$arr_books['category_id'] 	= $f_search['category_name'];
		$arr_books['no_of_chapter'] = $f_search['no_of_chapter'];
		$arr_books['publish'] 		= $f_search['publish'];
		$arr_books['no_of_page'] 	= $f_search['no_of_page'];
		$arr_books['author'] 		= $f_search['author'];
		$arr_books['translator'] 	= $f_search['translator'];
		$arr_books['reading_times'] = $f_search['reading_times'];
		$arr_books['book_id']		= $f_search['book_id'];
		$arr_books['volunteer_id']	= $f_search['volunteer_id'];
		$arr_books['name']			= $f_search['name'];
		$arr_books['image']			= $f_search['image'];

		array_push($arr_result, $arr_books);
	}
	//print_r($arr_result);
	echo json_encode($arr_result);
}

////////////////////////////////////////////////////////////////////////////////////////////////////
/**

*/
function insertVolunteer($json){ 
	//insertUser('6','name','email','address','2017-08-15','sex','5.youtube.com','pic5.png');
	global $db;
	$volunteer = json_decode($json);

	$volunteer_id 	= $volunteer->volunteer_id;
	$name 			= $volunteer->name;
	$email 			= $volunteer->email;
	$address		= $volunteer->address;
	$birthday		= $volunteer->birthday;
	$sex			= $volunteer->sex;
	$channel_link	= $volunteer->channel_link;
	$avatar			= $volunteer->avatar;


	$sql_checkV = "SELECT volunteer_id FROM VOLUNTEER WHERE volunteer_id = '".$volunteer_id."'";
	$q_checkV = $db->query($sql_checkV);
	$num_row = 0 ;
	$num_row = mysqli_num_rows($q_checkV);

	if($num_row==0){
		$sql_int = "INSERT INTO VOLUNTEER (volunteer_id,name,email,address,birthday,sex, channel_link, avatar)
					VALUES ('".$volunteer_id."','".$name."','".$email."','".$address."','".$birthday."','".$sex."','".$channel_link."','".$avatar."')";
		$q_int = $db->query($sql_int);
	}else{
		$report = "already have volunteer";
		echo json_encode($report);
	}

	if($q_int){
		echo json_encode(" INSERTED SUCCESSFULLY");
	}else{
		echo json_last_error(); // 4 (JSON_ERROR_SYNTAX)
		echo json_last_error_msg(); // unexpected character 
		error_reporting(E_ALL);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////
function getDetailInsert($isbn){
	global $db;
	
	$arr_book = array();

	$sql_search ="SELECT * FROM BOOK WHERE isbn = '".$isbn."'";
	$q_search = $db->query($sql_search);
	$f_search = $q_search->fetch_array();
	$num_row = mysqli_num_rows($q_search);

	if($num_row>0){
		$arr_book['result']			= 0;
		$arr_book['book_name']		= $f_search['book_name'];
		$arr_book['category_id']	= $f_search['category_id'];
		$arr_book['no_of_chapter']	= $f_search['no_of_chapter'];
		$arr_book['publish']		= $f_search['publish'];
		$arr_book['no_of_page']		= $f_search['no_of_page'];
		$arr_book['author']			= $f_search['author'];
		$arr_book['translator']		= $f_search['translator'];
		$arr_book['reading_times']	= $f_search['reading_times'];
		$arr_book['image']			= $f_search['image'];

		echo json_encode($arr_book);
	}else{
		// $arr_book['result']			= 1;	
		echo json_encode(1);	
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////
function insertBook($json){
//insertBook('0001',"3","gift_test3", 1, 3, "พิมพ์ที่", 33, "นักเขียน", "แปล");
	global $db;

	$book 			= json_decode($json);
	$volunteer_id 	= $book->volunteer_id;
	$isbn 			= $book->isbn; 
	$book_name 		= $book->book_name;
	$category_id 	= $book->category_id;
	$no_of_chapter 	= $book->no_of_chapter;
	$publish 		= $book->publish;
	$no_of_page 	= $book->no_of_page;
	$author 		= $book->author;
	$translator 	= $book->translator;
	$image			= $book->image;

	$sql_check = "SELECT ISBN FROM BOOK WHERE ISBN = '".$isbn."'";
	$q_check = $db->query($sql_check);
	$num_row = 0 ;
	$num_row = mysqli_num_rows($q_check);

	if($num_row>=1){
		$sql_readtimes = "UPDATE BOOK SET reading_times = reading_times+1 WHERE ISBN = '".$isbn."'";
		$q_readtimes = $db->query($sql_readtimes);		
	}else{
		$sql_int = "INSERT INTO BOOK (ISBN, book_name, category_id, no_of_chapter, publish, no_of_page, author, translator,reading_times, image)
					VALUES ('".$isbn."','".$book_name."','".$category_id."','".$no_of_chapter."','".$publish."','".$no_of_page."','".$author."','".$translator."','1','".$image."')";
		$q_int = $db->query($sql_int);

	}

	//CHECK THE ISBN IN TABLE: CREATED_BOOK
	$sql_checkBook 	= "SELECT ISBN FROM CREATED_BOOK WHERE ISBN ='".$isbn."' AND volunteer_id = '".$volunteer_id."'";
	$q_checkBook	= $db->query($sql_checkBook);
	$row_checkBook 	= 0 ;
	$row_checkBook 	= mysqli_num_rows($q_checkBook);
	//
	if($row_checkBook>=1){
		echo json_encode(1);
	}else{
		$sql_createdBook = "INSERT INTO CREATED_BOOK (volunteer_id,ISBN) VALUES ('".$volunteer_id."','".$isbn."')";
		$q_createdBook 	= $db->query($sql_createdBook);

	}

	if($q_createdBook){
		$sql_bookid = "SELECT book_id FROM CREATED_BOOK WHERE volunteer_id ='".$volunteer_id."' AND isbn = '".$isbn."' ";
		$q_bookid = $db->query($sql_bookid);
		$f_bookid = $q_bookid->fetch_array();
		echo json_encode($f_bookid['book_id']);
	}else{
		//echo $sql_createdBook.'</br></br>';
		echo mysqli_error($db);
	}

	
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insertChapter($arr_chap){ //insertChapter($arr_book);
	global $db;
	$chapter 		= json_decode($arr_chap);
	$book_id		= $chapter->book_id;
	$playlist_link	= $chapter->playlist_link;
	$chapter_id 	= $chapter->chapter_id;
	$volunteer_id	= $chapter->volunteer_id;
	// $sql_vol = "SELECT volunteer_id FROM CREATED_BOOK WHERE book_id = ".$book_id." ";
	// $q_vol = $db->query($sql_vol);
	// $f_vol = $q_vol->fetch_array();
	// $volunteer_id = $f_vol['volunteer_id'];

	// $sql_max = "SELECT MAX()chapter_id AS MAX_ID FROM CHAPTER WHERE book_id = ".$book_id."";
	// $q_max = $db->query($sql_max);
	// $f_max = $q_max->fetch_array();
	// $new_chapterid = $f_max['MAX_ID'] + 1;

	$sql_insert = "INSERT INTO CHAPTER( book_id, chapter_id, volunteer_id, playlist_link, complete, listening_times) VALUES(".$book_id.", ".$chapter_id.", '".$volunteer_id."' , '".$playlist_link."', 0, 0)";
	$q_insert = $db->query($sql_insert);

	// $result ="";
	if($q_insert){
		// $result .= "insert into chapter successfully";
		echo 0;
	}else{
		echo 9999;
	}

	// echo json_encode($result);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function insertParagraph($arr_para){
	global $db;
	$paragraph = json_decode($arr_para);
	$book_id 		= $paragraph->book_id;
	$chapter_id 	= $paragraph->chapter_id;
	$paragraph_order= $paragraph->paragraph_order;
	$video_link		= $paragraph->video_link;
	$video_time		= $paragraph->video_time;

	$sql_max = "SELECT MAX(paragraph_id) AS MAX_ID FROM PARAGRAPH WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." ";
	$q_max = $db->query($sql_max);
	$f_max = $q_max->fetch_array();
	$new_paraid = $f_max['MAX_ID']+1;

	$sql_check = "SELECT paragraph_order 
				FROM PARAGRAPH 
				WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." AND paragraph_order = ".$paragraph_order." ";
	$q_check = $db->query($sql_check);
	$num_row = 0 ;
	$num_row = mysqli_num_rows($q_check);
	
	if($num_row>0){
		$isused = 0;
	}else{
		$isused = 1;
	}

	$sql_insert = "INSERT PARAGRAPH(paragraph_id, book_id, chapter_id, paragraph_order,video_link, video_time, isused)
				VALUES(".$new_paraid.", ".$book_id.", ".$chapter_id.", ".$paragraph_order.", '".$video_link."', '".$video_time."', ".$isused.")";
	$q_insert = $db->query($sql_insert);

	if($q_insert){
		echo json_encode("insert successfully");
	}else{
		echo mysqli_error($db);
	}
}


//////////////////////////////////////////////////////////////////////////////////
function getUserInfo($volunteer_id){ //getUserInfo(2);
	global $db;
	$arr_userInfo = array();
	
	// if($volunteer_id!=''){
	// 	$wh = " WHERE volunteer_id = '".$volunteer_id."'";
	// }else{
	// 	$wh="";
	// }

	// $sql_user = "SELECT * FROM VOLUNTEER ".$wh."";

	if($volunteer_id != '') {
		
		$sql_user = "SELECT * FROM VOLUNTEER WHERE volunteer_id = '".$volunteer_id."'";

		$q_user = $db->query($sql_user);

		$sql_numBooks = "SELECT COUNT(book_id) AS number_of_books FROM CREATED_BOOK WHERE volunteer_id = '".$volunteer_id."'";
		$q_numBooks = $db->query($sql_numBooks);

		$f_user = $q_user->fetch_array();
		$f_numBooks = $q_numBooks->fetch_array();

		if(count($f_user) > 0) {

			$arr_userInfo['name'] 			= $f_user['name'];
			$arr_userInfo['email'] 			= $f_user['email'];
			$arr_userInfo['address'] 		= $f_user['address'];
			$arr_userInfo['birthday'] 		= $f_user['birthday'];
			$arr_userInfo['sex'] 			= $f_user['sex'];
			$arr_userInfo['channel_link']	= $f_user['channel_link'];
			$arr_userInfo['avatar']			= $f_user['avatar'];
			$arr_userInfo['create_at'] 		= $f_user['create_at'];
			$arr_userInfo['number_of_books'] = $f_numBooks['number_of_books'];

			$arr_info = array();
			$arr_info['volunteer_id']	=	$volunteer_id;
			$arr_info['type']			=	"returntime";
			$arr_info = json_encode($arr_info);
			
			$user = json_decode(getReadtime($arr_info));
			$number = $user->video_time;
			if($number==null){
				$number = "00:00:00";
			}
			$ranking = $user->ranking;
			if($ranking==null)$ranking = "";

			// $hours = str_pad(floor($number / 3600), 2, '0', STR_PAD_LEFT);
			// $minutes = str_pad(floor( ($number / 60) % 60), 2, '0', STR_PAD_LEFT);
			// $seconds = str_pad($number % 60, 2, '0', STR_PAD_LEFT);
			// $result_time = $hours.":".$minutes.":".$seconds;

			$arr_userInfo['ranking']		= $ranking;
			$arr_userInfo['video_time']		= $number;	

			echo json_encode($arr_userInfo); 
		} else {
			echo 9999;
		}

	}else {
		echo 9999;
	}

	
	//print_r($arr_userInfo);
	
}
//////////////////////////////////////////////////////////////////////////////////

function getReadtime($arr_info){ //getReadtime(); ระยะเวลาทั้งหมดที่user อ่าน //$volunteer_id is a volunteer_id for find the range of readingtime
	//getReadtime(3);
	global $db;

	$time = json_decode($arr_info);
	$volunteer_id 	= $time->volunteer_id;
	$type  			= $time->type;
	
	if($volunteer_id==''){
		$wh = " LIMIT 20";
	}else{
		$wh="";
	}
  	$sql_readtime = "SELECT CT.volunteer_id, VT.avatar,SUM(PG.video_time) AS Time
					FROM CHAPTER CT
					JOIN PARAGRAPH PG ON PG.chapter_id = CT.chapter_id AND CT.book_id = PG.book_id
					JOIN VOLUNTEER VT ON CT.volunteer_id = VT.volunteer_id
					GROUP BY CT.volunteer_id
					ORDER BY Time DESC ".$wh." ";

    $q_readtime = $db->query($sql_readtime);
    $arr_readtime = array();
    $result = array();
    $result_id = array();

    while($f_readtime = $q_readtime->fetch_array()){

    	$arr_readtime['volunteer_id'] = $f_readtime['volunteer_id'];
    	$arr_readtime['avatar'] = $f_readtime['avatar'];

    	$number =  $f_readtime['Time'];
    	
    	if($type=="returntime"){
    		$hours = str_pad(floor( $number / 3600), 2, '0', STR_PAD_LEFT);
			$minutes = str_pad(floor( ($number / 60) % 60), 2, '0', STR_PAD_LEFT);
			$seconds = str_pad($number % 60, 2, '0', STR_PAD_LEFT);
			$result_time = $hours.":".$minutes.":".$seconds;
			$arr_readtime['time'] = $result_time;
    	}else{
    		@$minutes = floor($number / 60);
    		$arr_readtime['time'] = $minutes;
    	}

    	array_push($result, $arr_readtime);
    }

    if($volunteer_id != ''){ 
    	$count_rank=1;
    	foreach ($result as $key => $value) {
    		if($value['volunteer_id']==$volunteer_id){
    			$result_id['avatar'] 		= $value['avatar'];
    			$result_id['ranking'] 		= $count_rank;
    			$result_id['video_time'] 	= $value['time'];
    		}
    		$count_rank++;
    	}
    	if($type=='return'||$type=="returntime"){
    		return json_encode($result_id); 
    	}else{
    		if(count($result_id)!=0){
    			echo json_encode($result_id);
    		}else{
    			$sql_undeUser = "SELECT avatar FROM VOLUNTEER WHERE volunteer_id = '".$volunteer_id."' ";
    			$q_undeUser = $db->query($sql_undeUser);
    			$f_undeUser = $q_undeUser->fetch_array();
    			$result_id['avatar'] 		= $f_undeUser['avatar'];
    			$result_id['video_time']	= 0;

    			echo json_encode($result_id);
    			//echo 0;
    		}
    	}
    	 
    	
    }else{
		echo json_encode($result); 
    }  

}


//////////////////////////////////////////////////////////////////////////////////
function getReadedBook($volunteer_id){ 
//getReadedBook('0001');
	global $db;
	$arr_bookDetail = array();
	$arr_result = array();

	$sql_books = "SELECT A.book_id, B.*, C.category_name, SUM(D.listening_times) AS listening_times
				FROM CREATED_BOOK A 
				JOIN BOOK B ON B.ISBN = A.ISBN
				JOIN CATEGORY C ON C.category_id = B.category_id
				LEFT JOIN CHAPTER D ON D.book_id = A.book_id
				WHERE A.volunteer_id = '".$volunteer_id."'
				GROUP BY book_id";

	$q_books = $db->query($sql_books);
	while($f_books = $q_books->fetch_array()){
		$bookarr = '{
		    "proc":"calculate",
		    "book_id": "'.$f_books['book_id'].'"
		}';
		$arr_bookDetail['book_id']			= $f_books['book_id'];
		$arr_bookDetail['ISBN'] 			= $f_books['ISBN'];
		$arr_bookDetail['book_name'] 		= $f_books['book_name'];
		$arr_bookDetail['category'] 		= $f_books['category_name'];
		$arr_bookDetail['no_of_chapter'] 	= $f_books['no_of_chapter'];
		$arr_bookDetail['publish'] 			= $f_books['publish'];
		$arr_bookDetail['no_of_page'] 		= $f_books['no_of_page'];
		$arr_bookDetail['author'] 			= $f_books['author'];
		$arr_bookDetail['translator'] 		= $f_books['translator'];
		$arr_bookDetail['reading_times']	= $f_books['reading_times'];
		$arr_bookDetail['listening_times']	= ($f_books['listening_times']!=null? $f_books['listening_times'] : "0");
		$arr_bookDetail['image']			= $f_books['image'];		
		$arr_bookDetail['percent_finish']	= calPercent($f_books['book_id']);
		$arr_bookDetail['rating']			= getRating($bookarr);

		array_push($arr_result, $arr_bookDetail);

	}
	echo json_encode($arr_result);
	//print_r($arr_result);
}


////////////////////////////////////////////////////////////////////////////////////////////////////

function calPercent($book_id){ //calPercent(2);
	global $db;

	$sql_cal = "SELECT no_of_chapter ,COUNT(C.book_id) AS num_complete FROM BOOK A 
				JOIN CREATED_BOOK B ON A.ISBN = B.ISBN 
				JOIN CHAPTER C ON C.book_id = B.book_id
				WHERE B.book_id = '".$book_id."' AND C.complete =1";

	$q_cal = $db->query($sql_cal);
	$f_cal =$q_cal->fetch_array();

	@$per_complete = ($f_cal['num_complete'] * 100 ) / $f_cal['no_of_chapter'];
	return $percent = number_format($per_complete).'%';
	//echo json_encode(number_format($per_complete).'%');
}
///////////////////////////////////////////////////////////////////////////////////////////////////

function getRating($book_id){
	global $db;
	$book = json_decode($book_id);
	$proc = ($book->proc!=''? $book->proc: "");
	$book_id = $book->book_id;

	$arr_rate = array();
	$arr_result = array();

	$sql_rate = "SELECT B.name, A.rating, A.comment
				FROM BLIND_FEEDBACK A
				JOIN BLIND B ON A.blind_id = B.blind_id
				WHERE book_id = ".$book_id."";
	$q_rate = $db->query($sql_rate);

	while($f_rate = $q_rate->fetch_array()){
		$arr_rate['name'] 		= $f_rate['name'];
		$arr_rate['rating']		=   $f_rate['rating'];
		$arr_rate['comment'] 	= $f_rate['comment'];

		array_push($arr_result, $arr_rate);
	}

	if($proc=="calculate"){
		$sum_rate = 0;
		$count=0;
		foreach ($arr_result as $key => $value) {
			$sum_rating += $value['rating'];
			$count++;
		}	
		@$avg_rating = number_format($sum_rating/$count,1);
		return ($avg_rating!=nan? $avg_rating:number_format(0,1));

	}else{
		echo json_encode($arr_result);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////
function getJoinedBook($volunteer_id){ //0003
	global $db;
	$arr_bookDetail = array();
	$arr_result = array();

	$sql_books = "SELECT A.book_id, B.isbn, C.*,E.category_name,SUM(D.listening_times) AS listening_times
				FROM JOIN_REQUEST A 
				JOIN CREATED_BOOK B ON A.book_id = B.book_id
				JOIN BOOK C ON B.ISBN = C.isbn
				JOIN CATEGORY E ON E.category_id = C.category_id
				LEFT JOIN CHAPTER D ON D.book_id = A.book_id
				WHERE requester_id ='".$volunteer_id."'AND is_accept=2 
				GROUP BY book_id ";
	$q_books = $db->query($sql_books);
	while($f_books = $q_books->fetch_array()){
		$bookarr = '{
		    "proc":"calculate",
		    "book_id": "'.$f_books['book_id'].'"
		}';
		$arr_bookDetail['book_id']			= $f_books['book_id'];
		$arr_bookDetail['ISBN'] 			= $f_books['ISBN'];
		$arr_bookDetail['book_name'] 		= $f_books['book_name'];
		$arr_bookDetail['category'] 		= $f_books['category_name'];
		$arr_bookDetail['no_of_chapter'] 	= $f_books['no_of_chapter'];
		$arr_bookDetail['publish'] 			= $f_books['publish'];
		$arr_bookDetail['no_of_page'] 		= $f_books['no_of_page'];
		$arr_bookDetail['author'] 			= $f_books['author'];
		$arr_bookDetail['translator'] 		= $f_books['translator'];
		$arr_bookDetail['reading_times']	= $f_books['reading_times'];
		$arr_bookDetail['listening_times']	= $f_books['listening_times'];
		$arr_bookDetail['image']			= $f_books['image'];		
		$arr_bookDetail['percent_finish']	= calPercent($f_books['book_id']);
		$arr_bookDetail['rating']			= getRating($bookarr);

		array_push($arr_result, $arr_bookDetail);

	}
	echo json_encode($arr_result);
	//print_r($arr_result);

}
////////////////////////////////////////////////////////////////////////////////////                                              
function getBookDetail($arr_book){
	global $db;
	$book = json_decode($arr_book);
	$book_id 		= $book->book_id;
	$chapter_id		= $book->chapter_id;

	$arr_bookDetail = array();
	
	if($chapter_id==''){
		$sql_book = "SELECT A.book_id,A.volunteer_id, B.*, C.category_name, SUM(D.listening_times) AS listening_times
				FROM CREATED_BOOK A 
				JOIN BOOK B ON B.ISBN = A.ISBN
				JOIN CATEGORY C ON C.category_id = B.category_id
				LEFT JOIN CHAPTER D ON A.book_id = D.book_id
				WHERE A.book_id = ".$book_id."";
		$q_book = $db->query($sql_book);
		$f_book = $q_book->fetch_array();
		$bookarr = '{
			    "proc":"calculate",
			    "book_id": "'.$f_book['book_id'].'"
			}';
		$arr_bookDetail['book_id']			= $f_book['book_id'];
		$arr_bookDetail['volunteer_id']	 	= $f_book['volunteer_id'];
		$arr_bookDetail['ISBN'] 			= $f_book['ISBN'];
		$arr_bookDetail['book_name'] 		= $f_book['book_name'];
		$arr_bookDetail['category'] 		= $f_book['category_name'];
		$arr_bookDetail['no_of_chapter'] 	= $f_book['no_of_chapter'];
		$arr_bookDetail['publish'] 			= $f_book['publish'];
		$arr_bookDetail['no_of_page'] 		= $f_book['no_of_page'];
		$arr_bookDetail['author'] 			= $f_book['author'];
		$arr_bookDetail['translator'] 		= ($f_book['translator']!=null? $f_book['translator']: " - ");
		$arr_bookDetail['reading_times']	= $f_book['reading_times'];
		$arr_bookDetail['listening_times']	= ($f_book['listening_times']!=null? $f_book['listening_times'] : "0");	
		$arr_bookDetail['image']			= $f_book['image'];	
		$arr_bookDetail['percent_finish']	= calPercent($f_book['book_id']);
		$arr_bookDetail['rating']			= getRating($bookarr);
	}else{
		$sql_book="SELECT A.volunteer_id,B.book_name,C.chapter_id,C.complete,C.playlist_link , (SELECT SUM(video_time) AS TIME FROM PARAGRAPH WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." ) AS video_time
				FROM CREATED_BOOK A 
				JOIN BOOK B ON A.ISBN = B.ISBN
				JOIN CHAPTER C ON A.book_id = C.book_id
				LEFT JOIN PARAGRAPH D ON C.chapter_id = C.chapter_id
				WHERE A.book_id = ".$book_id." AND C.chapter_id= ".$chapter_id."
				GROUP BY C.chapter_id";
		$q_book = $db->query($sql_book);
		$f_book = $q_book->fetch_array();

		if($f_book['complete']==1){
			$word_com = "เสร็จแล้ว";
		}else{
			$word_com = "ยังไม่เสร็จ";
		}
		if($f_book['video_time']==null){
			$time = 0;
		}else{
			$time = $f_book['video_time'];
			$hours = str_pad(floor($time / 3600), 2, '0', STR_PAD_LEFT);
			$minutes = str_pad(floor(($time / 60) % 60), 2, '0', STR_PAD_LEFT);
			$seconds = str_pad($time % 60, 2, '0', STR_PAD_LEFT);
			$result_time = $hours.":".$minutes.":".$seconds;
		}

		$arr_bookDetail['volunteer_id'] 	= $f_book['volunteer_id'];
		$arr_bookDetail['book_name'] 		= $f_book['book_name'];
		$arr_bookDetail['chapter_id'] 		= $f_book['chapter_id'];
		$arr_bookDetail['complete'] 		= $word_com;
		$arr_bookDetail['playlist_link']	= $f_book['playlist_link'];
		$arr_bookDetail['video_time'] 		= ($result_time!=null? $result_time : "00:00:00");
	}
	
				

	echo json_encode($arr_bookDetail);
	//print_r($arr_bookDetail);
}                                                                      
//////////////////////////////////////////////////////////////////////////////////
function getReadedChapter($book_id){
	global $db;
	$arr_chapterDetail = array();
	$arr_result = array();

	$sql_chapter = "SELECT DISTINCT A.chapter_id ,A.volunteer_id ,C.name ,A.playlist_link, A.complete, A.listening_times,(SELECT SUM(video_time) AS TIME
				FROM PARAGRAPH WHERE book_id = ".$book_id." AND chapter_id = A.chapter_id GROUP BY chapter_id) AS video_time
				FROM CHAPTER A
				LEFT JOIN PARAGRAPH B ON A.chapter_id = B.chapter_id
				JOIN VOLUNTEER C ON A.volunteer_id = C.volunteer_id 
				WHERE A.book_id = ".$book_id." ";

	$q_chapter = $db->query($sql_chapter);
	while($f_chapter = $q_chapter->fetch_array()){
		if($f_chapter['complete']==0){
			$s_complete = "ยังไม่เสร็จ";
		}else{
			$s_complete = "เสร็จแล้ว";
		}

		if($f_chapter['video_time']!= null){
			$number = $f_chapter['video_time'];
			$hours = str_pad(floor($number / 3600), 2, '0', STR_PAD_LEFT);
			$minutes = str_pad(floor( ($number / 60) % 60), 2, '0', STR_PAD_LEFT);
			$seconds = str_pad($number % 60, 2, '0', STR_PAD_LEFT);
			$result_time = $hours.":".$minutes.":".$seconds;
			// $hours = floor($number / 3600);
			// $minutes = floor(($number / 60) % 60);
			// $seconds = $number % 60;
			// $result_time = $hours.":".$minutes.":".$seconds;
			
		}else{
			$result_time = "00:00:00";
		}
		$arr_chapterDetail['chapter_id'] 		= $f_chapter['chapter_id'];
		$arr_chapterDetail['volunteer_id']		= $f_chapter['volunteer_id'];
		$arr_chapterDetail['name']				= $f_chapter['name'];
		$arr_chapterDetail['playlist_link'] 	= $f_chapter['playlist_link'];
		$arr_chapterDetail['listening_times'] 	= $f_chapter['listening_times'];
		$arr_chapterDetail['complete'] 			= $s_complete;
		$arr_chapterDetail['video_time'] 		= $result_time;

		array_push($arr_result, $arr_chapterDetail);
	}
	//print_r($arr_result);
	echo json_encode($arr_result);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////
function getReadedParagraph($arr_book){
	global $db;
	$book = json_decode($arr_book);
	$book_id 	= $book->book_id;
	$chapter_id	= $book->chapter_id;

	$arr_paraDetail = array();
	$arr_result = array();

	$sql_para = "SELECT paragraph_id, paragraph_order, video_time, video_link,isused 
				FROM PARAGRAPH 
				WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id."
				ORDER BY paragraph_order ";
	$q_para = $db->query($sql_para);

	

	while($f_para = $q_para->fetch_array()){
		if($f_para['isused']==1){
			$isused = "เสี่ยงที่เลือก";
		}else{
			$isused = "เสียงที่ไม่ถูกเลือก";
		}

		

		$number = $f_para['video_time'];
		$minutes = str_pad(floor( ($number / 60) % 60), 2, '0', STR_PAD_LEFT);
		$seconds = str_pad($number % 60, 2, '0', STR_PAD_LEFT);
		// $minutes = floor($number / 60);
		// $seconds = $number % 60;
		$result_time = $minutes.":".$seconds;

		$arr_paraDetail['paragraph_id']		= $f_para['paragraph_id'];
		$arr_paraDetail['paragraph_order']	= $f_para['paragraph_order'];
		$arr_paraDetail['isused']			= $isused;
		$arr_paraDetail['isused_num']		= $f_para['isused'];
		$arr_paraDetail['video_time'] 		= $result_time;
		$arr_paraDetail['video_link']		= $f_para['video_link'];

		array_push($arr_result, $arr_paraDetail);
	}
	
	echo json_encode($arr_result);

}

///////////////////////////////////////////////////////////////////////////////////////////////////////
function updateIsused($arr_para){
	global $db;
	$arr_use = array();
	$arr_use = json_decode($arr_para);
	
	foreach ($arr_use as $key => $value) {
		
		$book_id 			= $value->book_id;
		$chapter_id			= $value->chapter_id;
		$paragraph_id 		= $value->paragraph_id;
		$paragraph_order 	= $value->paragraph_order;
		$isused				= $value->isused;

		$sql_Isused = "UPDATE PARAGRAPH
					SET isused = ".$isused."
					WHERE paragraph_id = ".$paragraph_id."
					AND chapter_id = ".$chapter_id."
					AND book_id = ".$book_id."  ";
		$q_Isused = $db->query($sql_Isused);
		
		if($q_Isused==false){
			echo mysqli_error($db);

		}

	}

}



///////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////
function insertRequest($arr_data){
	global $db;
	$join = json_decode($arr_data);
	$book_id 		= $join->book_id;
	$chapter_id 	= $join->chapter_id;
	$requester_id 	= $join->requester_id;
	$owner_id 		= $join->owner_id;

	$sql_insert = "INSERT INTO JOIN_REQUEST (book_id, chapter_id, requester_id, owner_id,date)
				VALUES (".$book_id.", ".$chapter_id.", '".$requester_id."','".$owner_id."', CURRENT_TIMESTAMP)";
	$q_insert = $db->query($sql_insert);
	if($q_insert){
		echo json_encode(" INSERTED SUCCESSFULLY");
	}else{
		echo json_last_error(); // 4 (JSON_ERROR_SYNTAX)
		echo json_last_error_msg(); // unexpected character 
		error_reporting(E_ALL);
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////

function getAllRequest($owner_id){
	global $db;
	$arr_book 	= array();
	$arr_bookre = array();

	// $arr_upallbook 	= array();
	// $arr_upbookre 	= array();
	// $arr_upresult	= array();

	$sql_book = "SELECT J.book_id, J.chapter_id, J.is_accept, B.book_name, COUNT(J.chapter_id) AS count
				FROM JOIN_REQUEST J
				JOIN CREATED_BOOK C ON J.book_id = C.book_id
				JOIN BOOK B ON C.ISBN = B.ISBN
				WHERE owner_id = '".$owner_id."' AND is_accept = 0 AND is_accept = ALL (SELECT is_accept FROM JOIN_REQUEST I WHERE owner_id = '".$owner_id."' AND I.book_id = J.book_id AND I.chapter_id = J.chapter_id)
				GROUP BY J.book_id, J.chapter_id, J.is_accept;";
	$q_book = $db->query($sql_book);

	while($f_book = $q_book->fetch_array()){

		$sql_isread = "SELECT * FROM JOIN_REQUEST WHERE book_id = ".$f_book['book_id']." AND chapter_id = ".$f_book['chapter_id']." AND is_read = 0";
		$q_isread = $db->query($sql_isread);
		if(mysqli_num_rows($q_isread)){
			$is_read = 0;
		}else{
			$is_read = 1;
		}


		$arr_bookre['book_id'] 		= $f_book['book_id'];
		$arr_bookre['chapter_id'] 	= $f_book['chapter_id'];
		$arr_bookre['is_accept'] 	= $f_book['is_accept'];
		$arr_bookre['book_name']	= $f_book['book_name'];
		$arr_bookre['count']		= $f_book['count'];
		$arr_bookre['is_read']		= $is_read;

		array_push($arr_book, $arr_bookre);

		// $arr_upbookre['book_id']	= $f_book['book_id'];
		// $arr_upbookre['chapter_id']	= $f_book['chapter_id'];
		// array_push($arr_upallbook, $arr_upbookre);

	}

	// $arr_upresult['book']	= $arr_upallbook;

	echo json_encode($arr_book);

	// alertNotiRead(json_encode($arr_upresult));
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
function getBookRequest($book_id){
	global $db;

	$arr_result = array();

	$sql_req = "SELECT A.* , B.name, B.avatar
				FROM JOIN_REQUEST A
				JOIN VOLUNTEER B ON A.requester_id = B.volunteer_id 
				WHERE book_id=".$book_id." 
				ORDER BY chapter_id ASC, is_accept DESC";
	$q_req = $db->query($sql_req);
	$arr_req = array();
	while($f_req = $q_req->fetch_array()){
		$arr_req['join_id']			= $f_req['join_id'];
		$arr_req['chapter_id'] 		= $f_req['chapter_id'];
		$arr_req['book_id'] 		= $f_req['book_id'];
		$arr_req['requester_id'] 	= $f_req['requester_id'];
		$arr_req['name']			= $f_req['name'];
		$arr_req['avatar']			= $f_req['avatar'];
		$arr_req['is_accept'] 		= $f_req['is_accept'];

		$arr_info = array();
		$arr_info['volunteer_id']	=	$f_req['requester_id'];
		$arr_info['type']			=	"return";
		$arr_info = json_encode($arr_info);
		$user = json_decode(getReadtime($arr_info));

		$arr_req['rank'] 			= "rank ".($user->ranking!=null? $user->ranking : "-");

		array_push($arr_result, $arr_req);
	}
	//print_r($arr_result);
	echo json_encode($arr_result);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////
function acceptJoin($join_id){
	global $db;

	$sql_accept = "UPDATE JOIN_REQUEST SET is_accept=1, is_read=1 WHERE join_id= ".$join_id." ";
	$q_accept = $db->query($sql_accept);

	if($q_accept){
		echo json_encode("update SUCCESSFULLY");
	}else{
		echo mysqli_error($db);	
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
function deleteJoin($join_id){
	global $db;

	$sql_delj = "DELETE FROM JOIN_REQUEST WHERE join_id= ".$join_id." ";
	$q_delj = $db->query($sql_delj);

	if($q_delj){
		echo json_encode("delete joined request successfully");
	}else{
		echo json_encode(mysqli_error($db));
	}
}

///////////////////////////////////////////////////////////////////

function queryNoti($volunteer_id){
	global $db;
	$arr_noti = array();
	$arr_result = array();

	// $arr_upalljoin 	= array();
	// $arr_upjoinre 	= array();
	// $arr_upresult	= array();
	
	$sql_noti = "SELECT A.*, B.name, B.avatar,D.book_name
				FROM JOIN_REQUEST A
				JOIN VOLUNTEER B ON A.owner_id = B.volunteer_id
				JOIN CREATED_BOOK C ON A.book_id = C.book_id
				JOIN BOOK D ON C.ISBN = D.ISBN
				WHERE A.requester_id = '".$volunteer_id."' AND A.is_accept=1
				ORDER BY date DESC";

	$q_noti = $db->query($sql_noti);
	
	while($f_noti = $q_noti->fetch_array()){

		$arr_noti['join_id'] 	= $f_noti['join_id'];
		$arr_noti['name'] 		= $f_noti['name'];
		$arr_noti['avatar'] 	= $f_noti['avatar'];
		$arr_noti['book_name'] 	= $f_noti['book_name'];
		$arr_noti['chapter_id'] = $f_noti['chapter_id'];
		$arr_noti['is_read']	= $f_noti['is_read'];
		array_push($arr_result, $arr_noti);

		// $arr_upjoinre['join_id']	= $f_noti['join_id'];
		// array_push($arr_upalljoin, $arr_upjoinre);
	}

	// $arr_upresult['join_id']	= $arr_upalljoin;

	echo json_encode($arr_result);

	// alertNotiRead(json_encode($arr_upresult));
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

// function confirmJoin($arr_info){ //functions
// 	global $db;

// 	$info = json_decode($arr_info);
// 	$join_id 		= $info->join_id;
// 	$playlist_link	= $info->playlist_link;


// 	$sql_info = "SELECT * FROM JOIN_REQUEST WHERE join_id =".$join_id." ";
// 	$q_info = $db->query($sql_info);
// 	$f_info = $q_info->fetch_array();

// 	$arr_info = array();
// 	$arr_info['book_id']		=	$f_info['book_id'];
// 	$arr_info['chapter_id']		=	$f_info['chapter_id'];
// 	$arr_info['volunteer_id']	=	$f_info['requester_id'];
// 	$arr_info['playlist_link']	=	$playlist_link;

// 	$arr_info = json_encode($arr_info);
// 	insertChapter($arr_info);



// 	$sql_conf = "UPDATE JOIN_REQUEST SET is_accept=2 WHERE join_id = ".$join_id." ";
// 	$q_conf = $db->query($sql_conf);

// 	if($q_conf){
// 		// echo json_encode("update SUCCESSFULLY");
// 		echo $f_info['book_id'];
// 	}else{
// 		echo json_encode(mysqli_error($db));	
// 	}

// }


function confirmJoin($arr_info){ //functions
	global $db;

	$info = json_decode($arr_info);
	$join_id 		= $info->join_id;
	$playlist_link	= $info->playlist_link;

	//CREATE chapter into CHAPTER

	$sql_info = "SELECT book_id,chapter_id,requester_id FROM JOIN_REQUEST WHERE join_id =".$join_id." ";
	$q_info = $db->query($sql_info);
	$f_info = $q_info->fetch_array();

	$arr_info = array();
	$arr_info['book_id']		=	$f_info['book_id'];
	$arr_info['chapter_id']		=	$f_info['chapter_id'];
	$arr_info['volunteer_id']	=	$f_info['requester_id'];
	$arr_info['playlist_link']	=	$playlist_link;

	$arr_info = json_encode($arr_info);
	insertChapter($arr_info);

	//UPDATE is_accept = 2
	$sql_is2 = "UPDATE JOIN_REQUEST SET is_accept=2 WHERE join_id = ".$join_id." ";
	$q_is2 = $db->query($sql_is2);


	//DELETE FROM ALL REQUEST OF THIS BOOK AND CHAPTER

	$sql_conf = "DELETE FROM JOIN_REQUEST WHERE book_id = ".$f_info['book_id']." AND chapter_id = ".$f_info['chapter_id']." AND is_accept!=2 ";
	$q_conf = $db->query($sql_conf);

	if($q_conf){
		echo $f_info['book_id'];
	}else{
		echo json_encode(mysqli_error($db));	
	}

}


//////////////////////////////////////////////////////////////////////////////////////
function ignoreJoin($arr_book){
	global $db;
	$book = json_decode($arr_book);
	$book_id 		= $book->book_id;
	$chapter_id 	= $book->chapter_id;
	$volunteer_id 	= $book->volunteer_id;
	$playlist_link 	= $book->playlist_link;

	$sql_ign = "DELETE FROM JOIN_REQUEST WHERE book_id = ".$book_id." AND chapter_id= ".$chapter_id." ";
	$q_ign = $db->query($sql_ign);
	if($q_ign){
		echo json_encode("update SUCCESSFULLY");
	}else{
		echo json_encode(mysqli_error($db));	
	}

	$arr_info = array();
	$arr_info['book_id']		=	$book_id;
	$arr_info['chapter_id']		=	$chapter_id;
	$arr_info['volunteer_id']	=	$volunteer_id;
	$arr_info['playlist_link']	=	$playlist_link;

	$arr_info = json_encode($arr_info);
	//print_r($arr_info);

	insertChapter($arr_info);

}
///////////////////////////////////////////////////////////////////////////////////
function allowJoin($arr_book){ //functions
	global $db;
	$book = json_decode($arr_book);
	$book_id 	= $book->book_id;
	$chapter_id = $book->chapter_id;

	$arr_info = array();
	$arr_info['book_id']		=	$book_id;
	$arr_info['chapter_id']		=	$chapter_id;
	$arr_info['type']			= "return";
	$arr_info = json_encode($arr_info);
	$have = checkJoin($arr_info);

	if($have=="true"){
		$sql_up = "UPDATE JOIN_REQUEST SET is_accept = 0 WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." ";
		$q_up = $db->query($sql_up);
		if($q_up){
			echo json_encode("update SUCCESSFULLY");
		}else{
			echo json_encode(mysqli_error($db));	
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
function checkJoin($arr_book){ //fucntions but need to fix // add insert chapter
	global $db;
	$book 		= json_decode($arr_book);
	$book_id 	= $book->book_id;
	$chapter_id	= $book->chapter_id;
	$type		= $book->type;

	$sql_checkJ = "SELECT join_id FROM JOIN_REQUEST WHERE book_id = ".$book_id." AND chapter_id =".$chapter_id." ";
	$q_checkJ = $db->query($sql_checkJ);
	$num_rows = mysqli_num_rows($q_checkJ);
	if($num_rows>0){
		if($type!=''){
			return "true";
		}else{
			echo json_encode("true");
		}
		
	}else{
		//echo $sql_checkJ;
		if($type!=''){
			return "false";
		}else{
			echo json_encode("false");
		}
	}

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////
function deleteParagraph($arr_detail){
	global $db;

	$book = json_decode($arr_detail);
	$book_id 		= $book->book_id;
	$chapter_id 	= $book->chapter_id;
	$paragraph_id 	= $book->paragraph_id;

	$sql_delPara = "DELETE FROM PARAGRAPH
					WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." AND paragraph_id= ".$paragraph_id." ";
	$q_delPara = $db->query($sql_delPara);
	if($q_delPara){
		echo json_encode("delete a record succesfully");
	}else{
	 	echo mysqli_error($db);	
	}

}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getVideoLink($arr_detail){
	global $db;

	$book = json_decode($arr_detail);
	$book_id 		= $book->book_id;
	$chapter_id 	= $book->chapter_id;
	$paragraph_id 	= $book->paragraph_id;

	$sql_link = "SELECT video_link FROM PARAGRAPH 
				WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." AND paragraph_id=".$paragraph_id." ";
	$q_link = $db->query($sql_link);
	$f_link = $q_link->fetch_array();

	echo json_encode($f_link['video_link']);
}



///////////////////////////////////////////////////////////////////////////////////////////////////////


function bookBlindWant($book_limit){
	global $db;

	$arr_re = array();
	$arr_result = array();

	$sql_books = "SELECT DISTINCT ISBN, book_name, null as image, 0 as reading_times
				FROM BLIND_REQUEST
				WHERE ISBN is null
				UNION
				SELECT DISTINCT B.ISBN, B.book_name, image, reading_times
				FROM BLIND_REQUEST BL JOIN BOOK B ON BL.ISBN = B.ISBN
				WHERE reading_times <= 5 ";

	if($book_limit > 0) {
		$sql_books .= " LIMIT ".$book_limit;
	}
	$q_books = $db->query($sql_books);

	while($f_books = $q_books->fetch_array()){
		$arr_readd['ISBN'] 			= $f_books['ISBN'];
		$arr_readd['book_name'] 	= $f_books['book_name'];
		$arr_readd['image']			= $f_books['image'];

		array_push($arr_result, $arr_readd);
	}


	echo json_encode($arr_result);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////

function mostReadBook($book_limit){
	global $db;
	$arr_readd = array();
	$arr_result = array();

	$sql_data = "SELECT A.ISBN, A.book_name,A.image , B.category_name
				FROM BOOK A JOIN CATEGORY B ON A.category_id = B.category_id
				WHERE reading_times!=0 
				ORDER BY reading_times DESC";

	if($book_limit > 0) {
			$sql_data .= " LIMIT ".$book_limit;
	}

	$q_data = $db->query($sql_data);

	while ($f_data = $q_data->fetch_array()) {
		$arr_readd['ISBN'] 			= $f_data['ISBN'];
		$arr_readd['book_name'] 	= $f_data['book_name'];
		$arr_readd['image']			= $f_data['image'];

		// $arr_readd['category_id'] 	= $f_data['category_name'];
		// $arr_readd['no_of_chapter'] = $f_data['no_of_chapter'];
		// $arr_readd['publish'] 		= $f_data['publish'];
		// $arr_readd['no_of_page'] 	= $f_data['no_of_page'];
		// $arr_readd['author'] 		= $f_data['author'];
		// $arr_readd['translator'] 	= $f_data['translator'];
		// $arr_readd['reading_times'] = $f_data['reading_times'];

		array_push($arr_result, $arr_readd);
	}
	echo json_encode($arr_result);
}

////////////////////////////////////////////////////////////////////////////////////////////

function categoryBook($category_id) {
	global $db;

	$arr_cat = array();
	$arr_books = array();

	$sql_cat = "SELECT category_name FROM CATEGORY WHERE category_id = ".$category_id;

	$q_cat = $db->query($sql_cat);
	$f_cat = $q_cat->fetch_array();
	$arr_cat['category_name'] = $f_cat['category_name'];

	$sql_books = "SELECT ISBN, book_name, image
				 FROM BOOK
				 WHERE category_id = ".$category_id." AND reading_times != 0 ORDER BY reading_times DESC";
	$q_books = $db->query($sql_books);

	while ($f_book = $q_books->fetch_array()) {
		$arr_book['ISBN'] 			= $f_book['ISBN'];
		$arr_book['book_name'] 		= $f_book['book_name'];
		$arr_book['image']			= $f_book['image'];
		array_push($arr_books, $arr_book);
	}
	$arr_cat['books'] = $arr_books;

	echo json_encode($arr_cat);

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function popularCategory($book_limit) {
	global $db; 

	$arr_result = array();

	$sql_category = "SELECT B.category_id, C.category_name, SUM(reading_times) as reading_times
				 FROM BOOK B 
				 JOIN CATEGORY C ON B.category_id = C.category_id
				 WHERE reading_times > 0
				 GROUP BY category_id
				 ORDER BY reading_times DESC
				 LIMIT 5";

	$q_category = $db->query($sql_category);
	while($f_category = $q_category->fetch_array()){
		$arr_category['category_id'] = $f_category['category_id'];
		$arr_category['category_name'] = $f_category['category_name'];

		$arr_books = array();

		$sql_book = "SELECT ISBN, book_name, image 
					 FROM BOOK 
					 WHERE category_id=".$f_category['category_id']." AND reading_times > 0 ORDER BY reading_times DESC ";

		if($book_limit > 0) {
			$sql_book .= " LIMIT ".$book_limit;
		}

		$q_book = $db->query($sql_book);
		while($f_book = $q_book->fetch_array()) {
			$arr_book['ISBN'] = $f_book['ISBN'];
			$arr_book['book_name'] = $f_book['book_name'];
			$arr_book['image'] = $f_book['image'];

			array_push($arr_books, $arr_book);
		}
		
		$arr_category['books'] = $arr_books;

		array_push($arr_result, $arr_category);
	}

	echo json_encode($arr_result);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delChapter($arr_chap){
	global $db;

	$chap = json_decode($arr_chap);
	$book_id = $chap->book_id;
	$chapter_id = $chap->chapter_id;

	$sql_deljoin 	= "DELETE FROM JOIN_REQUEST WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." ";
	$q_deljoin 		= $db->query($sql_deljoin);

	$sql_delchap = "DELETE FROM CHAPTER WHERE book_id = ".$book_id." AND chapter_id = ".$chapter_id." ";
	$q_delchap = $db->query($sql_delchap);

	if($q_delchap) {
		echo json_encode("del FROM CHAPTER");
	}else{
		echo mysqli_error($db);	
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

function getUser($isbn){
	global $db;
	
	$arr_userre = array();
	$arr_user = array();

	$sql_user = "SELECT A.book_id,A.volunteer_id, B.name, B.avatar 
				FROM CREATED_BOOK A 
				JOIN VOLUNTEER B ON A.volunteer_id= B.volunteer_id WHERE isbn = '".$isbn."' ";
	$q_user = $db->query($sql_user);

	while($f_user = $q_user->fetch_array()){

		$arr_info = array();
		$arr_info['volunteer_id']	=	$f_user['volunteer_id'];
		$arr_info['type']			=	"return";
		$arr_info = json_encode($arr_info);
		$user = json_decode(getReadtime($arr_info));

		$arr_userre['book_id']		= $f_user['book_id'];
		$arr_userre['volunteer_id'] = $f_user['volunteer_id'];
		$arr_userre['name'] 		= $f_user['name'];
		$arr_userre['avatar'] 		= $f_user['avatar'];
		$arr_userre['percent'] 		= calPercent($f_user['book_id']);
		$arr_userre['ranking']		= $user->ranking;

		array_push($arr_user, $arr_userre);
	}
	
	echo json_encode($arr_user);
}

////////////////////////////////////////////////////////////////////////
function getDetailBookid($book_id){
	global $db;

	$arr_result = array();

	$sql_book = "SELECT * 
				FROM CREATED_BOOK A 
				JOIN BOOK B ON A.ISBN = B.ISBN
				WHERE A.book_id = ".$book_id." " ; 
	$q_book = $db->query($sql_book);
	$f_book = $q_book->fetch_array();

	$arr_result['book_id'] 			= $f_book['book_id'];
	$arr_result['volunteer_id'] 	= $f_book['volunteer_id'];
	$arr_result['ISBN'] 			= $f_book['ISBN'];
	$arr_result['create_at'] 		= $f_book['create_at'];
	$arr_result['book_name'] 		= $f_book['book_name'];
	$arr_result['category_id'] 		= $f_book['category_id'];
	$arr_result['no_of_chapter'] 	= $f_book['no_of_chapter'];
	$arr_result['publish'] 			= $f_book['publish'];
	$arr_result['no_of_page'] 		= $f_book['no_of_page'];
	$arr_result['author'] 			= $f_book['author'];
	$arr_result['translator'] 		= $f_book['translator'];
	$arr_result['reading_times'] 	= $f_book['reading_times'];
	$arr_result['image'] 			= $f_book['image'];

	echo json_encode($arr_result);

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function chapterComplete($arr_chap){
	global $db;

	$chapter 	= json_decode($arr_chap);
	$book_id 	= $chapter->book_id;
	$chapter_id = $chapter->chapter_id;
	$complete 	= $chapter->complete;


	$sql_chapter = "UPDATE CHAPTER SET complete = ".$complete." WHERE book_id=".$book_id." AND chapter_id=".$chapter_id." ";
	$q_chapter = $db->query($sql_chapter);
	
	if($q_chapter){
		echo json_encode(0);
	}else{
		echo mysqli_error($db);
	}
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
function deleteBook($book_id){
	global $db;
	$arr_playlist 	= array();
	$arr_video 		= array();
	$arr_result 	= array();

	// $sql_checkChap = "SELECT chapter_id FROM CHAPTER WHERE book_id = ".$book_id." ";
	// $q_checkChap = $db->query($sql_checkChap);
	// $row_checkChap = mysqli_num_rows($q_checkChap);
	// if($row_checkChap>0){

	// }

	$sql_playlist 	= "SELECT playlist_link FROM CHAPTER WHERE book_id = ".$book_id." ";
	$sql_videos		= "SELECT video_link FROM PARAGRAPH WHERE book_id = ".$book_id." ";

	$q_playlist	= $db->query($sql_playlist);
	$q_videos 	= $db->query($sql_videos);

	while($f_playlist = $q_playlist->fetch_array()){

		array_push($arr_playlist, $f_playlist['playlist_link']);

	}

	$arr_result['playlists']	= $arr_playlist;

	while ($f_videos = $q_videos->fetch_array()) {

		array_push($arr_video, $f_videos['video_link']);

	}

	$arr_result['videos'] = $arr_video;

	
	$sql_book = "DELETE FROM CREATED_BOOK WHERE book_id=".$book_id." ";
	$q_book = $db->query($sql_book);

	if($q_book){
		echo json_encode($arr_result);
	}else{
		echo mysqli_error($db);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

function alertNotiRead($arr_join){
	global $db;

	$json 		= json_decode($arr_join);
	$arr_use 	= array();

	$wh = "";

	if($json->book!=''){

		$arr_use = $json->book;
		$size 	 = count($arr_use);

		for($i=0 ; $i<$size ; $i++){

			$book_id 	= $arr_use[$i]->book_id;
			$chapter_id = $arr_use[$i]->chapter_id;

			if($i==0){
				$wh .= " ( book_id = ".$book_id." AND chapter_id = ".$chapter_id.") ";
			}else{
				$wh .= "OR ( 
				 = ".$book_id." AND chapter_id = ".$chapter_id.") ";
			}

		}//loop

		//echo $wh;
		$sql_update = "UPDATE JOIN_REQUEST
				SET is_read = 1
				WHERE ".$wh." ";

	}else{

		$arr_use 	= $json->join_id;
		$size 		= count($arr_use);

		for($i=0 ; $i<$size ; $i++){

			$join_id = $arr_use[$i]->join_id;

			if($i==0){
				$wh .= " join_id = ".$join_id." ";
			}else{
				$wh .= "OR join_id = ".$join_id." ";
			}

		}//loop

		//echo $wh;
		$sql_update = "UPDATE JOIN_REQUEST
				SET is_read = 2
				WHERE ".$wh." ";
	}

	$q_update = $db->query($sql_update);

	if($q_update){
		//echo json_encode(1) ;
	}else{
		//echo json_encode(0);
	}

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
include("bottom.php");
?>