// ユーザー登録
// 簡易バリデーションチェックのみ
document.getElementById('registerForm').addEventListener('submit', function(e) {
  const password = document.getElementById('registerPassword').value;
  const errorMsg = document.getElementById('registerErrorMsg');

  // パスワードの簡易バリデーション
  if (password.length < 6) {
    e.preventDefault();
    errorMsg.textContent = 'パスワードは6文字以上必要です。';
  }
});
