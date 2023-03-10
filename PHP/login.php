<?php
declare(strict_types=1);

namespace login;

require_once "./imp.php";

use Dotenv\Dotenv;
use imp\typeOf_input;
use imp\KKJ_Encryption;
use imp\KKJ_Sanitization;
use imp\KKJ_Database;

$dotenv = Dotenv::createImmutable("../../");
$dotenv->load();

function LogSanitize(&$data): void
{
    foreach ($data as $_ => &$value) {
        $value[0] = (new KKJ_Sanitization($value[1], $value[0]))->sanitize();
    }
}

function LogEncrypt(&$data): void
{
    foreach ($data as $_ => &$value) {
        if ($_ == "Password") {
            continue;
        }
        $value[0] = (new KKJ_Encryption($value[1], $value[0]))->Encrypt_Element();
    }
}


$data = array(
    "Username" => array($_POST['username'], typeOf_input::STRING),
    "Password" => array($_POST['password'], typeOf_input::PASSWORD)
);

LogSanitize($data);
LogEncrypt($data);


if (isset($_POST['UserLogin'])) {
    $client = new KKJ_Database($_ENV["MAIN_DB"], $_ENV["USER_DB"], $_ENV["DB_CONNECTION"]);

    $result = $client->findOne(array("Username" => $data["Username"][0]));

    if ($result) {
        if (password_verify($data["Password"][0], $result["Password"])) {
            echo "Login Successful";
            // User can be redirected to the main page
            // usage of header("Location: <URL>")
            session_id($_ENV["SESSION_ID"]);
            session_start();
            $_SESSION["Username"] = (new KKJ_Encryption(typeOf_input::STRING, $data["Username"][0]))->Decrypt_Element();

            //echo $_SESSION["Username"];
            header("Location: ../HTML/index.php");

        } else {
            echo "Invalid Password";
        }
    } else {
        echo "Invalid Username";
    }
} else if (isset($_POST['StaffLogin'])) {
    $client = new KKJ_Database($_ENV["MAIN_DB"], $_ENV["STAFF_DB"], $_ENV["DB_CONNECTION"]);

    $result = $client->findOne(array("Username" => (new KKJ_Encryption(typeOf_input::STRING, $data["Username"][0]))->Decrypt_Element()));

    if ($result) {
        if ($data["Password"][0] == $result["Password"]) {
            echo "Login Successful"; // User can be redirected to the main page
            // usage of header("Location: <URL>")
            //echo $_SESSION["Username"];
            header("Location: ../HTML/AdminPage.html");

        } else {
            echo "Invalid Password";
        }
    } else {
        echo "Invalid Username";
    }

}
?>