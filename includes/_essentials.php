<?php

	function tokenGenerator($input)
	{
		return md5(__SALT.$input);
	}