<?php
class Order {
    const PARAM_ID       = 'id';
    const PARAM_NAME     = 'name';
    const PARAM_LASTNAME = 'lastname';
    const PARAM_TARGETNAME = 'targetname';
    const PARAM_EMAIL    = 'email';
    const PARAM_NOTE     = 'note';
    const PARAM_ROOT     = 'orders';
    const PARAM_ITEM     = 'order';
    const PARAM_DATE     = 'regdate';
    const PARAM_IMAGE    = 'image'; 
    const PARAM_IMAGES   = 'images'; 
    const PARAM_PROMO    = 'promo';
    const PARAM_EYES    = 'eyes';
    const PARAM_HAIR    = 'hair';
    const PARAM_HEIGHT  = 'height';
    const PARAM_STATUS  = 'status';
    const PARAM_PROFILES = 'profiles';
    const PARAM_PROFILE = 'prof';    
    const PARAM_PAID    = 'paid';
    const PARAM_REPORTS = 'reports';
    const PARAM_REPORT  = 'report';
    
    public $regDate   = '';
    public $firstName = 'unknown';
    public $lastName  = 'unknown';
    public $email     = 'unknown';
    public $fileNames = [];
    public $id        = 0;
    public $notes     = null;
    public $promoCode = '';
    public $targetName = '';
    public $height    = '';
    public $eyes      = '';
    public $hair      = '';
    public $profiles  = Array();
 //   public $image     = null;
}