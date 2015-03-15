<?php
// src/AcmeGroup/SurveyBundle/Survey/visiteEvent.php

namespace AcmeGroup\SurveyBundle\Survey;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class visiteEvent extends Event {

	protected $user;
	protected $IP;

	public function __construct(UserInterface $user) {
		$this->user = $user;
		$this->IP = $_SERVER["REMOTE_ADDR"];
	}

	public function getIP() {
		return $this->IP;
	}

	public function getUser() {
		return $this->user;
	}

}



?>