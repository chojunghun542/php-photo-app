<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle ?? '로그인'; ?></title>
    <!--  -->
</head>
<body>
    <h2>로그인</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (isset($message)): ?>
        <p style="color: blue;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <!-- Action은 라우터를 통해 UserController::login()으로 연결됨 -->
    <form action="/login" method="POST">
        <!-- name 속성 수정: id -> login_id -->
        <label for="login_id">ID:</label>
        <input type="text" id="login_id" name="login_id" required><br><br>
        
        <!-- name 속성 수정: pw -> password -->
        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">로그인</button>
    </form>
    <p><a href="/register">회원가입</a></p>
</body>
</html>
