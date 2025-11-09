<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle ?? 'MVC 홈'; ?></title>
</head>
<body>
    <h1>MVC 구동 성공!</h1>
    <p>이 페이지는 기본 홈 뷰(home.php)입니다.</p>
    <p>라우팅이 정상적으로 작동하고 있습니다. 이제 게시판 목록이 표시될 것입니다.</p>
    
    <!-- 이 버튼을 누르면 PostController로 이동하는 라우팅이 실행되어야 합니다. -->
    <a href="/posts" class="btn btn-primary">게시판 보기</a>
</body>
</html>

