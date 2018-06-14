<?php

namespace app\controllers;

use core\App;
use core\Utils;
use core\SessionUtils;
use core\Validator;
use app\forms\LeaderboardForm;

class ProfileCtrl {

    private $uid;

    public function __construct() {

    }

    private function loadUserId() {
        $this->uid = SessionUtils::load('sessionId', true);
    }

    public function isInParty() {
        $this->loadUserId();
        $pid = App::getDB()->get("user", "party_id", [
            "id" => $this->uid
        ]);
        if ($pid == NULL) {
            return false;
        } else
            return true;
    }

    public function action_leaveParty() {
        $this->loadUserId();
        try {
            App::getDB()->update("user", [
                "party_id" => null,
                "role_id" => 3
                    ], [
                "id" => $this->uid
            ]);
            RoleUtils::removeRole('moderator');
            RoleUtils::addRole('user');
        } catch (\PDOException $e) {
            Utils::addErrorMessage('Wystąpił nieoczekiwany błąd podczas zapisu rekordu');
            if (App::getConf()->debug)
                Utils::addErrorMessage($e->getMessage());
        }
        $this->generateView();
    }

    public function action_profile() {
        $this->generateView();
    }

    public function generateGravatarUrl() {
      $uid = SessionUtils::load('sessionId', true);
      $email = App::getDB()->get("user", "email", [
          "id" => $uid
      ]);
      return $gravatarUrl = 'http://gravatar.com/avatar/'.md5($email).'?d=monsterid&s=200';
    }

    public function generateView() {
        App::getSmarty()->assign('gravatar', $this->generateGravatarUrl());
        App::getSmarty()->assign('isInParty', $this->isInParty());
        App::getSmarty()->display('ProfileView.tpl');
    }

}
