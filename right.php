<?php

/** @var \Modules\Base\Classes\Fetch\Rights $this */

$this->add_right("ipay", "ipay", "administrator", view:true, add:true, edit:true, delete:true);
$this->add_right("ipay", "ipay", "manager", view:true, add:true, edit:true, delete:true);
$this->add_right("ipay", "ipay", "supervisor", view:true, add:true, edit:true, delete:true);
$this->add_right("ipay", "ipay", "staff", view:true, add:true, edit:true);
$this->add_right("ipay", "ipay", "registered", view:true, add:true);
$this->add_right("ipay", "ipay", "guest", view:true, );
