// ログイン

document.getElementById('loginForm').addEventListener('submit', function(e) {
  const username = document.getElementById('loginUsername').value.trim();
  const password = document.getElementById('loginPassword').value;
  const errorMsg = document.getElementById('loginErrorMsg');

fetch("modules/login.php", {
  method: "POST",
  body: formData,
  credentials: "include"  // ←これが重要
})

  // バリデーションチェック
  if (!username || !password) {
    e.preventDefault();
    errorMsg.textContent = 'ユーザー名とパスワードを入力してください。';
  }
});
