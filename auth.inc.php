<?php
if (isset($_POST["server"])) {
	if (isset($_REQUEST[session_name()])) {
		session_regenerate_id();
		$_SESSION["usernames"][$_POST["server"]] = $_POST["username"];
		$_SESSION["passwords"][$_POST["server"]] = $_POST["password"];
		if (count($_POST) == ($_POST[session_name()] ? 4 : 3)) {
			if ((string) $_GET["server"] === $_POST["server"]) {
				$location = preg_replace('~(\\?)' . urlencode(session_name()) . '=[^&]*&|[?&]' . urlencode(session_name()) . '=[^&]*~', '\\1', $_SERVER["REQUEST_URI"]);
			} else {
				$location = preg_replace('~^[^?]*/([^?]*).*~', '\\1', $_SERVER["REQUEST_URI"]) . (strlen($_POST["server"]) ? '?server=' . urlencode($_POST["server"]) : '');
			}
			if (strlen(SID)) {
				$location .= (strpos($location, "?") === false ? "?" : "&") . SID;
			}
			header("Location: " . (strlen($location) ? $location : "."));
			exit;
		}
	}
	$_GET["server"] = $_POST["server"];
} elseif (isset($_GET["logout"])) {
	unset($_SESSION["usernames"][$_GET["server"]]);
	unset($_SESSION["passwords"][$_GET["server"]]);
	$_SESSION["tokens"][$_GET["server"]] = array();
	redirect(substr($SELF, 0, -1), lang('Logout successful.'));
}

if (!isset($_SESSION["usernames"][$_GET["server"]]) || !$mysql->connect($_GET["server"], $_SESSION["usernames"][$_GET["server"]], $_SESSION["passwords"][$_GET["server"]])) {
	page_header(lang('Login'));
	if (isset($_SESSION["usernames"][$_GET["server"]])) {
		echo "<p class='error'>" . lang('Invalid credentials.') . "</p>\n";
	} elseif (isset($_POST["server"])) {
		echo "<p class='error'>" . lang('Sessions must be enabled.') . "</p>\n";
	}
	?>
	<form action="" method="post">
	<table border="0" cellspacing="0" cellpadding="2">
	<tr><th><?php echo lang('Server'); ?>:</th><td><input name="server" value="<?php echo htmlspecialchars($_GET["server"]); ?>" maxlength="60" /></td></tr>
	<tr><th><?php echo lang('Username'); ?>:</th><td><input name="username" value="<?php echo htmlspecialchars($_SESSION["usernames"][$_GET["server"]]); ?>" maxlength="16" /></td></tr>
	<tr><th><?php echo lang('Password'); ?>:</th><td><input type="password" name="password" /></td></tr>
	<tr><th><?php
	foreach ($_POST as $key => $val) { // expired session
		if (is_array($val)) {
			foreach ($val as $key2 => $val2) {
				if (!is_array($val2)) {
					echo '<input type="hidden" name="' . htmlspecialchars($key . "[$key2]") . '" value="' . htmlspecialchars($val2) . '" />';
				} else {
					foreach ($val2 as $key3 => $val3) {
						echo '<input type="hidden" name="' . htmlspecialchars($key . "[$key2][$key3]") . '" value="' . htmlspecialchars($val3) . '" />';
					}
				}
			}
		} elseif ($key != "server" && $key != "username" && $key != "password") {
			echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '" />';
		}
	}
	foreach ($_FILES as $key => $val) {
		echo '<input type="hidden" name="files[' . htmlspecialchars($key) . ']" value="' . ($val["error"] ? $val["error"] : base64_encode(file_get_contents($val["tmp_name"]))) . '" />';
	}
	?></th><td><input type="submit" value="<?php echo lang('Login'); ?>" /></td></tr>
	</table>
	</form>
	<?php
	page_footer("auth");
	exit;
}
$mysql->query("SET SQL_QUOTE_SHOW_CREATE=1");
