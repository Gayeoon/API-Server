<?php

// API NO. 1 User 모든 정보 출력
function getUserInfo($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM User WHERE UserId = ? AND IsDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    $res = array_filter($res);

    return $res;
}

// API NO. 2 My배민 페이지의 User 정보 조회
function getUser($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT User.Name, User.Level, COUNT(*) AS Coupon
        FROM User
        JOIN Coupon
        ON User.UserIdx= Coupon.UserIdx AND Coupon.isUsed = 'N'
        WHERE UserId = ? AND isDeleted = 'N'
        GROUP BY User.UserIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// API NO. 3 포인트 이용 내역 조회
function getUserPoint($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT Point, date_format(Date,'%Y-%m-%d') AS Date , EndDate, Point.IsDeleted As Used, Store
        FROM Point
        JOIN User ON User.UserId = ? AND Point.UserIdx = User.UserIdx
        ORDER BY Date DESC;
        ";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    for($i=0; $i<sizeof($res); $i++)
    {
        $res[$i] = array_filter($res[$i]);
    }

    return $res;
}

// API NO. 4 보유 포인트 합계 조회
function getUserPointSum($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT CONCAT(sum(Point), '원') AS Sum FROM Point
        JOIN User ON UserId = ?
        WHERE User.UserIdx = Point.UserIdx AND Point.IsDeleted >= 3
        GROUP BY UserID;
        ";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// API NO. 5 찜한가게, 바로결제, 전화주문 목록 조회
function getUserChoose($keyword, $flag)
{
    $pdo = pdoSqlConnect();

    if($flag == 2) {
        $query = "SELECT Store.Name, Store.Star, Store.Min, Store.Represent, Store.IsOpen, Store.IsOrder
        FROM Choose
        JOIN User on User.UserId = ?
        JOIN  Store
        ON Choose.UserIdx = User.UserIdx AND Choose.StoreIdx = Store.StoreIdx;";

        $st = $pdo->prepare($query);
        $st->execute([$keyword]);
    }
    else {
        $query = "SELECT Store.Name, Store.Star, Store.Min, Store.Represent, Store.IsOpen, Store.IsOrder
        FROM OrderMenu
        JOIN User on User.UserId = ?
        JOIN  Store
        ON OrderMenu.UserIdx = User.UserIdx AND OrderMenu.Payment = ? AND OrderMenu.StoreIdx = Store.StoreIdx;";

        $st = $pdo->prepare($query);
        $st->execute([$keyword, $flag]);
    }


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// API NO. 6 Store 상세 정보
function getStore($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT Store.Name, Star, Min,  Type, CONCAT(FORMAT(Store.Tip , 0), '원') AS Tip, Store.Phone, Store.DeliveryTime, Explan
        FROM Store
        WHERE Store.StoreID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    $res = array_filter($res[0]);
    return $res;
}

// API NO. 7 Store 최근 리뷰 / 사장님 댓글 개수 조회
function getStoreReview($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT COUNT(*) AS Review, COUNT(Review.Comment) AS Comments
        FROM Store
        JOIN Review
        ON Review.StoreIdx = Store.StoreIdx
        WHERE StoreId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// API NO. 8 Store 찜 개수 조회
function getStoreChoose($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT COUNT(*) As Choose
        FROM Store
        JOIN Choose
        ON Choose.StoreIdx = Store.StoreIdx
        WHERE StoreId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// API NO. 9 Store 메뉴 목록 조회
function getStoreMenu($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT Menu.Name AS MenuName, CONCAT(FORMAT(Menu.Price , 0), '원') AS Price, Menu.Picture, Menu.IsPossible, Menu.MenuNum
        FROM Menu
        JOIN  Store
        ON Store.StoreID = 'mmmm' AND Menu.StoreIdx = Store.StoreIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// API NO. 10 User 주문 내역 목록 조회
function getUserOrder($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT OrderMenu.Type,
            CASE
                WHEN TIMESTAMPDIFF(DAY, OrderMenu.Date, NOW()) < 1 THEN CONCAT('오늘')
                WHEN TIMESTAMPDIFF(DAY, OrderMenu.Date, NOW()) <= 7 THEN CONCAT(TIMESTAMPDIFF(DAY, OrderMenu.Date, NOW()), '일 전')
                ELSE CONCAT(date_format(OrderMenu.Date,'%m/%d'), ' (', SUBSTR( _UTF8'일월화수목금토', DAYOFWEEK(OrderMenu.Date), 1),')')
        END AS Date,
        Store.Category, Store.Name, OrderMenu.IsReview, CONCAT(FORMAT(Store.Tip , 0), '원') AS Tip,
               GROUP_CONCAT(Menu.Name) AS MenuName, CONCAT(FORMAT(SUM(Menu.Price) , 0), '원') AS MenuPrice, Store.StoreId, OrderMenu.OrderNumber, OrderMenu.OrderIdx
        FROM OrderMenu
        JOIN User on User.UserId = 'judy'
        JOIN  Store
        ON OrderMenu.UserIdx = User.UserIdx AND OrderMenu.Type <= 1 AND OrderMenu.StoreIdx = Store.StoreIdx
        JOIN  OrderMenuList
        ON OrderMenu.OrderNumber = OrderMenuList.OrderNumber
        JOIN  Menu
        ON Menu.MenuNum = OrderMenuList.MenuNum
        GROUP BY OrderMenu.Date, OrderMenu.OrderIdx
        ORDER BY OrderMenu.Date DESC ;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// API NO. 11 User 상세 주문 내역 정보 조회
function getUserOrderDetail($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT
       CASE
           WHEN HOUR(OrderMenu.Date) < 12 THEN date_format(OrderMenu.Date,'%Y년 %m월 %d일 오전 %h:%i')
           ELSE date_format(OrderMenu.Date,'%Y년 %m월 %d일 오후 %h:%i')
       END AS Date,
        OrderMenu.OrderNumber,  Store.Name, CONCAT(FORMAT(Store.Tip , 0), '원') AS Tip, OrderMenu.IsDelivered, Store.Phone, OrderMenu.Payment, OrderMenu.Address, Store.StoreId
        FROM OrderMenu
        JOIN  Store
        ON OrderMenu.OrderNumber = ? AND OrderMenu.StoreIdx = Store.StoreIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    $res = array_filter($res[0]);
    return $res;
}

// API NO. 12 User 주문 내역 메뉴 정보 조회
function getUserOrderMenu($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT Menu.Name, CONCAT(FORMAT(Menu.Price , 0), '원') AS MenuPrice, OrderMenuList.MenuOption, OrderMenuList.MenuCnt
        FROM OrderMenu
        JOIN User on User.UserId = ?
        JOIN  OrderMenuList
        ON OrderMenu.UserIdx = User.UserIdx AND OrderMenu.OrderNumber = 'B0QE01AX5S' AND OrderMenuList.OrderNumber = OrderMenu.OrderNumber
        JOIN  Menu
        ON Menu.MenuNum = OrderMenuList.MenuNum;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    for($i=0; $i<sizeof($res); $i++)
    {
        $res[$i] = array_filter($res[$i]);
    }

    return $res;
}

// API NO. 13 User 리뷰 개수 조회
function getUserReviewCount($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT COUNT(*) AS MyReview
        FROM Review
        JOIN User on User.UserId = ?
        WHERE Review.UserIdx = User.UserIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// API NO. 14 User 리뷰 목록 조회
function getUserReview($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT Review.Contents, Review.Tag, GROUP_CONCAT(ReviewPicture.Picture SEPARATOR '|') AS Picture,
            CASE
                WHEN TIMESTAMPDIFF(DAY, Review.Date, NOW()) < 1 THEN CONCAT('방금 전')
                WHEN TIMESTAMPDIFF(DAY, Review.Date, NOW()) <= 1 THEN CONCAT('어제')
                WHEN TIMESTAMPDIFF(DAY, Review.Date, NOW()) <= 7 THEN CONCAT('이번 주')
                WHEN TIMESTAMPDIFF(DAY, Review.Date, NOW()) <= 29 THEN CONCAT(CAST(TIMESTAMPDIFF(DAY, Review.Date, NOW()) / 7 as unsigned), '주 전')
                WHEN TIMESTAMPDIFF(MONTH , Review.Date, NOW()) <= 1 THEN CONCAT('지난 달')
                WHEN TIMESTAMPDIFF(MONTH , Review.Date, NOW()) < 12 THEN CONCAT(TIMESTAMPDIFF(MONTH , Review.Date, NOW()), '개월 전')
                ELSE CONCAT('작년')
            END AS Date
             , Review.Star, Store.Name, Store.StoreId
        FROM Review
        JOIN User on User.UserId = 'judy'
        JOIN  Store
        ON Review.UserIdx = User.UserIdx AND Review.isDeleted = 'N' AND Review.StoreIdx = Store.StoreIdx
        JOIN ReviewPicture
        ON Review.ReviewIdx = ReviewPicture.ReviewIdx
        GROUP BY Review.ReviewIdx, Review.Date
        ORDER BY Review.Date DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    for($i=0; $i<sizeof($res); $i++)
    {
        $res[$i] = array_filter($res[$i]);
    }

    return $res;
}

// API NO. 15 User 회원가입
function createUser($UserId, $UserPw, $Name, $Phone, $Email, $MailReceiving, $SmsReceiving){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (UserId, UserPw, Name, Phone, Email, Level, MailReceiving, SmsReceiving)
 VALUES (?,?,?,?,?,0,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$UserId, $UserPw, $Name, $Phone, $Email, $MailReceiving, $SmsReceiving]);

    $st = null;
    $pdo = null;
}

// API NO. 16 Store 회원가입
function createStore($StoreId, $StorePw, $Name, $Type, $Category,
                    $OpenTime, $CloseTime, $IsDelivery, $IsOrder, $IsBmart,
                    $Represent, $Min, $Tip, $Phone, $Explan, $DeliveryTime,
                    $IsOpenList, $IsUltraCall, $Latitude, $Longitude){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Store (StoreId, StorePw, Name, Type, Category, IsDeleted,
                    OpenTime, CloseTime, IsOpen, IsDelivery, IsOrder, IsBmart,
                    Represent, Min, Tip, Phone, Explan, DeliveryTime,
                    IsOpenList, IsUltraCall, Latitude, Longitude)
 VALUES (?, ?, ?, ?, ?,'N',?, ?, 'Y', ?,?,?,?,?, ?,?,?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$StoreId, $StorePw, $Name, $Type, $Category,
        $OpenTime, $CloseTime, $IsDelivery, $IsOrder, $IsBmart,
        $Represent, $Min, $Tip, $Phone, $Explan, $DeliveryTime,
        $IsOpenList, $IsUltraCall, $Latitude, $Longitude]);

    $st = null;
    $pdo = null;
}

// API NO. 17 Store 메뉴 추가
function createStoreMenu($StoreIdx, $Name, $Picture, $Price, $MenuOption){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Menu (StoreIdx, Name, Picture, Price, IsPossible, MenuOption)
 VALUES (?, ?, ?, ?, 'Y', ?);";

    $st = $pdo->prepare($query);
    $st->execute([$StoreIdx, $Name, $Picture, $Price, $MenuOption]);

    $st = null;
    $pdo = null;
}

// Store Id 가져오기
function getStoreId($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT StoreIdx FROM Store WHERE StoreId = 'mmmm'";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// ID 체크
function isValidId($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM User WHERE UserId = ? AND IsDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

// Store Id 중복 체크
function isValidStoreId($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM Store WHERE StoreId = ? AND IsDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

// OrderNumber 유효성 체크
function isValidNumber($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM OrderMenu WHERE OrderNumber = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

// ID 중복 체크
function idCheck($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM User WHERE UserId = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

// 동일한 메뉴가 있는지 확인
function checkMenu($storeIdx, $name)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS (SELECT * FROM Menu WHERE StoreIdx = ? AND NAME = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx, $name]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

//// Test
//function getUsers($keyword)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select * from testTable where name like concat('%', ?, '%');";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$keyword]); //파라미터 list 형태로 넣을 것
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//// Test
//function getUserDetail($no)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM testTable WHERE no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$no]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//// Test
//function isValidNo($no)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT EXISTS (SELECT * FROM testTable WHERE no = ?) AS exist;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$no]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//    //echo json_encode($res);
//    return intval($res[0]['exist']);
//}
//
//// Test
//function isValidUser($id, $pw){
//    $pdo = pdoSqlConnect();
//    $query = "SELECT EXISTS(SELECT * FROM User WHERE userId= ? AND userPw = ?) AS exist;";
//
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$id, $pw]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st=null;$pdo = null;
//
//    return intval($res[0]["exist"]);
//
//}


// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
