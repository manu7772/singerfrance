<?php

namespace AcmeGroup\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeGroupUserBundle extends Bundle
{
	public function getParent() {
		return 'FOSUserBundle';
	}
}
