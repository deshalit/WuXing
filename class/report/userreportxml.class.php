<?php

class UserReportXML {

    const DIR_NAME = 'rptdata';
    
    const NODE_ROOT      = 'report';
    const NODE_FIRSTNAME = 'fname';
    const NODE_LASTNAME  = 'lname';
    const NODE_TARGETNAME = 'name';
    const NODE_ORDERID   = 'order';
    const NODE_ID        = 'id';
    const NODE_HASH      = 'hash';
    const NODE_EMAIL     = 'email';
 //   const NODE_DATE      = '';
    const NODE_PROFILES  = 'profiles';
    const NODE_PROFILE   = 'prof';
    const NODE_TEXT      = 'text';
    const NODE_PROPS     = 'props';
    const NODE_PROPERTY  = 'prop';
    const NODE_RISK      = 'riskitems';
    const NODE_RISKITEM  = 'risk';
    const NODE_MASK      = 'elems';
    const NODE_USERNOTES = 'note';
    const ATTR_ID        = 'id';
    
    protected static function getDir() {
        return $_SERVER['DOCUMENT_ROOT'] . '/' . self::DIR_NAME;
    }
        
    public function getFileName($hash) {
        return self::getDir() . '/' . $hash . '.xml';
    }
}
    