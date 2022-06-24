<?php
session_start();

$conn = mysqli_connect('localhost', "upreadru_demopo", "[IL/0J3gSmhma0Wh", "upreadru_demopo");
if(mysqli_connect_error()){
    echo "Connection Error";
    die();
}
mysqli_set_charset($conn, "utf8mb4");

class AuthClass {

    /**
     * Проверяет, авторизован пользователь или нет
     * Возвращает true если авторизован, иначе false
     * @return boolean 
     */
    public function isAuth() {
        if (isset($_SESSION["is_auth"])) { //Если сессия существует
            return $_SESSION["is_auth"]; //Возвращаем значение переменной сессии is_auth (хранит true если авторизован, false если не авторизован)
        }
        else return false; //Пользователь не авторизован, т.к. переменная is_auth не создана
    }
     
    /**
     * Авторизация пользователя
     * @param string $login
     * @param string $passwors 
     */
    public function auth($login, $pass) {
        global $conn;
		
		$query = "SELECT * FROM users WHERE login='$login' AND pass='$pass'";
		$result = mysqli_query($conn, $query);
		$user = mysqli_fetch_assoc($result);
		
		if (!empty($user)) {
            $_SESSION["is_auth"] = true; //Делаем пользователя авторизованным
            $_SESSION["login"] = $login; //Записываем в сессию логин пользователя
            $_SESSION["id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["familiya"] = $user["familiya"];
            $_SESSION["mail"] = $user["mail"];
            return true;
		} else {
            $_SESSION["is_auth"] = false;
            return false; 
		}
    }
     
    /**
     * Метод возвращает логин авторизованного пользователя 
     */
    public function getInfo() {
        if ($this->isAuth()) { //Если пользователь авторизован
            return array (
                "id" => $_SESSION["id"],
                "login" => $_SESSION["login"],
                "name" => $_SESSION["name"],
                "familiya" => $_SESSION["familiya"],
                "mail" => $_SESSION["mail"]
            );
        }
    }
     
     
    public function out() {
        $_SESSION = array(); //Очищаем сессию
        session_destroy(); //Уничтожаем
    }

    public function reg($login, $pass, $mail, $name, $familiya, $pass2){
        global $conn;

        if ($pass != $pass2){
            return "Пароль и подтверждение пароля не совпадают!";
        }

        if (!$login || !$pass || !$pass2 || !$mail || !$name || !$familiya){
            return "Не все поля заполнены!";
        }

       
        try {
            $query = "INSERT INTO `users` (`login`,`mail`,`pass`,`name`,`familiya`) VALUES ('$login','$mail','$pass','$name', '$familiya');";          
            mysqli_query($conn, $query);
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                return "Логин или email уже используются!";
            } else {
                throw $e;// in case it's any other error
            }
        }


        return ""; 
    }

    public function send($user_id, $mess){
        global $conn;
        $query = "INSERT INTO `mails` ( `user_id`, `txt`) VALUES ('$user_id', '$mess');";          
        mysqli_query($conn, $query);
    }
}
 
$auth = new AuthClass();

$error = "";
$message = "";

if (isset($_POST["auth"])) { 
    if (!$auth->auth($_POST["login"], $_POST["password"])) { //Если логин и пароль введен не правильно
        $error = "Логин или пароль введен не правильно!";
    }
}

//если регистрация
if (isset($_POST["reg"])){
    $error = $auth->reg($_POST["login"], $_POST["password"], $_POST["mail"], $_POST["name"], $_POST["familiya"], $_POST["password2"]);

    if (!$error){
        $auth->auth($_POST["login"], $_POST["password"]);
    }
}

//если отправка письма
if (isset($_POST["send"])){
    $cur_user = $auth->getInfo();
    $auth->send($cur_user["id"], $_POST["mess"]);
    $message = "Ваше сообщение отправлено!";

}
 
if (isset($_GET["is_exit"])) { //Если нажата кнопка выхода
    if ($_GET["is_exit"] == 1) {
        $auth->out(); //Выходим
        header("Location: ?is_exit=0"); //Редирект после выхода
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Сайт полицейского участка города Москвы</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
<header>
Сайт полицейского участка города Москвы
</header>
<content>


<div id="content_block">
Мы рады привествовать вас на сайте полицейского участка города Москвы!<br /><br />
Здесь вы можете узнать больше о работе правоохранительных органов нашего города.<br /><br />
<img src="foto.jpg" />
</div>

<div style="text-align: center;margin: 20px;font-size: 24px;">
    Внимание, розыск!
</div>
<div id="wanted_block">
    <div class="wrap_wanted">
        <img src="avatar1.jpg" /> 
        <div>Убийство</div>
    </div>
    <div class="wrap_wanted">
        <img src="avatar2.jpg" /> 
        <div>Хулиган</div>
    </div>
    <div class="wrap_wanted">
        <img src="avatar3.jpg" />
        <div>Воровство</div> 
    </div>
</div>

<div id="register_block">
<?php
if ($error){
    echo "<div class='error'>".$error."</div>";
}
if ($message){
    echo "<div class='ok'>".$message."</div>";
}

if ($auth->isAuth()) { // Если пользователь авторизован, приветствуем:  
    $cur_user = $auth->getInfo();
?>

<div>
    <h4>Приветствуем вас, <b><?php echo $cur_user["login"]; ?></b> <br />
Вы можете отправить нам любое сообщение в обратную связь, сообщить нам о правонарушениях столицы.
</h4> 

</div>

<div>
<form method="post" action="" style="display: flex;">
    <textarea name="mess" cols="20" rows="5"></textarea>
    <br />
    <input type="submit" value="Отправить" name="send"/>
</form>
</div>

<?php
    echo "<br/><br/><a href='?is_exit=1'>Выйти</a>"; //Показываем кнопку выхода
} 
else { //Если не авторизован, показываем форму ввода логина и пароля
?>

<form method="post" action="">
<h2>Войдите:</h2>
    Логин: 
    <br/><input type="text" name="login" 
    value="<?php echo (isset($_POST["login"])) ? $_POST["login"] : null; // Заполняем поле по умолчанию ?>" />
    <br/>
    Пароль:
    <br/> <input type="password" name="password" value="" /><br/>
    <input type="submit" value="Войти" name="auth"/>
</form>
<br /><br />

<form method="post" action="" id="form2">
    <div class="form_in">
<h2>Или зарегистрируйтесь:</h2>
    Логин<br/> 
    <input type="text" name="login" 
    value="<?php echo (isset($_POST["login"])) ? $_POST["login"] : null; // Заполняем поле по умолчанию ?>" />
    <br/><br/>
    Пароль:<br/>
     <input type="password" name="password" value="" /><br/>
    <br/>
    Подтвердите пароль:
    <br/> <input type="password" name="password2" value="" /><br/>
</div>
<div class="form_in">
    mail:
    <br/> <input type="email" name="mail" value="" /><br/>
    <br/>
    Имя:
    <br/> <input type="text" name="name" value="" /><br/>
    <br/>
    Фамилия: 
    <br/><input type="text" name="familiya" value="" /><br/>
    <input type="submit" value="Зарегистрироваться" name="reg"/>
</div>
</form>

<?php 
}
?>
</div>
</content>
<footer>
(c) 2022, Полицейский участок
</footer>
<div>



</body>
</html>