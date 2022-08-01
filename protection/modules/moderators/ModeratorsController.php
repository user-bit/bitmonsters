<?php
	class ModeratorsController extends BaseController
	{
		function  __construct($registry, $params)
		{
			parent::__construct($registry, $params);
			$this->tb = Moderators::$table;
			$this->name = Moderators::$name;
			$this->registry = $registry;
			$this->moderators = new Moderators($this->sets);
		}


		public function indexAction()
		{
			$vars['translate'] = $this->translation;
			$settings = $this->settings;
			$vars['message'] = '';
			if (isset($_POST['email'])) {
				$error = "";
				if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $error .= "<div class='alert alert-danger'>" . $this->translation['wrong_email'] . "</div>";
				$row = $this->db->row("SELECT id FROM moderators WHERE email=?", array($_POST['email']));
				if (!$row) $error .= "<div class='alert alert-danger'>" . $this->translation['email_exists2'] . "</div>";
				if ($error == "") {
					$pass = md5(uniqid());
					$this->db->query("UPDATE moderators SET active_code=? WHERE `id`=?", array($pass, $row['id']));
					$text = "Для смены пароля пройдите пожалуйста по ссылке <a href='".$prot."://{$_SERVER['HTTP_HOST']}/moderators/forgotpass/changepass/$pass' target='_blank'>".$prot."://{$_SERVER['HTTP_HOST']}/moderators/forgotpass/changepass/$pass</a>";
					Mail::send($settings['sitename'], // имя отправителя
						"info@" . $_SERVER['HTTP_HOST'], // email отправителя
						"Пользователь на сайте " . $settings['sitename'], // имя получателя
						$_POST['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"utf-8", // кодировка письма
						"Запрос о смене пароля на сайте " . $settings['sitename'], // тема письма
						$text // текст письма
					);
					$vars['message'] = "<div class='alert alert-success'>" . $this->translation['change_pass'] . "</div>";
				} else $vars['message'] = $error;
			}
			if (isset($this->params['changepass'])) {
				$row = $this->db->row("SELECT id, email, name FROM moderators WHERE active_code=?", array($this->params['changepass']));
				if (!$row) $vars['message'] = "<div class='alert alert-danger'>" . $this->translation['wrong_active'] . "!</div>";
				else {
					$pass2 = genPassword();
					$pass = md5($pass2);
					$code = md5(mktime());
					$this->db->query("UPDATE moderators SET password=?, active_code=? WHERE `id`=?", array($pass, $code, $row['id']));
					$text = "Ваш новый пароль: $pass2";
					Mail::send($settings['sitename'], // имя отправителя
						"info@" . $_SERVER['HTTP_HOST'], // email отправителя
						$row['name'], // имя получателя
						$row['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"utf-8", // кодировка письма
						"Ваш пароль изменен на сайте " . $settings['sitename'], //тема письма
						$text // текст письма
					);
					$vars['message'] = "<div class='alert alert-success'>" . $this->translation['change_new_pass'] . " Перейти в <a href='/admin'>Админ панель</a></div>";
				}
			}
			$data['content'] = $this->view->Render('forgotpassModerators.phtml', $vars);
			return $this->index($data);
		}
	}
