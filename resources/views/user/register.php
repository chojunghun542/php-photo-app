<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle ?? '회원가입'; ?></title>
    <!--  -->
</head>
<body>
    <h2>회원가입</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="/register" method="POST">
        
        <!-- name 속성 수정: id -> login_id -->
        <label for="login_id">ID:</label>
        <input type="text" id="login_id" name="login_id" required><br><br>
        
        <!-- name 속성 수정: pw -> password -->
        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <label for="name">이름:</label>
        <input type="text" id="name" name="name" required><br><br>

        <!-- 새 필드: Email -->
        <label for="email">이메일:</label>
        <input type="email" id="email" name="email" required><br><br>

        <!-- 새 필드: Phone Number -->
        <label for="phone_num">전화번호:</label>
        <input type="text" id="phone_num" name="phone_num"><br><br>

        <button type="submit">가입하기</button>
    </form>
    <p><a href="/login">이미 계정이 있으신가요? 로그인</a></p>
</body>
</html>
